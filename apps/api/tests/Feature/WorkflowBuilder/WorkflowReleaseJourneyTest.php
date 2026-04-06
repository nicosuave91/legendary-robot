<?php

declare(strict_types=1);

namespace Tests\Feature\WorkflowBuilder;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use App\Modules\Clients\Models\ClientNote;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\WorkflowBuilder\Models\WorkflowRun;

final class WorkflowReleaseJourneyTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;
    protected string $seeder = DatabaseSeeder::class;

    public function test_published_workflow_runs_after_domain_trigger_and_writes_side_effect_evidence(): void
    {
        $actor = User::query()->withoutGlobalScopes()->findOrFail('owner-user');
        Sanctum::actingAs($actor);

        $createResponse = $this->postJson('/api/v1/workflows', [
            'workflowKey' => 'closure-pass-4-release-note',
            'name' => 'Disposition note release journey',
            'description' => 'Release verification workflow that writes a governed client note.',
            'triggerDefinition' => [
                'event' => 'client.disposition.transitioned',
                'subjectType' => 'client',
            ],
            'stepsDefinition' => [
                [
                    'type' => 'create_client_note',
                    'definition' => [
                        'bodyTemplate' => 'Workflow release verification note for client {{clientId}} after {{triggerEvent}}.',
                    ],
                ],
            ],
        ]);

        $createResponse->assertCreated();
        $workflowId = (string) $createResponse->json('data.workflow.id');

        $publishResponse = $this->postJson('/api/v1/workflows/' . $workflowId . '/publish', []);
        $publishResponse->assertCreated();

        $transitionResponse = $this->postJson('/api/v1/clients/client-horizon-medical/disposition-transitions', [
            'targetDispositionCode' => 'qualified',
            'reason' => 'Release journey verification',
        ]);

        $transitionResponse
            ->assertCreated()
            ->assertJsonPath('data.result', 'transitioned');

        $run = WorkflowRun::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', 'tenant-default')
            ->where('workflow_id', $workflowId)
            ->latest('queued_at')
            ->first();

        self::assertNotNull($run);
        self::assertSame('completed', $run->status);

        $note = ClientNote::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', 'tenant-default')
            ->where('client_id', 'client-horizon-medical')
            ->latest('created_at')
            ->first();

        self::assertNotNull($note);
        self::assertStringContainsString('Workflow release verification note', (string) $note->body);

        $runsResponse = $this->getJson('/api/v1/workflows/' . $workflowId . '/runs');
        $runsResponse
            ->assertOk()
            ->assertJsonPath('data.items.0.status', 'completed');

        $runId = (string) $runsResponse->json('data.items.0.id');
        $runDetailResponse = $this->getJson('/api/v1/workflows/' . $workflowId . '/runs/' . $runId);

        $runDetailResponse->assertOk();
        $logTypes = array_column((array) $runDetailResponse->json('data.logs'), 'logType');
        self::assertContains('note_created', $logTypes);
        self::assertContains('run_completed', $logTypes);
    }
}
