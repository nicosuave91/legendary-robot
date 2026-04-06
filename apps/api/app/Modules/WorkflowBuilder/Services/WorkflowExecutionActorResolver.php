<?php

declare(strict_types=1);

namespace App\Modules\WorkflowBuilder\Services;

use App\Modules\Applications\Models\Application;
use App\Modules\Clients\Models\Client;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\WorkflowBuilder\Models\WorkflowRun;

final class WorkflowExecutionActorResolver
{
    public function resolve(WorkflowRun $run, ?Client $client = null, ?Application $application = null): User
    {
        $candidateIds = array_values(array_filter([
            $run->runtime_context['actorUserId'] ?? null,
            $run->trigger_payload_snapshot['actorUserId'] ?? null,
            $application?->owner_user_id,
            $application?->getAttribute('created_by'),
            $client?->owner_user_id,
            $client?->created_by,
        ], static fn ($value): bool => is_string($value) && trim($value) !== ''));

        if ($candidateIds !== []) {
            /** @var User|null $resolved */
            $resolved = User::query()
                ->where('tenant_id', (string) $run->tenant_id)
                ->where('status', 'active')
                ->whereIn('id', $candidateIds)
                ->orderByRaw('FIELD(id, ' . implode(',', array_fill(0, count($candidateIds), '?')) . ')', $candidateIds)
                ->first();

            if ($resolved !== null) {
                return $resolved;
            }
        }

        /** @var User|null $owner */
        $owner = User::query()
            ->where('tenant_id', (string) $run->tenant_id)
            ->where('status', 'active')
            ->whereHas('roles', fn ($builder) => $builder->where('name', 'owner'))
            ->oldest('created_at')
            ->first();

        if ($owner !== null) {
            return $owner;
        }

        /** @var User|null $admin */
        $admin = User::query()
            ->where('tenant_id', (string) $run->tenant_id)
            ->where('status', 'active')
            ->whereHas('roles', fn ($builder) => $builder->where('name', 'admin'))
            ->oldest('created_at')
            ->first();

        if ($admin !== null) {
            return $admin;
        }

        return User::query()
            ->where('tenant_id', (string) $run->tenant_id)
            ->where('status', 'active')
            ->oldest('created_at')
            ->firstOrFail();
    }
}
