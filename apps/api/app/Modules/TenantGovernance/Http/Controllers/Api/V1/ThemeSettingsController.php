<?php

declare(strict_types=1);

namespace App\Modules\TenantGovernance\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Modules\Shared\Support\ApiResponse;
use App\Modules\TenantGovernance\Http\Requests\UpdateThemeRequest;
use App\Modules\TenantGovernance\Services\ThemeSettingsService;

final class ThemeSettingsController extends Controller
{
    public function __construct(
        private readonly ThemeSettingsService $themeSettingsService,
    ) {
    }

    public function update(UpdateThemeRequest $request): JsonResponse
    {
        Gate::authorize('settings.theme.update');

        return ApiResponse::success(
            $this->themeSettingsService->update(
                actor: $request->user(),
                payload: $request->validated(),
                correlationId: (string) $request->attributes->get('correlation_id', ''),
            ),
            (string) $request->attributes->get('correlation_id', ''),
        );
    }

    public function show(Request $request): JsonResponse
    {
        Gate::authorize('settings.theme.read');

        $tenant = $request->user()->tenant;
        $theme = $tenant->themeSetting;

        return ApiResponse::success([
            'primary' => (string) ($theme !== null ? $theme->primary_color : '#1d4ed8'),
            'secondary' => (string) ($theme !== null ? $theme->secondary_color : '#0f172a'),
            'tertiary' => (string) ($theme !== null ? $theme->tertiary_color : '#64748b'),
        ], (string) $request->attributes->get('correlation_id', ''));
    }
}
