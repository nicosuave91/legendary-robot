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
use App\Modules\Communications\Models\EmailLog;
use App\Modules\Communications\Services\CommunicationAttachmentService;
use App\Modules\Communications\Services\CommunicationAuditService;
use App\Modules\Communications\Services\CommunicationCommandService;
use App\Modules\Communications\Services\CommunicationMailboxService;
use App\Modules\Communications\Services\Webhooks\CommunicationsWebhookTrustService;

final class SendGridInboundWebhookController extends Controller
{
    public function __invoke(
        Request $request,
        CommunicationCommandService $commandService,
        CommunicationAttachmentService $attachmentService,
        CommunicationAuditService $auditService,
        CommunicationsWebhookTrustService $trustService,
        CommunicationMailboxService $communicationMailboxService,
    ): JsonResponse {
        $trust = $trustService->verifySendGrid($request);
        if (!$trust->accepted) {
            return response()->json(['message' => 'Webhook verification failed.', 'reason' => $trust->failureReason], 401);
        }

        $route = $communicationMailboxService->resolveInboundRoute((string) $request->input('to', ''));

        if ($route === null) {
            $tenantId = (string) $request->input('tenant_id', '');
            $clientId = (string) $request->input('client_id', '');
            $client = Client::query()->withoutGlobalScopes()->where('tenant_id', $tenantId)->where('id', $clientId)->first();

            if ($client !== null) {
                $route = [
                    'tenantId' => (string) $tenantId,
                    'client' => $client,
                    'thread' => null,
                    'mailbox' => null,
                    'resolvedBy' => 'legacy_request_fields',
                ];
            }
        }

        if ($route === null) {
            return response()->json(['ok' => true], 202);
        }

        /** @var Client $client */
        $client = $route['client'];
        $tenantId = (string) $route['tenantId'];
        /** @var CommunicationThread|null $thread */
        $thread = $route['thread'] ?? null;
        $mailbox = $route['mailbox'] ?? null;
        $resolvedBy = (string) ($route['resolvedBy'] ?? 'mailbox_alias');

        $fromAddress = trim((string) $request->input('from', ''));
        $participantKey = strtolower($communicationMailboxService->extractEmailAddresses($fromAddress)[0] ?? $fromAddress);
        $subject = (string) $request->input('subject', '');
        $toAddresses = $communicationMailboxService->extractEmailAddresses((string) $request->input('to', ''));

        if ($thread === null) {
            $thread = CommunicationThread::query()->firstOrCreate([
                'tenant_id' => (string) $client->tenant_id,
                'client_id' => (string) $client->id,
                'channel' => 'email',
                'participant_key' => $participantKey,
            ], [
                'id' => (string) Str::uuid(),
                'subject_hint' => $subject,
                'created_by' => null,
                'last_activity_at' => now(),
            ]);
        }

        $providerMessageId = $this->extractProviderMessageId($request);

        $message = CommunicationMessage::query()
            ->withoutGlobalScopes()
            ->firstOrCreate([
                'tenant_id' => (string) $client->tenant_id,
                'provider_name' => 'sendgrid',
                'provider_message_id' => $providerMessageId,
            ], [
                'id' => (string) Str::uuid(),
                'client_id' => (string) $client->id,
                'communication_thread_id' => (string) $thread->id,
                'channel' => 'email',
                'direction' => 'inbound',
                'lifecycle_status' => 'received',
                'provider_status' => 'received',
                'from_address' => $fromAddress,
                'to_address' => implode(', ', $toAddresses),
                'subject' => $subject,
                'body_text' => (string) $request->input('text', ''),
                'body_html' => (string) $request->input('html', ''),
                'correlation_key' => (string) Str::uuid(),
                'submitted_at' => now(),
                'finalized_at' => now(),
                'queued_at' => now(),
            ]);

        $files = $this->flattenUploadedFiles($request);
        if ($message->wasRecentlyCreated) {
            $thread->forceFill(['last_activity_at' => now()])->save();
            $attachmentService->storeForMessage($client, CommunicationMessage::class, (string) $message->id, 'email', $files, null, 'inbound_email');
        }

        EmailLog::query()
            ->withoutGlobalScopes()
            ->firstOrCreate([
                'tenant_id' => (string) $client->tenant_id,
                'communication_message_id' => (string) $message->id,
            ], [
                'id' => (string) Str::uuid(),
                'client_id' => (string) $client->id,
                'provider_name' => 'sendgrid',
                'provider_message_id' => $providerMessageId,
                'from_email' => $fromAddress,
                'to_emails' => $toAddresses,
                'cc_emails' => $communicationMailboxService->extractEmailAddresses((string) $request->input('cc', '')) ?: null,
                'bcc_emails' => $communicationMailboxService->extractEmailAddresses((string) $request->input('bcc', '')) ?: null,
                'reply_to_email' => $mailbox?->inbound_address,
                'provider_metadata' => [
                    'headers' => (string) $request->input('headers', ''),
                    'resolvedBy' => $resolvedBy,
                    'mailboxAddress' => $mailbox?->inbound_address,
                ],
                'last_provider_event_at' => now(),
            ]);

        $payload = $this->safePayload($request, count($files));

        $eventRecord = $commandService->appendEvent(
            tenantId: (string) $client->tenant_id,
            clientId: (string) $client->id,
            subjectType: 'communication_message',
            subjectId: (string) $message->id,
            eventType: 'sendgrid.inbound.received',
            providerStatus: 'received',
            correlationKey: (string) $message->correlation_key,
            signatureVerified: $trust->verified,
            rawPayload: $payload,
            providerName: 'sendgrid',
            providerReference: $providerMessageId,
            providerEventId: $providerMessageId,
            statusBefore: $message->wasRecentlyCreated ? null : (string) $message->lifecycle_status,
            statusAfter: 'received',
        );

        if ($message->wasRecentlyCreated && $eventRecord->wasRecentlyCreated) {
            $auditService->record(
                null,
                (string) $client->tenant_id,
                'communication.email.inbound_received',
                'communication_message',
                (string) $message->id,
                (string) Str::uuid(),
                [
                    'fromAddress' => $fromAddress,
                    'resolvedBy' => $resolvedBy,
                    'signatureVerified' => $trust->verified,
                    'webhookTrustMode' => $trust->mode,
                    'mailboxAddress' => $mailbox?->inbound_address,
                    'attachmentCount' => count($files),
                ],
            );
        }

        return response()->json(['ok' => true]);
    }

