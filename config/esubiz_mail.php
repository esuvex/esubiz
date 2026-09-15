<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Esubiz Managed Mail
    |--------------------------------------------------------------------------
    |
    | Central alone talks to DirectAdmin.
    | Tenant Core installations never receive DirectAdmin credentials.
    |
    */

    'managed_domain' =>
        env(
            'ESUBIZ_MANAGED_MAIL_DOMAIN',
            'esubiz.com'
        ),

    'directadmin' => [

        'enabled' =>
            (bool) env(
                'ESUBIZ_DIRECTADMIN_MAIL_ENABLED',
                false
            ),

        'base_url' =>
            env(
                'ESUBIZ_DIRECTADMIN_URL',
                'https://127.0.0.1:2222'
            ),

        /*
         * Prefer a restricted DirectAdmin login key rather than
         * the server/root password.
         */
        'username' =>
            env(
                'ESUBIZ_DIRECTADMIN_USERNAME'
            ),

        'login_key' =>
            env(
                'ESUBIZ_DIRECTADMIN_LOGIN_KEY'
            ),

        'timeout' =>
            (int) env(
                'ESUBIZ_DIRECTADMIN_TIMEOUT',
                15
            ),

        'verify_tls' =>
            filter_var(
                env(
                    'ESUBIZ_DIRECTADMIN_VERIFY_TLS',
                    true
                ),
                FILTER_VALIDATE_BOOL
            ),

        /*
         * No dedicated mailbox quota is assigned here.
         *
         * Mailbox usage participates in the Core Website's single
         * shared storage allowance instead.
         */
        'mailbox_quota_mb' =>
            (int) env(
                'ESUBIZ_DIRECTADMIN_MAILBOX_QUOTA_MB',
                0
            ),
    ],
];
