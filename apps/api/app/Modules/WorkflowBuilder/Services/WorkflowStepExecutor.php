<?php

declare(strict_types=1);

namespace App\Modules\WorkflowBuilder\Services;

use Throwable;
use Illuminate\Support\Str;
use App\Modules\Applications\Models\Application;
use App\Modules\Clients\Models\Client;
use App\Modules\Clients\Services\ClientNoteService;
use App\Modules\Communications\Services\CommunicationCommandService;
use App\Modules\WorkflowBuilder\Jobs\ExecuteWorkflowRunStepJob;
use App\Modules\WorkflowBuilder\Models\WorkflowRun;
use App\Modules\WorkflowBuilder\Models\WorkflowVersion;

final class WorkflowStepExecutor
{
    use WorkflowSupport;

    public function __construct(
        private readonly WorkflowRunService $runService,
        private readonly WorkflowExecutionActorResolver $actorResolver,
        private readonly ClientNoteService $clientNoteService,
        private readonly CommunicationCommandService $communicationCommandService,
        private readonly WorkflowRuntimeContextResolver $runtimeContextResolver,
    ) {
    }

    public function execute(WorkflowRun $run, WorkflowVersion $version): void
    {
        $steps = $version->steps_definition ?? [];
        $stepIndex = (int) ($run->current_step_index ?? 0);

        if (!isset($steps[$stepIndex]) || !is_array($steps[$stepIndex])) {
            $this->completeRun($run, null, 'Workflow run completed with durable version-bound evidence.');

            return;
        }

        $step = $steps[$stepIndex];
        $type = (string) ($step['type'] ?? 'unknown');
        $definition = is_array($step['definition'] ?? null) ? $step['definition'] : [];
        $context = $this->hydrateRuntimeContext($run);

        $run->forceFill(['status' => 'running', 'started_at' => $run->started_at ?? now()])->save();
        $this->runService->appendLog($run, $stepIndex, 'step_started', 'Executing workflow step.', ['type' => $type]);

        try {
            if ($type === 'condition') {
                $matched = $this->evaluateCondition($context, $definition);

                if (!$matched) {
                    $this->runService->appendLog(
                        $run,
                        $stepIndex,
                        'condition_not_matched',
                        'Condition did not match. Remaining workflow steps were skipped.',
                        ['matched' => false]
                    );
                    $this->completeRun($run, $stepIndex, 'Workflow run completed because a condition step did not match.');

                    return;
                }

                $this->runService->appendLog($run, $stepIndex, 'condition_matched', 'Condition matched.', ['matched' => true]);
                $this->continueRun($run, $stepIndex + 1);

                return;
            }

            if ($type === 'wait') {
                $this->scheduleWait($run, $stepIndex, $definition);

                return;
            }

            if ($type === 'create_client_note') {
                $this->executeCreateClientNote($run, $stepIndex, $definition, $context);

                return;
            }

            if ($type === 'send_sms') {
                $this->executeSendSms($run, $stepIndex, $definition, $context);

                return;
            }

            if ($type === 'send_email') {
                $this->executeSendEmail($run, $stepIndex, $definition, $context);

                return;
            }

            $this->failRun($run, $stepIndex, sprintf('Unsupported workflow step type [%s].', $type), ['type' => $type]);
        } catch (Throwable $exception) {
            $this->failRun(
                $run,
                $stepIndex,
                $exception->getMessage() !== '' ? $exception->getMessage() : 'Workflow step execution failed.',
                ['type' => $type, 'exception' => $exception::class],
            );
        }
    }

