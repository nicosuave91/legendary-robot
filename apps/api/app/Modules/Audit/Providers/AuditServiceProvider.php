<?php

declare(strict_types=1);

namespace App\Modules\Audit\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Modules\IdentityAccess\Models\User;

final class AuditServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        Gate::define('audit.read', fn (?User $user): bool => $user?->hasPermission('audit.read') ?? false);
    }
}
