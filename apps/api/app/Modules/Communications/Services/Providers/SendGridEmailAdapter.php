<?php

declare(strict_types=1);

namespace App\Modules\Communications\Services\Providers;

use Illuminate\Support\Facades\Http;
use RuntimeException;
use App\Modules\Communications\Contracts\EmailTransportProvider;
use App\Modules\Communications\DTOs\ProviderSubmissionResultData;
use App\Modules\Communications\Models\CommunicationAttachment;
use App\Modules\Communications\Models\CommunicationMessage;
use App\Modules\Communications\Models\EmailLog;

final class SendGridEmailAdapter implements EmailTransportProvider
{
    public function send(CommunicationMessage $message): ProviderSubmissionResultData
    {
        $apiKey = (string) config('services.sendgrid.api_key');
        $fromEmail = (string) config('services.sendgrid.from_email');
        $fromName = (string) config('services.sendgrid.from_name', 'Snowball CRM');

        if ($apiKey === '' || $fromEmail === '') {
            throw new RuntimeException('SendGrid configuration is incomplete.');
        }

        $emailLog = EmailLog::query()->withoutGlobalScopes()->where('communication_message_id', $message->id)->firstOrFail();
        $attachments = CommunicationAttachment::query()->withoutGlobalScopes()
            ->where('tenant_id', $message->tenant_id)
            ->where('attachable_type', CommunicationMessage::class)
            ->where('attachable_id', $message->id)
            ->get();

        $payload = [
            'from' => ['email' => $fromEmail, 'name' => $fromName],
            'personalizations' => [[
                'to' => collect((array) $emailLog->to_emails)->map(fn (string $email): array => ['email' => $email])->values()->all(),
                'cc' => collect((array) ($emailLog->cc_emails ?? []))->map(fn (string $email): array => ['email' => $email])->values()->all(),
                'bcc' => collect((array) ($emailLog->bcc_emails ?? []))->map(fn (string $email): array => ['email' => $email])->values()->all(),
                'custom_args' => array_filter([
                    'tenant_id' => (string) $message->tenant_id,
                    'client_id' => (string) $message->client_id,
                    'message_id' => (string) $message->id,
                    'correlation_key' => (string) $message->correlation_key,
                    'thread_id' => (string) $message->communication_thread_id,
                    'reply_mailbox' => (string) (($emailLog->provider_metadata['replyMailboxAddress'] ?? null) ?: $emailLog->reply_to_email),
                ], fn (mixed $value): bool => $value !== null && $value !== ''),
            ]],
            'subject' => (string) ($message->subject ?? ''),
            'content' => array_values(array_filter([
                $message->body_text ? ['type' => 'text/plain', 'value' => (string) $message->body_text] : null,
                $message->body_html ? ['type' => 'text/html', 'value' => (string) $message->body_html] : null,
            ])),
            'attachments' => $attachments->map(function (CommunicationAttachment $attachment): array {
                return [
                    'filename' => (string) $attachment->original_filename,
                    'type' => (string) $attachment->mime_type,
                    'content' => base64_encode((string) file_get_contents(storage_path('app/' . $attachment->storage_path))),
                    'disposition' => 'attachment',
                ];
            })->values()->all(),
            'reply_to' => ['email' => (string) ($emailLog->reply_to_email ?: $fromEmail)],
        ];

        $response = Http::withToken($apiKey)->acceptJson()->post('https://api.sendgrid.com/v3/mail/send', $payload);

        return new ProviderSubmissionResultData(
            providerName: 'sendgrid',
            accepted: $response->successful(),
            providerReference: $response->header('X-Message-Id'),
            providerStatus: $response->successful() ? 'accepted' : 'rejected',
            rawResponse: ['headers' => $response->headers(), 'body' => $response->body()],
            failureCode: $response->successful() ? null : (string) $response->status(),
            failureMessage: $response->successful() ? null : 'SendGrid email request failed.',
        );
    }
}
