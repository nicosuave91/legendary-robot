<?php

declare(strict_types=1);

namespace App\Modules\CalendarTasks\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Modules\CalendarTasks\Http\Requests\ListEventsRequest;
use App\Modules\CalendarTasks\Http\Requests\StoreEventRequest;
use App\Modules\CalendarTasks\Http\Requests\UpdateEventRequest;
use App\Modules\CalendarTasks\Models\CalendarEvent;
use App\Modules\CalendarTasks\Services\EventQueryService;
use App\Modules\CalendarTasks\Services\EventService;
use App\Modules\Shared\Support\ApiResponse;

final class EventController extends Controller
{
    public function __construct(private readonly EventQueryService $eventQueryService, private readonly EventService $eventService) {}

    public function index(ListEventsRequest $request): JsonResponse
    {
        Gate::authorize('calendar.read');

        return ApiResponse::success($this->eventQueryService->listForActor($request->user(), $request->validated()), (string) $request->attributes->get('correlation_id', ''));
    }

    public function store(StoreEventRequest $request): JsonResponse
    {
        Gate::authorize('calendar.create');

        return ApiResponse::success($this->eventService->create($request->user(), $request->validated(), (string) $request->attributes->get('correlation_id', '')), (string) $request->attributes->get('correlation_id', ''), 201);
    }

    public function show(Request $request, string $eventId): JsonResponse
    {
        Gate::authorize('calendar.read');
        $event = CalendarEvent::query()->withoutGlobalScopes()->where('tenant_id', $request->user()->tenant_id)->where('id', $eventId)->firstOrFail();

        return ApiResponse::success($this->eventQueryService->detailForActor($request->user(), $event), (string) $request->attributes->get('correlation_id', ''));
    }

    public function update(UpdateEventRequest $request, string $eventId): JsonResponse
    {
        $event = CalendarEvent::query()->withoutGlobalScopes()->where('tenant_id', $request->user()->tenant_id)->where('id', $eventId)->firstOrFail();
        Gate::authorize('calendar.update', $event);

        return ApiResponse::success($this->eventService->update($request->user(), $event, $request->validated(), (string) $request->attributes->get('correlation_id', '')), (string) $request->attributes->get('correlation_id', ''));
    }
}
