<?php

declare(strict_types=1);

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Modules\IdentityAccess\Models\User;
use App\Modules\TenantGovernance\Services\IndustryConfigurationService;

final class AuthBootstrapConsistencyTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;
    protected string $seeder = DatabaseSeeder::class;

    public function test_theme_settings_round_trip_into_auth_bootstrap(): void
    {
        $owner = User::query()->withoutGlobalScopes()->findOrFail('owner-user');

        $payload = [
            'primary' => '#112233',
            'secondary' => '#223344',
            'tertiary' => '#334455',
        ];

        $this->actingAs($owner, 'web')
            ->patchJson('/api/v1/settings/theme', $payload)
            ->assertOk()
            ->assertJsonPath('data.primary', '#112233')
            ->assertJsonPath('data.secondary', '#223344')
            ->assertJsonPath('data.tertiary', '#334455');

        $this->actingAs($owner, 'web')
            ->getJson('/api/v1/settings/theme')
            ->assertOk()
            ->assertJsonPath('data.primary', '#112233')
            ->assertJsonPath('data.secondary', '#223344')
            ->assertJsonPath('data.tertiary', '#334455');

        $bootstrap = $this->actingAs($owner, 'web')
            ->getJson('/api/v1/auth/me')
            ->assertOk();

        self::assertSame('#112233', $bootstrap->json('data.theme.primary'));
        self::assertSame('#223344', $bootstrap->json('data.theme.secondary'));
        self::assertSame('#334455', $bootstrap->json('data.theme.tertiary'));
    }

    public function test_active_industry_version_and_capabilities_flow_into_auth_bootstrap(): void
    {
        $owner = User::query()->withoutGlobalScopes()->findOrFail('owner-user');
        $user = User::query()->withoutGlobalScopes()->findOrFail('standard-user');

        app(IndustryConfigurationService::class)->createVersion(
            $owner,
            [
                'industry' => 'Mortgage',
                'status' => 'published',
                'activate' => true,
                'capabilities' => [
                    'calendar',
                    'communications',
                    'imports',
                    'underwriting-dashboard',
                ],
                'notes' => 'Runtime verification activation.',
            ],
            'corr-industry-config-v2',
        );

        $beforeBootstrap = $this->actingAs($user, 'web')
            ->getJson('/api/v1/auth/me')
            ->assertOk();

        self::assertSame('required', $beforeBootstrap->json('data.onboardingState'));
        self::assertSame('/onboarding', $beforeBootstrap->json('data.landingRoute'));
        self::assertSame([], $beforeBootstrap->json('data.capabilities'));

        $this->actingAs($user, 'web')
            ->patchJson('/api/v1/onboarding/profile-confirmation', $this->profileConfirmationPayload())
            ->assertOk()
            ->assertJsonPath('data.currentStep', 'industry_selection');

        $this->actingAs($user, 'web')
            ->patchJson('/api/v1/onboarding/industry-selection', ['industry' => 'Mortgage'])
            ->assertOk()
            ->assertJsonPath('data.selectedIndustry', 'Mortgage')
            ->assertJsonPath('data.selectedIndustryConfigVersion', 'v2')
            ->assertJsonPath('data.currentStep', 'completion');

        $inProgressBootstrap = $this->actingAs($user, 'web')
            ->getJson('/api/v1/auth/me')
            ->assertOk();

        $inProgressCapabilities = $inProgressBootstrap->json('data.capabilities');

        self::assertSame('Mortgage', $inProgressBootstrap->json('data.selectedIndustry'));
        self::assertSame('v2', $inProgressBootstrap->json('data.selectedIndustryConfigVersion'));
        self::assertSame('/onboarding', $inProgressBootstrap->json('data.landingRoute'));
        self::assertContains('underwriting-dashboard', $inProgressCapabilities);
        self::assertContains('communications', $inProgressCapabilities);

        $this->actingAs($user, 'web')
            ->postJson('/api/v1/onboarding/complete')
            ->assertOk()
            ->assertJsonPath('data.state', 'completed');

        $completedBootstrap = $this->actingAs($user, 'web')
            ->getJson('/api/v1/auth/me')
            ->assertOk();

        self::assertSame('completed', $completedBootstrap->json('data.onboardingState'));
        self::assertSame('/app/dashboard', $completedBootstrap->json('data.landingRoute'));
        self::assertSame('Mortgage', $completedBootstrap->json('data.selectedIndustry'));
        self::assertSame('v2', $completedBootstrap->json('data.selectedIndustryConfigVersion'));
        self::assertContains('underwriting-dashboard', $completedBootstrap->json('data.capabilities'));
    }

    /**
     * @return array<string, string>
     */
    private function profileConfirmationPayload(): array
    {
        return [
            'firstName' => 'Runtime',
            'lastName' => 'Verifier',
            'phone' => '804-555-0199',
            'birthday' => '1990-01-02',
            'addressLine1' => '101 Runtime Way',
            'addressLine2' => 'Suite 200',
            'city' => 'Richmond',
            'stateCode' => 'VA',
            'postalCode' => '23219',
        ];
    }
}
