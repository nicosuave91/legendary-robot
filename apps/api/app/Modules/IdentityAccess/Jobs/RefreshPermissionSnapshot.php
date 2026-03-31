<?php

declare(strict_types=1);

namespace App\Modules\IdentityAccess\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Modules\Shared\Contracts\QueuesTenantAware;

final readonly class RefreshPermissionSnapshot implements ShouldQueue, QueuesTenantAware
{
    public function __construct(
        private string $tenantIdValue,
        private string $correlationIdValue,
        private string $userId,
    ) {
    }

    public function handle(): void
    {
        // Reserved for future permission snapshot refresh work.
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
