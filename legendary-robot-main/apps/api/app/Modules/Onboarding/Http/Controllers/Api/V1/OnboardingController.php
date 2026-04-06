<?php

declare(strict_types=1);

namespace App\Modules\Onboarding\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Modules\Onboarding\Http\Requests\IndustrySelectionRequest;
use App\Modules\Onboarding\Http\Requests\ProfileConfirmationRequest;
use App\Modules\Onboarding\Http\Resources\OnboardingStateResource;
use App\Modules\Onboarding\Services\OnboardingService;
use App\Modules\Shared\Support\ApiResponse;

final class OnboardingController extends Controller
{
    public function __construct(
        private readonly OnboardingService $onboardingService,
    ) {
    }

    public function state(Request $request): JsonResponse
    {
        Gate::authorize('onboarding.state.read');

        return ApiResponse::success(
            (new OnboardingStateResource($this->onboardingService->getState($request->user())))->resolve(),
            (string) $request->attributes->get('correlation_id', ''),
        );
    }

    public function confirmProfile(ProfileConfirmationRequest $request): JsonResponse
    {
        Gate::authorize('onboarding.profile.confirm');

        return ApiResponse::success(
            (new OnboardingStateResource($this->onboardingService->confirmProfile(
                user: $request->user(),
                payload: $request->validated(),
                correlationId: (string) $request->attributes->get('correlation_id', ''),
            )))->resolve(),
            (string) $request->attributes->get('correlation_id', ''),
        );
    }

    public function selectIndustry(IndustrySelectionRequest $request): JsonResponse
    {
        Gate::authorize('onboarding.industry.select');

        return ApiResponse::success(
            (new OnboardingStateResource($this->onboardingService->selectIndustry(
                user: $request->user(),
                industry: $request->validated('industry'),
                correlationId: (string) $request->attributes->get('correlation_id', ''),
            )))->resolve(),
            (string) $request->attributes->get('correlation_id', ''),
        );
    }

    public function complete(Request $request): JsonResponse
    {
        Gate::authorize('onboarding.complete');

        return ApiResponse::success(
            (new OnboardingStateResource($this->onboardingService->complete(
                user: $request->user(),
                correlationId: (string) $request->attributes->get('correlation_id', ''),
            )))->resolve(),
            (string) $request->attributes->get('correlation_id', ''),
        );
    }
}
