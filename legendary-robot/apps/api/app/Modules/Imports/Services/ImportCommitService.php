<?php

declare(strict_types=1);

namespace App\Modules\Imports\Services;

use Illuminate\Support\Facades\DB;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Imports\Jobs\CommitImportJob;
use App\Modules\Imports\Models\Import;
use App\Modules\Imports\Models\ImportRow;
use App\Modules\Clients\Services\ClientService;
use App\Modules\Notifications\Services\NotificationPublisher;
use App\Modules\Shared\Audit\AuditLogger;

final class ImportCommitService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly NotificationPublisher $notificationPublisher,
        private readonly ClientService $clientService,
    ) {
    }

    public function markQueued(User $actor, Import $import, string $correlationId): Import
    {
        abort_unless((string) $import->status === 'ready_to_commit', 422, 'Only validated imports can be committed.');

        $import->fill([
            'status' => 'commit_queued',
            'committed_by_user_id' => (string) $actor->id,
            'last_correlation_id' => $correlationId,
        ]);
        $import->save();

        $this->auditLogger->record([
            'tenant_id' => (string) $actor->tenant_id,
            'actor_id' => (string) $actor->id,
            'action' => 'imports.commit.queued',
            'subject_type' => 'import',
            'subject_id' => (string) $import->id,
            'correlation_id' => $correlationId,
            'before_summary' => null,
            'after_summary' => [
                'status' => 'commit_queued',
            ],
        ]);

        dispatch(new CommitImportJob((string) $actor->tenant_id, $correlationId, (string) $import->id));

        return $import->refresh();
    }

    public function commit(Import $import, string $correlationId): Import
    {
        $import->fill([
            'status' => 'committing',
            'last_correlation_id' => $correlationId,
            'failure_summary' => null,
        ]);
        $import->save();

        $this->auditLogger->record([
            'tenant_id' => (string) $import->tenant_id,
            'actor_id' => $import->committed_by_user_id,
            'action' => 'imports.commit.started',
            'subject_type' => 'import',
            'subject_id' => (string) $import->id,
            'correlation_id' => $correlationId,
            'before_summary' => null,
            'after_summary' => [
                'status' => 'committing',
            ],
        ]);

        try {
            $count = 0;

            DB::transaction(function () use ($import, $correlationId, &$count): void {
                $rows = ImportRow::query()
                    ->withoutGlobalScopes()
                    ->where('tenant_id', $import->tenant_id)
                    ->where('import_id', $import->id)
                    ->where('row_status', 'valid')
                    ->orderBy('row_number')
                    ->lockForUpdate()
                    ->get();

                $actor = User::query()->withoutGlobalScopes()->where('tenant_id', $import->tenant_id)->where('id', $import->committed_by_user_id)->firstOrFail();

                foreach ($rows as $row) {
                    $client = $this->clientService->createFromImport($actor, $row->normalized_payload ?? [], (string) $import->id, $correlationId);

                    $row->fill([
                        'row_status' => 'committed',
                        'target_subject_type' => 'client',
                        'target_subject_id' => $client['id'],
                        'committed_at' => now(),
                    ]);
                    $row->save();
                    $count++;
                }

                $import->fill([
                    'status' => 'committed',
                    'committed_row_count' => $count,
                    'committed_at' => now(),
                    'failure_summary' => null,
                ]);
                $import->save();
            });

            $this->auditLogger->record([
                'tenant_id' => (string) $import->tenant_id,
                'actor_id' => $import->committed_by_user_id,
                'action' => 'imports.commit.completed',
                'subject_type' => 'import',
                'subject_id' => (string) $import->id,
                'correlation_id' => $correlationId,
                'before_summary' => null,
                'after_summary' => [
                    'status' => 'committed',
                    'committedRowCount' => $count,
                ],
            ]);

            $this->notificationPublisher->publishForTenant(
                tenantId: (string) $import->tenant_id,
                notificationType: 'import.commit',
                category: 'imports',
                title: 'Import committed',
                body: sprintf('Import %s committed %d staged rows into governed client records.', $import->original_filename, $count),
                tone: 'success',
                sourceEventType: 'import',
                sourceEventId: (string) $import->id,
                payloadSnapshot: [
                    'status' => 'committed',
                    'committedRowCount' => $count,
                ],
                correlationId: $correlationId,
                actionUrl: sprintf('/app/imports/%s', $import->id),
            );
        } catch (\Throwable $throwable) {
            $import->fill([
                'status' => 'commit_failed',
                'failure_summary' => [
                    'message' => $throwable->getMessage(),
                ],
            ]);
            $import->save();

            $this->auditLogger->record([
                'tenant_id' => (string) $import->tenant_id,
                'actor_id' => $import->committed_by_user_id,
                'action' => 'imports.commit.failed',
                'subject_type' => 'import',
                'subject_id' => (string) $import->id,
                'correlation_id' => $correlationId,
                'before_summary' => null,
                'after_summary' => [
                    'status' => 'commit_failed',
                    'message' => $throwable->getMessage(),
                ],
            ]);

            $this->notificationPublisher->publishForTenant(
                tenantId: (string) $import->tenant_id,
                notificationType: 'import.commit',
                category: 'imports',
                title: 'Import commit failed',
                body: sprintf('Import %s failed during commit. Review the audit trail and error summary.', $import->original_filename),
                tone: 'danger',
                sourceEventType: 'import',
                sourceEventId: (string) $import->id,
                payloadSnapshot: [
                    'status' => 'commit_failed',
                    'message' => $throwable->getMessage(),
                ],
                correlationId: $correlationId,
                actionUrl: sprintf('/app/imports/%s', $import->id),
            );

            throw $throwable;
        }

        return $import->refresh();
    }
}
