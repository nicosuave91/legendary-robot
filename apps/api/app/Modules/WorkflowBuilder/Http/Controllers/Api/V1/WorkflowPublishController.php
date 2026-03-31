<?php

declare(strict_types=1);

namespace App\Modules\WorkflowBuilder\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Modules\Shared\Support\ApiResponse;
use App\Modules\WorkflowBuilder\Http\Requests\PublishWorkflowRequest;
use App\Modules\WorkflowBuilder\Models\Workflow;
use App\Modules\WorkflowBuilder\Services\WorkflowPublishService;

final class WorkflowPublishController extends Controller
{
    public function __construct(
        private readonly WorkflowPublishService $publishService,
    ) {
    }

    public function store(PublishWorkflowRequest $request, string $workflowId): JsonResponse
    {
        $workflow = Workflow::query()->withoutGlobalScopes()->where('tenant_id', $request->user()->tenant_id)->where('id', $workflowId)->firstOrFail();
        Gate::authorize('workflows.publish', $workflow);

        return ApiResponse::success(
            $this->publishService->publish($request->user(), $workflow, (string) $request->attributes->get('correlation_id', '')),
            (string) $request->attributes->get('correlation_id', ''),
            201,
        );
    }
}