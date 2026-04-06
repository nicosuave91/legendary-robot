<?php

declare(strict_types=1);

namespace App\Modules\IdentityAccess\Http\Resources;

use App\Modules\IdentityAccess\DTOs\AuthContextData;

final class AuthContextResource
{
    public function __construct(
        private readonly AuthContextData $data,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function resolve(): array
    {
        return [
            'isAuthenticated' => $this->data->isAuthenticated,
            'user' => [
                'id' => $this->data->userId,
                'email' => $this->data->email,
                'displayName' => $this->data->displayName,
            ],
            'tenant' => [
                'id' => $this->data->tenantId,
                'name' => $this->data->tenantName,
            ],
            'roles' => $this->data->roles,
            'permissions' => $this->data->permissions,
            'onboardingState' => $this->data->onboardingState,
            'onboardingStep' => $this->data->onboardingStep,
            'theme' => [
                'primary' => $this->data->theme['primary'],
                'secondary' => $this->data->theme['secondary'],
                'tertiary' => $this->data->theme['tertiary'],
            ],
            'landingRoute' => $this->data->landingRoute,
            'selectedIndustry' => $this->data->selectedIndustry,
            'selectedIndustryConfigVersion' => $this->data->selectedIndustryConfigVersion,
            'capabilities' => $this->data->capabilities,
        ];
    }
}
