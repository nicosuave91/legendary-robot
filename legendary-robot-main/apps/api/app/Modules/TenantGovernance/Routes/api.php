<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Modules\TenantGovernance\Http\Controllers\Api\V1\ThemeSettingsController;
use App\Modules\TenantGovernance\Http\Controllers\Api\V1\IndustryConfigurationController;

Route::prefix('api/v1/settings')
    ->middleware(['api', 'auth:sanctum'])
    ->group(function (): void {
        Route::get('theme', [ThemeSettingsController::class, 'show']);
        Route::patch('theme', [ThemeSettingsController::class, 'update']);

        Route::get('industry-configurations', [IndustryConfigurationController::class, 'index']);
        Route::post('industry-configurations', [IndustryConfigurationController::class, 'store']);
    });
