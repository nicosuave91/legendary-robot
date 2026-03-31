<?php

declare(strict_types=1);

namespace App\Modules\Shared\Tenancy;

final class TenantContext
{
    private ?string $tenantId = null;

    public function set(?string $tenantId): void
    {
        $this->tenantId = $tenantId;
    }

    public function id(): ?string
    {
        return $this->tenantId;
    }

    public function hasTenant(): bool
    {
        return $this->tenantId !== null;
    }
}
