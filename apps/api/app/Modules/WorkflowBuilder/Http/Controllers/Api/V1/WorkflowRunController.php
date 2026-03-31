<?php

declare(strict_types=1);

namespace App\Modules\WorkflowBuilder\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Modules\Shared\Support\ApiResponse;
use App\Modules\WorkflowBuilder\Http\Requests\ListWorkflowsRequest;
use App\Modules\WorkflowBuilder\Models\Workflow;
use App\Modules\WorkflowBuilder\Models\WorkflowRun;
use App\Modules\WorkflowBuilder\Services\WorkflowRunService;

final class WorkflowRunController extends Controller
{
    public function __construct(
        private readonly WorkflowRunService $runService,
    ) {
    }

    public function index(ListWorkflowsRequest $request, string $workflowId): JsonResponse
    {
        Gate::authorize('workflows.runs.read');

        $workflow = Workflow::query()->withoutGlobalScopes()->where('tenant_id', $request->user()->tenant_id)->where('id', $workflowId)->firstOrFail();

        return ApiResponse::success(
            $this->runService->listRunsForWorkflow((string) $request->user()->tenant_id, $workflow),
            (string) $request->attributes->get('correlation_id', ''),
        );
    }

    public function show(ListWorkflowsRequest $request, string $workflowId, string $runId): JsonResponse
    {
        Gate::authorize('workflows.runs.read');

        $workflow = Workflow::query()->withoutGlobalScopes()->where('tenant_id', $request->user()->tenant_id)->where('id', $workflowId)->firstOrFail();
        $run = WorkflowRun::query()->withoutGlobalScopes()->where('tenant_id', $request->user()->tenant_id)->where('workflow_id', $workflow->id)->where('id', $runId)->firstOrFail();

        return ApiResponse::success(
            $this->runService->detailForRun((string) $request->user()->tenant_id, $workflow, $run),
            (string) $request->attributes->get('correlation_id', ''),
        );
    }
}