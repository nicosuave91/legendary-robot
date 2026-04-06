<?php

declare(strict_types=1);

namespace App\Modules\Communications\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use RuntimeException;
use App\Modules\Clients\Models\Client;
use App\Modules\Communications\Models\CommunicationAttachment;
use App\Modules\Communications\Models\CommunicationMessage;
use App\Modules\Communications\Models\CommunicationThread;
use App\Modules\Communications\Models\EmailLog;
use App\Modules\Communications\Services\Providers\SendGridEmailAdapter;
use App\Modules\Communications\Services\Providers\TwilioMessagingAdapter;
use App\Modules\IdentityAccess\Models\User;
use Database\Seeders\DatabaseSeeder;
use Tests\TestCase;

final class CommunicationAttachmentGovernanceFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_owner_can_update_attachment_scan_status(): void
    {
        $owner = User::query()->withoutGlobalScopes()->findOrFail('owner-user');
        $attachment = $this->createAttachment('attachment-scan-update', 'pending');

        Sanctum::actingAs($owner);

        $response = $this->patchJson('/api/v1/communications/attachments/' . $attachment->id . '/scan-status', [
            'status' => 'clean',
            'engine' => 'clamav',
            'detail' => 'No threat signatures detected.',
        ])->assertOk()->json();

        $attachment->refresh();

        self::assertSame('clean', (string) $attachment->scan_status);
        self::assertSame('clamav', (string) $attachment->scan_engine);
        self::assertSame('No threat signatures detected.', (string) $attachment->scan_result_detail);
        self::assertNotNull($attachment->scan_requested_at);
        self::assertNotNull($attachment->scanned_at);
        self::assertSame('clean', $response['data']['scanStatus']);
    }

    public function test_public_attachment_route_requires_clean_scan_status(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('tenants/tenant-default/clients/client-jamie-foster/communications/email/message-attachment/attachments/attachment-file/sample.pdf', 'pdf-body');

        $pendingAttachment = $this->createAttachment('attachment-pending-public', 'pending', 'local', 'tenants/tenant-default/clients/client-jamie-foster/communications/email/message-attachment/attachments/attachment-file/sample.pdf', 'sample.pdf', 'application/pdf');
        $pendingUrl = URL::temporarySignedRoute('communications.attachments.public', now()->addMinutes(5), ['attachmentId' => (string) $pendingAttachment->id]);
        $this->get($pendingUrl)->assertStatus(404);

        $cleanAttachment = $this->createAttachment('attachment-clean-public', 'clean', 'local', 'tenants/tenant-default/clients/client-jamie-foster/communications/email/message-attachment/attachments/attachment-file/sample.pdf', 'sample.pdf', 'application/pdf');
        $cleanUrl = URL::temporarySignedRoute('communications.attachments.public', now()->addMinutes(5), ['attachmentId' => (string) $cleanAttachment->id]);
        $this->get($cleanUrl)->assertOk()->assertHeader('content-type', 'application/pdf');
    }

    public function test_twilio_adapter_blocks_pending_attachments_from_provider_submission(): void
    {
        config([
            'services.twilio.sid' => 'twilio-sid',
            'services.twilio.auth_token' => 'twilio-token',
            'services.twilio.from_number' => '+18045550100',
            'communications.attachments.outbound.required_scan_status' => 'clean',
        ]);

        $message = $this->createMessage('message-twilio-attachment', 'mms');
        $this->createAttachment('attachment-twilio-pending', 'pending', attachableId: (string) $message->id);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('scan status is "pending"');

        app(TwilioMessagingAdapter::class)->send($message);
    }

    public function test_sendgrid_adapter_blocks_quarantined_attachments_from_provider_submission(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('tenants/tenant-default/clients/client-jamie-foster/communications/email/message-sendgrid/attachments/attachment-file/file.pdf', 'file-body');

        config([
            'services.sendgrid.api_key' => 'sendgrid-key',
            'services.sendgrid.from_email' => 'ops@example.com',
            'communications.attachments.outbound.required_scan_status' => 'clean',
        ]);

        Http::fake();

        $message = $this->createMessage('message-sendgrid-attachment', 'email');
        EmailLog::query()->withoutGlobalScopes()->create([
            'id' => 'email-log-sendgrid-attachment',
            'tenant_id' => 'tenant-default',
            'client_id' => 'client-jamie-foster',
            'communication_message_id' => (string) $message->id,
            'provider_name' => 'sendgrid',
            'provider_message_id' => null,
            'from_email' => 'ops@example.com',
            'to_emails' => ['jamie.foster@example.com'],
            'cc_emails' => null,
            'bcc_emails' => null,
            'reply_to_email' => 'ops@example.com',
            'provider_metadata' => [],
        ]);

        $this->createAttachment('attachment-sendgrid-quarantined', 'quarantined', 'local', 'tenants/tenant-default/clients/client-jamie-foster/communications/email/message-sendgrid/attachments/attachment-file/file.pdf', 'file.pdf', 'application/pdf', (string) $message->id);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('scan status is "quarantined"');

        app(SendGridEmailAdapter::class)->send($message);
    }

    private function createMessage(string $id, string $channel): CommunicationMessage
    {
        $client = Client::query()->withoutGlobalScopes()->findOrFail('client-jamie-foster');
        $thread = CommunicationThread::query()->withoutGlobalScopes()->firstOrCreate(
            ['id' => 'thread-' . $id],
            [
                'tenant_id' => (string) $client->tenant_id,
                'client_id' => (string) $client->id,
                'channel' => $channel,
                'participant_key' => $channel === 'email' ? strtolower((string) $client->primary_email) : '+18045550101',
                'subject_hint' => null,
                'created_by' => 'owner-user',
                'last_activity_at' => now(),
            ]
        );

        return CommunicationMessage::query()->withoutGlobalScopes()->create([
            'id' => $id,
            'tenant_id' => (string) $client->tenant_id,
            'client_id' => (string) $client->id,
            'communication_thread_id' => (string) $thread->id,
            'channel' => $channel,
            'direction' => 'outbound',
            'lifecycle_status' => 'queued',
            'provider_name' => $channel === 'email' ? 'sendgrid' : 'twilio',
            'provider_message_id' => null,
            'provider_status' => null,
            'from_address' => $channel === 'email' ? 'ops@example.com' : '+18045550100',
            'to_address' => $channel === 'email' ? 'jamie.foster@example.com' : '+18045550101',
            'subject' => $channel === 'email' ? 'Security review message' : null,
            'body_text' => 'Attachment security test message.',
            'body_html' => null,
            'idempotency_key' => null,
            'correlation_key' => (string) Str::uuid(),
            'queued_at' => now(),
            'created_by' => 'owner-user',
        ]);
    }

    private function createAttachment(
        string $id,
        string $scanStatus,
        string $storageDisk = 'local',
        string $storagePath = 'tenants/tenant-default/clients/client-jamie-foster/communications/email/message-attachment/attachments/attachment-file/sample.pdf',
        string $originalFilename = 'sample.pdf',
        string $mimeType = 'application/pdf',
        string $attachableId = 'message-attachment',
    ): CommunicationAttachment {
        return CommunicationAttachment::query()->withoutGlobalScopes()->create([
            'id' => $id,
            'tenant_id' => 'tenant-default',
            'client_id' => 'client-jamie-foster',
            'attachable_type' => CommunicationMessage::class,
            'attachable_id' => $attachableId,
            'source_channel' => 'email',
            'provenance' => 'manual_upload',
            'storage_disk' => $storageDisk,
            'storage_path' => $storagePath,
            'storage_reference' => $storageDisk . ':' . $storagePath,
            'original_filename' => $originalFilename,
            'stored_filename' => $originalFilename,
            'mime_type' => $mimeType,
            'size_bytes' => 128,
            'checksum_sha256' => hash('sha256', $id),
            'scan_status' => $scanStatus,
            'scan_requested_at' => now()->subMinute(),
            'scanned_at' => $scanStatus === 'pending' ? null : now(),
            'scan_engine' => $scanStatus === 'pending' ? null : 'clamav',
            'scan_result_detail' => $scanStatus === 'clean' ? 'No threats.' : null,
            'quarantine_reason' => $scanStatus === 'quarantined' ? 'Malware detected.' : null,
        ]);
    }
}
