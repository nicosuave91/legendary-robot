<?php

declare(strict_types=1);

namespace App\Modules\HomepageAnalytics\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Modules\HomepageAnalytics\Http\Requests\DashboardProductionRequest;
use App\Modules\HomepageAnalytics\Services\DashboardSummaryService;
use App\Modules\HomepageAnalytics\Services\ProductionMetricsService;
use App\Modules\Shared\Support\ApiResponse;

final class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardSummaryService $dashboardSummaryService,
        private readonly ProductionMetricsService $productionMetricsService,
    ) {
    }

    public function summary(Request $request): JsonResponse
    {
        Gate::authorize('dashboard.summary.read');

        return ApiResponse::success(
            $this->dashboardSummaryService->forActor($request->user()),
            (string) $request->attributes->get('correlation_id', ''),
        );
    }

    public function production(DashboardProductionRequest $request): JsonResponse
    {
        Gate::authorize('dashboard.production.read');

        return ApiResponse::success(
            $this->productionMetricsService->forActor(
                actor: $request->user(),
                window: (string) ($request->validated()['window'] ?? '30d'),
            ),
            (string) $request->attributes->get('correlation_id', ''),
        );
    }
}
