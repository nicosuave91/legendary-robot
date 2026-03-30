<?php

declare(strict_types=1);

namespace Tests\Contract;

use PHPUnit\Framework\TestCase;

final class OpenApiArtifactTest extends TestCase
{
    public function test_sprint_2_contract_contains_identity_and_onboarding_paths(): void
    {
        $artifactPath = dirname(__DIR__, 4) . '/packages/contracts/openapi.json';
        $artifact = json_decode((string) file_get_contents($artifactPath), true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('/api/v1/auth/sign-in', $artifact['paths']);
        $this->assertArrayHasKey('/api/v1/onboarding/state', $artifact['paths']);
        $this->assertArrayHasKey('/api/v1/settings/accounts', $artifact['paths']);
    }
}
