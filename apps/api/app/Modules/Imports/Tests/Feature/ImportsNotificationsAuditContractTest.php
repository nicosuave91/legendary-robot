<?php

declare(strict_types=1);

namespace App\Modules\Imports\Tests\Feature;

use PHPUnit\Framework\TestCase;

final class ImportsNotificationsAuditContractTest extends TestCase
{
    public function test_contract_and_generated_client_define_sprint_9_surfaces(): void
    {
        $contractPath = dirname(__DIR__, 7) . '/apps/api/contracts/openapi.php';
        $contract = require $contractPath;
        $clientPath = dirname(__DIR__, 7) . '/apps/web/src/lib/api/generated/client.ts';
        $client = (string) file_get_contents($clientPath);

        $this->assertArrayHasKey('/api/v1/imports', $contract['paths']);
        $this->assertArrayHasKey('/api/v1/imports/{importId}', $contract['paths']);
        $this->assertArrayHasKey('/api/v1/imports/{importId}/validate', $contract['paths']);
        $this->assertArrayHasKey('/api/v1/imports/{importId}/commit', $contract['paths']);
        $this->assertArrayHasKey('/api/v1/notifications', $contract['paths']);
        $this->assertArrayHasKey('/api/v1/notifications/{notificationId}/dismiss', $contract['paths']);
        $this->assertArrayHasKey('/api/v1/audit', $contract['paths']);

        $this->assertArrayHasKey('ImportListEnvelope', $contract['components']['schemas']);
        $this->assertArrayHasKey('NotificationListEnvelope', $contract['components']['schemas']);
        $this->assertArrayHasKey('AuditListEnvelope', $contract['components']['schemas']);

        $this->assertStringContainsString('getImports', $client);
        $this->assertStringContainsString('postImportCommit', $client);
        $this->assertStringContainsString('getNotifications', $client);
        $this->assertStringContainsString('postNotificationDismiss', $client);
        $this->assertStringContainsString('getAudit', $client);
    }
}
