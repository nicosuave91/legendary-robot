<?php

declare(strict_types=1);

namespace App\Modules\WorkflowBuilder\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Modules\Shared\Contracts\QueuesTenantAware;
use App\Modules\WorkflowBuilder\Models\Workflow;
use App\Modules\WorkflowBuilder\Models\WorkflowVersion;
use App\Modules\WorkflowBuilder\Services\WorkflowRunService;

final readonly class StartWorkflowRunJob implements ShouldQueue, QueuesTenantAware
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        private string $tenantIdValue,
        private string $correlationIdValue,
        private string $workflowId,
        private string $workflowVersionId,
        private string $eventName,
        private string $subjectType,
        private string $subjectId,
        private array $payload,
        private string $idempotencyKey,
    ) {
    }

    public function handle(WorkflowRunService $runService): void
    {
        $workflow = Workflow::query()->withoutGlobalScopes()->where('tenant_id', $this->tenantIdValue)->where('id', $this->workflowId)->firstOrFail();
        $version = WorkflowVersion::query()->withoutGlobalScopes()->where('tenant_id', $this->tenantIdValue)->where('id', $this->workflowVersionId)->firstOrFail();

        $run = $runService->startRun($this->tenantIdValue, $this->correlationIdValue, $workflow, $version, $this->eventName, $this->subjectType, $this->subjectId, $this->payload, $this->idempotencyKey);
        dispatch(new ExecuteWorkflowRunStepJob($this->tenantIdValue, $this->correlationIdValue, (string) $run->id));
    }

    public function tenantId(): string { return $this->tenantIdValue; }
    public function correlationId(): string { return $this->correlationIdValue; }
}