    private function extractProviderMessageId(Request $request): string
    {
        $headers = (string) $request->input('headers', '');

        if (preg_match('/^Message-ID:\s*(.+)$/mi', $headers, $matches) === 1) {
            return trim((string) $matches[1]);
        }

        return 'sg-inbound-' . hash('sha256', json_encode([
            'from' => $request->input('from'),
            'to' => $request->input('to'),
            'subject' => $request->input('subject'),
            'text' => $request->input('text'),
            'date' => $request->input('date'),
        ], JSON_THROW_ON_ERROR));
    }

    /**
     * @return list<\Illuminate\Http\UploadedFile>
     */
    private function flattenUploadedFiles(Request $request): array
    {
        $files = [];

        foreach ($request->files->all() as $file) {
            if (is_array($file)) {
                foreach ($file as $nestedFile) {
                    if ($nestedFile !== null) {
                        $files[] = $nestedFile;
                    }
                }
            } elseif ($file !== null) {
                $files[] = $file;
            }
        }

        return $files;
    }

    /**
     * @return array<string, mixed>
     */
    private function safePayload(Request $request, int $attachmentCount): array
    {
        $payload = $request->except(array_keys($request->files->all()));
        $payload['attachmentCount'] = $attachmentCount;

        return $payload;
    }
}
