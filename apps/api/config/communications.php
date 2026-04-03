<?php

declare(strict_types=1);

return [
    'webhooks' => [
        'twilio' => [
            'enforce_signature' => (bool) env('TWILIO_WEBHOOK_ENFORCE_SIGNATURE', false),
            'base_url' => env('TWILIO_WEBHOOK_BASE_URL'),
        ],
        'sendgrid' => [
            'enforce_signature' => (bool) env('SENDGRID_WEBHOOK_ENFORCE_SIGNATURE', false),
            'public_key' => env('SENDGRID_WEBHOOK_PUBLIC_KEY'),
            'oauth_bearer_token' => env('SENDGRID_WEBHOOK_OAUTH_BEARER_TOKEN'),
        ],
    ],
];
