<?php

declare(strict_types=1);

namespace App\Modules\WorkflowBuilder\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Shared\Audit\AuditLogger;
use App\Modules\WorkflowBuilder\Models\Workflow;
use App\Modules\WorkflowBuilder\Models\WorkflowVersion;

final class WorkflowDraftService
{
    use WorkflowSupport;

    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly WorkflowCatalogService $catalogService,
    ) {
    }

    public function create(User $actor, array $payload, string $correlationId): array
    {
        $workflow = DB::transaction(function () use ($actor, $payload): Workflow {
            $workflow = Workflow::query()->create([
                'id' => (string) Str::uuid(),
                'tenant_id' => (string) $actor->tenant_id,
                'workflow_key' => (string) $payload['workflowKey'],
                'name' => (string) $payload['name'],
                'description' => $payload['description'] ?? null,
                'status' => 'draft',
                'created_by' => (string) $actor->id,
                'updated_by' => (string) $actor->id,
            ]);

            $version = WorkflowVersion::query()->create([
                'id' => (string) Str::uuid(),
                'tenant_id' => (string) $actor->tenant_id,
                'workflow_id' => (string) $workflow->id,
                'version_number' => 1,
                'lifecycle_state' => 'draft',
                'trigger_definition' => $payload['triggerDefinition'],
                'steps_definition' => $payload['stepsDefinition'],
                'checksum' => $this->checksum([
                    'triggerDefinition' => $payload['triggerDefinition'],
                    'stepsDefinition' => $payload['stepsDefinition'],
                ]),
                'created_by' => (string) $actor->id,
                'updated_by' => (string) $actor->id,
            ]);

            $workflow->forceFill(['current_draft_version_id' => (string) $version->id])->save();

            return $workflow->fresh(['latestPublishedVersion', 'currentDraftVersion']);
        });

        $this->auditLogger->record([
            'tenant_id' => (string) $actor->tenant_id,
            'actor_id' => (string) $actor->id,
            'action' => 'workflows.created',
            'subject_type' => 'workflow',
            'subject_id' => (string) $workflow->id,
            'correlation_id' => $correlationId,
            'before_summary' => null,
            'after_summary' => json_encode(['workflowKey' => $workflow->workflow_key, 'status' => $workflow->status], JSON_THROW_ON_ERROR),
        ]);

        return $this->catalogService->detailForUser($actor, $workflow);
    }

    public function update(User $actor, Workflow $workflow, array $payload, string $correlationId): array
    {
        abort_unless((string) $workflow->tenant_id === (string) $actor->tenant_id, 404);

        $updated = DB::transaction(function () use ($actor, $workflow, $payload): Workflow {
            $draftVersion = $workflow->currentDraftVersion()->withoutGlobalScopes()->first();

            if ($draftVersion === null) {
                $latest = $workflow->latestPublishedVersion()->withoutGlobalScopes()->firstOrFail();

                $draftVersion = WorkflowVersion::query()->create([
                    'id' => (string) Str::uuid(),
                    'tenant_id' => (string) $actor->tenant_id,
                    'workflow_id' => (string) $workflow->id,
                    'version_number' => ((int) $latest->version_number) + 1,
                    'lifecycle_state' => 'draft',
                    'trigger_definition' => $latest->trigger_definition ?? [],
                    'steps_definition' => $latest->steps_definition ?? [],
                    'checksum' => (string) $latest->checksum,
                    'supersedes_version_id' => (string) $latest->id,
                    'created_by' => (string) $actor->id,
                    'updated_by' => (string) $actor->id,
                ]);
            }

            abort_if((string) $draftVersion->lifecycle_state !== 'draft', 409, 'Published workflow versions are immutable.');

            $workflow->forceFill([
                'name' => $payload['name'] ?? $workflow->name,
                'description' => $payload['description'] ?? $workflow->description,
                'current_draft_version_id' => (string) $draftVersion->id,
                'updated_by' => (string) $actor->id,
            ])->save();

            $draftVersion->forceFill([
                'trigger_definition' => $payload['triggerDefinition'] ?? $draftVersion->trigger_definition,
                'steps_definition' => $payload['stepsDefinition'] ?? $draftVersion->steps_definition,
                'checksum' => $this->checksum([
                    'triggerDefinition' => $payload['triggerDefinition'] ?? $draftVersion->trigger_definition,
                    'stepsDefinition' => $payload['stepsDefinition'] ?? $draftVersion->steps_definition,
                ]),
                'updated_by' => (string) $actor->id,
            ])->save();

            return $workflow->fresh(['latestPublishedVersion', 'currentDraftVersion']);
        });

        $this->auditLogger->record([
            'tenant_id' => (string) $actor->tenant_id,
            'actor_id' => (string) $actor->id,
            'action' => 'workflows.draft.updated',
            'subject_type' => 'workflow',
            'subject_id' => (string) $workflow->id,
            'correlation_id' => $correlationId,
            'before_summary' => null,
            'after_summary' => json_encode(['currentDraftVersionId' => $updated->current_draft_version_id], JSON_THROW_ON_ERROR),
        ]);

        return $this->catalogService->detailForUser($actor, $updated);
    }
}