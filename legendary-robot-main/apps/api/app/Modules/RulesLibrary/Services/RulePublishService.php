<?php

declare(strict_types=1);

namespace App\Modules\RulesLibrary\Services;

use Illuminate\Support\Facades\DB;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\RulesLibrary\Models\Rule;
use App\Modules\RulesLibrary\Models\RuleVersion;
use App\Modules\Shared\Audit\AuditLogger;

final class RulePublishService
{
    use RuleSupport;

    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly RuleCatalogService $catalogService,
    ) {
    }

    public function publish(User $actor, Rule $rule, string $correlationId): array
    {
        abort_unless((string) $rule->tenant_id === (string) $actor->tenant_id, 404);

        $publishedRule = DB::transaction(function () use ($actor, $rule): Rule {
            /** @var RuleVersion $draftVersion */
            $draftVersion = $rule->currentDraftVersion()->withoutGlobalScopes()->firstOrFail();
            abort_if((string) $draftVersion->lifecycle_state !== 'draft', 409, 'Published rule versions are immutable.');

            $this->assertRuleVersionPublishable([
                'trigger_event' => $draftVersion->trigger_event,
                'condition_definition' => $draftVersion->condition_definition,
                'action_definition' => $draftVersion->action_definition,
            ]);

            $draftVersion->forceFill([
                'lifecycle_state' => 'published',
                'published_at' => now(),
                'published_by' => (string) $actor->id,
                'updated_by' => (string) $actor->id,
            ])->save();

            $rule->forceFill([
                'status' => 'published',
                'latest_published_version_id' => (string) $draftVersion->id,
                'current_draft_version_id' => null,
                'updated_by' => (string) $actor->id,
            ])->save();

            return $rule->fresh(['latestPublishedVersion', 'currentDraftVersion']);
        });

        $this->auditLogger->record([
            'tenant_id' => (string) $actor->tenant_id,
            'actor_id' => (string) $actor->id,
            'action' => 'rules.published',
            'subject_type' => 'rule',
            'subject_id' => (string) $rule->id,
            'correlation_id' => $correlationId,
            'before_summary' => null,
            'after_summary' => json_encode(['latestPublishedVersionId' => $publishedRule->latest_published_version_id], JSON_THROW_ON_ERROR),
        ]);

        return $this->catalogService->detailForUser($actor, $publishedRule);
    }
}