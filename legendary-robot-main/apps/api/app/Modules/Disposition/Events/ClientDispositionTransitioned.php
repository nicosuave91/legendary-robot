<?php

declare(strict_types=1);

namespace App\Modules\Disposition\Events;

final readonly class ClientDispositionTransitioned
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public string $tenantId,
        public string $correlationId,
        public string $clientId,
        public array $payload,
    ) {
    }
}