<?php

declare(strict_types=1);

namespace App\Modules\Applications\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use App\Modules\Applications\Events\ApplicationCreated;
use App\Modules\Applications\Models\Application;
use App\Modules\Applications\Models\ApplicationRuleApplication;
use App\Modules\Applications\Models\ApplicationStatusHistory;
use App\Modules\Clients\Models\Client;
use App\Modules\Disposition\Services\DispositionProjectionService;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Shared\Audit\AuditLogger;

final class ApplicationService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly ApplicationQueryService $applicationQueryService,
        private readonly ApplicationRuleEvaluator $applicationRuleEvaluator,
        private readonly DispositionProjectionService $dispositionProjectionService,
    ) {
    }

    public function create(User $actor, Client $client, array $payload, string $correlationId): array
    {
        $application = DB::transaction(function () use ($actor, $client, $payload, $correlationId): Application {
            $status = !empty($payload['submittedAt']) ? 'submitted' : 'draft';

            $application = Application::query()->create([
                'id' => (string) Str::uuid(),
                'tenant_id' => (string) $actor->tenant_id,
                'client_id' => (string) $client->id,
                'application_number' => $this->generateApplicationNumber((string) $actor->tenant_id),
                'owner_user_id' => $payload['ownerUserId'] ?? null,
                'product_type' => (string) $payload['productType'],
                'external_reference' => $payload['externalReference'] ?? null,
                'amount_requested' => $payload['amountRequested'] ?? null,
                'status' => $status,
                'submitted_at' => $payload['submittedAt'] ?? null,
                'metadata' => $payload['metadata'] ?? null,
                'created_by' => (string) $actor->id,
                'updated_by' => (string) $actor->id,
            ]);

            ApplicationStatusHistory::query()->create([
                'id' => (string) Str::uuid(),
                'tenant_id' => (string) $actor->tenant_id,
                'application_id' => (string) $application->id,
                'actor_user_id' => (string) $actor->id,
                'from_status' => null,
                'to_status' => $status,
                'reason' => 'Application created',
                'occurred_at' => now(),
                'metadata' => null,
            ]);

            foreach ($this->applicationRuleEvaluator->evaluate($application, 'application.created', $actor, ['correlationId' => $correlationId]) as $ruleResult) {
                ApplicationRuleApplication::query()->create([
                    'id' => (string) Str::uuid(),
                    'tenant_id' => (string) $actor->tenant_id,
                    'application_id' => (string) $application->id,
                    'rule_id' => $ruleResult['ruleId'] ?? null,
                    'rule_version_id' => $ruleResult['ruleVersionId'] ?? null,
                    'rule_key' => $ruleResult['ruleKey'],
                    'rule_version' => $ruleResult['ruleVersion'],
                    'rule_name_snapshot' => $ruleResult['ruleName'] ?? null,
                    'trigger_event' => $ruleResult['triggerEvent'],
                    'outcome' => $ruleResult['outcome'],
                    'title' => $ruleResult['title'],
                    'note_body' => $ruleResult['noteBody'],
                    'is_blocking' => $ruleResult['isBlocking'],
                    'evidence' => $ruleResult['evidence'],
                    'applied_at' => now(),
                ]);
            }

            $this->dispositionProjectionService->ensureInitialDispositionForClient($client, $actor, 'Disposition bootstrap during application creation.');

            return $application;
        });

        $this->auditLogger->record([
            'tenant_id' => (string) $actor->tenant_id,
            'actor_id' => (string) $actor->id,
            'action' => 'application.created',
            'subject_type' => 'application',
            'subject_id' => (string) $application->id,
            'correlation_id' => $correlationId,
            'before_summary' => null,
            'after_summary' => json_encode(['status' => $application->status, 'productType' => $application->product_type], JSON_THROW_ON_ERROR),
        ]);

        Event::dispatch(new ApplicationCreated(
            tenantId: (string) $actor->tenant_id,
            correlationId: $correlationId,
            applicationId: (string) $application->id,
            payload: [
                'applicationId' => (string) $application->id,
                'clientId' => (string) $client->id,
                'productType' => (string) $application->product_type,
                'amountRequested' => $application->amount_requested !== null ? (float) $application->amount_requested : null,
                'currentStatus' => (string) $application->status,
            ],
        ));

        return $this->applicationQueryService->detailForClient($actor, $client, $application->fresh());
    }

    private function generateApplicationNumber(string $tenantId): string
    {
        do {
            $candidate = 'APP-' . strtoupper(Str::random(8));
        } while (Application::query()->withoutGlobalScopes()->where('tenant_id', $tenantId)->where('application_number', $candidate)->exists());

        return $candidate;
    }
}
