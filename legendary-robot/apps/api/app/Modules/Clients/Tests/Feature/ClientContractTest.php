<?php

declare(strict_types=1);

namespace App\Modules\Clients\Tests\Feature;

use App\Modules\Audit\Models\AuditLog;
use App\Modules\Clients\Models\ClientNote;
use Tests\Support\SeededApiTestCase;

final class ClientContractTest extends SeededApiTestCase
{
    public function test_clients_index_returns_runtime_workspace_data_for_visible_tenant_records(): void
    {
        $this->sanctumActingAs('owner-user');

        $this->getJson('/api/v1/clients?sort=display_name&direction=asc')
            ->assertOk()
            ->assertJsonPath('data.pagination.total', 2)
            ->assertJsonPath('data.items.0.displayName', 'Horizon Medical')
            ->assertJsonPath('data.items.1.displayName', 'Jamie Foster');
    }

    public function test_client_workspace_returns_governed_summary_tabs_and_seeded_ruleful_data(): void
    {
        $this->sanctumActingAs('owner-user');

        $this->getJson('/api/v1/clients/client-jamie-foster')
            ->assertOk()
            ->assertJsonPath('data.client.displayName', 'Jamie Foster')
            ->assertJsonPath('data.summary.notesCount', 1)
            ->assertJsonPath('data.summary.eventsCount', 1)
            ->assertJsonPath('data.summary.applicationsCount', 1)
            ->assertJsonFragment(['key' => 'communications', 'label' => 'Communications'])
            ->assertJsonFragment(['key' => 'audit', 'label' => 'Audit']);
    }

    public function test_client_note_creation_uses_runtime_service_and_writes_audit_evidence(): void
    {
        $this->sanctumActingAs('owner-user');

        $response = $this
            ->withHeader('X-Correlation-Id', 'corr-clients-notes-runtime')
            ->postJson('/api/v1/clients/client-jamie-foster/notes', [
                'body' => 'Runtime note proof for governed client history.',
            ]);

        $response
            ->assertSuccessful()
            ->assertJsonPath('data.body', 'Runtime note proof for governed client history.')
            ->assertJsonPath('data.sourceType', 'user');

        $noteId = (string) $response->json('data.id');
        $note = ClientNote::query()->withoutGlobalScopes()->findOrFail($noteId);
        self::assertSame('client-jamie-foster', (string) $note->client_id);

        $audit = AuditLog::query()
            ->withoutGlobalScopes()
            ->where('action', 'clients.notes.create')
            ->where('subject_id', $noteId)
            ->latest('created_at')
            ->first();

        self::assertNotNull($audit);
        self::assertSame('corr-clients-notes-runtime', $audit->correlation_id);
    }
}
