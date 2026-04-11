<?php

declare(strict_types=1);

namespace App\Modules\Communications\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Throwable;
use App\Modules\Communications\Contracts\EmailTransportProvider;
use App\Modules\Communications\Models\CommunicationMessage;
use App\Modules\Communications\Models\EmailLog;
use App\Modules\Communications\Services\CommunicationAuditService;
use App\Modules\Communications\Services\CommunicationCommandService;
use App\Modules\Shared\Contracts\QueuesTenantAware;

final readonly class SubmitOutboundEmailJob implements ShouldQueue, QueuesTenantAware
{
    public function __construct(private string $tenantIdValue, private string $correlationIdValue, private string $messageId) {}

    public function handle(EmailTransportProvider $provider, CommunicationCommandService $communicationCommandService, CommunicationAuditService $auditService): void
    {
        $message = CommunicationMessage::query()->withoutGlobalScopes()->where('tenant_id', $this->tenantIdValue)->where('id', $this->messageId)->firstOrFail();
        $emailLog = EmailLog::query()->withoutGlobalScopes()->where('communication_message_id', $message->id)->firstOrFail();
        $statusBefore = (string) $message->lifecycle_status;
        $message->forceFill(['lifecycle_status' => 'submitting'])->save();

        try {
            $result = $provider->send($message);
            $message->forceFill([
                'provider_name' => $result->providerName,
                'provider_message_id' => $result->providerReference,
                'provider_status' => $result->providerStatus,
                'lifecycle_status' => $result->accepted ? 'submitted' : 'failed',
                'submitted_at' => $result->accepted ? now() : null,
                'finalized_at' => $result->accepted ? null : now(),
                'failure_code' => $result->failureCode,
                'failure_message' => $result->failureMessage,
            ])->save();
            $emailLog->forceFill(['provider_name' => $result->providerName, 'provider_message_id' => $result->providerReference, 'last_provider_event_at' => now(), 'provider_metadata' => array_merge((array) $emailLog->provider_metadata, $result->rawResponse)])->save();
            $communicationCommandService->appendEvent((string) $message->tenant_id, (string) $message->client_id, 'communication_message', (string) $message->id, $result->accepted ? 'provider_submission_accepted' : 'provider_submission_failed', $result->providerStatus, (string) $message->correlation_key, false, $result->toAuditSummary(), $result->providerName, $result->providerReference, null, $statusBefore, (string) $message->lifecycle_status);
            $auditService->record(null, (string) $message->tenant_id, $result->accepted ? 'communication.email.submitted' : 'communication.email.submit_failed', 'communication_message', (string) $message->id, $this->correlationIdValue, $result->toAuditSummary());
        } catch (Throwable $throwable) {
            $message->forceFill(['lifecycle_status' => 'failed', 'finalized_at' => now(), 'failure_code' => 'provider_exception', 'failure_message' => $throwable->getMessage()])->save();
            $communicationCommandService->appendEvent((string) $message->tenant_id, (string) $message->client_id, 'communication_message', (string) $message->id, 'provider_submission_exception', 'failed', (string) $message->correlation_key, false, ['message' => $throwable->getMessage()], 'sendgrid', null, null, $statusBefore, 'failed');
            $auditService->record(null, (string) $message->tenant_id, 'communication.email.submit_failed', 'communication_message', (string) $message->id, $this->correlationIdValue, ['message' => $throwable->getMessage()]);
        }
    }

    public function tenantId(): string { return $this->tenantIdValue; }
    public function correlationId(): string { return $this->correlationIdValue; }
}
