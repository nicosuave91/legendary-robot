<?php

declare(strict_types=1);

namespace Tests\Contract;

use PHPUnit\Framework\TestCase;

final class OpenApiArtifactTest extends TestCase
{
    private function artifact(): array
    {
        $artifactPath = dirname(__DIR__, 4) . '/packages/contracts/openapi.json';

        return json_decode((string) file_get_contents($artifactPath), true, 512, JSON_THROW_ON_ERROR);
    }

    public function test_contract_contains_settings_and_tenant_governance_paths(): void
    {
        $artifact = $this->artifact();

        $this->assertArrayHasKey('/api/v1/settings/profile', $artifact['paths']);
        $this->assertArrayHasKey('/api/v1/settings/theme', $artifact['paths']);
        $this->assertArrayHasKey('/api/v1/settings/accounts/{userId}', $artifact['paths']);
        $this->assertArrayHasKey('/api/v1/settings/industry-configurations', $artifact['paths']);
    }

    public function test_contract_contains_communications_paths(): void
    {
        $artifact = $this->artifact();

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
        $artifact = $this->artifact();

        $this->assertArrayHasKey('/api/v1/clients/{clientId}/disposition-transitions', $artifact['paths']);
        $this->assertArrayHasKey('/api/v1/clients/{clientId}/applications', $artifact['paths']);
        $this->assertArrayHasKey('/api/v1/clients/{clientId}/applications/{applicationId}', $artifact['paths']);
        $this->assertArrayHasKey('/api/v1/clients/{clientId}/applications/{applicationId}/status-transitions', $artifact['paths']);
    }

    public function test_contract_contains_phase_8_and_phase_9_release_surfaces(): void
    {
        $artifact = $this->artifact();

        $this->assertArrayHasKey('/api/v1/rules', $artifact['paths']);
        $this->assertArrayHasKey('/api/v1/rules/{ruleId}/publish', $artifact['paths']);
        $this->assertArrayHasKey('/api/v1/workflows', $artifact['paths']);
        $this->assertArrayHasKey('/api/v1/workflows/{workflowId}/publish', $artifact['paths']);
        $this->assertArrayHasKey('/api/v1/imports', $artifact['paths']);
        $this->assertArrayHasKey('/api/v1/imports/{importId}/validate', $artifact['paths']);
        $this->assertArrayHasKey('/api/v1/imports/{importId}/commit', $artifact['paths']);
        $this->assertArrayHasKey('/api/v1/notifications', $artifact['paths']);
        $this->assertArrayHasKey('/api/v1/audit', $artifact['paths']);
    }

    public function test_contract_contains_release_critical_envelopes_and_schemas(): void
    {
        $artifact = $this->artifact();

        $this->assertArrayHasKey('NotificationListEnvelope', $artifact['components']['schemas']);
        $this->assertArrayHasKey('ImportListEnvelope', $artifact['components']['schemas']);
        $this->assertArrayHasKey('ImportDetailEnvelope', $artifact['components']['schemas']);
        $this->assertArrayHasKey('AuditListEnvelope', $artifact['components']['schemas']);
        $this->assertArrayHasKey('RuleListEnvelope', $artifact['components']['schemas']);
        $this->assertArrayHasKey('WorkflowListEnvelope', $artifact['components']['schemas']);
    }
}
