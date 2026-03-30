<?php

declare(strict_types=1);

namespace App\Modules\Clients\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use App\Modules\Clients\Models\Client;
use App\Modules\IdentityAccess\Models\User;

final class ClientQueryService
{
    public function __construct(
        private readonly ClientVisibilityService $clientVisibilityService,
    ) {
    }

    public function paginateForActor(User $actor, array $filters): array
    {
        $sortMap = [
            'display_name' => 'clients.display_name',
            'created_at' => 'clients.created_at',
            'updated_at' => 'clients.updated_at',
            'last_activity_at' => 'clients.last_activity_at',
        ];

        $sort = (string) ($filters['sort'] ?? 'updated_at');
        $direction = (string) ($filters['direction'] ?? 'desc');
        $perPage = (int) ($filters['perPage'] ?? 20);

        $query = $this->clientVisibilityService
            ->queryForActor($actor)
            ->with(['address', 'owner'])
            ->withCount(['notes', 'documents']);

        $search = trim((string) ($filters['search'] ?? ''));
        if ($search !== '') {
            $query->where(function ($builder) use ($search): void {
                $builder
                    ->where('clients.display_name', 'like', '%' . $search . '%')
                    ->orWhere('clients.primary_email', 'like', '%' . $search . '%')
                    ->orWhere('clients.primary_phone', 'like', '%' . $search . '%');
            });
        }

        if (!empty($filters['status'])) {
            $query->where('clients.status', (string) $filters['status']);
        }

        $query->orderBy($sortMap[$sort] ?? 'clients.updated_at', $direction === 'asc' ? 'asc' : 'desc');
        if (($sortMap[$sort] ?? null) !== 'clients.display_name') {
            $query->orderBy('clients.display_name');
        }

        /** @var LengthAwarePaginator $paginator */
        $paginator = $query->paginate($perPage);

        return [
            'items' => $paginator->getCollection()
                ->map(fn (Client $client): array => [
                    'id' => (string) $client->id,
                    'displayName' => (string) $client->display_name,
                    'status' => (string) $client->status,
                    'primaryEmail' => $client->primary_email,
                    'primaryPhone' => $client->primary_phone,
                    'city' => $client->address?->city,
                    'stateCode' => $client->address?->state_code,
                    'ownerDisplayName' => $client->owner?->name,
                    'notesCount' => (int) $client->notes_count,
                    'documentsCount' => (int) $client->documents_count,
                    'updatedAt' => $client->updated_at?->toIso8601String(),
                    'createdAt' => $client->created_at?->toIso8601String(),
                ])
                ->values()
                ->all(),
            'pagination' => [
                'page' => (int) $paginator->currentPage(),
                'perPage' => (int) $paginator->perPage(),
                'total' => (int) $paginator->total(),
                'totalPages' => (int) $paginator->lastPage(),
            ],
            'appliedFilters' => [
                'search' => $search !== '' ? $search : null,
                'status' => $filters['status'] ?? null,
                'sort' => $sort,
                'direction' => $direction === 'asc' ? 'asc' : 'desc',
            ],
        ];
    }
}
