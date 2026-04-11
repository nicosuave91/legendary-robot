<?php

declare(strict_types=1);

namespace App\Modules\Notifications\Services;

use Illuminate\Support\Str;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Notifications\Models\Notification;
use App\Modules\Notifications\Models\NotificationRead;
use App\Modules\Notifications\Models\ToastDismissal;
use App\Modules\Shared\Audit\AuditLogger;

final class NotificationLifecycleService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {
    }

    public function markRead(User $actor, Notification $notification, string $correlationId): array
    {
        $read = NotificationRead::query()->firstOrCreate(
            [
                'tenant_id' => (string) $actor->tenant_id,
                'notification_id' => (string) $notification->id,
                'user_id' => (string) $actor->id,
            ],
            [
                'id' => (string) Str::uuid(),
                'read_at' => now(),
            ],
        );

        $this->auditLogger->record([
            'tenant_id' => (string) $actor->tenant_id,
            'actor_id' => (string) $actor->id,
            'action' => 'notifications.read',
            'subject_type' => 'notification',
            'subject_id' => (string) $notification->id,
            'correlation_id' => $correlationId,
            'before_summary' => null,
            'after_summary' => [
                'readAt' => $read->read_at?->toIso8601String(),
            ],
        ]);

        return [
            'notificationId' => (string) $notification->id,
            'read' => true,
            'readAt' => $read->read_at?->toIso8601String(),
        ];
    }

    public function dismiss(User $actor, Notification $notification, string $surface, string $correlationId): array
    {
        $dismissal = ToastDismissal::query()->firstOrCreate(
            [
                'tenant_id' => (string) $actor->tenant_id,
                'notification_id' => (string) $notification->id,
                'user_id' => (string) $actor->id,
                'surface' => $surface,
            ],
            [
                'id' => (string) Str::uuid(),
                'dismissed_at' => now(),
            ],
        );

        $this->auditLogger->record([
            'tenant_id' => (string) $actor->tenant_id,
            'actor_id' => (string) $actor->id,
            'action' => 'notifications.dismissed',
            'subject_type' => 'notification',
            'subject_id' => (string) $notification->id,
            'correlation_id' => $correlationId,
            'before_summary' => null,
            'after_summary' => [
                'surface' => $surface,
                'dismissedAt' => $dismissal->dismissed_at?->toIso8601String(),
            ],
        ]);

        return [
            'notificationId' => (string) $notification->id,
            'dismissed' => true,
            'dismissedAt' => $dismissal->dismissed_at?->toIso8601String(),
            'surface' => $surface,
        ];
    }
}
