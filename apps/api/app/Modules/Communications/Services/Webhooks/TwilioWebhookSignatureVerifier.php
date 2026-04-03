<?php

declare(strict_types=1);

namespace App\Modules\Communications\Services\Webhooks;

use Illuminate\Http\Request;
use Twilio\Security\RequestValidator;

final class TwilioWebhookSignatureVerifier
{
    public function __construct(
        private readonly ?string $authToken = null,
        private readonly ?string $webhookBaseUrl = null,
    ) {
    }

    public function isConfigured(): bool
    {
        return $this->resolveAuthToken() !== '';
    }

    public function verify(Request $request): bool
    {
        $authToken = $this->resolveAuthToken();
        $signature = (string) $request->header('X-Twilio-Signature', '');

        if ($authToken === '' || $signature === '') {
            return false;
        }

        $validator = new RequestValidator($authToken);
        $parameters = [];

        foreach ($request->request->all() as $key => $value) {
            $parameters[(string) $key] = is_scalar($value) ? (string) $value : json_encode($value, JSON_THROW_ON_ERROR);
        }

        return $validator->validate($signature, $this->resolveRequestUrl($request), $parameters);
    }

    private function resolveAuthToken(): string
    {
        return trim((string) ($this->authToken ?? config('services.twilio.auth_token', '')));
    }

    private function resolveRequestUrl(Request $request): string
    {
        $baseUrl = trim((string) ($this->webhookBaseUrl ?? config('communications.webhooks.twilio.base_url', '')));

        if ($baseUrl === '') {
            return $request->fullUrl();
        }

        $url = rtrim($baseUrl, '/') . '/' . ltrim($request->path(), '/');
        $queryString = $request->getQueryString();

        return $queryString ? sprintf('%s?%s', $url, $queryString) : $url;
    }
}
