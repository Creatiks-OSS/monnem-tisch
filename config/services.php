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

    'google' => [
        'analytics_id' => env('GOOGLE_ANALYTICS_ID', env('ANALYTICS_GOOGLE_ANALYTICS_ID')),
        'tag_manager_id' => env('GOOGLE_TAG_MANAGER_ID', env('ANALYTICS_GOOGLE_TAG_MANAGER_ID')),
        'maps_api_key' => env('GOOGLE_MAPS_API_KEY'),
        'ads_id' => env('GOOGLE_ADS_ID'),
    ],

    'stripe' => [
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        'currency' => env('STRIPE_CURRENCY', 'eur'),
    ],

    'paypal' => [
        'client_id' => env('PAYPAL_CLIENT_ID'),
        'secret' => env('PAYPAL_SECRET'),
        'mode' => env('PAYPAL_MODE', 'sandbox'),
    ],

    'whatsapp' => [
        'number' => env('WHATSAPP_NUMBER'),
        'api_url' => env('WHATSAPP_API_URL'),
        'api_token' => env('WHATSAPP_API_TOKEN'),
    ],

    'analytics' => [
        'enabled' => filter_var(env('ANALYTICS_ENABLED', true), FILTER_VALIDATE_BOOLEAN),
        'google_analytics_id' => env('ANALYTICS_GOOGLE_ANALYTICS_ID', env('GOOGLE_ANALYTICS_ID', env('ANALYTICS_GA_ID'))),
        'google_tag_manager_id' => env('ANALYTICS_GOOGLE_TAG_MANAGER_ID', env('GOOGLE_TAG_MANAGER_ID')),
        'facebook_pixel_id' => env('ANALYTICS_FACEBOOK_PIXEL_ID', env('ANALYTICS_META_PIXEL_ID')),
    ],

    'openai' => [
        'key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
    ],

];
