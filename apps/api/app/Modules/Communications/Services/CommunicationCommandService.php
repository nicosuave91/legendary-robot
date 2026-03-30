<?php

declare(strict_types=1);

namespace App\Modules\Communications\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Modules\Clients\Models\Client;
use App\Modules\Communications\Jobs\InitiateOutboundCallJob;
use App\Modules\Communications\Jobs\SubmitOutboundEmailJob;
use App\Modules\Communications\Jobs\SubmitOutboundSmsJob;
use App\Modules\Communications\Models\CallLog;
use App\Modules\Communications\Models\CommunicationMessage;
use App\Modules\Communications\Models\CommunicationThread;
use App\Modules\Communications\Models\DeliveryStatusEvent;
use App\Modules\Communications\Models\EmailLog;
use App\Modules\IdentityAccess\Models\User;

final class CommunicationCommandService
{
    public function __construct(
        private readonly CommunicationAttachmentService $attachmentService,
        private readonly CommunicationAuditService $auditService,
        private readonly CommunicationStatusProjector $statusProjector,
        private readonly CommunicationCorrelationService $correlationService,
    ) {
    }

    public function queueSms(User $actor, Client $client, array $payload, string $correlationId): array
    {
        return DB::transaction(function () use ($actor, $client, $payload, $correlationId): array {
            $messageId = (string) Str::uuid();
            $participantKey = $this->normalize((string) ($payload['toPhone'] ?? $client->primary_phone ?? ''));
            $thread = $this->firstOrCreateThread($client, 'sms', $participantKey, null, (string) $actor->id);

            $message = CommunicationMessage::query()->create([
                'id' => $messageId,
                'tenant_id' => (string) $client->tenant_id,
                'client_id' => (string) $client->id,
                'communication_thread_id' => (string) $thread->id,
                'channel' => !empty($payload['attachments']) ? 'mms' : 'sms',
                'direction' => 'outbound',
                'lifecycle_status' => 'queued',
                'provider_name' => 'twilio',
                'from_address' => config('services.twilio.from_number'),
                'to_address' => $participantKey,
                'body_text' => $payload['body'] ?? null,
                'idempotency_key' => $payload['idempotencyKey'] ?? null,
                'correlation_key' => (string) Str::uuid(),
                'queued_at' => now(),
                'created_by' => (string) $actor->id,
            ]);

            $attachments = $this->attachmentService->storeForMessage(
                client: $client,
                subjectType: CommunicationMessage::class,
                subjectId: (string) $message->id,
                channel: (string) $message->channel,
                files: array_values(array_filter((array) ($payload['attachments'] ?? []), fn ($file) => $file instanceof UploadedFile)),
                uploadedBy: (string) $actor->id,
            );

            $this->appendEvent((string) $client->tenant_id, (string) $client->id, 'communication_message', (string) $message->id, 'internal_queued', 'queued', $message->correlation_key, true, ['kind' => 'sms_queued']);
            $this->auditService->record($actor, (string) $client->tenant_id, 'communication.sms.queued', 'communication_message', (string) $message->id, $correlationId, [
                'channel' => (string) $message->channel,
                'toAddress' => (string) $message->to_address,
            ]);

            dispatch(new SubmitOutboundSmsJob((string) $client->tenant_id, $correlationId, (string) $message->id));

            return $this->presentMessage($message, $attachments, true);
        });
    }

