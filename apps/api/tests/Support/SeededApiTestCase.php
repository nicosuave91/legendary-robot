<?php

declare(strict_types=1);

namespace Tests\Support;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;
use App\Modules\IdentityAccess\Models\User;

abstract class SeededApiTestCase extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;
    protected string $seeder = DatabaseSeeder::class;

    protected function sanctumActingAs(string $userId = 'owner-user'): User
    {
        $user = User::query()->withoutGlobalScopes()->findOrFail($userId);
        Sanctum::actingAs($user);

        return $user;
    }
}
