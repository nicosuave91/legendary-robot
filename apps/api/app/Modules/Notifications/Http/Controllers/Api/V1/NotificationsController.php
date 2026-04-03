<?php

declare(strict_types=1);

namespace App\Modules\Notifications\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Modules\Notifications\Http\Requests\ListNotificationsRequest;
use App\Modules\Notifications\Services\NotificationFeedService;
use App\Modules\Shared\Support\ApiResponse;

final class NotificationsController extends Controller
{
    public function __construct(
        private readonly NotificationFeedService $feedService,
    ) {
    }

    public function index(ListNotificationsRequest $request): JsonResponse
    {
        Gate::authorize('notifications.read');

        return ApiResponse::success(
            $this->feedService->listForUser($request->user(), $request->validated()),
            (string) $request->attributes->get('correlation_id', ''),
        );
    }
}
