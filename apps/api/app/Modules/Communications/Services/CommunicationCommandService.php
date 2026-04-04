<?php

declare(strict_types=1);

namespace App\Modules\Communications\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
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
        private readonly CommunicationDeliveryEventService $deliveryEventService,
        private readonly CommunicationMailboxService $communicationMailboxService,
    ) {
    }

    public function queueSms(User $actor, Client $client, array $payload, string $correlationId): array
    {
        return DB::transaction(function () use ($actor, $client, $payload, $correlationId): array {
            $messageId = (string) Str::uuid();
            $participantKey = $this->normalizePhone((string) ($payload['toPhone'] ?? $client->primary_phone ?? ''));
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
            $idempotencyKey = $this->resolveIdempotencyKey($payload['idempotencyKey'] ?? null);
            if ($idempotencyKey !== null) {
                $existingMessage = $this->findExistingEmailByIdempotencyKey($client, $idempotencyKey);
                if ($existingMessage !== null) {
                    return $this->presentMessage($existingMessage, $this->attachmentService->serializeForMessage($existingMessage), true);
                }
            }

            $retryContext = $this->resolveRetryEmailContext($client, (string) ($payload['retryOfMessageId'] ?? ''));
            $retrySource = $retryContext['message'] ?? null;
            $retryEmailLog = $retryContext['emailLog'] ?? null;

            $toEmails = array_values(array_unique(array_map('strtolower', array_filter(array_map(
                fn (mixed $value): string => trim((string) $value),
                (array) ($payload['to'] ?? ($retryEmailLog?->to_emails ?? [])),
            )))));

            if ($toEmails === []) {
                throw ValidationException::withMessages([
                    'to' => ['At least one recipient email address is required.'],
                ]);
            }

            $subject = trim((string) ($payload['subject'] ?? $retrySource?->subject ?? ''));
            if ($subject === '') {
                throw ValidationException::withMessages([
                    'subject' => ['A subject is required when no retry source is supplied.'],
                ]);
            }

            $bodyText = array_key_exists('bodyText', $payload)
                ? (string) ($payload['bodyText'] ?? '')
                : (string) ($retrySource?->body_text ?? '');

            $bodyHtml = array_key_exists('bodyHtml', $payload)
                ? (string) ($payload['bodyHtml'] ?? '')
                : (string) ($retrySource?->body_html ?? '');

            if (trim($bodyText) === '' && trim($bodyHtml) === '') {
                throw ValidationException::withMessages([
                    'bodyText' => ['A plain-text body, HTML body, or retry source is required.'],
                ]);
            }

            $messageId = (string) Str::uuid();
            $primaryRecipient = strtolower((string) $toEmails[0]);
            $thread = $this->firstOrCreateThread($client, 'email', $primaryRecipient, $subject, (string) $actor->id);

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
                'subject' => $subject,
                'body_text' => trim($bodyText) !== '' ? $bodyText : null,
                'body_html' => trim($bodyHtml) !== '' ? $bodyHtml : null,
                'idempotency_key' => $idempotencyKey,
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

            if ($retrySource !== null && empty((array) ($payload['attachments'] ?? []))) {
                $attachments = $this->attachmentService->cloneMessageAttachments(
                    client: $client,
                    sourceMessage: $retrySource,
                    targetSubjectType: CommunicationMessage::class,
                    targetSubjectId: (string) $message->id,
                    uploadedBy: (string) $actor->id,
                );
            }

            $replyToAddress = $this->communicationMailboxService->replyToAddressForThread($client, $thread, (string) $actor->id)
                ?? config('services.sendgrid.from_email');

            EmailLog::query()->create([
                'id' => (string) Str::uuid(),
                'tenant_id' => (string) $client->tenant_id,
                'client_id' => (string) $client->id,
                'communication_message_id' => (string) $message->id,
                'provider_name' => 'sendgrid',
                'from_email' => config('services.sendgrid.from_email'),
                'to_emails' => $toEmails,
                'cc_emails' => array_values((array) ($payload['cc'] ?? ($retryEmailLog?->cc_emails ?? []))) ?: null,
                'bcc_emails' => array_values((array) ($payload['bcc'] ?? ($retryEmailLog?->bcc_emails ?? []))) ?: null,
                'reply_to_email' => $replyToAddress,
                'provider_metadata' => [
                    'retryOfMessageId' => $retrySource?->id,
                    'replyMailboxAddress' => $replyToAddress,
                    'replyThreadId' => (string) $thread->id,
                ],
            ]);

            $this->appendEvent((string) $client->tenant_id, (string) $client->id, 'communication_message', (string) $message->id, 'internal_queued', 'queued', $message->correlation_key, true, ['kind' => $retrySource !== null ? 'email_retry_queued' : 'email_queued']);
            $this->auditService->record($actor, (string) $client->tenant_id, 'communication.email.queued', 'communication_message', (string) $message->id, $correlationId, [
                'toCount' => count($toEmails),
                'subject' => (string) $message->subject,
                'retryOfMessageId' => $retrySource?->id,
                'idempotencyKey' => $idempotencyKey,
                'replyToEmail' => $replyToAddress,
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

    private function resolveIdempotencyKey(mixed $candidate): ?string
    {
        $value = trim((string) $candidate);

        return $value !== '' ? $value : null;
    }

    private function findExistingEmailByIdempotencyKey(Client $client, string $idempotencyKey): ?CommunicationMessage
    {
        return CommunicationMessage::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $client->tenant_id)
            ->where('client_id', $client->id)
            ->where('channel', 'email')
            ->where('idempotency_key', $idempotencyKey)
            ->first();
    }

    /**
     * @return array{message:CommunicationMessage|null,emailLog:EmailLog|null}
     */
    private function resolveRetryEmailContext(Client $client, string $retryOfMessageId): array
    {
        if (trim($retryOfMessageId) === '') {
            return ['message' => null, 'emailLog' => null];
        }

        $message = CommunicationMessage::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $client->tenant_id)
            ->where('client_id', $client->id)
            ->where('id', $retryOfMessageId)
            ->where('direction', 'outbound')
            ->where('channel', 'email')
            ->first();

        if ($message === null) {
            throw ValidationException::withMessages([
                'retryOfMessageId' => ['The referenced outbound email could not be found for this client.'],
            ]);
        }

        if (!in_array((string) $message->lifecycle_status, ['failed', 'undelivered', 'bounced', 'dropped'], true)) {
            throw ValidationException::withMessages([
                'retryOfMessageId' => ['Only failed outbound emails can be resent.'],
            ]);
        }

        $emailLog = EmailLog::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $client->tenant_id)
            ->where('communication_message_id', $message->id)
            ->first();

        return ['message' => $message, 'emailLog' => $emailLog];
    }

    /**
     * @param array<string, mixed> $rawPayload
     */
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
    ): DeliveryStatusEvent {
        return $this->deliveryEventService->record(
            tenantId: $tenantId,
            clientId: $clientId,
            subjectType: $subjectType,
            subjectId: $subjectId,
            eventType: $eventType,
            providerStatus: $providerStatus,
            correlationKey: $correlationKey,
            signatureVerified: $signatureVerified,
            rawPayload: $rawPayload,
            providerName: $providerName,
            providerReference: $providerReference,
            providerEventId: $providerEventId,
            statusBefore: $statusBefore,
            statusAfter: $statusAfter,
        );
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

    private function normalizePhone(string $value): string
    {
        return preg_replace('/\s+/', '', trim($value)) ?: $value;
    }
}
