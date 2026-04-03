<?php

declare(strict_types=1);

namespace App\Modules\RulesLibrary\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Modules\RulesLibrary\Http\Requests\ListRulesRequest;
use App\Modules\RulesLibrary\Models\Rule;
use App\Modules\RulesLibrary\Services\RuleCatalogService;
use App\Modules\Shared\Support\ApiResponse;

final class RuleExecutionLogController extends Controller
{
    public function __construct(
        private readonly RuleCatalogService $catalogService,
    ) {
    }

    public function index(ListRulesRequest $request, string $ruleId): JsonResponse
    {
        Gate::authorize('rules.execution-logs.read');

        $rule = Rule::query()->withoutGlobalScopes()->where('tenant_id', $request->user()->tenant_id)->where('id', $ruleId)->firstOrFail();

        return ApiResponse::success(
            $this->catalogService->executionLogsForUser($request->user(), $rule),
            (string) $request->attributes->get('correlation_id', ''),
        );
    }
}