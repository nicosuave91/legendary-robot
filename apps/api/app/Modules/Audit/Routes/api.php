<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Modules\Audit\Http\Controllers\Api\V1\AuditController;

Route::prefix('v1/audit')
    ->middleware(['api', 'auth:sanctum'])
    ->group(function (): void {
        Route::get('', [AuditController::class, 'index'])->name('api.v1.audit.index');
    });
