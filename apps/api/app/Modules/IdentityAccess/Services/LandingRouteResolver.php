<?php

declare(strict_types=1);

namespace App\Modules\IdentityAccess\Services;

use App\Modules\IdentityAccess\Models\User;
use App\Modules\Onboarding\Services\OnboardingStateResolver;

final class LandingRouteResolver
{
    public function __construct(
        private readonly OnboardingStateResolver $onboardingStateResolver,
    ) {
    }

    public function forUser(User $user): string
    {
        $state = $this->onboardingStateResolver->resolve($user);

        if ($state['state'] !== 'completed' && $state['state'] !== 'not_applicable') {
            return '/onboarding';
        }

        return '/app/dashboard';
    }
}
