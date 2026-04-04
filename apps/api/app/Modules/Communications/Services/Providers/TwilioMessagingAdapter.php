<?php

declare(strict_types=1);

namespace App\Modules\Communications\Services\Providers;

use Illuminate\Support\Facades\Http;
use RuntimeException;
use App\Modules\Communications\Contracts\SmsTransportProvider;
use App\Modules\Communications\DTOs\ProviderSubmissionResultData;
use App\Modules\Communications\Models\CommunicationAttachment;
use App\Modules\Communications\Models\CommunicationMessage;
use App\Modules\Communications\Services\CommunicationAttachmentUrlService;

final class TwilioMessagingAdapter implements SmsTransportProvider
{
    public function __construct(
        private readonly CommunicationAttachmentUrlService $communicationAttachmentUrlService,
    ) {
    }

    public function send(CommunicationMessage $message): ProviderSubmissionResultData
    {
        $sid = (string) config('services.twilio.sid');
        $authToken = (string) config('services.twilio.auth_token');
        $messagingServiceSid = config('services.twilio.messaging_service_sid');
        $fromNumber = config('services.twilio.from_number');

        if ($sid === '' || $authToken === '' || (($messagingServiceSid === null || $messagingServiceSid === '') && ($fromNumber === null || $fromNumber === ''))) {
            throw new RuntimeException('Twilio messaging configuration is incomplete.');
        }

        $payload = [
            'To' => (string) $message->to_address,
            'Body' => (string) ($message->body_text ?? ''),
            'StatusCallback' => route('webhooks.twilio.messaging', ['messageId' => $message->id, 'tenantId' => $message->tenant_id]),
        ];

        if ($messagingServiceSid) {
            $payload['MessagingServiceSid'] = (string) $messagingServiceSid;
        } else {
            $payload['From'] = (string) ($message->from_address ?: $fromNumber);
        }

        $mediaUrls = CommunicationAttachment::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $message->tenant_id)
            ->where('attachable_type', CommunicationMessage::class)
            ->where('attachable_id', $message->id)
            ->get()
            ->map(fn (CommunicationAttachment $attachment): string => $this->communicationAttachmentUrlService->temporaryPublicUrl($attachment))
            ->values()
            ->all();

        foreach ($mediaUrls as $index => $mediaUrl) {
            $payload[sprintf('MediaUrl%d', $index)] = (string) $mediaUrl;
        }

        $response = Http::asForm()->withBasicAuth($sid, $authToken)->post(sprintf('https://api.twilio.com/2010-04-01/Accounts/%s/Messages.json', $sid), $payload);
        $json = $response->json();

        return new ProviderSubmissionResultData(
            providerName: 'twilio',
            accepted: $response->successful(),
            providerReference: is_array($json) ? ($json['sid'] ?? null) : null,
            providerStatus: is_array($json) ? ($json['status'] ?? null) : null,
            rawResponse: is_array($json) ? $json : ['body' => $response->body()],
            failureCode: $response->successful() ? null : (string) (is_array($json) ? ($json['code'] ?? '') : $response->status()),
            failureMessage: $response->successful() ? null : (string) (is_array($json) ? ($json['message'] ?? 'Twilio messaging request failed.') : $response->body()),
        );
    }
}
