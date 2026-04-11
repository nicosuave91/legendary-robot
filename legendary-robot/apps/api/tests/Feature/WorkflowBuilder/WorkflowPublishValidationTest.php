<?php

declare(strict_types=1);

namespace Tests\Feature\WorkflowBuilder;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use App\Modules\IdentityAccess\Models\User;

final class WorkflowPublishValidationTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;
    protected string $seeder = DatabaseSeeder::class;

    public function test_workflow_detail_exposes_draft_validation_issues(): void
    {
        $actor = User::query()->withoutGlobalScopes()->findOrFail('owner-user');
        Sanctum::actingAs($actor);

        $createResponse = $this->postJson('/api/v1/workflows', [
            'workflowKey' => 'invalid-validation-demo',
            'name' => 'Invalid workflow draft',
            'triggerDefinition' => [
                'subjectType' => 'client',
            ],
            'stepsDefinition' => [
                [
                    'type' => 'send_email',
                    'definition' => [
                        'bodyTemplate' => 'Missing subject on purpose.',
                    ],
                ],
            ],
        ]);

        $createResponse->assertCreated();
        $workflowId = (string) $createResponse->json('data.workflow.id');

        $detailResponse = $this->getJson('/api/v1/workflows/' . $workflowId);

        $detailResponse
            ->assertOk()
            ->assertJsonPath('data.draftValidation.hasDraft', true)
            ->assertJsonPath('data.draftValidation.isValid', false);

        $errors = (array) $detailResponse->json('data.draftValidation.errors');
        self::assertNotEmpty($errors);
        self::assertContains('missing_trigger_event', array_column($errors, 'code'));
        self::assertContains('missing_email_subject', array_column($errors, 'code'));
    }

    public function test_invalid_workflow_draft_cannot_be_published(): void
    {
        $actor = User::query()->withoutGlobalScopes()->findOrFail('owner-user');
        Sanctum::actingAs($actor);

        $createResponse = $this->postJson('/api/v1/workflows', [
            'workflowKey' => 'invalid-publish-demo',
            'name' => 'Invalid workflow publish',
            'triggerDefinition' => [
                'subjectType' => 'client',
            ],
            'stepsDefinition' => [
                [
                    'type' => 'wait',
                    'definition' => [
                        'durationMinutes' => 0,
                    ],
                ],
            ],
        ]);

        $workflowId = (string) $createResponse->json('data.workflow.id');

        $publishResponse = $this->postJson('/api/v1/workflows/' . $workflowId . '/publish', []);

        $publishResponse
            ->assertStatus(422)
            ->assertJsonPath('errors.workflow.0', 'Draft workflow definition is not publishable.');

        $draftMessages = (array) $publishResponse->json('errors.draftValidation');
        self::assertNotEmpty($draftMessages);
    }
}
