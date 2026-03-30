<?php

declare(strict_types=1);

namespace App\Modules\Clients\Tests\Feature;

use PHPUnit\Framework\TestCase;

final class ClientContractTest extends TestCase
{
    public function test_contract_and_generated_client_define_sprint_4_client_surfaces(): void
    {
        $contractPath = dirname(__DIR__, 7) . '/apps/api/contracts/openapi.php';
        $contract = require $contractPath;
        $clientPath = dirname(__DIR__, 7) . '/apps/web/src/lib/api/generated/client.ts';
        $client = (string) file_get_contents($clientPath);

        $this->assertArrayHasKey('/api/v1/clients', $contract['paths']);
        $this->assertArrayHasKey('ClientWorkspaceEnvelope', $contract['components']['schemas']);
        $this->assertStringContainsString('getClients', $client);
        $this->assertStringContainsString('postClientDocuments', $client);
        $this->assertStringContainsString('queryParams?:', $client);
        $this->assertStringContainsString("contentType?: 'application/json' | 'multipart/form-data'", $client);
    }
}
