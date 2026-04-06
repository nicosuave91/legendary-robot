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
        $items = $this->collectItemsForClient($client, $filters);

        $pagination = $this->paginateItems($items, $filters);

        return [
            'clientId' => (string) $client->id,
            'items' => $pagination['items'],
            'paging' => [
                'nextCursor' => $pagination['nextCursor'],
                'hasMore' => $pagination['hasMore'],
            ],
            'filters' => [
                'channel' => $channelFilter,
                'status' => $statusFilter,
            ],
            'refresh' => [
                'hasPendingRecentItems' => $pagination['hasPendingRecentItems'],
                'recommendedPollSeconds' => $pagination['hasPendingRecentItems'] ? 5 : null,
            ],
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function collectItemsForClient(Client $client, array $filters): array
    {
        $channelFilter = (string) ($filters['channel'] ?? 'all');
        $statusFilter = (string) ($filters['status'] ?? 'all');
        $items = [];

        $messageQuery = CommunicationMessage::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $client->tenant_id)
            ->where('client_id', $client->id)
            ->when($channelFilter !== 'all', fn ($query) => $query->whereIn('channel', $channelFilter === 'sms' ? ['sms', 'mms'] : [$channelFilter]))
            ->latest('created_at')
            ->limit(250);

        /** @var \Illuminate\Database\Eloquent\Collection<int, CommunicationMessage> $messages */
        $messages = $messageQuery->get();

        foreach ($messages as $message) {
            if ($this->shouldSkipMessage($message, $statusFilter)) {
                continue;
            }

            $attachments = CommunicationAttachment::query()
                ->withoutGlobalScopes()
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

            $events = DeliveryStatusEvent::query()
                ->withoutGlobalScopes()
                ->where('tenant_id', $client->tenant_id)
                ->where('subject_type', 'communication_message')
                ->where('subject_id', $message->id)
                ->orderByDesc('received_at')
                ->get();

            $latestEvent = $events->first();
            $item = $this->communicationCommandService->presentMessage($message, $attachments, true);
            $item['status'] = $this->statusProjector->project(
                (string) $message->lifecycle_status,
                $message->provider_status,
                $message->failure_code,
                $message->failure_message,
                $latestEvent?->signature_verified ? 'provider_callback' : ($message->provider_message_id ? 'provider_submit' : 'internal'),
                optional($latestEvent?->received_at)->toIso8601String() ?? $message->updated_at?->toIso8601String(),
            );
            $item['evidence'] = [
                'source' => $latestEvent?->signature_verified ? 'provider_callback' : ($message->provider_message_id ? 'provider_submit' : 'internal'),
                'lastEventAt' => optional($latestEvent?->received_at)->toIso8601String(),
                'lastEventType' => $latestEvent?->provider_event_type,
                'eventCount' => $events->count(),
            ];

            $items[] = $item;
        }

        /** @var \Illuminate\Database\Eloquent\Collection<int, CallLog> $calls */
        $calls = CallLog::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $client->tenant_id)
            ->where('client_id', $client->id)
            ->latest('created_at')
            ->limit(250)
            ->get();

        foreach ($calls as $callLog) {
            if ($channelFilter !== 'all' && $channelFilter !== 'voice') {
                continue;
            }

            if ($this->shouldSkipCall($callLog, $statusFilter)) {
                continue;
            }

            $events = DeliveryStatusEvent::query()
                ->withoutGlobalScopes()
                ->where('tenant_id', $client->tenant_id)
                ->where('subject_type', 'call_log')
                ->where('subject_id', $callLog->id)
                ->orderByDesc('received_at')
                ->get();

            $latestEvent = $events->first();
            $item = $this->communicationCommandService->presentCall($callLog, true);
            $item['status'] = $this->statusProjector->project(
                (string) $callLog->lifecycle_status,
                null,
                $callLog->failure_code,
                $callLog->failure_message,
                $latestEvent?->signature_verified ? 'provider_callback' : ($callLog->provider_call_id ? 'provider_submit' : 'internal'),
                optional($latestEvent?->received_at)->toIso8601String() ?? $callLog->updated_at?->toIso8601String(),
            );
            $item['evidence'] = [
                'source' => $latestEvent?->signature_verified ? 'provider_callback' : ($callLog->provider_call_id ? 'provider_submit' : 'internal'),
                'lastEventAt' => optional($latestEvent?->received_at)->toIso8601String(),
                'lastEventType' => $latestEvent?->provider_event_type,
                'eventCount' => $events->count(),
            ];

            $items[] = $item;
        }

        usort($items, fn (array $left, array $right): int => strcmp((string) ($right['occurredAt'] ?? ''), (string) ($left['occurredAt'] ?? '')));

        return $items;
    }

    /**
     * @param list<array<string, mixed>> $items
     * @return array{items:list<array<string, mixed>>,nextCursor:?string,hasMore:bool,hasPendingRecentItems:bool}
     */
    public function paginateItems(array $items, array $filters): array
    {
        $limit = min(max((int) ($filters['limit'] ?? 50), 1), 100);
        $offset = $this->decodeCursor($filters['cursor'] ?? null);
        $slice = array_slice($items, $offset, $limit);
        $hasMore = ($offset + $limit) < count($items);

        return [
            'items' => $slice,
            'nextCursor' => $hasMore ? $this->encodeCursor($offset + $limit) : null,
            'hasMore' => $hasMore,
            'hasPendingRecentItems' => collect($items)->contains(fn (array $item): bool => !((bool) ($item['status']['isTerminal'] ?? true))),
        ];
    }

    private function shouldSkipMessage(CommunicationMessage $message, string $statusFilter): bool
    {
        if ($statusFilter === 'pending' && in_array((string) $message->lifecycle_status, ['delivered', 'received', 'failed', 'undelivered', 'bounced', 'dropped'], true)) {
            return true;
        }

        if ($statusFilter === 'failed' && !in_array((string) $message->lifecycle_status, ['failed', 'undelivered', 'bounced', 'dropped'], true)) {
            return true;
        }

        return false;
    }

    private function shouldSkipCall(CallLog $callLog, string $statusFilter): bool
    {
        if ($statusFilter === 'pending' && in_array((string) $callLog->lifecycle_status, ['completed', 'failed', 'busy', 'no_answer', 'canceled'], true)) {
            return true;
        }

        if ($statusFilter === 'failed' && !in_array((string) $callLog->lifecycle_status, ['failed', 'busy', 'no_answer', 'canceled'], true)) {
            return true;
        }

        return false;
    }

    private function encodeCursor(int $offset): string
    {
        return base64_encode((string) $offset);
    }

    private function decodeCursor(mixed $cursor): int
    {
        if (!is_string($cursor) || trim($cursor) === '') {
            return 0;
        }

        $decoded = base64_decode($cursor, true);
        if ($decoded === false) {
            return 0;
        }

        return max(0, (int) $decoded);
    }
}