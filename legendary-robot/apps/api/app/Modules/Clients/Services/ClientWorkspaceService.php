<?php

declare(strict_types=1);

namespace App\Modules\Clients\Services;

use Carbon\CarbonImmutable;
use App\Modules\Applications\Models\Application;
use App\Modules\Applications\Services\ApplicationQueryService;
use App\Modules\Audit\Models\AuditLog;
use App\Modules\CalendarTasks\Models\CalendarEvent;
use App\Modules\CalendarTasks\Services\EventQueryService;
use App\Modules\Clients\Models\Client;
use App\Modules\Clients\Models\ClientDocument;
use App\Modules\Clients\Models\ClientNote;
use App\Modules\Communications\Services\CommunicationTimelineService;
use App\Modules\Disposition\Services\DispositionProjectionService;
use App\Modules\IdentityAccess\Models\User;

final class ClientWorkspaceService
{
    public function __construct(
        private readonly ClientVisibilityService $clientVisibilityService,
        private readonly DispositionProjectionService $dispositionProjectionService,
        private readonly CommunicationTimelineService $communicationTimelineService,
        private readonly EventQueryService $eventQueryService,
        private readonly ApplicationQueryService $applicationQueryService,
    ) {
    }

    public function forActor(User $actor, Client $client): array
    {
        if (!$this->clientVisibilityService->canView($actor, $client)) {
            abort(404);
        }

        $client->loadMissing(['address', 'owner']);
        $client->loadCount(['notes', 'documents']);

        $currentDisposition = $this->dispositionProjectionService->currentForClient($client);
        $dispositionHistory = $this->dispositionProjectionService->historyForClient($client);
        $availableDispositionTransitions = $this->dispositionProjectionService->availableTransitionsForClient($client);

        $applicationsCount = Application::query()
            ->where('tenant_id', $actor->tenant_id)
            ->where('client_id', $client->id)
            ->count();

        $eventsCount = CalendarEvent::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $actor->tenant_id)
            ->where('client_id', $client->id)
            ->count();

        /** @var \Illuminate\Database\Eloquent\Collection<int, ClientNote> $recentNotesCollection */
        $recentNotesCollection = $client->notes()
            ->with('author')
            ->latest('created_at')
            ->limit(10)
            ->get();

        $recentNotes = $recentNotesCollection
            ->map(fn (ClientNote $note): array => [
                'id' => (string) $note->id,
                'sourceType' => (string) $note->source_type,
                'body' => (string) $note->body,
                'isEditable' => (bool) $note->is_editable,
                'authorDisplayName' => $note->author !== null ? $note->author->name : 'Unknown',
                'createdAt' => $note->created_at?->toIso8601String(),
            ])
            ->values()
            ->all();

        /** @var \Illuminate\Database\Eloquent\Collection<int, ClientDocument> $recentDocumentsCollection */
        $recentDocumentsCollection = $client->documents()
            ->with('uploadedBy')
            ->latest('created_at')
            ->limit(10)
            ->get();

        $recentDocuments = $recentDocumentsCollection
            ->map(fn (ClientDocument $document): array => [
                'id' => (string) $document->id,
                'originalFilename' => (string) $document->original_filename,
                'mimeType' => (string) $document->mime_type,
                'sizeBytes' => (int) $document->size_bytes,
                'provenance' => (string) $document->provenance,
                'attachmentCategory' => $document->attachment_category,
                'uploadedByDisplayName' => $document->uploadedBy !== null ? $document->uploadedBy->name : 'Unknown',
                'uploadedAt' => $document->created_at?->toIso8601String(),
                'storageReference' => (string) $document->storage_reference,
            ])
            ->values()
            ->all();

        $noteIds = $client->notes()->pluck('id')->all();
        $documentIds = $client->documents()->pluck('id')->all();
        $applicationIds = Application::query()
            ->where('tenant_id', $actor->tenant_id)
            ->where('client_id', $client->id)
            ->pluck('id')
            ->all();

        /** @var \Illuminate\Database\Eloquent\Collection<int, AuditLog> $auditEntries */
        $auditEntries = AuditLog::query()
            ->where(function ($query) use ($client, $noteIds, $documentIds, $applicationIds): void {
                $query->where(fn ($inner) => $inner->where('subject_type', 'client')->where('subject_id', (string) $client->id));

                if (!empty($noteIds)) {
                    $query->orWhere(fn ($inner) => $inner->where('subject_type', 'client_note')->whereIn('subject_id', $noteIds));
                }

                if (!empty($documentIds)) {
                    $query->orWhere(fn ($inner) => $inner->where('subject_type', 'client_document')->whereIn('subject_id', $documentIds));
                }

                if (!empty($applicationIds)) {
                    $query->orWhere(fn ($inner) => $inner->where('subject_type', 'application')->whereIn('subject_id', $applicationIds));
                }
            })
            ->latest('created_at')
            ->limit(10)
            ->get();

        $actorNames = User::query()
            ->whereIn('id', $auditEntries->pluck('actor_id')->filter()->unique()->values()->all())
            ->pluck('name', 'id');

