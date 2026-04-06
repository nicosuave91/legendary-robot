<?php

declare(strict_types=1);

namespace App\Modules\RulesLibrary\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Modules\RulesLibrary\Http\Requests\PublishRuleRequest;
use App\Modules\RulesLibrary\Models\Rule;
use App\Modules\RulesLibrary\Services\RulePublishService;
use App\Modules\Shared\Support\ApiResponse;

final class RulePublishController extends Controller
{
    public function __construct(
        private readonly RulePublishService $publishService,
    ) {
    }

    public function store(PublishRuleRequest $request, string $ruleId): JsonResponse
    {
        $rule = Rule::query()->withoutGlobalScopes()->where('tenant_id', $request->user()->tenant_id)->where('id', $ruleId)->firstOrFail();
        Gate::authorize('rules.publish', $rule);

        return ApiResponse::success(
            $this->publishService->publish($request->user(), $rule, (string) $request->attributes->get('correlation_id', '')),
            (string) $request->attributes->get('correlation_id', ''),
            201,
        );
    }
}