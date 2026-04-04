<?php

declare(strict_types=1);

namespace App\Modules\Communications\Services\Providers;

use Illuminate\Support\Facades\Http;
use RuntimeException;
use App\Modules\Communications\Contracts\VoiceTransportProvider;
use App\Modules\Communications\DTOs\ProviderSubmissionResultData;
use App\Modules\Communications\Models\CallLog;

final class TwilioVoiceAdapter implements VoiceTransportProvider
{
    public function initiate(CallLog $callLog): ProviderSubmissionResultData
    {
        $sid = (string) config('services.twilio.sid');
        $authToken = (string) config('services.twilio.auth_token');
        $fromNumber = (string) config('services.twilio.voice_from_number');

        if ($sid === '' || $authToken === '' || $fromNumber === '') {
            throw new RuntimeException('Twilio voice configuration is incomplete.');
        }

        $payload = [
            'To' => (string) $callLog->to_number,
            'From' => (string) ($callLog->from_number ?: $fromNumber),
            'Url' => route('twiml.twilio.voice.outbound', ['callLogId' => $callLog->id, 'tenantId' => $callLog->tenant_id]),
            'StatusCallback' => route('webhooks.twilio.voice', ['callLogId' => $callLog->id, 'tenantId' => $callLog->tenant_id]),
            'StatusCallbackEvent' => ['initiated', 'ringing', 'answered', 'completed'],
        ];

        $response = Http::asForm()->withBasicAuth($sid, $authToken)->post(sprintf('https://api.twilio.com/2010-04-01/Accounts/%s/Calls.json', $sid), $payload);
        $json = $response->json();

        return new ProviderSubmissionResultData(
            providerName: 'twilio',
            accepted: $response->successful(),
            providerReference: is_array($json) ? ($json['sid'] ?? null) : null,
            providerStatus: is_array($json) ? ($json['status'] ?? null) : null,
            rawResponse: is_array($json) ? $json : ['body' => $response->body()],
            failureCode: $response->successful() ? null : (string) (is_array($json) ? ($json['code'] ?? '') : $response->status()),
            failureMessage: $response->successful() ? null : (string) (is_array($json) ? ($json['message'] ?? 'Twilio voice request failed.') : $response->body()),
        );
    }
}
