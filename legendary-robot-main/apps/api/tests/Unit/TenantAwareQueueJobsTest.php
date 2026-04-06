<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Modules\Communications\Jobs\InitiateOutboundCallJob;
use App\Modules\Communications\Jobs\SubmitOutboundEmailJob;
use App\Modules\Communications\Jobs\SubmitOutboundSmsJob;
use App\Modules\Imports\Jobs\CommitImportJob;
use App\Modules\Imports\Jobs\ValidateImportJob;
use App\Modules\WorkflowBuilder\Jobs\StartWorkflowRunJob;

final class TenantAwareQueueJobsTest extends TestCase
{
    public function test_release_critical_queue_jobs_expose_non_empty_tenant_and_correlation_identifiers(): void
    {
        $jobs = [
            new ValidateImportJob('tenant-a', 'corr-a', 'import-a'),
            new CommitImportJob('tenant-b', 'corr-b', 'import-b'),
            new SubmitOutboundSmsJob('tenant-c', 'corr-c', 'message-c'),
            new SubmitOutboundEmailJob('tenant-d', 'corr-d', 'message-d'),
            new InitiateOutboundCallJob('tenant-e', 'corr-e', 'call-e'),
            new StartWorkflowRunJob('tenant-f', 'corr-f', 'workflow-f', 'version-f', 'application.created', 'application', 'subject-f', ['foo' => 'bar'], 'idem-f'),
        ];

        foreach ($jobs as $job) {
            self::assertNotSame('', $job->tenantId());
            self::assertNotSame('', $job->correlationId());
        }
    }
}
