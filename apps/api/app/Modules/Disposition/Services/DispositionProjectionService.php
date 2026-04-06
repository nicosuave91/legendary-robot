<?php

declare(strict_types=1);

namespace App\Modules\Disposition\Services;

use Illuminate\Support\Str;
use App\Modules\Clients\Models\Client;
use App\Modules\Clients\Models\ClientStatusHistory;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Disposition\Models\ClientDispositionHistory;

final class DispositionProjectionService
{
    public function __construct(
        private readonly DispositionDefinitionCatalog $catalog,
    ) {
    }

    public function ensureInitialDispositionForClient(Client $client, ?User $actor = null, ?string $reason = null): array
    {
        $existing = ClientDispositionHistory::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $client->tenant_id)
            ->where('client_id', $client->id)
            ->latest('occurred_at')
            ->first();

        if ($existing !== null) {
            return $this->currentForClient($client);
        }

        $candidate = in_array((string) $client->status, ['lead', 'qualified', 'applied', 'active', 'inactive'], true)
            ? (string) $client->status
            : 'lead';

        $definition = $this->catalog->findForTenant((string) $client->tenant_id, $candidate);
        $candidate = $definition !== null ? (string) $definition->code : 'lead';

        $history = ClientDispositionHistory::query()->create([
            'id' => (string) Str::uuid(),
            'tenant_id' => (string) $client->tenant_id,
            'client_id' => (string) $client->id,
            'actor_user_id' => $actor?->id,
            'from_disposition_code' => null,
            'to_disposition_code' => $candidate,
            'reason' => $reason ?? 'Initial lifecycle projection bootstrap.',
            'warnings_snapshot' => null,
            'occurred_at' => $client->created_at ?? now(),
        ]);

        $this->syncLegacyClientStatus($client, $candidate, $actor, null, $history->reason);

        return $this->currentForClient($client);
    }

    public function currentForClient(Client $client): array
    {
        $history = ClientDispositionHistory::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $client->tenant_id)
            ->where('client_id', $client->id)
            ->latest('occurred_at')
            ->latest('created_at')
            ->first();

        if ($history === null) {
            return $this->ensureInitialDispositionForClient($client);
        }

        $definition = $this->catalog->findForTenant((string) $client->tenant_id, (string) $history->to_disposition_code);

        return [
            'code' => (string) $history->to_disposition_code,
            'label' => $definition !== null ? (string) $definition->label : Str::headline((string) $history->to_disposition_code),
            'tone' => $this->toneForCode((string) $history->to_disposition_code),
            'isTerminal' => $definition !== null ? (bool) $definition->is_terminal : ((string) $history->to_disposition_code === 'inactive'),
            'changedAt' => $history->occurred_at?->toIso8601String(),
            'changedByDisplayName' => $history->actor?->name,
        ];
    }

    public function availableTransitionsForClient(Client $client): array
    {
        $current = $this->currentForClient($client);
        $definition = $this->catalog->findForTenant((string) $client->tenant_id, (string) $current['code']);

        $allowed = $definition !== null ? $definition->allowed_next_codes : [];

        return collect($allowed)->map(function (string $code) use ($client): array {
            $definition = $this->catalog->findForTenant((string) $client->tenant_id, $code);

            return [
                'code' => $code,
                'label' => $definition !== null ? (string) $definition->label : Str::headline($code),
                'tone' => $this->toneForCode($code),
            ];
        })->values()->all();
    }

    public function historyForClient(Client $client, int $limit = 10): array
    {
        return ClientDispositionHistory::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $client->tenant_id)
            ->where('client_id', $client->id)
            ->with('actor')
            ->latest('occurred_at')
            ->limit($limit)
            ->get()
            ->map(function (ClientDispositionHistory $entry): array {
                return [
                    'id' => (string) $entry->id,
                    'fromDispositionCode' => $entry->from_disposition_code,
                    'toDispositionCode' => (string) $entry->to_disposition_code,
                    'reason' => $entry->reason,
                    'occurredAt' => $entry->occurred_at?->toIso8601String(),
                    'actorDisplayName' => $entry->actor?->name,
                ];
            })
            ->values()
            ->all();
    }

    public function syncLegacyClientStatus(Client $client, string $toCode, ?User $actor = null, ?string $fromCode = null, ?string $reason = null): void
    {
        $client->forceFill([
            'status' => $toCode,
            'last_activity_at' => now(),
        ])->save();

        ClientStatusHistory::query()->create([
            'id' => (string) Str::uuid(),
            'tenant_id' => (string) $client->tenant_id,
            'client_id' => (string) $client->id,
            'actor_user_id' => $actor?->id,
            'from_status' => $fromCode,
            'to_status' => $toCode,
            'reason' => $reason,
            'occurred_at' => now(),
        ]);
    }

    private function toneForCode(string $code): string
    {
        return match ($code) {
            'active' => 'success',
            'inactive' => 'warning',
            'lead' => 'info',
            default => 'neutral',
        };
    }
}