<?php

declare(strict_types=1);

namespace App\Modules\Notifications\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Notifications\Models\Notification;

final class NotificationsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        Gate::define('notifications.read', fn (?User $user): bool => $user?->hasPermission('notifications.read') ?? false);
        Gate::define('notifications.dismiss', function (?User $user, ?Notification $notification = null): bool {
            return $user !== null
                && $notification !== null
                && $user->hasPermission('notifications.dismiss')
                && (string) $user->tenant_id === (string) $notification->tenant_id
                && ($notification->target_user_id === null || (string) $notification->target_user_id === (string) $user->id);
        });
        Gate::define('notifications.read-mark', function (?User $user, ?Notification $notification = null): bool {
            return $user !== null
                && $notification !== null
                && $user->hasPermission('notifications.read')
                && (string) $user->tenant_id === (string) $notification->tenant_id
                && ($notification->target_user_id === null || (string) $notification->target_user_id === (string) $user->id);
        });
    }
}
