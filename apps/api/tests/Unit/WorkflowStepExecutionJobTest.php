<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Modules\WorkflowBuilder\Jobs\ExecuteWorkflowRunStepJob;

final class WorkflowStepExecutionJobTest extends TestCase
{
    public function test_execute_workflow_run_step_job_carries_tenant_and_correlation_identifiers(): void
    {
        $job = new ExecuteWorkflowRunStepJob('tenant-123', 'corr-789', 'run-456');

        self::assertSame('tenant-123', $job->tenantId());
        self::assertSame('corr-789', $job->correlationId());
    }
}
