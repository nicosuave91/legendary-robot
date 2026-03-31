<?php

declare(strict_types=1);

namespace App\Modules\TenantGovernance\Services;

use App\Modules\IdentityAccess\Models\User;

final class IndustryCapabilityResolver
{
    public function __construct(
        private readonly IndustryConfigurationService $industryConfigurationService,
    ) {
    }

    /**
     * @return array{industry: ?string, version: ?string, capabilities: array<int, string>}
     */
    public function forUser(User $user): array
    {
        $user->loadMissing(['industryAssignment']);

        $industry = $user->industryAssignment?->industry;
        $version = $user->industryAssignment?->config_version;

        if ($industry === null || $version === null) {
            return [
                'industry' => $industry,
                'version' => $version,
                'capabilities' => [],
            ];
        }

        $config = $this->industryConfigurationService->versionForTenantIndustry(
            (string) $user->tenant_id,
            (string) $industry,
            (string) $version,
        );

        return [
            'industry' => $industry,
            'version' => $version,
            'capabilities' => array_values($config?->capabilities ?? []),
        ];
    }
}
