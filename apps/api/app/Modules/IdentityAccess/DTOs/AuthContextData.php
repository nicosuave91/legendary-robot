<?php

declare(strict_types=1);

namespace App\Modules\IdentityAccess\DTOs;

final readonly class AuthContextData
{
    /**
     * @param array<int, string> $roles
     * @param array<int, string> $permissions
     * @param array{primary: string, secondary: string, tertiary: string} $theme
     * @param array<int, string> $capabilities
     */
    public function __construct(
        public bool $isAuthenticated,
        public string $userId,
        public string $email,
        public string $displayName,
        public string $tenantId,
        public string $tenantName,
        public array $roles,
        public array $permissions,
        public string $onboardingState,
        public ?string $onboardingStep,
        public array $theme,
        public string $landingRoute,
        public ?string $selectedIndustry,
        public ?string $selectedIndustryConfigVersion,
        public array $capabilities,
    ) {
    }
}
