<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Modules\WorkflowBuilder\Http\Controllers\Api\V1\WorkflowPublishController;
use App\Modules\WorkflowBuilder\Http\Controllers\Api\V1\WorkflowRunController;
use App\Modules\WorkflowBuilder\Http\Controllers\Api\V1\WorkflowsController;

Route::prefix('v1')
    ->middleware(['api', 'auth:sanctum'])
    ->group(function (): void {
        Route::get('workflows', [WorkflowsController::class, 'index'])->name('api.v1.workflows.index');
        Route::post('workflows', [WorkflowsController::class, 'store'])->name('api.v1.workflows.store');
        Route::get('workflows/{workflowId}', [WorkflowsController::class, 'show'])->name('api.v1.workflows.show');
        Route::patch('workflows/{workflowId}', [WorkflowsController::class, 'update'])->name('api.v1.workflows.update');
        Route::post('workflows/{workflowId}/publish', [WorkflowPublishController::class, 'store'])->name('api.v1.workflows.publish');
        Route::get('workflows/{workflowId}/runs', [WorkflowRunController::class, 'index'])->name('api.v1.workflows.runs.index');
        Route::get('workflows/{workflowId}/runs/{runId}', [WorkflowRunController::class, 'show'])->name('api.v1.workflows.runs.show');
    });
