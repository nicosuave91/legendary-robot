\
<?php

declare(strict_types=1);

namespace App\Modules\RulesLibrary\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\RulesLibrary\Models\Rule;
use App\Modules\RulesLibrary\Models\RuleVersion;
use App\Modules\Shared\Audit\AuditLogger;

final class RuleDraftService
{
    use RuleSupport;

    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly RuleCatalogService $catalogService,
    ) {
    }

    public function create(User $actor, array $payload, string $correlationId): array
    {
        $rule = DB::transaction(function () use ($actor, $payload): Rule {
            $rule = Rule::query()->create([
                'id' => (string) Str::uuid(),
                'tenant_id' => (string) $actor->tenant_id,
                'rule_key' => (string) $payload['ruleKey'],
                'name' => (string) $payload['name'],
                'description' => $payload['description'] ?? null,
                'module_scope' => (string) $payload['moduleScope'],
                'subject_type' => (string) $payload['subjectType'],
                'status' => 'draft',
                'created_by' => (string) $actor->id,
                'updated_by' => (string) $actor->id,
            ]);

            $version = RuleVersion::query()->create([
                'id' => (string) Str::uuid(),
                'tenant_id' => (string) $actor->tenant_id,
                'rule_id' => (string) $rule->id,
                'version_number' => 1,
                'lifecycle_state' => 'draft',
                'trigger_event' => (string) $payload['triggerEvent'],
                'severity' => (string) $payload['severity'],
                'industry_scope' => $payload['industryScope'] ?? null,
                'condition_definition' => $payload['conditionDefinition'],
                'action_definition' => $payload['actionDefinition'],
                'execution_label' => $payload['executionLabel'] ?? null,
                'note_template' => $payload['noteTemplate'] ?? null,
                'checksum' => $this->checksum([
                    'triggerEvent' => $payload['triggerEvent'],
                    'severity' => $payload['severity'],
                    'conditionDefinition' => $payload['conditionDefinition'],
                    'actionDefinition' => $payload['actionDefinition'],
                    'executionLabel' => $payload['executionLabel'] ?? null,
                    'noteTemplate' => $payload['noteTemplate'] ?? null,
                ]),
                'created_by' => (string) $actor->id,
                'updated_by' => (string) $actor->id,
            ]);

            $rule->forceFill(['current_draft_version_id' => (string) $version->id])->save();

            /** @var Rule $freshRule */
            $freshRule = $rule->fresh(['latestPublishedVersion', 'currentDraftVersion']);

            return $freshRule;
        });

        $this->auditLogger->record([
            'tenant_id' => (string) $actor->tenant_id,
            'actor_id' => (string) $actor->id,
            'action' => 'rules.created',
            'subject_type' => 'rule',
            'subject_id' => (string) $rule->id,
            'correlation_id' => $correlationId,
            'before_summary' => null,
            'after_summary' => json_encode(['ruleKey' => $rule->rule_key, 'status' => $rule->status], JSON_THROW_ON_ERROR),
        ]);

        return $this->catalogService->detailForUser($actor, $rule);
    }

    public function update(User $actor, Rule $rule, array $payload, string $correlationId): array
    {
        abort_unless((string) $rule->tenant_id === (string) $actor->tenant_id, 404);

        $updatedRule = DB::transaction(function () use ($actor, $rule, $payload): Rule {
            /** @var RuleVersion|null $draftVersion */
            $draftVersion = $rule->currentDraftVersion()->withoutGlobalScopes()->first();

            if ($draftVersion === null) {
                /** @var RuleVersion $latest */
                $latest = $rule->latestPublishedVersion()->withoutGlobalScopes()->firstOrFail();

                $draftVersion = RuleVersion::query()->create([
                    'id' => (string) Str::uuid(),
                    'tenant_id' => (string) $actor->tenant_id,
                    'rule_id' => (string) $rule->id,
                    'version_number' => ((int) $latest->version_number) + 1,
                    'lifecycle_state' => 'draft',
                    'trigger_event' => (string) $latest->trigger_event,
                    'severity' => (string) $latest->severity,
                    'industry_scope' => $latest->industry_scope,
                    'condition_definition' => $latest->condition_definition ?? [],
                    'action_definition' => $latest->action_definition ?? [],
                    'execution_label' => $latest->execution_label,
                    'note_template' => $latest->note_template,
                    'checksum' => (string) $latest->checksum,
                    'supersedes_version_id' => (string) $latest->id,
                    'created_by' => (string) $actor->id,
                    'updated_by' => (string) $actor->id,
                ]);
            }

            abort_if((string) $draftVersion->lifecycle_state !== 'draft', 409, 'Published rule versions are immutable.');

            $rule->forceFill([
                'name' => $payload['name'] ?? $rule->name,
                'description' => $payload['description'] ?? $rule->description,
                'module_scope' => $payload['moduleScope'] ?? $rule->module_scope,
                'subject_type' => $payload['subjectType'] ?? $rule->subject_type,
                'current_draft_version_id' => (string) $draftVersion->id,
                'updated_by' => (string) $actor->id,
            ])->save();

            $draftVersion->forceFill([
                'trigger_event' => $payload['triggerEvent'] ?? $draftVersion->trigger_event,
                'severity' => $payload['severity'] ?? $draftVersion->severity,
                'industry_scope' => $payload['industryScope'] ?? $draftVersion->industry_scope,
                'condition_definition' => $payload['conditionDefinition'] ?? $draftVersion->condition_definition,
                'action_definition' => $payload['actionDefinition'] ?? $draftVersion->action_definition,
                'execution_label' => $payload['executionLabel'] ?? $draftVersion->execution_label,
                'note_template' => $payload['noteTemplate'] ?? $draftVersion->note_template,
                'checksum' => $this->checksum([
                    'triggerEvent' => $payload['triggerEvent'] ?? $draftVersion->trigger_event,
                    'severity' => $payload['severity'] ?? $draftVersion->severity,
                    'conditionDefinition' => $payload['conditionDefinition'] ?? $draftVersion->condition_definition,
                    'actionDefinition' => $payload['actionDefinition'] ?? $draftVersion->action_definition,
                    'executionLabel' => $payload['executionLabel'] ?? $draftVersion->execution_label,
                    'noteTemplate' => $payload['noteTemplate'] ?? $draftVersion->note_template,
                ]),
                'updated_by' => (string) $actor->id,
            ])->save();

            /** @var Rule $freshRule */
            $freshRule = $rule->fresh(['latestPublishedVersion', 'currentDraftVersion']);

            return $freshRule;
        });

        $this->auditLogger->record([
            'tenant_id' => (string) $actor->tenant_id,
            'actor_id' => (string) $actor->id,
            'action' => 'rules.draft.updated',
            'subject_type' => 'rule',
            'subject_id' => (string) $rule->id,
            'correlation_id' => $correlationId,
            'before_summary' => null,
            'after_summary' => json_encode(['currentDraftVersionId' => $updatedRule->current_draft_version_id], JSON_THROW_ON_ERROR),
        ]);

        return $this->catalogService->detailForUser($actor, $updatedRule);
    }
}
