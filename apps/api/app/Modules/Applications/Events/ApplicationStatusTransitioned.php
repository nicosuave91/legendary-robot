<?php

declare(strict_types=1);

namespace App\Modules\Applications\Events;

final readonly class ApplicationStatusTransitioned
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public string $tenantId,
        public string $correlationId,
        public string $applicationId,
        public array $payload,
    ) {
    }
}