    private function executeCreateClientNote(WorkflowRun $run, int $stepIndex, array $definition, array $context): void
    {
        $actor = $this->actorResolver->resolveForRun($run);
        $client = $this->resolveClientForRun($run);
        $body = $this->renderTemplate((string) ($definition['bodyTemplate'] ?? $definition['body'] ?? ''), $context, $run);

        if (trim($body) === '') {
            throw new \RuntimeException('Workflow client-note steps require a non-empty bodyTemplate or body.');
        }

        $note = $this->clientNoteService->create(
            $actor,
            $client,
            ['body' => $body],
            $this->correlationIdForRun($run),
        );

        $this->runService->appendLog($run, $stepIndex, 'note_created', 'Workflow created a governed client note.', [
            'clientId' => (string) $client->id,
            'noteId' => $note['id'] ?? null,
        ]);

        $this->continueRun($run, $stepIndex + 1);
    }

    private function executeSendSms(WorkflowRun $run, int $stepIndex, array $definition, array $context): void
    {
        $actor = $this->actorResolver->resolveForRun($run);
        $client = $this->resolveClientForRun($run);
        $body = $this->renderTemplate((string) ($definition['bodyTemplate'] ?? $definition['body'] ?? ''), $context, $run);

        if (trim($body) === '') {
            throw new \RuntimeException('Workflow SMS steps require a non-empty bodyTemplate or body.');
        }

        $result = $this->communicationCommandService->queueSms(
            $actor,
            $client,
            [
                'body' => $body,
                'toPhone' => $definition['toPhone'] ?? $client->primary_phone,
                'idempotencyKey' => $this->idempotencyKey($run, $stepIndex, 'sms'),
            ],
            $this->correlationIdForRun($run),
        );

        $this->runService->appendLog($run, $stepIndex, 'communication_queued', 'Workflow queued an outbound SMS/MMS.', [
            'clientId' => (string) $client->id,
            'messageId' => $result['id'] ?? null,
            'channel' => $result['channel'] ?? null,
        ]);

        $this->continueRun($run, $stepIndex + 1);
    }

    private function executeSendEmail(WorkflowRun $run, int $stepIndex, array $definition, array $context): void
    {
        $actor = $this->actorResolver->resolveForRun($run);
        $client = $this->resolveClientForRun($run);

        $subject = $this->renderTemplate((string) ($definition['subjectTemplate'] ?? $definition['subject'] ?? ''), $context, $run);
        $bodyText = $this->renderTemplate((string) ($definition['bodyTemplate'] ?? $definition['bodyText'] ?? ''), $context, $run);

        if (trim($subject) === '') {
            throw new \RuntimeException('Workflow email steps require a non-empty subjectTemplate or subject.');
        }

        if (trim($bodyText) === '') {
            throw new \RuntimeException('Workflow email steps require a non-empty bodyTemplate or bodyText.');
        }

        $to = array_values(array_filter(array_map(
            static fn (mixed $value): string => trim((string) $value),
            is_array($definition['to'] ?? null) ? $definition['to'] : [$definition['to'] ?? $client->primary_email],
        )));

        if ($to === []) {
            throw new \RuntimeException('Workflow email steps require a recipient or a client primary email address.');
        }

        $result = $this->communicationCommandService->queueEmail(
            $actor,
            $client,
            [
                'to' => $to,
                'subject' => $subject,
                'bodyText' => $bodyText,
                'idempotencyKey' => $this->idempotencyKey($run, $stepIndex, 'email'),
            ],
            $this->correlationIdForRun($run),
        );

        $this->runService->appendLog($run, $stepIndex, 'communication_queued', 'Workflow queued an outbound email.', [
            'clientId' => (string) $client->id,
            'messageId' => $result['id'] ?? null,
            'channel' => $result['channel'] ?? null,
        ]);

        $this->continueRun($run, $stepIndex + 1);
    }

    private function scheduleWait(WorkflowRun $run, int $stepIndex, array $definition): void
    {
        $minutes = max(1, (int) ($definition['durationMinutes'] ?? 0));
        $resumeAt = now()->addMinutes($minutes);

        $run->forceFill([
            'status' => 'waiting',
            'current_step_index' => $stepIndex + 1,
        ])->save();

        $this->runService->appendLog($run, $stepIndex, 'wait_scheduled', 'Wait step scheduled a delayed continuation.', [
            'durationMinutes' => $minutes,
            'resumeAt' => $resumeAt->toIso8601String(),
        ]);

        dispatch(
            (new ExecuteWorkflowRunStepJob((string) $run->tenant_id, $this->correlationIdForRun($run), (string) $run->id))
                ->delay($resumeAt)
        );
    }

