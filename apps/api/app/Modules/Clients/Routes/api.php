<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Modules\Clients\Http\Controllers\Api\V1\ClientController;
use App\Modules\Clients\Http\Controllers\Api\V1\ClientDocumentController;
use App\Modules\Clients\Http\Controllers\Api\V1\ClientNoteController;

Route::prefix('v1/clients')
    ->middleware(['api', 'auth:sanctum'])
    ->group(function (): void {
        Route::get('', [ClientController::class, 'index'])->name('api.v1.clients.index');
        Route::post('', [ClientController::class, 'store'])->name('api.v1.clients.store');
        Route::get('{clientId}', [ClientController::class, 'show'])->name('api.v1.clients.show');
        Route::patch('{clientId}', [ClientController::class, 'update'])->name('api.v1.clients.update');
        Route::post('{clientId}/notes', [ClientNoteController::class, 'store'])->name('api.v1.clients.notes.store');
        Route::post('{clientId}/documents', [ClientDocumentController::class, 'store'])->name('api.v1.clients.documents.store');
    });
