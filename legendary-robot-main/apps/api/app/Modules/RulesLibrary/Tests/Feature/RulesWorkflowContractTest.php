<?php

declare(strict_types=1);

namespace App\Modules\RulesLibrary\Tests\Feature;

use PHPUnit\Framework\TestCase;

final class RulesWorkflowContractTest extends TestCase
{
    public function test_contract_and_generated_client_define_sprint_8_surfaces(): void
    {
        $contractPath = dirname(__DIR__, 7) . '/apps/api/contracts/openapi.php';
        $contract = require $contractPath;
        $clientPath = dirname(__DIR__, 7) . '/apps/web/src/lib/api/generated/client.ts';
        $client = (string) file_get_contents($clientPath);

        $this->assertArrayHasKey('/api/v1/rules', $contract['paths']);
        $this->assertArrayHasKey('/api/v1/rules/{ruleId}', $contract['paths']);
        $this->assertArrayHasKey('/api/v1/rules/{ruleId}/publish', $contract['paths']);
        $this->assertArrayHasKey('/api/v1/workflows', $contract['paths']);
        $this->assertArrayHasKey('/api/v1/workflows/{workflowId}', $contract['paths']);
        $this->assertArrayHasKey('/api/v1/workflows/{workflowId}/publish', $contract['paths']);

        $this->assertArrayHasKey('RuleListEnvelope', $contract['components']['schemas']);
        $this->assertArrayHasKey('WorkflowListEnvelope', $contract['components']['schemas']);
        $this->assertStringContainsString('getRules', $client);
        $this->assertStringContainsString('postRulePublish', $client);
        $this->assertStringContainsString('getWorkflows', $client);
        $this->assertStringContainsString('postWorkflowPublish', $client);
    }
}