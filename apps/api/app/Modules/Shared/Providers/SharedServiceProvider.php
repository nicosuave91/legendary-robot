<?php

declare(strict_types=1);

namespace App\Modules\Shared\Providers;

use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;
use App\Modules\Shared\Audit\AuditLogger;
use App\Modules\Shared\Contracts\QueuesTenantAware;
use App\Modules\Shared\Tenancy\TenantContext;

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

        Queue::before(function (JobProcessing $event): void {
            $command = $event->job->payload()['data']['command'] ?? null;

            if ($command instanceof QueuesTenantAware) {
                app(TenantContext::class)->set($command->tenantId());
            }
        });

        Queue::after(function (JobProcessed $event): void {
            app(TenantContext::class)->set(null);
        });
    }
}
