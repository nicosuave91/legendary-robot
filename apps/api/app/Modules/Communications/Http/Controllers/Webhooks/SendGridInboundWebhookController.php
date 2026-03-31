<?php

declare(strict_types=1);

namespace App\Modules\Communications\Http\Controllers\Webhooks;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Modules\Clients\Models\Client;
use App\Modules\Communications\Models\CommunicationMessage;
use App\Modules\Communications\Models\CommunicationThread;
use App\Modules\Communications\Services\CommunicationAttachmentService;
use App\Modules\Communications\Services\CommunicationAuditService;
use App\Modules\Communications\Services\CommunicationCommandService;
use App\Modules\Communications\Services\Webhooks\CommunicationsWebhookTrustService;

final class SendGridInboundWebhookController extends Controller
{
    public function __invoke(Request $request, CommunicationCommandService $commandService, CommunicationAttachmentService $attachmentService, CommunicationAuditService $auditService, CommunicationsWebhookTrustService $trustService): JsonResponse
    {
        $trust = $trustService->verifySendGrid($request);
        if (!$trust->accepted) {
            return response()->json(['message' => 'Webhook verification failed.', 'reason' => $trust->failureReason], 401);
        }

        $tenantId = (string) $request->input('tenant_id', '');
        $clientId = (string) $request->input('client_id', '');
        $client = Client::query()->withoutGlobalScopes()->where('tenant_id', $tenantId)->where('id', $clientId)->first();

        if ($client === null) {
            return response()->json(['ok' => true], 202);
        }

        $fromAddress = (string) $request->input('from', '');
        $thread = CommunicationThread::query()->firstOrCreate([
            'tenant_id' => (string) $client->tenant_id,
            'client_id' => (string) $client->id,
            'channel' => 'email',
            'participant_key' => strtolower($fromAddress),
        ], [
            'id' => (string) Str::uuid(),
            'subject_hint' => (string) $request->input('subject', ''),
            'created_by' => null,
            'last_activity_at' => now(),
        ]);

        $message = CommunicationMessage::query()->create([
            'id' => (string) Str::uuid(),
            'tenant_id' => (string) $client->tenant_id,
            'client_id' => (string) $client->id,
            'communication_thread_id' => (string) $thread->id,
            'channel' => 'email',
            'direction' => 'inbound',
            'lifecycle_status' => 'received',
            'provider_name' => 'sendgrid',
            'from_address' => $fromAddress,
            'to_address' => (string) $request->input('to', ''),
            'subject' => (string) $request->input('subject', ''),
            'body_text' => (string) $request->input('text', ''),
            'body_html' => (string) $request->input('html', ''),
            'correlation_key' => (string) Str::uuid(),
            'queued_at' => now(),
        ]);

        $files = [];
        foreach ($request->files->all() as $file) {
            if (is_array($file)) {
                foreach ($file as $nestedFile) {
                    if ($nestedFile !== null) $files[] = $nestedFile;
                }
            } elseif ($file !== null) {
                $files[] = $file;
            }
        }

        $attachmentService->storeForMessage($client, CommunicationMessage::class, (string) $message->id, 'email', $files, null, 'inbound_email');
        $commandService->appendEvent((string) $client->tenant_id, (string) $client->id, 'communication_message', (string) $message->id, 'sendgrid.inbound.received', 'received', (string) $message->correlation_key, $trust->verified, $request->all(), 'sendgrid', null, null, null, 'received');
        $auditService->record(null, (string) $client->tenant_id, 'communication.email.inbound_received', 'communication_message', (string) $message->id, (string) Str::uuid(), ['fromAddress' => $fromAddress, 'signatureVerified' => $trust->verified, 'webhookTrustMode' => $trust->mode]);

        return response()->json(['ok' => true]);
    }
}
