<?php

declare(strict_types=1);

namespace App\Modules\Communications\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use App\Modules\Communications\Models\CommunicationAttachment;

final class CommunicationAttachmentGovernanceService
{
    public function assertManualUploadAllowed(string $channel, UploadedFile $file): void
    {
        $policy = $this->channelUploadPolicy($channel);
        $maxBytes = (int) ($policy['max_bytes'] ?? 0);
        $allowedMimeTypes = array_map('strtolower', (array) ($policy['allowed_mime_types'] ?? []));
        $mimeType = strtolower((string) ($file->getClientMimeType() ?: $file->getMimeType() ?: 'application/octet-stream'));
        $sizeBytes = (int) ($file->getSize() ?? 0);

        if ($maxBytes > 0 && $sizeBytes > $maxBytes) {
            throw ValidationException::withMessages(['attachments' => [sprintf('Attachment "%s" exceeds the %d byte policy for %s communications.', $file->getClientOriginalName(), $maxBytes, $channel)]]);
        }

        if ($allowedMimeTypes !== [] && !in_array($mimeType, $allowedMimeTypes, true)) {
            throw ValidationException::withMessages(['attachments' => [sprintf('Attachment "%s" with MIME type "%s" is not allowed for %s communications.', $file->getClientOriginalName(), $mimeType, $channel)]]);
        }
    }

    /**
     * @param Collection<int, CommunicationAttachment> $attachments
     */
    public function assertProviderEligible(string $providerName, string $channel, Collection $attachments): void
    {
        if ($attachments->isEmpty()) {
            return;
        }

        $policy = $this->providerPolicy($providerName);
        $requiredScanStatus = (string) config('communications.attachments.outbound.required_scan_status', 'clean');
        $maxBytes = (int) ($policy['max_bytes'] ?? 0);
        $allowedMimeTypes = array_map('strtolower', (array) ($policy['allowed_mime_types'] ?? []));

        /** @var CommunicationAttachment $attachment */
        foreach ($attachments as $attachment) {
            $status = strtolower((string) ($attachment->scan_status ?? 'pending'));

            if ($requiredScanStatus !== '' && $status !== strtolower($requiredScanStatus)) {
                throw new RuntimeException(sprintf('Attachment "%s" is not eligible for %s delivery because its scan status is "%s". It must be "%s" first.', $attachment->original_filename, $providerName, $status, $requiredScanStatus));
            }

            $mimeType = strtolower((string) $attachment->mime_type);
            if ($allowedMimeTypes !== [] && !in_array($mimeType, $allowedMimeTypes, true)) {
                throw new RuntimeException(sprintf('Attachment "%s" with MIME type "%s" is not allowed for %s outbound delivery.', $attachment->original_filename, $mimeType, $providerName));
            }

            if ($maxBytes > 0 && (int) $attachment->size_bytes > $maxBytes) {
                throw new RuntimeException(sprintf('Attachment "%s" exceeds the provider attachment policy for %s outbound delivery.', $attachment->original_filename, $providerName));
            }

            if (($attachment->storage_disk ?? '') === '' || ($attachment->storage_path ?? '') === '') {
                throw new RuntimeException(sprintf('Attachment "%s" is missing persisted storage metadata and cannot be delivered.', $attachment->original_filename));
            }
        }
    }

    public function canServePublicly(CommunicationAttachment $attachment): bool
    {
        $requiredScanStatus = strtolower((string) config('communications.attachments.public_delivery.required_scan_status', 'clean'));
        $status = strtolower((string) ($attachment->scan_status ?? 'pending'));

        return $requiredScanStatus === ''
            ? ($attachment->storage_disk ?? '') !== '' && ($attachment->storage_path ?? '') !== ''
            : $status === $requiredScanStatus && ($attachment->storage_disk ?? '') !== '' && ($attachment->storage_path ?? '') !== '';
    }

    public function updateScanStatus(CommunicationAttachment $attachment, string $status, ?string $engine = null, ?string $detail = null, ?string $quarantineReason = null, ?string $actorId = null): CommunicationAttachment
    {
        $normalizedStatus = strtolower(trim($status));

        $attachment->scan_status = $normalizedStatus;
        $attachment->scan_requested_at = $attachment->scan_requested_at ?? now();
        $attachment->scan_engine = $engine !== null && trim($engine) !== '' ? trim($engine) : $attachment->scan_engine;
        $attachment->scan_result_detail = $detail !== null && trim($detail) !== '' ? trim($detail) : null;
        $attachment->scan_updated_by = $actorId;

        if ($normalizedStatus === 'pending') {
            $attachment->scanned_at = null;
            $attachment->quarantine_reason = null;
        } else {
            $attachment->scanned_at = now();
            $attachment->quarantine_reason = in_array($normalizedStatus, ['rejected', 'quarantined'], true)
                ? ($quarantineReason !== null && trim($quarantineReason) !== '' ? trim($quarantineReason) : $attachment->quarantine_reason)
                : null;
        }

        $attachment->save();

        return $attachment->refresh();
    }

    /**
     * @return array<string, mixed>
     */
    public function serializeSummary(CommunicationAttachment $attachment): array
    {
        return [
            'id' => (string) $attachment->id,
            'originalFilename' => (string) $attachment->original_filename,
            'mimeType' => (string) $attachment->mime_type,
            'sizeBytes' => (int) $attachment->size_bytes,
            'scanStatus' => (string) ($attachment->scan_status ?? 'pending'),
            'scanRequestedAt' => $attachment->scan_requested_at?->toIso8601String(),
            'scannedAt' => $attachment->scanned_at?->toIso8601String(),
            'scanEngine' => $attachment->scan_engine,
            'scanResultDetail' => $attachment->scan_result_detail,
            'quarantineReason' => $attachment->quarantine_reason,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function channelUploadPolicy(string $channel): array
    {
        return (array) config('communications.attachments.upload.channels.' . $channel, []);
    }

    /**
     * @return array<string, mixed>
     */
    private function providerPolicy(string $providerName): array
    {
        return (array) config('communications.attachments.outbound.providers.' . $providerName, []);
    }
}
