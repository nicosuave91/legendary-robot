<?php

declare(strict_types=1);

namespace App\Modules\Communications\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use App\Modules\Clients\Models\Client;
use App\Modules\Communications\Models\CallLog;
use App\Modules\Communications\Models\CommunicationMessage;
use App\Modules\Communications\Models\CommunicationThread;
use App\Modules\IdentityAccess\Models\User;
use Database\Seeders\DatabaseSeeder;
use Tests\TestCase;

final class CommunicationsInboxAndTimelineFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_client_timeline_supports_cursor_pagination(): void
    {
        $owner = User::query()->withoutGlobalScopes()->findOrFail('owner-user');
        $client = Client::query()->withoutGlobalScopes()->findOrFail('client-jamie-foster');

        $thread = CommunicationThread::query()->withoutGlobalScopes()->create([
            'id' => 'thread-pagination',
            'tenant_id' => (string) $client->tenant_id,
            'client_id' => (string) $client->id,
            'channel' => 'email',
            'participant_key' => strtolower((string) $client->primary_email),
            'subject_hint' => 'Pagination thread',
            'created_by' => 'owner-user',
            'last_activity_at' => now(),
        ]);

        $first = $this->seedMessage($client, $thread, 'msg-page-1', 'Newest message', now()->subMinutes(1));
        $second = $this->seedMessage($client, $thread, 'msg-page-2', 'Second newest', now()->subMinutes(2));
        $third = $this->seedMessage($client, $thread, 'msg-page-3', 'Third newest', now()->subMinutes(3));

        $response = $this->actingAs($owner, 'web')
            ->getJson('/api/v1/clients/' . $client->id . '/communications?limit=2')
            ->assertOk()
            ->json();

        self::assertCount(2, $response['data']['items']);
        self::assertTrue((bool) $response['data']['paging']['hasMore']);
        self::assertNotNull($response['data']['paging']['nextCursor']);
        self::assertSame($first->id, $response['data']['items'][0]['id']);
        self::assertSame($second->id, $response['data']['items'][1]['id']);

        $nextCursor = (string) $response['data']['paging']['nextCursor'];

        $nextPage = $this->actingAs($owner, 'web')
            ->getJson('/api/v1/clients/' . $client->id . '/communications?limit=2&cursor=' . urlencode($nextCursor))
            ->assertOk()
            ->json();

        self::assertCount(1, $nextPage['data']['items']);
        self::assertFalse((bool) $nextPage['data']['paging']['hasMore']);
        self::assertSame($third->id, $nextPage['data']['items'][0]['id']);
    }

    public function test_communications_inbox_returns_recent_activity_across_visible_clients(): void
    {
        $owner = User::query()->withoutGlobalScopes()->findOrFail('owner-user');
        $tenantId = (string) $owner->tenant_id;

        $client = Client::query()->withoutGlobalScopes()->findOrFail('client-jamie-foster');
        $secondClient = Client::query()->withoutGlobalScopes()->create([
            'id' => 'client-inbox-second',
            'tenant_id' => $tenantId,
            'owner_user_id' => (string) $owner->id,
            'created_by' => (string) $owner->id,
            'display_name' => 'Inbox Second Client',
            'primary_email' => 'second@example.com',
            'primary_phone' => '+18045550122',
            'status' => 'active',
            'last_activity_at' => now(),
        ]);

        $threadOne = CommunicationThread::query()->withoutGlobalScopes()->create([
            'id' => 'thread-inbox-one',
            'tenant_id' => $tenantId,
            'client_id' => (string) $client->id,
            'channel' => 'email',
            'participant_key' => 'jamie@example.com',
            'subject_hint' => 'Inbox first client',
            'created_by' => 'owner-user',
            'last_activity_at' => now(),
        ]);

        $threadTwo = CommunicationThread::query()->withoutGlobalScopes()->create([
            'id' => 'thread-inbox-two',
            'tenant_id' => $tenantId,
            'client_id' => (string) $secondClient->id,
            'channel' => 'sms',
            'participant_key' => '+18045550122',
            'subject_hint' => null,
            'created_by' => 'owner-user',
            'last_activity_at' => now(),
        ]);

        $older = $this->seedMessage($client, $threadOne, 'msg-inbox-old', 'Older inbox message', now()->subMinutes(5));
        $newer = $this->seedMessage($secondClient, $threadTwo, 'msg-inbox-new', 'Newest inbox message', now()->subMinute(), 'sms');

        $response = $this->actingAs($owner, 'web')
            ->getJson('/api/v1/communications/inbox?limit=10')
            ->assertOk()
            ->json();

        self::assertSame($newer->id, $response['data']['items'][0]['timelineItem']['id']);
        self::assertSame($secondClient->id, $response['data']['items'][0]['client']['id']);
        self::assertSame($older->id, $response['data']['items'][1]['timelineItem']['id']);
        self::assertSame($client->id, $response['data']['items'][1]['client']['id']);
        self::assertGreaterThanOrEqual(2, (int) $response['data']['summary']['itemCount']);
        self::assertSame('all', $response['data']['filters']['channel']);
    }

    private function seedMessage(Client $client, CommunicationThread $thread, string $messageId, string $body, \Illuminate\Support\Carbon $timestamp, string $channel = 'email'): CommunicationMessage
    {
        $message = CommunicationMessage::query()->withoutGlobalScopes()->create([
            'id' => $messageId,
            'tenant_id' => (string) $client->tenant_id,
            'client_id' => (string) $client->id,
            'communication_thread_id' => (string) $thread->id,
            'channel' => $channel,
            'direction' => 'outbound',
            'lifecycle_status' => 'submitted',
            'provider_name' => $channel === 'email' ? 'sendgrid' : 'twilio',
            'provider_message_id' => null,
            'provider_status' => 'submitted',
            'from_address' => $channel === 'email' ? 'ops@example.com' : '+18045550100',
            'to_address' => $channel === 'email' ? strtolower((string) ($client->primary_email ?? 'client@example.com')) : (string) ($client->primary_phone ?? '+18045550123'),
            'subject' => $channel === 'email' ? 'Inbox subject ' . $messageId : null,
            'body_text' => $body,
            'body_html' => null,
            'idempotency_key' => null,
            'correlation_key' => (string) Str::uuid(),
            'queued_at' => $timestamp,
            'submitted_at' => $timestamp,
            'finalized_at' => null,
            'failure_code' => null,
            'failure_message' => null,
            'created_by' => 'owner-user',
        ]);

        CommunicationMessage::query()->withoutGlobalScopes()->where('id', $messageId)->update([
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        return $message->fresh();
    }
}
