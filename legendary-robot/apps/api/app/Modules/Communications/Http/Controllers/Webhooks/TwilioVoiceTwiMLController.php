<?php

declare(strict_types=1);

namespace App\Modules\Communications\Http\Controllers\Webhooks;

use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use App\Modules\Communications\Models\CallLog;

final class TwilioVoiceTwiMLController extends Controller
{
    public function outbound(string $callLogId, string $tenantId): Response
    {
        $callLog = CallLog::query()->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('id', $callLogId)
            ->firstOrFail();

        $bridgeNumber = trim((string) ($callLog->bridged_to_number ?: config('communications.voice.bridge.default_agent_number', '')));
        $intro = trim((string) config('communications.voice.bridge.customer_intro_message', 'Please hold while we connect your call.'));
        $missingAgentMessage = trim((string) config('communications.voice.bridge.missing_agent_message', 'We are unable to connect your call at this time.'));

        $xml = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<Response>',
        ];

        if ($intro !== '') {
            $xml[] = '  <Say voice="alice">' . e($intro) . '</Say>';
        }

        if ($bridgeNumber !== '') {
            $xml[] = '  <Dial answerOnBridge="true" callerId="' . e((string) ($callLog->from_number ?? '')) . '" timeout="20">';
            $xml[] = '    <Number url="' . e(route('twiml.twilio.voice.agent_whisper', ['callLogId' => $callLog->id, 'tenantId' => $callLog->tenant_id])) . '">' . e($bridgeNumber) . '</Number>';
            $xml[] = '  </Dial>';
        } else {
            $xml[] = '  <Say voice="alice">' . e($missingAgentMessage) . '</Say>';
            $xml[] = '  <Hangup />';
        }

        $xml[] = '</Response>';

        return response(implode("\n", $xml), 200, ['Content-Type' => 'text/xml; charset=UTF-8']);
    }

    public function agentWhisper(string $callLogId, string $tenantId): Response
    {
        $callLog = CallLog::query()->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('id', $callLogId)
            ->firstOrFail();

        $clientName = $callLog->client?->display_name ?? 'client';
        $purposeNote = trim((string) ($callLog->purpose_note ?? ''));

        $messages = [
            'You are joining a Snowball CRM client call for ' . $clientName . '.',
        ];

        if ($purposeNote !== '') {
            $messages[] = 'Purpose note: ' . $purposeNote . '.';
        }

        $messages[] = 'You will be connected now.';

        $xml = [
            '<?xml version="1.0" encoding="UTF-8"?>',
            '<Response>',
            '  <Say voice="alice">' . e(implode(' ', $messages)) . '</Say>',
            '</Response>',
        ];

        return response(implode("\n", $xml), 200, ['Content-Type' => 'text/xml; charset=UTF-8']);
    }
}
