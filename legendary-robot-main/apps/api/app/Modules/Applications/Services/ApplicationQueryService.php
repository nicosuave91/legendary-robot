<?php

declare(strict_types=1);

namespace App\Modules\Applications\Services;

use App\Modules\Applications\Models\Application;
use App\Modules\Applications\Models\ApplicationRuleApplication;
use App\Modules\Applications\Models\ApplicationStatusHistory;
use App\Modules\Clients\Models\Client;
use App\Modules\IdentityAccess\Models\User;

final class ApplicationQueryService
{
    public function __construct(
        private readonly ApplicationStatusTransitionService $applicationStatusTransitionService,
    ) {
    }

    public function listForClient(User $actor, Client $client): array
    {
        $applications = Application::query()
            ->where('tenant_id', $actor->tenant_id)
            ->where('client_id', $client->id)
            ->with(['owner', 'ruleApplications'])
            ->latest('created_at')
            ->get();

        return [
            'items' => $applications->map(fn (Application $application): array => $this->serializeSummary($application))->values()->all(),
            'meta' => [
                'total' => $applications->count(),
            ],
        ];
    }

    public function detailForClient(User $actor, Client $client, Application $application): array
    {
        abort_unless((string) $application->tenant_id === (string) $actor->tenant_id && (string) $application->client_id === (string) $client->id, 404);

        $application->loadMissing(['owner', 'statusHistory.actor', 'ruleApplications']);

        /** @var \Illuminate\Database\Eloquent\Collection<int, ApplicationStatusHistory> $statusHistory */
        $statusHistory = $application->statusHistory()
            ->with('actor')
            ->latest('occurred_at')
            ->get();

        /** @var \Illuminate\Database\Eloquent\Collection<int, ApplicationRuleApplication> $ruleNotes */
        $ruleNotes = $application->ruleApplications()
            ->latest('applied_at')
            ->get();

        return [
            'application' => $this->serializeSummary($application) + [
                'externalReference' => $application->external_reference,
                'amountRequested' => $application->amount_requested !== null ? (string) $application->amount_requested : null,
                'submittedAt' => $application->submitted_at?->toIso8601String(),
                'availableStatusTransitions' => $this->applicationStatusTransitionService->availableTransitions((string) $application->status),
            ],
            'statusHistory' => $statusHistory
                ->map(fn (ApplicationStatusHistory $entry): array => [
                    'id' => (string) $entry->id,
                    'fromStatus' => $entry->from_status,
                    'toStatus' => (string) $entry->to_status,
                    'reason' => $entry->reason,
                    'occurredAt' => $entry->occurred_at?->toIso8601String(),
                    'actorDisplayName' => $entry->actor?->name,
                ])
                ->values()
                ->all(),
            'ruleNotes' => $ruleNotes
                ->map(fn (ApplicationRuleApplication $rule): array => [
                    'id' => (string) $rule->id,
                    'ruleId' => $rule->rule_id,
                    'ruleVersionId' => $rule->rule_version_id,
                    'ruleKey' => (string) $rule->rule_key,
                    'ruleVersion' => (string) $rule->rule_version,
                    'ruleName' => $rule->rule_name_snapshot,
                    'outcome' => (string) $rule->outcome,
                    'title' => (string) $rule->title,
                    'body' => (string) $rule->note_body,
                    'appliedAt' => $rule->applied_at?->toIso8601String(),
                    'isViewOnly' => true,
                ])
                ->values()
                ->all(),
        ];
    }

    public function serializeSummary(Application $application): array
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, ApplicationRuleApplication> $ruleApplications */
        $ruleApplications = $application->relationLoaded('ruleApplications') ? $application->ruleApplications : $application->ruleApplications()->get();

        /** @var ApplicationStatusHistory|null $latestStatusHistory */
        $latestStatusHistory = $application->relationLoaded('statusHistory')
            ? $application->statusHistory->sortByDesc('occurred_at')->first()
            : $application->statusHistory()->latest('occurred_at')->first();

        return [
            'id' => (string) $application->id,
            'applicationNumber' => (string) $application->application_number,
            'productType' => (string) $application->product_type,
            'ownerDisplayName' => $application->owner?->name,
            'currentStatus' => [
                'code' => (string) $application->status,
                'label' => str($application->status)->headline()->toString(),
                'tone' => $this->applicationStatusTransitionService->toneForStatus((string) $application->status),
                'changedAt' => $latestStatusHistory?->occurred_at?->toIso8601String() ?? $application->updated_at?->toIso8601String(),
            ],
            'ruleSummary' => [
                'infoCount' => $ruleApplications->where('outcome', 'info')->count(),
                'warningCount' => $ruleApplications->where('outcome', 'warning')->count(),
                'blockingCount' => $ruleApplications->where('outcome', 'blocking')->count(),
                'lastAppliedAt' => $ruleApplications->sortByDesc('applied_at')->first()?->applied_at?->toIso8601String(),
            ],
            'createdAt' => $application->created_at?->toIso8601String(),
            'updatedAt' => $application->updated_at?->toIso8601String(),
        ];
    }
}
