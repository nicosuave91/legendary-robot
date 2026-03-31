<?php

declare(strict_types=1);

namespace App\Modules\Imports\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Modules\Imports\Models\Import;
use App\Modules\Imports\Services\ImportValidationService;
use App\Modules\Shared\Contracts\QueuesTenantAware;

final readonly class ValidateImportJob implements ShouldQueue, QueuesTenantAware
{
    public function __construct(
        private string $tenantIdValue,
        private string $correlationIdValue,
        private string $importId,
    ) {
    }

    public function handle(ImportValidationService $validationService): void
    {
        $import = Import::query()->withoutGlobalScopes()->where('tenant_id', $this->tenantIdValue)->where('id', $this->importId)->firstOrFail();
        $validationService->validate($import, $this->correlationIdValue);
    }

    public function tenantId(): string
    {
        return $this->tenantIdValue;
    }

    public function correlationId(): string
    {
        return $this->correlationIdValue;
    }
}
