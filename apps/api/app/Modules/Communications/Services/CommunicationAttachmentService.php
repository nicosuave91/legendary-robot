<?php

declare(strict_types=1);

namespace App\Modules\Communications\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use App\Modules\Clients\Models\Client;
use App\Modules\Communications\Models\CommunicationAttachment;
use App\Modules\Shared\Storage\TenantFileStorage;

final class CommunicationAttachmentService
{
    public function __construct(
        private readonly TenantFileStorage $tenantFileStorage,
    ) {
    }

    public function storeForMessage(
        Client $client,
        string $subjectType,
        string $subjectId,
        string $channel,
        array $files,
        ?string $uploadedBy = null,
        string $provenance = 'manual_upload',
    ): array {
        $attachments = [];

        foreach ($files as $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }

            $attachmentId = (string) Str::uuid();
            $stored = $this->tenantFileStorage->storeCommunicationAttachment(
                tenantId: (string) $client->tenant_id,
                clientId: (string) $client->id,
                subjectId: $subjectId,
                attachmentId: $attachmentId,
                file: $file,
                channel: $channel,
            );

            $attachment = CommunicationAttachment::query()->create([
                'id' => $attachmentId,
                'tenant_id' => (string) $client->tenant_id,
                'client_id' => (string) $client->id,
                'attachable_type' => $subjectType,
                'attachable_id' => $subjectId,
                'source_channel' => $channel,
                'provenance' => $provenance,
                'storage_disk' => $stored['storageDisk'],
                'storage_path' => $stored['storagePath'],
                'storage_reference' => $stored['storageReference'],
                'original_filename' => $stored['originalFilename'],
                'stored_filename' => $stored['storedFilename'],
                'mime_type' => $stored['mimeType'],
                'size_bytes' => $stored['sizeBytes'],
                'checksum_sha256' => $stored['checksumSha256'],
                'scan_status' => 'pending',
                'uploaded_by' => $uploadedBy,
            ]);

            $attachments[] = [
                'id' => (string) $attachment->id,
                'originalFilename' => (string) $attachment->original_filename,
                'mimeType' => (string) $attachment->mime_type,
                'sizeBytes' => (int) $attachment->size_bytes,
                'provenance' => (string) $attachment->provenance,
                'storageReference' => (string) $attachment->storage_reference,
                'scanStatus' => (string) $attachment->scan_status,
            ];
        }

        return $attachments;
    }
}
