<?php

declare(strict_types=1);

namespace App\Modules\Communications\Services;

use App\Modules\Clients\Models\Client;
use App\Modules\Communications\Models\CallLog;
use App\Modules\Communications\Models\CommunicationAttachment;
use App\Modules\Communications\Models\CommunicationMessage;
use App\Modules\Communications\Models\DeliveryStatusEvent;

final class CommunicationTimelineService
{
    public function __construct(
        private readonly CommunicationCommandService $communicationCommandService,
        private readonly CommunicationStatusProjector $statusProjector,
    ) {
    }

    public function forClient(Client $client, array $filters): array
    {
        $channelFilter = (string) ($filters['channel'] ?? 'all');
        $statusFilter = (string) ($filters['status'] ?? 'all');
        $limit = (int) ($filters['limit'] ?? 50);
        $items = [];

        $messages = CommunicationMessage::query()->withoutGlobalScopes()
            ->where('tenant_id', $client->tenant_id)
            ->where('client_id', $client->id)
            ->when($channelFilter !== 'all', fn ($query) => $query->whereIn('channel', $channelFilter === 'sms' ? ['sms', 'mms'] : [$channelFilter]))
            ->latest('created_at')
            ->limit($limit)
            ->get();

        foreach ($messages as $message) {
            if ($statusFilter === 'pending' && in_array((string) $message->lifecycle_status, ['delivered', 'received', 'failed', 'undelivered', 'bounced', 'dropped'], true)) {
                continue;
            }
            if ($statusFilter === 'failed' && !in_array((string) $message->lifecycle_status, ['failed', 'undelivered', 'bounced', 'dropped'], true)) {
                continue;
            }

            $attachments = CommunicationAttachment::query()->withoutGlobalScopes()
                ->where('tenant_id', $client->tenant_id)
                ->where('attachable_type', CommunicationMessage::class)
                ->where('attachable_id', $message->id)
                ->get()
                ->map(fn (CommunicationAttachment $attachment): array => [
                    'id' => (string) $attachment->id,
                    'originalFilename' => (string) $attachment->original_filename,
                    'mimeType' => (string) $attachment->mime_type,
                    'sizeBytes' => (int) $attachment->size_bytes,
                    'provenance' => (string) $attachment->provenance,
                    'storageReference' => (string) $attachment->storage_reference,
                    'scanStatus' => (string) $attachment->scan_status,
                ])->values()->all();

            $events = DeliveryStatusEvent::query()->withoutGlobalScopes()
                ->where('tenant_id', $client->tenant_id)
                ->where('subject_type', 'communication_message')
                ->where('subject_id', $message->id)
                ->orderByDesc('received_at')
                ->get();

            $item = $this->communicationCommandService->presentMessage($message, $attachments, true);
            $item['status'] = $this->statusProjector->project(
                (string) $message->lifecycle_status,
                $message->provider_status,
                $message->failure_code,
                $message->failure_message,
                $events->first()?->signature_verified ? 'provider_callback' : ($message->provider_message_id ? 'provider_submit' : 'internal'),
                optional($events->first()?->received_at)->toIso8601String() ?? $message->updated_at?->toIso8601String(),
            );
            $item['evidence'] = [
                'source' => $events->first()?->signature_verified ? 'provider_callback' : ($message->provider_message_id ? 'provider_submit' : 'internal'),
                'lastEventAt' => optional($events->first()?->received_at)->toIso8601String(),
                'lastEventType' => $events->first()?->provider_event_type,
                'eventCount' => $events->count(),
            ];
            $items[] = $item;
        }

        $calls = CallLog::query()->withoutGlobalScopes()
            ->where('tenant_id', $client->tenant_id)
            ->where('client_id', $client->id)
            ->latest('created_at')
            ->limit($limit)
            ->get();

        foreach ($calls as $callLog) {
            if ($channelFilter !== 'all' && $channelFilter !== 'voice') {
                continue;
            }
            if ($statusFilter === 'pending' && in_array((string) $callLog->lifecycle_status, ['completed', 'failed', 'busy', 'no_answer', 'canceled'], true)) {
                continue;
            }
            if ($statusFilter === 'failed' && !in_array((string) $callLog->lifecycle_status, ['failed', 'busy', 'no_answer', 'canceled'], true)) {
                continue;
            }

            $events = DeliveryStatusEvent::query()->withoutGlobalScopes()
                ->where('tenant_id', $client->tenant_id)
                ->where('subject_type', 'call_log')
                ->where('subject_id', $callLog->id)
                ->orderByDesc('received_at')
                ->get();

            $item = $this->communicationCommandService->presentCall($callLog, true);
            $item['status'] = $this->statusProjector->project(
                (string) $callLog->lifecycle_status,
                null,
                $callLog->failure_code,
                $callLog->failure_message,
                $events->first()?->signature_verified ? 'provider_callback' : ($callLog->provider_call_id ? 'provider_submit' : 'internal'),
                optional($events->first()?->received_at)->toIso8601String() ?? $callLog->updated_at?->toIso8601String(),
            );
            $item['evidence'] = [
                'source' => $events->first()?->signature_verified ? 'provider_callback' : ($callLog->provider_call_id ? 'provider_submit' : 'internal'),
                'lastEventAt' => optional($events->first()?->received_at)->toIso8601String(),
                'lastEventType' => $events->first()?->provider_event_type,
                'eventCount' => $events->count(),
            ];
            $items[] = $item;
        }

        usort($items, fn (array $left, array $right): int => strcmp((string) ($right['occurredAt'] ?? ''), (string) ($left['occurredAt'] ?? '')));
        $hasPendingRecentItems = collect($items)->contains(fn (array $item): bool => !((bool) ($item['status']['isTerminal'] ?? true)));

        return [
            'clientId' => (string) $client->id,
            'items' => array_slice($items, 0, $limit),
            'paging' => ['nextCursor' => null, 'hasMore' => false],
            'filters' => ['channel' => $channelFilter, 'status' => $statusFilter],
            'refresh' => ['hasPendingRecentItems' => $hasPendingRecentItems, 'recommendedPollSeconds' => $hasPendingRecentItems ? 5 : null],
        ];
    }
}
