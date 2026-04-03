<?php

declare(strict_types=1);

namespace App\Modules\Applications\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Modules\Applications\Http\Requests\ListClientApplicationsRequest;
use App\Modules\Applications\Http\Requests\StoreApplicationRequest;
use App\Modules\Applications\Models\Application;
use App\Modules\Applications\Services\ApplicationQueryService;
use App\Modules\Applications\Services\ApplicationService;
use App\Modules\Clients\Models\Client;
use App\Modules\Shared\Support\ApiResponse;

final class ClientApplicationsController extends Controller
{
    public function __construct(
        private readonly ApplicationQueryService $applicationQueryService,
        private readonly ApplicationService $applicationService,
    ) {
    }

    public function index(ListClientApplicationsRequest $request, string $clientId): JsonResponse
    {
        $client = Client::query()->withoutGlobalScopes()->where('tenant_id', $request->user()->tenant_id)->where('id', $clientId)->firstOrFail();
        Gate::authorize('clients.applications.read', $client);

        return ApiResponse::success(
            $this->applicationQueryService->listForClient($request->user(), $client),
            (string) $request->attributes->get('correlation_id', ''),
        );
    }

    public function store(StoreApplicationRequest $request, string $clientId): JsonResponse
    {
        $client = Client::query()->withoutGlobalScopes()->where('tenant_id', $request->user()->tenant_id)->where('id', $clientId)->firstOrFail();
        Gate::authorize('clients.applications.create', $client);

        return ApiResponse::success(
            $this->applicationService->create(
                actor: $request->user(),
                client: $client,
                payload: $request->validated(),
                correlationId: (string) $request->attributes->get('correlation_id', ''),
            ),
            (string) $request->attributes->get('correlation_id', ''),
            201,
        );
    }

    public function show(ListClientApplicationsRequest $request, string $clientId, string $applicationId): JsonResponse
    {
        $client = Client::query()->withoutGlobalScopes()->where('tenant_id', $request->user()->tenant_id)->where('id', $clientId)->firstOrFail();
        Gate::authorize('clients.applications.read', $client);

        $application = Application::query()->withoutGlobalScopes()
            ->where('tenant_id', $request->user()->tenant_id)
            ->where('client_id', $client->id)
            ->where('id', $applicationId)
            ->firstOrFail();

        return ApiResponse::success(
            $this->applicationQueryService->detailForClient($request->user(), $client, $application),
            (string) $request->attributes->get('correlation_id', ''),
        );
    }
}
