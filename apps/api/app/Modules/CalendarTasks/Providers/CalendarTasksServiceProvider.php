<?php

declare(strict_types=1);

namespace App\Modules\CalendarTasks\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Modules\CalendarTasks\Models\CalendarEvent;
use App\Modules\CalendarTasks\Models\EventTask;
use App\Modules\Clients\Models\Client;
use App\Modules\Clients\Services\ClientVisibilityService;
use App\Modules\IdentityAccess\Models\User;

final class CalendarTasksServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        Gate::define('calendar.read', fn (?User $user): bool => $user?->hasPermission('calendar.read') ?? false);
        Gate::define('calendar.create', fn (?User $user): bool => $user?->hasPermission('calendar.create') ?? false);
        Gate::define('calendar.update', function (?User $user, ?CalendarEvent $event = null): bool {
            return $user !== null
                && $event !== null
                && $user->hasPermission('calendar.update')
                && $user->tenant_id === $event->tenant_id;
        });
        Gate::define('calendar.tasks.update', function (?User $user, ?EventTask $task = null): bool {
            return $user !== null
                && $task !== null
                && $user->hasPermission('calendar.tasks.update')
                && $user->tenant_id === $task->tenant_id;
        });
        Gate::define('clients.events.read', function (?User $user, ?Client $client = null): bool {
            if ($user === null || $client === null || !$user->hasPermission('clients.events.read') || $user->tenant_id !== $client->tenant_id) {
                return false;
            }

            return app(ClientVisibilityService::class)->canView($user, $client);
        });
    }
}
