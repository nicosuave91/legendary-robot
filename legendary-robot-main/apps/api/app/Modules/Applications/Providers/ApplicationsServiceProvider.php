<?php

declare(strict_types=1);

namespace App\Modules\Applications\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Modules\Applications\Models\Application;
use App\Modules\Clients\Models\Client;
use App\Modules\Clients\Services\ClientVisibilityService;
use App\Modules\IdentityAccess\Models\User;

final class ApplicationsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            \App\Modules\Applications\Services\ApplicationRuleEvaluator::class,
            \App\Modules\Applications\Services\DefaultApplicationRuleEvaluator::class,
        );
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        Gate::define('clients.applications.read', function (?User $user, ?Client $client = null): bool {
            if ($user === null || $client === null || !$user->hasPermission('clients.applications.read') || $user->tenant_id !== $client->tenant_id) {
                return false;
            }

            return app(ClientVisibilityService::class)->canView($user, $client);
        });

        Gate::define('clients.applications.create', function (?User $user, ?Client $client = null): bool {
            if ($user === null || $client === null || !$user->hasPermission('clients.applications.create') || $user->tenant_id !== $client->tenant_id) {
                return false;
            }

            return app(ClientVisibilityService::class)->canView($user, $client);
        });

        Gate::define('clients.applications.status.transition', function (?User $user, ?Application $application = null): bool {
            return $user !== null
                && $application !== null
                && $user->hasPermission('clients.applications.status.transition')
                && $user->tenant_id === $application->tenant_id;
        });
    }
}
