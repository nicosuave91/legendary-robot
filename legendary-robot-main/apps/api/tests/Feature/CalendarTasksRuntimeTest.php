<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use Database\Seeders\DatabaseSeeder;
use App\Modules\Audit\Models\AuditLog;
use App\Modules\CalendarTasks\Models\CalendarEvent;
use App\Modules\CalendarTasks\Models\EventTask;
use App\Modules\CalendarTasks\Models\TaskStatusHistory;
use App\Modules\Clients\Models\Client;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\TenantGovernance\Models\Tenant;

final class CalendarTasksRuntimeTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;
    protected string $seeder = DatabaseSeeder::class;

    public function test_task_status_update_writes_history_and_audit_evidence(): void
    {
        $actor = User::query()->withoutGlobalScopes()->findOrFail('owner-user');
        Sanctum::actingAs($actor);

        $client = Client::query()->create([
            'id' => 'client-runtime-1',
            'tenant_id' => (string) $actor->tenant_id,
            'owner_user_id' => (string) $actor->id,
            'created_by' => (string) $actor->id,
            'display_name' => 'Runtime Client',
            'status' => 'lead',
        ]);

        $event = CalendarEvent::query()->create([
            'id' => 'event-runtime-1',
            'tenant_id' => (string) $actor->tenant_id,
            'client_id' => (string) $client->id,
            'owner_user_id' => (string) $actor->id,
            'created_by' => (string) $actor->id,
            'updated_by' => (string) $actor->id,
            'title' => 'Runtime verification event',
            'event_type' => 'appointment',
            'status' => 'scheduled',
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDay()->addHour(),
            'is_all_day' => false,
        ]);

        $task = EventTask::query()->create([
            'id' => 'task-runtime-1',
            'tenant_id' => (string) $actor->tenant_id,
            'event_id' => (string) $event->id,
            'assigned_user_id' => (string) $actor->id,
            'created_by' => (string) $actor->id,
            'updated_by' => (string) $actor->id,
            'title' => 'Complete runtime closeout',
            'status' => 'open',
            'sort_order' => 1,
            'is_required' => true,
        ]);

        $response = $this
            ->withHeader('X-Correlation-Id', 'corr-calendar-runtime')
            ->patchJson('/api/v1/tasks/' . $task->id . '/status', [
                'targetStatus' => 'completed',
                'reason' => 'Runtime closeout verification',
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.result', 'updated')
            ->assertJsonPath('data.mutatedTaskId', 'task-runtime-1');

        $task->refresh();

        self::assertSame('completed', $task->status);
        self::assertNotNull($task->completed_at);

        $history = TaskStatusHistory::query()
            ->withoutGlobalScopes()
            ->where('event_task_id', $task->id)
            ->latest('occurred_at')
            ->first();

        self::assertNotNull($history);
        self::assertSame('open', $history->from_status);
        self::assertSame('completed', $history->to_status);

        $audit = AuditLog::query()
            ->withoutGlobalScopes()
            ->where('subject_type', 'event_task')
            ->where('subject_id', $task->id)
            ->where('action', 'calendar.task.status_changed')
            ->latest('created_at')
            ->first();

        self::assertNotNull($audit);
        self::assertSame('corr-calendar-runtime', $audit->correlation_id);
    }

    public function test_client_events_endpoint_rejects_cross_tenant_access_by_tenant_scoping(): void
    {
        $actor = User::query()->withoutGlobalScopes()->findOrFail('owner-user');
        Sanctum::actingAs($actor);

        Tenant::query()->create([
            'id' => 'tenant-other',
            'name' => 'Other Tenant',
        ]);

        $foreignClient = Client::query()->create([
            'id' => 'client-foreign-1',
            'tenant_id' => 'tenant-other',
            'owner_user_id' => (string) $actor->id,
            'created_by' => (string) $actor->id,
            'display_name' => 'Foreign Tenant Client',
            'status' => 'lead',
        ]);

        $response = $this->getJson('/api/v1/clients/' . $foreignClient->id . '/events');

        $response->assertNotFound();
    }
}
