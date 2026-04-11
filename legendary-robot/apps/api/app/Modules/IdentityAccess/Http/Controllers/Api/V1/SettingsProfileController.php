<?php

declare(strict_types=1);

namespace App\Modules\IdentityAccess\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Modules\IdentityAccess\Http\Requests\UpdateProfileRequest;
use App\Modules\IdentityAccess\Services\ProfileSettingsService;
use App\Modules\Shared\Support\ApiResponse;

final class SettingsProfileController extends Controller
{
    public function __construct(
        private readonly ProfileSettingsService $profileSettingsService,
    ) {
    }

    public function show(Request $request): JsonResponse
    {
        Gate::authorize('settings.profile.read');

        return ApiResponse::success(
            $this->profileSettingsService->snapshot($request->user()),
            (string) $request->attributes->get('correlation_id', ''),
        );
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        Gate::authorize('settings.profile.update');

        return ApiResponse::success(
            $this->profileSettingsService->updateSelf(
                actor: $request->user(),
                payload: $request->validated(),
                correlationId: (string) $request->attributes->get('correlation_id', ''),
            ),
            (string) $request->attributes->get('correlation_id', ''),
        );
    }
}
