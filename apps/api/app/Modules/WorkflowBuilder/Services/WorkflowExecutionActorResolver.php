<?php

declare(strict_types=1);

namespace App\Modules\WorkflowBuilder\Services;

use RuntimeException;
use App\Modules\Applications\Models\Application;
use App\Modules\Clients\Models\Client;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\WorkflowBuilder\Models\WorkflowRun;

final class WorkflowExecutionActorResolver
{
    public function resolveForRun(WorkflowRun $run): User
    {
        $context = (array) ($run->runtime_context ?? []);

        $candidateIds = array_values(array_filter([
            $this->stringOrNull($context['actorUserId'] ?? null),
            $this->stringOrNull($context['ownerUserId'] ?? null),
            $this->candidateFromSubject($run),
            $this->candidateFromClientId($run, $this->stringOrNull($context['clientId'] ?? null)),
        ]));

        foreach ($candidateIds as $candidateId) {
            $user = User::query()
                ->withoutGlobalScopes()
                ->where('tenant_id', (string) $run->tenant_id)
                ->where('id', $candidateId)
                ->where('status', 'active')
                ->first();

            if ($user !== null) {
                return $user;
            }
        }

        $fallback = User::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', (string) $run->tenant_id)
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->orderByRaw("case when id = 'owner-user' then 0 when id = 'admin-user' then 1 else 2 end")
            ->orderBy('created_at')
            ->first();

        if ($fallback !== null) {
            return $fallback;
        }

        throw new RuntimeException('Unable to resolve a workflow execution actor for the current tenant.');
    }

    private function candidateFromSubject(WorkflowRun $run): ?string
    {
        if ((string) $run->subject_type === 'client') {
            return $this->candidateFromClientId($run, (string) $run->subject_id);
        }

        if ((string) $run->subject_type !== 'application') {
            return null;
        }

        /** @var Application|null $application */
        $application = Application::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', (string) $run->tenant_id)
            ->where('id', (string) $run->subject_id)
            ->first();

        if ($application === null) {
            return null;
        }

        return $this->stringOrNull($application->owner_user_id)
            ?? $this->candidateFromClientId($run, (string) $application->client_id);
    }

    private function candidateFromClientId(WorkflowRun $run, ?string $clientId): ?string
    {
        if ($clientId === null || $clientId === '') {
            return null;
        }

        /** @var Client|null $client */
        $client = Client::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', (string) $run->tenant_id)
            ->where('id', $clientId)
            ->first();

        if ($client === null) {
            return null;
        }

        return $this->stringOrNull($client->owner_user_id)
            ?? $this->stringOrNull($client->created_by);
    }

    private function stringOrNull(mixed $value): ?string
    {
        $candidate = trim((string) $value);

        return $candidate !== '' ? $candidate : null;
    }
}
