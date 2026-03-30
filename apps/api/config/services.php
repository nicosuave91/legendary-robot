<?php

declare(strict_types=1);

return [
    'twilio' => [
        'sid' => env('TWILIO_ACCOUNT_SID'),
        'auth_token' => env('TWILIO_AUTH_TOKEN'),
        'messaging_service_sid' => env('TWILIO_MESSAGING_SERVICE_SID'),
        'from_number' => env('TWILIO_FROM_NUMBER'),
        'voice_from_number' => env('TWILIO_VOICE_FROM_NUMBER', env('TWILIO_FROM_NUMBER')),
    ],
    'sendgrid' => [
        'api_key' => env('SENDGRID_API_KEY'),
        'from_email' => env('SENDGRID_FROM_EMAIL'),
        'from_name' => env('SENDGRID_FROM_NAME', 'Snowball CRM'),
        'webhook_secret' => env('SENDGRID_WEBHOOK_SECRET'),
    ],
];
