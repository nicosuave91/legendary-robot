<?php

declare(strict_types=1);

namespace App\Modules\HomepageAnalytics\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Modules\IdentityAccess\Models\User;

final class HomepageAnalyticsServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::define('dashboard.summary.read', fn (?User $user): bool => $user?->hasPermission('dashboard.summary.read') ?? false);
        Gate::define('dashboard.production.read', fn (?User $user): bool => $user?->hasPermission('dashboard.production.read') ?? false);
    }
}
