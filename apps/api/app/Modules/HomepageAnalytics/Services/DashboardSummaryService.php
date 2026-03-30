<?php

declare(strict_types=1);

namespace App\Modules\HomepageAnalytics\Services;

use App\Modules\Clients\Models\ClientDocument;
use App\Modules\Clients\Models\ClientNote;
use App\Modules\Clients\Services\ClientVisibilityService;
use App\Modules\IdentityAccess\Models\User;

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
        $visibleClientIds = $this->clientVisibilityService
            ->queryForActor($actor)
            ->pluck('clients.id')
            ->all();

        $totalClients = count($visibleClientIds);
        $newClientsLastSevenDays = $this->clientVisibilityService
            ->queryForActor($actor)
            ->where('clients.created_at', '>=', now()->subDays(7))
            ->count();

        $recentNotes = empty($visibleClientIds)
            ? 0
            : ClientNote::query()
                ->whereIn('client_id', $visibleClientIds)
                ->where('created_at', '>=', now()->subDays(7))
                ->count();

        $recentDocuments = empty($visibleClientIds)
            ? 0
            : ClientDocument::query()
                ->whereIn('client_id', $visibleClientIds)
                ->where('created_at', '>=', now()->subDays(7))
                ->count();

        $industry = $actor->industryAssignment?->industry;
        $industryVersion = $actor->industryAssignment?->config_version;

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
                    deltaValue: 0,
                    deltaLabel: 'No comparison window',
                ),
                $this->kpiCard(
                    key: 'clients_new_7d',
                    label: 'New clients · 7 days',
                    value: $newClientsLastSevenDays,
                    description: 'Recently created client records in your current scope.',
                    href: '/app/clients?sort=created_at&direction=desc',
                    deltaValue: $newClientsLastSevenDays,
                    deltaLabel: 'Created in the last 7 days',
                ),
                $this->kpiCard(
                    key: 'notes_7d',
                    label: 'Notes · 7 days',
                    value: $recentNotes,
                    description: 'User-authored note activity across visible client records.',
                    href: '/app/clients?sort=last_activity_at&direction=desc',
                    deltaValue: $recentNotes,
                    deltaLabel: 'Note entries in the last 7 days',
                ),
                $this->kpiCard(
                    key: 'documents_7d',
                    label: 'Documents · 7 days',
                    value: $recentDocuments,
                    description: 'Governed document uploads attached to visible client records.',
                    href: '/app/clients?sort=last_activity_at&direction=desc',
                    deltaValue: $recentDocuments,
                    deltaLabel: 'Uploads in the last 7 days',
                ),
            ],
            'activitySummary' => [
                'visibleClientCount' => $totalClients,
                'recentNoteCount' => $recentNotes,
                'recentDocumentCount' => $recentDocuments,
            ],
            'calendarPanelEnabled' => false,
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
        int $deltaValue,
        string $deltaLabel,
    ): array {
        return [
            'key' => $key,
            'label' => $label,
            'value' => $value,
            'description' => $description,
            'href' => $href,
            'delta' => [
                'direction' => $deltaValue > 0 ? 'up' : 'flat',
                'value' => $deltaValue,
                'label' => $deltaLabel,
            ],
        ];
    }
}
