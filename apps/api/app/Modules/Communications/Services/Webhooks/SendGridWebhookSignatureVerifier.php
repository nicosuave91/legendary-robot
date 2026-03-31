<?php

declare(strict_types=1);

namespace App\Modules\Communications\Services\Webhooks;

use Illuminate\Http\Request;

final class SendGridWebhookSignatureVerifier
{
    public function __construct(
        private readonly ?string $publicKey = null,
        private readonly ?string $oauthBearerToken = null,
    ) {
    }

    public function hasVerificationConfiguration(): bool
    {
        return $this->resolvePublicKey() !== '' || $this->resolveOAuthBearerToken() !== '';
    }

    public function verify(Request $request): bool
    {
        $signatureConfigured = $this->resolvePublicKey() !== '';
        $oauthConfigured = $this->resolveOAuthBearerToken() !== '';

        if (!$signatureConfigured && !$oauthConfigured) {
            return false;
        }

        if ($signatureConfigured && !$this->verifySignature($request)) {
            return false;
        }

        if ($oauthConfigured && !$this->verifyOAuthBearerToken($request)) {
            return false;
        }

        return true;
    }

    private function verifySignature(Request $request): bool
    {
        $signature = (string) $request->header('X-Twilio-Email-Event-Webhook-Signature', '');
        $timestamp = (string) $request->header('X-Twilio-Email-Event-Webhook-Timestamp', '');
        $publicKey = $this->normalizePublicKey($this->resolvePublicKey());

        if ($signature === '' || $timestamp === '' || $publicKey === '') {
            return false;
        }

        $decodedSignature = base64_decode($signature, true);
        if ($decodedSignature === false) {
            return false;
        }

        $payload = $timestamp . $request->getContent();
        $verified = openssl_verify($payload, $decodedSignature, $publicKey, OPENSSL_ALGO_SHA256);

        return $verified === 1;
    }

    private function verifyOAuthBearerToken(Request $request): bool
    {
        $expectedToken = $this->resolveOAuthBearerToken();
        if ($expectedToken === '') {
            return false;
        }

        $authorizationHeader = trim((string) $request->header('Authorization', ''));
        if (!str_starts_with($authorizationHeader, 'Bearer ')) {
            return false;
        }

        return hash_equals('Bearer ' . $expectedToken, $authorizationHeader);
    }

    private function resolvePublicKey(): string
    {
        return trim((string) ($this->publicKey ?? config('communications.webhooks.sendgrid.public_key', '')));
    }

    private function resolveOAuthBearerToken(): string
    {
        return trim((string) ($this->oauthBearerToken ?? config('communications.webhooks.sendgrid.oauth_bearer_token', '')));
    }

    private function normalizePublicKey(string $publicKey): string
    {
        if ($publicKey === '') {
            return '';
        }

        if (str_contains($publicKey, 'BEGIN PUBLIC KEY')) {
            return $publicKey;
        }

        $normalized = preg_replace('/\s+/', '', $publicKey) ?? $publicKey;
        $chunks = trim(chunk_split($normalized, 64, "\n"));

        return "-----BEGIN PUBLIC KEY-----\n{$chunks}\n-----END PUBLIC KEY-----";
    }
}
