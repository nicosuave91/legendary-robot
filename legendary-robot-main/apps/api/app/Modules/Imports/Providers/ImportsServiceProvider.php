<?php

declare(strict_types=1);

namespace App\Modules\Imports\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Imports\Models\Import;

final class ImportsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        Gate::define('imports.read', fn (?User $user): bool => $user?->hasPermission('imports.read') ?? false);
        Gate::define('imports.create', fn (?User $user): bool => $user?->hasPermission('imports.create') ?? false);
        Gate::define('imports.validate', function (?User $user, ?Import $import = null): bool {
            return $user !== null
                && $import !== null
                && $user->hasPermission('imports.validate')
                && (string) $user->tenant_id === (string) $import->tenant_id;
        });
        Gate::define('imports.commit', function (?User $user, ?Import $import = null): bool {
            return $user !== null
                && $import !== null
                && $user->hasPermission('imports.commit')
                && (string) $user->tenant_id === (string) $import->tenant_id;
        });
    }
}
