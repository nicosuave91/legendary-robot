<?php

declare(strict_types=1);

namespace App\Modules\Communications\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Modules\Clients\Models\Client;
use App\Modules\Communications\Http\Requests\ListClientCommunicationsRequest;
use App\Modules\Communications\Http\Requests\SendEmailRequest;
use App\Modules\Communications\Http\Requests\SendSmsRequest;
use App\Modules\Communications\Http\Requests\StartCallRequest;
use App\Modules\Communications\Services\CommunicationCommandService;
use App\Modules\Communications\Services\CommunicationTimelineService;
use App\Modules\Shared\Support\ApiResponse;

final class ClientCommunicationsController extends Controller
{
    public function __construct(
        private readonly CommunicationTimelineService $communicationTimelineService,
        private readonly CommunicationCommandService $communicationCommandService,
    ) {
    }

    public function index(ListClientCommunicationsRequest $request, string $clientId): JsonResponse
    {
        $client = Client::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $request->user()->tenant_id)
            ->where('id', $clientId)
            ->firstOrFail();

        Gate::authorize('clients.communications.read', $client);

        return ApiResponse::success(
            $this->communicationTimelineService->forClient($client, $request->validated()),
            (string) $request->attributes->get('correlation_id', ''),
        );
    }

    public function sendSms(SendSmsRequest $request, string $clientId): JsonResponse
    {
        $client = Client::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $request->user()->tenant_id)
            ->where('id', $clientId)
            ->firstOrFail();

        Gate::authorize('clients.communications.sms.send', $client);

        $payload = $request->validated();
        $payload['attachments'] = $request->file('attachments', []);

        return ApiResponse::success(
            $this->communicationCommandService->queueSms(
                actor: $request->user(),
                client: $client,
                payload: $payload,
                correlationId: (string) $request->attributes->get('correlation_id', ''),
            ),
            (string) $request->attributes->get('correlation_id', ''),
            201,
        );
    }

    public function sendEmail(SendEmailRequest $request, string $clientId): JsonResponse
    {
        $client = Client::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $request->user()->tenant_id)
            ->where('id', $clientId)
            ->firstOrFail();

        Gate::authorize('clients.communications.email.send', $client);

        $payload = $request->validated();
        $payload['attachments'] = $request->file('attachments', []);

        return ApiResponse::success(
            $this->communicationCommandService->queueEmail(
                actor: $request->user(),
                client: $client,
                payload: $payload,
                correlationId: (string) $request->attributes->get('correlation_id', ''),
            ),
            (string) $request->attributes->get('correlation_id', ''),
            201,
        );
    }

    public function startCall(StartCallRequest $request, string $clientId): JsonResponse
    {
        $client = Client::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $request->user()->tenant_id)
            ->where('id', $clientId)
            ->firstOrFail();

        Gate::authorize('clients.communications.call.create', $client);

        return ApiResponse::success(
            $this->communicationCommandService->queueCall(
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
