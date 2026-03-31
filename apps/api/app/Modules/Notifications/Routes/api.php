<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Modules\Notifications\Http\Controllers\Api\V1\NotificationDismissController;
use App\Modules\Notifications\Http\Controllers\Api\V1\NotificationReadController;
use App\Modules\Notifications\Http\Controllers\Api\V1\NotificationsController;

Route::prefix('api/v1/notifications')
    ->middleware(['api', 'auth:sanctum'])
    ->group(function (): void {
        Route::get('', [NotificationsController::class, 'index'])->name('api.v1.notifications.index');
        Route::post('{notificationId}/dismiss', [NotificationDismissController::class, 'store'])->name('api.v1.notifications.dismiss');
        Route::post('{notificationId}/read', [NotificationReadController::class, 'store'])->name('api.v1.notifications.read');
    });
