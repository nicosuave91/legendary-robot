<?php

declare(strict_types=1);

namespace App\Modules\Imports\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Modules\Imports\Http\Requests\ListImportErrorsRequest;
use App\Modules\Imports\Models\Import;
use App\Modules\Imports\Services\ImportCatalogService;
use App\Modules\Shared\Support\ApiResponse;

final class ImportErrorsController extends Controller
{
    public function __construct(
        private readonly ImportCatalogService $catalogService,
    ) {
    }

    public function index(ListImportErrorsRequest $request, string $importId): JsonResponse
    {
        Gate::authorize('imports.read');

        $import = Import::query()->withoutGlobalScopes()->where('tenant_id', $request->user()->tenant_id)->where('id', $importId)->firstOrFail();

        return ApiResponse::success(
            $this->catalogService->errorListForUser($request->user(), $import, $request->validated()),
            (string) $request->attributes->get('correlation_id', ''),
        );
    }
}
