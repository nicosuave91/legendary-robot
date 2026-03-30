<?php

declare(strict_types=1);

namespace App\Modules\Clients\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Modules\Clients\Http\Requests\StoreClientDocumentRequest;
use App\Modules\Clients\Models\Client;
use App\Modules\Clients\Services\ClientDocumentService;
use App\Modules\Shared\Support\ApiResponse;

final class ClientDocumentController extends Controller
{
    public function __construct(
        private readonly ClientDocumentService $clientDocumentService,
    ) {
    }

    public function store(StoreClientDocumentRequest $request, string $clientId): JsonResponse
    {
        $client = Client::query()->withoutGlobalScopes()->where('tenant_id', $request->user()->tenant_id)->where('id', $clientId)->firstOrFail();
        Gate::authorize('clients.documents.create', $client);

        return ApiResponse::success(
            $this->clientDocumentService->create(
                actor: $request->user(),
                client: $client,
                file: $request->file('file'),
                attachmentCategory: $request->string('attachmentCategory')->toString() ?: null,
                correlationId: (string) $request->attributes->get('correlation_id', ''),
            ),
            (string) $request->attributes->get('correlation_id', ''),
            201,
        );
    }
}
