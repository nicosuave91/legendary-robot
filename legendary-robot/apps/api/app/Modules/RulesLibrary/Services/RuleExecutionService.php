<?php

declare(strict_types=1);

namespace App\Modules\RulesLibrary\Services;

use Illuminate\Support\Str;
use App\Modules\Applications\Models\Application;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\RulesLibrary\Models\RuleExecutionLog;
use App\Modules\RulesLibrary\Models\RuleVersion;

final class RuleExecutionService
{
    use RuleSupport;

    /**
     * @param iterable<int, RuleVersion> $versions
     * @param array<string, mixed> $context
     * @return array<int, array<string, mixed>>
     */
    public function evaluateApplicationRules(string $correlationId, Application $application, iterable $versions, string $triggerEvent, ?User $actor = null, array $context = []): array
    {
        $facts = [
            'applicationId' => (string) $application->id,
            'productType' => (string) $application->product_type,
            'amountRequested' => $application->amount_requested !== null ? (float) $application->amount_requested : null,
            'externalReference' => $application->external_reference,
            'submittedAt' => $application->submitted_at?->toIso8601String(),
            'currentStatus' => (string) $application->status,
            'targetStatus' => $context['targetStatus'] ?? null,
        ];

        $results = [];

        foreach ($versions as $version) {
            $applies = $this->evaluateConditionGroup($facts, $version->condition_definition ?? []);
            $action = $version->action_definition ?? [];
            $outcome = $applies ? ((string) ($action['outcome'] ?? $version->severity)) : 'skipped';

            RuleExecutionLog::query()->create([
                'id' => (string) Str::uuid(),
                'tenant_id' => (string) $application->tenant_id,
                'rule_id' => (string) $version->rule_id,
                'rule_version_id' => (string) $version->id,
                'subject_type' => 'application',
                'subject_id' => (string) $application->id,
                'trigger_event' => $triggerEvent,
                'execution_source' => $actor !== null ? 'user' : 'system',
                'outcome' => $outcome,
                'correlation_id' => $correlationId,
                'actor_user_id' => $actor?->id,
                'context_snapshot' => $facts,
                'outcome_summary' => ['matched' => $applies, 'ruleKey' => $version->rule?->rule_key],
                'executed_at' => now(),
            ]);

            if (!$applies) {
                continue;
            }

            $results[] = [
                'ruleId' => (string) $version->rule_id,
                'ruleVersionId' => (string) $version->id,
                'ruleKey' => (string) ($version->rule?->rule_key ?? ''),
                'ruleName' => (string) ($version->rule?->name ?? ''),
                'ruleVersion' => 'v' . (string) $version->version_number,
                'triggerEvent' => $triggerEvent,
                'outcome' => $outcome,
                'title' => (string) ($action['title'] ?? $version->execution_label ?? $version->rule?->name ?? 'Rule applied'),
                'noteBody' => (string) ($action['bodyTemplate'] ?? $version->note_template ?? 'A governed rule matched this application.'),
                'isBlocking' => $outcome === 'blocking',
                'evidence' => [
                    'ruleVersionId' => (string) $version->id,
                    'facts' => $facts,
                ],
            ];
        }

        return $results;
    }
}