<?php

declare(strict_types=1);

namespace App\Modules\Communications\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use App\Modules\Audit\Models\AuditLog;
use App\Modules\Clients\Models\Client;
use App\Modules\Communications\Models\CallLog;
use App\Modules\Communications\Models\CommunicationMessage;
use App\Modules\Communications\Models\CommunicationThread;
use App\Modules\Communications\Models\DeliveryStatusEvent;
use App\Modules\Communications\Models\EmailLog;
use Database\Seeders\DatabaseSeeder;
use Tests\TestCase;

final class CommunicationsWebhookCallbackFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_sendgrid_events_webhook_accepts_verified_requests_and_dedupes_duplicate_callbacks(): void
    {
        config([
            'communications.webhooks.sendgrid.enforce_signature' => true,
            'communications.webhooks.sendgrid.public_key' => '',
            'communications.webhooks.sendgrid.oauth_bearer_token' => 'release-token-123',
        ]);

        $message = $this->createEmailMessage();

        $event = [
            'event' => 'delivered',
            'message_id' => (string) $message->id,
            'sg_message_id' => 'sg-message-123',
            'sg_event_id' => 'sg-event-123',
        ];

        $this->json('POST', '/webhooks/sendgrid/events', [$event], [
            'Authorization' => 'Bearer release-token-123',
        ])->assertOk()->assertJson(['ok' => true]);

        $this->json('POST', '/webhooks/sendgrid/events', [$event], [
            'Authorization' => 'Bearer release-token-123',
        ])->assertOk()->assertJson(['ok' => true]);

        $message->refresh();

        self::assertSame('delivered', (string) $message->lifecycle_status);
        self::assertSame('delivered', (string) $message->provider_status);
        self::assertNotNull($message->finalized_at);

        self::assertSame(1, DeliveryStatusEvent::query()
            ->withoutGlobalScopes()
            ->where('subject_type', 'communication_message')
            ->where('subject_id', $message->id)
            ->count());

        self::assertSame(1, AuditLog::query()
            ->withoutGlobalScopes()
            ->where('action', 'communication.email.callback_processed')
            ->where('subject_id', $message->id)
            ->count());

        $eventRecord = DeliveryStatusEvent::query()
            ->withoutGlobalScopes()
            ->where('provider_event_id', 'sg-event-123')
            ->firstOrFail();

        self::assertTrue((bool) $eventRecord->signature_verified);
        self::assertSame('delivered', (string) $eventRecord->status_after);
    }

    public function test_sendgrid_events_webhook_does_not_regress_terminal_status_from_late_non_terminal_callbacks(): void
    {
        config([
            'communications.webhooks.sendgrid.enforce_signature' => true,
            'communications.webhooks.sendgrid.public_key' => '',
            'communications.webhooks.sendgrid.oauth_bearer_token' => 'release-token-123',
        ]);

        $message = $this->createEmailMessage(
            lifecycleStatus: 'delivered',
            providerStatus: 'delivered',
            finalized: true,
        );

        $event = [
            'event' => 'processed',
            'message_id' => (string) $message->id,
            'sg_message_id' => 'sg-message-processed',
            'sg_event_id' => 'sg-event-processed',
        ];

        $this->json('POST', '/webhooks/sendgrid/events', [$event], [
            'Authorization' => 'Bearer release-token-123',
        ])->assertOk()->assertJson(['ok' => true]);

        $message->refresh();

        self::assertSame('delivered', (string) $message->lifecycle_status);
        self::assertSame('delivered', (string) $message->provider_status);

        $eventRecord = DeliveryStatusEvent::query()
            ->withoutGlobalScopes()
            ->where('provider_event_id', 'sg-event-processed')
            ->firstOrFail();

        self::assertSame('delivered', (string) $eventRecord->status_before);
        self::assertSame('delivered', (string) $eventRecord->status_after);
    }

    public function test_twilio_messaging_webhook_rejects_unverified_callbacks_when_enforcement_is_enabled(): void
    {
        config([
            'communications.webhooks.twilio.enforce_signature' => true,
            'services.twilio.auth_token' => '',
        ]);

        $message = $this->createSmsMessage();

        $this->post(
            '/webhooks/twilio/messaging?messageId=' . $message->id . '&tenantId=' . $message->tenant_id,
            [
                'SmsStatus' => 'delivered',
                'SmsSid' => 'SM123',
            ],
        )->assertStatus(401);

        $message->refresh();

        self::assertSame('queued', (string) $message->lifecycle_status);
        self::assertSame(0, DeliveryStatusEvent::query()->withoutGlobalScopes()->count());
        self::assertSame(0, AuditLog::query()->withoutGlobalScopes()->where('action', 'communication.sms.callback_processed')->count());
    }

    public function test_twilio_messaging_webhook_allows_unverified_callbacks_when_enforcement_is_disabled(): void
    {
        config([
            'communications.webhooks.twilio.enforce_signature' => false,
            'services.twilio.auth_token' => '',
        ]);

        $message = $this->createSmsMessage();

        $this->post(
            '/webhooks/twilio/messaging?messageId=' . $message->id . '&tenantId=' . $message->tenant_id,
            [
                'SmsStatus' => 'delivered',
                'SmsSid' => 'SM123',
            ],
        )->assertOk()->assertJson(['ok' => true]);

        $message->refresh();

        self::assertSame('delivered', (string) $message->lifecycle_status);
        self::assertSame('delivered', (string) $message->provider_status);
        self::assertSame('SM123', (string) $message->provider_message_id);

        $eventRecord = DeliveryStatusEvent::query()
            ->withoutGlobalScopes()
            ->where('subject_type', 'communication_message')
            ->where('subject_id', $message->id)
            ->firstOrFail();

        self::assertFalse((bool) $eventRecord->signature_verified);
        self::assertSame(1, AuditLog::query()
            ->withoutGlobalScopes()
            ->where('action', 'communication.sms.callback_processed')
            ->where('subject_id', $message->id)
            ->count());
    }

    public function test_twilio_voice_webhook_processes_callbacks_once_when_duplicates_arrive(): void
    {
        config([
            'communications.webhooks.twilio.enforce_signature' => false,
            'services.twilio.auth_token' => '',
        ]);

        $callLog = $this->createCallLog();

        $payload = [
            'CallStatus' => 'completed',
            'CallSid' => 'CA123',
            'CallDuration' => '64',
        ];

        $this->post(
            '/webhooks/twilio/voice?callLogId=' . $callLog->id . '&tenantId=' . $callLog->tenant_id,
            $payload,
        )->assertOk()->assertJson(['ok' => true]);

        $this->post(
            '/webhooks/twilio/voice?callLogId=' . $callLog->id . '&tenantId=' . $callLog->tenant_id,
            $payload,
        )->assertOk()->assertJson(['ok' => true]);

        $callLog->refresh();

        self::assertSame('completed', (string) $callLog->lifecycle_status);
        self::assertSame('CA123', (string) $callLog->provider_call_id);
        self::assertSame(64, (int) $callLog->duration_seconds);
        self::assertNotNull($callLog->ended_at);

        self::assertSame(1, DeliveryStatusEvent::query()
            ->withoutGlobalScopes()
            ->where('subject_type', 'call_log')
            ->where('subject_id', $callLog->id)
            ->count());

        self::assertSame(1, AuditLog::query()
            ->withoutGlobalScopes()
            ->where('action', 'communication.call.callback_processed')
            ->where('subject_id', $callLog->id)
            ->count());
    }

    private function createSmsMessage(): CommunicationMessage
    {
        $client = $this->seededClient();
        $thread = CommunicationThread::query()->withoutGlobalScopes()->create([
            'id' => (string) Str::uuid(),
            'tenant_id' => (string) $client->tenant_id,
            'client_id' => (string) $client->id,
            'channel' => 'sms',
            'participant_key' => (string) $client->primary_phone,
            'subject_hint' => null,
            'created_by' => 'owner-user',
            'last_activity_at' => now(),
        ]);

        return CommunicationMessage::query()->withoutGlobalScopes()->create([
            'id' => (string) Str::uuid(),
            'tenant_id' => (string) $client->tenant_id,
            'client_id' => (string) $client->id,
            'communication_thread_id' => (string) $thread->id,
            'channel' => 'sms',
            'direction' => 'outbound',
            'lifecycle_status' => 'queued',
            'provider_name' => 'twilio',
            'provider_message_id' => null,
            'provider_status' => null,
            'from_address' => '+18045550100',
            'to_address' => (string) $client->primary_phone,
            'subject' => null,
            'body_text' => 'Seeded SMS body',
            'body_html' => null,
            'idempotency_key' => null,
            'correlation_key' => (string) Str::uuid(),
            'queued_at' => now(),
            'submitted_at' => null,
            'finalized_at' => null,
            'failure_code' => null,
            'failure_message' => null,
            'created_by' => 'owner-user',
        ]);
    }

    private function createEmailMessage(string $lifecycleStatus = 'submitted', ?string $providerStatus = 'accepted', bool $finalized = false): CommunicationMessage
    {
        $client = $this->seededClient();
        $thread = CommunicationThread::query()->withoutGlobalScopes()->create([
            'id' => (string) Str::uuid(),
            'tenant_id' => (string) $client->tenant_id,
            'client_id' => (string) $client->id,
            'channel' => 'email',
            'participant_key' => strtolower((string) $client->primary_email),
            'subject_hint' => 'Seeded subject',
            'created_by' => 'owner-user',
            'last_activity_at' => now(),
        ]);

        $message = CommunicationMessage::query()->withoutGlobalScopes()->create([
            'id' => (string) Str::uuid(),
            'tenant_id' => (string) $client->tenant_id,
            'client_id' => (string) $client->id,
            'communication_thread_id' => (string) $thread->id,
            'channel' => 'email',
            'direction' => 'outbound',
            'lifecycle_status' => $lifecycleStatus,
            'provider_name' => 'sendgrid',
            'provider_message_id' => 'seed-sendgrid-message-id',
            'provider_status' => $providerStatus,
            'from_address' => 'ops@example.com',
            'to_address' => strtolower((string) $client->primary_email),
            'subject' => 'Seeded subject',
            'body_text' => 'Seeded body',
            'body_html' => null,
            'idempotency_key' => null,
            'correlation_key' => (string) Str::uuid(),
            'queued_at' => now()->subMinute(),
            'submitted_at' => now()->subMinute(),
            'finalized_at' => $finalized ? now()->subSecond() : null,
            'failure_code' => null,
            'failure_message' => null,
            'created_by' => 'owner-user',
        ]);

        EmailLog::query()->withoutGlobalScopes()->create([
            'id' => (string) Str::uuid(),
            'tenant_id' => (string) $client->tenant_id,
            'client_id' => (string) $client->id,
            'communication_message_id' => (string) $message->id,
            'provider_name' => 'sendgrid',
            'provider_message_id' => 'seed-sendgrid-message-id',
            'from_email' => 'ops@example.com',
            'to_emails' => [strtolower((string) $client->primary_email)],
            'cc_emails' => null,
            'bcc_emails' => null,
            'reply_to_email' => 'ops@example.com',
            'provider_metadata' => ['seeded' => true],
            'last_provider_event_at' => null,
        ]);

        return $message;
    }

    private function createCallLog(): CallLog
    {
        $client = $this->seededClient();

        return CallLog::query()->withoutGlobalScopes()->create([
            'id' => (string) Str::uuid(),
            'tenant_id' => (string) $client->tenant_id,
            'client_id' => (string) $client->id,
            'direction' => 'outbound',
            'lifecycle_status' => 'submitted',
            'provider_name' => 'twilio',
            'provider_call_id' => null,
            'from_number' => '+18045550100',
            'to_number' => (string) $client->primary_phone,
            'correlation_key' => (string) Str::uuid(),
            'queued_at' => now()->subMinute(),
            'started_at' => now()->subMinute(),
            'ended_at' => null,
            'duration_seconds' => null,
            'failure_code' => null,
            'failure_message' => null,
            'initiated_by' => 'owner-user',
        ]);
    }

    private function seededClient(): Client
    {
        return Client::query()->withoutGlobalScopes()->findOrFail('client-jamie-foster');
    }
}
