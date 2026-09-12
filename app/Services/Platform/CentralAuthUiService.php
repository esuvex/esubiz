<?php

namespace App\Services\Platform;

class CentralAuthUiService
{
    /*
     * ESUBIZ_CENTRAL_AUTH_UI_V1
     *
     * Central Esubiz authentication presentation/configuration.
     *
     * Central auth deliberately uses CentralSiteSettingsService
     * rather than tenant/Core local settings.
     *
     * This gives Central login, registration, forgot-password
     * and reset-password pages the same configurable premium
     * architecture as Core while preserving configuration
     * ownership boundaries.
     */
    public function __construct(
        protected CentralSiteSettingsService $settings
    ) {
    }

    public function all(): array
    {
        return [
            'brand' => $this->brand(),
            'appearance' => $this->appearance(),
            'copy' => $this->copy(),
            'providers' => $this->providers(),
        ];
    }

    public function brand(): array
    {
        /*
         * ESUBIZ_CENTRAL_AUTH_BRANDING_V16
         *
         * Branding priority:
         *
         * 1. Central Auth Settings override.
         * 2. Existing Central Esubiz branding.
         * 3. Dynamic discovery from Central Site Settings.
         *
         * Core branding remains completely separate.
         */

        $authLogo = $this->setting(
            [
                'auth.logo_path',
                'auth.logo',
                'auth.branding.logo_path',
                'auth.branding.logo',
                'auth.settings.logo_path',
                'auth_settings.logo_path',
                'auth_logo_path',
                'auth_logo',
            ]
        );

        $centralLogo = $this->setting(
            [
                'branding.logo_path',
                'branding.logo',
                'branding.site_logo_path',
                'branding.header_logo_path',
                'platform.logo_path',
                'platform.logo',
                'platform.site_logo',
                'site.logo_path',
                'site.logo',
                'site_logo_path',
                'site_logo',
                'website_logo_path',
                'website_logo',
                'header_logo_path',
                'header_logo',
                'logo_path',
                'logo',
            ]
        );

        if (
            $centralLogo === null
            || $centralLogo === ''
        ) {
            $centralLogo =
                $this->discoverBrandAsset(
                    'logo'
                );
        }

        $authFavicon = $this->setting(
            [
                'auth.favicon_path',
                'auth.favicon',
                'auth.branding.favicon_path',
                'auth.branding.favicon',
                'auth.settings.favicon_path',
                'auth_settings.favicon_path',
                'auth_favicon_path',
                'auth_favicon',
            ]
        );

        $centralFavicon = $this->setting(
            [
                'branding.favicon_path',
                'branding.favicon',
                'platform.favicon_path',
                'platform.favicon',
                'platform.site_favicon',
                'site.favicon_path',
                'site.favicon',
                'site_favicon_path',
                'site_favicon',
                'website_favicon_path',
                'website_favicon',
                'favicon_path',
                'favicon',
            ]
        );

        if (
            $centralFavicon === null
            || $centralFavicon === ''
        ) {
            $centralFavicon =
                $this->discoverBrandAsset(
                    'favicon'
                );
        }

        return [
            'site_name' => (string) (
                $this->settings->get(
                    'platform.site_name'
                )
                ?: $this->settings->get(
                    'site.name'
                )
                ?: 'Esubiz'
            ),

            'auth_logo' =>
                $this->assetUrl(
                    $authLogo
                ),

            'logo' =>
                $this->assetUrl(
                    $centralLogo
                ),

            'auth_favicon' =>
                $this->assetUrl(
                    $authFavicon
                ),

            'favicon' =>
                $this->assetUrl(
                    $centralFavicon
                ),
        ];
    }

    public function appearance(): array
    {
        return [
            'primary_color' => $this->color(
                $this->setting(
                    [
                        'auth.primary_color',
                        'branding.primary_color',
                    ]
                ),
                '#0b1f3a'
            ),

            'accent_color' => $this->color(
                $this->setting(
                    [
                        'auth.accent_color',
                        'branding.accent_color',
                    ]
                ),
                '#c89b3c'
            ),

            'background_color' => $this->color(
                $this->setting(
                    [
                        'auth.background_color',
                    ]
                ),
                '#f5f7fb'
            ),

            'card_color' => $this->color(
                $this->setting(
                    [
                        'auth.card_color',
                    ]
                ),
                '#ffffff'
            ),

            'text_color' => $this->color(
                $this->setting(
                    [
                        'auth.text_color',
                    ]
                ),
                '#172033'
            ),

            'muted_text_color' => $this->color(
                $this->setting(
                    [
                        'auth.muted_text_color',
                    ]
                ),
                '#667085'
            ),

            'background_image' => $this->setting(
                [
                    'auth.background_image_path',
                    'auth.background_path',
                ]
            ),
        ];
    }

