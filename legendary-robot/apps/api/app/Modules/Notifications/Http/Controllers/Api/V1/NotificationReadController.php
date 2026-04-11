<?php

declare(strict_types=1);

namespace App\Modules\Notifications\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Modules\Notifications\Http\Requests\ReadNotificationRequest;
use App\Modules\Notifications\Models\Notification;
use App\Modules\Notifications\Services\NotificationLifecycleService;
use App\Modules\Shared\Support\ApiResponse;

final class NotificationReadController extends Controller
{
    public function __construct(
        private readonly NotificationLifecycleService $lifecycleService,
    ) {
    }

    public function store(ReadNotificationRequest $request, string $notificationId): JsonResponse
    {
        $notification = Notification::query()->withoutGlobalScopes()->where('tenant_id', $request->user()->tenant_id)->where('id', $notificationId)->firstOrFail();
        Gate::authorize('notifications.read-mark', $notification);

        return ApiResponse::success(
            $this->lifecycleService->markRead(
                $request->user(),
                $notification,
                (string) $request->attributes->get('correlation_id', ''),
            ),
            (string) $request->attributes->get('correlation_id', ''),
        );
    }
}
