<?php

declare(strict_types=1);

namespace App\Modules\Clients\Services;

use App\Modules\Applications\Models\Application;
use App\Modules\Audit\Models\AuditLog;
use App\Modules\CalendarTasks\Models\CalendarEvent;
use App\Modules\Clients\Models\Client;
use App\Modules\Clients\Models\ClientDocument;
use App\Modules\Clients\Models\ClientNote;
use App\Modules\Disposition\Services\DispositionProjectionService;
use App\Modules\IdentityAccess\Models\User;

final class ClientWorkspaceService
{
    public function __construct(
        private readonly ClientVisibilityService $clientVisibilityService,
        private readonly DispositionProjectionService $dispositionProjectionService,
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
}
