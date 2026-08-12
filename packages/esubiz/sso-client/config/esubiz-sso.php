<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Esubiz SSO Client
    |--------------------------------------------------------------------------
    |
    | Every Esubiz application uses this same client configuration.
    | Only the application credentials and callback URL change.
    |
    */

    'enabled' => env('ESUBIZ_SSO_ENABLED', true),

    'routes_enabled' => env('ESUBIZ_SSO_CLIENT_ROUTES', false),

    'issuer' => env(
        'ESUBIZ_SSO_ISSUER',
        'https://esubiz.com'
    ),

    'client_id' => env('ESUBIZ_SSO_CLIENT_ID'),

    'client_secret' => env('ESUBIZ_SSO_CLIENT_SECRET'),

    'redirect_uri' => env('ESUBIZ_SSO_REDIRECT_URI'),

    'success_redirect' => env('ESUBIZ_SSO_SUCCESS_REDIRECT', '/'),

    /*
    |--------------------------------------------------------------------------
    | Optional Role-Based Success Redirects
    |--------------------------------------------------------------------------
    |
    | Applications may define destinations for authenticated user roles.
    | When no matching role exists, success_redirect remains the fallback.
    |
    */
    'success_redirects' => [],

    'logout_redirect' => env('ESUBIZ_SSO_LOGOUT_REDIRECT', '/'),

    'scopes' => array_values(
        array_filter(
            preg_split(
                '/\s+/',
                env('ESUBIZ_SSO_SCOPES', 'identity.read')
            )
        )
    ),

];