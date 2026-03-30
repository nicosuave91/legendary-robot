<?php

declare(strict_types=1);

namespace App\Modules\Shared\Audit;

use App\Modules\Audit\Models\AuditLog;

final class AuditLogger
{
    /**
     * @param array<string, mixed> $payload
     */
    public function record(array $payload): void
    {
        AuditLog::query()->create($payload);
    }
}
