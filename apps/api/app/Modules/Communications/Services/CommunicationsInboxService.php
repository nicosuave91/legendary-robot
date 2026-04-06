<?php

declare(strict_types=1);

namespace App\Modules\Communications\Services;

use App\Modules\Clients\Models\Client;
use App\Modules\IdentityAccess\Models\User;

final class CommunicationsInboxService
{
    public function __construct(
        private readonly CommunicationTimelineService $communicationTimelineService,
        private readonly ClientCommunicationAccessService $clientCommunicationAccessService,
    ) {
    }

    public function forActor(User $actor, array $filters): array
    {
        $channelFilter = (string) ($filters['channel'] ?? 'all');
        $statusFilter = (string) ($filters['status'] ?? 'all');
        $search = trim((string) ($filters['search'] ?? ''));

        /** @var \Illuminate\Database\Eloquent\Collection<int, Client> $clients */
        $clients = Client::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $actor->tenant_id)
            ->with('owner')
            ->get();

        $accessibleClients = $clients
            ->filter(fn (Client $client): bool => $this->clientCommunicationAccessService->canRead($actor, $client))
            ->values();

        $items = [];

        foreach ($accessibleClients as $client) {
            $clientItems = $this->communicationTimelineService->collectItemsForClient($client, [
                'channel' => $channelFilter,
                'status' => $statusFilter,
            ]);

            foreach ($clientItems as $item) {
                if ($search !== '' && !$this->matchesSearch($client, $item, $search)) {
                    continue;
                }

                $items[] = [
                    'client' => [
                        'id' => (string) $client->id,
                        'displayName' => (string) $client->display_name,
                        'status' => (string) $client->status,
                        'ownerDisplayName' => $client->owner?->name,
                        'primaryEmail' => $client->primary_email,
                        'primaryPhone' => $client->primary_phone,
                        'lastActivityAt' => $client->last_activity_at?->toIso8601String(),
                    ],
                    'timelineItem' => $item,
                    '_sortAt' => (string) $item['occurredAt'],
                ];
            }
        }

        usort($items, fn (array $left, array $right): int => strcmp((string) $right['_sortAt'], (string) $left['_sortAt']));

        $pagination = $this->communicationTimelineService->paginateItems($items, $filters);
        $pagedItems = array_map(function (array $item): array {
            unset($item['_sortAt']);

            return $item;
        }, $pagination['items']);

        return [
            'items' => $pagedItems,
            'paging' => [
                'nextCursor' => $pagination['nextCursor'],
                'hasMore' => $pagination['hasMore'],
            ],
            'filters' => [
                'search' => $search !== '' ? $search : null,
                'channel' => $channelFilter,
                'status' => $statusFilter,
            ],
            'refresh' => [
                'hasPendingRecentItems' => $pagination['hasPendingRecentItems'],
                'recommendedPollSeconds' => $pagination['hasPendingRecentItems'] ? 5 : null,
            ],
            'summary' => [
                'clientCount' => $accessibleClients->count(),
                'itemCount' => count($items),
            ],
        ];
    }

    /**
     * @param array<string, mixed> $timelineItem
     */
    private function matchesSearch(Client $client, array $timelineItem, string $search): bool
    {
        $needle = mb_strtolower($search);
        $haystacks = [
            (string) $client->display_name,
            (string) ($client->primary_email ?? ''),
            (string) ($client->primary_phone ?? ''),
            (string) ($timelineItem['content']['subject'] ?? ''),
            (string) ($timelineItem['content']['preview'] ?? ''),
            (string) ($timelineItem['counterpart']['address'] ?? ''),
        ];

        foreach ($haystacks as $haystack) {
            if ($haystack !== '' && str_contains(mb_strtolower($haystack), $needle)) {
                return true;
            }
        }

        return false;
    }
}