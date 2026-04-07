<?php

declare(strict_types=1);

namespace App\Modules\WorkflowBuilder\Services;

use Illuminate\Support\Collection;
use App\Modules\WorkflowBuilder\Models\WorkflowVersion;

final class WorkflowTriggerMatcher
{
    use WorkflowSupport;

    /**
     * @param array<string, mixed> $payload
     * @return Collection<int, WorkflowVersion>
     */
    public function matchingPublishedVersions(string $tenantId, string $eventName, string $subjectType, array $payload): Collection
    {
        $versions = WorkflowVersion::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('lifecycle_state', 'published')
            ->with('workflow')
            ->get();

        return $versions->filter(function (WorkflowVersion $version) use ($eventName, $subjectType, $payload): bool {
            $trigger = $version->trigger_definition ?? [];

            if (($trigger['event'] ?? null) !== $eventName) {
                return false;
            }

            if (($trigger['subjectType'] ?? null) !== $subjectType) {
                return false;
            }

            foreach (($trigger['filters'] ?? []) as $filter) {
                if (!is_array($filter) || !$this->evaluateCondition($payload, $filter)) {
                    return false;
                }
            }

            return true;
        })->values();
    }
}
