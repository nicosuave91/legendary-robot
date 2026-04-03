<?php

declare(strict_types=1);

namespace App\Modules\Disposition\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use App\Modules\Clients\Models\Client;
use App\Modules\Disposition\Events\ClientDispositionTransitioned;
use App\Modules\Disposition\Models\ClientDispositionHistory;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Shared\Audit\AuditLogger;

final class DispositionTransitionService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly DispositionProjectionService $projectionService,
        private readonly DispositionTransitionValidator $validator,
    ) {
    }

    public function transition(User $actor, Client $client, array $payload, string $correlationId): array
    {
        $targetCode = (string) ($payload['targetDispositionCode'] ?? '');
        $acknowledgeWarnings = (bool) ($payload['acknowledgeWarnings'] ?? false);
        $reason = $payload['reason'] ?? null;

        $validated = $this->validator->validate($actor, $client, $targetCode);

        if ($validated['blockingIssues'] !== []) {
            return [
                'statusCode' => 422,
                'payload' => [
                    'result' => 'blocked',
                    'currentDisposition' => $validated['currentDisposition'],
                    'availableTransitions' => $validated['availableTransitions'],
                    'warnings' => $validated['warnings'],
                    'blockingIssues' => $validated['blockingIssues'],
                    'historyEntry' => null,
                ],
            ];
        }

        if ($validated['warnings'] !== [] && !$acknowledgeWarnings) {
            return [
                'statusCode' => 409,
                'payload' => [
                    'result' => 'warning_confirmation_required',
                    'currentDisposition' => $validated['currentDisposition'],
                    'availableTransitions' => $validated['availableTransitions'],
                    'warnings' => $validated['warnings'],
                    'blockingIssues' => [],
                    'historyEntry' => null,
                ],
            ];
        }

        $historyEntry = DB::transaction(function () use ($actor, $client, $targetCode, $reason, $validated): ClientDispositionHistory {
            $currentCode = (string) $validated['currentDisposition']['code'];

            $history = ClientDispositionHistory::query()->create([
                'id' => (string) Str::uuid(),
                'tenant_id' => (string) $client->tenant_id,
                'client_id' => (string) $client->id,
                'actor_user_id' => (string) $actor->id,
                'from_disposition_code' => $currentCode,
                'to_disposition_code' => $targetCode,
                'reason' => $reason,
                'warnings_snapshot' => $validated['warnings'] ?: null,
                'occurred_at' => now(),
            ]);

            $this->projectionService->syncLegacyClientStatus($client, $targetCode, $actor, $currentCode, $reason);

            return $history->load('actor');
        });

        $this->auditLogger->record([
            'tenant_id' => (string) $actor->tenant_id,
            'actor_id' => (string) $actor->id,
            'action' => 'client.disposition.transitioned',
            'subject_type' => 'client',
            'subject_id' => (string) $client->id,
            'correlation_id' => $correlationId,
            'before_summary' => json_encode(['disposition' => $validated['currentDisposition']['code']], JSON_THROW_ON_ERROR),
            'after_summary' => json_encode(['disposition' => $targetCode, 'warnings' => $validated['warnings']], JSON_THROW_ON_ERROR),
        ]);

        Event::dispatch(new ClientDispositionTransitioned(
            tenantId: (string) $actor->tenant_id,
            correlationId: $correlationId,
            clientId: (string) $client->id,
            payload: [
                'clientId' => (string) $client->id,
                'fromDispositionCode' => (string) $validated['currentDisposition']['code'],
                'toDispositionCode' => $targetCode,
                'currentStatus' => $targetCode,
            ],
        ));

        return [
            'statusCode' => 201,
            'payload' => [
                'result' => 'transitioned',
                'currentDisposition' => $this->projectionService->currentForClient($client->fresh()),
                'availableTransitions' => $this->projectionService->availableTransitionsForClient($client->fresh()),
                'warnings' => $validated['warnings'],
                'blockingIssues' => [],
                'historyEntry' => [
                    'id' => (string) $historyEntry->id,
                    'fromDispositionCode' => $historyEntry->from_disposition_code,
                    'toDispositionCode' => (string) $historyEntry->to_disposition_code,
                    'reason' => $historyEntry->reason,
                    'occurredAt' => $historyEntry->occurred_at?->toIso8601String(),
                    'actorDisplayName' => $historyEntry->actor?->name,
                ],
            ],
        ];
    }
}
