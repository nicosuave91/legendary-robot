<?php

declare(strict_types=1);

namespace App\Modules\Communications\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use App\Modules\Clients\Models\Client;
use App\Modules\Communications\Jobs\SubmitOutboundEmailJob;
use App\Modules\Communications\Models\CommunicationAttachment;
use App\Modules\Communications\Models\CommunicationMailbox;
use App\Modules\Communications\Models\CommunicationMessage;
use App\Modules\Communications\Models\CommunicationThread;
use App\Modules\Communications\Models\EmailLog;
use App\Modules\IdentityAccess\Models\User;
use Database\Seeders\DatabaseSeeder;
use Tests\TestCase;

final class SendGridEmailCompletionFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_outbound_email_creates_reply_mailbox_when_inbound_domain_is_configured(): void
    {
        Queue::fake();
        config(['communications.inbound_email.domain' => 'replies.example.test']);

        $owner = User::query()->withoutGlobalScopes()->findOrFail('owner-user');
        Sanctum::actingAs($owner);

        $response = $this->withHeaders(['Accept' => 'application/json'])->post('/api/v1/clients/client-jamie-foster/communications/email', [
            'to' => ['jamie@example.com'],
            'subject' => 'Policy update',
            'bodyText' => 'Your policy update is ready.',
        ])->assertCreated();

        $messageId = (string) $response->json('data.id');
        $message = CommunicationMessage::query()->withoutGlobalScopes()->findOrFail($messageId);
        $emailLog = EmailLog::query()->withoutGlobalScopes()->where('communication_message_id', $messageId)->firstOrFail();
        $mailbox = CommunicationMailbox::query()->withoutGlobalScopes()->where('communication_thread_id', $message->communication_thread_id)->firstOrFail();

        self::assertStringEndsWith('@replies.example.test', (string) $emailLog->reply_to_email);
        self::assertSame((string) $emailLog->reply_to_email, (string) $mailbox->inbound_address);
        Queue::assertPushed(SubmitOutboundEmailJob::class);
    }

    public function test_sendgrid_inbound_webhook_routes_by_mailbox_alias_without_raw_tenant_or_client_fields(): void
    {
        Storage::fake('local');
        config([
            'communications.inbound_email.domain' => 'replies.example.test',
            'communications.webhooks.sendgrid.enforce_signature' => false,
        ]);

        $client = Client::query()->withoutGlobalScopes()->findOrFail('client-jamie-foster');
        $thread = CommunicationThread::query()->withoutGlobalScopes()->create([
            'id' => 'thread-inbound-email',
            'tenant_id' => (string) $client->tenant_id,
            'client_id' => (string) $client->id,
            'channel' => 'email',
            'participant_key' => 'jamie@example.com',
            'subject_hint' => 'Existing conversation',
            'created_by' => 'owner-user',
            'last_activity_at' => now()->subMinute(),
        ]);

        $mailbox = CommunicationMailbox::query()->withoutGlobalScopes()->create([
            'id' => 'mailbox-inbound-email',
            'tenant_id' => (string) $client->tenant_id,
            'client_id' => (string) $client->id,
            'communication_thread_id' => (string) $thread->id,
            'provider_name' => 'sendgrid',
            'alias_local_part' => 'reply-inbound-abc123',
            'inbound_address' => 'reply-inbound-abc123@replies.example.test',
            'label' => 'Inbound reply mailbox',
            'is_active' => true,
            'metadata' => ['channel' => 'email'],
        ]);

        $response = $this->post('/webhooks/sendgrid/inbound', [
            'from' => 'Jamie Foster <jamie@example.com>',
            'to' => 'reply-inbound-abc123@replies.example.test',
            'subject' => 'Re: Existing conversation',
            'text' => 'Thanks for the update.',
            'html' => '<p>Thanks for the update.</p>',
            'headers' => "Message-ID: <reply-message@example.com>\nIn-Reply-To: <original@example.com>",
            'attachment1' => UploadedFile::fake()->create('evidence.txt', 5, 'text/plain'),
        ])->assertOk();

        $message = CommunicationMessage::query()
            ->withoutGlobalScopes()
            ->where('provider_name', 'sendgrid')
            ->where('provider_message_id', '<reply-message@example.com>')
            ->firstOrFail();

        self::assertSame((string) $client->tenant_id, (string) $message->tenant_id);
        self::assertSame((string) $client->id, (string) $message->client_id);
        self::assertSame((string) $thread->id, (string) $message->communication_thread_id);
        self::assertSame('inbound', (string) $message->direction);
        self::assertSame('received', (string) $message->lifecycle_status);

        $emailLog = EmailLog::query()->withoutGlobalScopes()->where('communication_message_id', $message->id)->firstOrFail();
        self::assertSame('Jamie Foster <jamie@example.com>', (string) $emailLog->from_email);
        self::assertSame((string) $mailbox->inbound_address, (string) $emailLog->reply_to_email);
        self::assertSame('mailbox_alias', (string) ($emailLog->provider_metadata['resolvedBy'] ?? ''));

        self::assertSame(1, CommunicationAttachment::query()
            ->withoutGlobalScopes()
            ->where('attachable_id', $message->id)
            ->count());
    }

    public function test_email_retry_reuses_failed_email_content_and_honors_idempotency(): void
    {
        Queue::fake();
        config(['communications.inbound_email.domain' => 'replies.example.test']);

        $owner = User::query()->withoutGlobalScopes()->findOrFail('owner-user');
        Sanctum::actingAs($owner);

        $client = Client::query()->withoutGlobalScopes()->findOrFail('client-jamie-foster');
        $thread = CommunicationThread::query()->withoutGlobalScopes()->create([
            'id' => 'thread-retry-email',
            'tenant_id' => (string) $client->tenant_id,
            'client_id' => (string) $client->id,
            'channel' => 'email',
            'participant_key' => 'jamie@example.com',
            'subject_hint' => 'Retry subject',
            'created_by' => 'owner-user',
            'last_activity_at' => now(),
        ]);

        $sourceMessage = CommunicationMessage::query()->withoutGlobalScopes()->create([
            'id' => 'source-failed-email',
            'tenant_id' => (string) $client->tenant_id,
            'client_id' => (string) $client->id,
            'communication_thread_id' => (string) $thread->id,
            'channel' => 'email',
            'direction' => 'outbound',
            'lifecycle_status' => 'dropped',
            'provider_name' => 'sendgrid',
            'provider_message_id' => 'sg-original-1',
            'provider_status' => 'dropped',
            'from_address' => 'ops@example.com',
            'to_address' => 'jamie@example.com',
            'subject' => 'Retry subject',
            'body_text' => 'Retry this failed email.',
            'body_html' => '<p>Retry this failed email.</p>',
            'idempotency_key' => null,
            'correlation_key' => (string) Str::uuid(),
            'queued_at' => now()->subMinutes(5),
            'submitted_at' => now()->subMinutes(4),
            'finalized_at' => now()->subMinutes(3),
            'failure_code' => 'dropped',
            'failure_message' => 'Mailbox unavailable.',
            'created_by' => 'owner-user',
        ]);

        EmailLog::query()->withoutGlobalScopes()->create([
            'id' => 'source-email-log',
            'tenant_id' => (string) $client->tenant_id,
            'client_id' => (string) $client->id,
            'communication_message_id' => (string) $sourceMessage->id,
            'provider_name' => 'sendgrid',
            'provider_message_id' => 'sg-original-1',
            'from_email' => 'ops@example.com',
            'to_emails' => ['jamie@example.com'],
            'cc_emails' => ['cc@example.com'],
            'bcc_emails' => ['bcc@example.com'],
            'reply_to_email' => 'reply-old@replies.example.test',
            'provider_metadata' => ['seeded' => true],
            'last_provider_event_at' => now()->subMinutes(3),
        ]);

        CommunicationAttachment::query()->withoutGlobalScopes()->create([
            'id' => 'retry-attachment-source',
            'tenant_id' => (string) $client->tenant_id,
            'client_id' => (string) $client->id,
            'attachable_type' => CommunicationMessage::class,
            'attachable_id' => (string) $sourceMessage->id,
            'source_channel' => 'email',
            'provenance' => 'manual_upload',
            'storage_disk' => 'local',
            'storage_path' => 'tenants/tenant-default/clients/client-jamie-foster/communications/email/source-failed-email/attachments/retry-attachment-source/report.pdf',
            'storage_reference' => 'local:tenants/tenant-default/clients/client-jamie-foster/communications/email/source-failed-email/attachments/retry-attachment-source/report.pdf',
            'original_filename' => 'report.pdf',
            'stored_filename' => 'report.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 1024,
            'checksum_sha256' => hash('sha256', 'pdf-content'),
            'scan_status' => 'pending',
        ]);

        $payload = [
            'retryOfMessageId' => (string) $sourceMessage->id,
            'idempotencyKey' => 'email-retry-001',
        ];

        $firstResponse = $this->withHeaders(['Accept' => 'application/json'])
            ->post('/api/v1/clients/client-jamie-foster/communications/email', $payload)
            ->assertCreated();

        $firstMessageId = (string) $firstResponse->json('data.id');

        $secondResponse = $this->withHeaders(['Accept' => 'application/json'])
            ->post('/api/v1/clients/client-jamie-foster/communications/email', $payload)
            ->assertCreated();

        self::assertSame($firstMessageId, (string) $secondResponse->json('data.id'));

        $resentMessage = CommunicationMessage::query()->withoutGlobalScopes()->findOrFail($firstMessageId);
        $resentEmailLog = EmailLog::query()->withoutGlobalScopes()->where('communication_message_id', $firstMessageId)->firstOrFail();

        self::assertSame('Retry subject', (string) $resentMessage->subject);
        self::assertSame('Retry this failed email.', (string) $resentMessage->body_text);
        self::assertSame('<p>Retry this failed email.</p>', (string) $resentMessage->body_html);
        self::assertSame('email-retry-001', (string) $resentMessage->idempotency_key);
        self::assertSame(['jamie@example.com'], (array) $resentEmailLog->to_emails);
        self::assertSame(['cc@example.com'], (array) $resentEmailLog->cc_emails);
        self::assertSame(['bcc@example.com'], (array) $resentEmailLog->bcc_emails);
        self::assertSame((string) $sourceMessage->id, (string) (($resentEmailLog->provider_metadata['retryOfMessageId'] ?? '')));
        self::assertSame(1, CommunicationMessage::query()->withoutGlobalScopes()->where('idempotency_key', 'email-retry-001')->count());
        self::assertSame(1, CommunicationAttachment::query()->withoutGlobalScopes()->where('attachable_id', $resentMessage->id)->count());

        Queue::assertPushed(SubmitOutboundEmailJob::class);
    }
}
