<?php

declare(strict_types=1);

namespace App\Modules\Communications\Http\Controllers\Webhooks;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Modules\Communications\Models\CommunicationMessage;
use App\Modules\Communications\Models\EmailLog;
use App\Modules\Communications\Services\CommunicationAuditService;
use App\Modules\Communications\Services\CommunicationCommandService;
use App\Modules\Communications\Services\Webhooks\CommunicationsWebhookTrustService;

final class SendGridEventsWebhookController extends Controller
{
    public function __invoke(Request $request, CommunicationCommandService $commandService, CommunicationAuditService $auditService, CommunicationsWebhookTrustService $trustService): JsonResponse
    {
        $trust = $trustService->verifySendGrid($request);
        if (!$trust->accepted) {
            return response()->json(['message' => 'Webhook verification failed.', 'reason' => $trust->failureReason], 401);
        }

        foreach ((array) $request->all() as $event) {
            if (!is_array($event)) {
                continue;
            }

            $messageId = (string) Arr::get($event, 'message_id', Arr::get($event, 'messageId', Arr::get($event, 'custom_args.message_id', '')));
            if ($messageId === '') {
                continue;
            }

            $message = CommunicationMessage::query()->withoutGlobalScopes()->where('id', $messageId)->first();
            if ($message === null) {
                continue;
            }

            $emailLog = EmailLog::query()->withoutGlobalScopes()->where('communication_message_id', $message->id)->first();
            $providerEvent = (string) Arr::get($event, 'event', '');
            $statusBefore = (string) $message->lifecycle_status;
            $candidateStatus = match ($providerEvent) {
                'processed', 'deferred' => 'delivery_pending',
                'delivered' => 'delivered',
                'bounce' => 'bounced',
                'dropped' => 'dropped',
                'open', 'click' => 'delivered',
                'spamreport' => 'failed',
                default => $providerEvent !== '' ? $providerEvent : $statusBefore,
            };
            $statusAfter = $this->resolveLifecycleStatus($statusBefore, $candidateStatus);
            $providerReference = (string) Arr::get($event, 'sg_message_id', $emailLog?->provider_message_id);
            $providerEventId = Arr::get($event, 'sg_event_id', Arr::get($event, 'event_id'));

            DB::transaction(function () use ($commandService, $auditService, $trust, $event, $message, $emailLog, $providerEvent, $providerReference, $providerEventId, $statusBefore, $statusAfter): void {
                $eventRecord = $commandService->appendEvent(
                    tenantId: (string) $message->tenant_id,
                    clientId: (string) $message->client_id,
                    subjectType: 'communication_message',
                    subjectId: (string) $message->id,
                    eventType: 'sendgrid.event.callback',
                    providerStatus: $providerEvent,
                    correlationKey: (string) $message->correlation_key,
                    signatureVerified: $trust->verified,
                    rawPayload: $event,
                    providerName: 'sendgrid',
                    providerReference: $providerReference !== '' ? $providerReference : null,
                    providerEventId: is_string($providerEventId) && $providerEventId !== '' ? $providerEventId : null,
                    statusBefore: $statusBefore,
                    statusAfter: $statusAfter,
                );

                if (!$eventRecord->wasRecentlyCreated) {
                    return;
                }

                if ($this->shouldReplaceProviderStatus($statusBefore, $statusAfter)) {
                    $message->provider_status = $providerEvent;
                }

                $message->lifecycle_status = $statusAfter;
                if ($this->isTerminalStatus($statusAfter) && $message->finalized_at === null) {
                    $message->finalized_at = now();
                }
                $message->save();

                if ($emailLog !== null) {
                    $emailLog->forceFill([
                        'last_provider_event_at' => now(),
                        'provider_message_id' => (string) ($emailLog->provider_message_id ?: $providerReference),
                        'provider_metadata' => array_merge((array) $emailLog->provider_metadata, ['lastEvent' => $event]),
                    ])->save();
                }

                $auditService->record(
                    null,
                    (string) $message->tenant_id,
                    'communication.email.callback_processed',
                    'communication_message',
                    (string) $message->id,
                    (string) ($message->correlation_key ?: Str::uuid()),
                    [
                        'providerEvent' => $providerEvent,
                        'statusAfter' => $statusAfter,
                        'signatureVerified' => $trust->verified,
                        'webhookTrustMode' => $trust->mode,
                    ],
                );
            });
        }

        return response()->json(['ok' => true]);
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
        return in_array($status, ['delivered', 'received', 'completed', 'failed', 'undelivered', 'bounced', 'dropped', 'busy', 'no_answer', 'canceled'], true);
    }

    private function statusRank(string $status): int
    {
        return match ($status) {
            'queued' => 10,
            'submitting' => 20,
            'submitted' => 30,
            'delivery_pending' => 40,
            'delivered', 'received', 'completed', 'failed', 'undelivered', 'bounced', 'dropped', 'busy', 'no_answer', 'canceled' => 90,
            default => 50,
        };
    }
}
