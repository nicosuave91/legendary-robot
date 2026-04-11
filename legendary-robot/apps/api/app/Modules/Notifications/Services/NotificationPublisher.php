<?php

declare(strict_types=1);

namespace App\Modules\Notifications\Services;

use Illuminate\Support\Str;
use App\Modules\Notifications\Models\Notification;
use App\Modules\Shared\Audit\AuditLogger;

final class NotificationPublisher
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {
    }

    /**
     * @param array<string, mixed> $payloadSnapshot
     */
    public function publishForTenant(
        string $tenantId,
        string $notificationType,
        string $category,
        string $title,
        ?string $body,
        string $tone,
        string $sourceEventType,
        ?string $sourceEventId,
        array $payloadSnapshot,
        string $correlationId,
        ?string $actionUrl = null,
        ?string $targetUserId = null,
    ): Notification {
        $notification = Notification::query()->create([
            'id' => (string) Str::uuid(),
            'tenant_id' => $tenantId,
            'target_user_id' => $targetUserId,
            'audience_scope' => $targetUserId === null ? 'tenant' : 'user',
            'notification_type' => $notificationType,
            'category' => $category,
            'title' => $title,
            'body' => $body,
            'tone' => $tone,
            'action_url' => $actionUrl,
            'source_event_type' => $sourceEventType,
            'source_event_id' => $sourceEventId,
            'payload_snapshot' => $payloadSnapshot,
            'emitted_at' => now(),
        ]);

        $this->auditLogger->record([
            'tenant_id' => $tenantId,
            'actor_id' => null,
            'action' => 'notifications.emitted',
            'subject_type' => 'notification',
            'subject_id' => (string) $notification->id,
            'correlation_id' => $correlationId,
            'before_summary' => null,
            'after_summary' => [
                'category' => $category,
                'notificationType' => $notificationType,
                'tone' => $tone,
                'sourceEventType' => $sourceEventType,
                'sourceEventId' => $sourceEventId,
            ],
        ]);

        return $notification;
    }
}
