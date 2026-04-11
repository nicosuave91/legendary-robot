<?php

declare(strict_types=1);

namespace App\Modules\Onboarding\Tests\Feature;

use PHPUnit\Framework\TestCase;

final class OnboardingStateContractTest extends TestCase
{
    public function test_openapi_defines_profile_confirmation_and_industry_selection(): void
    {
        $contractPath = dirname(__DIR__, 7) . '/apps/api/contracts/openapi.php';
        $contract = require $contractPath;
        $schemas = $contract['components']['schemas'];

        $this->assertArrayHasKey('ProfileConfirmationRequest', $schemas);
        $this->assertArrayHasKey('IndustrySelectionRequest', $schemas);
        $this->assertSame('patchOnboardingProfileConfirmation', $contract['paths']['/api/v1/onboarding/profile-confirmation']['patch']['operationId']);
        $this->assertSame('patchOnboardingIndustrySelection', $contract['paths']['/api/v1/onboarding/industry-selection']['patch']['operationId']);
    }
}
