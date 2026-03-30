<?php

declare(strict_types=1);

namespace App\Modules\Shared\Providers;

use Illuminate\Support\ServiceProvider;
use App\Modules\Shared\Tenancy\TenantContext;
use App\Modules\Shared\Audit\AuditLogger;

final class SharedServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(TenantContext::class, fn () => new TenantContext());
        $this->app->singleton(AuditLogger::class, fn () => new AuditLogger());
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }
}
