<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Modules\Applications\Http\Controllers\Api\V1\ApplicationStatusTransitionController;
use App\Modules\Applications\Http\Controllers\Api\V1\ClientApplicationsController;

Route::prefix('api/v1/clients')
    ->middleware(['api', 'auth:sanctum'])
    ->group(function (): void {
        Route::get('{clientId}/applications', [ClientApplicationsController::class, 'index'])->name('api.v1.clients.applications.index');
        Route::post('{clientId}/applications', [ClientApplicationsController::class, 'store'])->name('api.v1.clients.applications.store');
        Route::get('{clientId}/applications/{applicationId}', [ClientApplicationsController::class, 'show'])->name('api.v1.clients.applications.show');
        Route::post('{clientId}/applications/{applicationId}/status-transitions', [ApplicationStatusTransitionController::class, 'store'])->name('api.v1.clients.applications.status-transitions.store');
    });
