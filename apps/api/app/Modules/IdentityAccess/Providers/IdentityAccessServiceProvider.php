<?php

declare(strict_types=1);

namespace App\Modules\IdentityAccess\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

final class IdentityAccessServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::define('identity-access.auth.read-self', fn ($user): bool => $user !== null);
        Gate::define('identity-access.auth.sign-out', fn ($user): bool => $user !== null);
        Gate::define('settings.profile.read', fn ($user): bool => $user !== null);
        Gate::define('settings.profile.update', fn ($user): bool => $user !== null);
    }
}
