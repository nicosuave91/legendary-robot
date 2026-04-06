<?php

declare(strict_types=1);

namespace App\Modules\Disposition\Services;

use Illuminate\Support\Collection;
use App\Modules\Disposition\Models\DispositionDefinition;

final class DispositionDefinitionCatalog
{
    public function forTenant(string $tenantId): Collection
    {
        return DispositionDefinition::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function findForTenant(string $tenantId, string $code): ?DispositionDefinition
    {
        return DispositionDefinition::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('code', $code)
            ->where('is_active', true)
            ->first();
    }
}