    public function copy(): array
    {
        return [
            'login_title' => $this->text(
                'auth.login_title',
                'Welcome back'
            ),

            'login_subtitle' => $this->text(
                'auth.login_subtitle',
                'Sign in to continue to your Esubiz account.'
            ),

            'register_title' => $this->text(
                'auth.register_title',
                'Create your account'
            ),

            'register_subtitle' => $this->text(
                'auth.register_subtitle',
                'Join Esubiz and start building your digital business.'
            ),

            'forgot_title' => $this->text(
                'auth.forgot_title',
                'Forgot your password?'
            ),

            'forgot_subtitle' => $this->text(
                'auth.forgot_subtitle',
                'Enter your email address and we will send you a password reset link.'
            ),

            'reset_title' => $this->text(
                'auth.reset_title',
                'Set a new password'
            ),

            'reset_subtitle' => $this->text(
                'auth.reset_subtitle',
                'Choose a secure password for your Esubiz account.'
            ),

            'login_button' => $this->text(
                'auth.login_button_text',
                'Sign In'
            ),

            'register_button' => $this->text(
                'auth.register_button_text',
                'Create Account'
            ),

            'forgot_button' => $this->text(
                'auth.forgot_button_text',
                'Send Reset Link'
            ),

            'reset_button' => $this->text(
                'auth.reset_button_text',
                'Reset Password'
            ),
        ];
    }

    public function providers(): array
    {
        /*
         * Provider visibility is controlled by Central Admin.
         *
         * Credentials/configuration remain separate from the UI
         * enable/disable flag.
         */
        $definitions = [
            'google' => [
                'label' => 'Continue with Google',
            ],

            'facebook' => [
                'label' => 'Continue with Facebook',
            ],

            'apple' => [
                'label' => 'Continue with Apple',
            ],

            'microsoft' => [
                'label' => 'Continue with Microsoft',
            ],
        ];

        $providers = [];

        foreach (
            $definitions as $key => $definition
        ) {
            $enabled = $this->boolean(
                $this->settings->get(
                    'auth.providers.'
                    . $key
                    . '.enabled'
                )
            );

            $providers[$key] = array_merge(
                $definition,
                [
                    'key' => $key,
                    'enabled' => $enabled,

                    'label' => $this->text(
                        'auth.providers.'
                        . $key
                        . '.label',
                        $definition['label']
                    ),
                ]
            );
        }

        return $providers;
    }

    public function enabledProviders(): array
    {
        return array_values(
            array_filter(
                $this->providers(),
                static fn (array $provider) =>
                    $provider['enabled']
            )
        );
    }

    protected function assetUrl(
        mixed $value
    ): ?string {
        /*
         * ESUBIZ_CENTRAL_AUTH_STORAGE_ASSET_V17
         *
         * Resolve Central branding according to where the
         * uploaded file actually lives.
         */
        if (
            !is_scalar($value)
            || trim((string) $value) === ''
        ) {
            return null;
        }

        $value = trim(
            (string) $value
        );

        if (
            preg_match(
                '#^(?:https?:)?//#i',
                $value
            )
            || str_starts_with(
                $value,
                'data:'
            )
        ) {
            return $value;
        }

        $value = str_replace(
            '\\',
            '/',
            $value
        );

        /*
         * Convert absolute DirectAdmin/public_html paths
         * back into an application-relative path.
         */
        if (
            str_contains(
                $value,
                '/public_html/'
            )
        ) {
            $publicHtmlTail = substr(
                $value,
                strpos(
                    $value,
                    '/public_html/'
                )
                + strlen('/public_html/')
            );

            /*
             * Strip application directory if present.
             */
            if (
                str_starts_with(
                    $publicHtmlTail,
                    'esubiz/'
                )
            ) {
                $publicHtmlTail = substr(
                    $publicHtmlTail,
                    strlen('esubiz/')
                );
            }

            $value = $publicHtmlTail;
        }

        /*
         * Normalize known Laravel filesystem prefixes.
         */
        $value = preg_replace(
            '#^/?storage/app/public/#i',
            '',
            $value
        );

        $value = preg_replace(
            '#^/?public/storage/#i',
            '',
            $value
        );

        $value = preg_replace(
            '#^/?storage/#i',
            '',
            $value
        );

        $value = preg_replace(
            '#^/?public/#i',
            '',
            $value
        );

        $relative = ltrim(
            $value,
            '/'
        );

        /*
         * File physically inside Laravel public/.
         */
        try {
            if (
                is_file(
                    public_path(
                        $relative
                    )
                )
            ) {
                return url(
                    '/'
                    . $relative
                );
            }
        } catch (\Throwable $exception) {
            // Continue to storage resolution.
        }

        /*
         * File physically inside storage/app/public/.
         *
         * This is the expected location for Central branding
         * uploads such as central-media/branding/*.
         */
        try {
            if (
                is_file(
                    storage_path(
                        'app/public/'
                        . $relative
                    )
                )
            ) {
                /*
                 * ESUBIZ_CENTRAL_MEDIA_URL_V18
                 *
                 * Central branding/media uses the dedicated
                 * public Laravel media route because /storage
                 * is denied by the host.
                 */
                if (
                    str_starts_with(
                        $relative,
                        'central-media/'
                    )
                ) {
                    return url(
                        '/'
                        . $relative
                    );
                }

                return url(
                    '/storage/'
                    . $relative
                );
            }
        } catch (\Throwable $exception) {
            // Continue to final fallback.
        }

        /*
         * Preserve a sensible root-relative fallback.
         */
        return url(
            '/'
            . $relative
        );
    }


