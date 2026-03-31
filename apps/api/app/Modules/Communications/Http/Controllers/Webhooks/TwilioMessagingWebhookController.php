<?php

declare(strict_types=1);

namespace App\Modules\Communications\Http\Controllers\Webhooks;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Modules\Communications\Models\CommunicationMessage;
use App\Modules\Communications\Services\CommunicationAuditService;
use App\Modules\Communications\Services\CommunicationCommandService;

final class TwilioMessagingWebhookController extends Controller
{
    public function __invoke(Request $request, CommunicationCommandService $commandService, CommunicationAuditService $auditService): JsonResponse
    {
        $messageId = (string) $request->query('messageId', '');
        $tenantId = (string) $request->query('tenantId', '');
        $message = CommunicationMessage::query()->withoutGlobalScopes()->where('tenant_id', $tenantId)->where('id', $messageId)->first();

        if ($message === null) {
            return response()->json(['ok' => true], 202);
        }

        $statusBefore = (string) $message->lifecycle_status;
        $providerStatus = (string) ($request->input('MessageStatus') ?: $request->input('SmsStatus') ?: 'received');
        $nextStatus = match ($providerStatus) {
            'queued', 'accepted', 'sending' => 'delivery_pending',
            'sent' => 'submitted',
            'delivered' => 'delivered',
            'received' => 'received',
            'undelivered' => 'undelivered',
            'failed' => 'failed',
            default => $providerStatus,
        };

        if ((string) $request->input('SmsSid') !== '') {
            $message->provider_message_id = (string) $request->input('SmsSid');
        }

        $message->provider_status = $providerStatus;
        $message->lifecycle_status = $nextStatus;
        if (in_array($nextStatus, ['delivered', 'received', 'undelivered', 'failed'], true)) {
            $message->finalized_at = now();
        }
        $message->save();

        $commandService->appendEvent((string) $message->tenant_id, (string) $message->client_id, 'communication_message', (string) $message->id, 'twilio.messaging.callback', $providerStatus, (string) $message->correlation_key, true, $request->all(), 'twilio', (string) ($request->input('SmsSid') ?: $message->provider_message_id), (string) Str::uuid(), $statusBefore, $nextStatus);
        $auditService->record(null, (string) $message->tenant_id, 'communication.sms.callback_processed', 'communication_message', (string) $message->id, (string) Str::uuid(), ['providerStatus' => $providerStatus, 'statusAfter' => $nextStatus]);

        return response()->json(['ok' => true]);
    }
}
