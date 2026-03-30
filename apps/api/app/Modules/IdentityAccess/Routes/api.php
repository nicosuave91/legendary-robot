<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Modules\IdentityAccess\Http\Controllers\Api\V1\AuthController;
use App\Modules\IdentityAccess\Http\Controllers\Api\V1\AccountController;

Route::prefix('api/v1')
    ->middleware(['api'])
    ->group(function (): void {
        Route::prefix('auth')->group(function (): void {
            Route::post('sign-in', [AuthController::class, 'signIn']);
            Route::middleware('auth:sanctum')->group(function (): void {
                Route::post('sign-out', [AuthController::class, 'signOut']);
                Route::get('me', [AuthController::class, 'me']);
            });
        });

        Route::middleware(['auth:sanctum'])->prefix('settings')->group(function (): void {
            Route::get('accounts', [AccountController::class, 'index']);
            Route::post('accounts', [AccountController::class, 'store']);
        });
    });
