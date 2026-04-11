<?php

declare(strict_types=1);

namespace App\Modules\WorkflowBuilder\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Shared\Audit\AuditLogger;
use App\Modules\WorkflowBuilder\Models\Workflow;
use App\Modules\WorkflowBuilder\Models\WorkflowVersion;

final class WorkflowPublishService
{
    use WorkflowSupport;

    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly WorkflowCatalogService $catalogService,
        private readonly WorkflowDefinitionValidator $definitionValidator,
    ) {
    }

    public function publish(User $actor, Workflow $workflow, string $correlationId): array
    {
        abort_unless((string) $workflow->tenant_id === (string) $actor->tenant_id, 404);

        $published = DB::transaction(function () use ($actor, $workflow): Workflow {
            /** @var WorkflowVersion $draftVersion */
            $draftVersion = $workflow->currentDraftVersion()->withoutGlobalScopes()->firstOrFail();
            abort_if((string) $draftVersion->lifecycle_state !== 'draft', 409, 'Published workflow versions are immutable.');

            $validation = $this->definitionValidator->validate(
                (array) ($draftVersion->trigger_definition ?? []),
                (array) ($draftVersion->steps_definition ?? []),
            );

            if ($validation['isValid'] !== true) {
                throw ValidationException::withMessages([
                    'workflow' => ['Draft workflow definition is not publishable.'],
                    'draftValidation' => array_map(
                        static fn (array $issue): string => sprintf('%s: %s', $issue['path'], $issue['message']),
                        $validation['errors'],
                    ),
                ]);
            }

            $this->assertTriggerDefinition($draftVersion->trigger_definition ?? []);
            $this->assertStepsDefinition($draftVersion->steps_definition ?? []);

            $draftVersion->forceFill([
                'lifecycle_state' => 'published',
                'published_at' => now(),
                'published_by' => (string) $actor->id,
                'updated_by' => (string) $actor->id,
            ])->save();

            $workflow->forceFill([
                'status' => 'published',
                'latest_published_version_id' => (string) $draftVersion->id,
                'current_draft_version_id' => null,
                'updated_by' => (string) $actor->id,
            ])->save();

            return $workflow->fresh(['latestPublishedVersion', 'currentDraftVersion']);
        });

        $this->auditLogger->record([
            'tenant_id' => (string) $actor->tenant_id,
            'actor_id' => (string) $actor->id,
            'action' => 'workflows.published',
            'subject_type' => 'workflow',
            'subject_id' => (string) $workflow->id,
            'correlation_id' => $correlationId,
            'before_summary' => null,
            'after_summary' => json_encode(['latestPublishedVersionId' => $published->latest_published_version_id], JSON_THROW_ON_ERROR),
        ]);

        return $this->catalogService->detailForUser($actor, $published);
    }
}
