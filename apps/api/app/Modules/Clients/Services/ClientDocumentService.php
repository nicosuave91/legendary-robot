<?php

declare(strict_types=1);

namespace App\Modules\Clients\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Modules\Clients\Models\Client;
use App\Modules\Clients\Models\ClientDocument;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Shared\Audit\AuditLogger;
use App\Modules\Shared\Storage\TenantFileStorage;

final class ClientDocumentService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly TenantFileStorage $tenantFileStorage,
    ) {
    }

    public function create(User $actor, Client $client, UploadedFile $file, ?string $attachmentCategory, string $correlationId): array
    {
        $documentId = (string) Str::uuid();
        $storedFile = $this->tenantFileStorage->storeClientDocument(
            tenantId: (string) $client->tenant_id,
            clientId: (string) $client->id,
            documentId: $documentId,
            file: $file,
        );

        $document = DB::transaction(function () use ($actor, $client, $documentId, $storedFile, $attachmentCategory): ClientDocument {
            $document = ClientDocument::query()->create([
                'id' => $documentId,
                'tenant_id' => (string) $client->tenant_id,
                'client_id' => (string) $client->id,
                'uploaded_by_user_id' => (string) $actor->id,
                'provenance' => 'manual_upload',
                'attachment_category' => $attachmentCategory,
                'original_filename' => (string) $storedFile['originalFilename'],
                'stored_filename' => (string) $storedFile['storedFilename'],
                'storage_disk' => (string) $storedFile['storageDisk'],
                'storage_path' => (string) $storedFile['storagePath'],
                'storage_reference' => (string) $storedFile['storageReference'],
                'mime_type' => (string) $storedFile['mimeType'],
                'size_bytes' => (int) $storedFile['sizeBytes'],
                'checksum_sha256' => $storedFile['checksumSha256'],
            ]);

            $client->forceFill(['last_activity_at' => now()])->save();

            return $document->load('uploadedBy');
        });

        $this->auditLogger->record([
            'tenant_id' => (string) $actor->tenant_id,
            'actor_id' => (string) $actor->id,
            'action' => 'clients.documents.create',
            'subject_type' => 'client_document',
            'subject_id' => (string) $document->id,
            'correlation_id' => $correlationId,
            'before_summary' => null,
            'after_summary' => json_encode([
                'clientId' => (string) $client->id,
                'filename' => $document->original_filename,
                'mimeType' => $document->mime_type,
                'sizeBytes' => $document->size_bytes,
            ], JSON_THROW_ON_ERROR),
        ]);

        return [
            'id' => (string) $document->id,
            'originalFilename' => (string) $document->original_filename,
            'mimeType' => (string) $document->mime_type,
            'sizeBytes' => (int) $document->size_bytes,
            'provenance' => (string) $document->provenance,
            'attachmentCategory' => $document->attachment_category,
            'uploadedByDisplayName' => (string) ($document->uploadedBy?->name ?? $actor->name),
            'uploadedAt' => $document->created_at?->toIso8601String(),
            'storageReference' => (string) $document->storage_reference,
        ];
    }
}
