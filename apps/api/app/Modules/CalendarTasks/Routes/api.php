<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Modules\CalendarTasks\Http\Controllers\Api\V1\CalendarDayController;
use App\Modules\CalendarTasks\Http\Controllers\Api\V1\ClientEventsController;
use App\Modules\CalendarTasks\Http\Controllers\Api\V1\EventController;
use App\Modules\CalendarTasks\Http\Controllers\Api\V1\TaskStatusController;

Route::middleware(['api', 'auth:sanctum'])->group(function (): void {
    Route::get('api/v1/calendar/day', [CalendarDayController::class, 'show'])->name('api.v1.calendar.day');

    Route::prefix('api/v1/events')->group(function (): void {
        Route::get('', [EventController::class, 'index'])->name('api.v1.events.index');
        Route::post('', [EventController::class, 'store'])->name('api.v1.events.store');
        Route::get('{eventId}', [EventController::class, 'show'])->name('api.v1.events.show');
        Route::patch('{eventId}', [EventController::class, 'update'])->name('api.v1.events.update');
    });

    Route::patch('api/v1/tasks/{taskId}/status', [TaskStatusController::class, 'update'])->name('api.v1.tasks.status.update');
    Route::get('api/v1/clients/{clientId}/events', [ClientEventsController::class, 'index'])->name('api.v1.clients.events.index');
});
