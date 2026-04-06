<?php

declare(strict_types=1);

namespace App\Modules\Communications\Services;

use App\Modules\Clients\Models\Client;
use App\Modules\Communications\Models\CommunicationEndpoint;

final class CommunicationEndpointResolver
{
    public function __construct(
        private readonly PhoneNumberNormalizer $phoneNumberNormalizer,
    ) {
    }

    public function resolveOutboundSmsAddress(string $tenantId): ?string
    {
        $endpoint = CommunicationEndpoint::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('provider_name', 'twilio')
            ->where('channel', 'sms')
            ->where('is_active', true)
            ->orderByDesc('is_default_outbound')
            ->orderBy('created_at')
            ->first();

        if ($endpoint !== null) {
            return $this->phoneNumberNormalizer->normalize((string) $endpoint->address_display)
                ?? $this->phoneNumberNormalizer->normalize((string) $endpoint->address_normalized);
        }

        return $this->phoneNumberNormalizer->normalize((string) config('services.twilio.from_number'));
    }

    /**
     * @return array{tenantId:string, client:Client, endpoint:?CommunicationEndpoint}|null
     */
    public function resolveInboundSmsRoute(?string $toAddress, ?string $fromAddress): ?array
    {
        $normalizedTo = $this->phoneNumberNormalizer->normalize($toAddress);
        $normalizedFrom = $this->phoneNumberNormalizer->normalize($fromAddress);

        if ($normalizedTo === null || $normalizedFrom === null) {
            return null;
        }

        $endpoint = CommunicationEndpoint::query()
            ->withoutGlobalScopes()
            ->where('provider_name', 'twilio')
            ->where('channel', 'sms')
            ->where('is_active', true)
            ->where('address_normalized', $normalizedTo)
            ->first();

        if ($endpoint !== null) {
            $client = $this->findClientByPhone((string) $endpoint->tenant_id, $normalizedFrom);

            return $client !== null ? [
                'tenantId' => (string) $endpoint->tenant_id,
                'client' => $client,
                'endpoint' => $endpoint,
            ] : null;
        }

        $fallbackFromNumber = $this->phoneNumberNormalizer->normalize((string) config('services.twilio.from_number'));

        if ($fallbackFromNumber !== null && $fallbackFromNumber === $normalizedTo) {
            $matchingClients = Client::query()
                ->withoutGlobalScopes()
                ->whereNotNull('primary_phone')
                ->get()
                ->filter(fn (Client $client): bool => $this->phoneNumberNormalizer->same($client->primary_phone, $normalizedFrom))
                ->values();

            if ($matchingClients->count() === 1) {
                /** @var Client $client */
                $client = $matchingClients->first();

                return [
                    'tenantId' => (string) $client->tenant_id,
                    'client' => $client,
                    'endpoint' => null,
                ];
            }
        }

        return null;
    }

    private function findClientByPhone(string $tenantId, string $normalizedPhone): ?Client
    {
        /** @var Client|null $client */
        $client = Client::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->whereNotNull('primary_phone')
            ->get()
            ->first(fn (Client $candidate): bool => $this->phoneNumberNormalizer->same($candidate->primary_phone, $normalizedPhone));

        return $client;
    }
}
