<?php

namespace App\Services\Platform;

use App\Services\Media\CentralMediaService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class CentralSiteSettingsService
{
    public const WORKSPACE_ID = null;

    public const DEFAULTS = [
        /*
         * ESUBIZ_GLOBAL_SEO_SETTINGS_V10
         *
         * Esubiz serves Nigeria and the international market.
         * Central Admin can override every value below.
         */
        'seo.site_title' => 'Esubiz — Build, Manage & Grow Your Business Online',
        'seo.title_suffix' => 'Esubiz',
        'seo.meta_description' => 'Esubiz helps businesses in Nigeria and worldwide build websites, manage customers, automate operations, sell online and grow from one intelligent platform.',
        'seo.meta_keywords' => 'Esubiz, website builder, CRM software, ecommerce platform, business automation, business management software, business operating system, SaaS platform, online business tools, Nigeria business software, global business software',
        'seo.canonical_url' => 'https://esubiz.com',
        'seo.robots' => 'index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1',
        'seo.og_title' => 'Esubiz — Build, Manage & Grow Your Business',
        'seo.og_description' => 'Build websites, manage customers, automate operations, sell online and grow your business from one intelligent platform.',
        'seo.twitter_card' => 'summary_large_image',
        'seo.organization_name' => 'Esubiz',
        'seo.organization_description' => 'Esubiz is a Business Operating System for businesses in Nigeria and worldwide, combining website creation, CRM, ecommerce, automation and business management in one intelligent platform.',

            'system.maintenance_enabled' => '0',
            'system.maintenance_message' => 'Esubiz is temporarily undergoing scheduled maintenance. Please check back shortly.',
            'system.support_enabled' => '1',
            'system.registration_enabled' => '1',
            'system.default_dashboard' => 'user',
            'system.session_timeout_minutes' => '120',

        'platform.site_name' => 'Esubiz',

        'logo_path' => '',
        'favicon_path' => '',

        'platform.timezone' => 'Africa/Lagos',
        'platform.country' => 'NG',

        /*
         * ESUBIZ_GLOBAL_CURRENCY_SETTINGS_V2
         *
         * Central Esubiz is authoritative.
         *
         * These settings are intended for:
         * - Esubiz Central
         * - SaaS websites
         * - Off-server websites through Esubiz APIs
         * - Wallet
         * - Gift Card
         * - Payment gateways later
         *
         * Product prices remain stored in the primary currency.
         */
        'platform.currency.primary' => 'NGN',

        /*
         * JSON array of selected secondary currency codes.
         *
         * Example:
         * ["USD","GBP","EUR"]
         */
        'platform.currency.secondary' => '[]',

        /*
         * JSON object keyed by currency.
         *
         * Example:
         * {
         *   "USD": {
         *     "enabled": true,
         *     "markup_type": "percentage",
         *     "markup_value": 5
         *   }
         * }
         *
         * Conversion itself remains automatic through the existing
         * Frankfurter currency-rate service.
         */
        'platform.currency.secondary_settings' => '{}',

        'platform.language' => 'en',

        'platform.date_format' => 'd M Y',
        'platform.time_format' => 'H:i',
        'platform.week_start' => 'monday',

        'platform.currency_position' => 'before',
        'platform.number_format' => '1,234.56',

        'platform.business_email' => '',
        'platform.support_email' => '',
        'platform.phone' => '',
        'platform.address' => '',
        'platform.registration_number' => '',
    ];

    public function __construct(
        protected CentralMediaService $media
    ) {
    }

    public function get(
        string $key,
        mixed $default = null
    ): mixed {
        $value = DB::table('site_settings')
            ->whereNull('workspace_id')
            ->where('key', $key)
            ->value('value');

        if ($value !== null) {
            return $value;
        }

        return self::DEFAULTS[$key] ?? $default;
    }

    public function all(): array
    {
        $stored = DB::table('site_settings')
            ->whereNull('workspace_id')
            ->whereIn(
                'key',
                array_keys(self::DEFAULTS)
            )
            ->pluck('value', 'key')
            ->all();

        return array_replace(
            self::DEFAULTS,
            $stored
        );
    }

    public function set(
        string $key,
        mixed $value
    ): void {
        if (is_array($value)) {
            $value = json_encode(
                $value,
                JSON_UNESCAPED_SLASHES
            );
        }

        DB::table('site_settings')->updateOrInsert(
            [
                'workspace_id' => null,
                'key' => $key,
            ],
            [
                'value' => $value,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    public function setMany(
        array $settings
    ): void {
        DB::transaction(
            function () use ($settings) {
                foreach ($settings as $key => $value) {
                    $this->set(
                        $key,
                        $value
                    );
                }
            }
        );
    }

    public function primaryCurrency(): string
    {
        /*
         * ESUBIZ_CENTRAL_PRIMARY_CURRENCY_AUTHORITY_V2
         *
         * 1. Central Admin configured primary currency is authoritative.
         * 2. currencies.is_base is the database fallback/source.
         * 3. No currency code is hardcoded here.
         */
        $currency = strtoupper(
            trim(
                (string) $this->get(
                    'platform.currency.primary',
                    ''
                )
            )
        );

        if ($currency !== '') {
            return $currency;
        }

        $baseCurrency = \Illuminate\Support\Facades\DB::table(
            'currencies'
        )
            ->where('is_base', true)
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->value('code');

        $baseCurrency = strtoupper(
            trim((string) $baseCurrency)
        );

        if ($baseCurrency !== '') {
            return $baseCurrency;
        }

        throw new \RuntimeException(
            'Central primary currency has not been configured.'
        );
    }

    public function secondaryCurrencies(): array
    {
        $raw = $this->get(
            'platform.currency.secondary',
            '[]'
        );

        $decoded = json_decode(
            (string) $raw,
            true
        );

        if (!is_array($decoded)) {
            return [];
        }

        $primary = $this->primaryCurrency();

        return array_values(
            array_unique(
                array_filter(
                    array_map(
                        fn ($currency) =>
                            strtoupper(
                                trim(
                                    (string) $currency
                                )
                            ),
                        $decoded
                    ),
                    fn ($currency) =>
                        $currency !== ''
                        && $currency !== $primary
                )
            )
        );
    }

    public function secondaryCurrencySettings(): array
    {
        $raw = $this->get(
            'platform.currency.secondary_settings',
            '{}'
        );

        $decoded = json_decode(
            (string) $raw,
            true
        );

        if (!is_array($decoded)) {
            $decoded = [];
        }

        $result = [];

        foreach (
            $this->secondaryCurrencies()
            as $currency
        ) {
            $config = $decoded[$currency] ?? [];

            $markupType =
                (string) (
                    $config['markup_type']
                    ?? 'percentage'
                );

            if (
                !in_array(
                    $markupType,
                    ['percentage', 'fixed'],
                    true
                )
            ) {
                $markupType = 'percentage';
            }

            $result[$currency] = [
                'enabled' => (bool) (
                    $config['enabled']
                    ?? true
                ),

                'automatic_conversion' => true,

                'rate_provider' => 'frankfurter',

                'markup_type' => $markupType,

                'markup_value' => max(
                    0,
                    (float) (
                        $config['markup_value']
                        ?? 0
                    )
                ),
            ];
        }

        return $result;
    }

    public function configureSecondaryCurrencies(
        array $currencies,
        array $settings
    ): void {
        $primary = $this->primaryCurrency();

        $currencies = array_values(
            array_unique(
                array_filter(
                    array_map(
                        fn ($currency) =>
                            strtoupper(
                                trim(
                                    (string) $currency
                                )
                            ),
                        $currencies
                    ),
                    fn ($currency) =>
                        $currency !== ''
                        && $currency !== $primary
                )
            )
        );

        $normalizedSettings = [];

        foreach ($currencies as $currency) {
            $config = $settings[$currency] ?? [];

            $markupType =
                (string) (
                    $config['markup_type']
                    ?? 'percentage'
                );

            if (
                !in_array(
                    $markupType,
                    ['percentage', 'fixed'],
                    true
                )
            ) {
                $markupType = 'percentage';
            }

            $normalizedSettings[$currency] = [
                'enabled' => (bool) (
                    $config['enabled']
                    ?? true
                ),

                /*
                 * Live rate conversion is authoritative.
                 * No product-level converted prices are stored.
                 */
                'automatic_conversion' => true,

                'rate_provider' => 'frankfurter',

                'markup_type' => $markupType,

                'markup_value' => max(
                    0,
                    (float) (
                        $config['markup_value']
                        ?? 0
                    )
                ),
            ];
        }

        $this->setMany([
            'platform.currency.secondary' =>
                $currencies,

            'platform.currency.secondary_settings' =>
                $normalizedSettings,
        ]);
    }

    public function replaceLogo(
        UploadedFile $file
    ): string {
        $oldPath = (string) $this->get(
            'logo_path',
            ''
        );

        $newPath = $this->media->replace(
            $oldPath ?: null,
            $file,
            'branding',
            \App\Services\Media\EsubizImageOptimizer::PROFILE_LOGO
        );

        $this->set(
            'logo_path',
            $newPath
        );

        return $newPath;
    }

    public function replaceFavicon(
        UploadedFile $file
    ): string {
        $oldPath = (string) $this->get(
            'favicon_path',
            ''
        );

        $newPath = $this->media->replace(
            $oldPath ?: null,
            $file,
            'branding',
            \App\Services\Media\EsubizImageOptimizer::PROFILE_FAVICON
        );

        $this->set(
            'favicon_path',
            $newPath
        );

        return $newPath;
    }

    public function logoPath(): ?string
    {
        return $this->nullablePath(
            $this->get(
                'logo_path',
                ''
            )
        );
    }

    public function faviconPath(): ?string
    {
        return $this->nullablePath(
            $this->get(
                'favicon_path',
                ''
            )
        );
    }

    protected function nullablePath(
        mixed $value
    ): ?string {
        $value = trim(
            (string) $value
        );

        return $value !== ''
            ? $value
            : null;
    }

    /*
     * ESUBIZ_CENTRAL_SETTINGS_SOURCE_OF_TRUTH_V25
     *
     * Reusable Central defaults for current and future Esubiz
     * surfaces. These helpers do not alter Laravel/server timezone.
     */
    public function siteName(): string
    {
        return (string) (
            $this->get('platform.site_name')
            ?: 'Esubiz'
        );
    }

    public function timezone(): string
    {
        return (string) (
            $this->get('platform.timezone')
            ?: 'Africa/Lagos'
        );
    }

    public function country(): string
    {
        return strtoupper(
            (string) (
                $this->get('platform.country')
                ?: 'NG'
            )
        );
    }

    public function language(): string
    {
        return (string) (
            $this->get('platform.language')
            ?: 'en'
        );
    }

    public function dateFormat(): string
    {
        return (string) (
            $this->get('platform.date_format')
            ?: 'd M Y'
        );
    }

    public function timeFormat(): string
    {
        return (string) (
            $this->get('platform.time_format')
            ?: 'H:i'
        );
    }
    public function maintenanceEnabled(): bool
    {
        return filter_var(
            $this->get('system.maintenance_enabled') ?? false,
            FILTER_VALIDATE_BOOLEAN
        );
    }

    public function supportEnabled(): bool
    {
        return filter_var(
            $this->get('system.support_enabled') ?? true,
            FILTER_VALIDATE_BOOLEAN
        );
    }

    public function registrationEnabled(): bool
    {
        return filter_var(
            $this->get('system.registration_enabled') ?? true,
            FILTER_VALIDATE_BOOLEAN
        );
    }

}
