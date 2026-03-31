<?php

declare(strict_types=1);

namespace App\Modules\WorkflowBuilder\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Modules\Shared\Contracts\QueuesTenantAware;
use App\Modules\WorkflowBuilder\Models\WorkflowRun;
use App\Modules\WorkflowBuilder\Models\WorkflowVersion;
use App\Modules\WorkflowBuilder\Services\WorkflowStepExecutor;

final readonly class ExecuteWorkflowRunStepJob implements ShouldQueue, QueuesTenantAware
{
    public function __construct(
        private string $tenantIdValue,
        private string $runId,
    ) {
    }

    public function handle(WorkflowStepExecutor $stepExecutor): void
    {
        $run = WorkflowRun::query()->withoutGlobalScopes()->where('tenant_id', $this->tenantIdValue)->where('id', $this->runId)->firstOrFail();
        $version = WorkflowVersion::query()->withoutGlobalScopes()->where('tenant_id', $this->tenantIdValue)->where('id', $run->workflow_version_id)->firstOrFail();

        if (in_array((string) $run->status, ['completed', 'failed', 'cancelled'], true)) {
            return;
        }

        $stepExecutor->execute($run, $version);
    }

    public function tenantId(): string { return $this->tenantIdValue; }
    public function correlationId(): string { return ''; }
}