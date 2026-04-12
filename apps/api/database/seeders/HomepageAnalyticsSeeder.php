<?php

declare(strict_types=1);

namespace Database\Seeders;

use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Modules\Clients\Models\Client;
use App\Modules\Clients\Models\ClientDocument;
use App\Modules\Clients\Models\ClientNote;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\TenantGovernance\Models\Tenant;

final class HomepageAnalyticsSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::query()->find('tenant-default');
        $owner = User::query()->find('owner-user');
        $admin = User::query()->find('admin-user');
        $user = User::query()->find('standard-user');

        if ($tenant === null || $owner === null || $admin === null || $user === null) {
            return;
        }

        $today = CarbonImmutable::now()->startOfDay();
        $owners = [$owner->id, $admin->id, $user->id];
        $clientIds = [];

        $clientSchedule = [
            88, 84, 79, 75, 70, 66, 61, 56, 51,
            46, 41, 36, 31, 25, 19, 13, 8, 2,
        ];

        foreach ($clientSchedule as $index => $daysAgo) {
            $createdAt = $today->subDays($daysAgo)->setTime(9 + ($index % 4), 15);
            $clientId = sprintf('seed-production-client-%03d', $index + 1);
            $clientIds[] = $clientId;

            Client::query()->withoutGlobalScopes()->firstOrCreate(
                ['id' => $clientId],
                [
                    'tenant_id' => $tenant->id,
                    'owner_user_id' => $owners[$index % count($owners)],
                    'created_by' => $admin->id,
                    'display_name' => sprintf('Production Client %02d', $index + 1),
                    'first_name' => 'Production',
                    'last_name' => sprintf('Client %02d', $index + 1),
                    'company_name' => sprintf('Production Client %02d', $index + 1),
                    'primary_email' => sprintf('production-client-%02d@example.com', $index + 1),
                    'primary_phone' => sprintf('804-555-%04d', 2000 + $index),
                    'preferred_contact_channel' => $index % 2 === 0 ? 'email' : 'phone',
                    'status' => $index % 3 === 0 ? 'lead' : 'active',
                    'last_activity_at' => $createdAt,
                ]
            );

            DB::table('clients')
                ->where('id', $clientId)
                ->update([
                    'created_at' => $createdAt->toDateTimeString(),
                    'updated_at' => $createdAt->toDateTimeString(),
                    'last_activity_at' => $createdAt->toDateTimeString(),
                ]);
        }

        foreach ($clientSchedule as $index => $daysAgo) {
            $clientId = $clientIds[$index];
            $authorId = $owners[($index + 1) % count($owners)];

            $initialNoteAt = $today->subDays($daysAgo)->setTime(11, 0);
            $followUpNoteAt = $today->subDays(max($daysAgo - 1, 0))->setTime(14, 30);

            $this->upsertNote(
                id: sprintf('seed-production-note-%03d-a', $index + 1),
                tenantId: $tenant->id,
                clientId: $clientId,
                authorId: $authorId,
                body: 'Seeded production note establishing a stable multi-day chart baseline.',
                createdAt: $initialNoteAt,
            );

            $this->upsertNote(
                id: sprintf('seed-production-note-%03d-b', $index + 1),
                tenantId: $tenant->id,
                clientId: $clientId,
                authorId: $authorId,
                body: 'Seeded follow-up note to keep the production trend distributed over time.',
                createdAt: $followUpNoteAt,
            );

            if ($index % 3 === 0) {
                $statusNoteAt = $today->subDays(max($daysAgo - 3, 0))->setTime(16, 0);

                $this->upsertNote(
                    id: sprintf('seed-production-note-%03d-c', $index + 1),
                    tenantId: $tenant->id,
                    clientId: $clientId,
                    authorId: $authorId,
                    body: 'Seeded review note to increase trend continuity for homepage analytics.',
                    createdAt: $statusNoteAt,
                );
            }

            $documentAt = $today->subDays(max($daysAgo - (($index % 2) + 1), 0))->setTime(15, 15);

            $this->upsertDocument(
                id: sprintf('seed-production-document-%03d-a', $index + 1),
                tenantId: $tenant->id,
                clientId: $clientId,
                uploadedByUserId: $authorId,
                originalFilename: sprintf('production-file-%02d.pdf', $index + 1),
                createdAt: $documentAt,
            );

            if ($index % 4 === 0) {
                $supplementalDocumentAt = $today->subDays(max($daysAgo - 4, 0))->setTime(10, 45);

                $this->upsertDocument(
                    id: sprintf('seed-production-document-%03d-b', $index + 1),
                    tenantId: $tenant->id,
                    clientId: $clientId,
                    uploadedByUserId: $authorId,
                    originalFilename: sprintf('production-supplement-%02d.pdf', $index + 1),
                    createdAt: $supplementalDocumentAt,
                );
            }

            DB::table('clients')
                ->where('id', $clientId)
                ->update([
                    'last_activity_at' => max($documentAt, $followUpNoteAt)->toDateTimeString(),
                    'updated_at' => max($documentAt, $followUpNoteAt)->toDateTimeString(),
                ]);
        }
    }

    private function upsertNote(
        string $id,
        string $tenantId,
        string $clientId,
        string $authorId,
        string $body,
        CarbonImmutable $createdAt,
    ): void {
        ClientNote::query()->withoutGlobalScopes()->firstOrCreate(
            ['id' => $id],
            [
                'tenant_id' => $tenantId,
                'client_id' => $clientId,
                'author_user_id' => $authorId,
                'source_type' => 'user',
                'body' => $body,
                'is_editable' => true,
            ]
        );

        DB::table('client_notes')
            ->where('id', $id)
            ->update([
                'created_at' => $createdAt->toDateTimeString(),
                'updated_at' => $createdAt->toDateTimeString(),
            ]);
    }

    private function upsertDocument(
        string $id,
        string $tenantId,
        string $clientId,
        string $uploadedByUserId,
        string $originalFilename,
        CarbonImmutable $createdAt,
    ): void {
        ClientDocument::query()->withoutGlobalScopes()->firstOrCreate(
            ['id' => $id],
            [
                'tenant_id' => $tenantId,
                'client_id' => $clientId,
                'uploaded_by_user_id' => $uploadedByUserId,
                'provenance' => 'seed',
                'attachment_category' => 'evidence',
                'original_filename' => $originalFilename,
                'stored_filename' => $originalFilename,
                'storage_disk' => 'local',
                'storage_path' => 'seeded-production/' . $originalFilename,
                'storage_reference' => 'seeded-production/' . $originalFilename,
                'mime_type' => 'application/pdf',
                'size_bytes' => 128000,
                'checksum_sha256' => hash('sha256', $id),
            ]
        );

        DB::table('client_documents')
            ->where('id', $id)
            ->update([
                'created_at' => $createdAt->toDateTimeString(),
                'updated_at' => $createdAt->toDateTimeString(),
            ]);
    }
}
