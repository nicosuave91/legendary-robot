<?php

declare(strict_types=1);

namespace App\Modules\TenantGovernance\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Modules\IdentityAccess\Models\User;

final class TenantGovernanceServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        Gate::define('settings.theme.read', fn (?User $user): bool => $user?->hasPermission('settings.theme.read') ?? false);
        Gate::define('settings.theme.update', fn (?User $user): bool => $user?->hasPermission('settings.theme.update') ?? false);
        Gate::define('settings.industry-configurations.read', fn (?User $user): bool => $user?->hasPermission('settings.industry-configurations.read') ?? false);
        Gate::define('settings.industry-configurations.create', fn (?User $user): bool => $user?->hasPermission('settings.industry-configurations.create') ?? false);
    }
}
