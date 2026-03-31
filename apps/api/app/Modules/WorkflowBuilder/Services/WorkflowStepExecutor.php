<?php

declare(strict_types=1);

namespace App\Modules\WorkflowBuilder\Services;

use App\Modules\WorkflowBuilder\Jobs\ExecuteWorkflowRunStepJob;
use App\Modules\WorkflowBuilder\Models\WorkflowRun;
use App\Modules\WorkflowBuilder\Models\WorkflowVersion;

final class WorkflowStepExecutor
{
    use WorkflowSupport;

    public function __construct(
        private readonly WorkflowRunService $runService,
    ) {
    }

    public function execute(WorkflowRun $run, WorkflowVersion $version): void
    {
        $steps = $version->steps_definition ?? [];
        $stepIndex = (int) ($run->current_step_index ?? 0);

        if (!isset($steps[$stepIndex]) || !is_array($steps[$stepIndex])) {
            $run->forceFill(['status' => 'completed', 'completed_at' => now()])->save();
            $this->runService->appendLog($run, null, 'run_completed', 'Workflow run completed with durable version-bound evidence.');

            return;
        }

        $step = $steps[$stepIndex];
        $type = (string) ($step['type'] ?? 'unknown');
        $definition = is_array($step['definition'] ?? null) ? $step['definition'] : [];
        $context = (array) ($run->runtime_context ?? []);

        $run->forceFill(['status' => 'running', 'started_at' => $run->started_at ?? now()])->save();
        $this->runService->appendLog($run, $stepIndex, 'step_started', 'Executing workflow step.', ['type' => $type]);

        if ($type === 'condition') {
            $matched = $this->evaluateCondition($context, $definition);
            $this->runService->appendLog($run, $stepIndex, 'condition_evaluated', $matched ? 'Condition matched.' : 'Condition did not match.', ['matched' => $matched]);
            $run->forceFill(['current_step_index' => $stepIndex + 1])->save();
            dispatch(new ExecuteWorkflowRunStepJob((string) $run->tenant_id, (string) $run->id));

            return;
        }

        if ($type === 'wait') {
            $run->forceFill(['status' => 'waiting', 'current_step_index' => $stepIndex + 1])->save();
            $this->runService->appendLog($run, $stepIndex, 'wait_scheduled', 'Wait step recorded. The follow-up execution remains queue-driven.', ['definition' => $definition]);
            dispatch(new ExecuteWorkflowRunStepJob((string) $run->tenant_id, (string) $run->id));

            return;
        }

        if (in_array($type, ['send_sms', 'send_email', 'create_client_note'], true)) {
            $logType = $type === 'create_client_note' ? 'note_created' : 'communication_queued';
            $this->runService->appendLog($run, $stepIndex, $logType, 'Step recorded for later module-specific execution.', ['type' => $type, 'definition' => $definition]);
            $run->forceFill(['current_step_index' => $stepIndex + 1, 'status' => 'running'])->save();
            dispatch(new ExecuteWorkflowRunStepJob((string) $run->tenant_id, (string) $run->id));

            return;
        }

        $run->forceFill([
            'status' => 'failed',
            'failed_at' => now(),
            'failure_summary' => ['message' => sprintf('Unsupported workflow step type [%s].', $type)],
        ])->save();

        $this->runService->appendLog($run, $stepIndex, 'step_failed', 'Workflow step type is not supported by the Sprint 8 baseline executor.', ['type' => $type]);
    }
}