    private function resolveClientForRun(WorkflowRun $run): Client
    {
        if ((string) $run->subject_type === 'client') {
            return Client::query()
                ->withoutGlobalScopes()
                ->where('tenant_id', (string) $run->tenant_id)
                ->where('id', (string) $run->subject_id)
                ->firstOrFail();
        }

        if ((string) $run->subject_type === 'application') {
            /** @var Application $application */
            $application = Application::query()
                ->withoutGlobalScopes()
                ->where('tenant_id', (string) $run->tenant_id)
                ->where('id', (string) $run->subject_id)
                ->firstOrFail();

            return Client::query()
                ->withoutGlobalScopes()
                ->where('tenant_id', (string) $run->tenant_id)
                ->where('id', (string) $application->client_id)
                ->firstOrFail();
        }

        throw new \RuntimeException(sprintf('Workflow runs for subject type [%s] cannot resolve a client target.', (string) $run->subject_type));
    }

    /**
     * @return array<string, mixed>
     */
    private function hydrateRuntimeContext(WorkflowRun $run): array
    {
        $context = (array) ($run->runtime_context ?? []);
        $hydrated = $this->runtimeContextResolver->resolveForRun($run, $context);

        if ($hydrated !== $context) {
            $run->forceFill(['runtime_context' => $hydrated])->save();
        }

        return $hydrated;
    }

    private function renderTemplate(string $template, array $context, WorkflowRun $run): string
    {
        $rendered = $template;
        $replacements = [
            'workflowRunId' => (string) $run->id,
            'workflowVersionId' => (string) $run->workflow_version_id,
            'subjectType' => (string) $run->subject_type,
            'subjectId' => (string) $run->subject_id,
            'triggerEvent' => (string) $run->trigger_event,
            'correlationId' => $this->correlationIdForRun($run),
        ] + $context;

        foreach ($replacements as $key => $value) {
            if (is_scalar($value) || $value === null) {
                $rendered = str_replace('{{' . $key . '}}', (string) ($value ?? ''), $rendered);
            }
        }

        return trim($rendered);
    }

    private function continueRun(WorkflowRun $run, int $nextStepIndex): void
    {
        $run->forceFill(['current_step_index' => $nextStepIndex, 'status' => 'running'])->save();
        dispatch(new ExecuteWorkflowRunStepJob((string) $run->tenant_id, $this->correlationIdForRun($run), (string) $run->id));
    }

    private function completeRun(WorkflowRun $run, ?int $stepIndex, string $message): void
    {
        $run->forceFill([
            'status' => 'completed',
            'completed_at' => now(),
            'current_step_index' => $stepIndex,
        ])->save();

        $this->runService->appendLog($run, $stepIndex, 'run_completed', $message);
    }

    private function failRun(WorkflowRun $run, int $stepIndex, string $message, array $payload = []): void
    {
        $run->forceFill([
            'status' => 'failed',
            'failed_at' => now(),
            'failure_summary' => ['message' => $message],
        ])->save();

        $this->runService->appendLog($run, $stepIndex, 'step_failed', $message, $payload);
    }

    private function correlationIdForRun(WorkflowRun $run): string
    {
        return (string) ($run->correlation_id ?: ('workflow-' . Str::uuid()));
    }

    private function idempotencyKey(WorkflowRun $run, int $stepIndex, string $channel): string
    {
        return hash('sha256', implode(':', [
            (string) $run->tenant_id,
            (string) $run->id,
            (string) $stepIndex,
            $channel,
        ]));
    }
}
