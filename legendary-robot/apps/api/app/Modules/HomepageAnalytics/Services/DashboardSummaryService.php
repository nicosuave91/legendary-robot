<?php

declare(strict_types=1);

namespace App\Modules\HomepageAnalytics\Services;

use App\Modules\Clients\Models\ClientDocument;
use App\Modules\Clients\Models\ClientNote;
use App\Modules\Clients\Services\ClientVisibilityService;
use App\Modules\IdentityAccess\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class DashboardSummaryService
{
    public function __construct(
        private readonly ClientVisibilityService $clientVisibilityService,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function forActor(User $actor): array
    {
        $visibleClientQuery = $this->clientVisibilityService->queryForActor($actor);
        $visibleClientIds = $visibleClientQuery
            ->pluck('clients.id')
            ->all();

        $totalClients = count($visibleClientIds);
        $industry = $actor->industryAssignment?->industry;
        $industryVersion = $actor->industryAssignment?->config_version;

        $currentWindowStart = now()->subDays(7);
        $priorWindowStart = now()->subDays(14);
        $priorWindowEnd = now()->subDays(7);

        $newClientsCurrent = $this->countClientsWithinWindow(
            $this->clientVisibilityService->queryForActor($actor),
            $currentWindowStart,
            null,
        );

        $newClientsPrior = $this->countClientsWithinWindow(
            $this->clientVisibilityService->queryForActor($actor),
            $priorWindowStart,
            $priorWindowEnd,
        );

        $recentNotes = $this->countRecordsForWindow(
            ClientNote::query(),
            $visibleClientIds,
            $currentWindowStart,
            null,
        );

        $priorNotes = $this->countRecordsForWindow(
            ClientNote::query(),
            $visibleClientIds,
            $priorWindowStart,
            $priorWindowEnd,
        );

        $recentDocuments = $this->countRecordsForWindow(
            ClientDocument::query(),
            $visibleClientIds,
            $currentWindowStart,
            null,
        );

        $priorDocuments = $this->countRecordsForWindow(
            ClientDocument::query(),
            $visibleClientIds,
            $priorWindowStart,
            $priorWindowEnd,
        );

        return [
            'hero' => [
                'greeting' => 'Welcome back',
                'userDisplayName' => (string) $actor->name,
                'tenantName' => (string) ($actor->tenant?->name ?? 'Workspace'),
                'selectedIndustry' => $industry,
                'selectedIndustryConfigVersion' => $industryVersion,
                'subtitle' => 'Your operational cockpit stays tenant-scoped, role-safe, and driven by canonical CRM activity.',
            ],
            'kpis' => [
                $this->kpiCard(
                    key: 'clients_total',
                    label: 'Visible clients',
                    value: $totalClients,
                    description: 'All client records currently visible to this actor.',
                    href: '/app/clients',
                    delta: [
                        'direction' => 'flat',
                        'value' => $totalClients,
                        'label' => 'Current scope',
                    ],
                ),
                $this->kpiCard(
                    key: 'clients_new_7d',
                    label: 'New clients',
                    value: $newClientsCurrent,
                    description: 'Recently created client records in your current scope.',
                    href: '/app/clients?sort=created_at&direction=desc',
                    delta: $this->periodDelta($newClientsCurrent, $newClientsPrior, '7d'),
                ),
                $this->kpiCard(
                    key: 'notes_7d',
                    label: 'Notes',
                    value: $recentNotes,
                    description: 'User-authored note activity across visible client records.',
                    href: '/app/clients?sort=last_activity_at&direction=desc',
                    delta: $this->periodDelta($recentNotes, $priorNotes, '7d'),
                ),
                $this->kpiCard(
                    key: 'documents_7d',
                    label: 'Documents',
                    value: $recentDocuments,
                    description: 'Governed document uploads attached to visible client records.',
                    href: '/app/clients?sort=last_activity_at&direction=desc',
                    delta: $this->periodDelta($recentDocuments, $priorDocuments, '7d'),
                ),
            ],
            'activitySummary' => [
                'visibleClientCount' => $totalClients,
                'recentNoteCount' => $recentNotes,
                'recentDocumentCount' => $recentDocuments,
            ],
            'calendarPanelEnabled' => true,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function kpiCard(
        string $key,
        string $label,
        int $value,
        string $description,
        string $href,
        array $delta,
    ): array {
        return [
            'key' => $key,
            'label' => $label,
            'value' => $value,
            'description' => $description,
            'href' => $href,
            'delta' => $delta,
        ];
    }

    private function countClientsWithinWindow(Builder $query, \DateTimeInterface $start, ?\DateTimeInterface $end): int
    {
        $query->where('clients.created_at', '>=', $start);

        if ($end !== null) {
            $query->where('clients.created_at', '<', $end);
        }

        return $query->count();
    }

    /**
     * @param Builder<\Illuminate\Database\Eloquent\Model> $query
     * @param array<int, mixed> $visibleClientIds
     */
    private function countRecordsForWindow(Builder $query, array $visibleClientIds, \DateTimeInterface $start, ?\DateTimeInterface $end): int
    {
        if (empty($visibleClientIds)) {
            return 0;
        }

        $query
            ->whereIn('client_id', $visibleClientIds)
            ->where('created_at', '>=', $start);

        if ($end !== null) {
            $query->where('created_at', '<', $end);
        }

        return $query->count();
    }

    /**
     * @return array{direction: string, value: int, label: string}
     */
    private function periodDelta(int $current, int $previous, string $windowLabel): array
    {
        $difference = $current - $previous;
        $direction = $difference > 0 ? 'up' : ($difference < 0 ? 'down' : 'flat');
        $absoluteDifference = abs($difference);

        if ($direction === 'flat') {
            return [
                'direction' => $direction,
                'value' => 0,
                'label' => sprintf('No change vs prior %s', $windowLabel),
            ];
        }

        return [
            'direction' => $direction,
            'value' => $absoluteDifference,
            'label' => sprintf('%s %d vs prior %s', $direction === 'up' ? '↑' : '↓', $absoluteDifference, $windowLabel),
        ];
    }
}
