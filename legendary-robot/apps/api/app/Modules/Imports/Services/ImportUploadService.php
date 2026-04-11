<?php

declare(strict_types=1);

namespace App\Modules\Imports\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Imports\Models\Import;
use App\Modules\Shared\Audit\AuditLogger;
use App\Modules\Shared\Storage\TenantFileStorage;

final class ImportUploadService
{
    public function __construct(
        private readonly TenantFileStorage $tenantFileStorage,
        private readonly AuditLogger $auditLogger,
        private readonly ImportCatalogService $catalogService,
    ) {
    }

    public function create(User $actor, string $importType, UploadedFile $file, string $correlationId): array
    {
        $importId = (string) Str::uuid();
        $storage = $this->tenantFileStorage->storeImportArtifact((string) $actor->tenant_id, $importId, 'original', $file);

        $import = Import::query()->create([
            'id' => $importId,
            'tenant_id' => (string) $actor->tenant_id,
            'import_type' => $importType,
            'file_format' => 'csv',
            'status' => 'uploaded',
            'uploaded_by_user_id' => (string) $actor->id,
            'validated_by_user_id' => null,
            'committed_by_user_id' => null,
            'original_filename' => $storage['originalFilename'],
            'stored_filename' => $storage['storedFilename'],
            'storage_disk' => $storage['storageDisk'],
            'storage_path' => $storage['storagePath'],
            'storage_reference' => $storage['storageReference'],
            'mime_type' => $storage['mimeType'],
            'size_bytes' => $storage['sizeBytes'],
            'checksum_sha256' => $storage['checksumSha256'],
            'parser_version' => ImportValidationService::PARSER_VERSION,
            'last_correlation_id' => $correlationId,
        ]);

        $this->auditLogger->record([
            'tenant_id' => (string) $actor->tenant_id,
            'actor_id' => (string) $actor->id,
            'action' => 'imports.uploaded',
            'subject_type' => 'import',
            'subject_id' => (string) $import->id,
            'correlation_id' => $correlationId,
            'before_summary' => null,
            'after_summary' => [
                'importType' => $importType,
                'filename' => $import->original_filename,
                'sizeBytes' => (int) $import->size_bytes,
                'status' => (string) $import->status,
            ],
        ]);

        return $this->catalogService->detailForUser($actor, $import);
    }
}
