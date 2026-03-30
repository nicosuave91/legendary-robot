<?php

declare(strict_types=1);

return [
    App\Providers\AppServiceProvider::class,
    App\Modules\Shared\Providers\SharedServiceProvider::class,
    App\Modules\Audit\Providers\AuditServiceProvider::class,
    App\Modules\TenantGovernance\Providers\TenantGovernanceServiceProvider::class,
    App\Modules\IdentityAccess\Providers\IdentityAccessServiceProvider::class,
    App\Modules\Onboarding\Providers\OnboardingServiceProvider::class,
    App\Modules\HomepageAnalytics\Providers\HomepageAnalyticsServiceProvider::class,
    App\Modules\Clients\Providers\ClientsServiceProvider::class,
    App\Modules\Communications\Providers\CommunicationsServiceProvider::class,
];
