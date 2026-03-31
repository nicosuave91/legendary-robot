<?php

declare(strict_types=1);

namespace App\Modules\CalendarTasks\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Modules\CalendarTasks\Models\CalendarEvent;
use App\Modules\CalendarTasks\Models\EventTask;
use App\Modules\CalendarTasks\Models\TaskStatusHistory;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Shared\Audit\AuditLogger;

final class TaskStatusTransitionService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly EventQueryService $eventQueryService,
    ) {
    }

    public function transition(User $actor, EventTask $task, array $payload, string $correlationId): array
    {
        $targetStatus = (string) $payload['targetStatus'];
        $currentStatus = (string) $task->status;
        if (!in_array($targetStatus, $this->allowedTransitions($currentStatus), true)) {
            abort(422, 'The requested task status transition is not allowed from the current status.');
        }
        if ($targetStatus === 'blocked' && empty($payload['blockedReason'])) {
            abort(422, 'Blocked tasks require a blockedReason.');
        }

        DB::transaction(function () use ($actor, $task, $payload, $targetStatus, $currentStatus): void {
            $task->forceFill([
                'status' => $targetStatus,
                'completed_at' => $targetStatus === 'completed' ? now() : null,
                'blocked_reason' => $targetStatus === 'blocked' ? ($payload['blockedReason'] ?? $payload['reason'] ?? null) : null,
                'updated_by' => (string) $actor->id,
            ])->save();

            TaskStatusHistory::query()->create([
                'id' => (string) Str::uuid(),
                'tenant_id' => (string) $task->tenant_id,
                'event_task_id' => (string) $task->id,
                'event_id' => (string) $task->event_id,
                'actor_user_id' => (string) $actor->id,
                'from_status' => $currentStatus,
                'to_status' => $targetStatus,
                'reason' => $payload['blockedReason'] ?? $payload['reason'] ?? null,
                'metadata' => null,
                'occurred_at' => now(),
            ]);
        });

        $this->auditLogger->record([
            'tenant_id' => (string) $actor->tenant_id,
            'actor_id' => (string) $actor->id,
            'action' => 'calendar.task.status_changed',
            'subject_type' => 'event_task',
            'subject_id' => (string) $task->id,
            'correlation_id' => $correlationId,
            'before_summary' => json_encode(['status' => $currentStatus], JSON_THROW_ON_ERROR),
            'after_summary' => json_encode(['status' => $targetStatus], JSON_THROW_ON_ERROR),
        ]);

        $event = CalendarEvent::query()->withoutGlobalScopes()->where('tenant_id', $actor->tenant_id)->where('id', $task->event_id)->firstOrFail();

        return [
            'result' => 'updated',
            'mutatedTaskId' => (string) $task->id,
            'event' => $this->eventQueryService->detailForActor($actor, $event),
        ];
    }

    public function allowedTransitions(string $currentStatus): array
    {
        return match ($currentStatus) {
            'open' => ['completed', 'skipped', 'blocked'],
            'blocked' => ['open', 'completed', 'skipped'],
            'skipped' => ['open', 'completed'],
            'completed' => ['open'],
            default => [],
        };
    }
}
