<?php

declare(strict_types=1);

namespace App\Modules\IdentityAccess\Events;

final readonly class UserSignedIn
{
    public function __construct(
        public string $userId,
        public string $tenantId,
        public string $correlationId,
    ) {
    }
}