    public function queueEmail(User $actor, Client $client, array $payload, string $correlationId): array
    {
        return DB::transaction(function () use ($actor, $client, $payload, $correlationId): array {
            $messageId = (string) Str::uuid();
            $primaryRecipient = strtolower((string) (($payload['to'][0] ?? $client->primary_email ?? '')));
            $thread = $this->firstOrCreateThread($client, 'email', $primaryRecipient, (string) ($payload['subject'] ?? null), (string) $actor->id);

            $message = CommunicationMessage::query()->create([
                'id' => $messageId,
                'tenant_id' => (string) $client->tenant_id,
                'client_id' => (string) $client->id,
                'communication_thread_id' => (string) $thread->id,
                'channel' => 'email',
                'direction' => 'outbound',
                'lifecycle_status' => 'queued',
                'provider_name' => 'sendgrid',
                'from_address' => config('services.sendgrid.from_email'),
                'to_address' => $primaryRecipient,
                'subject' => $payload['subject'],
                'body_text' => $payload['bodyText'] ?? null,
                'body_html' => $payload['bodyHtml'] ?? null,
                'idempotency_key' => $payload['idempotencyKey'] ?? null,
                'correlation_key' => (string) Str::uuid(),
                'queued_at' => now(),
                'created_by' => (string) $actor->id,
            ]);

            $attachments = $this->attachmentService->storeForMessage(
                client: $client,
                subjectType: CommunicationMessage::class,
                subjectId: (string) $message->id,
                channel: 'email',
                files: array_values(array_filter((array) ($payload['attachments'] ?? []), fn ($file) => $file instanceof UploadedFile)),
                uploadedBy: (string) $actor->id,
            );

            EmailLog::query()->create([
                'id' => (string) Str::uuid(),
                'tenant_id' => (string) $client->tenant_id,
                'client_id' => (string) $client->id,
                'communication_message_id' => (string) $message->id,
                'provider_name' => 'sendgrid',
                'from_email' => config('services.sendgrid.from_email'),
                'to_emails' => array_values((array) ($payload['to'] ?? [])),
                'cc_emails' => array_values((array) ($payload['cc'] ?? [])) ?: null,
                'bcc_emails' => array_values((array) ($payload['bcc'] ?? [])) ?: null,
                'reply_to_email' => config('services.sendgrid.from_email'),
                'provider_metadata' => [
                    'retryOfMessageId' => $payload['retryOfMessageId'] ?? null,
                ],
            ]);

            $this->appendEvent((string) $client->tenant_id, (string) $client->id, 'communication_message', (string) $message->id, 'internal_queued', 'queued', $message->correlation_key, true, ['kind' => 'email_queued']);
            $this->auditService->record($actor, (string) $client->tenant_id, 'communication.email.queued', 'communication_message', (string) $message->id, $correlationId, [
                'toCount' => count((array) ($payload['to'] ?? [])),
                'subject' => (string) $message->subject,
            ]);

            dispatch(new SubmitOutboundEmailJob((string) $client->tenant_id, $correlationId, (string) $message->id));

            return $this->presentMessage($message, $attachments, true);
        });
    }

    public function queueCall(User $actor, Client $client, array $payload, string $correlationId): array
    {
        return DB::transaction(function () use ($actor, $client, $payload, $correlationId): array {
            $callLog = CallLog::query()->create([
                'id' => (string) Str::uuid(),
                'tenant_id' => (string) $client->tenant_id,
                'client_id' => (string) $client->id,
                'direction' => 'outbound',
                'lifecycle_status' => 'queued',
                'provider_name' => 'twilio',
                'from_number' => config('services.twilio.voice_from_number'),
                'to_number' => $payload['toPhone'] ?? $client->primary_phone,
                'correlation_key' => (string) Str::uuid(),
                'queued_at' => now(),
                'initiated_by' => (string) $actor->id,
            ]);

            $this->appendEvent((string) $client->tenant_id, (string) $client->id, 'call_log', (string) $callLog->id, 'internal_queued', 'queued', $callLog->correlation_key, true, ['kind' => 'call_queued']);
            $this->auditService->record($actor, (string) $client->tenant_id, 'communication.call.queued', 'call_log', (string) $callLog->id, $correlationId, [
                'toNumber' => (string) $callLog->to_number,
                'purposeNote' => $payload['purposeNote'] ?? null,
            ]);

            dispatch(new InitiateOutboundCallJob((string) $client->tenant_id, $correlationId, (string) $callLog->id, $payload['purposeNote'] ?? null));

            return $this->presentCall($callLog, true);
        });
    }

