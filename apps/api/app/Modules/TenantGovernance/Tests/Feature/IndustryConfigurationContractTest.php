<?php

declare(strict_types=1);

namespace App\Modules\TenantGovernance\Tests\Feature;

use PHPUnit\Framework\TestCase;

final class IndustryConfigurationContractTest extends TestCase
{
    public function test_openapi_defines_versioned_industry_configuration_contracts(): void
    {
        $contractPath = dirname(__DIR__, 7) . '/apps/api/contracts/openapi.php';
        $contract = require $contractPath;
        $schemas = $contract['components']['schemas'];

        $this->assertArrayHasKey('IndustryConfigurationSummary', $schemas);
        $this->assertArrayHasKey('CreateIndustryConfigurationRequest', $schemas);
        $this->assertSame(
            'postSettingsIndustryConfigurations',
            $contract['paths']['/api/v1/settings/industry-configurations']['post']['operationId']
        );
    }
}
