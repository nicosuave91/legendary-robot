<?php

declare(strict_types=1);

namespace App\Modules\IdentityAccess\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Modules\IdentityAccess\Models\User;

final class IdentityAccessServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        Gate::define('identity-access.auth.read-self', fn (?User $user): bool => $user !== null);
        Gate::define('identity-access.auth.sign-out', fn (?User $user): bool => $user !== null);
        Gate::define('settings.profile.read', fn (?User $user): bool => $user !== null);
        Gate::define('settings.profile.update', fn (?User $user): bool => $user !== null);

        Gate::define('settings.accounts.read', fn (?User $user): bool => $user?->hasPermission('settings.accounts.read') ?? false);
        Gate::define('settings.accounts.create', fn (?User $user): bool => $user?->hasPermission('settings.accounts.create') ?? false);
        Gate::define('settings.accounts.update', function (?User $user, ?User $subject = null): bool {
            if ($user === null || $subject === null) {
                return false;
            }

            return $user->hasPermission('settings.accounts.update')
                && $user->tenant_id === $subject->tenant_id
                && !$subject->hasRole('owner');
        });
        Gate::define('settings.accounts.decommission', function (?User $user, ?User $subject = null): bool {
            if ($user === null || $subject === null) {
                return false;
            }

            return $user->hasPermission('settings.accounts.decommission')
                && $user->tenant_id === $subject->tenant_id
                && $user->id !== $subject->id
                && !$subject->hasRole('owner');
        });
    }
}
