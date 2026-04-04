<?php

declare(strict_types=1);

namespace App\Modules\IdentityAccess\Services;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Modules\IdentityAccess\Models\Role;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Onboarding\Models\OnboardingState;
use App\Modules\Onboarding\Models\UserIndustryAssignment;
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
            ->with(['roles', 'onboardingState', 'industryAssignment', 'profile'])
            ->orderBy('name')
            ->get()
            ->map(function (User $user): array {
                /** @var UserProfile|null $profile */
                $profile = $user->profile;
                /** @var OnboardingState|null $onboardingState */
                $onboardingState = $user->onboardingState;
                /** @var UserIndustryAssignment|null $industryAssignment */
                $industryAssignment = $user->industryAssignment;

                return [
                    'id' => (string) $user->id,
                    'email' => (string) $user->email,
                    'displayName' => (string) $user->name,
                    'roles' => $user->roles->pluck('name')->values()->all(),
                    'status' => (string) ($user->status ?? 'active'),
                    'onboardingState' => (string) ($onboardingState?->state ?? 'required'),
                    'selectedIndustry' => $industryAssignment?->industry,
                    'selectedIndustryConfigVersion' => $industryAssignment?->config_version,
                    'firstName' => (string) ($profile?->first_name ?? ''),
                    'lastName' => (string) ($profile?->last_name ?? ''),
                ];
            })
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
            'status' => 'active',
            'created_by' => (string) $actor->id,
        ]);

        /** @var Role $role */
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
                'status' => 'active',
                'onboardingState' => 'required',
            ], JSON_THROW_ON_ERROR),
        ]);

        return [
            'id' => (string) $user->id,
            'email' => (string) $user->email,
            'displayName' => (string) $user->name,
            'roles' => [(string) $role->name],
            'status' => 'active',
            'onboardingState' => 'required',
            'selectedIndustry' => null,
            'selectedIndustryConfigVersion' => null,
            'firstName' => (string) ($payload['firstName'] ?? ''),
            'lastName' => (string) ($payload['LastName'] ?? $payload['lastName'] ?? ''),
        ];
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function updateAccount(User $actor, User $subject, array $payload, string $correlationId): array
    {
        $subject->loadMissing(['roles', 'profile', 'onboardingState', 'industryAssignment']);

        $before = [
            'displayName' => (string) $subject->name,
            'role' => (string) ($subject->roles->first()?->name ?? 'user'),
            'status' => (string) ($subject->status ?? 'active'),
        ];

        $subject->fill([
            'name' => (string) $payload['displayName'],
            'status' => (string) $payload['status'],
            'deactivated_at' => $payload['status'] === 'deactivated' ? now() : null,
        ]);
        $subject->save();

        /** @var Role $role */
        $role = Role::query()->where('name', (string) $payload['role'])->firstOrFail();
        $subject->roles()->sync([$role->id]);

        /** @var UserProfile|null $currentProfile */
        $currentProfile = $subject->profile;

        $profile = $currentProfile ?? new UserProfile([
            'id' => (string) Str::uuid(),
            'tenant_id' => (string) $subject->tenant_id,
            'user_id' => (string) $subject->id,
        ]);
        $profile->fill([
            'first_name' => $payload['firstName'] ?? null,
            'last_name' => $payload['lastName'] ?? null,
        ]);
        $profile->save();

        /** @var OnboardingState|null $onboardingState */
        $onboardingState = $subject->onboardingState;
        /** @var UserIndustryAssignment|null $industryAssignment */
        $industryAssignment = $subject->industryAssignment;

        $after = [
            'displayName' => (string) $subject->name,
            'role' => (string) $role->name,
            'status' => (string) $subject->status,
        ];

        $this->auditLogger->record([
            'tenant_id' => (string) $actor->tenant_id,
            'actor_id' => (string) $actor->id,
            'action' => 'settings.accounts.update',
            'subject_type' => 'user',
            'subject_id' => (string) $subject->id,
            'correlation_id' => $correlationId,
            'before_summary' => json_encode($before, JSON_THROW_ON_ERROR),
            'after_summary' => json_encode($after, JSON_THROW_ON_ERROR),
        ]);

        return [
            'id' => (string) $subject->id,
            'email' => (string) $subject->email,
            'displayName' => (string) $subject->name,
            'roles' => [(string) $role->name],
            'status' => (string) $subject->status,
            'onboardingState' => (string) ($onboardingState?->state ?? 'required'),
            'selectedIndustry' => $industryAssignment?->industry,
            'selectedIndustryConfigVersion' => $industryAssignment?->config_version,
            'firstName' => (string) ($profile->first_name ?? ''),
            'lastName' => (string) ($profile->last_name ?? ''),
        ];
    }

    public function decommissionAccount(User $actor, User $subject, string $correlationId): void
    {
        $before = [
            'status' => (string) ($subject->status ?? 'active'),
            'deletedAt' => $subject->deleted_at?->toIso8601String(),
        ];

        $subject->fill([
            'status' => 'deactivated',
            'deactivated_at' => now(),
        ]);
        $subject->save();
        $subject->delete();

        $this->auditLogger->record([
            'tenant_id' => (string) $actor->tenant_id,
            'actor_id' => (string) $actor->id,
            'action' => 'settings.accounts.decommission',
            'subject_type' => 'user',
            'subject_id' => (string) $subject->id,
            'correlation_id' => $correlationId,
            'before_summary' => json_encode($before, JSON_THROW_ON_ERROR),
            'after_summary' => json_encode([
                'status' => 'deactivated',
                'deletedAt' => $subject->deleted_at?->toIso8601String(),
            ], JSON_THROW_ON_ERROR),
        ]);
    }
}
