<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Modules\Onboarding\Http\Controllers\Api\V1\OnboardingController;

Route::prefix('v1/onboarding')
    ->middleware(['api', 'auth:sanctum'])
    ->group(function (): void {
        Route::get('state', [OnboardingController::class, 'state']);
        Route::patch('profile-confirmation', [OnboardingController::class, 'confirmProfile']);
        Route::patch('industry-selection', [OnboardingController::class, 'selectIndustry']);
        Route::post('complete', [OnboardingController::class, 'complete']);
    });
