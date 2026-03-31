<?php

declare(strict_types=1);

namespace App\Modules\Clients\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Modules\Clients\Models\Client;
use App\Modules\Clients\Models\ClientNote;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Shared\Audit\AuditLogger;

final class ClientNoteService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {
    }

    public function create(User $actor, Client $client, array $payload, string $correlationId): array
    {
        $note = DB::transaction(function () use ($actor, $client, $payload): ClientNote {
            $note = ClientNote::query()->create([
                'id' => (string) Str::uuid(),
                'tenant_id' => (string) $client->tenant_id,
                'client_id' => (string) $client->id,
                'author_user_id' => (string) $actor->id,
                'source_type' => 'user',
                'body' => (string) $payload['body'],
                'is_editable' => true,
            ]);

            $client->forceFill(['last_activity_at' => now()])->save();

            return $note->load('author');
        });

        $this->auditLogger->record([
            'tenant_id' => (string) $actor->tenant_id,
            'actor_id' => (string) $actor->id,
            'action' => 'clients.notes.create',
            'subject_type' => 'client_note',
            'subject_id' => (string) $note->id,
            'correlation_id' => $correlationId,
            'before_summary' => null,
            'after_summary' => json_encode([
                'clientId' => (string) $client->id,
                'preview' => mb_substr((string) $note->body, 0, 140),
            ], JSON_THROW_ON_ERROR),
        ]);

        return [
            'id' => (string) $note->id,
            'sourceType' => (string) $note->source_type,
            'body' => (string) $note->body,
            'isEditable' => (bool) $note->is_editable,
            'authorDisplayName' => (string) ($note->author?->name ?? $actor->name),
            'createdAt' => $note->created_at?->toIso8601String(),
        ];
    }
}
