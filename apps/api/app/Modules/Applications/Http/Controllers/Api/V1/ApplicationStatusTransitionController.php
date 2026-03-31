<?php

declare(strict_types=1);

namespace App\Modules\Applications\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Modules\Applications\Http\Requests\TransitionApplicationStatusRequest;
use App\Modules\Applications\Models\Application;
use App\Modules\Applications\Services\ApplicationQueryService;
use App\Modules\Applications\Services\ApplicationStatusTransitionService;
use App\Modules\Clients\Models\Client;
use App\Modules\Shared\Support\ApiResponse;

final class ApplicationStatusTransitionController extends Controller
{
    public function __construct(
        private readonly ApplicationStatusTransitionService $applicationStatusTransitionService,
        private readonly ApplicationQueryService $applicationQueryService,
    ) {
    }

    public function store(TransitionApplicationStatusRequest $request, string $clientId, string $applicationId): JsonResponse
    {
        $client = Client::query()->withoutGlobalScopes()->where('tenant_id', $request->user()->tenant_id)->where('id', $clientId)->firstOrFail();
        $application = Application::query()->withoutGlobalScopes()
            ->where('tenant_id', $request->user()->tenant_id)
            ->where('client_id', $client->id)
            ->where('id', $applicationId)
            ->firstOrFail();

        Gate::authorize('clients.applications.status.transition', $application);

        $result = $this->applicationStatusTransitionService->transition(
            actor: $request->user(),
            client: $client,
            application: $application,
            payload: $request->validated(),
            correlationId: (string) $request->attributes->get('correlation_id', ''),
        );

        $payload = array_merge($result['payload'], [
            'application' => $this->applicationQueryService->detailForClient($request->user(), $client, $application->fresh()),
        ]);

        return ApiResponse::success(
            $payload,
            (string) $request->attributes->get('correlation_id', ''),
            $result['statusCode'],
        );
    }
}
