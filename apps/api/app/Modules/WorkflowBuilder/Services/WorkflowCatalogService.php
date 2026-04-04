<?php

declare(strict_types=1);

namespace App\Modules\WorkflowBuilder\Services;

use App\Modules\IdentityAccess\Models\User;
use App\Modules\WorkflowBuilder\Models\Workflow;
use App\Modules\WorkflowBuilder\Models\WorkflowVersion;

final class WorkflowCatalogService
{
    public function listForUser(User $actor, array $filters = []): array
    {
        $query = Workflow::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $actor->tenant_id)
            ->with(['latestPublishedVersion', 'currentDraftVersion'])
            ->latest('updated_at');

        if (($filters['status'] ?? null) !== null) {
            $query->where('status', $filters['status']);
        }

        /** @var \Illuminate\Database\Eloquent\Collection<int, Workflow> $items */
        $items = $query->get();

        return [
            'items' => $items->map(fn (Workflow $workflow): array => $this->serializeListItem($workflow))->values()->all(),
            'meta' => ['total' => $items->count()],
        ];
    }

    public function detailForUser(User $actor, Workflow $workflow): array
    {
        abort_unless((string) $workflow->tenant_id === (string) $actor->tenant_id, 404);

        /** @var \Illuminate\Database\Eloquent\Collection<int, WorkflowVersion> $versions */
        $versions = $workflow->versions()
            ->withoutGlobalScopes()
            ->where('tenant_id', $actor->tenant_id)
            ->latest('version_number')
            ->get();

        return [
            'workflow' => $this->serializeListItem($workflow) + [
                'currentDraftVersionId' => $workflow->current_draft_version_id,
                'latestPublishedVersionId' => $workflow->latest_published_version_id,
            ],
            'versions' => $versions->map(fn (WorkflowVersion $version): array => [
                'id' => (string) $version->id,
                'versionNumber' => (int) $version->version_number,
                'lifecycleState' => (string) $version->lifecycle_state,
                'triggerDefinition' => $version->trigger_definition,
                'stepsDefinition' => $version->steps_definition,
                'checksum' => (string) $version->checksum,
                'publishedAt' => $version->published_at?->toIso8601String(),
                'publishedBy' => $version->published_by,
                'createdAt' => $version->created_at?->toIso8601String(),
                'updatedAt' => $version->updated_at?->toIso8601String(),
            ])->values()->all(),
            'meta' => ['versionCount' => $versions->count()],
        ];
    }

    public function serializeListItem(Workflow $workflow): array
    {
        $triggerSummary = $workflow->latestPublishedVersion?->trigger_definition['event']
            ?? $workflow->currentDraftVersion?->trigger_definition['event']
            ?? 'draft';

        return [
            'id' => (string) $workflow->id,
            'workflowKey' => (string) $workflow->workflow_key,
            'name' => (string) $workflow->name,
            'description' => $workflow->description,
            'status' => (string) $workflow->status,
            'triggerSummary' => (string) $triggerSummary,
            'latestPublishedVersionNumber' => $workflow->latestPublishedVersion?->version_number,
            'currentDraftVersionNumber' => $workflow->currentDraftVersion?->version_number,
            'latestPublishedAt' => $workflow->latestPublishedVersion?->published_at?->toIso8601String(),
            'updatedAt' => $workflow->updated_at?->toIso8601String(),
        ];
    }
}
