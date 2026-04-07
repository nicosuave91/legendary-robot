<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Database\Seeders\DatabaseSeeder;
use App\Modules\Clients\Models\Client;
use App\Modules\Clients\Models\ClientNote;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\WorkflowBuilder\Models\Workflow;
use App\Modules\WorkflowBuilder\Models\WorkflowRun;
use App\Modules\WorkflowBuilder\Models\WorkflowRunLog;
use App\Modules\WorkflowBuilder\Models\WorkflowVersion;
use App\Modules\WorkflowBuilder\Services\WorkflowDefinitionValidator;
use App\Modules\WorkflowBuilder\Services\WorkflowStepExecutor;
use App\Modules\WorkflowBuilder\Services\WorkflowTriggerMatcher;

final class WorkflowBuilderRuntimeTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;
    protected string $seeder = DatabaseSeeder::class;

    public function test_trigger_matcher_requires_matching_subject_type_and_filters(): void
    {
        $actor = User::query()->withoutGlobalScopes()->findOrFail('owner-user');

        [, $matchingVersion] = $this->createPublishedWorkflow(
            $actor,
            'workflow-match-1',
            'workflow-version-match-1',
            'application-submitted-follow-up',
            [
                'event' => 'application.created',
                'subjectType' => 'application',
                'filters' => [
                    ['fact' => 'currentStatus', 'operator' => 'eq', 'value' => 'submitted'],
                ],
            ],
            [
                ['type' => 'create_client_note', 'definition' => ['title' => 'Follow-up', 'bodyTemplate' => 'Submitted application note.']],
            ],
        );

        $this->createPublishedWorkflow(
            $actor,
            'workflow-match-2',
            'workflow-version-match-2',
            'client-submitted-follow-up',
            [
                'event' => 'application.created',
                'subjectType' => 'client',
                'filters' => [
                    ['fact' => 'currentStatus', 'operator' => 'eq', 'value' => 'submitted'],
                ],
            ],
            [
                ['type' => 'create_client_note', 'definition' => ['title' => 'Wrong subject type', 'bodyTemplate' => 'Should not match.']],
            ],
        );

        $this->createPublishedWorkflow(
            $actor,
            'workflow-match-3',
            'workflow-version-match-3',
            'application-approved-follow-up',
            [
                'event' => 'application.created',
                'subjectType' => 'application',
                'filters' => [
                    ['fact' => 'currentStatus', 'operator' => 'eq', 'value' => 'approved'],
                ],
            ],
            [
                ['type' => 'create_client_note', 'definition' => ['title' => 'Wrong filter', 'bodyTemplate' => 'Should not match.']],
            ],
        );

        $matches = app(WorkflowTriggerMatcher::class)->matchingPublishedVersions(
            (string) $actor->tenant_id,
            'application.created',
            'application',
            ['currentStatus' => 'submitted'],
        );

        self::assertCount(1, $matches);
        self::assertSame((string) $matchingVersion->id, (string) $matches->first()?->id);
    }

    public function test_condition_step_stops_run_when_condition_does_not_match(): void
    {
        $actor = User::query()->withoutGlobalScopes()->findOrFail('owner-user');

        $client = Client::query()->create([
            'id' => 'client-workflow-runtime-1',
            'tenant_id' => (string) $actor->tenant_id,
            'owner_user_id' => (string) $actor->id,
            'created_by' => (string) $actor->id,
            'display_name' => 'Workflow Runtime Client',
            'status' => 'lead',
        ]);

        [$workflow, $version] = $this->createPublishedWorkflow(
            $actor,
            'workflow-runtime-condition',
            'workflow-version-runtime-condition',
            'client-condition-guard',
            [
                'event' => 'client.updated',
                'subjectType' => 'client',
                'filters' => [],
            ],
            [
                ['type' => 'condition', 'definition' => ['fact' => 'currentStatus', 'operator' => 'eq', 'value' => 'active']],
                ['type' => 'create_client_note', 'definition' => ['title' => 'Should never run', 'bodyTemplate' => 'This note should not be created.']],
            ],
        );

        $run = WorkflowRun::query()->create([
            'id' => 'workflow-run-condition-1',
            'tenant_id' => (string) $actor->tenant_id,
            'workflow_id' => (string) $workflow->id,
            'workflow_version_id' => (string) $version->id,
            'trigger_event' => 'client.updated',
            'subject_type' => 'client',
            'subject_id' => (string) $client->id,
            'status' => 'queued',
            'current_step_index' => 0,
            'idempotency_key' => 'workflow-run-condition-1-key',
            'correlation_id' => 'corr-workflow-runtime-condition',
            'trigger_payload_snapshot' => ['currentStatus' => 'lead'],
            'runtime_context' => ['currentStatus' => 'lead'],
            'queued_at' => now(),
        ]);

        app(WorkflowStepExecutor::class)->execute($run, $version);

        $run->refresh();

        self::assertSame('completed', $run->status);
        self::assertNotNull($run->completed_at);
        self::assertSame(0, ClientNote::query()->withoutGlobalScopes()->where('client_id', $client->id)->count());
        self::assertTrue(
            WorkflowRunLog::query()
                ->withoutGlobalScopes()
                ->where('workflow_run_id', $run->id)
                ->where('log_type', 'condition_not_matched')
                ->exists()
        );
    }

    public function test_publish_validation_rejects_runtime_actions_for_unsupported_subject_types(): void
    {
        $result = app(WorkflowDefinitionValidator::class)->validate(
            [
                'event' => 'event.updated',
                'subjectType' => 'event',
                'filters' => [],
            ],
            [
                ['type' => 'send_sms', 'definition' => ['bodyTemplate' => 'This should not publish.']],
            ],
        );

        self::assertFalse($result['isValid']);
        self::assertContains('unsupported_action_subject_type', array_column($result['errors'], 'code'));
    }

    /**
     * @param array<string, mixed> $triggerDefinition
     * @param array<int, mixed> $stepsDefinition
     * @return array{0: Workflow, 1: WorkflowVersion}
     */
    private function createPublishedWorkflow(
        User $actor,
        string $workflowId,
        string $versionId,
        string $workflowKey,
        array $triggerDefinition,
        array $stepsDefinition,
    ): array {
        $workflow = Workflow::query()->create([
            'id' => $workflowId,
            'tenant_id' => (string) $actor->tenant_id,
            'workflow_key' => $workflowKey,
            'name' => str($workflowKey)->headline()->toString(),
            'description' => 'Workflow runtime test fixture',
            'status' => 'published',
            'created_by' => (string) $actor->id,
            'updated_by' => (string) $actor->id,
        ]);

        $version = WorkflowVersion::query()->create([
            'id' => $versionId,
            'tenant_id' => (string) $actor->tenant_id,
            'workflow_id' => (string) $workflow->id,
            'version_number' => 1,
            'lifecycle_state' => 'published',
            'trigger_definition' => $triggerDefinition,
            'steps_definition' => $stepsDefinition,
            'checksum' => hash('sha256', json_encode([
                'triggerDefinition' => $triggerDefinition,
                'stepsDefinition' => $stepsDefinition,
            ], JSON_THROW_ON_ERROR)),
            'published_at' => now(),
            'published_by' => (string) $actor->id,
            'created_by' => (string) $actor->id,
            'updated_by' => (string) $actor->id,
        ]);

        $workflow->forceFill([
            'latest_published_version_id' => (string) $version->id,
            'current_draft_version_id' => null,
        ])->save();

        return [$workflow->fresh(['latestPublishedVersion', 'currentDraftVersion']), $version->fresh()];
    }
}
