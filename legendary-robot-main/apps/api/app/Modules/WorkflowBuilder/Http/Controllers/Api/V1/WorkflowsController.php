<?php

declare(strict_types=1);

namespace App\Modules\WorkflowBuilder\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Modules\Shared\Support\ApiResponse;
use App\Modules\WorkflowBuilder\Http\Requests\CreateWorkflowRequest;
use App\Modules\WorkflowBuilder\Http\Requests\ListWorkflowsRequest;
use App\Modules\WorkflowBuilder\Http\Requests\UpdateWorkflowDraftRequest;
use App\Modules\WorkflowBuilder\Models\Workflow;
use App\Modules\WorkflowBuilder\Services\WorkflowCatalogService;
use App\Modules\WorkflowBuilder\Services\WorkflowDraftService;

final class WorkflowsController extends Controller
{
    public function __construct(
        private readonly WorkflowCatalogService $catalogService,
        private readonly WorkflowDraftService $draftService,
    ) {
    }

    public function index(ListWorkflowsRequest $request): JsonResponse
    {
        Gate::authorize('workflows.read');

        return ApiResponse::success(
            $this->catalogService->listForUser($request->user(), $request->validated()),
            (string) $request->attributes->get('correlation_id', ''),
        );
    }

    public function store(CreateWorkflowRequest $request): JsonResponse
    {
        Gate::authorize('workflows.create');

        return ApiResponse::success(
            $this->draftService->create($request->user(), $request->validated(), (string) $request->attributes->get('correlation_id', '')),
            (string) $request->attributes->get('correlation_id', ''),
            201,
        );
    }

    public function show(ListWorkflowsRequest $request, string $workflowId): JsonResponse
    {
        Gate::authorize('workflows.read');

        $workflow = Workflow::query()->withoutGlobalScopes()->where('tenant_id', $request->user()->tenant_id)->where('id', $workflowId)->firstOrFail();

        return ApiResponse::success(
            $this->catalogService->detailForUser($request->user(), $workflow),
            (string) $request->attributes->get('correlation_id', ''),
        );
    }

    public function update(UpdateWorkflowDraftRequest $request, string $workflowId): JsonResponse
    {
        $workflow = Workflow::query()->withoutGlobalScopes()->where('tenant_id', $request->user()->tenant_id)->where('id', $workflowId)->firstOrFail();
        Gate::authorize('workflows.update-draft', $workflow);

        return ApiResponse::success(
            $this->draftService->update($request->user(), $workflow, $request->validated(), (string) $request->attributes->get('correlation_id', '')),
            (string) $request->attributes->get('correlation_id', ''),
        );
    }
}