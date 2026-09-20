<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // License offline-signature signing. Read via config(), never env() in app code.
    'license' => [
        'signing_private_key' => env('LICENSE_SIGNING_PRIVATE_KEY'),
        'signing_public_key' => env('LICENSE_SIGNING_PUBLIC_KEY'),
        'signing_public_keys' => env('LICENSE_SIGNING_PUBLIC_KEYS') ? json_decode(env('LICENSE_SIGNING_PUBLIC_KEYS'), true) : [],
        'signing_key_id' => env('LICENSE_SIGNING_KEY_ID', 'corevisys-key-1'),
        'rotation_overlap_days' => env('LICENSE_ROTATION_OVERLAP_DAYS', 30),
        'signing_revoked_key_ids' => env('LICENSE_SIGNING_REVOKED_KEY_IDS') ? array_filter(array_map('trim', explode(',', env('LICENSE_SIGNING_REVOKED_KEY_IDS')))) : [],
        'fingerprint_enforcement_deadline' => env('FINGERPRINT_ENFORCEMENT_DEADLINE', now()->addDays(90)->format('Y-m-d')),
        'fingerprint_grace_mode' => env('FINGERPRINT_GRACE_MODE', true),
    ],

    'stripe' => [
        'secret' => env('STRIPE_SECRET_KEY'),
        'publishable' => env('STRIPE_PUBLISHABLE_KEY'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
    ],

    'bkash' => [
        'app_key' => env('BKASH_APP_KEY'),
        'app_secret' => env('BKASH_APP_SECRET'),
        'username' => env('BKASH_USERNAME'),
        'password' => env('BKASH_PASSWORD'),
    ],

];