        $recentAudit = $auditEntries
            ->map(fn (AuditLog $entry): array => [
                'id' => (string) $entry->id,
                'action' => (string) $entry->action,
                'actorDisplayName' => (string) ($actorNames[(string) $entry->actor_id] ?? 'System'),
                'subjectType' => (string) $entry->subject_type,
                'createdAt' => $entry->created_at?->toIso8601String(),
            ])
            ->values()
            ->all();

        $overview = $this->overviewForActor(
            actor: $actor,
            client: $client,
            recentNotes: $recentNotes,
        );

        return [
            'client' => [
                'id' => (string) $client->id,
                'displayName' => (string) $client->display_name,
                'firstName' => $client->first_name,
                'lastName' => $client->last_name,
                'companyName' => $client->company_name,
                'status' => (string) $client->status,
                'primaryEmail' => $client->primary_email,
                'primaryPhone' => $client->primary_phone,
                'preferredContactChannel' => $client->preferred_contact_channel,
                'dateOfBirth' => $client->date_of_birth?->toDateString(),
                'ownerUserId' => $client->owner_user_id,
                'ownerDisplayName' => $client->owner?->name,
                'address' => $client->address ? [
                    'addressLine1' => $client->address->address_line_1,
                    'addressLine2' => $client->address->address_line_2,
                    'city' => $client->address->city,
                    'stateCode' => $client->address->state_code,
                    'postalCode' => $client->address->postal_code,
                ] : null,
                'createdAt' => $client->created_at?->toIso8601String(),
                'updatedAt' => $client->updated_at?->toIso8601String(),
            ],
            'overview' => $overview,
            'currentDisposition' => $currentDisposition,
            'availableDispositionTransitions' => $availableDispositionTransitions,
            'dispositionHistory' => $dispositionHistory,
            'summary' => [
                'notesCount' => (int) $client->notes_count,
                'documentsCount' => (int) $client->documents_count,
                'eventsCount' => $eventsCount,
                'applicationsCount' => $applicationsCount,
                'lastActivityAt' => $client->last_activity_at?->toIso8601String(),
            ],
            'recentNotes' => $recentNotes,
            'recentDocuments' => $recentDocuments,
            'recentAudit' => $recentAudit,
            'tabs' => [
                ['key' => 'overview', 'label' => 'Overview', 'href' => '/app/clients/' . $client->id . '/overview', 'available' => true],
                ['key' => 'communications', 'label' => 'Communications', 'href' => '/app/clients/' . $client->id . '/communications', 'available' => true],
                ['key' => 'events', 'label' => 'Events', 'href' => '/app/clients/' . $client->id . '/events', 'available' => true],
                ['key' => 'applications', 'label' => 'Applications', 'href' => '/app/clients/' . $client->id . '/applications', 'available' => true],
                ['key' => 'notes', 'label' => 'Notes', 'href' => '/app/clients/' . $client->id . '/notes', 'available' => true],
                ['key' => 'documents', 'label' => 'Documents', 'href' => '/app/clients/' . $client->id . '/documents', 'available' => true],
                ['key' => 'audit', 'label' => 'Audit', 'href' => '/app/clients/' . $client->id . '/audit', 'available' => true],
            ],
        ];
    }

    /**
     * @param list<array<string, mixed>> $recentNotes
     * @return array<string, mixed>
     */
    private function overviewForActor(User $actor, Client $client, array $recentNotes): array
    {
        $communications = $this->communicationTimelineService->collectItemsForClient($client, [
            'channel' => 'all',
            'status' => 'all',
            'limit' => 10,
        ]);

        $latestCommunication = $communications[0] ?? null;

        $eventList = $this->eventQueryService->listForClient($actor, $client, [
            'startDate' => CarbonImmutable::now()->subDays(1)->toDateString(),
            'endDate' => CarbonImmutable::now()->addDays(90)->toDateString(),
        ]);

        $events = collect($eventList['items'] ?? []);
        $nextEvent = $events
            ->filter(fn (array $event): bool => isset($event['startsAt']) && is_string($event['startsAt']))
            ->sortBy('startsAt')
            ->first();

        $applicationList = $this->applicationQueryService->listForClient($actor, $client);
        $applications = collect($applicationList['items'] ?? []);
        $leadApplication = $applications
            ->sortByDesc(fn (array $application): array => [
                (int) ($application['ruleSummary']['blockingCount'] ?? 0),
                (int) ($application['ruleSummary']['warningCount'] ?? 0),
                (string) ($application['updatedAt'] ?? ''),
            ])
            ->first();

        $recentNote = $recentNotes[0] ?? null;

        $recommendedAction = $this->recommendedActionForOverview(
            client: $client,
            latestCommunication: is_array($latestCommunication) ? $latestCommunication : null,
            nextEvent: is_array($nextEvent) ? $nextEvent : null,
            leadApplication: is_array($leadApplication) ? $leadApplication : null,
        );

        return [
            'recommendedAction' => $recommendedAction,
            'latestCommunication' => is_array($latestCommunication) ? [
                'id' => (string) $latestCommunication['id'],
                'channel' => (string) $latestCommunication['channel'],
                'direction' => (string) $latestCommunication['direction'],
                'occurredAt' => $latestCommunication['occurredAt'] ?? null,
                'preview' => $latestCommunication['content']['preview']
                    ?? $latestCommunication['content']['subject']
                    ?? $latestCommunication['content']['bodyText']
                    ?? null,
                'status' => [
                    'label' => (string) ($latestCommunication['status']['displayLabel'] ?? 'Unknown'),
                    'tone' => (string) ($latestCommunication['status']['tone'] ?? 'neutral'),
                ],
            ] : null,
            'nextEvent' => is_array($nextEvent) ? [
                'id' => (string) $nextEvent['id'],
                'title' => (string) $nextEvent['title'],
                'eventType' => (string) $nextEvent['eventType'],
                'startsAt' => $nextEvent['startsAt'] ?? null,
                'endsAt' => $nextEvent['endsAt'] ?? null,
                'taskSummary' => $nextEvent['taskSummary'] ?? [
                    'total' => 0,
                    'open' => 0,
                    'completed' => 0,
                    'blocked' => 0,
                    'skipped' => 0,
                ],
            ] : null,
            'leadApplication' => is_array($leadApplication) ? [
                'id' => (string) $leadApplication['id'],
                'applicationNumber' => (string) $leadApplication['applicationNumber'],
                'productType' => (string) $leadApplication['productType'],
                'currentStatus' => $leadApplication['currentStatus'] ?? [
                    'code' => 'unknown',
                    'label' => 'Unknown',
                    'tone' => 'neutral',
                    'changedAt' => null,
                ],
                'ruleSummary' => $leadApplication['ruleSummary'] ?? [
                    'infoCount' => 0,
                    'warningCount' => 0,
                    'blockingCount' => 0,
                    'lastAppliedAt' => null,
                ],
            ] : null,
            'recentNote' => is_array($recentNote) ? [
                'id' => (string) $recentNote['id'],
                'body' => (string) $recentNote['body'],
                'authorDisplayName' => (string) $recentNote['authorDisplayName'],
                'createdAt' => $recentNote['createdAt'] ?? null,
            ] : null,
        ];
    }

    /**
     * @param array<string, mixed>|null $latestCommunication
     * @param array<string, mixed>|null $nextEvent
     * @param array<string, mixed>|null $leadApplication
     * @return array<string, string>
     */
    private function recommendedActionForOverview(
        Client $client,
        ?array $latestCommunication,
        ?array $nextEvent,
        ?array $leadApplication,
    ): array {
        $clientBaseHref = '/app/clients/' . $client->id;

        $blockingCount = (int) ($leadApplication['ruleSummary']['blockingCount'] ?? 0);
        if ($leadApplication !== null && $blockingCount > 0) {
            return [
                'code' => 'review_blocking_application',
                'title' => 'Review blocked application',
                'description' => 'This client has application rule evidence that needs review before work can proceed.',
                'ctaLabel' => 'Open application',
                'ctaHref' => $clientBaseHref . '/applications',
                'tone' => 'danger',
            ];
        }

        $latestCommunicationTone = (string) ($latestCommunication['status']['tone'] ?? 'neutral');
        if ($latestCommunication !== null && $latestCommunicationTone === 'danger') {
            return [
                'code' => 'retry_failed_communication',
                'title' => 'Retry failed outreach',
                'description' => 'The latest communication attempt did not complete successfully.',
                'ctaLabel' => 'Open communications',
                'ctaHref' => $clientBaseHref . '/communications',
                'tone' => 'warning',
            ];
        }

        $openTaskCount = (int) ($nextEvent['taskSummary']['open'] ?? 0);
        $blockedTaskCount = (int) ($nextEvent['taskSummary']['blocked'] ?? 0);
        if ($nextEvent !== null && ($openTaskCount > 0 || $blockedTaskCount > 0)) {
            return [
                'code' => 'prepare_upcoming_event',
                'title' => 'Prepare upcoming event',
                'description' => 'There is scheduled client work with open or blocked tasks that needs attention.',
                'ctaLabel' => 'Open event',
                'ctaHref' => $clientBaseHref . '/events',
                'tone' => $blockedTaskCount > 0 ? 'warning' : 'info',
            ];
        }

        $lastActivityAt = $client->last_activity_at?->toDateString();
        $staleBoundary = CarbonImmutable::now()->subDays(7)->toDateString();
        if ($lastActivityAt === null || $lastActivityAt < $staleBoundary) {
            return [
                'code' => 'follow_up_client',
                'title' => 'Follow up with this client',
                'description' => 'There has not been a recent recorded touchpoint for this client.',
                'ctaLabel' => 'Open communications',
                'ctaHref' => $clientBaseHref . '/communications',
                'tone' => 'info',
            ];
        }

        return [
            'code' => 'no_urgent_action',
            'title' => 'No urgent action',
            'description' => 'This client does not currently have a blocked application, failed outreach, or upcoming task needing attention.',
            'ctaLabel' => 'Review overview',
            'ctaHref' => $clientBaseHref . '/overview',
            'tone' => 'neutral',
        ];
    }
}
