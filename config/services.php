<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
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

    // Connection to OneClick sites (see SiteConnectionService). Shown read-only
    // on the API Integration > API Configuration page.
    'site_connection' => [
        // Seconds to wait for a site when the Control Center pulls from it.
        'timeout' => (int) env('SITE_CONNECTION_TIMEOUT', 10),
        // A site that reported before but stays silent this long is marked Offline.
        'stale_after_minutes' => (int) env('SITE_CONNECTION_STALE_AFTER_MINUTES', 15),
    ],

];
