<?php

declare(strict_types=1);

$enforceByDefault = env('APP_ENV', 'production') === 'production';

return [
    'webhooks' => [
        'twilio' => [
            'enforce_signature' => env('TWILIO_WEBHOOK_ENFORCE_SIGNATURE', $enforceByDefault),
            'base_url' => env('TWILIO_WEBHOOK_BASE_URL'),
        ],
        'sendgrid' => [
            'enforce_signature' => env('SENDGRID_WEBHOOK_ENFORCE_SIGNATURE', $enforceByDefault),
            'public_key' => env('SENDGRID_WEBHOOK_PUBLIC_KEY'),
            'oauth_bearer_token' => env('SENDGRID_WEBHOOK_OAUTH_BEARER_TOKEN'),
        ],
    ],
    'inbound_email' => [
        'domain' => (string) env('COMMUNICATIONS_INBOUND_EMAIL_DOMAIN', ''),
        'local_part_prefix' => (string) env('COMMUNICATIONS_INBOUND_EMAIL_LOCAL_PART_PREFIX', 'reply'),
    ],
];
