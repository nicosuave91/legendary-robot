<?php

declare(strict_types=1);

namespace App\Modules\Communications\Http\Controllers\Webhooks;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Modules\Communications\Models\CommunicationMessage;
use App\Modules\Communications\Models\CommunicationThread;
use App\Modules\Communications\Services\CommunicationAttachmentService;
use App\Modules\Communications\Services\CommunicationAuditService;
use App\Modules\Communications\Services\CommunicationCommandService;
use App\Modules\Communications\Services\CommunicationEndpointResolver;
use App\Modules\Communications\Services\PhoneNumberNormalizer;
use App\Modules\Communications\Services\Webhooks\CommunicationsWebhookTrustService;

final class TwilioMessagingWebhookController extends Controller
{
    public function __invoke(
        Request $request,
        CommunicationCommandService $commandService,
        CommunicationAuditService $auditService,
        CommunicationsWebhookTrustService $trustService,
        CommunicationEndpointResolver $communicationEndpointResolver,
        CommunicationAttachmentService $communicationAttachmentService,
        PhoneNumberNormalizer $phoneNumberNormalizer,
    ): JsonResponse {
        $trust = $trustService->verifyTwilio($request);
        if (!$trust->accepted) {
            return response()->json(['message' => 'Webhook verification failed.', 'reason' => $trust->failureReason], 401);
        }

        $messageId = (string) $request->query('messageId', '');
        $tenantId = (string) $request->query('tenantId', '');

        if ($messageId !== '' && $tenantId !== '') {
            return $this->handleStatusCallback($request, $messageId, $tenantId, $commandService, $auditService, $trust);
        }

        return $this->handleInboundMessage(
            request: $request,
            commandService: $commandService,
            auditService: $auditService,
            communicationEndpointResolver: $communicationEndpointResolver,
            communicationAttachmentService: $communicationAttachmentService,
            phoneNumberNormalizer: $phoneNumberNormalizer,
            trustVerified: $trust->verified,
            trustMode: $trust->mode,
        );
    }

    private function handleStatusCallback(
        Request $request,
        string $messageId,
        string $tenantId,
        CommunicationCommandService $commandService,
        CommunicationAuditService $auditService,
        object $trust,
    ): JsonResponse {
        $message = CommunicationMessage::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('id', $messageId)
            ->first();

        if ($message === null) {
            return response()->json(['ok' => true], 202);
        }

        $providerStatus = (string) ($request->input('MessageStatus') ?: $request->input('SmsStatus') ?: 'received');

        return DB::transaction(function () use ($request, $message, $providerStatus, $commandService, $auditService, $trust): JsonResponse {
            $statusBefore = (string) $message->lifecycle_status;
            $candidateStatus = match ($providerStatus) {
                'queued', 'accepted', 'sending' => 'delivery_pending',
                'sent' => 'submitted',
                'delivered' => 'delivered',
                'received' => 'received',
                'undelivered' => 'undelivered',
                'failed' => 'failed',
                default => $providerStatus,
            };
            $statusAfter = $this->resolveLifecycleStatus($statusBefore, $candidateStatus);

            $eventRecord = $commandService->appendEvent(
                tenantId: (string) $message->tenant_id,
                clientId: (string) $message->client_id,
                subjectType: 'communication_message',
                subjectId: (string) $message->id,
                eventType: 'twilio.messaging.callback',
                providerStatus: $providerStatus,
                correlationKey: (string) $message->correlation_key,
                signatureVerified: $trust->verified,
                rawPayload: $request->all(),
                providerName: 'twilio',
                providerReference: (string) ($request->input('SmsSid') ?: $message->provider_message_id),
                providerEventId: null,
                statusBefore: $statusBefore,
                statusAfter: $statusAfter,
            );

            if (!$eventRecord->wasRecentlyCreated) {
                return response()->json(['ok' => true]);
            }

            if ((string) $request->input('SmsSid') !== '') {
                $message->provider_message_id = (string) $request->input('SmsSid');
            }

            if ($this->shouldReplaceProviderStatus($statusBefore, $statusAfter)) {
                $message->provider_status = $providerStatus;
            }

            $message->lifecycle_status = $statusAfter;

            if ($this->isTerminalStatus($statusAfter) && $message->finalized_at === null) {
                $message->finalized_at = now();
            }

            $message->save();

            $auditService->record(
                null,
                (string) $message->tenant_id,
                'communication.sms.callback_processed',
                'communication_message',
                (string) $message->id,
                (string) ($message->correlation_key ?: Str::uuid()),
                [
                    'providerStatus' => $providerStatus,
                    'statusAfter' => $statusAfter,
                    'signatureVerified' => $trust->verified,
                    'webhookTrustMode' => $trust->mode,
                ],
            );

            return response()->json(['ok' => true]);
        });
    }

