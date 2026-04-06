<?php

declare(strict_types=1);

namespace App\Modules\WorkflowBuilder\Listeners;

use App\Modules\Applications\Events\ApplicationCreated;
use App\Modules\Applications\Events\ApplicationStatusTransitioned;
use App\Modules\Disposition\Events\ClientDispositionTransitioned;
use App\Modules\WorkflowBuilder\Services\WorkflowRunService;

final class QueueMatchingWorkflowRuns
{
    public function __construct(
        private readonly WorkflowRunService $runService,
    ) {
    }

    public function handle(object $event): void
    {
        if ($event instanceof ApplicationCreated) {
            $this->runService->queueMatchingRuns($event->tenantId, $event->correlationId, 'application.created', 'application', $event->applicationId, $event->payload);

            return;
        }

        if ($event instanceof ApplicationStatusTransitioned) {
            $this->runService->queueMatchingRuns($event->tenantId, $event->correlationId, 'application.status_transitioned', 'application', $event->applicationId, $event->payload);

            return;
        }

        if ($event instanceof ClientDispositionTransitioned) {
            $this->runService->queueMatchingRuns($event->tenantId, $event->correlationId, 'client.disposition.transitioned', 'client', $event->clientId, $event->payload);
        }
    }
}