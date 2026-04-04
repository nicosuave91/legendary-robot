<?php

declare(strict_types=1);

namespace App\Modules\Communications\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Modules\Clients\Models\Client;
use App\Modules\Communications\Models\CommunicationAttachment;
use App\Modules\Communications\Models\CommunicationMessage;
use App\Modules\Shared\Storage\TenantFileStorage;

final class CommunicationAttachmentService
{
    public function __construct(
        private readonly TenantFileStorage $tenantFileStorage,
        private readonly CommunicationAttachmentGovernanceService $communicationAttachmentGovernanceService,
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

            $this->communicationAttachmentGovernanceService->assertManualUploadAllowed($channel, $file);

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
                'scan_requested_at' => now(),
                'uploaded_by' => $uploadedBy,
            ]);

            $attachments[] = $this->serializeAttachment($attachment);
        }

        return $attachments;
    }

    public function serializeForMessage(CommunicationMessage $message): array
    {
        return CommunicationAttachment::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $message->tenant_id)
            ->where('attachable_type', CommunicationMessage::class)
            ->where('attachable_id', $message->id)
            ->get()
            ->map(fn (CommunicationAttachment $attachment): array => $this->serializeAttachment($attachment))
            ->values()
            ->all();
    }

    public function cloneMessageAttachments(
        Client $client,
        CommunicationMessage $sourceMessage,
        string $targetSubjectType,
        string $targetSubjectId,
        ?string $uploadedBy = null,
        string $provenance = 'retry_clone',
    ): array {
        $attachments = CommunicationAttachment::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $client->tenant_id)
            ->where('attachable_type', CommunicationMessage::class)
            ->where('attachable_id', $sourceMessage->id)
            ->get();

        $clones = [];

        foreach ($attachments as $attachment) {
            $clone = CommunicationAttachment::query()->create([
                'id' => (string) Str::uuid(),
                'tenant_id' => (string) $client->tenant_id,
                'client_id' => (string) $client->id,
                'attachable_type' => $targetSubjectType,
                'attachable_id' => $targetSubjectId,
                'source_channel' => $attachment->source_channel,
                'provenance' => $provenance,
                'storage_disk' => $attachment->storage_disk,
                'storage_path' => $attachment->storage_path,
                'storage_reference' => $attachment->storage_reference,
                'original_filename' => $attachment->original_filename,
                'stored_filename' => $attachment->stored_filename,
                'mime_type' => $attachment->mime_type,
                'size_bytes' => $attachment->size_bytes,
                'checksum_sha256' => $attachment->checksum_sha256,
                'scan_status' => $attachment->scan_status,
                'scan_requested_at' => $attachment->scan_requested_at,
                'scanned_at' => $attachment->scanned_at,
                'scan_engine' => $attachment->scan_engine,
                'scan_result_detail' => $attachment->scan_result_detail,
                'quarantine_reason' => $attachment->quarantine_reason,
                'scan_updated_by' => $attachment->scan_updated_by,
                'provider_attachment_id' => null,
                'uploaded_by' => $uploadedBy,
            ]);

            $clones[] = $this->serializeAttachment($clone);
        }

        return $clones;
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function importTwilioInboundMedia(Client $client, CommunicationMessage $message, array $payload): array
    {
        $count = max(0, (int) ($payload['NumMedia'] ?? 0));
        if ($count === 0) {
            return [];
        }

        $sid = (string) config('services.twilio.sid');
        $authToken = (string) config('services.twilio.auth_token');
        $attachments = [];

        for ($index = 0; $index < $count; $index++) {
            $mediaUrl = (string) ($payload['MediaUrl' . $index] ?? '');
            if ($mediaUrl === '') {
                continue;
            }

            $mimeType = (string) ($payload['MediaContentType' . $index] ?? 'application/octet-stream');

            $request = Http::timeout(20);
            if ($sid !== '' && $authToken !== '') {
                $request = $request->withBasicAuth($sid, $authToken);
            }

            $response = $request->get($mediaUrl);
            if (!$response->successful()) {
                continue;
            }

            $attachmentId = (string) Str::uuid();
            $originalFilename = $this->inferOriginalFilename($mediaUrl, $index, $mimeType);

            $stored = $this->tenantFileStorage->storeCommunicationAttachmentBytes(
                tenantId: (string) $client->tenant_id,
                clientId: (string) $client->id,
                subjectId: (string) $message->id,
                attachmentId: $attachmentId,
                channel: (string) $message->channel,
                originalFilename: $originalFilename,
                mimeType: $mimeType,
                contents: (string) $response->body(),
            );

            $attachment = CommunicationAttachment::query()->create([
                'id' => $attachmentId,
                'tenant_id' => (string) $client->tenant_id,
                'client_id' => (string) $client->id,
                'attachable_type' => CommunicationMessage::class,
                'attachable_id' => (string) $message->id,
                'source_channel' => (string) $message->channel,
                'provenance' => 'provider_inbound',
                'storage_disk' => $stored['storageDisk'],
                'storage_path' => $stored['storagePath'],
                'storage_reference' => $stored['storageReference'],
                'original_filename' => $stored['originalFilename'],
                'stored_filename' => $stored['storedFilename'],
                'mime_type' => $stored['mimeType'],
                'size_bytes' => $stored['sizeBytes'],
                'checksum_sha256' => $stored['checksumSha256'],
                'scan_status' => 'pending',
                'scan_requested_at' => now(),
                'provider_attachment_id' => (string) ($payload['MediaSid' . $index] ?? ''),
            ]);

            $attachments[] = $this->serializeAttachment($attachment);
        }

        return $attachments;
    }

    private function serializeAttachment(CommunicationAttachment $attachment): array
    {
        return [
            'id' => (string) $attachment->id,
            'originalFilename' => (string) $attachment->original_filename,
            'mimeType' => (string) $attachment->mime_type,
            'sizeBytes' => (int) $attachment->size_bytes,
            'provenance' => (string) $attachment->provenance,
            'storageReference' => (string) $attachment->storage_reference,
            'scanStatus' => (string) ($attachment->scan_status ?? 'pending'),
        ];
    }

    private function inferOriginalFilename(string $mediaUrl, int $index, string $mimeType): string
    {
        $path = (string) parse_url($mediaUrl, PHP_URL_PATH);
        $basename = trim((string) basename($path));

        if ($basename !== '' && str_contains($basename, '.')) {
            return $basename;
        }

        return sprintf('twilio-media-%d.%s', $index + 1, $this->extensionForMime($mimeType));
    }

    private function extensionForMime(string $mimeType): string
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
