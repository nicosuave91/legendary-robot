<?php

declare(strict_types=1);

namespace App\Modules\CalendarTasks\Services;

use Carbon\CarbonImmutable;
use App\Modules\CalendarTasks\Models\CalendarEvent;
use App\Modules\CalendarTasks\Models\EventTask;
use App\Modules\Clients\Models\Client;
use App\Modules\IdentityAccess\Models\User;

final class EventQueryService
{
    public function listForActor(User $actor, array $filters): array
    {
        $start = CarbonImmutable::parse((string) $filters['startDate'])->startOfDay();
        $end = CarbonImmutable::parse((string) $filters['endDate'])->endOfDay();

        $query = CalendarEvent::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $actor->tenant_id)
            ->where('starts_at', '>=', $start)
            ->where('starts_at', '<=', $end)
            ->with(['client', 'owner', 'tasks']);

        if (!empty($filters['clientId'])) {
            $query->where('client_id', (string) $filters['clientId']);
        }
        if (!empty($filters['ownerUserId'])) {
            $query->where('owner_user_id', (string) $filters['ownerUserId']);
        }

        return [
            'items' => $query->orderBy('starts_at')->get()->map(fn (CalendarEvent $event): array => $this->summary($event))->values()->all(),
            'range' => ['startDate' => $start->toDateString(), 'endDate' => $end->toDateString()],
        ];
    }

    public function detailForActor(User $actor, CalendarEvent $event): array
    {
        $event = CalendarEvent::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $actor->tenant_id)
            ->where('id', $event->id)
            ->with(['client', 'owner', 'tasks.assignedUser', 'tasks.history.actor'])
            ->firstOrFail();

        $tasks = $event->tasks->sortBy('sort_order')->values();

        return [
            'id' => (string) $event->id,
            'title' => (string) $event->title,
            'description' => $event->description,
            'eventType' => (string) $event->event_type,
            'status' => (string) $event->status,
            'startsAt' => $event->starts_at?->toIso8601String(),
            'endsAt' => $event->ends_at?->toIso8601String(),
            'isAllDay' => (bool) $event->is_all_day,
            'location' => $event->location,
            'client' => $event->client ? ['id' => (string) $event->client->id, 'displayName' => (string) $event->client->display_name] : null,
            'owner' => $event->owner ? ['id' => (string) $event->owner->id, 'displayName' => (string) $event->owner->name] : null,
            'taskSummary' => $this->taskSummary($event->tasks),
            'tasks' => $tasks->map(fn (EventTask $task): array => [
                'id' => (string) $task->id,
                'title' => (string) $task->title,
                'description' => $task->description,
                'status' => (string) $task->status,
                'isRequired' => (bool) $task->is_required,
                'sortOrder' => (int) $task->sort_order,
                'dueAt' => $task->due_at?->toIso8601String(),
                'completedAt' => $task->completed_at?->toIso8601String(),
                'blockedReason' => $task->blocked_reason,
                'assignedUser' => $task->assignedUser ? ['id' => (string) $task->assignedUser->id, 'displayName' => (string) $task->assignedUser->name] : null,
                'availableActions' => $this->availableTaskActions((string) $task->status),
                'history' => $task->history->sortByDesc('occurred_at')->values()->map(fn ($history): array => [
                    'id' => (string) $history->id,
                    'fromStatus' => $history->from_status,
                    'toStatus' => (string) $history->to_status,
                    'reason' => $history->reason,
                    'occurredAt' => $history->occurred_at?->toIso8601String(),
                    'actorDisplayName' => (string) ($history->actor?->name ?? 'System'),
                ])->all(),
            ])->all(),
        ];
    }

    public function listForClient(User $actor, Client $client, array $filters = []): array
    {
        $query = CalendarEvent::query()->withoutGlobalScopes()->where('tenant_id', $actor->tenant_id)->where('client_id', $client->id)->with(['client', 'owner', 'tasks']);
        if (!empty($filters['startDate'])) {
            $query->where('starts_at', '>=', CarbonImmutable::parse((string) $filters['startDate'])->startOfDay());
        }
        if (!empty($filters['endDate'])) {
            $query->where('starts_at', '<=', CarbonImmutable::parse((string) $filters['endDate'])->endOfDay());
        }

        return ['items' => $query->orderBy('starts_at')->get()->map(fn (CalendarEvent $event): array => $this->summary($event))->values()->all()];
    }

    public function summary(CalendarEvent $event): array
    {
        $event->loadMissing(['client', 'owner', 'tasks']);

        return [
            'id' => (string) $event->id,
            'title' => (string) $event->title,
            'description' => $event->description,
            'eventType' => (string) $event->event_type,
            'status' => (string) $event->status,
            'startsAt' => $event->starts_at?->toIso8601String(),
            'endsAt' => $event->ends_at?->toIso8601String(),
            'isAllDay' => (bool) $event->is_all_day,
            'location' => $event->location,
            'client' => $event->client ? ['id' => (string) $event->client->id, 'displayName' => (string) $event->client->display_name] : null,
            'owner' => $event->owner ? ['id' => (string) $event->owner->id, 'displayName' => (string) $event->owner->name] : null,
            'taskSummary' => $this->taskSummary($event->tasks),
        ];
    }

    public function taskSummary($tasks): array
    {
        $collection = collect($tasks);
        return [
            'total' => $collection->count(),
            'open' => $collection->where('status', 'open')->count(),
            'completed' => $collection->where('status', 'completed')->count(),
            'blocked' => $collection->where('status', 'blocked')->count(),
            'skipped' => $collection->where('status', 'skipped')->count(),
        ];
    }

    public function availableTaskActions(string $status): array
    {
        return match ($status) {
            'open' => ['completed', 'skipped', 'blocked'],
            'blocked' => ['open', 'completed', 'skipped'],
            'skipped' => ['open', 'completed'],
            'completed' => ['open'],
            default => [],
        };
    }
}
