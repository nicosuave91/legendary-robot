<?php

declare(strict_types=1);

namespace App\Modules\CalendarTasks\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Modules\CalendarTasks\Http\Requests\GetCalendarDayRequest;
use App\Modules\CalendarTasks\Services\CalendarDayQueryService;
use App\Modules\Shared\Support\ApiResponse;

final class CalendarDayController extends Controller
{
    public function __construct(private readonly CalendarDayQueryService $calendarDayQueryService) {}

    public function show(GetCalendarDayRequest $request): JsonResponse
    {
        Gate::authorize('calendar.read');

        return ApiResponse::success(
            $this->calendarDayQueryService->forActor($request->user(), (string) $request->validated()['date']),
            (string) $request->attributes->get('correlation_id', ''),
        );
    }
}
