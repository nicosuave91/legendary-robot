<?php

declare(strict_types=1);

namespace App\Modules\Communications\DTOs;

final readonly class ProviderSubmissionResultData
{
    public function __construct(
        public string $providerName,
        public bool $accepted,
        public ?string $providerReference,
        public ?string $providerStatus,
        public array $rawResponse = [],
        public ?string $failureCode = null,
        public ?string $failureMessage = null,
    ) {
    }

    public function toAuditSummary(): array
    {
        return [
            'providerName' => $this->providerName,
            'accepted' => $this->accepted,
            'providerReference' => $this->providerReference,
            'providerStatus' => $this->providerStatus,
            'failureCode' => $this->failureCode,
            'failureMessage' => $this->failureMessage,
        ];
    }
}
