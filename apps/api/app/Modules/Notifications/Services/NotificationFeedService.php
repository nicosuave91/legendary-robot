\
<?php

declare(strict_types=1);

namespace App\Modules\Notifications\Services;

use App\Modules\IdentityAccess\Models\User;
use App\Modules\Notifications\Models\Notification;
use App\Modules\Notifications\Models\NotificationRead;
use App\Modules\Notifications\Models\ToastDismissal;

final class NotificationFeedService
{
    public function listForUser(User $actor, array $filters = []): array
    {
        $query = Notification::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $actor->tenant_id)
            ->where(function ($builder) use ($actor): void {
                $builder->whereNull('target_user_id')->orWhere('target_user_id', $actor->id);
            })
            ->with([
                'reads' => fn ($relation) => $relation->where('user_id', $actor->id),
                'dismissals' => fn ($relation) => $relation->where('user_id', $actor->id),
            ])
            ->latest('emitted_at');

        $includeDismissed = filter_var($filters['includeDismissed'] ?? false, FILTER_VALIDATE_BOOL);
        $items = $query->limit(50)->get();

        $serialized = $items
            ->map(fn (Notification $notification): array => $this->serializeForUser($notification, $actor))
            ->filter(fn (array $notification): bool => $includeDismissed || !$notification['isDismissed'])
            ->values();

        return [
            'items' => $serialized->all(),
            'meta' => [
                'total' => $serialized->count(),
                'unread' => $serialized->where('isRead', false)->count(),
            ],
        ];
    }

    public function serializeForUser(Notification $notification, User $actor): array
    {
        /** @var NotificationRead|null $read */
        $read = $notification->reads->first();

        /** @var \Illuminate\Database\Eloquent\Collection<int, ToastDismissal> $dismissals */
        $dismissals = $notification->dismissals;

        /** @var ToastDismissal|null $dismissal */
        $dismissal = $dismissals->sortByDesc('dismissed_at')->first();

        return [
            'id' => (string) $notification->id,
            'category' => (string) $notification->category,
            'notificationType' => (string) $notification->notification_type,
            'title' => (string) $notification->title,
            'body' => $notification->body,
            'tone' => (string) $notification->tone,
            'actionUrl' => $notification->action_url,
            'sourceEventType' => (string) $notification->source_event_type,
            'sourceEventId' => $notification->source_event_id,
            'isRead' => $read !== null,
            'readAt' => $read?->read_at?->toIso8601String(),
            'isDismissed' => $dismissal !== null,
            'dismissedAt' => $dismissal?->dismissed_at?->toIso8601String(),
            'emittedAt' => $notification->emitted_at?->toIso8601String(),
            'payloadSnapshot' => $notification->payload_snapshot ?? [],
        ];
    }
}
