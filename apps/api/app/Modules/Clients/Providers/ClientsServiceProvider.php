<?php

declare(strict_types=1);

namespace App\Modules\Clients\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Modules\Clients\Models\Client;
use App\Modules\Clients\Services\ClientVisibilityService;
use App\Modules\IdentityAccess\Models\User;

final class ClientsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        Gate::define('clients.read', fn (?User $user): bool => $user?->hasPermission('clients.read') ?? false);
        Gate::define('clients.create', fn (?User $user): bool => $user?->hasPermission('clients.create') ?? false);
        Gate::define('clients.update', function (?User $user, ?Client $client = null): bool {
            if ($user === null || $client === null || !$user->hasPermission('clients.update') || $user->tenant_id !== $client->tenant_id) {
                return false;
            }

            return app(ClientVisibilityService::class)->canView($user, $client);
        });
        Gate::define('clients.view', function (?User $user, ?Client $client = null): bool {
            if ($user === null || $client === null || !$user->hasPermission('clients.read') || $user->tenant_id !== $client->tenant_id) {
                return false;
            }

            return app(ClientVisibilityService::class)->canView($user, $client);
        });
        Gate::define('clients.notes.create', function (?User $user, ?Client $client = null): bool {
            if ($user === null || $client === null || !$user->hasPermission('clients.notes.create') || $user->tenant_id !== $client->tenant_id) {
                return false;
            }

            return app(ClientVisibilityService::class)->canView($user, $client);
        });
        Gate::define('clients.documents.create', function (?User $user, ?Client $client = null): bool {
            if ($user === null || $client === null || !$user->hasPermission('clients.documents.create') || $user->tenant_id !== $client->tenant_id) {
                return false;
            }

            return app(ClientVisibilityService::class)->canView($user, $client);
        });
    }
}
