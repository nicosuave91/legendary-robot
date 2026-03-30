<?php

declare(strict_types=1);

namespace App\Modules\Communications\Http\Controllers\Webhooks;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Modules\Communications\Models\CommunicationMessage;
use App\Modules\Communications\Models\EmailLog;
use App\Modules\Communications\Services\CommunicationAuditService;
use App\Modules\Communications\Services\CommunicationCommandService;

final class SendGridEventsWebhookController extends Controller
{
    public function __invoke(Request $request, CommunicationCommandService $commandService, CommunicationAuditService $auditService): JsonResponse
    {
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
            $nextStatus = match ($providerEvent) {
                'processed', 'deferred' => 'delivery_pending',
                'delivered' => 'delivered',
                'bounce' => 'bounced',
                'dropped' => 'dropped',
                'open', 'click' => 'delivered',
                'spamreport' => 'failed',
                default => $providerEvent !== '' ? $providerEvent : $statusBefore,
            };

            $message->provider_status = $providerEvent;
            $message->lifecycle_status = $nextStatus;
            if (in_array($nextStatus, ['delivered', 'bounced', 'dropped', 'failed'], true)) {
                $message->finalized_at = now();
            }
            $message->save();

            if ($emailLog !== null) {
                $emailLog->forceFill([
                    'last_provider_event_at' => now(),
                    'provider_message_id' => (string) ($emailLog->provider_message_id ?: Arr::get($event, 'sg_message_id')),
                    'provider_metadata' => array_merge((array) $emailLog->provider_metadata, ['lastEvent' => $event]),
                ])->save();
            }

            $commandService->appendEvent((string) $message->tenant_id, (string) $message->client_id, 'communication_message', (string) $message->id, 'sendgrid.event.callback', $providerEvent, (string) $message->correlation_key, true, $event, 'sendgrid', (string) Arr::get($event, 'sg_message_id'), (string) Str::uuid(), $statusBefore, $nextStatus);
            $auditService->record(null, (string) $message->tenant_id, 'communication.email.callback_processed', 'communication_message', (string) $message->id, (string) Str::uuid(), ['providerEvent' => $providerEvent, 'statusAfter' => $nextStatus]);
        }

        return response()->json(['ok' => true]);
    }
}
