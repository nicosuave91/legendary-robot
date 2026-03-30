<?php

declare(strict_types=1);

namespace App\Modules\IdentityAccess\Tests\Feature;

use PHPUnit\Framework\TestCase;

final class AuthBootstrapTest extends TestCase
{
    public function test_generated_client_contains_auth_and_onboarding_operations(): void
    {
        $clientPath = dirname(__DIR__, 7) . '/apps/web/src/lib/api/generated/client.ts';
        $client = (string) file_get_contents($clientPath);

        $this->assertStringContainsString('postAuthSignIn', $client);
        $this->assertStringContainsString('getOnboardingState', $client);
        $this->assertStringContainsString('postSettingsAccounts', $client);
    }
}
