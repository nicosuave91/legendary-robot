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
    'voice' => [
        'bridge' => [
            'default_agent_number' => env('TWILIO_VOICE_AGENT_NUMBER'),
            'customer_intro_message' => env('TWILIO_VOICE_CUSTOMER_INTRO_MESSAGE', 'Please hold while we connect your call.'),
            'missing_agent_message' => env('TWILIO_VOICE_MISSING_AGENT_MESSAGE', 'We are unable to connect your call at this time.'),
        ],
    ],
    'attachments' => [
        'public_delivery' => [
            'required_scan_status' => env('COMMUNICATION_ATTACHMENT_PUBLIC_REQUIRED_SCAN_STATUS', 'clean'),
        ],
        'upload' => [
            'channels' => [
                'sms' => [
                    'max_bytes' => 20 * 1024 * 1024,
                    'allowed_mime_types' => ['image/jpeg','image/jpg','image/png','image/gif','application/pdf','text/plain'],
                ],
                'mms' => [
                    'max_bytes' => 20 * 1024 * 1024,
                    'allowed_mime_types' => ['image/jpeg','image/jpg','image/png','image/gif','application/pdf','text/plain'],
                ],
                'email' => [
                    'max_bytes' => 25 * 1024 * 1024,
                    'allowed_mime_types' => ['image/jpeg','image/jpg','image/png','image/gif','application/pdf','text/plain','application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
                ],
            ],
        ],
        'outbound' => [
            'required_scan_status' => env('COMMUNICATION_ATTACHMENT_OUTBOUND_REQUIRED_SCAN_STATUS', 'clean'),
            'providers' => [
                'twilio' => [
                    'max_bytes' => 20 * 1024 * 1024,
                    'allowed_mime_types' => ['image/jpeg','image/jpg','image/png','image/gif','application/pdf','text/plain'],
                ],
                'sendgrid' => [
                    'max_bytes' => 25 * 1024 * 1024,
                    'allowed_mime_types' => ['image/jpeg','image/jpg','image/png','image/gif','application/pdf','text/plain','application/msword','application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
                ],
            ],
        ],
    ],
];
