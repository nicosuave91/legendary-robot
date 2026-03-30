<?php

declare(strict_types=1);

namespace App\Modules\IdentityAccess\Listeners;

use App\Modules\IdentityAccess\Events\UserSignedIn;
use App\Modules\Shared\Audit\AuditLogger;

final class WriteSignInAudit
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {
    }

    public function handle(UserSignedIn $event): void
    {
        $this->auditLogger->record([
            'tenant_id' => $event->tenantId,
            'actor_id' => $event->userId,
            'action' => 'auth.sign_in',
            'subject_type' => 'user',
            'subject_id' => $event->userId,
            'correlation_id' => $event->correlationId,
            'before_summary' => null,
            'after_summary' => null,
        ]);
    }
}
