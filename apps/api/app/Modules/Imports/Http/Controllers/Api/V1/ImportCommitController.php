<?php

declare(strict_types=1);

namespace App\Modules\Imports\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Modules\Imports\Http\Requests\CommitImportRequest;
use App\Modules\Imports\Models\Import;
use App\Modules\Imports\Services\ImportCatalogService;
use App\Modules\Imports\Services\ImportCommitService;
use App\Modules\Shared\Support\ApiResponse;

final class ImportCommitController extends Controller
{
    public function __construct(
        private readonly ImportCatalogService $catalogService,
        private readonly ImportCommitService $commitService,
    ) {
    }

    public function store(CommitImportRequest $request, string $importId): JsonResponse
    {
        $import = Import::query()->withoutGlobalScopes()->where('tenant_id', $request->user()->tenant_id)->where('id', $importId)->firstOrFail();
        Gate::authorize('imports.commit', $import);

        $correlationId = (string) $request->attributes->get('correlation_id', '');
        $import = $this->commitService->markQueued($request->user(), $import, $correlationId);

        return ApiResponse::success(
            $this->catalogService->detailForUser($request->user(), $import),
            $correlationId,
        );
    }
}
