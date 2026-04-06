<?php

declare(strict_types=1);

namespace App\Modules\Onboarding\Http\Resources;

final class OnboardingStateResource
{
    /**
     * @param array<string, mixed> $state
     */
    public function __construct(
        private readonly array $state,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function resolve(): array
    {
        return $this->state;
    }
}
