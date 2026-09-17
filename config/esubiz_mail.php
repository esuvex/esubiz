<?php

return [

    /*
     * ESUBIZ_MANAGED_MAIL_CONNECTION_V1
     *
     * Public connection parameters for mailboxes hosted on the
     * Esubiz server. Credentials remain in Central website_mailboxes.
     *
     * These values are intentionally independent of Laravel's
     * default application mailer.
     */
    'managed_mail' => [
        'smtp' => [
            'host' => env(
                'ESUBIZ_MANAGED_SMTP_HOST',
                'server-162-35-174-157.da.direct'
            ),
            'port' => (int) env(
                'ESUBIZ_MANAGED_SMTP_PORT',
                587
            ),
            'encryption' => env(
                'ESUBIZ_MANAGED_SMTP_ENCRYPTION',
                'tls'
            ),
            'verify_peer' => filter_var(
                env(
                    'ESUBIZ_MANAGED_SMTP_VERIFY_PEER',
                    true
                ),
                FILTER_VALIDATE_BOOL
            ),
        ],

        'imap' => [
            'host' => env(
                'ESUBIZ_MANAGED_IMAP_HOST',
                'server-162-35-174-157.da.direct'
            ),
            'port' => (int) env(
                'ESUBIZ_MANAGED_IMAP_PORT',
                993
            ),
            'encryption' => env(
                'ESUBIZ_MANAGED_IMAP_ENCRYPTION',
                'ssl'
            ),
            'verify_peer' => filter_var(
                env(
                    'ESUBIZ_MANAGED_IMAP_VERIFY_PEER',
                    false
                ),
                FILTER_VALIDATE_BOOL
            ),
        ],
    ],



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
