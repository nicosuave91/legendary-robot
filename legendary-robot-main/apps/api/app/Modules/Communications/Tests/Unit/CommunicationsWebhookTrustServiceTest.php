<?php

declare(strict_types=1);

namespace App\Modules\Communications\Tests\Unit;

use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use App\Modules\Communications\Services\Webhooks\CommunicationsWebhookTrustService;
use App\Modules\Communications\Services\Webhooks\SendGridWebhookSignatureVerifier;
use App\Modules\Communications\Services\Webhooks\TwilioWebhookSignatureVerifier;

final class CommunicationsWebhookTrustServiceTest extends TestCase
{
    public function test_it_allows_unverified_callbacks_when_enforcement_is_disabled_and_provider_trust_is_unconfigured(): void
    {
        $service = new CommunicationsWebhookTrustService(
            new TwilioWebhookSignatureVerifier('', null),
            new SendGridWebhookSignatureVerifier('', ''),
            false,
            false,
        );

        $result = $service->verifyTwilio(Request::create('/webhooks/twilio/messaging', 'POST'));

        self::assertTrue($result->accepted);
        self::assertFalse($result->verified);
        self::assertSame('unverified_allowed', $result->mode);
    }

    public function test_it_rejects_unconfigured_callbacks_when_enforcement_is_enabled(): void
    {
        $service = new CommunicationsWebhookTrustService(
            new TwilioWebhookSignatureVerifier('', null),
            new SendGridWebhookSignatureVerifier('', ''),
            true,
            true,
        );

        $twilioResult = $service->verifyTwilio(Request::create('/webhooks/twilio/voice', 'POST'));
        $sendGridResult = $service->verifySendGrid(Request::create('/webhooks/sendgrid/events', 'POST'));

        self::assertFalse($twilioResult->accepted);
        self::assertFalse($sendGridResult->accepted);
    }
}
