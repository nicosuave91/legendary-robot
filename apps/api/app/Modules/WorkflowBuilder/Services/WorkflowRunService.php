<?php

declare(strict_types=1);

namespace App\Modules\WorkflowBuilder\Services;

use Illuminate\Support\Str;
use App\Modules\Shared\Audit\AuditLogger;
use App\Modules\WorkflowBuilder\Jobs\StartWorkflowRunJob;
use App\Modules\WorkflowBuilder\Models\Workflow;
use App\Modules\WorkflowBuilder\Models\WorkflowRun;
use App\Modules\WorkflowBuilder\Models\WorkflowRunLog;
use App\Modules\WorkflowBuilder\Models\WorkflowVersion;

final class WorkflowRunService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly WorkflowTriggerMatcher $triggerMatcher,
        private readonly WorkflowRuntimeContextResolver $runtimeContextResolver,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function queueMatchingRuns(string $tenantId, string $correlationId, string $eventName, string $subjectType, string $subjectId, array $payload): void
    {
        $matches = $this->triggerMatcher->matchingPublishedVersions($tenantId, $eventName, $subjectType, $payload);

        foreach ($matches as $version) {
            $idempotencyKey = hash('sha256', implode(':', [$tenantId, $eventName, $subjectType, $subjectId, (string) $version->id, $correlationId]));
            dispatch(new StartWorkflowRunJob(
                $tenantId,
                $correlationId,
                (string) $version->workflow_id,
                (string) $version->id,
                $eventName,
                $subjectType,
                $subjectId,
                $payload,
                $idempotencyKey,
            ));
        }
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function startRun(string $tenantId, string $correlationId, Workflow $workflow, WorkflowVersion $version, string $eventName, string $subjectType, string $subjectId, array $payload, string $idempotencyKey): WorkflowRun
    {
        $existing = WorkflowRun::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('idempotency_key', $idempotencyKey)
            ->first();

        if ($existing !== null) {
            return $existing;
        }

        $run = new WorkflowRun([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'workflow_id' => (string) $workflow->id,
            'workflow_version_id' => (string) $version->id,
            'trigger_event' => $eventName,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'status' => 'queued',
            'current_step_index' => null,
            'idempotency_key' => $idempotencyKey,
            'correlation_id' => $correlationId,
            'trigger_payload_snapshot' => $payload,
            'runtime_context' => $payload,
            'queued_at' => now(),
        ]);

        $run->runtime_context = $this->runtimeContextResolver->resolveForRun($run, $payload);
        $run->save();

        $this->appendLog(
            $run,
            null,
            'trigger_matched',
            'A published workflow version matched the incoming domain event.',
            ['workflowVersionId' => (string) $version->id, 'eventName' => $eventName, 'subjectType' => $subjectType],
        );

        $this->auditLogger->record([
            'tenant_id' => $tenantId,
            'actor_id' => null,
            'action' => 'workflow.run.queued',
            'subject_type' => 'workflow_run',
            'subject_id' => (string) $run->id,
            'correlation_id' => $correlationId,
            'before_summary' => null,
            'after_summary' => json_encode([
                'workflowId' => $workflow->id,
                'workflowVersionId' => $version->id,
                'eventName' => $eventName,
                'subjectType' => $subjectType,
            ], JSON_THROW_ON_ERROR),
        ]);

        return $run;
    }

    /**
     * @param array<string, mixed>|null $payload
     */
    public function appendLog(WorkflowRun $run, ?int $stepIndex, string $logType, string $message, ?array $payload = null): void
    {
        WorkflowRunLog::query()->create([
            'id' => (string) Str::uuid(),
            'tenant_id' => (string) $run->tenant_id,
            'workflow_run_id' => (string) $run->id,
            'workflow_version_id' => (string) $run->workflow_version_id,
            'step_index' => $stepIndex,
            'log_type' => $logType,
            'message' => $message,
            'payload_snapshot' => $payload,
            'occurred_at' => now(),
        ]);
    }

    public function listRunsForWorkflow(string $tenantId, Workflow $workflow): array
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, WorkflowRun> $items */
        $items = WorkflowRun::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('workflow_id', $workflow->id)
            ->latest('queued_at')
            ->get();

        return [
            'items' => $items->map(fn (WorkflowRun $run): array => $this->serializeRun($run))->values()->all(),
            'meta' => ['total' => $items->count()],
        ];
    }

    public function detailForRun(string $tenantId, Workflow $workflow, WorkflowRun $run): array
    {
        abort_unless((string) $run->tenant_id === $tenantId && (string) $run->workflow_id === (string) $workflow->id, 404);

        /** @var \Illuminate\Database\Eloquent\Collection<int, WorkflowRunLog> $logs */
        $logs = $run->logs()
            ->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->oldest('occurred_at')
            ->get();

        return [
            'run' => $this->serializeRun($run),
            'logs' => $logs->map(fn (WorkflowRunLog $log): array => [
                'id' => (string) $log->id,
                'workflowRunId' => (string) $log->workflow_run_id,
                'workflowVersionId' => (string) $log->workflow_version_id,
                'stepIndex' => $log->step_index,
                'logType' => (string) $log->log_type,
                'message' => (string) $log->message,
                'payloadSnapshot' => $log->payload_snapshot ?? [],
                'occurredAt' => $log->occurred_at?->toIso8601String(),
            ])->values()->all(),
        ];
    }

    public function serializeRun(WorkflowRun $run): array
    {
        return [
            'id' => (string) $run->id,
            'workflowId' => (string) $run->workflow_id,
            'workflowVersionId' => (string) $run->workflow_version_id,
            'triggerEvent' => (string) $run->trigger_event,
            'subjectType' => (string) $run->subject_type,
            'subjectId' => (string) $run->subject_id,
            'status' => (string) $run->status,
            'currentStepIndex' => $run->current_step_index,
            'correlationId' => $run->correlation_id,
            'queuedAt' => $run->queued_at?->toIso8601String(),
            'startedAt' => $run->started_at?->toIso8601String(),
            'completedAt' => $run->completed_at?->toIso8601String(),
            'failedAt' => $run->failed_at?->toIso8601String(),
            'failureSummary' => $run->failure_summary ?? [],
        ];
    }
}
