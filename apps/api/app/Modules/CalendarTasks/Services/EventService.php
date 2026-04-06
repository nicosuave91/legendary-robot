<?php

declare(strict_types=1);

namespace App\Modules\CalendarTasks\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Modules\CalendarTasks\Models\CalendarEvent;
use App\Modules\CalendarTasks\Models\EventTask;
use App\Modules\CalendarTasks\Models\TaskStatusHistory;
use App\Modules\Clients\Models\Client;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Shared\Audit\AuditLogger;

final class EventService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly EventQueryService $eventQueryService,
    ) {
    }

    public function create(User $actor, array $payload, string $correlationId): array
    {
        $event = DB::transaction(function () use ($actor, $payload): CalendarEvent {
            $client = $this->resolveClient($actor, $payload['clientId'] ?? null);
            $owner = $this->resolveOwner($actor, $payload['ownerUserId'] ?? null);

            $event = CalendarEvent::query()->create([
                'id' => (string) Str::uuid(),
                'tenant_id' => (string) $actor->tenant_id,
                'client_id' => $client?->id,
                'owner_user_id' => $owner?->id,
                'created_by' => (string) $actor->id,
                'updated_by' => (string) $actor->id,
                'title' => (string) $payload['title'],
                'description' => $payload['description'] ?? null,
                'event_type' => (string) $payload['eventType'],
                'status' => (string) ($payload['status'] ?? 'scheduled'),
                'starts_at' => $payload['startsAt'],
                'ends_at' => $payload['endsAt'] ?? null,
                'is_all_day' => (bool) ($payload['isAllDay'] ?? false),
                'location' => $payload['location'] ?? null,
                'metadata' => $payload['metadata'] ?? null,
            ]);

            foreach (($payload['tasks'] ?? []) as $index => $taskPayload) {
                $task = EventTask::query()->create([
                    'id' => (string) Str::uuid(),
                    'tenant_id' => (string) $actor->tenant_id,
                    'event_id' => (string) $event->id,
                    'assigned_user_id' => $taskPayload['assignedUserId'] ?? null,
                    'created_by' => (string) $actor->id,
                    'updated_by' => (string) $actor->id,
                    'title' => (string) $taskPayload['title'],
                    'description' => $taskPayload['description'] ?? null,
                    'status' => 'open',
                    'sort_order' => (int) ($taskPayload['sortOrder'] ?? $index),
                    'is_required' => (bool) ($taskPayload['isRequired'] ?? true),
                    'due_at' => $taskPayload['dueAt'] ?? null,
                    'metadata' => $taskPayload['metadata'] ?? null,
                ]);

                TaskStatusHistory::query()->create([
                    'id' => (string) Str::uuid(),
                    'tenant_id' => (string) $actor->tenant_id,
                    'event_task_id' => (string) $task->id,
                    'event_id' => (string) $event->id,
                    'actor_user_id' => (string) $actor->id,
                    'from_status' => null,
                    'to_status' => 'open',
                    'reason' => 'Task created',
                    'metadata' => null,
                    'occurred_at' => now(),
                ]);
            }

            return $event;
        });

        $this->auditLogger->record([
            'tenant_id' => (string) $actor->tenant_id,
            'actor_id' => (string) $actor->id,
            'action' => 'calendar.event.created',
            'subject_type' => 'event',
            'subject_id' => (string) $event->id,
            'correlation_id' => $correlationId,
            'before_summary' => null,
            'after_summary' => json_encode(['eventType' => $event->event_type, 'status' => $event->status], JSON_THROW_ON_ERROR),
        ]);

        $event->refresh();

        return $this->eventQueryService->detailForActor($actor, $event);
    }

    public function update(User $actor, CalendarEvent $event, array $payload, string $correlationId): array
    {
        $before = ['status' => $event->status, 'startsAt' => $event->starts_at->toIso8601String(), 'clientId' => $event->client_id];

        DB::transaction(function () use ($actor, $event, $payload): void {
            if (array_key_exists('clientId', $payload)) {
                $client = $this->resolveClient($actor, $payload['clientId']);
                $event->client_id = $client?->id;
            }
            if (array_key_exists('ownerUserId', $payload)) {
                $owner = $this->resolveOwner($actor, $payload['ownerUserId']);
                $event->owner_user_id = $owner?->id;
            }

            $event->forceFill([
                'title' => $payload['title'] ?? $event->title,
                'description' => array_key_exists('description', $payload) ? ($payload['description'] ?? null) : $event->description,
                'event_type' => $payload['eventType'] ?? $event->event_type,
                'status' => $payload['status'] ?? $event->status,
                'starts_at' => $payload['startsAt'] ?? $event->starts_at,
                'ends_at' => array_key_exists('endsAt', $payload) ? ($payload['endsAt'] ?? null) : $event->ends_at,
                'is_all_day' => array_key_exists('isAllDay', $payload) ? (bool) $payload['isAllDay'] : $event->is_all_day,
                'location' => array_key_exists('location', $payload) ? ($payload['location'] ?? null) : $event->location,
                'metadata' => array_key_exists('metadata', $payload) ? ($payload['metadata'] ?? null) : $event->metadata,
                'updated_by' => (string) $actor->id,
            ])->save();
        });

        $this->auditLogger->record([
            'tenant_id' => (string) $actor->tenant_id,
            'actor_id' => (string) $actor->id,
            'action' => 'calendar.event.updated',
            'subject_type' => 'event',
            'subject_id' => (string) $event->id,
            'correlation_id' => $correlationId,
            'before_summary' => json_encode($before, JSON_THROW_ON_ERROR),
            'after_summary' => json_encode(['status' => $event->status, 'startsAt' => $event->starts_at->toIso8601String(), 'clientId' => $event->client_id], JSON_THROW_ON_ERROR),
        ]);

        $event->refresh();

        return $this->eventQueryService->detailForActor($actor, $event);
    }

    private function resolveClient(User $actor, ?string $clientId): ?Client
    {
        if ($clientId === null || $clientId === '') {
            return null;
        }

        return Client::query()->withoutGlobalScopes()->where('tenant_id', $actor->tenant_id)->where('id', $clientId)->firstOrFail();
    }

    private function resolveOwner(User $actor, ?string $ownerUserId): ?User
    {
        if ($ownerUserId === null || $ownerUserId === '') {
            return null;
        }

        return User::query()->where('tenant_id', $actor->tenant_id)->where('id', $ownerUserId)->firstOrFail();
    }
}