<?php

declare(strict_types=1);

namespace App\Modules\CalendarTasks\Tests\Feature;

use App\Modules\Audit\Models\AuditLog;
use App\Modules\CalendarTasks\Models\EventTask;
use App\Modules\CalendarTasks\Models\TaskStatusHistory;
use Tests\Support\SeededApiTestCase;

final class CalendarTasksContractTest extends SeededApiTestCase
{
    public function test_calendar_day_event_detail_and_client_event_surfaces_are_runtime_backed(): void
    {
        $this->sanctumActingAs('owner-user');
        $selectedDate = now()->toDateString();

        $this->getJson('/api/v1/calendar/day?date=' . $selectedDate)
            ->assertOk()
            ->assertJsonPath('data.selectedDate', $selectedDate)
            ->assertJsonPath('data.summary.eventCount', 1)
            ->assertJsonPath('data.events.0.id', 'seed-event-jamie-intake');

        $this->getJson('/api/v1/events/seed-event-jamie-intake')
            ->assertOk()
            ->assertJsonPath('data.title', 'Borrower intake review')
            ->assertJsonPath('data.client.displayName', 'Jamie Foster')
            ->assertJsonPath('data.taskSummary.total', 2);

        $this->getJson('/api/v1/clients/client-jamie-foster/events')
            ->assertOk()
            ->assertJsonPath('data.items.0.id', 'seed-event-jamie-intake');
    }

    public function test_task_status_transition_updates_runtime_state_history_and_audit(): void
    {
        $this->sanctumActingAs('owner-user');

        $response = $this
            ->withHeader('X-Correlation-Id', 'corr-calendar-runtime-seeded-task')
            ->patchJson('/api/v1/tasks/seed-event-task-jamie-1/status', [
                'targetStatus' => 'completed',
                'reason' => 'Verified from runtime calendar drilldown test.',
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.result', 'updated')
            ->assertJsonPath('data.mutatedTaskId', 'seed-event-task-jamie-1')
            ->assertJsonPath('data.event.taskSummary.completed', 1);

        $task = EventTask::query()->withoutGlobalScopes()->findOrFail('seed-event-task-jamie-1');
        self::assertSame('completed', $task->status);
        self::assertNotNull($task->completed_at);

        $history = TaskStatusHistory::query()
            ->withoutGlobalScopes()
            ->where('event_task_id', 'seed-event-task-jamie-1')
            ->latest('occurred_at')
            ->first();

        self::assertNotNull($history);
        self::assertSame('open', $history->from_status);
        self::assertSame('completed', $history->to_status);

        $audit = AuditLog::query()
            ->withoutGlobalScopes()
            ->where('action', 'calendar.task.status_changed')
            ->where('subject_id', 'seed-event-task-jamie-1')
            ->latest('created_at')
            ->first();

        self::assertNotNull($audit);
        self::assertSame('corr-calendar-runtime-seeded-task', $audit->correlation_id);
    }
}
