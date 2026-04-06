<?php

declare(strict_types=1);

namespace App\Modules\Onboarding\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

final class OnboardingServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        Gate::define('onboarding.state.read', fn ($user): bool => $user !== null);
        Gate::define('onboarding.profile.confirm', fn ($user): bool => $user !== null);
        Gate::define('onboarding.industry.select', fn ($user): bool => $user !== null);
        Gate::define('onboarding.complete', fn ($user): bool => $user !== null);
    }
}
