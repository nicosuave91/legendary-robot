<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Modules\Imports\Http\Controllers\Api\V1\ImportCommitController;
use App\Modules\Imports\Http\Controllers\Api\V1\ImportErrorsController;
use App\Modules\Imports\Http\Controllers\Api\V1\ImportsController;
use App\Modules\Imports\Http\Controllers\Api\V1\ImportValidateController;

Route::prefix('v1/imports')
    ->middleware(['api', 'auth:sanctum'])
    ->group(function (): void {
        Route::get('', [ImportsController::class, 'index'])->name('api.v1.imports.index');
        Route::post('', [ImportsController::class, 'store'])->name('api.v1.imports.store');
        Route::get('{importId}', [ImportsController::class, 'show'])->name('api.v1.imports.show');
        Route::get('{importId}/errors', [ImportErrorsController::class, 'index'])->name('api.v1.imports.errors.index');
        Route::post('{importId}/validate', [ImportValidateController::class, 'store'])->name('api.v1.imports.validate');
        Route::post('{importId}/commit', [ImportCommitController::class, 'store'])->name('api.v1.imports.commit');
    });
