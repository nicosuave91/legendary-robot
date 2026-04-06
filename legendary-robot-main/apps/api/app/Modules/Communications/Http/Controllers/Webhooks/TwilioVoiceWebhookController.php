<?php

declare(strict_types=1);

namespace App\Modules\Communications\Http\Controllers\Webhooks;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Modules\Communications\Models\CallLog;
use App\Modules\Communications\Services\CommunicationAuditService;
use App\Modules\Communications\Services\CommunicationCommandService;
use App\Modules\Communications\Services\Webhooks\CommunicationsWebhookTrustService;

final class TwilioVoiceWebhookController extends Controller
{
    public function __invoke(Request $request, CommunicationCommandService $commandService, CommunicationAuditService $auditService, CommunicationsWebhookTrustService $trustService): JsonResponse
    {
        $trust = $trustService->verifyTwilio($request);
        if (!$trust->accepted) {
            return response()->json(['message' => 'Webhook verification failed.', 'reason' => $trust->failureReason], 401);
        }

        $callLogId = (string) $request->query('callLogId', '');
        $tenantId = (string) $request->query('tenantId', '');
        $callLog = CallLog::query()->withoutGlobalScopes()->where('tenant_id', $tenantId)->where('id', $callLogId)->first();

        if ($callLog === null) {
            return response()->json(['ok' => true], 202);
        }

        $providerStatus = (string) ($request->input('CallStatus') ?: 'completed');

        return DB::transaction(function () use ($request, $callLog, $providerStatus, $commandService, $auditService, $trust): JsonResponse {
            $statusBefore = (string) $callLog->lifecycle_status;
            $candidateStatus = match ($providerStatus) {
                'initiated' => 'submitted',
                'ringing' => 'ringing',
                'answered', 'in-progress' => 'in_progress',
                'completed' => 'completed',
                'busy' => 'busy',
                'no-answer' => 'no_answer',
                'canceled' => 'canceled',
                'failed' => 'failed',
                default => $providerStatus,
            };
            $statusAfter = $this->resolveLifecycleStatus($statusBefore, $candidateStatus);

            $eventRecord = $commandService->appendEvent(
                tenantId: (string) $callLog->tenant_id,
                clientId: (string) $callLog->client_id,
                subjectType: 'call_log',
                subjectId: (string) $callLog->id,
                eventType: 'twilio.voice.callback',
                providerStatus: $providerStatus,
                correlationKey: (string) $callLog->correlation_key,
                signatureVerified: $trust->verified,
                rawPayload: $request->all(),
                providerName: 'twilio',
                providerReference: (string) ($request->input('CallSid') ?: $callLog->provider_call_id),
                providerEventId: null,
                statusBefore: $statusBefore,
                statusAfter: $statusAfter,
            );

            if (!$eventRecord->wasRecentlyCreated) {
                return response()->json(['ok' => true]);
            }

            if ((string) $request->input('CallSid') !== '') {
                $callLog->provider_call_id = (string) $request->input('CallSid');
            }

            $callLog->lifecycle_status = $statusAfter;

            if ($statusAfter === 'in_progress') {
                if ($callLog->started_at === null) {
                    $callLog->started_at = now();
                }
                if ($callLog->answered_at === null) {
                    $callLog->answered_at = now();
                }
            }

            if ((int) $request->input('CallDuration', 0) > 0) {
                $callLog->duration_seconds = (int) $request->input('CallDuration');
            }

            if ($this->isTerminalStatus($statusAfter) && $callLog->ended_at === null) {
                $callLog->ended_at = now();
            }

            $callLog->save();

            $auditService->record(
                null,
                (string) $callLog->tenant_id,
                'communication.call.callback_processed',
                'call_log',
                (string) $callLog->id,
                (string) ($callLog->correlation_key ?: Str::uuid()),
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

    private function isTerminalStatus(string $status): bool
    {
        return in_array($status, ['completed', 'busy', 'no_answer', 'canceled', 'failed'], true);
    }

    private function statusRank(string $status): int
    {
        return match ($status) {
            'queued' => 10,
            'submitting' => 20,
            'submitted' => 30,
            'ringing' => 40,
            'in_progress' => 50,
            'completed', 'busy', 'no_answer', 'canceled', 'failed' => 90,
            default => 50,
        };
    }
}
