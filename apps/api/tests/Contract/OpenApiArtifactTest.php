<?php

declare(strict_types=1);

namespace Tests\Contract;

use PHPUnit\Framework\TestCase;

final class OpenApiArtifactTest extends TestCase
{
    public function test_sprint_3_contract_contains_settings_and_tenant_governance_paths(): void
    {
        $artifactPath = dirname(__DIR__, 3) . '/packages/contracts/openapi.json';
        $artifact = json_decode((string) file_get_contents($artifactPath), true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('/api/v1/settings/profile', $artifact['paths']);
        $this->assertArrayHasKey('/api/v1/settings/theme', $artifact['paths']);
        $this->assertArrayHasKey('/api/v1/settings/accounts/{userId}', $artifact['paths']);
        $this->assertArrayHasKey('/api/v1/settings/industry-configurations', $artifact['paths']);
    }
}
