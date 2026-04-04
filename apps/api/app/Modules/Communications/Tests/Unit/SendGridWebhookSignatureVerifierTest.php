<?php

declare(strict_types=1);

namespace App\Modules\Communications\Tests\Unit;

use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;
use App\Modules\Communications\Services\Webhooks\SendGridWebhookSignatureVerifier;

final class SendGridWebhookSignatureVerifierTest extends TestCase
{
    private const PRIVATE_KEY_PEM = <<<'PEM'
-----BEGIN PRIVATE KEY-----
MIIEvAIBADANBgkqhkiG9w0BAQEFAASCBKYwggSiAgEAAoIBAQDGd1Cby3+5kDwy
qPyETuMlCfmZGDv7rFoA2ID3Cp8JlHhp1qBrga228E3hLIjIvdLnYT41//aa/sfI
sg80NRpyNgyGXDaz8rasHULTfoYTB830ttZj39Se02N6ZyS3AaNtqR6Tu+TbSyqV
/lGLX1ZJoXJsqc5S8SvOhBD5xb0iF5hpuFQ4hbo4hB914cEllN+kXsvMNJXxk+lm
tgaNvrUQF0M8gzNihpXxJd9D7+Zt6gavt/zZ5GHEDfPE1zgkA5YWQA5BAHKx6kAX
qmnAjTHzuqZDiO/rSh3sRTYkGFffz3wwf4zcQhUj53jKmsMnU6UCbb69+faEfuY8
2yqXhDZvAgMBAAECggEAXDdpbZKPXPYfenyZpJKRKY8qek/P4z9wrI7s0Z0OS6HA
l5ECBiIByQx8a2HJhtjo69j70pSGDfvPcboGpX/9M09Y339ubYucBHouKF5URvhr
4san9E03GrtnMCNH5H7u5I/d5NA75QdWmM5MLoHUcq8BH2uBMnncHH+x8ZTHLlTM
X+d+omXSmPRVyo5CKnw/VIjgrIlfUtQIqhTTFVDWnFEzKsPm/eFXPIRXFt+APV5L
w4u8XOJH9G5k4eOoHnpivC5QcdQ3BaBCo0oF6LClNMRnosnT8LcSax0RMcuRgcJb
LxM3vYXTv1n4WUlkIIr8k8CeyjiF9LYrapXmGgoEaQKBgQD+PvxFX8c2mW4ErrQy
ROJyFOTyOrfqW6IdPXzUk2ErmjIdg2TnndW/7cKXhYa13JVD/uavLRI6S8NSM0cH
BiCLlYwot2AO/egVCKdSm1tJHTByV2udSsuItCiHZlLodtuB1WPFlhu+3gDG3H/6
jgcWNtVRB1qr4cx6DkSy31D3iQKBgQDH1dGJfYWyXo94vv9qXFYwo1xMVajXP78Z
rNC474APZYMHA2m3K950XoviXlQlgnexo4jq1uMrwW2HhIlce23urx3GcIoMs9Ej
MH/O2E02JdciZY4w0Hr992v4BToW+u0EgOTMpwEJtN6FnZpqoMe4hCtkUfGfRfkV
v9nBEtzINwKBgEtglR5kRQW0+188BISa06AK/c2rBvfebvPIVQgZIcfxkUYavIHG
06ZxcS3HEqc8XKyqF+57qFnMOH5YYYh9koyPq9wucbZAJNyHkkP1d1z34HMl4+vg
TvOapiFufsoY4v9dKpVb6aHu48ARg4ffL86wNxIs4f7vlNXjAQ8fEjwxAoGAX28L
jH+3k8F4/w8n52GFf+DkOUhHQ9SbrOVNIKSwIao28uh8Wg/HQwfMAiVORkOSafFR
d+V7w++yIR/4gYQose5LZ/Ni2kIdxJJq1xuRdCSKI1EgoMtPkW3R0Dae0U6wVpA/
tESlSD9qbaEgl3+FIeds2ZIQtCG0nOBsW7poWe0CgYAi9OI219BzkmzPhADY5CuD
fmCYW3t9sAwqmlOGe5DYDXuBLvWnYN4JJmoSzKkHat4BYGByG0E6Uh9hNH06kvTu
Tk8LH5sh8AsMV4dISmOUnJ74ih5C7jqOkpHXD4CqmlELUryrBFpvDoY44teuTyS9
d3+seRiZB7rtk2ANDJ4lSw==
-----END PRIVATE KEY-----
PEM;

    private const PUBLIC_KEY_PEM = <<<'PEM'
-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAxndQm8t/uZA8Mqj8hE7j
JQn5mRg7+6xaANiA9wqfCZR4adaga4GttvBN4SyIyL3S52E+Nf/2mv7HyLIPNDUa
cjYMhlw2s/K2rB1C036GEwfN9LbWY9/UntNjemcktwGjbakek7vk20sqlf5Ri19W
SaFybKnOUvErzoQQ+cW9IheYabhUOIW6OIQfdeHBJZTfpF7LzDSV8ZPpZrYGjb61
EBdDPIMzYoaV8SXfQ+/mbeoGr7f82eRhxA3zxNc4JAOWFkAOQQBysepAF6ppwI0x
87qmQ4jv60od7EU2JBhX3898MH+M3EIVI+d4yprDJ1OlAm2+vfn2hH7mPNsql4Q2
bwIDAQAB
-----END PUBLIC KEY-----
PEM;

    public function test_it_verifies_signed_sendgrid_payloads_using_raw_body_and_timestamp(): void
    {
        $timestamp = '1712086442';
        $payload = json_encode([['event' => 'delivered', 'message_id' => 'msg-123']], JSON_THROW_ON_ERROR);
        $signedPayload = $timestamp . $payload;

        $signed = openssl_sign($signedPayload, $signature, self::PRIVATE_KEY_PEM, OPENSSL_ALGO_SHA256);
        self::assertTrue($signed);

        $request = Request::create('/webhooks/sendgrid/events', 'POST', [], [], [], [
            'HTTP_X_TWILIO_EMAIL_EVENT_WEBHOOK_SIGNATURE' => base64_encode($signature),
            'HTTP_X_TWILIO_EMAIL_EVENT_WEBHOOK_TIMESTAMP' => $timestamp,
        ], $payload);

        $verifier = new SendGridWebhookSignatureVerifier(self::PUBLIC_KEY_PEM, null);

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
