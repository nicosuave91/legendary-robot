<?php

declare(strict_types=1);

namespace App\Modules\Communications\DTOs;

final readonly class WebhookVerificationResultData
{
    public function __construct(
        public bool $accepted,
        public bool $verified,
        public string $mode,
        public ?string $failureReason = null,
    ) {
    }
}
