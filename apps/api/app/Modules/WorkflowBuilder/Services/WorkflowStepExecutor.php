<?php

declare(strict_types=1);

namespace App\Modules\WorkflowBuilder\Services;

use Carbon\CarbonImmutable;
use RuntimeException;
use Throwable;
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
        private readonly CommunicationCommandService $communicationCommandService,
        private readonly ClientNoteService $clientNoteService,
    ) {
    }

    public function execute(WorkflowRun $run, WorkflowVersion $version): void
    {
        $steps = $version->steps_definition ?? [];
        $stepIndex = (int) ($run->current_step_index ?? 0);

        if (!isset($steps[$stepIndex]) || !is_array($steps[$stepIndex])) {
            $run->forceFill([
                'status' => 'completed',
                'completed_at' => now(),
            ])->save();

            $this->runService->appendLog($run, null, 'run_completed', 'Workflow run completed with durable version-bound evidence.');

            return;
        }

        $step = $steps[$stepIndex];
        $type = (string) ($step['type'] ?? 'unknown');
        $definition = is_array($step['definition'] ?? null) ? $step['definition'] : [];
        $context = (array) ($run->runtime_context ?? []);

        $run->forceFill([
            'status' => 'running',
            'started_at' => $run->started_at ?? now(),
        ])->save();

        $this->runService->appendLog($run, $stepIndex, 'step_started', 'Executing workflow step.', ['type' => $type]);

        try {
            if ($type === 'condition') {
                $matched = $this->evaluateCondition($context, $definition);

                $this->runService->appendLog(
                    $run,
                    $stepIndex,
                    'condition_evaluated',
                    $matched ? 'Condition matched.' : 'Condition did not match.',
                    ['matched' => $matched],
                );

                $this->scheduleNextStep($run, $stepIndex);

                return;
            }

            if ($type === 'wait') {
                $renderedDefinition = $this->renderDefinition($definition, $context);
                $delaySeconds = $this->resolveWaitDelaySeconds($renderedDefinition);
                $resumeAt = $delaySeconds > 0 ? now()->addSeconds($delaySeconds)->toIso8601String() : now()->toIso8601String();

                $this->runService->appendLog(
                    $run,
                    $stepIndex,
                    'wait_scheduled',
                    $delaySeconds > 0
                        ? 'Wait step scheduled for delayed queue-driven continuation.'
                        : 'Wait step resolved immediately and will continue on the queue.',
                    [
                        'delaySeconds' => $delaySeconds,
                        'resumeAt' => $resumeAt,
                        'definition' => $renderedDefinition,
                    ],
                );

                $this->scheduleNextStep($run, $stepIndex, $delaySeconds);

                return;
            }

            if (in_array($type, ['send_sms', 'send_email', 'create_client_note'], true)) {
                [$client, $application] = $this->resolveSubjectRecords($run);
                $actor = $this->actorResolver->resolve($run, $client, $application);
                $runtimeContext = $this->buildRuntimeContext($run, $client, $application, $context);
                $renderedDefinition = $this->renderDefinition($definition, $runtimeContext);

                if ($type === 'send_sms') {
                    $result = $this->communicationCommandService->queueSms(
                        $actor,
                        $client,
                        [
                            'body' => $this->nullableString($renderedDefinition['body'] ?? $renderedDefinition['bodyText'] ?? null),
                            'toPhone' => $this->nullableString($renderedDefinition['toPhone'] ?? $client->primary_phone),
                            'idempotencyKey' => $this->workflowIdempotencyKey($run, $stepIndex, $type),
                        ],
                        (string) ($run->correlation_id ?? ''),
                    );

                    $this->runService->appendLog(
                        $run,
                        $stepIndex,
                        'communication_queued',
                        'Workflow step queued an outbound SMS/MMS through the governed communications module.',
                        [
                            'type' => $type,
                            'messageId' => $result['id'] ?? null,
                            'channel' => $result['channel'] ?? null,
                            'toAddress' => $result['counterpart']['address'] ?? null,
                        ],
                    );

                    $this->scheduleNextStep($run, $stepIndex);

                    return;
                }

                if ($type === 'send_email') {
                    $to = $this->normalizeEmailList($renderedDefinition['to'] ?? null, $client->primary_email);
                    $cc = $this->normalizeEmailList($renderedDefinition['cc'] ?? null);
                    $bcc = $this->normalizeEmailList($renderedDefinition['bcc'] ?? null);

                    $result = $this->communicationCommandService->queueEmail(
                        $actor,
                        $client,
                        [
                            'to' => $to,
                            'cc' => $cc !== [] ? $cc : null,
                            'bcc' => $bcc !== [] ? $bcc : null,
                            'subject' => (string) ($renderedDefinition['subject'] ?? ('Workflow follow-up for ' . $client->display_name)),
                            'bodyText' => $this->nullableString($renderedDefinition['bodyText'] ?? $renderedDefinition['body'] ?? null),
                            'bodyHtml' => $this->nullableString($renderedDefinition['bodyHtml'] ?? null),
                            'idempotencyKey' => $this->workflowIdempotencyKey($run, $stepIndex, $type),
                        ],
                        (string) ($run->correlation_id ?? ''),
                    );

                    $this->runService->appendLog(
                        $run,
                        $stepIndex,
                        'communication_queued',
                        'Workflow step queued an outbound email through the governed communications module.',
                        [
                            'type' => $type,
                            'messageId' => $result['id'] ?? null,
                            'channel' => $result['channel'] ?? null,
                            'toAddress' => $result['counterpart']['address'] ?? null,
                        ],
                    );

                    $this->scheduleNextStep($run, $stepIndex);

                    return;
                }

                $note = $this->clientNoteService->create(
                    $actor,
                    $client,
                    [
                        'body' => (string) ($renderedDefinition['body'] ?? ('Workflow note for ' . $client->display_name)),
                    ],
                    (string) ($run->correlation_id ?? ''),
                );

                $this->runService->appendLog(
                    $run,
                    $stepIndex,
                    'note_created',
                    'Workflow step created a governed client note.',
                    [
                        'type' => $type,
                        'noteId' => $note['id'] ?? null,
                        'preview' => mb_substr((string) ($note['body'] ?? ''), 0, 140),
                    ],
                );

                $this->scheduleNextStep($run, $stepIndex);

                return;
            }

            throw new RuntimeException(sprintf('Unsupported workflow step type [%s].', $type));
        } catch (Throwable $exception) {
            $run->forceFill([
                'status' => 'failed',
                'failed_at' => now(),
                'failure_summary' => [
                    'message' => $exception->getMessage(),
                    'stepType' => $type,
                ],
            ])->save();

            $this->runService->appendLog(
                $run,
                $stepIndex,
                'step_failed',
                'Workflow step execution failed.',
                [
                    'type' => $type,
                    'message' => $exception->getMessage(),
                ],
            );
        }
    }

    /**
     * @return array{0: Client, 1: Application|null}
     */
    private function resolveSubjectRecords(WorkflowRun $run): array
    {
        if ((string) $run->subject_type === 'client') {
            /** @var Client|null $client */
            $client = Client::query()
                ->withoutGlobalScopes()
                ->where('tenant_id', (string) $run->tenant_id)
                ->where('id', (string) $run->subject_id)
                ->first();

            if ($client === null) {
                throw new RuntimeException('Workflow run client subject could not be resolved.');
            }

            return [$client, null];
        }

        if ((string) $run->subject_type === 'application') {
            /** @var Application|null $application */
            $application = Application::query()
                ->withoutGlobalScopes()
                ->with('client')
                ->where('tenant_id', (string) $run->tenant_id)
                ->where('id', (string) $run->subject_id)
                ->first();

            if ($application === null || $application->client === null) {
                throw new RuntimeException('Workflow run application subject could not be resolved.');
            }

            return [$application->client, $application];
        }

        throw new RuntimeException(sprintf('Workflow subject type [%s] is not supported for action execution.', (string) $run->subject_type));
    }

    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    private function buildRuntimeContext(WorkflowRun $run, Client $client, ?Application $application, array $context): array
    {
        return array_merge($context, [
            'workflowRunId' => (string) $run->id,
            'workflowVersionId' => (string) $run->workflow_version_id,
            'subjectType' => (string) $run->subject_type,
            'subjectId' => (string) $run->subject_id,
            'clientId' => (string) $client->id,
            'clientDisplayName' => (string) $client->display_name,
            'clientEmail' => $client->primary_email,
            'clientPhone' => $client->primary_phone,
            'clientStatus' => (string) $client->status,
            'applicationId' => $application?->id,
            'applicationNumber' => $application?->application_number,
            'applicationStatus' => $application?->status,
            'productType' => $application?->product_type ?? ($context['productType'] ?? null),
            'amountRequested' => $application?->amount_requested ?? ($context['amountRequested'] ?? null),
            'correlationId' => $run->correlation_id,
        ]);
    }

    /**
     * @param array<string, mixed> $definition
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    private function renderDefinition(array $definition, array $context): array
    {
        $render = function (mixed $value) use (&$render, $context): mixed {
            if (is_string($value)) {
                return preg_replace_callback('/\{\{\s*([A-Za-z0-9_.-]+)\s*\}\}/', static function (array $matches) use ($context): string {
                    $resolved = data_get($context, (string) ($matches[1] ?? ''));

                    if (is_scalar($resolved) || $resolved === null) {
                        return $resolved === null ? '' : (string) $resolved;
                    }

                    return json_encode($resolved, JSON_THROW_ON_ERROR);
                }, $value);
            }

            if (is_array($value)) {
                foreach ($value as $key => $item) {
                    $value[$key] = $render($item);
                }
            }

            return $value;
        };

        /** @var array<string, mixed> $rendered */
        $rendered = $render($definition);

        return $rendered;
    }

    private function workflowIdempotencyKey(WorkflowRun $run, int $stepIndex, string $type): string
    {
        return hash('sha256', implode(':', [
            (string) $run->tenant_id,
            (string) $run->id,
            (string) $run->workflow_version_id,
            (string) $stepIndex,
            $type,
        ]));
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $string = trim((string) $value);

        return $string !== '' ? $string : null;
    }

    /**
     * @return array<int, string>
     */
    private function normalizeEmailList(mixed $value, ?string $fallback = null): array
    {
        $raw = [];

        if (is_array($value)) {
            $raw = $value;
        } elseif (is_string($value) && trim($value) !== '') {
            $raw = [$value];
        }

        if ($raw === [] && $fallback !== null && trim($fallback) !== '') {
            $raw = [$fallback];
        }

        return array_values(array_filter(array_map(
            static fn (mixed $item): string => trim((string) $item),
            $raw,
        )));
    }

    private function resolveWaitDelaySeconds(array $definition): int
    {
        if (($definition['seconds'] ?? null) !== null) {
            return max(0, (int) $definition['seconds']);
        }

        if (($definition['minutes'] ?? null) !== null) {
            return max(0, (int) $definition['minutes'] * 60);
        }

        if (($definition['hours'] ?? null) !== null) {
            return max(0, (int) $definition['hours'] * 3600);
        }

        if (($definition['until'] ?? null) !== null) {
            $target = CarbonImmutable::parse((string) $definition['until']);

            return max(0, now()->diffInSeconds($target, false));
        }

        return 0;
    }

    private function scheduleNextStep(WorkflowRun $run, int $stepIndex, int $delaySeconds = 0): void
    {
        $nextStatus = $delaySeconds > 0 ? 'waiting' : 'running';

        $run->forceFill([
            'current_step_index' => $stepIndex + 1,
            'status' => $nextStatus,
        ])->save();

        $job = new ExecuteWorkflowRunStepJob((string) $run->tenant_id, (string) ($run->correlation_id ?? ''), (string) $run->id);

        if ($delaySeconds > 0) {
            dispatch($job->delay(now()->addSeconds($delaySeconds)));

            return;
        }

        dispatch($job);
    }
}
