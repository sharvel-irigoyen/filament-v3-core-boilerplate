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

    'equifax' => [
        'url' => env('EQUIFAX_API_URL'),
        'key' => env('EQUIFAX_API_KEY'),
        'mock' => env('EQUIFAX_API_MOCK', false),
        'cache_ttl' => env('EQUIFAX_CACHE_TTL', 24),
        'id_sistema' => env('EQUIFAX_ID_SISTEMA'),
        'oauth_url' => env('EQUIFAX_OAUTH_URL'),
        'client_id' => env('EQUIFAX_CLIENT_ID'),
        'client_secret' => env('EQUIFAX_CLIENT_SECRET'),
        'scope' => env('EQUIFAX_SCOPE'),
    ],

];
