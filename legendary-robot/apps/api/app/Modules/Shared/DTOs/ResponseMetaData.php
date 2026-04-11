<?php

declare(strict_types=1);

namespace App\Modules\Shared\DTOs;

final readonly class ResponseMetaData
{
    public function __construct(
        public string $apiVersion,
        public string $correlationId,
    ) {
    }
}
