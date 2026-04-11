<?php

declare(strict_types=1);

namespace App\Modules\Audit\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Modules\Audit\Http\Requests\ListAuditRequest;
use App\Modules\Audit\Services\AuditSearchService;
use App\Modules\Shared\Support\ApiResponse;

final class AuditController extends Controller
{
    public function __construct(
        private readonly AuditSearchService $searchService,
    ) {
    }

    public function index(ListAuditRequest $request): JsonResponse
    {
        Gate::authorize('audit.read');

        return ApiResponse::success(
            $this->searchService->listForUser($request->user(), $request->validated()),
            (string) $request->attributes->get('correlation_id', ''),
        );
    }
}
