<?php

declare(strict_types=1);

namespace Tests\Feature\WorkflowBuilder;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use App\Modules\Applications\Models\Application;
use App\Modules\Clients\Models\Client;
use App\Modules\Clients\Models\ClientNote;
use App\Modules\Communications\Jobs\SubmitOutboundEmailJob;
use App\Modules\Communications\Jobs\SubmitOutboundSmsJob;
use App\Modules\Communications\Models\CommunicationMessage;
use App\Modules\WorkflowBuilder\Jobs\ExecuteWorkflowRunStepJob;
use App\Modules\WorkflowBuilder\Models\Workflow;
use App\Modules\WorkflowBuilder\Models\WorkflowRun;
use App\Modules\WorkflowBuilder\Models\WorkflowRunLog;
use App\Modules\WorkflowBuilder\Models\WorkflowVersion;
use App\Modules\WorkflowBuilder\Services\WorkflowStepExecutor;
use Database\Seeders\DatabaseSeeder;
use Tests\TestCase;

final class WorkflowActionExecutionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_client_note_step_creates_a_governed_note_and_advances_the_run(): void
    {
        Queue::fake();

        $client = Client::query()->withoutGlobalScopes()->findOrFail('client-jamie-foster');

        [$run, $version] = $this->createWorkflowRun(
            subjectType: 'client',
            subjectId: (string) $client->id,
            steps: [[
                'type' => 'create_client_note',
                'definition' => [
                    'body' => 'Workflow note for {{clientDisplayName}} ({{clientId}}).',
                ],
            ]],
        );

        app(WorkflowStepExecutor::class)->execute($run, $version);

        $run->refresh();

        $this->assertSame('running', (string) $run->status);
        $this->assertSame(1, $run->current_step_index);
        $this->assertDatabaseHas((new ClientNote())->getTable(), [
            'tenant_id' => 'tenant-default',
            'client_id' => (string) $client->id,
            'body' => 'Workflow note for Jamie Foster (' . $client->id . ').',
        ]);
        $this->assertDatabaseHas((new WorkflowRunLog())->getTable(), [
            'workflow_run_id' => (string) $run->id,
            'log_type' => 'note_created',
        ]);

        Queue::assertPushed(ExecuteWorkflowRunStepJob::class);
    }

    public function test_email_step_queues_a_real_email_message_for_an_application_subject(): void
    {
        Queue::fake();

        $application = Application::query()->withoutGlobalScopes()->findOrFail('application-jamie-foster-001');

        [$run, $version] = $this->createWorkflowRun(
            subjectType: 'application',
            subjectId: (string) $application->id,
            steps: [[
                'type' => 'send_email',
                'definition' => [
                    'subject' => 'Workflow follow-up for {{applicationNumber}}',
                    'bodyText' => 'Current status is {{applicationStatus}} for {{clientDisplayName}}.',
                ],
            ]],
        );

        app(WorkflowStepExecutor::class)->execute($run, $version);

        $run->refresh();

        $this->assertSame('running', (string) $run->status);
        $this->assertSame(1, $run->current_step_index);
        $this->assertDatabaseHas((new CommunicationMessage())->getTable(), [
            'tenant_id' => 'tenant-default',
            'client_id' => (string) $application->client_id,
            'channel' => 'email',
            'subject' => 'Workflow follow-up for ' . $application->application_number,
        ]);
        $this->assertDatabaseHas((new WorkflowRunLog())->getTable(), [
            'workflow_run_id' => (string) $run->id,
            'log_type' => 'communication_queued',
        ]);

        Queue::assertPushed(SubmitOutboundEmailJob::class);
        Queue::assertPushed(ExecuteWorkflowRunStepJob::class);
    }

    public function test_sms_step_queues_a_real_sms_message_for_a_client_subject(): void
    {
        Queue::fake();

        $client = Client::query()->withoutGlobalScopes()->findOrFail('client-jamie-foster');

        [$run, $version] = $this->createWorkflowRun(
            subjectType: 'client',
            subjectId: (string) $client->id,
            steps: [[
                'type' => 'send_sms',
                'definition' => [
                    'body' => 'SMS follow-up for {{clientDisplayName}} at {{clientPhone}}.',
                ],
            ]],
        );

        app(WorkflowStepExecutor::class)->execute($run, $version);

        $run->refresh();

        $this->assertSame('running', (string) $run->status);
        $this->assertSame(1, $run->current_step_index);
        $this->assertDatabaseHas((new CommunicationMessage())->getTable(), [
            'tenant_id' => 'tenant-default',
            'client_id' => (string) $client->id,
            'channel' => 'sms',
            'to_address' => '+18045550101',
        ]);

        Queue::assertPushed(SubmitOutboundSmsJob::class);
        Queue::assertPushed(ExecuteWorkflowRunStepJob::class);
    }

    /**
     * @param array<int, array<string, mixed>> $steps
     * @return array{0: WorkflowRun, 1: WorkflowVersion}
     */
    private function createWorkflowRun(string $subjectType, string $subjectId, array $steps): array
    {
        $workflow = Workflow::query()->create([
            'id' => (string) Str::uuid(),
            'tenant_id' => 'tenant-default',
            'workflow_key' => 'closure-pass-' . Str::lower(Str::random(8)),
            'name' => 'Closure pass workflow',
            'description' => 'Generated during automated closure validation.',
            'status' => 'published',
            'created_by' => 'admin-user',
            'updated_by' => 'admin-user',
        ]);

        $version = WorkflowVersion::query()->create([
            'id' => (string) Str::uuid(),
            'tenant_id' => 'tenant-default',
            'workflow_id' => (string) $workflow->id,
            'version_number' => 1,
            'lifecycle_state' => 'published',
            'trigger_definition' => [
                'event' => 'manual.test',
                'subjectType' => $subjectType,
            ],
            'steps_definition' => $steps,
            'checksum' => hash('sha256', json_encode($steps, JSON_THROW_ON_ERROR)),
            'published_at' => now(),
            'published_by' => 'admin-user',
            'created_by' => 'admin-user',
            'updated_by' => 'admin-user',
        ]);

        $workflow->forceFill([
            'latest_published_version_id' => (string) $version->id,
            'status' => 'published',
        ])->save();

        $run = WorkflowRun::query()->create([
            'id' => (string) Str::uuid(),
            'tenant_id' => 'tenant-default',
            'workflow_id' => (string) $workflow->id,
            'workflow_version_id' => (string) $version->id,
            'trigger_event' => 'manual.test',
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'status' => 'queued',
            'current_step_index' => 0,
            'idempotency_key' => hash('sha256', (string) Str::uuid()),
            'correlation_id' => 'corr-workflow-action-test',
            'trigger_payload_snapshot' => [
                'actorUserId' => 'admin-user',
            ],
            'runtime_context' => [
                'actorUserId' => 'admin-user',
            ],
            'queued_at' => now(),
        ]);

        return [$run, $version];
    }
}
