<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Esubiz Single Sign-On
    |--------------------------------------------------------------------------
    |
    | Central SSO configuration for Esubiz applications and subdomains.
    | Each client application receives its own SSO credentials.
    |
    */

    'enabled' => env('SSO_ENABLED', true),

    'issuer' => env('SSO_ISSUER', env('APP_URL')),

    'clients' => [

        'marketplace' => [
            'name' => 'Esubiz Marketplace',
            'url' => env('MARKETPLACE_SSO_URL', 'https://marketplace.esubiz.com'),
            'secret' => env('MARKETPLACE_SSO_SECRET'),
        ],

    ],

];
