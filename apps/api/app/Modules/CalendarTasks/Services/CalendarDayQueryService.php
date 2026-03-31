<?php

declare(strict_types=1);

namespace App\Modules\CalendarTasks\Services;

use Carbon\CarbonImmutable;
use App\Modules\CalendarTasks\Models\CalendarEvent;
use App\Modules\IdentityAccess\Models\User;

final class CalendarDayQueryService
{
    public function __construct(private readonly EventQueryService $eventQueryService) {}

    public function forActor(User $actor, string $date): array
    {
        $selectedDate = CarbonImmutable::parse($date)->startOfDay();
        $events = CalendarEvent::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $actor->tenant_id)
            ->whereBetween('starts_at', [$selectedDate, $selectedDate->endOfDay()])
            ->with(['client', 'owner', 'tasks'])
            ->orderBy('starts_at')
            ->get();

        $tasks = $events->flatMap(fn (CalendarEvent $event) => $event->tasks);

        return [
            'selectedDate' => $selectedDate->toDateString(),
            'isToday' => $selectedDate->isSameDay(now()),
            'summary' => [
                'eventCount' => $events->count(),
                'openTaskCount' => $tasks->where('status', 'open')->count(),
                'completedTaskCount' => $tasks->where('status', 'completed')->count(),
                'blockedTaskCount' => $tasks->where('status', 'blocked')->count(),
                'skippedTaskCount' => $tasks->where('status', 'skipped')->count(),
            ],
            'events' => $events->map(fn (CalendarEvent $event): array => $this->eventQueryService->summary($event))->values()->all(),
        ];
    }
}
