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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | GIPHY API
    |--------------------------------------------------------------------------
    |
    | Credentials and connection settings for the external GIPHY provider that
    | the application integrates with. Get a free API key at
    | https://developers.giphy.com/dashboard/.
    |
    */

    'giphy' => [
        'key' => env('GIPHY_API_KEY'),
        'base_url' => env('GIPHY_BASE_URL', 'https://api.giphy.com/v1'),
        'timeout' => (int) env('GIPHY_TIMEOUT', 10),
        'rating' => env('GIPHY_RATING', 'g'),
        'lang' => env('GIPHY_LANG', 'en'),
    ],

];
