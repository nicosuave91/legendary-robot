<?php

declare(strict_types=1);

namespace App\Modules\Imports\Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use App\Modules\Imports\Models\Import;
use App\Modules\Imports\Services\ImportValidationService;
use App\Modules\Notifications\Models\ToastDismissal;
use Tests\Support\SeededApiTestCase;

final class ImportsNotificationsAuditContractTest extends SeededApiTestCase
{
    public function test_import_upload_validate_and_commit_surfaces_are_runtime_backed(): void
    {
        $actor = $this->sanctumActingAs('owner-user');

        $file = UploadedFile::fake()->createWithContent(
            'clients.csv',
            implode("\n", [
                'display_name,primary_email,primary_phone',
                'Runtime Import Client,runtime.import@example.com,804-555-0199',
            ]),
        );

        $uploadResponse = $this
            ->withHeader('X-Correlation-Id', 'corr-import-upload-runtime')
            ->post('/api/v1/imports', [
                'importType' => 'clients',
                'file' => $file,
            ]);

        $uploadResponse
            ->assertCreated()
            ->assertJsonPath('data.import.status', 'uploaded')
            ->assertJsonPath('data.import.importType', 'clients');

        $importId = (string) $uploadResponse->json('data.import.id');
        $import = Import::query()->withoutGlobalScopes()->findOrFail($importId);
        $validatedImport = app(ImportValidationService::class)->validate($import, 'corr-import-validated-runtime');

        self::assertSame('ready_to_commit', (string) $validatedImport->status);

        $this
            ->withHeader('X-Correlation-Id', 'corr-import-commit-runtime')
            ->postJson('/api/v1/imports/' . $importId . '/commit', [])
            ->assertOk()
            ->assertJsonPath('data.import.status', 'commit_queued')
            ->assertJsonPath('data.import.canCommit', false);
    }

    public function test_notifications_dismissal_and_audit_search_are_runtime_backed(): void
    {
        $this->sanctumActingAs('owner-user');

        $this->getJson('/api/v1/notifications')
            ->assertOk()
            ->assertJsonFragment(['id' => 'seed-notification-import-ready'])
            ->assertJsonFragment(['title' => 'Seeded import notification']);

        $dismissResponse = $this
            ->withHeader('X-Correlation-Id', 'corr-notifications-dismiss-runtime')
            ->postJson('/api/v1/notifications/seed-notification-import-ready/dismiss', [
                'surface' => 'header_center',
            ]);

        $dismissResponse
            ->assertOk()
            ->assertJsonPath('data.notificationId', 'seed-notification-import-ready')
            ->assertJsonPath('data.dismissed', true)
            ->assertJsonPath('data.surface', 'header_center');

        $dismissal = ToastDismissal::query()
            ->withoutGlobalScopes()
            ->where('notification_id', 'seed-notification-import-ready')
            ->where('surface', 'header_center')
            ->first();

        self::assertNotNull($dismissal);

        $this->getJson('/api/v1/audit?action=notifications.dismissed&subjectId=seed-notification-import-ready')
            ->assertOk()
            ->assertJsonPath('data.items.0.action', 'notifications.dismissed')
            ->assertJsonPath('data.items.0.subjectId', 'seed-notification-import-ready');
    }
}
