<?php

declare(strict_types=1);

namespace App\Modules\Communications\Http\Controllers\Webhooks;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Modules\Communications\Models\CommunicationMessage;
use App\Modules\Communications\Services\CommunicationAuditService;
use App\Modules\Communications\Services\CommunicationCommandService;
use App\Modules\Communications\Services\Webhooks\CommunicationsWebhookTrustService;

final class TwilioMessagingWebhookController extends Controller
{
    public function __invoke(Request $request, CommunicationCommandService $commandService, CommunicationAuditService $auditService, CommunicationsWebhookTrustService $trustService): JsonResponse
    {
        $trust = $trustService->verifyTwilio($request);
        if (!$trust->accepted) {
            return response()->json(['message' => 'Webhook verification failed.', 'reason' => $trust->failureReason], 401);
        }

        $messageId = (string) $request->query('messageId', '');
        $tenantId = (string) $request->query('tenantId', '');
        $message = CommunicationMessage::query()->withoutGlobalScopes()->where('tenant_id', $tenantId)->where('id', $messageId)->first();

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
