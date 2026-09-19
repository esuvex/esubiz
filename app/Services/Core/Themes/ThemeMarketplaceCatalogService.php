<?php

namespace App\Services\Core\Themes;

use App\Services\Marketplace\Themes\ThemeMarketplaceResolver;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class ThemeMarketplaceCatalogService
{
    /*
     * ESUBIZ_CORE_THEME_MARKETPLACE_CATALOG_SERVICE_V1
     *
     * Universal Core Theme Marketplace catalog adapter.
     *
     * SaaS:
     *   Reads the authoritative central Esubiz database through
     *   ThemeMarketplaceResolver.
     *
     * Off-server:
     *   Reads the authoritative Esubiz Theme Marketplace catalog
     *   through the central public API endpoint.
     *
     * The Theme package itself remains universal. Deployment only
     * changes commerce/catalog resolution.
     */

    public function __construct(
        protected ThemeMarketplaceResolver $resolver
    ) {
    }


    /**
     * Resolve Marketplace Themes for the current Core deployment.
     */
    public function themes(
        ?string $deployment = null,
        ?int $websiteId = null
    ): Collection {
        $deployment =
            $deployment
            ?: $this->deployment();

        if (
            $deployment
            === ThemeMarketplaceResolver::DEPLOYMENT_SAAS
        ) {
            return $this->withInstallationState(
                $this->saasThemes(),
                $websiteId
            );
        }

        if (
            $deployment
            === ThemeMarketplaceResolver::DEPLOYMENT_OFF_SERVER
        ) {
            return $this->withInstallationState(
                $this->offServerThemes(),
                $websiteId
            );
        }

        throw new RuntimeException(
            'Unsupported Core Theme Marketplace deployment: '
            . $deployment
        );
    }


    /**
     * SaaS Core shares the central Esubiz application and therefore
     * reads the authoritative Theme catalog locally.
     */
    protected function saasThemes(): Collection
    {
        return $this->resolver
            ->forDeployment(
                ThemeMarketplaceResolver::DEPLOYMENT_SAAS
            )
            ->map(
                fn ($theme) =>
                    $this->normalizeDatabaseTheme(
                        $theme,
                        ThemeMarketplaceResolver::DEPLOYMENT_SAAS
                    )
            )
            ->values();
    }


    /**
     * Off-server Core must never maintain its own commercial truth.
     * It asks Central Esubiz for currently eligible Themes.
     */
    protected function offServerThemes(): Collection
    {
        $url =
            rtrim(
                (string) config(
                    'services.esubiz.marketplace_url',
                    config(
                        'app.url'
                    )
                ),
                '/'
            )
            . '/marketplace/themes/catalog';

        $response = Http::timeout(15)
            ->acceptJson()
            ->get(
                $url,
                [
                    'deployment' =>
                        ThemeMarketplaceResolver::DEPLOYMENT_OFF_SERVER,
                ]
            );

        if (!$response->successful()) {
            throw new RuntimeException(
                'Unable to load the Esubiz Theme Marketplace catalog.'
            );
        }

        $payload = $response->json();

        if (
            !is_array($payload)
            || !($payload['ok'] ?? false)
            || !is_array($payload['themes'] ?? null)
        ) {
            throw new RuntimeException(
                'Invalid Esubiz Theme Marketplace catalog response.'
            );
        }

        return collect(
            $payload['themes']
        )
            ->map(
                fn ($theme) =>
                    $this->normalizeApiTheme(
                        $theme
                    )
            )
            ->values();
    }


    /**
     * Normalize SaaS/local DB records into the same public structure
     * returned to off-server Core.
     */
    protected function normalizeDatabaseTheme(
        object $theme,
        string $deployment
    ): array {
        $isSaas =
            $deployment
            === ThemeMarketplaceResolver::DEPLOYMENT_SAAS;

        return [
            'id' => (int) $theme->id,
            'uuid' => $theme->uuid,
            'name' => $theme->name,
            'slug' => $theme->slug,
            'version' => $theme->version,

            'publisher' => [
                'name' => $theme->publisher_name,
                'type' => $theme->publisher_type,
            ],

            'deployment' => $deployment,

            'price' => $isSaas
                ? $theme->saas_price
                : $theme->off_server_price,

            'currency' => $isSaas
                ? $theme->saas_currency
                : $theme->off_server_currency,

            'billing' => $isSaas
                ? [
                    'period' =>
                        $theme->saas_billing_period,
                    'interval' =>
                        $theme->saas_billing_interval,
                ]
                : null,

            'marketplace' => [
                'featured' =>
                    (bool) $theme->marketplace_featured,
                'category' =>
                    $theme->marketplace_category,
            ],

            /*
             * ESUBIZ_THEME_MARKETPLACE_PREVIEW_URL_V1
             *
             * Core consumes a safe Marketplace preview URL.
             * Internal package paths remain private.
             */
            'preview_url' =>
                route(
                    'marketplace.themes.preview',
                    [
                        'themePackageId' =>
                            $theme->id,
                    ]
                ),

            'package' => [
                'checksum_sha256' =>
                    $theme->checksum_sha256,
                'bytes' =>
                    (int) $theme->package_bytes,
            ],
        ];
    }


    /**
     * Normalize remote API items defensively.
     */
    protected function normalizeApiTheme(
        array $theme
    ): array {
        return [
            'id' => (int) ($theme['id'] ?? 0),
            'uuid' => $theme['uuid'] ?? null,
            'name' => $theme['name'] ?? null,
            'slug' => $theme['slug'] ?? null,
            'version' => $theme['version'] ?? null,

            'publisher' =>
                is_array($theme['publisher'] ?? null)
                    ? $theme['publisher']
                    : [],

            'deployment' =>
                $theme['deployment']
                ?? ThemeMarketplaceResolver::DEPLOYMENT_OFF_SERVER,

            'price' =>
                $theme['price'] ?? null,

            'currency' =>
                $theme['currency'] ?? 'NGN',

            'billing' =>
                $theme['billing'] ?? null,

            'marketplace' =>
                is_array($theme['marketplace'] ?? null)
                    ? $theme['marketplace']
                    : [],

            'preview_url' =>
                $theme['preview_url']
                ?? null,

            'package' =>
                is_array($theme['package'] ?? null)
                    ? $theme['package']
                    : [],
        ];
    }


    /**
     * Determine the current Core deployment mode.
     *
     * Central/SaaS remains the safe default.
     * Off-server builds can explicitly set:
     *
     * ESUBIZ_DEPLOYMENT=off_server
     */
    public function deployment(): string
    {
        $deployment = strtolower(
            trim(
                (string) config(
                    'services.esubiz.deployment',
                    env(
                        'ESUBIZ_DEPLOYMENT',
                        ThemeMarketplaceResolver::DEPLOYMENT_SAAS
                    )
                )
            )
        );

        return $deployment
            === ThemeMarketplaceResolver::DEPLOYMENT_OFF_SERVER
                ? ThemeMarketplaceResolver::DEPLOYMENT_OFF_SERVER
                : ThemeMarketplaceResolver::DEPLOYMENT_SAAS;
    }

    /*
     * ESUBIZ_CORE_THEME_INSTALL_STATE_V4
     *
     * Installed Theme state is owned by Core.
     *
     * This service does not scan directories and does not maintain
     * a separate Marketplace installation ledger.
     *
     * Every Theme is checked against the current Core website's
     * universal database-backed InstalledThemeRegistry.
     */
    protected function withInstallationState(
        Collection $themes,
        ?int $websiteId
    ): Collection {
        $registry = app(
            \App\Services\Core\Themes\InstalledThemeRegistry::class
        );

        return $themes
            ->map(
                function (array $theme) use ($registry, $websiteId) {
                    $installedHere =
                        $registry->isInstalled(
                            $theme['slug'] ?? null,
                            $theme['version'] ?? null,
                            $websiteId
                        );

                    $theme['installed'] =
                        $installedHere;

                    $theme['can_purchase'] =
                        !$installedHere;

                    $theme['purchase_status'] =
                        $installedHere
                            ? 'installed'
                            : 'available';

                    return $theme;
                }
            )
            ->values();
    }


}
