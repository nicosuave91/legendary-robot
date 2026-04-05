<?php

declare(strict_types=1);

namespace App\Modules\TenantGovernance\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\TenantGovernance\Models\TenantIndustryConfiguration;

final class IndustryConfigurationService
{
    /**
     * @return list<array<string, mixed>>
     */
    public function listForActor(User $actor): array
    {
        return TenantIndustryConfiguration::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $actor->tenant_id)
            ->orderBy('industry')
            ->orderByDesc('is_active')
            ->orderByDesc('activated_at')
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (TenantIndustryConfiguration $configuration): array => $this->serialize($configuration))
            ->values()
            ->all();
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function createVersion(User $actor, array $payload, string $correlationId): array
    {
        unset($correlationId);

        return DB::transaction(function () use ($actor, $payload): array {
            $industry = (string) $payload['industry'];
            $status = (string) $payload['status'];
            $activate = (bool) ($payload['activate'] ?? false);

            $latestVersion = TenantIndustryConfiguration::query()
                ->withoutGlobalScopes()
                ->where('tenant_id', $actor->tenant_id)
                ->where('industry', $industry)
                ->orderByDesc('created_at')
                ->first();

            $nextVersionNumber = $this->nextVersionNumber($latestVersion?->version);

            $configuration = TenantIndustryConfiguration::query()->create([
                'id' => (string) Str::uuid(),
                'tenant_id' => (string) $actor->tenant_id,
                'industry' => $industry,
                'version' => sprintf('v%d', $nextVersionNumber),
                'status' => $status,
                'is_active' => $activate,
                'capabilities' => array_values((array) ($payload['capabilities'] ?? [])),
                'notes' => $payload['notes'] ?? null,
                'created_by' => (string) $actor->id,
                'published_at' => $status === 'published' ? now() : null,
                'activated_at' => $activate ? now() : null,
            ]);

            if ($activate) {
                TenantIndustryConfiguration::query()
                    ->withoutGlobalScopes()
                    ->where('tenant_id', $actor->tenant_id)
                    ->where('industry', $industry)
                    ->where('id', '!=', $configuration->id)
                    ->update([
                        'is_active' => false,
                        'activated_at' => null,
                    ]);
            }

            /** @var TenantIndustryConfiguration $fresh */
            $fresh = $configuration->fresh();

            return $this->serialize($fresh);
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function serialize(TenantIndustryConfiguration $configuration): array
    {
        return [
            'id' => (string) $configuration->id,
            'industry' => (string) $configuration->industry,
            'version' => (string) $configuration->version,
            'status' => (string) $configuration->status,
            'isActive' => (bool) $configuration->is_active,
            'capabilities' => array_values((array) $configuration->capabilities),
            'notes' => $configuration->notes,
            'publishedAt' => $configuration->published_at?->toIso8601String(),
            'activatedAt' => $configuration->activated_at?->toIso8601String(),
        ];
    }

    private function nextVersionNumber(?string $currentVersion): int
    {
        if ($currentVersion === null || trim($currentVersion) === '') {
            return 1;
        }

        if (preg_match('/^v(?P<number>\d+)$/i', trim($currentVersion), $matches) === 1) {
            return ((int) ($matches['number'] ?? 0)) + 1;
        }

        return 1;
    }
}
