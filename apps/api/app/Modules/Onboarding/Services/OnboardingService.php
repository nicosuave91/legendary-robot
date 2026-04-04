<?php

declare(strict_types=1);

namespace App\Modules\Onboarding\Services;

use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Onboarding\Models\OnboardingState;
use App\Modules\Onboarding\Models\UserIndustryAssignment;
use App\Modules\Onboarding\Models\UserProfile;
use App\Modules\Shared\Audit\AuditLogger;
use App\Modules\TenantGovernance\Models\TenantIndustryConfiguration;
use App\Modules\TenantGovernance\Services\IndustryConfigurationService;

final class OnboardingService
{
    private const INDUSTRIES = ['Legal', 'Medical', 'Mortgage'];

    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly OnboardingStateResolver $stateResolver,
        private readonly IndustryConfigurationService $industryConfigurationService,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function getState(User $user): array
    {
        $user->loadMissing(['profile', 'onboardingState', 'industryAssignment']);
        $resolved = $this->stateResolver->resolve($user);

        /** @var UserProfile|null $profile */
        $profile = $user->profile;

        return [
            'state' => $resolved['state'],
            'currentStep' => $resolved['currentStep'],
            'isBypassed' => $resolved['state'] === 'not_applicable',
            'availableIndustries' => self::INDUSTRIES,
            'selectedIndustry' => $resolved['selectedIndustry'],
            'selectedIndustryConfigVersion' => $resolved['selectedIndustryConfigVersion'],
            'profile' => [
                'firstName' => (string) ($profile?->first_name ?? ''),
                'lastName' => (string) ($profile?->last_name ?? ''),
                'phone' => (string) ($profile?->phone ?? ''),
                'birthday' => $profile?->birthday?->format('Y-m-d'),
                'addressLine1' => (string) ($profile?->address_line_1 ?? ''),
                'addressLine2' => (string) ($profile?->address_line_2 ?? ''),
                'city' => (string) ($profile?->city ?? ''),
                'stateCode' => (string) ($profile?->state_code ?? ''),
                'postalCode' => (string) ($profile?->postal_code ?? ''),
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

        /** @var UserProfile|null $currentProfile */
        $currentProfile = $user->profile;

        $profile = $currentProfile ?? new UserProfile([
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
        $user->loadMissing(['profile', 'industryAssignment', 'onboardingState']);

        /** @var UserProfile|null $profile */
        $profile = $user->profile;
        /** @var UserIndustryAssignment|null $existingAssignment */
        $existingAssignment = $user->industryAssignment;

        if (!in_array($industry, self::INDUSTRIES, true)) {
            throw ValidationException::withMessages(['industry' => 'Unsupported industry selection.']);
        }

        if ($profile?->profile_confirmed_at === null) {
            throw ValidationException::withMessages(['profile' => 'Profile confirmation is required before industry selection.']);
        }

        /** @var TenantIndustryConfiguration|null $activeConfig */
        $activeConfig = $this->industryConfigurationService->activeVersionForTenantAndIndustry(
            (string) $user->tenant_id,
            $industry,
        );

        if ($activeConfig === null) {
            throw ValidationException::withMessages([
                'industry' => 'No active tenant configuration version exists for the selected industry.',
            ]);
        }

        $assignment = $existingAssignment ?? new UserIndustryAssignment([
            'id' => (string) Str::uuid(),
            'tenant_id' => (string) $user->tenant_id,
            'user_id' => (string) $user->id,
        ]);
        $before = $assignment->exists
            ? ['industry' => $assignment->industry, 'configVersion' => $assignment->config_version]
            : null;

        $assignment->fill([
            'industry' => $industry,
            'config_version' => (string) $activeConfig->version,
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
            'before_summary' => $before ? json_encode($before, JSON_THROW_ON_ERROR) : null,
            'after_summary' => json_encode([
                'industry' => $industry,
                'configVersion' => (string) $activeConfig->version,
            ], JSON_THROW_ON_ERROR),
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
            'after_summary' => json_encode(['completedAt' => $state->completed_at->toIso8601String()], JSON_THROW_ON_ERROR),
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
        /** @var OnboardingState|null $existingState */
        $existingState = $user->onboardingState;

        if ($existingState !== null) {
            return $existingState;
        }

        /** @var OnboardingState $state */
        $state = OnboardingState::query()->create([
            'id' => (string) Str::uuid(),
            'tenant_id' => (string) $user->tenant_id,
            'user_id' => (string) $user->id,
            'state' => 'required',
            'required_at' => now(),
        ]);

        return $state;
    }
}
