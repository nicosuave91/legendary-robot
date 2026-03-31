<?php

declare(strict_types=1);

namespace App\Modules\Imports\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Imports\Models\Import;
use App\Modules\Imports\Models\ImportError;
use App\Modules\Imports\Models\ImportRow;
use App\Modules\Notifications\Services\NotificationPublisher;
use App\Modules\Shared\Audit\AuditLogger;

final class ImportValidationService
{
    public const PARSER_VERSION = 'imports-clients-csv-v1';

    public function __construct(
        private readonly ImportCsvParser $parser,
        private readonly ImportRowNormalizer $normalizer,
        private readonly AuditLogger $auditLogger,
        private readonly NotificationPublisher $notificationPublisher,
    ) {
    }

    public function markQueued(User $actor, Import $import, string $correlationId): Import
    {
        $import->fill([
            'status' => 'validation_queued',
            'validated_by_user_id' => (string) $actor->id,
            'last_correlation_id' => $correlationId,
        ]);
        $import->save();

        $this->auditLogger->record([
            'tenant_id' => (string) $actor->tenant_id,
            'actor_id' => (string) $actor->id,
            'action' => 'imports.validation.queued',
            'subject_type' => 'import',
            'subject_id' => (string) $import->id,
            'correlation_id' => $correlationId,
            'before_summary' => null,
            'after_summary' => [
                'status' => 'validation_queued',
            ],
        ]);

        return $import->refresh();
    }

