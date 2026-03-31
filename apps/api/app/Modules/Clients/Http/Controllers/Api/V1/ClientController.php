<?php

declare(strict_types=1);

namespace App\Modules\Clients\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Modules\Clients\Http\Requests\ListClientsRequest;
use App\Modules\Clients\Http\Requests\StoreClientRequest;
use App\Modules\Clients\Http\Requests\UpdateClientRequest;
use App\Modules\Clients\Models\Client;
use App\Modules\Clients\Services\ClientQueryService;
use App\Modules\Clients\Services\ClientService;
use App\Modules\Clients\Services\ClientWorkspaceService;
use App\Modules\Shared\Support\ApiResponse;

final class ClientController extends Controller
{
    public function __construct(
        private readonly ClientQueryService $clientQueryService,
        private readonly ClientService $clientService,
        private readonly ClientWorkspaceService $clientWorkspaceService,
    ) {
    }

    public function index(ListClientsRequest $request): JsonResponse
    {
        Gate::authorize('clients.read');

        return ApiResponse::success(
            $this->clientQueryService->paginateForActor($request->user(), $request->validated()),
            (string) $request->attributes->get('correlation_id', ''),
        );
    }

    public function store(StoreClientRequest $request): JsonResponse
    {
        Gate::authorize('clients.create');

        return ApiResponse::success(
            $this->clientService->create(
                actor: $request->user(),
                payload: $request->validated(),
                correlationId: (string) $request->attributes->get('correlation_id', ''),
            ),
            (string) $request->attributes->get('correlation_id', ''),
            201,
        );
    }

    public function show(ListClientsRequest $request, string $clientId): JsonResponse
    {
        $client = Client::query()->withoutGlobalScopes()->where('tenant_id', $request->user()->tenant_id)->where('id', $clientId)->firstOrFail();
        Gate::authorize('clients.view', $client);

        return ApiResponse::success(
            $this->clientWorkspaceService->forActor($request->user(), $client),
            (string) $request->attributes->get('correlation_id', ''),
        );
    }

    public function update(UpdateClientRequest $request, string $clientId): JsonResponse
    {
        $client = Client::query()->withoutGlobalScopes()->where('tenant_id', $request->user()->tenant_id)->where('id', $clientId)->firstOrFail();
        Gate::authorize('clients.update', $client);

        return ApiResponse::success(
            $this->clientService->update(
                actor: $request->user(),
                client: $client,
                payload: $request->validated(),
                correlationId: (string) $request->attributes->get('correlation_id', ''),
            ),
            (string) $request->attributes->get('correlation_id', ''),
        );
    }
}
