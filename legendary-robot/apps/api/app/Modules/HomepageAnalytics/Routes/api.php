<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Modules\HomepageAnalytics\Http\Controllers\Api\V1\DashboardController;

Route::prefix('v1/dashboard')
    ->middleware(['api', 'auth:sanctum'])
    ->group(function (): void {
        Route::get('summary', [DashboardController::class, 'summary'])->name('api.v1.dashboard.summary');
        Route::get('production', [DashboardController::class, 'production'])->name('api.v1.dashboard.production');
    });
