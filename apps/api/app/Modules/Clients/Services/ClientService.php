<?php

declare(strict_types=1);

namespace App\Modules\Clients\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Modules\Clients\Models\Client;
use App\Modules\Clients\Models\ClientAddress;
use App\Modules\Clients\Models\ClientStatusHistory;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Shared\Audit\AuditLogger;

final class ClientService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {
    }

    public function create(User $actor, array $payload, string $correlationId): array
    {
        $client = DB::transaction(function () use ($actor, $payload): Client {
            $ownerUserId = $this->resolveOwnerUserId($actor, $payload['ownerUserId'] ?? null);
            $client = Client::query()->create([
                'id' => (string) Str::uuid(),
                'tenant_id' => (string) $actor->tenant_id,
                'owner_user_id' => $ownerUserId,
                'created_by' => (string) $actor->id,
                'display_name' => (string) $payload['displayName'],
                'first_name' => $payload['firstName'] ?? null,
                'last_name' => $payload['lastName'] ?? null,
                'company_name' => $payload['companyName'] ?? null,
                'primary_email' => $payload['primaryEmail'] ?? null,
                'primary_phone' => $payload['primaryPhone'] ?? null,
                'preferred_contact_channel' => $payload['preferredContactChannel'] ?? null,
                'date_of_birth' => $payload['dateOfBirth'] ?? null,
                'status' => (string) ($payload['status'] ?? 'lead'),
                'last_activity_at' => now(),
            ]);

            $this->upsertAddress($client, $payload);
            $this->appendStatusHistory($client, $actor, null, (string) $client->status, 'Initial client creation');

            return $client->load(['address', 'owner']);
        });

        $this->auditLogger->record([
            'tenant_id' => (string) $actor->tenant_id,
            'actor_id' => (string) $actor->id,
            'action' => 'clients.create',
            'subject_type' => 'client',
            'subject_id' => (string) $client->id,
            'correlation_id' => $correlationId,
            'before_summary' => null,
            'after_summary' => json_encode([
                'displayName' => $client->display_name,
                'status' => $client->status,
                'ownerUserId' => $client->owner_user_id,
            ], JSON_THROW_ON_ERROR),
        ]);

        return $this->serializeClient($client);
    }

    public function update(User $actor, Client $client, array $payload, string $correlationId): array
    {
        $before = [
            'displayName' => (string) $client->display_name,
            'status' => (string) $client->status,
            'primaryEmail' => $client->primary_email,
            'primaryPhone' => $client->primary_phone,
        ];

        $updated = DB::transaction(function () use ($actor, $client, $payload): Client {
            $fromStatus = (string) $client->status;
            $ownerUserId = array_key_exists('ownerUserId', $payload)
                ? $this->resolveOwnerUserId($actor, $payload['ownerUserId'])
                : $client->owner_user_id;

            $client->fill([
                'owner_user_id' => $ownerUserId,
                'display_name' => (string) $payload['displayName'],
                'first_name' => $payload['firstName'] ?? null,
                'last_name' => $payload['lastName'] ?? null,
                'company_name' => $payload['companyName'] ?? null,
                'primary_email' => $payload['primaryEmail'] ?? null,
                'primary_phone' => $payload['primaryPhone'] ?? null,
                'preferred_contact_channel' => $payload['preferredContactChannel'] ?? null,
                'date_of_birth' => $payload['dateOfBirth'] ?? null,
                'status' => (string) ($payload['status'] ?? $client->status),
                'last_activity_at' => now(),
            ]);
            $client->save();

            $this->upsertAddress($client, $payload);
            if ($fromStatus !== (string) $client->status) {
                $this->appendStatusHistory($client, $actor, $fromStatus, (string) $client->status, 'Profile update');
            }

            return $client->load(['address', 'owner']);
        });

        $this->auditLogger->record([
            'tenant_id' => (string) $actor->tenant_id,
            'actor_id' => (string) $actor->id,
            'action' => 'clients.update',
            'subject_type' => 'client',
            'subject_id' => (string) $updated->id,
            'correlation_id' => $correlationId,
            'before_summary' => json_encode($before, JSON_THROW_ON_ERROR),
            'after_summary' => json_encode([
                'displayName' => $updated->display_name,
                'status' => $updated->status,
                'primaryEmail' => $updated->primary_email,
                'primaryPhone' => $updated->primary_phone,
            ], JSON_THROW_ON_ERROR),
        ]);

        return $this->serializeClient($updated);
    }

    private function upsertAddress(Client $client, array $payload): void
    {
        $addressPayload = [
            'address_line_1' => $payload['addressLine1'] ?? null,
            'address_line_2' => $payload['addressLine2'] ?? null,
            'city' => $payload['city'] ?? null,
            'state_code' => $payload['stateCode'] ?? null,
            'postal_code' => $payload['postalCode'] ?? null,
        ];

        $hasAnyAddressField = collect($addressPayload)->contains(fn ($value): bool => $value !== null && $value !== '');
        if (!$hasAnyAddressField && $client->address === null) {
            return;
        }

        $address = $client->address ?? new ClientAddress([
            'id' => (string) Str::uuid(),
            'tenant_id' => (string) $client->tenant_id,
            'client_id' => (string) $client->id,
        ]);

        $address->fill($addressPayload);
        $address->save();
    }

    private function appendStatusHistory(Client $client, User $actor, ?string $fromStatus, string $toStatus, ?string $reason): void
    {
        ClientStatusHistory::query()->create([
            'id' => (string) Str::uuid(),
            'tenant_id' => (string) $client->tenant_id,
            'client_id' => (string) $client->id,
            'actor_user_id' => (string) $actor->id,
            'from_status' => $fromStatus,
            'to_status' => $toStatus,
            'reason' => $reason,
            'occurred_at' => now(),
        ]);
    }

    private function resolveOwnerUserId(User $actor, mixed $candidate): string
    {
        if (!$actor->hasPermission('clients.read.all') || $candidate === null || $candidate === '') {
            return (string) $actor->id;
        }

        $owner = User::query()->where('tenant_id', $actor->tenant_id)->where('id', (string) $candidate)->first();

        return (string) ($owner?->id ?? $actor->id);
    }

    private function serializeClient(Client $client): array
    {
        return [
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
        ];
    }
}
