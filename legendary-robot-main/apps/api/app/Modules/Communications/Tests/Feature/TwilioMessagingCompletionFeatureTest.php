<?php

declare(strict_types=1);

namespace App\Modules\Communications\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Modules\Audit\Models\AuditLog;
use App\Modules\Clients\Models\Client;
use App\Modules\Communications\Models\CommunicationAttachment;
use App\Modules\Communications\Models\CommunicationEndpoint;
use App\Modules\Communications\Models\CommunicationMessage;
use App\Modules\Communications\Models\CommunicationThread;
use App\Modules\Communications\Services\CommunicationAttachmentUrlService;
use App\Modules\IdentityAccess\Models\User;
use Database\Seeders\DatabaseSeeder;
use Tests\TestCase;

final class TwilioMessagingCompletionFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_inbound_twilio_mms_creates_inbound_message_and_imports_media(): void
    {
        Storage::fake('local');
        Http::fake([
            'https://media.twilio.test/*' => Http::response('fake-image-bytes', 200, ['Content-Type' => 'image/jpeg']),
        ]);

        config([
            'communications.webhooks.twilio.enforce_signature' => false,
            'services.twilio.sid' => 'twilio-sid',
            'services.twilio.auth_token' => 'twilio-auth-token',
            'services.twilio.from_number' => '+18045550100',
        ]);

        CommunicationEndpoint::query()->withoutGlobalScopes()->create([
            'id' => 'endpoint-twilio-default',
            'tenant_id' => 'tenant-default',
            'channel' => 'sms',
            'provider_name' => 'twilio',
            'endpoint_kind' => 'phone_number',
            'address_display' => '+18045550100',
            'address_normalized' => '+18045550100',
            'label' => 'Default Twilio Number',
            'is_active' => true,
            'is_default_outbound' => true,
        ]);

        $this->post('/webhooks/twilio/messaging', [
            'MessageSid' => 'SM-INBOUND-001',
            'From' => '+1 (804) 555-0101',
            'To' => '+1 (804) 555-0100',
            'Body' => 'Inbound MMS for Jamie',
            'NumMedia' => '1',
            'MediaUrl0' => 'https://media.twilio.test/media/1',
            'MediaContentType0' => 'image/jpeg',
        ])->assertOk()->assertJson(['ok' => true]);

        $message = CommunicationMessage::query()
            ->withoutGlobalScopes()
            ->where('provider_message_id', 'SM-INBOUND-001')
            ->firstOrFail();

        self::assertSame('tenant-default', (string) $message->tenant_id);
        self::assertSame('client-jamie-foster', (string) $message->client_id);
        self::assertSame('inbound', (string) $message->direction);
        self::assertSame('mms', (string) $message->channel);
        self::assertSame('received', (string) $message->lifecycle_status);
        self::assertSame('Inbound MMS for Jamie', (string) $message->body_text);

        $attachment = CommunicationAttachment::query()
            ->withoutGlobalScopes()
            ->where('attachable_id', $message->id)
            ->firstOrFail();

        self::assertSame('provider_inbound', (string) $attachment->provenance);
        Storage::disk('local')->assertExists((string) $attachment->storage_path);

        self::assertSame(1, AuditLog::query()
            ->withoutGlobalScopes()
            ->where('action', 'communication.sms.inbound_received')
            ->where('subject_id', $message->id)
            ->count());
    }

    public function test_signed_public_attachment_route_serves_stored_media(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('tenants/tenant-default/clients/client-jamie-foster/communications/mms/message-1/attachments/attachment-1/sample.jpg', 'binary-body');

        $attachment = CommunicationAttachment::query()->withoutGlobalScopes()->create([
            'id' => 'attachment-public-1',
            'tenant_id' => 'tenant-default',
            'client_id' => 'client-jamie-foster',
            'attachable_type' => CommunicationMessage::class,
            'attachable_id' => 'message-1',
            'source_channel' => 'mms',
            'provenance' => 'manual_upload',
            'storage_disk' => 'local',
            'storage_path' => 'tenants/tenant-default/clients/client-jamie-foster/communications/mms/message-1/attachments/attachment-1/sample.jpg',
            'storage_reference' => 'local:tenants/tenant-default/clients/client-jamie-foster/communications/mms/message-1/attachments/attachment-1/sample.jpg',
            'original_filename' => 'sample.jpg',
            'stored_filename' => 'sample.jpg',
            'mime_type' => 'image/jpeg',
            'size_bytes' => 11,
            'checksum_sha256' => hash('sha256', 'binary-body'),
            'scan_status' => 'clean',
        ]);

        $url = app(CommunicationAttachmentUrlService::class)->temporaryPublicUrl($attachment, 10);

        $this->get($url)->assertOk()->assertHeader('content-type', 'image/jpeg');
    }

    public function test_sms_retry_reuses_failed_message_content_clones_attachments_and_honors_idempotency(): void
    {
        $owner = User::query()->withoutGlobalScopes()->findOrFail('owner-user');
        $client = Client::query()->withoutGlobalScopes()->findOrFail('client-jamie-foster');

        $thread = CommunicationThread::query()->withoutGlobalScopes()->create([
            'id' => 'thread-source-sms',
            'tenant_id' => (string) $client->tenant_id,
            'client_id' => (string) $client->id,
            'channel' => 'sms',
            'participant_key' => '+18045550101',
            'subject_hint' => null,
            'created_by' => 'owner-user',
            'last_activity_at' => now(),
        ]);

        $sourceMessage = CommunicationMessage::query()->withoutGlobalScopes()->create([
            'id' => 'source-failed-sms',
            'tenant_id' => (string) $client->tenant_id,
            'client_id' => (string) $client->id,
            'communication_thread_id' => (string) $thread->id,
            'channel' => 'mms',
            'direction' => 'outbound',
            'lifecycle_status' => 'failed',
            'provider_name' => 'twilio',
            'provider_message_id' => 'SM-FAILED-001',
            'provider_status' => 'failed',
            'from_address' => '+18045550100',
            'to_address' => '+18045550101',
            'subject' => null,
            'body_text' => 'Please retry this message.',
            'body_html' => null,
            'idempotency_key' => null,
            'correlation_key' => (string) Str::uuid(),
            'queued_at' => now()->subMinutes(5),
            'submitted_at' => now()->subMinutes(4),
            'finalized_at' => now()->subMinutes(3),
            'failure_code' => '30003',
            'failure_message' => 'Destination handset unavailable.',
            'created_by' => 'owner-user',
        ]);

        CommunicationAttachment::query()->withoutGlobalScopes()->create([
            'id' => 'source-failed-attachment',
            'tenant_id' => (string) $client->tenant_id,
            'client_id' => (string) $client->id,
            'attachable_type' => CommunicationMessage::class,
            'attachable_id' => (string) $sourceMessage->id,
            'source_channel' => 'mms',
            'provenance' => 'manual_upload',
            'storage_disk' => 'local',
            'storage_path' => 'tenants/tenant-default/clients/client-jamie-foster/communications/mms/source-failed-sms/attachments/source-failed-attachment/file.jpg',
            'storage_reference' => 'local:tenants/tenant-default/clients/client-jamie-foster/communications/mms/source-failed-sms/attachments/source-failed-attachment/file.jpg',
            'original_filename' => 'file.jpg',
            'stored_filename' => 'file.jpg',
            'mime_type' => 'image/jpeg',
            'size_bytes' => 12,
            'checksum_sha256' => hash('sha256', 'fake-content'),
            'scan_status' => 'pending',
        ]);

        $payload = [
            'retryOfMessageId' => (string) $sourceMessage->id,
            'idempotencyKey' => 'sms-retry-001',
            'toPhone' => '+1 (804) 555-0101',
        ];

        $firstResponse = $this->actingAs($owner, 'web')
            ->withHeaders(['Accept' => 'application/json'])
            ->post('/api/v1/clients/client-jamie-foster/communications/sms', $payload)
            ->assertCreated();

        $firstMessageId = (string) $firstResponse->json('data.id');

        $secondResponse = $this->actingAs($owner, 'web')
            ->withHeaders(['Accept' => 'application/json'])
            ->post('/api/v1/clients/client-jamie-foster/communications/sms', $payload)
            ->assertCreated();

        self::assertSame($firstMessageId, (string) $secondResponse->json('data.id'));

        $retriedMessage = CommunicationMessage::query()->withoutGlobalScopes()->findOrFail($firstMessageId);

        self::assertSame('Please retry this message.', (string) $retriedMessage->body_text);
        self::assertSame('+18045550101', (string) $retriedMessage->to_address);
        self::assertSame('mms', (string) $retriedMessage->channel);
        self::assertSame('sms-retry-001', (string) $retriedMessage->idempotency_key);

        self::assertSame(1, CommunicationMessage::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $client->tenant_id)
            ->where('client_id', $client->id)
            ->where('idempotency_key', 'sms-retry-001')
            ->count());

        self::assertSame(1, CommunicationAttachment::query()
            ->withoutGlobalScopes()
            ->where('attachable_id', $retriedMessage->id)
            ->count());
    }
}
