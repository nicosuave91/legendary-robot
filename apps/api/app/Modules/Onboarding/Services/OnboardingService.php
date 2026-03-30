<?php

declare(strict_types=1);

namespace App\Modules\Onboarding\Services;

use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Onboarding\Models\OnboardingState;
use App\Modules\Onboarding\Models\UserIndustryAssignment;
use App\Modules\Onboarding\Models\UserProfile;
use App\Modules\Shared\Audit\AuditLogger;

final class OnboardingService
{
    private const INDUSTRIES = ['Legal', 'Medical', 'Mortgage'];

    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly OnboardingStateResolver $stateResolver,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getState(User $user): array
    {
        $user->loadMissing(['profile', 'onboardingState', 'industryAssignment']);
        $resolved = $this->stateResolver->resolve($user);

        return [
            'state' => $resolved['state'],
            'currentStep' => $resolved['currentStep'],
            'isBypassed' => $resolved['state'] === 'not_applicable',
            'availableIndustries' => self::INDUSTRIES,
            'selectedIndustry' => $resolved['selectedIndustry'],
            'profile' => [
                'firstName' => (string) ($user->profile?->first_name ?? ''),
                'lastName' => (string) ($user->profile?->last_name ?? ''),
                'phone' => (string) ($user->profile?->phone ?? ''),
                'birthday' => $user->profile?->birthday?->format('Y-m-d'),
                'addressLine1' => (string) ($user->profile?->address_line_1 ?? ''),
                'addressLine2' => (string) ($user->profile?->address_line_2 ?? ''),
                'city' => (string) ($user->profile?->city ?? ''),
                'stateCode' => (string) ($user->profile?->state_code ?? ''),
                'postalCode' => (string) ($user->profile?->postal_code ?? ''),
            ],
            'canComplete' => $resolved['currentStep'] === 'completion',
        ];
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function confirmProfile(User $user, array $payload, string $correlationId): array
    {
        $this->assertNotOwnerBypass($user);

        $profile = $user->profile ?? new UserProfile([
            'id' => (string) Str::uuid(),
            'tenant_id' => (string) $user->tenant_id,
            'user_id' => (string) $user->id,
        ]);

        $before = $profile->exists ? $profile->only(['first_name', 'last_name', 'phone', 'city', 'state_code', 'postal_code']) : null;

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
            'profile_confirmed_at' => now(),
        ]);
        $profile->save();

        $state = $this->ensureMutableState($user);
        $state->state = 'in_progress';
        $state->started_at ??= now();
        $state->save();

        $this->auditLogger->record([
            'tenant_id' => (string) $user->tenant_id,
            'actor_id' => (string) $user->id,
            'action' => 'onboarding.profile_confirmed',
            'subject_type' => 'user',
            'subject_id' => (string) $user->id,
            'correlation_id' => $correlationId,
            'before_summary' => $before ? json_encode($before, JSON_THROW_ON_ERROR) : null,
            'after_summary' => json_encode([
                'firstName' => $profile->first_name,
                'lastName' => $profile->last_name,
                'confirmedAt' => $profile->profile_confirmed_at?->toIso8601String(),
            ], JSON_THROW_ON_ERROR),
        ]);

        $user->unsetRelation('profile');
        $user->unsetRelation('onboardingState');

        return $this->getState($user);
    }

    /**
     * @return array<string, mixed>
     */
    public function selectIndustry(User $user, string $industry, string $correlationId): array
    {
        $this->assertNotOwnerBypass($user);

        if (!in_array($industry, self::INDUSTRIES, true)) {
            throw ValidationException::withMessages(['industry' => 'Unsupported industry selection.']);
        }

        if ($user->profile?->profile_confirmed_at === null) {
            throw ValidationException::withMessages(['profile' => 'Profile confirmation is required before industry selection.']);
        }

        $assignment = $user->industryAssignment ?? new UserIndustryAssignment([
            'id' => (string) Str::uuid(),
            'tenant_id' => (string) $user->tenant_id,
            'user_id' => (string) $user->id,
        ]);
        $before = $assignment->exists ? $assignment->industry : null;
        $assignment->fill([
            'industry' => $industry,
            'config_version' => 'v1',
            'assigned_at' => now(),
        ]);
        $assignment->save();

        $state = $this->ensureMutableState($user);
        $state->state = 'in_progress';
        $state->started_at ??= now();
        $state->save();

        $this->auditLogger->record([
            'tenant_id' => (string) $user->tenant_id,
            'actor_id' => (string) $user->id,
            'action' => 'onboarding.industry_selected',
            'subject_type' => 'user',
            'subject_id' => (string) $user->id,
            'correlation_id' => $correlationId,
            'before_summary' => $before ? json_encode(['industry' => $before], JSON_THROW_ON_ERROR) : null,
            'after_summary' => json_encode(['industry' => $industry, 'configVersion' => 'v1'], JSON_THROW_ON_ERROR),
        ]);

        $user->unsetRelation('industryAssignment');
        $user->unsetRelation('onboardingState');

        return $this->getState($user);
    }

    /**
     * @return array<string, mixed>
     */
    public function complete(User $user, string $correlationId): array
    {
        $this->assertNotOwnerBypass($user);

        $stateSnapshot = $this->stateResolver->resolve($user->loadMissing(['profile', 'industryAssignment', 'onboardingState']));
        if ($stateSnapshot['currentStep'] !== 'completion') {
            throw ValidationException::withMessages(['onboarding' => 'Profile confirmation and industry selection must be completed first.']);
        }

        $state = $this->ensureMutableState($user);
        $state->state = 'completed';
        $state->started_at ??= now();
        $state->completed_at = now();
        $state->save();

        $this->auditLogger->record([
            'tenant_id' => (string) $user->tenant_id,
            'actor_id' => (string) $user->id,
            'action' => 'onboarding.completed',
            'subject_type' => 'user',
            'subject_id' => (string) $user->id,
            'correlation_id' => $correlationId,
            'before_summary' => null,
            'after_summary' => json_encode(['completedAt' => $state->completed_at?->toIso8601String()], JSON_THROW_ON_ERROR),
        ]);

        $user->unsetRelation('onboardingState');

        return $this->getState($user);
    }

    private function assertNotOwnerBypass(User $user): void
    {
        if ($user->hasRole('owner')) {
            throw ValidationException::withMessages(['onboarding' => 'Owners bypass onboarding.']);
        }
    }

    private function ensureMutableState(User $user): OnboardingState
    {
        return $user->onboardingState ?? OnboardingState::query()->create([
            'id' => (string) Str::uuid(),
            'tenant_id' => (string) $user->tenant_id,
            'user_id' => (string) $user->id,
            'state' => 'required',
            'required_at' => now(),
        ]);
    }
}
