<?php

declare(strict_types=1);

namespace App\Modules\Communications\Services;

final class CommunicationCorrelationService
{
    public function dedupeHash(array $payload): string
    {
        return hash('sha256', json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}
