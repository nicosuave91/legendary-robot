\
<?php

declare(strict_types=1);

namespace App\Modules\Onboarding\Services;

use App\Modules\IdentityAccess\Models\User;
use App\Modules\Onboarding\Models\OnboardingState;
use App\Modules\Onboarding\Models\UserIndustryAssignment;
use App\Modules\Onboarding\Models\UserProfile;

final class OnboardingStateResolver
{
    /**
     * @return array{state: string, currentStep: ?string, selectedIndustry: ?string, selectedIndustryConfigVersion: ?string}
     */
    public function resolve(User $user): array
    {
        /** @var UserProfile|null $profile */
        $profile = $user->profile;
        /** @var UserIndustryAssignment|null $industryAssignment */
        $industryAssignment = $user->industryAssignment;
        /** @var OnboardingState|null $onboardingState */
        $onboardingState = $user->onboardingState;

        if ($user->hasRole('owner')) {
            return [
                'state' => 'not_applicable',
                'currentStep' => null,
                'selectedIndustry' => null,
                'selectedIndustryConfigVersion' => null,
            ];
        }

        $profileConfirmed = $profile?->profile_confirmed_at !== null;
        $industrySelected = $industryAssignment?->industry !== null;
        $completed = $onboardingState?->completed_at !== null || $onboardingState?->state === 'completed';

        if ($completed) {
            return [
                'state' => 'completed',
                'currentStep' => null,
                'selectedIndustry' => $industryAssignment?->industry,
                'selectedIndustryConfigVersion' => $industryAssignment?->config_version,
            ];
        }

        if (!$profileConfirmed) {
            return [
                'state' => $onboardingState?->started_at ? 'in_progress' : 'required',
                'currentStep' => 'profile_confirmation',
                'selectedIndustry' => $industryAssignment?->industry,
                'selectedIndustryConfigVersion' => $industryAssignment?->config_version,
            ];
        }

        if (!$industrySelected) {
            return [
                'state' => 'in_progress',
                'currentStep' => 'industry_selection',
                'selectedIndustry' => null,
                'selectedIndustryConfigVersion' => null,
            ];
        }

        return [
            'state' => 'in_progress',
            'currentStep' => 'completion',
            'selectedIndustry' => $industryAssignment?->industry,
            'selectedIndustryConfigVersion' => $industryAssignment?->config_version,
        ];
    }
}
