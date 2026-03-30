<?php

declare(strict_types=1);

namespace App\Modules\IdentityAccess\Services;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Modules\IdentityAccess\Models\Role;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Onboarding\Models\OnboardingState;
use App\Modules\Onboarding\Models\UserProfile;
use App\Modules\Shared\Audit\AuditLogger;

final class UserProvisioningService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listVisibleAccounts(User $actor): array
    {
        return User::query()
            ->where('tenant_id', $actor->tenant_id)
            ->with(['roles', 'onboardingState'])
            ->orderBy('name')
            ->get()
            ->map(fn (User $user): array => [
                'id' => (string) $user->id,
                'email' => (string) $user->email,
                'displayName' => (string) $user->name,
                'roles' => $user->roles->pluck('name')->values()->all(),
                'onboardingState' => (string) ($user->onboardingState?->state ?? 'required'),
            ])
            ->all();
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function createAccount(User $actor, array $payload, string $correlationId): array
    {
        $user = User::query()->create([
            'id' => (string) Str::uuid(),
            'tenant_id' => (string) $actor->tenant_id,
            'name' => (string) $payload['displayName'],
            'email' => (string) $payload['email'],
            'password' => Hash::make((string) $payload['password']),
            'created_by' => (string) $actor->id,
        ]);

        $role = Role::query()->where('name', (string) $payload['role'])->firstOrFail();
        $user->roles()->attach($role->id);

        UserProfile::query()->create([
            'id' => (string) Str::uuid(),
            'tenant_id' => (string) $actor->tenant_id,
            'user_id' => (string) $user->id,
            'first_name' => (string) ($payload['firstName'] ?? ''),
            'last_name' => (string) ($payload['lastName'] ?? ''),
        ]);

        OnboardingState::query()->create([
            'id' => (string) Str::uuid(),
            'tenant_id' => (string) $actor->tenant_id,
            'user_id' => (string) $user->id,
            'state' => 'required',
            'required_at' => now(),
        ]);

        $this->auditLogger->record([
            'tenant_id' => (string) $actor->tenant_id,
            'actor_id' => (string) $actor->id,
            'action' => 'settings.accounts.create',
            'subject_type' => 'user',
            'subject_id' => (string) $user->id,
            'correlation_id' => $correlationId,
            'before_summary' => null,
            'after_summary' => json_encode([
                'email' => $user->email,
                'role' => $role->name,
                'onboardingState' => 'required',
            ], JSON_THROW_ON_ERROR),
        ]);

        return [
            'id' => (string) $user->id,
            'email' => (string) $user->email,
            'displayName' => (string) $user->name,
            'roles' => [(string) $role->name],
            'onboardingState' => 'required',
        ];
    }
}