    protected function discoverBrandAsset(
        string $type
    ): mixed {
        /*
         * ESUBIZ_CENTRAL_BRAND_DISCOVERY_V16
         *
         * Allows Central Auth to use the branding already
         * configured elsewhere in Central Site Settings even
         * while historic key names are being consolidated.
         */

        try {
            $settings =
                $this->settings->all();
        } catch (\Throwable $exception) {
            return null;
        }

        if (!is_array($settings)) {
            return null;
        }

        $type = strtolower(
            trim($type)
        );

        $candidates = [];

        foreach (
            $settings
            as $key => $value
        ) {
            if (
                !is_scalar($value)
                || trim(
                    (string) $value
                ) === ''
            ) {
                continue;
            }

            $keyString = strtolower(
                (string) $key
            );

            if (
                !str_contains(
                    $keyString,
                    $type
                )
            ) {
                continue;
            }

            /*
             * Do not accidentally use unrelated branding such
             * as footer/provider/email/payment logos.
             */
            $excluded = [
                'footer',
                'email',
                'mail',
                'provider',
                'google',
                'facebook',
                'instagram',
                'tiktok',
                'twitter',
                'payment',
                'gateway',
                'marketplace',
                'developer',
            ];

            $skip = false;

            foreach (
                $excluded as $word
            ) {
                if (
                    str_contains(
                        $keyString,
                        $word
                    )
                ) {
                    $skip = true;
                    break;
                }
            }

            if ($skip) {
                continue;
            }

            /*
             * Prefer obvious Central/site/platform branding.
             */
            $score = 0;

            foreach (
                [
                    'auth' => 100,
                    'branding' => 80,
                    'platform' => 70,
                    'site' => 60,
                    'website' => 50,
                    'header' => 40,
                ]
                as $word => $points
            ) {
                if (
                    str_contains(
                        $keyString,
                        $word
                    )
                ) {
                    $score += $points;
                }
            }

            $candidates[] = [
                'score' => $score,
                'value' => $value,
            ];
        }

        if ($candidates === []) {
            return null;
        }

        usort(
            $candidates,
            static fn (
                array $a,
                array $b
            ) =>
                $b['score']
                <=>
                $a['score']
        );

        return $candidates[0]['value']
            ?? null;
    }

    protected function setting(
        array $keys,
        mixed $fallback = null
    ): mixed {
        foreach ($keys as $key) {
            $value = $this->settings->get(
                $key
            );

            if (
                $value !== null
                && $value !== ''
            ) {
                return $value;
            }
        }

        return $fallback;
    }

    protected function text(
        string $key,
        string $fallback
    ): string {
        $value = trim(
            (string) $this->settings->get(
                $key,
                ''
            )
        );

        return $value !== ''
            ? $value
            : $fallback;
    }

    protected function color(
        mixed $value,
        string $fallback
    ): string {
        $value = trim(
            (string) $value
        );

        if (
            preg_match(
                '/^#[0-9a-fA-F]{6}$/',
                $value
            )
        ) {
            return strtolower($value);
        }

        return $fallback;
    }

    protected function boolean(
        mixed $value
    ): bool {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        return in_array(
            strtolower(
                trim((string) $value)
            ),
            [
                '1',
                'true',
                'yes',
                'on',
                'enabled',
            ],
            true
        );
    }
}
