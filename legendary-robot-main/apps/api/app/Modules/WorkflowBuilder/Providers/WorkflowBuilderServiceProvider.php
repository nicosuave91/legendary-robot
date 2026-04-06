<?php

declare(strict_types=1);

namespace App\Modules\WorkflowBuilder\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Modules\Applications\Events\ApplicationCreated;
use App\Modules\Applications\Events\ApplicationStatusTransitioned;
use App\Modules\Disposition\Events\ClientDispositionTransitioned;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\WorkflowBuilder\Listeners\QueueMatchingWorkflowRuns;
use App\Modules\WorkflowBuilder\Models\Workflow;

final class WorkflowBuilderServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        Gate::define('workflows.read', fn (?User $user): bool => $user?->hasPermission('workflows.read') ?? false);
        Gate::define('workflows.create', fn (?User $user): bool => $user?->hasPermission('workflows.create') ?? false);
        Gate::define('workflows.runs.read', fn (?User $user): bool => $user?->hasPermission('workflows.runs.read') ?? false);
        Gate::define('workflows.update-draft', function (?User $user, ?Workflow $workflow = null): bool {
            return $user !== null
                && $workflow !== null
                && $user->hasPermission('workflows.update-draft')
                && (string) $user->tenant_id === (string) $workflow->tenant_id;
        });
        Gate::define('workflows.publish', function (?User $user, ?Workflow $workflow = null): bool {
            return $user !== null
                && $workflow !== null
                && $user->hasPermission('workflows.publish')
                && (string) $user->tenant_id === (string) $workflow->tenant_id;
        });

        Event::listen(ApplicationCreated::class, QueueMatchingWorkflowRuns::class);
        Event::listen(ApplicationStatusTransitioned::class, QueueMatchingWorkflowRuns::class);
        Event::listen(ClientDispositionTransitioned::class, QueueMatchingWorkflowRuns::class);
    }
}