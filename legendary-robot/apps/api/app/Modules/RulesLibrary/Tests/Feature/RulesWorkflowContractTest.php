<?php

declare(strict_types=1);

namespace App\Modules\RulesLibrary\Tests\Feature;

use Illuminate\Support\Str;
use Tests\Support\SeededApiTestCase;

final class RulesWorkflowContractTest extends SeededApiTestCase
{
    public function test_rule_create_and_publish_paths_execute_runtime_behavior(): void
    {
        $this->sanctumActingAs('owner-user');

        $ruleKey = 'runtime-rule-' . Str::lower((string) Str::uuid());
        $createResponse = $this
            ->withHeader('X-Correlation-Id', 'corr-rules-runtime-create')
            ->postJson('/api/v1/rules', [
                'ruleKey' => $ruleKey,
                'name' => 'Runtime rule verification',
                'description' => 'Ensures rules move through the real draft lifecycle.',
                'moduleScope' => 'applications',
                'subjectType' => 'application',
                'triggerEvent' => 'application.created',
                'severity' => 'warning',
                'industryScope' => ['Mortgage'],
                'conditionDefinition' => [
                    'fact' => 'amountRequested',
                    'operator' => 'gte',
                    'value' => 250000,
                ],
                'actionDefinition' => [
                    'type' => 'create_view_note',
                    'title' => 'Runtime rule note',
                ],
                'executionLabel' => 'Runtime rule execution',
                'noteTemplate' => 'Runtime rule note body.',
            ]);

        $createResponse
            ->assertCreated()
            ->assertJsonPath('data.rule.ruleKey', $ruleKey)
            ->assertJsonPath('data.rule.currentDraftVersionNumber', 1)
            ->assertJsonPath('data.versions.0.lifecycleState', 'draft');

        $ruleId = (string) $createResponse->json('data.rule.id');

        $this->postJson('/api/v1/rules/' . $ruleId . '/publish', [])
            ->assertCreated()
            ->assertJsonPath('data.rule.latestPublishedVersionNumber', 1)
            ->assertJsonPath('data.versions.0.lifecycleState', 'published');
    }

    public function test_workflow_create_exposes_draft_validation_and_valid_publish_is_runtime_backed(): void
    {
        $this->sanctumActingAs('owner-user');

        $workflowKey = 'runtime-workflow-' . Str::lower((string) Str::uuid());
        $createResponse = $this
            ->withHeader('X-Correlation-Id', 'corr-workflows-runtime-create')
            ->postJson('/api/v1/workflows', [
                'workflowKey' => $workflowKey,
                'name' => 'Runtime workflow verification',
                'description' => 'Ensures workflow detail and publish use runtime validation.',
                'triggerDefinition' => [
                    'event' => 'application.created',
                    'subjectType' => 'application',
                    'filters' => [],
                ],
                'stepsDefinition' => [
                    [
                        'type' => 'create_client_note',
                        'definition' => [
                            'title' => 'Runtime workflow note',
                            'bodyTemplate' => 'Created by runtime workflow test.',
                        ],
                    ],
                ],
            ]);

        $createResponse
            ->assertCreated()
            ->assertJsonPath('data.workflow.workflowKey', $workflowKey)
            ->assertJsonPath('data.draftValidation.hasDraft', true)
            ->assertJsonPath('data.draftValidation.isValid', true);

        $workflowId = (string) $createResponse->json('data.workflow.id');

        $this->postJson('/api/v1/workflows/' . $workflowId . '/publish', [])
            ->assertCreated()
            ->assertJsonPath('data.workflow.latestPublishedVersionNumber', 1)
            ->assertJsonPath('data.versions.0.lifecycleState', 'published');
    }
}
