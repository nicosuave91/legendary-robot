<?php

declare(strict_types=1);

namespace App\Modules\IdentityAccess\Tests\Feature;

use App\Modules\IdentityAccess\Models\User;
use App\Modules\Onboarding\Models\OnboardingState;
use Tests\Support\SeededApiTestCase;

final class AuthBootstrapTest extends SeededApiTestCase
{
    public function test_auth_bootstrap_returns_owner_runtime_context(): void
    {
        $this->sanctumActingAs('owner-user');

        $this->withHeader('X-Correlation-Id', 'corr-auth-bootstrap-owner')
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.user.email', 'owner@example.com')
            ->assertJsonPath('data.onboardingState', 'not_applicable')
            ->assertJsonPath('data.landingRoute', '/app/dashboard')
            ->assertJsonPath('data.tenant.name', 'Default Workspace');
    }

    public function test_auth_bootstrap_routes_admin_with_required_onboarding_to_onboarding_page(): void
    {
        $this->sanctumActingAs('admin-user');

        $this->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.user.email', 'admin@example.com')
            ->assertJsonPath('data.onboardingState', 'required')
            ->assertJsonPath('data.landingRoute', '/onboarding');
    }

    public function test_account_creation_is_runtime_backed_and_defaults_to_required_onboarding(): void
    {
        $owner = $this->sanctumActingAs('owner-user');

        $response = $this
            ->withHeader('X-Correlation-Id', 'corr-settings-accounts-create')
            ->postJson('/api/v1/settings/accounts', [
                'email' => 'new.user@example.com',
                'displayName' => 'New User',
                'role' => 'user',
                'password' => 'Password123!',
                'firstName' => 'New',
                'lastName' => 'User',
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.email', 'new.user@example.com')
            ->assertJsonPath('data.onboardingState', 'required')
            ->assertJsonPath('data.roles.0', 'user');

        $createdUserId = (string) $response->json('data.id');
        $createdUser = User::query()->withoutGlobalScopes()->findOrFail($createdUserId);

        self::assertSame((string) $owner->tenant_id, (string) $createdUser->tenant_id);
        self::assertDatabaseHas('user_profiles', [
            'tenant_id' => (string) $owner->tenant_id,
            'user_id' => $createdUserId,
            'first_name' => 'New',
            'last_name' => 'User',
        ]);

        $onboardingState = OnboardingState::query()->withoutGlobalScopes()->where('user_id', $createdUserId)->first();
        self::assertNotNull($onboardingState);
        self::assertSame('required', $onboardingState->state);
    }
}
