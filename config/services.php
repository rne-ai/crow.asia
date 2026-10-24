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

    'mayar' => [
        'api_key' => env('MAYAR_API_KEY', 'mock_key_crow_dev'),
        'webhook_token' => env('MAYAR_WEBHOOK_TOKEN', 'crow_webhook_secret_123'),
        'base_url' => env('MAYAR_BASE_URL', 'https://api.mayar.id/hl/v2'),
    ],

    'r2' => [
        'bucket' => env('CLOUDFLARE_R2_BUCKET', 'crow-asia-playbooks'),
        'endpoint' => env('CLOUDFLARE_R2_ENDPOINT'),
    ],

];