    private function handleInboundMessage(
        Request $request,
        CommunicationCommandService $commandService,
        CommunicationAuditService $auditService,
        CommunicationEndpointResolver $communicationEndpointResolver,
        CommunicationAttachmentService $communicationAttachmentService,
        PhoneNumberNormalizer $phoneNumberNormalizer,
        bool $trustVerified,
        string $trustMode,
    ): JsonResponse {
        $providerMessageId = (string) ($request->input('MessageSid') ?: $request->input('SmsSid') ?: '');
        if ($providerMessageId === '') {
            return response()->json(['ok' => true], 202);
        }

        $route = $communicationEndpointResolver->resolveInboundSmsRoute(
            (string) $request->input('To', ''),
            (string) $request->input('From', ''),
        );

        if ($route === null) {
            return response()->json(['ok' => true], 202);
        }

        $client = $route['client'];
        $tenantId = (string) $route['tenantId'];
        $fromAddress = $phoneNumberNormalizer->normalize((string) $request->input('From')) ?? (string) $request->input('From');
        $toAddress = $phoneNumberNormalizer->normalize((string) $request->input('To')) ?? (string) $request->input('To');
        $channel = ((int) $request->input('NumMedia', 0) > 0) ? 'mms' : 'sms';

        return DB::transaction(function () use (
            $request,
            $client,
            $tenantId,
            $providerMessageId,
            $fromAddress,
            $toAddress,
            $channel,
            $commandService,
            $auditService,
            $communicationAttachmentService,
            $trustVerified,
            $trustMode,
        ): JsonResponse {
            $thread = CommunicationThread::query()->firstOrCreate([
                'tenant_id' => $tenantId,
                'client_id' => (string) $client->id,
                'channel' => $channel,
                'participant_key' => $fromAddress,
            ], [
                'id' => (string) Str::uuid(),
                'subject_hint' => null,
                'created_by' => null,
                'last_activity_at' => now(),
            ]);

            $message = CommunicationMessage::query()
                ->withoutGlobalScopes()
                ->firstOrCreate([
                    'tenant_id' => $tenantId,
                    'provider_name' => 'twilio',
                    'provider_message_id' => $providerMessageId,
                ], [
                    'id' => (string) Str::uuid(),
                    'client_id' => (string) $client->id,
                    'communication_thread_id' => (string) $thread->id,
                    'channel' => $channel,
                    'direction' => 'inbound',
                    'lifecycle_status' => 'received',
                    'provider_status' => 'received',
                    'from_address' => $fromAddress,
                    'to_address' => $toAddress,
                    'body_text' => $request->input('Body'),
                    'body_html' => null,
                    'correlation_key' => (string) Str::uuid(),
                    'submitted_at' => now(),
                    'finalized_at' => now(),
                    'created_by' => null,
                ]);

            $attachments = [];
            if ($message->wasRecentlyCreated) {
                $thread->forceFill(['last_activity_at' => now()])->save();

                $attachments = $communicationAttachmentService->importTwilioInboundMedia(
                    client: $client,
                    message: $message,
                    payload: $request->all(),
                );
            }

            $statusBefore = $message->wasRecentlyCreated ? null : (string) $message->lifecycle_status;
            $eventRecord = $commandService->appendEvent(
                tenantId: (string) $message->tenant_id,
                clientId: (string) $message->client_id,
                subjectType: 'communication_message',
                subjectId: (string) $message->id,
                eventType: 'twilio.messaging.inbound',
                providerStatus: 'received',
                correlationKey: (string) $message->correlation_key,
                signatureVerified: $trustVerified,
                rawPayload: $request->all(),
                providerName: 'twilio',
                providerReference: $providerMessageId,
                providerEventId: $providerMessageId,
                statusBefore: $statusBefore,
                statusAfter: 'received',
            );

            if ($message->wasRecentlyCreated && $eventRecord->wasRecentlyCreated) {
                $auditService->record(
                    null,
                    (string) $message->tenant_id,
                    'communication.sms.inbound_received',
                    'communication_message',
                    (string) $message->id,
                    (string) ($message->correlation_key ?: Str::uuid()),
                    [
                        'channel' => $channel,
                        'fromAddress' => $fromAddress,
                        'toAddress' => $toAddress,
                        'signatureVerified' => $trustVerified,
                        'webhookTrustMode' => $trustMode,
                        'attachmentCount' => count($attachments),
                    ],
                );
            }

            return response()->json(['ok' => true]);
        });
    }

    private function resolveLifecycleStatus(string $currentStatus, string $candidateStatus): string
    {
        if ($candidateStatus === '' || $candidateStatus === $currentStatus) {
            return $currentStatus;
        }

        if ($this->isTerminalStatus($currentStatus)) {
            return $currentStatus;
        }

        if ($this->isTerminalStatus($candidateStatus)) {
            return $candidateStatus;
        }

        return $this->statusRank($candidateStatus) >= $this->statusRank($currentStatus)
            ? $candidateStatus
            : $currentStatus;
    }

    private function shouldReplaceProviderStatus(string $statusBefore, string $statusAfter): bool
    {
        return !($statusBefore === $statusAfter && $this->isTerminalStatus($statusBefore));
    }

    private function isTerminalStatus(string $status): bool
    {
        return in_array($status, ['delivered', 'received', 'undelivered', 'failed', 'bounced', 'dropped'], true);
    }

    private function statusRank(string $status): int
    {
        return match ($status) {
            'queued' => 10,
            'submitting' => 20,
            'submitted' => 30,
            'delivery_pending' => 40,
            'delivered', 'received', 'undelivered', 'failed', 'bounced', 'dropped' => 90,
            default => 50,
        };
    }
}
