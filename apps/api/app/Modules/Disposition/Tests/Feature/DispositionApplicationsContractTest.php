<?php

declare(strict_types=1);

namespace App\Modules\Disposition\Tests\Feature;

use PHPUnit\Framework\TestCase;

final class DispositionApplicationsContractTest extends TestCase
{
    public function test_contract_and_generated_client_define_sprint_7_surfaces(): void
    {
        $contractPath = dirname(__DIR__, 7) . '/apps/api/contracts/openapi.php';
        $contract = require $contractPath;
        $clientPath = dirname(__DIR__, 7) . '/apps/web/src/lib/api/generated/client.ts';
        $client = (string) file_get_contents($clientPath);

        $this->assertArrayHasKey('/api/v1/clients/{clientId}/disposition-transitions', $contract['paths']);
        $this->assertArrayHasKey('/api/v1/clients/{clientId}/applications', $contract['paths']);
        $this->assertArrayHasKey('/api/v1/clients/{clientId}/applications/{applicationId}', $contract['paths']);
        $this->assertArrayHasKey('/api/v1/clients/{clientId}/applications/{applicationId}/status-transitions', $contract['paths']);

        $this->assertArrayHasKey('DispositionTransitionEnvelope', $contract['components']['schemas']);
        $this->assertArrayHasKey('ApplicationDetailEnvelope', $contract['components']['schemas']);
        $this->assertStringContainsString('postClientDispositionTransitions', $client);
        $this->assertStringContainsString('getClientApplications', $client);
        $this->assertStringContainsString('getClientApplication', $client);
        $this->assertStringContainsString('postClientApplicationStatusTransitions', $client);
    }
}
