<?php

declare(strict_types=1);

namespace App\Modules\RulesLibrary\Services;

use Illuminate\Support\Collection;
use App\Modules\RulesLibrary\Models\RuleVersion;

final class RuleExecutionReadyQueryService
{
    /**
     * @return Collection<int, RuleVersion>
     */
    public function publishedVersionsFor(string $tenantId, string $moduleScope, string $triggerEvent): Collection
    {
        return RuleVersion::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('lifecycle_state', 'published')
            ->where('trigger_event', $triggerEvent)
            ->whereHas('rule', function ($query) use ($tenantId, $moduleScope): void {
                $query->withoutGlobalScopes()
                    ->where('tenant_id', $tenantId)
                    ->where('module_scope', $moduleScope)
                    ->where('status', 'published');
            })
            ->with('rule')
            ->orderByDesc('published_at')
            ->get();
    }
}