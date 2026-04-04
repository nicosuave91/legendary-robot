\
<?php

declare(strict_types=1);

namespace App\Modules\TenantGovernance\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Modules\TenantGovernance\Http\Requests\StoreIndustryConfigurationRequest;
use App\Modules\TenantGovernance\Services\IndustryConfigurationService;
use App\Modules\Shared\Support\ApiResponse;

final class IndustryConfigurationController extends Controller
{
    public function __construct(
        private readonly IndustryConfigurationService $industryConfigurationService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('settings.industry-configurations.read');

        return ApiResponse::success(
            ['items' => $this->industryConfigurationService->listForActor($request->user())],
            (string) $request->attributes->get('correlation_id', ''),
        );
    }

    public function store(StoreIndustryConfigurationRequest $request): JsonResponse
    {
        Gate::authorize('settings.industry-configurations.create');

        return ApiResponse::success(
            $this->industryConfigurationService->createVersion(
                actor: $request->user(),
                payload: $request->validated(),
                correlationId: (string) $request->attributes->get('correlation_id', ''),
            ),
            (string) $request->attributes->get('correlation_id', ''),
            201,
        );
    }
}