    private function firstOrCreateThread(Client $client, string $channel, ?string $participantKey, ?string $subjectHint, ?string $createdBy): CommunicationThread
    {
        return CommunicationThread::query()->firstOrCreate([
            'tenant_id' => (string) $client->tenant_id,
            'client_id' => (string) $client->id,
            'channel' => $channel,
            'participant_key' => $participantKey,
        ], [
            'id' => (string) Str::uuid(),
            'subject_hint' => $subjectHint,
            'created_by' => $createdBy,
            'last_activity_at' => now(),
        ]);
    }

    public function appendEvent(
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
    ): void {
        DeliveryStatusEvent::query()->create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
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
            'dedupe_hash' => $this->correlationService->dedupeHash([
                'tenantId' => $tenantId,
                'subjectType' => $subjectType,
                'subjectId' => $subjectId,
                'eventType' => $eventType,
                'providerReference' => $providerReference,
                'providerEventId' => $providerEventId,
                'providerStatus' => $providerStatus,
                'rawPayload' => $rawPayload,
            ]),
            'raw_payload' => $rawPayload,
            'status_before' => $statusBefore,
            'status_after' => $statusAfter,
        ]);
    }

    public function presentMessage(CommunicationMessage $message, array $attachments = [], bool $includeStatus = false): array
    {
        return [
            'id' => (string) $message->id,
            'kind' => 'message',
            'channel' => (string) $message->channel,
            'direction' => (string) $message->direction,
            'occurredAt' => $message->created_at?->toIso8601String(),
            'counterpart' => [
                'name' => null,
                'address' => (string) ($message->direction === 'outbound' ? $message->to_address : $message->from_address),
            ],
            'content' => [
                'subject' => $message->subject,
                'bodyText' => $message->body_text,
                'preview' => $message->body_text ? mb_substr((string) $message->body_text, 0, 140) : $message->subject,
            ],
            'attachments' => $attachments,
            'status' => $includeStatus ? $this->statusProjector->project((string) $message->lifecycle_status, $message->provider_status, $message->failure_code, $message->failure_message, $message->provider_message_id ? 'provider_submit' : 'internal', $message->updated_at?->toIso8601String()) : null,
            'evidence' => [
                'source' => $message->provider_message_id ? 'provider_submit' : 'internal',
                'lastEventAt' => $message->updated_at?->toIso8601String(),
                'lastEventType' => null,
                'eventCount' => 0,
            ],
            'call' => null,
            'actions' => [
                'canRetry' => in_array((string) $message->lifecycle_status, ['failed', 'undelivered', 'bounced', 'dropped'], true),
            ],
        ];
    }

    public function presentCall(CallLog $callLog, bool $includeStatus = false): array
    {
        return [
            'id' => (string) $callLog->id,
            'kind' => 'call',
            'channel' => 'voice',
            'direction' => (string) $callLog->direction,
            'occurredAt' => $callLog->created_at?->toIso8601String(),
            'counterpart' => [
                'name' => null,
                'address' => (string) ($callLog->direction === 'outbound' ? $callLog->to_number : $callLog->from_number),
            ],
            'content' => [
                'subject' => 'Call initiated',
                'bodyText' => null,
                'preview' => 'Outbound call activity',
            ],
            'attachments' => [],
            'status' => $includeStatus ? $this->statusProjector->project((string) $callLog->lifecycle_status, null, $callLog->failure_code, $callLog->failure_message, $callLog->provider_call_id ? 'provider_submit' : 'internal', $callLog->updated_at?->toIso8601String()) : null,
            'evidence' => [
                'source' => $callLog->provider_call_id ? 'provider_submit' : 'internal',
                'lastEventAt' => $callLog->updated_at?->toIso8601String(),
                'lastEventType' => null,
                'eventCount' => 0,
            ],
            'call' => [
                'durationSeconds' => $callLog->duration_seconds,
            ],
            'actions' => [
                'canRetry' => in_array((string) $callLog->lifecycle_status, ['failed', 'busy', 'no_answer', 'canceled'], true),
            ],
        ];
    }

    private function normalize(string $value): string
    {
        return preg_replace('/\\s+/', '', trim($value)) ?: $value;
    }
}
