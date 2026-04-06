<?php

declare(strict_types=1);

namespace App\Modules\Communications\Tests\Unit;

use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use App\Modules\Communications\Services\Webhooks\SendGridWebhookSignatureVerifier;

final class SendGridWebhookSignatureVerifierTest extends TestCase
{
    public function test_it_verifies_signed_sendgrid_payloads_using_raw_body_and_timestamp(): void
    {
        $keyPair = openssl_pkey_new([
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
            'private_key_bits' => 2048,
        ]);

        if ($keyPair === false) {
            self::markTestSkipped('OpenSSL RSA key generation is unavailable in this environment.');
        }

        $privateKeyPem = '';
        $exported = openssl_pkey_export($keyPair, $privateKeyPem);

        if ($exported === false || $privateKeyPem === '') {
            self::markTestSkipped('OpenSSL private key export is unavailable in this environment.');
        }

        $details = openssl_pkey_get_details($keyPair);

        if (!is_array($details) || !isset($details['key']) || $details['key'] === '') {
            self::markTestSkipped('OpenSSL public key details are unavailable in this environment.');
        }

        $publicKeyPem = (string) $details['key'];

        $timestamp = '1712086442';
        $payload = json_encode([['event' => 'delivered', 'message_id' => 'msg-123']], JSON_THROW_ON_ERROR);
        $signedPayload = $timestamp . $payload;

        $signature = '';
        $signed = openssl_sign($signedPayload, $signature, $privateKeyPem, OPENSSL_ALGO_SHA256);

        if ($signed === false || $signature === '') {
            self::markTestSkipped('OpenSSL signing is unavailable in this environment.');
        }

        $request = Request::create('/webhooks/sendgrid/events', 'POST', [], [], [], [
            'HTTP_X_TWILIO_EMAIL_EVENT_WEBHOOK_SIGNATURE' => base64_encode($signature),
            'HTTP_X_TWILIO_EMAIL_EVENT_WEBHOOK_TIMESTAMP' => $timestamp,
        ], $payload);

        $verifier = new SendGridWebhookSignatureVerifier($publicKeyPem, null);

        self::assertTrue($verifier->verify($request));
    }

    public function test_it_can_validate_sendgrid_oauth_bearer_tokens_when_configured(): void
    {
        $request = Request::create('/webhooks/sendgrid/inbound', 'POST', [], [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer release-token-123',
        ], 'raw-body');

        $verifier = new SendGridWebhookSignatureVerifier(null, 'release-token-123');

        self::assertTrue($verifier->verify($request));
    }

    public function test_it_rejects_when_verification_inputs_do_not_match(): void
    {
        $request = Request::create('/webhooks/sendgrid/events', 'POST', [], [], [], [
            'HTTP_X_TWILIO_EMAIL_EVENT_WEBHOOK_SIGNATURE' => base64_encode('invalid'),
            'HTTP_X_TWILIO_EMAIL_EVENT_WEBHOOK_TIMESTAMP' => '1712086442',
            'HTTP_AUTHORIZATION' => 'Bearer wrong-token',
        ], '[]');

        $verifier = new SendGridWebhookSignatureVerifier('not-a-real-public-key', 'expected-token');

        self::assertFalse($verifier->verify($request));
    }
}
