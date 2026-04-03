<?php

declare(strict_types=1);

namespace App\Modules\Imports\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Modules\Imports\Models\Import;
use App\Modules\Imports\Services\ImportCommitService;
use App\Modules\Shared\Contracts\QueuesTenantAware;

final readonly class CommitImportJob implements ShouldQueue, QueuesTenantAware
{
    public function __construct(
        private string $tenantIdValue,
        private string $correlationIdValue,
        private string $importId,
    ) {
    }

    public function handle(ImportCommitService $commitService): void
    {
        $import = Import::query()->withoutGlobalScopes()->where('tenant_id', $this->tenantIdValue)->where('id', $this->importId)->firstOrFail();
        $commitService->commit($import, $this->correlationIdValue);
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
