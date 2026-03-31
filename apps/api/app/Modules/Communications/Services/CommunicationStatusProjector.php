<?php

declare(strict_types=1);

namespace App\Modules\Communications\Services;

final class CommunicationStatusProjector
{
    public function project(
        string $lifecycle,
        ?string $providerStatus,
        ?string $reasonCode = null,
        ?string $reasonMessage = null,
        string $source = 'internal',
        ?string $updatedAt = null,
    ): array {
        $tone = match ($lifecycle) {
            'delivered', 'received', 'completed' => 'success',
            'deferred', 'busy', 'no_answer' => 'warning',
            'failed', 'undelivered', 'bounced', 'dropped', 'canceled' => 'danger',
            default => 'neutral',
        };

        $display = match ($lifecycle) {
            'queued' => 'Queued',
            'submitting' => 'Submitting',
            'submitted' => 'Submitted',
            'delivery_pending' => 'Delivery pending',
            'delivered' => 'Delivered',
            'received' => 'Received',
            'failed' => 'Failed',
            'undelivered' => 'Undelivered',
            'bounced' => 'Bounced',
            'deferred' => 'Deferred',
            'dropped' => 'Dropped',
            'ringing' => 'Ringing',
            'in_progress' => 'In progress',
            'completed' => 'Completed',
            'busy' => 'Busy',
            'no_answer' => 'No answer',
            'canceled' => 'Canceled',
            default => ucfirst(str_replace('_', ' ', $lifecycle)),
        };

        $isTerminal = in_array($lifecycle, ['delivered', 'received', 'completed', 'failed', 'undelivered', 'bounced', 'dropped', 'busy', 'no_answer', 'canceled'], true);

        return [
            'lifecycle' => $lifecycle,
            'providerStatus' => $providerStatus,
            'displayLabel' => $display,
            'tone' => $tone,
            'isTerminal' => $isTerminal,
            'updatedAt' => $updatedAt,
            'reasonCode' => $reasonCode,
            'reasonMessage' => $reasonMessage,
            'source' => $source,
        ];
    }
}
