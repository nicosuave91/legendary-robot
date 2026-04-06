<?php

declare(strict_types=1);

namespace Tests\Feature\Release;

use App\Modules\IdentityAccess\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ReleaseReadinessSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    public function test_owner_can_read_release_critical_surfaces(): void
    {
        $owner = User::query()->withoutGlobalScopes()->findOrFail('owner-user');
        $today = now()->toDateString();
        $startDate = now()->startOfMonth()->toDateString();
        $endDate = now()->endOfMonth()->toDateString();

        $this->actingAs($owner, 'web')
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.user.email', 'owner@example.com');

        $this->actingAs($owner, 'web')->getJson('/api/v1/dashboard/summary')->assertOk();
        $this->actingAs($owner, 'web')->getJson('/api/v1/dashboard/production?window=30d')->assertOk();

        $this->actingAs($owner, 'web')->getJson('/api/v1/clients')->assertOk();
        $this->actingAs($owner, 'web')->getJson('/api/v1/clients/client-jamie-foster')->assertOk();
        $this->actingAs($owner, 'web')->getJson('/api/v1/clients/client-jamie-foster/communications')->assertOk();
        $this->actingAs($owner, 'web')->getJson('/api/v1/clients/client-jamie-foster/events')->assertOk();
        $this->actingAs($owner, 'web')->getJson('/api/v1/clients/client-jamie-foster/applications')->assertOk();

        $this->actingAs($owner, 'web')->getJson('/api/v1/calendar/day?date=' . $today)->assertOk();
        $this->actingAs($owner, 'web')->getJson('/api/v1/events?startDate=' . $startDate . '&endDate=' . $endDate)->assertOk();

        $this->actingAs($owner, 'web')->getJson('/api/v1/imports')->assertOk();
        $this->actingAs($owner, 'web')->getJson('/api/v1/notifications')->assertOk();
        $this->actingAs($owner, 'web')->getJson('/api/v1/audit')->assertOk();
        $this->actingAs($owner, 'web')->getJson('/api/v1/rules')->assertOk();
        $this->actingAs($owner, 'web')->getJson('/api/v1/workflows')->assertOk();
    }
}
