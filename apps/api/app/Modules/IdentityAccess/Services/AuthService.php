<?php

declare(strict_types=1);

namespace App\Modules\IdentityAccess\Services;

use App\Modules\IdentityAccess\DTOs\AuthContextData;
use App\Modules\Shared\Audit\AuditLogger;

final class AuthService
{
    public function __construct(
        private readonly AuditLogger $auditLogger,
    ) {
    }

    public function signIn(string $email, string $password, string $correlationId): AuthContextData
    {
        // Sprint 1 intentionally stops at baseline scaffolding.
        // Replace with real Sanctum session establishment in Sprint 2.
        $context = $this->buildStubContext($email);

        $this->auditLogger->record([
            'tenant_id' => $context->tenantId,
            'actor_id' => $context->userId,
            'action' => 'auth.sign_in',
            'subject_type' => 'user',
            'subject_id' => $context->userId,
            'correlation_id' => $correlationId,
            'before_summary' => null,
            'after_summary' => json_encode(['email' => $email], JSON_THROW_ON_ERROR),
        ]);

        return $context;
    }

    public function signOut(string $correlationId): void
    {
        $this->auditLogger->record([
            'tenant_id' => 'tenant-1',
            'actor_id' => 'user-1',
            'action' => 'auth.sign_out',
            'subject_type' => 'user',
            'subject_id' => 'user-1',
            'correlation_id' => $correlationId,
            'before_summary' => null,
            'after_summary' => null,
        ]);
    }

    public function me(string $correlationId): AuthContextData
    {
        return $this->buildStubContext('owner@example.com');
    }

    private function buildStubContext(string $email): AuthContextData
    {
        return new AuthContextData(
            isAuthenticated: true,
            userId: 'user-1',
            email: $email,
            displayName: 'Tenant Owner',
            tenantId: 'tenant-1',
            tenantName: 'Default Workspace',
            roles: ['owner'],
            permissions: [
                'identity-access.auth.read-self',
                'identity-access.auth.sign-out',
                'settings.profile.read',
                'settings.profile.update',
            ],
            onboardingState: 'not_applicable',
            theme: [
                'primary' => '#1d4ed8',
                'secondary' => '#0f172a',
                'tertiary' => '#64748b',
            ],
            landingRoute: '/app/dashboard',
        );
    }
}
