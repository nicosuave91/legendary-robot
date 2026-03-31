<?php

declare(strict_types=1);

namespace App\Modules\Communications\Http\Controllers\Webhooks;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Modules\Communications\Models\CallLog;
use App\Modules\Communications\Services\CommunicationAuditService;
use App\Modules\Communications\Services\CommunicationCommandService;

final class TwilioVoiceWebhookController extends Controller
{
    public function __invoke(Request $request, CommunicationCommandService $commandService, CommunicationAuditService $auditService): JsonResponse
    {
        $callLogId = (string) $request->query('callLogId', '');
        $tenantId = (string) $request->query('tenantId', '');
        $callLog = CallLog::query()->withoutGlobalScopes()->where('tenant_id', $tenantId)->where('id', $callLogId)->first();

        if ($callLog === null) {
            return response()->json(['ok' => true], 202);
        }

        $statusBefore = (string) $callLog->lifecycle_status;
        $providerStatus = (string) ($request->input('CallStatus') ?: 'completed');
        $nextStatus = match ($providerStatus) {
            'initiated' => 'submitted',
            'ringing' => 'ringing',
            'in-progress' => 'in_progress',
            'completed' => 'completed',
            'busy' => 'busy',
            'no-answer' => 'no_answer',
            'canceled' => 'canceled',
            'failed' => 'failed',
            default => $providerStatus,
        };

        if ((string) $request->input('CallSid') !== '') {
            $callLog->provider_call_id = (string) $request->input('CallSid');
        }

        $callLog->lifecycle_status = $nextStatus;
        if ((int) $request->input('CallDuration', 0) > 0) {
            $callLog->duration_seconds = (int) $request->input('CallDuration');
        }
        if (in_array($nextStatus, ['completed', 'busy', 'no_answer', 'canceled', 'failed'], true)) {
            $callLog->ended_at = now();
        }
        $callLog->save();

        $commandService->appendEvent((string) $callLog->tenant_id, (string) $callLog->client_id, 'call_log', (string) $callLog->id, 'twilio.voice.callback', $providerStatus, (string) $callLog->correlation_key, true, $request->all(), 'twilio', (string) ($request->input('CallSid') ?: $callLog->provider_call_id), (string) Str::uuid(), $statusBefore, $nextStatus);
        $auditService->record(null, (string) $callLog->tenant_id, 'communication.call.callback_processed', 'call_log', (string) $callLog->id, (string) Str::uuid(), ['providerStatus' => $providerStatus, 'statusAfter' => $nextStatus]);

        return response()->json(['ok' => true]);
    }
}
