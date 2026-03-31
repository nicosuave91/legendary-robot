<?php

declare(strict_types=1);

namespace App\Modules\Disposition\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Modules\Clients\Models\Client;
use App\Modules\Disposition\Http\Requests\TransitionClientDispositionRequest;
use App\Modules\Disposition\Services\DispositionTransitionService;
use App\Modules\Shared\Support\ApiResponse;

final class ClientDispositionTransitionController extends Controller
{
    public function __construct(
        private readonly DispositionTransitionService $transitionService,
    ) {
    }

    public function store(TransitionClientDispositionRequest $request, string $clientId): JsonResponse
    {
        $client = Client::query()->withoutGlobalScopes()->where('tenant_id', $request->user()->tenant_id)->where('id', $clientId)->firstOrFail();
        Gate::authorize('clients.disposition.transition', $client);

        $result = $this->transitionService->transition(
            actor: $request->user(),
            client: $client,
            payload: $request->validated(),
            correlationId: (string) $request->attributes->get('correlation_id', ''),
        );

        return ApiResponse::success(
            $result['payload'],
            (string) $request->attributes->get('correlation_id', ''),
            $result['statusCode'],
        );
    }
}
