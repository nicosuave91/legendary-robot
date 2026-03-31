<?php

declare(strict_types=1);

namespace App\Modules\Communications\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Throwable;
use App\Modules\Communications\Contracts\VoiceTransportProvider;
use App\Modules\Communications\Models\CallLog;
use App\Modules\Communications\Services\CommunicationAuditService;
use App\Modules\Communications\Services\CommunicationCommandService;
use App\Modules\Shared\Contracts\QueuesTenantAware;

final readonly class InitiateOutboundCallJob implements ShouldQueue, QueuesTenantAware
{
    public function __construct(private string $tenantIdValue, private string $correlationIdValue, private string $callLogId, private ?string $purposeNote = null) {}

    public function handle(VoiceTransportProvider $provider, CommunicationCommandService $communicationCommandService, CommunicationAuditService $auditService): void
    {
        $callLog = CallLog::query()->withoutGlobalScopes()->where('tenant_id', $this->tenantIdValue)->where('id', $this->callLogId)->firstOrFail();
        $statusBefore = (string) $callLog->lifecycle_status;
        $callLog->forceFill(['lifecycle_status' => 'submitting'])->save();

        try {
            $result = $provider->initiate($callLog);
            $callLog->forceFill(['provider_name' => $result->providerName, 'provider_call_id' => $result->providerReference, 'lifecycle_status' => $result->accepted ? 'submitted' : 'failed', 'started_at' => $result->accepted ? now() : null, 'failure_code' => $result->failureCode, 'failure_message' => $result->failureMessage])->save();
            $communicationCommandService->appendEvent((string) $callLog->tenant_id, (string) $callLog->client_id, 'call_log', (string) $callLog->id, $result->accepted ? 'provider_submission_accepted' : 'provider_submission_failed', $result->providerStatus, (string) $callLog->correlation_key, false, $result->toAuditSummary(), $result->providerName, $result->providerReference, null, $statusBefore, (string) $callLog->lifecycle_status);
            $auditService->record(null, (string) $callLog->tenant_id, $result->accepted ? 'communication.call.submitted' : 'communication.call.submit_failed', 'call_log', (string) $callLog->id, $this->correlationIdValue, array_merge($result->toAuditSummary(), ['purposeNote' => $this->purposeNote]));
        } catch (Throwable $throwable) {
            $callLog->forceFill(['lifecycle_status' => 'failed', 'failure_code' => 'provider_exception', 'failure_message' => $throwable->getMessage()])->save();
            $communicationCommandService->appendEvent((string) $callLog->tenant_id, (string) $callLog->client_id, 'call_log', (string) $callLog->id, 'provider_submission_exception', 'failed', (string) $callLog->correlation_key, false, ['message' => $throwable->getMessage()], 'twilio', null, null, $statusBefore, 'failed');
            $auditService->record(null, (string) $callLog->tenant_id, 'communication.call.submit_failed', 'call_log', (string) $callLog->id, $this->correlationIdValue, ['message' => $throwable->getMessage()]);
        }
    }

    public function tenantId(): string { return $this->tenantIdValue; }
    public function correlationId(): string { return $this->correlationIdValue; }
}
