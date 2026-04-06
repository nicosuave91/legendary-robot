<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Modules\Disposition\Http\Controllers\Api\V1\ClientDispositionTransitionController;

Route::middleware(['api', 'auth:sanctum'])
    ->prefix('v1/clients')
    ->group(function (): void {
        Route::post('{clientId}/disposition-transitions', [ClientDispositionTransitionController::class, 'store'])
            ->name('api.v1.clients.disposition-transitions.store');
    });
