<?php

declare(strict_types=1);

namespace App\Modules\RulesLibrary\Services;

use App\Modules\IdentityAccess\Models\User;
use App\Modules\RulesLibrary\Models\Rule;
use App\Modules\RulesLibrary\Models\RuleExecutionLog;
use App\Modules\RulesLibrary\Models\RuleVersion;

final class RuleCatalogService
{
    public function listForUser(User $actor, array $filters = []): array
    {
        $query = Rule::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $actor->tenant_id)
            ->with(['latestPublishedVersion', 'currentDraftVersion'])
            ->latest('updated_at');

        if (($filters['moduleScope'] ?? null) !== null) {
            $query->where('module_scope', $filters['moduleScope']);
        }

        if (($filters['status'] ?? null) !== null) {
            $query->where('status', $filters['status']);
        }

        $items = $query->get();

        return [
            'items' => $items->map(fn (Rule $rule): array => $this->serializeListItem($rule))->values()->all(),
            'meta' => ['total' => $items->count()],
        ];
    }

    public function detailForUser(User $actor, Rule $rule): array
    {
        abort_unless((string) $rule->tenant_id === (string) $actor->tenant_id, 404);

        /** @var \Illuminate\Database\Eloquent\Collection<int, RuleVersion> $versions */
        $versions = $rule->versions()->withoutGlobalScopes()->where('tenant_id', $actor->tenant_id)->latest('version_number')->get();

        /** @var \Illuminate\Database\Eloquent\Collection<int, RuleExecutionLog> $logs */
        $logs = RuleExecutionLog::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $actor->tenant_id)
            ->where('rule_id', $rule->id)
            ->latest('executed_at')
            ->limit(25)
            ->get();

        return [
            'rule' => $this->serializeListItem($rule) + [
                'currentDraftVersionId' => $rule->current_draft_version_id,
                'latestPublishedVersionId' => $rule->latest_published_version_id,
            ],
            'versions' => $versions->map(fn (RuleVersion $version): array => [
                'id' => (string) $version->id,
                'versionNumber' => (int) $version->version_number,
                'lifecycleState' => (string) $version->lifecycle_state,
                'triggerEvent' => (string) $version->trigger_event,
                'severity' => (string) $version->severity,
                'conditionDefinition' => $version->condition_definition ?? [],
                'actionDefinition' => $version->action_definition ?? [],
                'executionLabel' => $version->execution_label,
                'noteTemplate' => $version->note_template,
                'checksum' => (string) $version->checksum,
                'publishedAt' => $version->published_at?->toIso8601String(),
                'publishedBy' => $version->published_by,
                'createdAt' => $version->created_at?->toIso8601String(),
                'updatedAt' => $version->updated_at?->toIso8601String(),
            ])->values()->all(),
            'executionLogs' => $logs->map(fn (RuleExecutionLog $log): array => [
                'id' => (string) $log->id,
                'ruleId' => (string) $log->rule_id,
                'ruleVersionId' => (string) $log->rule_version_id,
                'subjectType' => (string) $log->subject_type,
                'subjectId' => (string) $log->subject_id,
                'triggerEvent' => (string) $log->trigger_event,
                'executionSource' => (string) $log->execution_source,
                'outcome' => (string) $log->outcome,
                'correlationId' => $log->correlation_id,
                'actorUserId' => $log->actor_user_id,
                'contextSnapshot' => $log->context_snapshot ?? [],
                'outcomeSummary' => $log->outcome_summary ?? [],
                'executedAt' => $log->executed_at?->toIso8601String(),
            ])->values()->all(),
        ];
    }

    public function executionLogsForUser(User $actor, Rule $rule): array
    {
        abort_unless((string) $rule->tenant_id === (string) $actor->tenant_id, 404);

        /** @var \Illuminate\Database\Eloquent\Collection<int, RuleExecutionLog> $items */
        $items = RuleExecutionLog::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $actor->tenant_id)
            ->where('rule_id', $rule->id)
            ->latest('executed_at')
            ->limit(100)
            ->get();

        return [
            'items' => $items->map(fn (RuleExecutionLog $log): array => [
                'id' => (string) $log->id,
                'ruleId' => (string) $log->rule_id,
                'ruleVersionId' => (string) $log->rule_version_id,
                'subjectType' => (string) $log->subject_type,
                'subjectId' => (string) $log->subject_id,
                'triggerEvent' => (string) $log->trigger_event,
                'executionSource' => (string) $log->execution_source,
                'outcome' => (string) $log->outcome,
                'correlationId' => $log->correlation_id,
                'actorUserId' => $log->actor_user_id,
                'contextSnapshot' => $log->context_snapshot ?? [],
                'outcomeSummary' => $log->outcome_summary ?? [],
                'executedAt' => $log->executed_at?->toIso8601String(),
            ])->values()->all(),
            'meta' => ['total' => $items->count()],
        ];
    }

    public function serializeListItem(Rule $rule): array
    {
        return [
            'id' => (string) $rule->id,
            'ruleKey' => (string) $rule->rule_key,
            'name' => (string) $rule->name,
            'description' => $rule->description,
            'moduleScope' => (string) $rule->module_scope,
            'subjectType' => (string) $rule->subject_type,
            'status' => (string) $rule->status,
            'latestPublishedVersionNumber' => $rule->latestPublishedVersion?->version_number,
            'currentDraftVersionNumber' => $rule->currentDraftVersion?->version_number,
            'latestPublishedAt' => $rule->latestPublishedVersion?->published_at?->toIso8601String(),
            'updatedAt' => $rule->updated_at?->toIso8601String(),
        ];
    }
}
