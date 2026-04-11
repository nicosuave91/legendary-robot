<?php

declare(strict_types=1);

namespace App\Modules\Communications\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use App\Modules\Clients\Models\Client;
use App\Modules\Communications\Models\CallLog;
use App\Modules\IdentityAccess\Models\User;
use Database\Seeders\DatabaseSeeder;
use Tests\TestCase;

final class TwilioVoiceCompletionFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_outbound_voice_twiml_route_returns_real_dial_flow(): void
    {
        config([
            'communications.voice.bridge.default_agent_number' => '+18045550999',
            'communications.voice.bridge.customer_intro_message' => 'Please hold while we connect your call.',
            'services.twilio.voice_from_number' => '+18045550100',
        ]);

        $callLog = $this->createCallLog([
            'purpose_note' => 'Review policy update',
            'bridged_to_number' => '+18045550999',
            'from_number' => '+18045550100',
        ]);

        $response = $this->get('/twiml/twilio/voice/outbound/' . $callLog->id . '/' . $callLog->tenant_id);

        $response->assertOk();
        $response->assertHeader('content-type', 'text/xml; charset=UTF-8');
        $response->assertSee('Please hold while we connect your call.', false);
        $response->assertSee('<Dial', false);
        $response->assertSee('+18045550999', false);
        $response->assertSee('/twiml/twilio/voice/agent-whisper/', false);
    }

    public function test_agent_whisper_twiml_includes_client_name_and_purpose_note(): void
    {
        $callLog = $this->createCallLog([
            'purpose_note' => 'Review policy update',
        ]);

        $response = $this->get('/twiml/twilio/voice/agent-whisper/' . $callLog->id . '/' . $callLog->tenant_id);

        $response->assertOk();
        $response->assertHeader('content-type', 'text/xml; charset=UTF-8');
        $response->assertSee('Jamie Foster', false);
        $response->assertSee('Review policy update', false);
    }

    public function test_call_idempotency_reuses_existing_call_log(): void
    {
        config([
            'communications.voice.bridge.default_agent_number' => '+18045550999',
            'services.twilio.voice_from_number' => '+18045550100',
        ]);

        $owner = User::query()->withoutGlobalScopes()->findOrFail('owner-user');

        $payload = [
            'toPhone' => '+1 (804) 555-0101',
            'purposeNote' => 'Call about application status',
            'idempotencyKey' => 'call-idempotency-001',
        ];

        $first = $this->actingAs($owner, 'web')
            ->withHeaders(['Accept' => 'application/json'])
            ->post('/api/v1/clients/client-jamie-foster/communications/call', $payload)
            ->assertCreated();

        $second = $this->actingAs($owner, 'web')
            ->withHeaders(['Accept' => 'application/json'])
            ->post('/api/v1/clients/client-jamie-foster/communications/call', $payload)
            ->assertCreated();

        self::assertSame((string) $first->json('data.id'), (string) $second->json('data.id'));
        self::assertSame(1, CallLog::query()->withoutGlobalScopes()->where('idempotency_key', 'call-idempotency-001')->count());

        $callLog = CallLog::query()->withoutGlobalScopes()->where('idempotency_key', 'call-idempotency-001')->firstOrFail();
        self::assertSame('+18045550101', (string) $callLog->to_number);
        self::assertSame('Call about application status', (string) $callLog->purpose_note);
        self::assertSame('+18045550999', (string) $callLog->bridged_to_number);
    }

    public function test_retry_call_clones_failed_call_context_into_new_call_log(): void
    {
        config([
            'communications.voice.bridge.default_agent_number' => '+18045550999',
            'services.twilio.voice_from_number' => '+18045550100',
        ]);

        $owner = User::query()->withoutGlobalScopes()->findOrFail('owner-user');

        $sourceCall = $this->createCallLog([
            'lifecycle_status' => 'failed',
            'failure_code' => '30005',
            'failure_message' => 'Busy',
            'purpose_note' => 'Follow up on underwriting exception',
            'to_number' => '+18045550101',
            'bridged_to_number' => '+18045550888',
        ]);

        $response = $this->actingAs($owner, 'web')
            ->withHeaders(['Accept' => 'application/json'])
            ->post('/api/v1/clients/client-jamie-foster/communications/call', [
                'retryOfCallLogId' => (string) $sourceCall->id,
                'idempotencyKey' => 'call-retry-001',
            ])
            ->assertCreated();

        $retriedCall = CallLog::query()->withoutGlobalScopes()->findOrFail((string) $response->json('data.id'));

        self::assertSame('+18045550101', (string) $retriedCall->to_number);
        self::assertSame('Follow up on underwriting exception', (string) $retriedCall->purpose_note);
        self::assertSame((string) $sourceCall->id, (string) $retriedCall->retry_of_call_log_id);
        self::assertSame('+18045550888', (string) $retriedCall->bridged_to_number);
        self::assertSame('call-retry-001', (string) $retriedCall->idempotency_key);
    }

    public function test_voice_callback_marks_call_started_answered_and_completed(): void
    {
        config([
            'communications.webhooks.twilio.enforce_signature' => false,
            'services.twilio.auth_token' => '',
        ]);

        $callLog = $this->createCallLog([
            'lifecycle_status' => 'submitted',
            'purpose_note' => 'Discuss renewal',
        ]);

        $this->post('/webhooks/twilio/voice?callLogId=' . $callLog->id . '&tenantId=' . $callLog->tenant_id, [
            'CallStatus' => 'in-progress',
            'CallSid' => 'CA-VOICE-001',
        ])->assertOk();

        $callLog->refresh();

        self::assertSame('in_progress', (string) $callLog->lifecycle_status);
        self::assertNotNull($callLog->started_at);
        self::assertNotNull($callLog->answered_at);

        $this->post('/webhooks/twilio/voice?callLogId=' . $callLog->id . '&tenantId=' . $callLog->tenant_id, [
            'CallStatus' => 'completed',
            'CallSid' => 'CA-VOICE-001',
            'CallDuration' => '42',
        ])->assertOk();

        $callLog->refresh();

        self::assertSame('completed', (string) $callLog->lifecycle_status);
        self::assertSame(42, (int) $callLog->duration_seconds);
        self::assertNotNull($callLog->ended_at);
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function createCallLog(array $overrides = []): CallLog
    {
        $client = Client::query()->withoutGlobalScopes()->findOrFail('client-jamie-foster');

        return CallLog::query()->withoutGlobalScopes()->create(array_merge([
            'id' => (string) Str::uuid(),
            'tenant_id' => (string) $client->tenant_id,
            'client_id' => (string) $client->id,
            'direction' => 'outbound',
            'lifecycle_status' => 'queued',
            'provider_name' => 'twilio',
            'provider_call_id' => null,
            'from_number' => '+18045550100',
            'to_number' => '+18045550101',
            'purpose_note' => null,
            'idempotency_key' => null,
            'retry_of_call_log_id' => null,
            'bridged_to_number' => '+18045550999',
            'correlation_key' => (string) Str::uuid(),
            'queued_at' => now()->subMinute(),
            'started_at' => null,
            'answered_at' => null,
            'ended_at' => null,
            'duration_seconds' => null,
            'failure_code' => null,
            'failure_message' => null,
            'initiated_by' => 'owner-user',
        ], $overrides));
    }
}
