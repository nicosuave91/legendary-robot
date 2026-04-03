<?php

declare(strict_types=1);

namespace Tests\Contract;

use PHPUnit\Framework\TestCase;

final class CalendarTasksOpenApiArtifactTest extends TestCase
{
    public function test_openapi_artifact_contains_calendar_paths_and_schemas(): void
    {
        $artifactPath = dirname(__DIR__, 4) . '/packages/contracts/openapi.json';
        $artifact = json_decode((string) file_get_contents($artifactPath), true, 512, JSON_THROW_ON_ERROR);

        $this->assertArrayHasKey('/api/v1/calendar/day', $artifact['paths']);
        $this->assertArrayHasKey('/api/v1/events', $artifact['paths']);
        $this->assertArrayHasKey('/api/v1/events/{eventId}', $artifact['paths']);
        $this->assertArrayHasKey('/api/v1/tasks/{taskId}/status', $artifact['paths']);
        $this->assertArrayHasKey('/api/v1/clients/{clientId}/events', $artifact['paths']);
        $this->assertArrayHasKey('CalendarDayEnvelope', $artifact['components']['schemas']);
        $this->assertArrayHasKey('EventDetailEnvelope', $artifact['components']['schemas']);
        $this->assertArrayHasKey('TaskStatusTransitionEnvelope', $artifact['components']['schemas']);
    }
}
