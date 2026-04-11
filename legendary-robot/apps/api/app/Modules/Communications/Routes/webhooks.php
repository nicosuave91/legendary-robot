<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Modules\Communications\Http\Controllers\Webhooks\PublicCommunicationAttachmentController;
use App\Modules\Communications\Http\Controllers\Webhooks\SendGridEventsWebhookController;
use App\Modules\Communications\Http\Controllers\Webhooks\SendGridInboundWebhookController;
use App\Modules\Communications\Http\Controllers\Webhooks\TwilioMessagingWebhookController;
use App\Modules\Communications\Http\Controllers\Webhooks\TwilioVoiceTwiMLController;
use App\Modules\Communications\Http\Controllers\Webhooks\TwilioVoiceWebhookController;

Route::get('/communications/attachments/{attachmentId}', PublicCommunicationAttachmentController::class)->name('communications.attachments.public');
Route::get('/twiml/twilio/voice/outbound/{callLogId}/{tenantId}', [TwilioVoiceTwiMLController::class, 'outbound'])->name('twiml.twilio.voice.outbound');
Route::get('/twiml/twilio/voice/agent-whisper/{callLogId}/{tenantId}', [TwilioVoiceTwiMLController::class, 'agentWhisper'])->name('twiml.twilio.voice.agent_whisper');
Route::post('/webhooks/twilio/messaging', TwilioMessagingWebhookController::class)->name('webhooks.twilio.messaging');
Route::post('/webhooks/twilio/voice', TwilioVoiceWebhookController::class)->name('webhooks.twilio.voice');
Route::post('/webhooks/sendgrid/inbound', SendGridInboundWebhookController::class)->name('webhooks.sendgrid.inbound');
Route::post('/webhooks/sendgrid/events', SendGridEventsWebhookController::class)->name('webhooks.sendgrid.events');
