<?php

declare(strict_types=1);

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Modules\Clients\Models\Client;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Imports\Models\Import;
use App\Modules\Imports\Services\ImportCommitService;
use App\Modules\Imports\Services\ImportUploadService;
use App\Modules\Imports\Services\ImportValidationService;
use App\Modules\Notifications\Models\Notification;
use App\Modules\Notifications\Models\ToastDismissal;

final class ImportsNotificationsAuditRuntimeTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;
    protected string $seeder = DatabaseSeeder::class;

    public function test_import_lifecycle_creates_durable_notifications_and_searchable_audit_evidence(): void
    {
        Storage::fake('local');

        $owner = User::query()->withoutGlobalScopes()->findOrFail('owner-user');

        $file = UploadedFile::fake()->createWithContent(
            'clients.csv',
            implode("\n", [
                'display_name,primary_email,primary_phone,preferred_contact_channel',
                'Runtime Import Client,runtime.import@example.com,804-555-0177,email',
            ]),
        );

        $created = app(ImportUploadService::class)->create(
            actor: $owner,
            importType: 'clients',
            file: $file,
            correlationId: 'corr-import-upload-runtime',
        );

        /** @var Import $import */
        $import = Import::query()->withoutGlobalScopes()->findOrFail((string) $created['import']['id']);

        app(ImportValidationService::class)->validate($import, 'corr-import-validate-runtime');

        $import->refresh();

        self::assertSame('ready_to_commit', $import->status);

        $validationNotification = Notification::query()
            ->withoutGlobalScopes()
            ->where('source_event_id', $import->id)
            ->where('notification_type', 'import.validation')
            ->latest('created_at')
            ->first();

        self::assertNotNull($validationNotification);

        $import->forceFill([
            'committed_by_user_id' => (string) $owner->id,
        ])->save();

        app(ImportCommitService::class)->commit($import->fresh(), 'corr-import-commit-runtime');

        $import->refresh();

        self::assertSame('committed', $import->status);
        self::assertSame(1, (int) $import->committed_row_count);
        self::assertTrue(
            Client::query()
                ->withoutGlobalScopes()
                ->where('tenant_id', $owner->tenant_id)
                ->where('primary_email', 'runtime.import@example.com')
                ->exists()
        );

        $commitNotification = Notification::query()
            ->withoutGlobalScopes()
            ->where('source_event_id', $import->id)
            ->where('notification_type', 'import.commit')
            ->latest('created_at')
            ->first();

        self::assertNotNull($commitNotification);

        $notificationsResponse = $this->actingAs($owner, 'web')
            ->getJson('/api/v1/notifications')
            ->assertOk();

        $notificationIds = array_map(
            static fn (array $item): string => (string) $item['id'],
            $notificationsResponse->json('data.items') ?? [],
        );

        self::assertContains((string) $commitNotification->id, $notificationIds);

        $this->actingAs($owner, 'web')
            ->postJson('/api/v1/notifications/' . $commitNotification->id . '/dismiss', [
                'surface' => 'header_center',
            ])
            ->assertOk()
            ->assertJsonPath('data.dismissed', true)
            ->assertJsonPath('data.notificationId', (string) $commitNotification->id);

        self::assertTrue(
            ToastDismissal::query()
                ->withoutGlobalScopes()
                ->where('tenant_id', $owner->tenant_id)
                ->where('notification_id', $commitNotification->id)
                ->where('user_id', $owner->id)
                ->where('surface', 'header_center')
                ->exists()
        );

        $auditResponse = $this->actingAs($owner, 'web')
            ->getJson('/api/v1/audit?correlationId=corr-import-commit-runtime')
            ->assertOk();

        $actions = array_map(
            static fn (array $item): string => (string) $item['action'],
            $auditResponse->json('data.items') ?? [],
        );

        self::assertContains('imports.commit.completed', $actions);
    }
}
