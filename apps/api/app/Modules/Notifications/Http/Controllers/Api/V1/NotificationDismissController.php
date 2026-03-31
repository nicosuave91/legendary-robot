<?php

declare(strict_types=1);

namespace App\Modules\Notifications\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Modules\Notifications\Http\Requests\DismissNotificationRequest;
use App\Modules\Notifications\Models\Notification;
use App\Modules\Notifications\Services\NotificationLifecycleService;
use App\Modules\Shared\Support\ApiResponse;

final class NotificationDismissController extends Controller
{
    public function __construct(
        private readonly NotificationLifecycleService $lifecycleService,
    ) {
    }

    public function store(DismissNotificationRequest $request, string $notificationId): JsonResponse
    {
        $notification = Notification::query()->withoutGlobalScopes()->where('tenant_id', $request->user()->tenant_id)->where('id', $notificationId)->firstOrFail();
        Gate::authorize('notifications.dismiss', $notification);

        return ApiResponse::success(
            $this->lifecycleService->dismiss(
                $request->user(),
                $notification,
                (string) ($request->validated('surface') ?? 'header_center'),
                (string) $request->attributes->get('correlation_id', ''),
            ),
            (string) $request->attributes->get('correlation_id', ''),
        );
    }
}
