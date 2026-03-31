<?php

declare(strict_types=1);

namespace App\Modules\TenantGovernance\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\TenantGovernance\Models\TenantIndustryConfiguration;
use App\Modules\Shared\Audit\AuditLogger;

final class IndustryConfigurationService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function listForActor(User $actor): array
    {
        return TenantIndustryConfiguration::query()
            ->where('tenant_id', $actor->tenant_id)
            ->orderBy('industry')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (TenantIndustryConfiguration $config): array => [
                'id' => (string) $config->id,
                'industry' => (string) $config->industry,
                'version' => (string) $config->version,
                'status' => (string) $config->status,
                'isActive' => (bool) $config->is_active,
                'capabilities' => array_values($config->capabilities ?? []),
                'notes' => $config->notes,
                'publishedAt' => $config->published_at?->toIso8601String(),
                'activatedAt' => $config->activated_at?->toIso8601String(),
            ])
            ->all();
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function createVersion(User $actor, array $payload, string $correlationId): array
    {
        $version = $this->nextVersion((string) $actor->tenant_id, (string) $payload['industry']);
        $status = (string) ($payload['status'] ?? 'draft');
        $shouldActivate = (bool) ($payload['activate'] ?? false);
        $capabilities = array_values(array_unique(array_filter(
            array_map(
                static fn ($entry): string => trim((string) $entry),
                is_array($payload['capabilities'] ?? null) ? $payload['capabilities'] : []
            ),
            static fn (string $entry): bool => $entry !== ''
        )));

        $config = DB::transaction(function () use ($actor, $payload, $version, $status, $shouldActivate, $capabilities): TenantIndustryConfiguration {
            if ($shouldActivate || $status === 'published') {
                TenantIndustryConfiguration::query()
                    ->where('tenant_id', $actor->tenant_id)
                    ->where('industry', $payload['industry'])
                    ->where('is_active', true)
                    ->update(['is_active' => false]);
            }

            return TenantIndustryConfiguration::query()->create([
                'id' => (string) Str::uuid(),
                'tenant_id' => (string) $actor->tenant_id,
                'industry' => (string) $payload['industry'],
                'version' => $version,
                'status' => $status,
                'is_active' => $shouldActivate || $status === 'published',
                'capabilities' => $capabilities,
                'notes' => $payload['notes'] ?? null,
                'created_by' => (string) $actor->id,
                'published_at' => $status === 'published' ? now() : null,
                'activated_at' => ($shouldActivate || $status === 'published') ? now() : null,
            ]);
        });

        $this->auditLogger->record([
            'tenant_id' => (string) $actor->tenant_id,
            'actor_id' => (string) $actor->id,
            'action' => 'settings.industry_configurations.create',
            'subject_type' => 'tenant_industry_config',
            'subject_id' => (string) $config->id,
            'correlation_id' => $correlationId,
            'before_summary' => null,
            'after_summary' => json_encode([
                'industry' => $config->industry,
                'version' => $config->version,
                'status' => $config->status,
                'isActive' => $config->is_active,
                'capabilities' => $config->capabilities,
            ], JSON_THROW_ON_ERROR),
        ]);

        return [
            'id' => (string) $config->id,
            'industry' => (string) $config->industry,
            'version' => (string) $config->version,
            'status' => (string) $config->status,
            'isActive' => (bool) $config->is_active,
            'capabilities' => array_values($config->capabilities ?? []),
            'notes' => $config->notes,
            'publishedAt' => $config->published_at?->toIso8601String(),
            'activatedAt' => $config->activated_at?->toIso8601String(),
        ];
    }

    public function activeVersionForTenantAndIndustry(string $tenantId, string $industry): ?TenantIndustryConfiguration
    {
        return TenantIndustryConfiguration::query()
            ->where('tenant_id', $tenantId)
            ->where('industry', $industry)
            ->where('status', 'published')
            ->where('is_active', true)
            ->latest('activated_at')
            ->latest('created_at')
            ->first();
    }

    public function versionForTenantIndustry(string $tenantId, string $industry, string $version): ?TenantIndustryConfiguration
    {
        return TenantIndustryConfiguration::query()
            ->where('tenant_id', $tenantId)
            ->where('industry', $industry)
            ->where('version', $version)
            ->first();
    }

    private function nextVersion(string $tenantId, string $industry): string
    {
        $existingVersions = TenantIndustryConfiguration::query()
            ->withoutGlobalScopes()
            ->where('tenant_id', $tenantId)
            ->where('industry', $industry)
            ->pluck('version')
            ->all();

        $highest = 0;
        foreach ($existingVersions as $version) {
            if (preg_match('/^v(\d+)$/', (string) $version, $matches) === 1) {
                $highest = max($highest, (int) $matches[1]);
            }
        }

        return 'v' . ($highest + 1);
    }
}
