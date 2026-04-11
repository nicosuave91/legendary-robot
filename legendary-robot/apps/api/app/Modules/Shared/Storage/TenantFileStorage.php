<?php

declare(strict_types=1);

namespace App\Modules\Shared\Storage;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

final class TenantFileStorage
{
    public function storeClientDocument(string $tenantId, string $clientId, string $documentId, UploadedFile $file): array
    {
        return $this->storeFile(
            directory: sprintf('tenants/%s/clients/%s/documents/%s', $tenantId, $clientId, $documentId),
            fallbackFilename: $documentId,
            file: $file,
        );
    }

    public function storeCommunicationAttachment(
        string $tenantId,
        string $clientId,
        string $subjectId,
        string $attachmentId,
        UploadedFile $file,
        string $channel,
    ): array {
        return $this->storeFile(
            directory: sprintf(
                'tenants/%s/clients/%s/communications/%s/%s/attachments/%s',
                $tenantId,
                $clientId,
                $channel,
                $subjectId,
                $attachmentId,
            ),
            fallbackFilename: $attachmentId,
            file: $file,
        );
    }

    public function storeCommunicationAttachmentBytes(
        string $tenantId,
        string $clientId,
        string $subjectId,
        string $attachmentId,
        string $channel,
        string $originalFilename,
        string $mimeType,
        string $contents,
    ): array {
        $disk = 'local';
        $extension = strtolower((string) (pathinfo($originalFilename, PATHINFO_EXTENSION) ?: $this->extensionFromMime($mimeType)));
        $baseName = Str::slug(pathinfo($originalFilename, PATHINFO_FILENAME));
        $storedFilename = trim($baseName, '-') !== '' ? sprintf('%s.%s', $baseName, $extension) : sprintf('%s.%s', $attachmentId, $extension);
        $directory = sprintf(
            'tenants/%s/clients/%s/communications/%s/%s/attachments/%s',
            $tenantId,
            $clientId,
            $channel,
            $subjectId,
            $attachmentId,
        );
        $storagePath = $directory . '/' . $storedFilename;

        Storage::disk($disk)->put($storagePath, $contents);

        return [
            'storageDisk' => $disk,
            'storagePath' => $storagePath,
            'storageReference' => sprintf('%s:%s', $disk, $storagePath),
            'storedFilename' => $storedFilename,
            'originalFilename' => $originalFilename,
            'mimeType' => $mimeType,
            'sizeBytes' => strlen($contents),
            'checksumSha256' => hash('sha256', $contents),
        ];
    }

    public function storeImportArtifact(string $tenantId, string $importId, string $phase, UploadedFile $file): array
    {
        return $this->storeFile(
            directory: sprintf('tenants/%s/imports/%s/%s', $tenantId, $importId, $phase),
            fallbackFilename: $importId,
            file: $file,
        );
    }

    private function storeFile(string $directory, string $fallbackFilename, UploadedFile $file): array
    {
        $disk = 'local';
        $extension = strtolower((string) ($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin'));
        $baseName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $storedFilename = trim($baseName, '-') !== '' ? sprintf('%s.%s', $baseName, $extension) : sprintf('%s.%s', $fallbackFilename, $extension);
        $storagePath = Storage::disk($disk)->putFileAs($directory, $file, $storedFilename);

        return [
            'storageDisk' => $disk,
            'storagePath' => $storagePath,
            'storageReference' => sprintf('%s:%s', $disk, $storagePath),
            'storedFilename' => $storedFilename,
            'originalFilename' => (string) $file->getClientOriginalName(),
            'mimeType' => (string) ($file->getClientMimeType() ?? $file->getMimeType() ?? 'application/octet-stream'),
            'sizeBytes' => (int) $file->getSize(),
            'checksumSha256' => $file->getRealPath() ? hash_file('sha256', $file->getRealPath()) : null,
        ];
    }

    private function extensionFromMime(string $mimeType): string
    {
        return match (strtolower($mimeType)) {
            'image/jpeg', 'image/jpg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'application/pdf' => 'pdf',
            'text/plain' => 'txt',
            'audio/mpeg' => 'mp3',
            'audio/wav', 'audio/x-wav' => 'wav',
            'video/mp4' => 'mp4',
            default => 'bin',
        };
    }
}
