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


    'coingecko' => [
        'demo_api_key' => env('COINGECKO_DEMO_API_KEY'),
    ],



    /*
    |--------------------------------------------------------------------------
    | Esubiz Central Site AI
    |--------------------------------------------------------------------------
    |
    | All tenant websites and off-server Core installations route
    | website AI requests through the central Esubiz AI platform.
    |
    */
    'esubiz_site_ai' => [
        'url' =>
            env(
                'ESUBIZ_SITE_AI_URL'
            ),

        'token' =>
            env(
                'ESUBIZ_SITE_AI_TOKEN'
            ),

        'timeout' =>
            env(
                'ESUBIZ_SITE_AI_TIMEOUT',
                120
            ),
    ],



    /*
    |--------------------------------------------------------------------------
    | Esubiz Managed Google Authentication
    |--------------------------------------------------------------------------
    |
    | ESUBIZ_MANAGED_GOOGLE_CONFIG_V2
    |
    | These credentials belong to the Central Esubiz-managed Google
    | application. They are never supplied to individual Core sites.
    |
    */

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    ],


    /*
     * ESUBIZ_IPINFO_LITE_V2
     *
     * Free country-level visitor geolocation.
     * Token remains server-side in .env.
     */
    'ipinfo_lite' => [
        'token' => env('IPINFO_LITE_TOKEN'),
    ],


];
