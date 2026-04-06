<?php

declare(strict_types=1);

namespace App\Modules\Communications\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Modules\Communications\Http\Requests\ListCommunicationsInboxRequest;
use App\Modules\Communications\Services\CommunicationsInboxService;
use App\Modules\Shared\Support\ApiResponse;

final class CommunicationsInboxController extends Controller
{
    public function __construct(
        private readonly CommunicationsInboxService $communicationsInboxService,
    ) {
    }

    public function index(ListCommunicationsInboxRequest $request): JsonResponse
    {
        abort_unless($request->user()?->hasPermission('clients.communications.read'), 403);

        return ApiResponse::success(
            $this->communicationsInboxService->forActor($request->user(), $request->validated()),
            (string) $request->attributes->get('correlation_id', ''),
        );
    }
}