    public function validate(Import $import, string $correlationId): Import
    {
        $import->fill([
            'status' => 'validating',
            'last_correlation_id' => $correlationId,
            'failure_summary' => null,
        ]);
        $import->save();

        $this->auditLogger->record([
            'tenant_id' => (string) $import->tenant_id,
            'actor_id' => $import->validated_by_user_id,
            'action' => 'imports.validation.started',
            'subject_type' => 'import',
            'subject_id' => (string) $import->id,
            'correlation_id' => $correlationId,
            'before_summary' => null,
            'after_summary' => [
                'status' => 'validating',
            ],
        ]);

        $absolutePath = Storage::disk((string) $import->storage_disk)->path((string) $import->storage_path);
        $parsedRows = $this->parser->parse($absolutePath);

        $summary = DB::transaction(function () use ($import, $parsedRows): array {
            ImportError::query()->withoutGlobalScopes()->where('tenant_id', $import->tenant_id)->where('import_id', $import->id)->delete();
            ImportRow::query()->withoutGlobalScopes()->where('tenant_id', $import->tenant_id)->where('import_id', $import->id)->delete();

            $validRowCount = 0;
            $invalidRowCount = 0;
            $warningCount = 0;

            foreach ($parsedRows as $index => $parsedRow) {
                $rowNumber = $index + 2;
                $normalized = $this->normalizer->normalizeClientsRow($parsedRow);
                $errors = $this->validateNormalizedRow($normalized);

                $status = $errors === [] ? 'valid' : 'invalid';
                if ($status === 'valid') {
                    $validRowCount++;
                } else {
                    $invalidRowCount++;
                }

                $row = ImportRow::query()->create([
                    'id' => (string) Str::uuid(),
                    'tenant_id' => (string) $import->tenant_id,
                    'import_id' => (string) $import->id,
                    'row_number' => $rowNumber,
                    'row_status' => $status,
                    'raw_payload' => $parsedRow,
                    'normalized_payload' => $normalized,
                    'failure_summary' => $errors === [] ? null : ['count' => count($errors)],
                    'validated_at' => now(),
                ]);

                foreach ($errors as $error) {
                    if (($error['severity'] ?? 'error') === 'warning') {
                        $warningCount++;
                    }

                    ImportError::query()->create([
                        'id' => (string) Str::uuid(),
                        'tenant_id' => (string) $import->tenant_id,
                        'import_id' => (string) $import->id,
                        'import_row_id' => (string) $row->id,
                        'row_number' => $rowNumber,
                        'field_name' => $error['fieldName'] ?? null,
                        'error_code' => (string) $error['errorCode'],
                        'severity' => (string) ($error['severity'] ?? 'error'),
                        'message' => (string) $error['message'],
                        'context_snapshot' => $error['contextSnapshot'] ?? null,
                    ]);
                }
            }

            return [
                'rowCount' => count($parsedRows),
                'validRowCount' => $validRowCount,
                'invalidRowCount' => $invalidRowCount,
                'warningCount' => $warningCount,
            ];
        });

        $status = $summary['invalidRowCount'] === 0 ? 'ready_to_commit' : 'validation_failed';
        $import->fill([
            'status' => $status,
            'row_count' => $summary['rowCount'],
            'valid_row_count' => $summary['validRowCount'],
            'invalid_row_count' => $summary['invalidRowCount'],
            'summary_snapshot' => [
                'warningCount' => $summary['warningCount'],
                'rowCount' => $summary['rowCount'],
            ],
            'validated_at' => now(),
            'failure_summary' => $status === 'validation_failed'
                ? ['message' => 'Validation blocked commit because one or more rows failed server checks.']
                : null,
        ]);
        $import->save();

        $action = $status === 'ready_to_commit' ? 'imports.validation.completed' : 'imports.validation.failed';
        $this->auditLogger->record([
            'tenant_id' => (string) $import->tenant_id,
            'actor_id' => $import->validated_by_user_id,
            'action' => $action,
            'subject_type' => 'import',
            'subject_id' => (string) $import->id,
            'correlation_id' => $correlationId,
            'before_summary' => null,
            'after_summary' => [
                'status' => $status,
                'rowCount' => $summary['rowCount'],
                'validRowCount' => $summary['validRowCount'],
                'invalidRowCount' => $summary['invalidRowCount'],
                'warningCount' => $summary['warningCount'],
            ],
        ]);

        $this->notificationPublisher->publishForTenant(
            tenantId: (string) $import->tenant_id,
            notificationType: 'import.validation',
            category: 'imports',
            title: $status === 'ready_to_commit' ? 'Import validation ready' : 'Import validation found errors',
            body: $status === 'ready_to_commit'
                ? sprintf('Import %s validated successfully and is ready for commit.', $import->original_filename)
                : sprintf('Import %s requires review before commit.', $import->original_filename),
            tone: $status === 'ready_to_commit' ? 'success' : 'warning',
            sourceEventType: 'import',
            sourceEventId: (string) $import->id,
            payloadSnapshot: [
                'status' => $status,
                'rowCount' => $summary['rowCount'],
                'validRowCount' => $summary['validRowCount'],
                'invalidRowCount' => $summary['invalidRowCount'],
            ],
            correlationId: $correlationId,
            actionUrl: sprintf('/app/imports/%s', $import->id),
        );

        return $import->refresh();
    }

    /**
     * @param array<string, mixed> $normalized
     * @return array<int, array<string, mixed>>
     */
    private function validateNormalizedRow(array $normalized): array
    {
        $errors = [];

        if (($normalized['displayName'] ?? null) === null) {
            $errors[] = [
                'fieldName' => 'displayName',
                'errorCode' => 'required',
                'severity' => 'error',
                'message' => 'Display name is required.',
            ];
        }

        if (($normalized['primaryEmail'] ?? null) !== null && filter_var($normalized['primaryEmail'], FILTER_VALIDATE_EMAIL) === false) {
            $errors[] = [
                'fieldName' => 'primaryEmail',
                'errorCode' => 'email',
                'severity' => 'error',
                'message' => 'Primary email must be a valid email address.',
            ];
        }

        if (($normalized['dateOfBirth'] ?? null) !== null) {
            try {
                \Illuminate\Support\Carbon::parse((string) $normalized['dateOfBirth']);
            } catch (\Throwable) {
                $errors[] = [
                    'fieldName' => 'dateOfBirth',
                    'errorCode' => 'date',
                    'severity' => 'error',
                    'message' => 'Date of birth must be a valid date.',
                ];
            }
        }

        return $errors;
    }
}
