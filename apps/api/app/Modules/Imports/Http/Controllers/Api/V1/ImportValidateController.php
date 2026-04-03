<?php

declare(strict_types=1);

namespace App\Modules\Imports\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Modules\Imports\Http\Requests\ValidateImportRequest;
use App\Modules\Imports\Jobs\ValidateImportJob;
use App\Modules\Imports\Models\Import;
use App\Modules\Imports\Services\ImportCatalogService;
use App\Modules\Imports\Services\ImportValidationService;
use App\Modules\Shared\Support\ApiResponse;
use Illuminate\Support\Facades\Gate;

final class ImportValidateController extends Controller
{
    public function __construct(
        private readonly ImportCatalogService $catalogService,
        private readonly ImportValidationService $validationService,
    ) {
    }

    public function store(ValidateImportRequest $request, string $importId): JsonResponse
    {
        $import = Import::query()->withoutGlobalScopes()->where('tenant_id', $request->user()->tenant_id)->where('id', $importId)->firstOrFail();
        Gate::authorize('imports.validate', $import);

        $correlationId = (string) $request->attributes->get('correlation_id', '');
        $import = $this->validationService->markQueued($request->user(), $import, $correlationId);

        dispatch(new ValidateImportJob((string) $request->user()->tenant_id, $correlationId, (string) $import->id));

        return ApiResponse::success(
            $this->catalogService->detailForUser($request->user(), $import),
            $correlationId,
        );
    }
}
