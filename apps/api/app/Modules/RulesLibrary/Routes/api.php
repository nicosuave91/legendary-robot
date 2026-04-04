<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Modules\RulesLibrary\Http\Controllers\Api\V1\RuleExecutionLogController;
use App\Modules\RulesLibrary\Http\Controllers\Api\V1\RulePublishController;
use App\Modules\RulesLibrary\Http\Controllers\Api\V1\RulesController;

Route::prefix('v1')
    ->middleware(['api', 'auth:sanctum'])
    ->group(function (): void {
        Route::get('rules', [RulesController::class, 'index'])->name('api.v1.rules.index');
        Route::post('rules', [RulesController::class, 'store'])->name('api.v1.rules.store');
        Route::get('rules/{ruleId}', [RulesController::class, 'show'])->name('api.v1.rules.show');
        Route::patch('rules/{ruleId}', [RulesController::class, 'update'])->name('api.v1.rules.update');
        Route::post('rules/{ruleId}/publish', [RulePublishController::class, 'store'])->name('api.v1.rules.publish');
        Route::get('rules/{ruleId}/execution-logs', [RuleExecutionLogController::class, 'index'])->name('api.v1.rules.execution-logs.index');
    });
