<?php

declare(strict_types=1);

namespace App\Modules\RulesLibrary\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\RulesLibrary\Models\Rule;

final class RulesLibraryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            \App\Modules\Applications\Services\ApplicationRuleEvaluator::class,
            \App\Modules\RulesLibrary\Services\GovernedApplicationRuleEvaluator::class,
        );
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        Gate::define('rules.read', fn (?User $user): bool => $user?->hasPermission('rules.read') ?? false);
        Gate::define('rules.create', fn (?User $user): bool => $user?->hasPermission('rules.create') ?? false);
        Gate::define('rules.execution-logs.read', fn (?User $user): bool => $user?->hasPermission('rules.execution-logs.read') ?? false);
        Gate::define('rules.update-draft', function (?User $user, ?Rule $rule = null): bool {
            return $user !== null
                && $rule !== null
                && $user->hasPermission('rules.update-draft')
                && (string) $user->tenant_id === (string) $rule->tenant_id;
        });
        Gate::define('rules.publish', function (?User $user, ?Rule $rule = null): bool {
            return $user !== null
                && $rule !== null
                && $user->hasPermission('rules.publish')
                && (string) $user->tenant_id === (string) $rule->tenant_id;
        });
    }
}