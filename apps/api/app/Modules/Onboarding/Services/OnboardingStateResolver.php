<?php

declare(strict_types=1);

namespace App\Modules\Onboarding\Services;

use App\Modules\IdentityAccess\Models\User;

final class OnboardingStateResolver
{
    /**
     * @return array{state: string, currentStep: ?string, selectedIndustry: ?string}
     */
    public function resolve(User $user): array
    {
        if ($user->hasRole('owner')) {
            return [
                'state' => 'not_applicable',
                'currentStep' => null,
                'selectedIndustry' => null,
            ];
        }

        $profileConfirmed = $user->profile?->profile_confirmed_at !== null;
        $industrySelected = $user->industryAssignment?->industry !== null;
        $completed = $user->onboardingState?->completed_at !== null || $user->onboardingState?->state === 'completed';

        if ($completed) {
            return [
                'state' => 'completed',
                'currentStep' => null,
                'selectedIndustry' => $user->industryAssignment?->industry,
            ];
        }

        if (!$profileConfirmed) {
            return [
                'state' => $user->onboardingState?->started_at ? 'in_progress' : 'required',
                'currentStep' => 'profile_confirmation',
                'selectedIndustry' => $user->industryAssignment?->industry,
            ];
        }

        if (!$industrySelected) {
            return [
                'state' => 'in_progress',
                'currentStep' => 'industry_selection',
                'selectedIndustry' => null,
            ];
        }

        return [
            'state' => 'in_progress',
            'currentStep' => 'completion',
            'selectedIndustry' => $user->industryAssignment?->industry,
        ];
    }
}
