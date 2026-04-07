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

        $summary = [
            'notesCount' => (int) $client->notes_count,
            'documentsCount' => (int) $client->documents_count,
            'eventsCount' => $eventsCount,
            'applicationsCount' => $applicationsCount,
            'lastActivityAt' => $client->last_activity_at?->toIso8601String(),
        ];

        $overview = $this->buildOverview(
            actor: $actor,
            client: $client,
            summary: $summary,
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
            'currentDisposition' => $currentDisposition,
            'availableDispositionTransitions' => $availableDispositionTransitions,
            'dispositionHistory' => $dispositionHistory,
            'summary' => $summary,
            'overview' => $overview,
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
     * @param array{notesCount:int,documentsCount:int,eventsCount:int,applicationsCount:int,lastActivityAt:?string} $summary
     * @param list<array{id:string,sourceType:string,body:string,isEditable:bool,authorDisplayName:string,createdAt:?string}> $recentNotes
     * @return array{
     *   recommendedAction: array{code:string,title:string,description:string,ctaLabel:?string,ctaHref:?string,tone:string},
     *   latestCommunication:?array<string,mixed>,
     *   nextEvent:?array<string,mixed>,
     *   leadApplication:?array<string,mixed>,
     *   recentNote:?array<string,mixed>
     * }
     */
    private function buildOverview(User $actor, Client $client, array $summary, array $recentNotes): array
    {
        $latestCommunication = $this->communicationTimelineService->collectItemsForClient($client, ['limit' => 1])[0] ?? null;

        $nextEventModel = CalendarEvent::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $actor->tenant_id)
            ->where('client_id', $client->id)
            ->where('status', 'scheduled')
            ->where('starts_at', '>=', now())
            ->with(['client', 'owner', 'tasks'])
            ->orderBy('starts_at')
            ->first();

        $nextEvent = $nextEventModel !== null ? $this->eventQueryService->summary($nextEventModel) : null;

        /** @var \Illuminate\Database\Eloquent\Collection<int, Application> $applications */
        $applications = Application::query()
            ->where('tenant_id', $actor->tenant_id)
            ->where('client_id', $client->id)
            ->with(['owner', 'ruleApplications', 'statusHistory'])
            ->get();

        $leadApplicationModel = $applications
            ->sort(function (Application $left, Application $right): int {
                $leftBlocking = $left->ruleApplications->where('outcome', 'blocking')->count();
                $rightBlocking = $right->ruleApplications->where('outcome', 'blocking')->count();

                if ($leftBlocking !== $rightBlocking) {
                    return $rightBlocking <=> $leftBlocking;
                }

                $leftWarning = $left->ruleApplications->where('outcome', 'warning')->count();
                $rightWarning = $right->ruleApplications->where('outcome', 'warning')->count();

                if ($leftWarning !== $rightWarning) {
                    return $rightWarning <=> $leftWarning;
                }

                return strcmp(
                    optional($right->updated_at)->toIso8601String() ?? '',
                    optional($left->updated_at)->toIso8601String() ?? '',
                );
            })
            ->first();

        $leadApplication = $leadApplicationModel !== null ? $this->applicationQueryService->serializeSummary($leadApplicationModel) : null;
        $recentNote = $recentNotes[0] ?? null;

        return [
            'recommendedAction' => $this->recommendedAction($client, $summary, $latestCommunication, $nextEvent, $leadApplication),
            'latestCommunication' => $latestCommunication,
            'nextEvent' => $nextEvent,
            'leadApplication' => $leadApplication,
            'recentNote' => $recentNote,
        ];
    }

    /**
     * @param array{notesCount:int,documentsCount:int,eventsCount:int,applicationsCount:int,lastActivityAt:?string} $summary
     * @param array<string,mixed>|null $latestCommunication
     * @param array<string,mixed>|null $nextEvent
     * @param array<string,mixed>|null $leadApplication
     * @return array{code:string,title:string,description:string,ctaLabel:?string,ctaHref:?string,tone:string}
     */
    private function recommendedAction(Client $client, array $summary, ?array $latestCommunication, ?array $nextEvent, ?array $leadApplication): array
    {
        $clientBaseHref = '/app/clients/' . $client->id;

        if ((int) ($leadApplication['ruleSummary']['blockingCount'] ?? 0) > 0) {
            return [
                'code' => 'review_blocking_application',
                'title' => 'Review blocked application',
                'description' => 'This client has application rule evidence that needs review before work can continue.',
                'ctaLabel' => 'Open application',
                'ctaHref' => $clientBaseHref . '/applications',
                'tone' => 'danger',
            ];
        }

        if (($latestCommunication['status']['tone'] ?? null) === 'danger') {
            return [
                'code' => 'retry_failed_communication',
                'title' => 'Review failed communication',
                'description' => 'The latest outreach ended in a failed delivery or call outcome and may need follow-up.',
                'ctaLabel' => 'Open communications',
                'ctaHref' => $clientBaseHref . '/communications',
                'tone' => 'warning',
            ];
        }

        if ($nextEvent !== null && ((int) ($nextEvent['taskSummary']['open'] ?? 0) > 0 || (int) ($nextEvent['taskSummary']['blocked'] ?? 0) > 0)) {
            return [
                'code' => 'prepare_upcoming_event',
                'title' => 'Prepare for the next event',
                'description' => 'This client has an upcoming scheduled event with open or blocked tasks.',
                'ctaLabel' => 'Open event',
                'ctaHref' => $clientBaseHref . '/events',
                'tone' => 'info',
            ];
        }

        $lastActivityAt = $summary['lastActivityAt'] !== null ? CarbonImmutable::parse($summary['lastActivityAt']) : null;
        if ($lastActivityAt === null || $lastActivityAt->lessThan(now()->subDays(7))) {
            return [
                'code' => 'follow_up_client',
                'title' => 'Follow up with this client',
                'description' => 'There has been no recent recorded activity, so an outreach or check-in may be appropriate.',
                'ctaLabel' => 'Open communications',
                'ctaHref' => $clientBaseHref . '/communications',
                'tone' => 'neutral',
            ];
        }

        return [
            'code' => 'no_urgent_action',
            'title' => 'No urgent action',
            'description' => 'This client does not currently show a blocking application, failed outreach, or upcoming task pressure.',
            'ctaLabel' => null,
            'ctaHref' => null,
            'tone' => 'success',
        ];
    }
}
