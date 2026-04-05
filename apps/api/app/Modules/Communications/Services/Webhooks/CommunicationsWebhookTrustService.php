<?php

declare(strict_types=1);

namespace App\Modules\Communications\Services\Webhooks;

use Illuminate\Http\Request;
use App\Modules\Communications\DTOs\WebhookVerificationResultData;

final class CommunicationsWebhookTrustService
{
    public function __construct(
        private readonly TwilioWebhookSignatureVerifier $twilioWebhookSignatureVerifier,
        private readonly SendGridWebhookSignatureVerifier $sendGridWebhookSignatureVerifier,
        private readonly ?bool $twilioEnforceSignature = null,
        private readonly ?bool $sendGridEnforceSignature = null,
    ) {
    }

    public function verifyTwilio(Request $request): WebhookVerificationResultData
    {
        if (!$this->twilioWebhookSignatureVerifier->isConfigured()) {
            if ($this->resolveTwilioEnforcement()) {
                return new WebhookVerificationResultData(false, false, 'twilio_signature_required', 'Twilio webhook signature enforcement is enabled but no auth token is configured.');
            }

            return new WebhookVerificationResultData(true, false, 'unverified_allowed', 'Twilio webhook signature is not configured in this environment.');
        }

        if ($this->twilioWebhookSignatureVerifier->verify($request)) {
            return new WebhookVerificationResultData(true, true, 'twilio_signature_verified');
        }

        if ($this->resolveTwilioEnforcement()) {
            return new WebhookVerificationResultData(false, false, 'twilio_signature_invalid', 'Twilio webhook signature validation failed.');
        }

        return new WebhookVerificationResultData(true, false, 'unverified_allowed', 'Twilio webhook signature validation failed, but enforcement is disabled in this environment.');
    }

    public function verifySendGrid(Request $request): WebhookVerificationResultData
    {
        if (!$this->sendGridWebhookSignatureVerifier->hasVerificationConfiguration()) {
            if ($this->resolveSendGridEnforcement()) {
                return new WebhookVerificationResultData(false, false, 'sendgrid_verification_required', 'SendGrid webhook enforcement is enabled but neither a public key nor an OAuth bearer token is configured.');
            }

            return new WebhookVerificationResultData(true, false, 'unverified_allowed', 'SendGrid webhook verification is not configured in this environment.');
        }

        if ($this->sendGridWebhookSignatureVerifier->verify($request)) {
            return new WebhookVerificationResultData(true, true, 'sendgrid_verified');
        }

        if ($this->resolveSendGridEnforcement()) {
            return new WebhookVerificationResultData(false, false, 'sendgrid_verification_invalid', 'SendGrid webhook verification failed.');
        }

        return new WebhookVerificationResultData(true, false, 'unverified_allowed', 'SendGrid webhook verification failed, but enforcement is disabled in this environment.');
    }

    private function resolveTwilioEnforcement(): bool
    {
        return $this->twilioEnforceSignature ?? (bool) config('communications.webhooks.twilio.enforce_signature', false);
    }

    private function resolveSendGridEnforcement(): bool
    {
        return $this->sendGridEnforceSignature ?? (bool) config('communications.webhooks.sendgrid.enforce_signature', false);
    }
}
