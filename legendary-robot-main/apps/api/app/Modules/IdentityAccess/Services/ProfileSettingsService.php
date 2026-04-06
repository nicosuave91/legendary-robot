<?php

declare(strict_types=1);

namespace App\Modules\IdentityAccess\Services;

use Illuminate\Support\Str;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Onboarding\Models\UserProfile;
use App\Modules\Shared\Audit\AuditLogger;

final class ProfileSettingsService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function updateSelf(User $actor, array $payload, string $correlationId): array
    {
        $profile = $actor->profile ?? new UserProfile([
            'id' => (string) Str::uuid(),
            'tenant_id' => (string) $actor->tenant_id,
            'user_id' => (string) $actor->id,
        ]);

        $before = [
            'displayName' => (string) $actor->name,
            'firstName' => (string) ($profile->first_name ?? ''),
            'lastName' => (string) ($profile->last_name ?? ''),
            'phone' => (string) ($profile->phone ?? ''),
            'birthday' => $profile->birthday?->format('Y-m-d'),
            'addressLine1' => (string) ($profile->address_line_1 ?? ''),
            'addressLine2' => (string) ($profile->address_line_2 ?? ''),
            'city' => (string) ($profile->city ?? ''),
            'stateCode' => (string) ($profile->state_code ?? ''),
            'postalCode' => (string) ($profile->postal_code ?? ''),
        ];

        $actor->fill([
            'name' => (string) $payload['displayName'],
        ]);
        $actor->save();

        $profile->fill([
            'first_name' => $payload['firstName'] ?? null,
            'last_name' => $payload['lastName'] ?? null,
            'phone' => $payload['phone'] ?? null,
            'birthday' => $payload['birthday'] ?? null,
            'address_line_1' => $payload['addressLine1'] ?? null,
            'address_line_2' => $payload['addressLine2'] ?? null,
            'city' => $payload['city'] ?? null,
            'state_code' => $payload['stateCode'] ?? null,
            'postal_code' => $payload['postalCode'] ?? null,
        ]);
        $profile->save();

        $after = [
            'displayName' => (string) $actor->name,
            'firstName' => (string) ($profile->first_name ?? ''),
            'lastName' => (string) ($profile->last_name ?? ''),
            'phone' => (string) ($profile->phone ?? ''),
            'birthday' => $profile->birthday?->format('Y-m-d'),
            'addressLine1' => (string) ($profile->address_line_1 ?? ''),
            'addressLine2' => (string) ($profile->address_line_2 ?? ''),
            'city' => (string) ($profile->city ?? ''),
            'stateCode' => (string) ($profile->state_code ?? ''),
            'postalCode' => (string) ($profile->postal_code ?? ''),
        ];

        $this->auditLogger->record([
            'tenant_id' => (string) $actor->tenant_id,
            'actor_id' => (string) $actor->id,
            'action' => 'settings.profile.update',
            'subject_type' => 'user',
            'subject_id' => (string) $actor->id,
            'correlation_id' => $correlationId,
            'before_summary' => json_encode($before, JSON_THROW_ON_ERROR),
            'after_summary' => json_encode($after, JSON_THROW_ON_ERROR),
        ]);

        return $after;
    }

    /**
     * @return array<string, mixed>
     */
    public function snapshot(User $actor): array
    {
        $actor->loadMissing(['profile']);

        return [
            'userId' => (string) $actor->id,
            'email' => (string) $actor->email,
            'displayName' => (string) $actor->name,
            'firstName' => (string) ($actor->profile?->first_name ?? ''),
            'lastName' => (string) ($actor->profile?->last_name ?? ''),
            'phone' => (string) ($actor->profile?->phone ?? ''),
            'birthday' => $actor->profile?->birthday?->format('Y-m-d'),
            'addressLine1' => (string) ($actor->profile?->address_line_1 ?? ''),
            'addressLine2' => (string) ($actor->profile?->address_line_2 ?? ''),
            'city' => (string) ($actor->profile?->city ?? ''),
            'stateCode' => (string) ($actor->profile?->state_code ?? ''),
            'postalCode' => (string) ($actor->profile?->postal_code ?? ''),
        ];
    }
}
