<?php

declare(strict_types=1);

namespace App\Modules\CalendarTasks\Tests\Feature;

use PHPUnit\Framework\TestCase;

final class CalendarTasksContractTest extends TestCase
{
    public function test_contract_and_generated_client_define_calendar_surfaces(): void
    {
        $contractPath = dirname(__DIR__, 7) . '/apps/api/contracts/openapi.php';
        $contract = require $contractPath;
        $clientPath = dirname(__DIR__, 7) . '/apps/web/src/lib/api/generated/client.ts';
        $client = (string) file_get_contents($clientPath);

        $this->assertArrayHasKey('/api/v1/calendar/day', $contract['paths']);
        $this->assertArrayHasKey('/api/v1/events', $contract['paths']);
        $this->assertArrayHasKey('/api/v1/events/{eventId}', $contract['paths']);
        $this->assertArrayHasKey('/api/v1/tasks/{taskId}/status', $contract['paths']);
        $this->assertArrayHasKey('/api/v1/clients/{clientId}/events', $contract['paths']);
        $this->assertArrayHasKey('CalendarDayEnvelope', $contract['components']['schemas']);
        $this->assertArrayHasKey('EventDetailEnvelope', $contract['components']['schemas']);
        $this->assertStringContainsString('getCalendarDay', $client);
        $this->assertStringContainsString('postEvents', $client);
        $this->assertStringContainsString('patchTaskStatus', $client);
        $this->assertStringContainsString('getClientEvents', $client);
    }
}
