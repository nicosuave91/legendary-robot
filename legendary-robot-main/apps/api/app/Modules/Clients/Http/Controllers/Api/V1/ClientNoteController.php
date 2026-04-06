<?php

declare(strict_types=1);

namespace App\Modules\Clients\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Modules\Clients\Http\Requests\StoreClientNoteRequest;
use App\Modules\Clients\Models\Client;
use App\Modules\Clients\Services\ClientNoteService;
use App\Modules\Shared\Support\ApiResponse;

final class ClientNoteController extends Controller
{
    public function __construct(
        private readonly ClientNoteService $clientNoteService,
    ) {
    }

    public function store(StoreClientNoteRequest $request, string $clientId): JsonResponse
    {
        $client = Client::query()->withoutGlobalScopes()->where('tenant_id', $request->user()->tenant_id)->where('id', $clientId)->firstOrFail();
        Gate::authorize('clients.notes.create', $client);

        return ApiResponse::success(
            $this->clientNoteService->create(
                actor: $request->user(),
                client: $client,
                payload: $request->validated(),
                correlationId: (string) $request->attributes->get('correlation_id', ''),
            ),
            (string) $request->attributes->get('correlation_id', ''),
            201,
        );
    }
}
