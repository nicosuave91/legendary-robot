<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Modules\Communications\Http\Controllers\Api\V1\ClientCommunicationsController;

Route::prefix('api/v1/clients/{clientId}/communications')->middleware(['api', 'auth:sanctum'])->group(function (): void {
    Route::get('', [ClientCommunicationsController::class, 'index'])->name('api.v1.clients.communications.index');
    Route::post('sms', [ClientCommunicationsController::class, 'sendSms'])->name('api.v1.clients.communications.sms');
    Route::post('email', [ClientCommunicationsController::class, 'sendEmail'])->name('api.v1.clients.communications.email');
    Route::post('call', [ClientCommunicationsController::class, 'startCall'])->name('api.v1.clients.communications.call');
});
