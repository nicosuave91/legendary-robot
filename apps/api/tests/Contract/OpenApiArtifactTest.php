<?php

declare(strict_types=1);

namespace Tests\Contract;

use PHPUnit\Framework\TestCase;

final class OpenApiArtifactTest extends TestCase
{
    public function test_contract_contains_settings_and_tenant_governance_paths(): void
    {
        $artifactPath = dirname(__DIR__, 3) . '/packages/contracts/openapi.json';
        $artifact = json_decode((string) file_get_contents($artifactPath), true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('/api/v1/settings/profile', $artifact['paths']);
        $this->assertArrayHasKey('/api/v1/settings/theme', $artifact['paths']);
        $this->assertArrayHasKey('/api/v1/settings/accounts/{userId}', $artifact['paths']);
        $this->assertArrayHasKey('/api/v1/settings/industry-configurations', $artifact['paths']);
    }

    public function test_contract_contains_communications_paths(): void
    {
        $artifactPath = dirname(__DIR__, 3) . '/packages/contracts/openapi.json';
        $artifact = json_decode((string) file_get_contents($artifactPath), true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('/api/v1/clients/{clientId}/communications', $artifact['paths']);
        $this->assertArrayHasKey('/api/v1/clients/{clientId}/communications/sms', $artifact['paths']);
        $this->assertArrayHasKey('/api/v1/clients/{clientId}/communications/email', $artifact['paths']);
        $this->assertArrayHasKey('/api/v1/clients/{clientId}/communications/call', $artifact['paths']);
        $this->assertArrayHasKey('/webhooks/twilio/messaging', $artifact['paths']);
        $this->assertArrayHasKey('/webhooks/twilio/voice', $artifact['paths']);
        $this->assertArrayHasKey('/webhooks/sendgrid/inbound', $artifact['paths']);
        $this->assertArrayHasKey('/webhooks/sendgrid/events', $artifact['paths']);
    }

    public function test_contract_contains_sprint_7_disposition_and_application_paths(): void
    {
        $artifactPath = dirname(__DIR__, 3) . '/packages/contracts/openapi.json';
        $artifact = json_decode((string) file_get_contents($artifactPath), true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('/api/v1/clients/{clientId}/disposition-transitions', $artifact['paths']);
        $this->assertArrayHasKey('/api/v1/clients/{clientId}/applications', $artifact['paths']);
        $this->assertArrayHasKey('/api/v1/clients/{clientId}/applications/{applicationId}', $artifact['paths']);
        $this->assertArrayHasKey('/api/v1/clients/{clientId}/applications/{applicationId}/status-transitions', $artifact['paths']);
    }
}
