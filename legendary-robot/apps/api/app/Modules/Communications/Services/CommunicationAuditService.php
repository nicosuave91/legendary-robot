<?php

declare(strict_types=1);

namespace App\Modules\Communications\Services;

use App\Modules\IdentityAccess\Models\User;
use App\Modules\Shared\Audit\AuditLogger;

final class CommunicationAuditService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {
    }

    public function record(
        ?User $actor,
        string $tenantId,
        string $action,
        string $subjectType,
        ?string $subjectId,
        string $correlationId,
        array $afterSummary = [],
        ?array $beforeSummary = null,
    ): void {
        $this->auditLogger->record([
            'tenant_id' => $tenantId,
            'actor_id' => $actor?->id,
            'action' => $action,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'correlation_id' => $correlationId,
            'before_summary' => $beforeSummary,
            'after_summary' => $afterSummary,
        ]);
    }
}
