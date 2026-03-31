<?php

declare(strict_types=1);

namespace App\Modules\Applications\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use App\Modules\Applications\Events\ApplicationStatusTransitioned;
use App\Modules\Applications\Models\Application;
use App\Modules\Applications\Models\ApplicationRuleApplication;
use App\Modules\Applications\Models\ApplicationStatusHistory;
use App\Modules\Clients\Models\Client;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Shared\Audit\AuditLogger;

final class ApplicationStatusTransitionService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly ApplicationRuleEvaluator $applicationRuleEvaluator,
    ) {
    }

    public function availableTransitions(string $currentStatus): array
    {
        $map = [
            'draft' => ['submitted', 'withdrawn'],
            'submitted' => ['in_review', 'withdrawn'],
            'in_review' => ['approved', 'declined', 'withdrawn'],
            'approved' => [],
            'declined' => [],
            'withdrawn' => [],
        ];

        return collect($map[$currentStatus] ?? [])->map(fn (string $code): array => [
            'code' => $code,
            'label' => str($code)->headline()->toString(),
            'tone' => $this->toneForStatus($code),
        ])->values()->all();
    }

    public function toneForStatus(string $status): string
    {
        return match ($status) {
            'approved' => 'success',
            'declined', 'withdrawn' => 'warning',
            'submitted', 'in_review' => 'info',
            default => 'neutral',
        };
    }

    public function transition(User $actor, Client $client, Application $application, array $payload, string $correlationId): array
    {
        $targetStatus = (string) ($payload['targetStatus'] ?? '');
        $currentStatus = (string) $application->status;
        $availableCodes = collect($this->availableTransitions($currentStatus))->pluck('code')->all();

        if (!in_array($targetStatus, $availableCodes, true)) {
            return [
                'statusCode' => 422,
                'payload' => [
                    'result' => 'blocked',
                    'blockingIssues' => [[
                        'code' => 'invalid_application_status_transition',
                        'message' => 'The requested application status change is not allowed from the current state.',
                        'severity' => 'blocking',
                    ]],
                    'warnings' => [],
                ],
            ];
        }

        $effectiveSubmittedAt = $payload['submittedAt']
            ?? $application->submitted_at
            ?? ($targetStatus === 'submitted' ? now() : null);

        $workingCopy = $application->replicate();
        $workingCopy->submitted_at = $effectiveSubmittedAt;
        $workingCopy->status = $currentStatus;

        $ruleResults = $this->applicationRuleEvaluator->evaluate(
            $workingCopy,
            'application.status_transition.requested',
            $actor,
            ['targetStatus' => $targetStatus, 'correlationId' => $correlationId]
        );

        foreach ($ruleResults as $ruleResult) {
            ApplicationRuleApplication::query()->create([
                'id' => (string) Str::uuid(),
                'tenant_id' => (string) $actor->tenant_id,
                'application_id' => (string) $application->id,
                'rule_id' => $ruleResult['ruleId'] ?? null,
                'rule_version_id' => $ruleResult['ruleVersionId'] ?? null,
                'rule_key' => $ruleResult['ruleKey'],
                'rule_version' => $ruleResult['ruleVersion'],
                'rule_name_snapshot' => $ruleResult['ruleName'] ?? null,
                'trigger_event' => $ruleResult['triggerEvent'],
                'outcome' => $ruleResult['outcome'],
                'title' => $ruleResult['title'],
                'note_body' => $ruleResult['noteBody'],
                'is_blocking' => $ruleResult['isBlocking'],
                'evidence' => $ruleResult['evidence'],
                'applied_at' => now(),
            ]);
        }

        $blockingIssues = collect($ruleResults)->filter(fn (array $result): bool => (bool) $result['isBlocking'])->map(fn (array $result): array => [
            'code' => (string) $result['ruleKey'],
            'message' => (string) $result['noteBody'],
            'severity' => 'blocking',
        ])->values()->all();

        $warnings = collect($ruleResults)->filter(fn (array $result): bool => (string) $result['outcome'] === 'warning')->map(fn (array $result): array => [
            'code' => (string) $result['ruleKey'],
            'message' => (string) $result['noteBody'],
            'severity' => 'warning',
        ])->values()->all();

        if ($blockingIssues !== []) {
            $this->auditLogger->record([
                'tenant_id' => (string) $actor->tenant_id,
                'actor_id' => (string) $actor->id,
                'action' => 'application.rule.blocked_transition',
                'subject_type' => 'application',
                'subject_id' => (string) $application->id,
                'correlation_id' => $correlationId,
                'before_summary' => json_encode(['status' => $currentStatus], JSON_THROW_ON_ERROR),
                'after_summary' => json_encode(['blockedTargetStatus' => $targetStatus, 'blockingIssues' => $blockingIssues], JSON_THROW_ON_ERROR),
            ]);

            return [
                'statusCode' => 422,
                'payload' => [
                    'result' => 'blocked',
                    'blockingIssues' => $blockingIssues,
                    'warnings' => $warnings,
                ],
            ];
        }

        DB::transaction(function () use ($actor, $application, $effectiveSubmittedAt, $payload, $targetStatus, $currentStatus): void {
            $application->forceFill([
                'status' => $targetStatus,
                'submitted_at' => $effectiveSubmittedAt,
                'updated_by' => (string) $actor->id,
            ])->save();

            ApplicationStatusHistory::query()->create([
                'id' => (string) Str::uuid(),
                'tenant_id' => (string) $application->tenant_id,
                'application_id' => (string) $application->id,
                'actor_user_id' => (string) $actor->id,
                'from_status' => $currentStatus,
                'to_status' => $targetStatus,
                'reason' => $payload['reason'] ?? null,
                'occurred_at' => now(),
                'metadata' => null,
            ]);
        });

        $this->auditLogger->record([
            'tenant_id' => (string) $actor->tenant_id,
            'actor_id' => (string) $actor->id,
            'action' => 'application.status.transitioned',
            'subject_type' => 'application',
            'subject_id' => (string) $application->id,
            'correlation_id' => $correlationId,
            'before_summary' => json_encode(['status' => $currentStatus], JSON_THROW_ON_ERROR),
            'after_summary' => json_encode(['status' => $targetStatus, 'warnings' => $warnings], JSON_THROW_ON_ERROR),
        ]);

        Event::dispatch(new ApplicationStatusTransitioned(
            tenantId: (string) $actor->tenant_id,
            correlationId: $correlationId,
            applicationId: (string) $application->id,
            payload: [
                'applicationId' => (string) $application->id,
                'clientId' => (string) $client->id,
                'productType' => (string) $application->product_type,
                'amountRequested' => $application->amount_requested !== null ? (float) $application->amount_requested : null,
                'currentStatus' => $targetStatus,
                'targetStatus' => $targetStatus,
            ],
        ));

        return [
            'statusCode' => 200,
            'payload' => [
                'result' => 'transitioned',
                'blockingIssues' => [],
                'warnings' => $warnings,
            ],
        ];
    }
}
