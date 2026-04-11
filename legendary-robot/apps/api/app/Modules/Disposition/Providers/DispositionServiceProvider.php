<?php

declare(strict_types=1);

namespace App\Modules\Disposition\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Modules\Clients\Models\Client;
use App\Modules\Clients\Services\ClientVisibilityService;
use App\Modules\IdentityAccess\Models\User;

final class DispositionServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        Gate::define('clients.disposition.read', function (?User $user, ?Client $client = null): bool {
            if ($user === null || $client === null || !$user->hasPermission('clients.disposition.read') || $user->tenant_id !== $client->tenant_id) {
                return false;
            }

            return app(ClientVisibilityService::class)->canView($user, $client);
        });

        Gate::define('clients.disposition.transition', function (?User $user, ?Client $client = null): bool {
            if ($user === null || $client === null || !$user->hasPermission('clients.disposition.transition') || $user->tenant_id !== $client->tenant_id) {
                return false;
            }

            return app(ClientVisibilityService::class)->canView($user, $client);
        });
    }
}
