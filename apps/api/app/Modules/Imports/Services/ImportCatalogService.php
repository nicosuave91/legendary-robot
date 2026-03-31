<?php

declare(strict_types=1);

namespace App\Modules\Imports\Services;

use App\Modules\IdentityAccess\Models\User;
use App\Modules\Imports\Models\Import;
use App\Modules\Imports\Models\ImportError;
use App\Modules\Imports\Models\ImportRow;

final class ImportCatalogService
{
    public function listForUser(User $actor, array $filters = []): array
    {
        $query = Import::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $actor->tenant_id)
            ->latest('created_at');

        if (($filters['status'] ?? null) !== null) {
            $query->where('status', $filters['status']);
        }

        if (($filters['importType'] ?? null) !== null) {
            $query->where('import_type', $filters['importType']);
        }

        $items = $query->get();

        return [
            'items' => $items->map(fn (Import $import): array => $this->serializeSummary($import))->values()->all(),
            'meta' => [
                'total' => $items->count(),
            ],
        ];
    }

    public function detailForUser(User $actor, Import $import): array
    {
        abort_unless((string) $import->tenant_id === (string) $actor->tenant_id, 404);

        $previewRows = ImportRow::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $actor->tenant_id)
            ->where('import_id', $import->id)
            ->orderBy('row_number')
            ->limit(25)
            ->get();

        $previewErrors = ImportError::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $actor->tenant_id)
            ->where('import_id', $import->id)
            ->orderBy('row_number')
            ->limit(50)
            ->get();

        return [
            'import' => $this->serializeSummary($import) + [
                'summary' => [
                    'blockingErrorCount' => (int) $import->invalid_row_count,
                    'warningCount' => (int) (($import->summary_snapshot['warningCount'] ?? 0)),
                    'createdTargetCount' => (int) $import->committed_row_count,
                ],
                'previewRows' => $previewRows->map(fn (ImportRow $row): array => [
                    'id' => (string) $row->id,
                    'rowNumber' => (int) $row->row_number,
                    'rowStatus' => (string) $row->row_status,
                    'normalizedPayload' => $row->normalized_payload ?? [],
                    'targetSubjectType' => $row->target_subject_type,
                    'targetSubjectId' => $row->target_subject_id,
                ])->values()->all(),
                'previewErrors' => $previewErrors->map(fn (ImportError $error): array => $this->serializeError($error))->values()->all(),
                'latestFailureSummary' => $import->failure_summary,
                'latestCorrelationId' => $import->last_correlation_id,
                'storageReference' => $import->storage_reference,
                'uploadedAt' => $import->created_at?->toIso8601String(),
                'validatedAt' => $import->validated_at?->toIso8601String(),
                'committedAt' => $import->committed_at?->toIso8601String(),
            ],
        ];
    }

    public function errorListForUser(User $actor, Import $import, array $filters = []): array
    {
        abort_unless((string) $import->tenant_id === (string) $actor->tenant_id, 404);

        $query = ImportError::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $actor->tenant_id)
            ->where('import_id', $import->id)
            ->orderBy('row_number');

        if (($filters['severity'] ?? null) !== null) {
            $query->where('severity', $filters['severity']);
        }

        $items = $query->get();

        return [
            'items' => $items->map(fn (ImportError $error): array => $this->serializeError($error))->values()->all(),
            'meta' => [
                'total' => $items->count(),
            ],
        ];
    }

    public function serializeSummary(Import $import): array
    {
        return [
            'id' => (string) $import->id,
            'importType' => (string) $import->import_type,
            'fileFormat' => (string) $import->file_format,
            'originalFilename' => (string) $import->original_filename,
            'status' => (string) $import->status,
            'rowCount' => (int) $import->row_count,
            'validRowCount' => (int) $import->valid_row_count,
            'invalidRowCount' => (int) $import->invalid_row_count,
            'committedRowCount' => (int) $import->committed_row_count,
            'uploadedByUserId' => $import->uploaded_by_user_id,
            'validatedByUserId' => $import->validated_by_user_id,
            'committedByUserId' => $import->committed_by_user_id,
            'parserVersion' => $import->parser_version,
            'canValidate' => in_array((string) $import->status, ['uploaded', 'validation_failed'], true),
            'canCommit' => (string) $import->status === 'ready_to_commit',
            'uploadedAt' => $import->created_at?->toIso8601String(),
            'validatedAt' => $import->validated_at?->toIso8601String(),
            'committedAt' => $import->committed_at?->toIso8601String(),
        ];
    }

    private function serializeError(ImportError $error): array
    {
        return [
            'id' => (string) $error->id,
            'rowNumber' => (int) $error->row_number,
            'fieldName' => $error->field_name,
            'errorCode' => (string) $error->error_code,
            'severity' => (string) $error->severity,
            'message' => (string) $error->message,
            'contextSnapshot' => $error->context_snapshot ?? [],
        ];
    }
}
