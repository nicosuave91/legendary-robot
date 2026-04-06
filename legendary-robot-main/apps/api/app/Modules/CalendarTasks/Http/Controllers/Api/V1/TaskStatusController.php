<?php

declare(strict_types=1);

namespace App\Modules\CalendarTasks\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Modules\CalendarTasks\Http\Requests\UpdateTaskStatusRequest;
use App\Modules\CalendarTasks\Models\EventTask;
use App\Modules\CalendarTasks\Services\TaskStatusTransitionService;
use App\Modules\Shared\Support\ApiResponse;

final class TaskStatusController extends Controller
{
    public function __construct(private readonly TaskStatusTransitionService $taskStatusTransitionService) {}

    public function update(UpdateTaskStatusRequest $request, string $taskId): JsonResponse
    {
        $task = EventTask::query()->withoutGlobalScopes()->where('tenant_id', $request->user()->tenant_id)->where('id', $taskId)->firstOrFail();
        Gate::authorize('calendar.tasks.update', $task);

        return ApiResponse::success($this->taskStatusTransitionService->transition($request->user(), $task, $request->validated(), (string) $request->attributes->get('correlation_id', '')), (string) $request->attributes->get('correlation_id', ''));
    }
}
