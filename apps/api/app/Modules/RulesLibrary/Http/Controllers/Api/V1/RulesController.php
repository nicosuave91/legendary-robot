<?php

declare(strict_types=1);

namespace App\Modules\RulesLibrary\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Modules\RulesLibrary\Http\Requests\CreateRuleRequest;
use App\Modules\RulesLibrary\Http\Requests\ListRulesRequest;
use App\Modules\RulesLibrary\Http\Requests\UpdateRuleDraftRequest;
use App\Modules\RulesLibrary\Models\Rule;
use App\Modules\RulesLibrary\Services\RuleCatalogService;
use App\Modules\RulesLibrary\Services\RuleDraftService;
use App\Modules\Shared\Support\ApiResponse;

final class RulesController extends Controller
{
    public function __construct(
        private readonly RuleCatalogService $catalogService,
        private readonly RuleDraftService $draftService,
    ) {
    }

    public function index(ListRulesRequest $request): JsonResponse
    {
        Gate::authorize('rules.read');

        return ApiResponse::success(
            $this->catalogService->listForUser($request->user(), $request->validated()),
            (string) $request->attributes->get('correlation_id', ''),
        );
    }

    public function store(CreateRuleRequest $request): JsonResponse
    {
        Gate::authorize('rules.create');

        return ApiResponse::success(
            $this->draftService->create($request->user(), $request->validated(), (string) $request->attributes->get('correlation_id', '')),
            (string) $request->attributes->get('correlation_id', ''),
            201,
        );
    }

    public function show(ListRulesRequest $request, string $ruleId): JsonResponse
    {
        Gate::authorize('rules.read');

        $rule = Rule::query()->withoutGlobalScopes()->where('tenant_id', $request->user()->tenant_id)->where('id', $ruleId)->firstOrFail();

        return ApiResponse::success(
            $this->catalogService->detailForUser($request->user(), $rule),
            (string) $request->attributes->get('correlation_id', ''),
        );
    }

    public function update(UpdateRuleDraftRequest $request, string $ruleId): JsonResponse
    {
        $rule = Rule::query()->withoutGlobalScopes()->where('tenant_id', $request->user()->tenant_id)->where('id', $ruleId)->firstOrFail();
        Gate::authorize('rules.update-draft', $rule);

        return ApiResponse::success(
            $this->draftService->update($request->user(), $rule, $request->validated(), (string) $request->attributes->get('correlation_id', '')),
            (string) $request->attributes->get('correlation_id', ''),
        );
    }
}