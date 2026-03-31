<?php

declare(strict_types=1);

namespace App\Modules\Shared\Contracts;

interface QueuesTenantAware
{
    public function tenantId(): string;
    public function correlationId(): string;
}
