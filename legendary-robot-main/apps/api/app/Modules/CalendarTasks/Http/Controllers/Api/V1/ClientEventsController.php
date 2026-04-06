<?php

declare(strict_types=1);

namespace App\Modules\CalendarTasks\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Modules\CalendarTasks\Http\Requests\ListClientEventsRequest;
use App\Modules\CalendarTasks\Services\EventQueryService;
use App\Modules\Clients\Models\Client;
use App\Modules\Shared\Support\ApiResponse;

final class ClientEventsController extends Controller
{
    public function __construct(private readonly EventQueryService $eventQueryService) {}

    public function index(ListClientEventsRequest $request, string $clientId): JsonResponse
    {
        $client = Client::query()->withoutGlobalScopes()->where('tenant_id', $request->user()->tenant_id)->where('id', $clientId)->firstOrFail();
        Gate::authorize('clients.events.read', $client);

        return ApiResponse::success($this->eventQueryService->listForClient($request->user(), $client, $request->validated()), (string) $request->attributes->get('correlation_id', ''));
    }
}
