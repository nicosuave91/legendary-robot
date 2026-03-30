<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Modules\IdentityAccess\Http\Controllers\Api\V1\AuthController;

Route::prefix('api/v1/auth')
    ->middleware(['api'])
    ->group(function (): void {
        Route::post('sign-in', [AuthController::class, 'signIn']);
        Route::post('sign-out', [AuthController::class, 'signOut']);
        Route::get('me', [AuthController::class, 'me']);
    });
