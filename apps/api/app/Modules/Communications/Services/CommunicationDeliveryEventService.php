<?php

declare(strict_types=1);

namespace App\Modules\Communications\Services;

use Illuminate\Database\QueryException;
use Illuminate\Support\Str;
use App\Modules\Communications\Models\DeliveryStatusEvent;

final class CommunicationDeliveryEventService
{
    public function __construct(
        private readonly CommunicationCorrelationService $correlationService,
    ) {
    }

    /**
     * @param array<string, mixed> $rawPayload
     */
    public function record(
        string $tenantId,
        ?string $clientId,
        string $subjectType,
        string $subjectId,
        string $eventType,
        ?string $providerStatus,
        ?string $correlationKey,
        bool $signatureVerified,
        array $rawPayload,
        ?string $providerName = null,
        ?string $providerReference = null,
        ?string $providerEventId = null,
        ?string $statusBefore = null,
        ?string $statusAfter = null,
    ): DeliveryStatusEvent {
        $dedupeHash = $this->correlationService->dedupeHash([
            'tenantId' => $tenantId,
            'clientId' => $clientId,
            'subjectType' => $subjectType,
            'subjectId' => $subjectId,
            'eventType' => $eventType,
            'providerName' => $providerName,
            'providerReference' => $providerReference,
            'providerEventId' => $providerEventId,
            'providerStatus' => $providerStatus,
            'rawPayload' => $rawPayload,
        ]);

        $attributes = [
            'tenant_id' => $tenantId,
            'dedupe_hash' => $dedupeHash,
        ];

        $values = [
            'id' => (string) Str::uuid(),
            'client_id' => $clientId,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'provider_name' => $providerName,
            'provider_reference' => $providerReference,
            'provider_event_id' => $providerEventId,
            'provider_event_type' => $eventType,
            'provider_status' => $providerStatus,
            'occurred_at' => now(),
            'received_at' => now(),
            'correlation_key' => $correlationKey,
            'signature_verified' => $signatureVerified,
            'raw_payload' => $rawPayload,
            'status_before' => $statusBefore,
            'status_after' => $statusAfter,
        ];

        try {
            return DeliveryStatusEvent::query()
                ->withoutGlobalScopes()
                ->firstOrCreate($attributes, $values);
        } catch (QueryException $exception) {
            if (!$this->isUniqueConstraintViolation($exception)) {
                throw $exception;
            }

            return DeliveryStatusEvent::query()
                ->withoutGlobalScopes()
                ->where($attributes)
                ->firstOrFail();
        }
    }

    private function isUniqueConstraintViolation(QueryException $exception): bool
    {
        $sqlState = (string) ($exception->errorInfo[0] ?? $exception->getCode());
        $driverCode = (int) ($exception->errorInfo[1] ?? 0);

        return in_array($sqlState, ['23000', '23505'], true)
            || in_array($driverCode, [19, 1062, 1555, 2067], true);
    }
}
