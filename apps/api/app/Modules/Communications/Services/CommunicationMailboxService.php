<?php

declare(strict_types=1);

namespace App\Modules\Communications\Services;

use Illuminate\Support\Str;
use App\Modules\Clients\Models\Client;
use App\Modules\Communications\Models\CommunicationMailbox;
use App\Modules\Communications\Models\CommunicationThread;

final class CommunicationMailboxService
{
    public function replyToAddressForThread(Client $client, CommunicationThread $thread, ?string $createdBy = null): ?string
    {
        $domain = $this->inboundDomain();
        if ($domain === '') {
            return null;
        }

        $mailbox = CommunicationMailbox::query()->firstOrCreate([
            'tenant_id' => (string) $client->tenant_id,
            'provider_name' => 'sendgrid',
            'communication_thread_id' => (string) $thread->id,
        ], [
            'id' => (string) Str::uuid(),
            'client_id' => (string) $client->id,
            'alias_local_part' => $this->generateAliasLocalPart((string) $client->tenant_id, (string) $client->id, (string) $thread->id),
            'inbound_address' => '',
            'label' => 'Client email reply mailbox',
            'is_active' => true,
            'metadata' => ['channel' => 'email'],
            'created_by' => $createdBy,
            'updated_by' => $createdBy,
        ]);

        if ((string) $mailbox->inbound_address === '') {
            $mailbox->forceFill([
                'inbound_address' => sprintf('%s@%s', $mailbox->alias_local_part, $domain),
            ])->save();
        }

        return (string) $mailbox->inbound_address;
    }

    /**
     * @return array{tenantId:string, client:Client, thread:?CommunicationThread, mailbox:CommunicationMailbox, resolvedBy:string}|null
     */
    public function resolveInboundRoute(string $rawRecipientList): ?array
    {
        foreach ($this->extractEmailAddresses($rawRecipientList) as $recipient) {
            $normalizedRecipient = $this->normalizeEmail($recipient);
            if ($normalizedRecipient === null) {
                continue;
            }

            $localPart = (string) strstr($normalizedRecipient, '@', true);
            $mailbox = CommunicationMailbox::query()
                ->withoutGlobalScopes()
                ->where('provider_name', 'sendgrid')
                ->where('is_active', true)
                ->where(function ($query) use ($normalizedRecipient, $localPart): void {
                    $query->whereRaw('LOWER(inbound_address) = ?', [$normalizedRecipient])
                        ->orWhere('alias_local_part', $localPart);
                })
                ->first();

            if ($mailbox === null) {
                continue;
            }

            $client = Client::query()
                ->withoutGlobalScopes()
                ->where('tenant_id', $mailbox->tenant_id)
                ->where('id', $mailbox->client_id)
                ->first();

            if ($client === null) {
                continue;
            }

            $thread = CommunicationThread::query()
                ->withoutGlobalScopes()
                ->where('tenant_id', $mailbox->tenant_id)
                ->where('id', $mailbox->communication_thread_id)
                ->first();

            return [
                'tenantId' => (string) $mailbox->tenant_id,
                'client' => $client,
                'thread' => $thread,
                'mailbox' => $mailbox,
                'resolvedBy' => 'mailbox_alias',
            ];
        }

        return null;
    }

    /**
     * @return list<string>
     */
    public function extractEmailAddresses(string $rawValue): array
    {
        $matches = [];
        preg_match_all('/[A-Z0-9._%+\-]+@[A-Z0-9.\-]+\.[A-Z]{2,}/i', $rawValue, $matches);

        $addresses = array_values(array_unique(array_map(
            fn (string $email): string => strtolower(trim($email)),
            $matches[0],
        )));

        if ($addresses !== []) {
            return $addresses;
        }

        $candidate = strtolower(trim($rawValue));

        return filter_var($candidate, FILTER_VALIDATE_EMAIL) ? [$candidate] : [];
    }

    private function normalizeEmail(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $candidate = strtolower(trim($value));

        return filter_var($candidate, FILTER_VALIDATE_EMAIL) ? $candidate : null;
    }

    private function generateAliasLocalPart(string $tenantId, string $clientId, string $threadId): string
    {
        $prefix = trim((string) config('communications.inbound_email.local_part_prefix', 'reply'));
        $prefix = $prefix !== '' ? Str::slug($prefix) : 'reply';
        $hash = substr(hash('sha256', implode('|', [$tenantId, $clientId, $threadId])), 0, 24);

        return sprintf('%s-%s', $prefix, $hash);
    }

    private function inboundDomain(): string
    {
        return strtolower(trim((string) config('communications.inbound_email.domain', '')));
    }
}
