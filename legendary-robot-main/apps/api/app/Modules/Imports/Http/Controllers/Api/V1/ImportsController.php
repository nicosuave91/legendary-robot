<?php

declare(strict_types=1);

namespace App\Modules\Imports\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Modules\Imports\Http\Requests\ListImportsRequest;
use App\Modules\Imports\Http\Requests\StoreImportRequest;
use App\Modules\Imports\Models\Import;
use App\Modules\Imports\Services\ImportCatalogService;
use App\Modules\Imports\Services\ImportUploadService;
use App\Modules\Shared\Support\ApiResponse;

final class ImportsController extends Controller
{
    public function __construct(
        private readonly ImportCatalogService $catalogService,
        private readonly ImportUploadService $uploadService,
    ) {
    }

    public function index(ListImportsRequest $request): JsonResponse
    {
        Gate::authorize('imports.read');

        return ApiResponse::success(
            $this->catalogService->listForUser($request->user(), $request->validated()),
            (string) $request->attributes->get('correlation_id', ''),
        );
    }

    public function store(StoreImportRequest $request): JsonResponse
    {
        Gate::authorize('imports.create');

        return ApiResponse::success(
            $this->uploadService->create(
                $request->user(),
                (string) $request->validated('importType'),
                $request->file('file'),
                (string) $request->attributes->get('correlation_id', ''),
            ),
            (string) $request->attributes->get('correlation_id', ''),
            201,
        );
    }

    public function show(ListImportsRequest $request, string $importId): JsonResponse
    {
        Gate::authorize('imports.read');

        $import = Import::query()->withoutGlobalScopes()->where('tenant_id', $request->user()->tenant_id)->where('id', $importId)->firstOrFail();

        return ApiResponse::success(
            $this->catalogService->detailForUser($request->user(), $import),
            (string) $request->attributes->get('correlation_id', ''),
        );
    }
}
