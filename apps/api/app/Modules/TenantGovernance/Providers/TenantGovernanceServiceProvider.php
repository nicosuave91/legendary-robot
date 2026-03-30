<?php

declare(strict_types=1);

namespace App\Modules\TenantGovernance\Providers;

use Illuminate\Support\ServiceProvider;

final class TenantGovernanceServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
    }
}
