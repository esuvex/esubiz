<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Esubiz Core
    |--------------------------------------------------------------------------
    |
    | Core is mandatory for every Esubiz website.
    | Website Types, Themes, Modules and Addons extend this foundation.
    |
    */

    'name' => 'core',

    'version' => '1.0.0',

    'type' => 'core',

    /*
    |--------------------------------------------------------------------------
    | Core Capabilities
    |--------------------------------------------------------------------------
    */

    'capabilities' => [

        'cms' => [
            'pages' => [
                'enabled' => true,
                'limit' => null,
            ],

            'media' => [
                'enabled' => true,
                'limit' => null,
            ],

            'menus' => [
                'enabled' => true,
                'limit' => null,
            ],

            'page_builder' => [
                'enabled' => true,
                'level' => 'basic',
            ],

            'form_builder' => [
                'enabled' => true,
                'level' => 'basic',
            ],

            'widgets' => [
                'enabled' => true,
                'level' => 'basic',
            ],

            'default_landing_page' => [
                'enabled' => true,
                'limit' => 1,
            ],
        ],

        'email' => [
            'enabled' => true,
            'mailboxes' => 2,
        ],

        'crm' => [
            'enabled' => true,
            'limit_per_feature' => 10,
        ],

        'hr' => [
            'enabled' => true,
            'limit_per_feature' => 5,
        ],

        'payments' => [

            'enabled' => true,

            'offline' => [
                'enabled' => true,
                'limit' => null,
            ],

            'online' => [
                'enabled' => true,
                'limit' => 2,

                'gateways' => [
                    'paystack',
                    'paypal',
                ],
            ],
        ],

        'live_chat' => [
            'enabled' => true,
            'level' => 'basic',
        ],

        'whatsapp' => [
            'enabled' => true,
            'limit_per_feature' => 1,
        ],

        'pos' => [
            'enabled' => true,
            'limit_per_feature' => 5,
            'offline_mode' => true,
        ],

        'security' => [
            'enabled' => true,
            'limit' => 1,
        ],

        'panorama_360' => [
            'enabled' => true,
            'level' => 'basic',
        ],

        'qr_code' => [
            'enabled' => true,
            'limit' => 5,
        ],

        'storage' => [
            'enabled' => true,
            'limit' => 1,
            'unit' => 'GB',
        ],

        'bandwidth' => [
            'enabled' => true,
            'limit' => 40,
            'unit' => 'GB',
        ],

        'referrals' => [
            'enabled' => true,
            'limit' => null,
        ],

        'sms' => [
            'enabled' => true,
            'billing' => 'credits',
            'credit_source' => 'owner',
        ],

        'resource_monitor' => [
            'enabled' => true,
            'tracks' => [
                'core',
                'addons',
            ],
        ],

        'marketing' => [
            'enabled' => true,

            'provider' => 'esubiz',

            'social_networks' => [
                'facebook',
                'instagram',
                'google',
                'twitter',
                'tiktok',
            ],
        ],

        'ai' => [
            'enabled' => true,

            'billing' => 'credits',

            'credit_source' => 'owner',

            'functions' => [
                'page_content',
                'live_chat',
                'themes',
                'automation',
                'site_functions',
            ],
        ],

        'branches' => [
            'enabled' => true,
            'limit' => null,
        ],

        'financial_tracker' => [
            'enabled' => true,
            'scope' => 'esubiz',
            'tracks' => [
                'plan_income',
                'website_subscription_income',
                'payments',
                'revenue',
                'transactions',
            ],
        ],

        'license_tracker' => [
            'enabled' => true,
            'scope' => 'website',
            'tracks' => [
                'core',
                'website_type',
                'theme',
                'modules',
                'addons',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Core Resources
    |--------------------------------------------------------------------------
    */

    'resources' => [

        'storage' => [
            'limit' => 1,
            'unit' => 'GB',
        ],

        'bandwidth' => [
            'limit' => 40,
            'unit' => 'GB',
        ],

        'email.mailboxes' => [
            'limit' => 2,
        ],

        'crm.per_feature' => [
            'limit' => 10,
        ],

        'hr.per_feature' => [
            'limit' => 5,
        ],

        'whatsapp.per_feature' => [
            'limit' => 1,
        ],

        'pos.per_feature' => [
            'limit' => 5,
        ],

        'security' => [
            'limit' => 1,
        ],

        'qr_code' => [
            'limit' => 5,
        ],

        'online_payment_gateways' => [
            'limit' => 2,
        ],

        'default_landing_page' => [
            'limit' => 1,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Unlimited Core Resources
    |--------------------------------------------------------------------------
    */

    'unlimited' => [
        'pages',
        'media',
        'offline_payment_gateways',
        'menus',
        'referrals',
        'branches',
    ],

    /*
    |--------------------------------------------------------------------------
    | Core Defaults
    |--------------------------------------------------------------------------
    */

    'defaults' => [

        'theme' => 'core-default',

        'landing_page' => 'default',

        'enabled_modules' => [],

        'enabled_addons' => [],

        'settings' => [],
    ],

];
