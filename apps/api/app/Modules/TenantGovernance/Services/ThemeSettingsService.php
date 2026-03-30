<?php

declare(strict_types=1);

namespace App\Modules\TenantGovernance\Services;

use App\Modules\IdentityAccess\Models\User;
use App\Modules\Shared\Audit\AuditLogger;
use App\Modules\TenantGovernance\Models\ThemeSetting;

final class ThemeSettingsService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, string>
     */
    public function update(User $actor, array $payload, string $correlationId): array
    {
        $theme = ThemeSetting::query()->firstOrCreate([
            'tenant_id' => (string) $actor->tenant_id,
        ], [
            'primary_color' => '#1d4ed8',
            'secondary_color' => '#0f172a',
            'tertiary_color' => '#64748b',
        ]);

        $before = [
            'primary' => (string) $theme->primary_color,
            'secondary' => (string) $theme->secondary_color,
            'tertiary' => (string) $theme->tertiary_color,
        ];

        $theme->fill([
            'primary_color' => (string) $payload['primary'],
            'secondary_color' => (string) $payload['secondary'],
            'tertiary_color' => (string) $payload['tertiary'],
        ]);
        $theme->save();

        $after = [
            'primary' => (string) $theme->primary_color,
            'secondary' => (string) $theme->secondary_color,
            'tertiary' => (string) $theme->tertiary_color,
        ];

        $this->auditLogger->record([
            'tenant_id' => (string) $actor->tenant_id,
            'actor_id' => (string) $actor->id,
            'action' => 'settings.theme.update',
            'subject_type' => 'theme_setting',
            'subject_id' => (string) $theme->id,
            'correlation_id' => $correlationId,
            'before_summary' => json_encode($before, JSON_THROW_ON_ERROR),
            'after_summary' => json_encode($after, JSON_THROW_ON_ERROR),
        ]);

        return $after;
    }
}
