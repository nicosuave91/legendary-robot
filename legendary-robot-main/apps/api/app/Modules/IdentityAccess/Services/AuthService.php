<?php

declare(strict_types=1);

namespace App\Modules\IdentityAccess\Services;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Modules\IdentityAccess\DTOs\AuthContextData;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\Onboarding\Services\OnboardingStateResolver;
use App\Modules\Shared\Audit\AuditLogger;
use App\Modules\TenantGovernance\Services\IndustryCapabilityResolver;

final class AuthService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
        private readonly PermissionSnapshotService $permissionSnapshotService,
        private readonly LandingRouteResolver $landingRouteResolver,
        private readonly OnboardingStateResolver $onboardingStateResolver,
        private readonly IndustryCapabilityResolver $industryCapabilityResolver,
    ) {
    }

    public function signIn(Request $request, string $email, string $password, string $correlationId): AuthContextData
    {
        if (!Auth::guard('web')->attempt(['email' => $email, 'password' => $password])) {
            throw new AuthenticationException('Invalid credentials.');
        }

        $request->session()->regenerate();

        /** @var User $user */
        $user = Auth::guard('web')->user();
        $user->loadMissing(['tenant.themeSetting', 'roles.permissions', 'profile', 'onboardingState', 'industryAssignment']);

        if (($user->status ?? 'active') !== 'active') {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw new AuthenticationException('Account is deactivated.');
        }

        $context = $this->buildContext($user);

        $this->auditLogger->record([
            'tenant_id' => $context->tenantId,
            'actor_id' => $context->userId,
            'action' => 'auth.sign_in',
            'subject_type' => 'user',
            'subject_id' => $context->userId,
            'correlation_id' => $correlationId,
            'before_summary' => null,
            'after_summary' => json_encode(['email' => $email, 'landingRoute' => $context->landingRoute], JSON_THROW_ON_ERROR),
        ]);

        return $context;
    }

    public function signOut(Request $request, string $correlationId): void
    {
        /** @var User|null $user */
        $user = $request->user();

        if ($user !== null) {
            $this->auditLogger->record([
                'tenant_id' => (string) $user->tenant_id,
                'actor_id' => (string) $user->id,
                'action' => 'auth.sign_out',
                'subject_type' => 'user',
                'subject_id' => (string) $user->id,
                'correlation_id' => $correlationId,
                'before_summary' => null,
                'after_summary' => null,
            ]);
        }

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    public function me(Request $request): AuthContextData
    {
        /** @var User $user */
        $user = Auth::guard('web')->user();
        $user->loadMissing(['tenant.themeSetting', 'roles.permissions', 'profile', 'onboardingState', 'industryAssignment']);

        return $this->buildContext($user);
    }

    private function buildContext(User $user): AuthContextData
    {
        $onboarding = $this->onboardingStateResolver->resolve($user);
        $theme = $user->tenant?->themeSetting;
        $capabilitySnapshot = $this->industryCapabilityResolver->forUser($user);

        return new AuthContextData(
            isAuthenticated: true,
            userId: (string) $user->id,
            email: (string) $user->email,
            displayName: (string) $user->name,
            tenantId: (string) $user->tenant_id,
            tenantName: (string) ($user->tenant?->name ?? 'Workspace'),
            roles: $this->permissionSnapshotService->roleNames($user),
            permissions: $this->permissionSnapshotService->forUser($user),
            onboardingState: $onboarding['state'],
            onboardingStep: $onboarding['currentStep'],
            theme: [
                'primary' => (string) ($theme?->primary_color ?? '#1d4ed8'),
                'secondary' => (string) ($theme?->secondary_color ?? '#0f172a'),
                'tertiary' => (string) ($theme?->tertiary_color ?? '#64748b'),
            ],
            landingRoute: $this->landingRouteResolver->forUser($user),
            selectedIndustry: $onboarding['selectedIndustry'],
            selectedIndustryConfigVersion: $onboarding['selectedIndustryConfigVersion'],
            capabilities: $capabilitySnapshot['capabilities'],
        );
    }
}
