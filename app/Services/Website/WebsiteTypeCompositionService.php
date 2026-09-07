<?php

namespace App\Services\Website;

use App\Models\Website;
use App\Services\Core\Installer\CoreProductInstallerManager;
use InvalidArgumentException;
use RuntimeException;

/**
 * ESUBIZ_WEBSITE_TYPE_COMPOSITION_SERVICE_V1
 *
 * Applies an already-resolved Website Type deployment profile to
 * an initialized Esubiz Core installation.
 *
 * Website Type remains the commercial/wizard identity.
 *
 * This service does not decide which products belong to a Website Type.
 * The deployment-profile resolver / Admin configuration owns that.
 *
 * It only applies the normalized composition it receives:
 *
 * - Theme
 * - Modules
 * - Add-ons
 * - Add-on Bundles
 *
 * Supported deployment profiles:
 *
 * - saas
 * - off_server
 *
 * The same composition engine can therefore be reused by:
 *
 * - SaaS tenant provisioning
 * - Off-server first-run installation
 *
 * Initial Website Type deployment is NOT treated as a normal
 * Marketplace purchase/deployment job.
 */
class WebsiteTypeCompositionService
{
    public function __construct(
        protected CoreProductInstallerManager $installer
    ) {
    }

    /**
     * Apply a normalized Website Type composition to Core.
     *
     * Expected normalized profile shape:
     *
     * [
     *     'website_type' => 'ecommerce',
     *     'deployment' => 'saas',
     *
     *     'themes' => [
     *         [
     *             'slug' => 'theme-slug',
     *             'version' => '1.0.0',
     *             'extracted_path' => '/path/to/staged/theme',
     *             'manifest' => [...],
     *             'context' => [...],
     *         ],
     *     ],
     *
     *     'default_theme' => 'theme-slug',
     *     'selected_theme' => 'theme-slug',
     *
     *     'modules' => [...],
     *     'addons' => [...],
     *     'bundles' => [...],
     * ]
     */
    public function apply(
        Website $website,
        array $profile,
        array $context = []
    ): array {
        $deployment = $this->normalizeDeployment(
            $profile['deployment'] ?? ($context['deployment'] ?? 'saas')
        );

        $websiteType = trim((string) (
            $profile['website_type']
            ?? $website->type
            ?? ''
        ));

        if ($websiteType === '') {
            throw new InvalidArgumentException(
                'Website Type is required before applying its composition.'
            );
        }

        $baseContext = array_merge($context, [
            'website_id' => $website->id,
            'website_type' => $websiteType,
            'deployment' => $deployment,
            'initial_website_type_provisioning' => true,
        ]);

        $installed = [
            'website_type' => $websiteType,
            'deployment' => $deployment,
            'modules' => [],
            'addons' => [],
            'bundles' => [],
            'theme' => null,
        ];

        /*
         * Install Website Type functionality before the visual theme.
         *
         * This ensures modules/add-ons/bundles are available before
         * the final selected/default theme is enabled.
         */
        foreach ([
            'modules' => 'module',
            'addons' => 'addon',
            'bundles' => 'bundle',
        ] as $profileKey => $productType) {
            foreach ($this->items($profile, $profileKey) as $item) {
                $result = $this->installComponent(
                    $productType,
                    $item,
                    $baseContext
                );

                $installed[$profileKey][] = $result;
            }
        }

        /*
         * A Website Type may expose multiple compatible themes,
         * but only one theme becomes active.
         *
         * Priority:
         * 1. selected_theme
         * 2. default_theme
         */
        $themeSlug = trim((string) (
            $profile['selected_theme']
            ?? $profile['default_theme']
            ?? ''
        ));

        if ($themeSlug !== '') {
            $theme = $this->findTheme(
                $this->items($profile, 'themes'),
                $themeSlug
            );

            if ($theme === null) {
                throw new RuntimeException(
                    "Website Type theme [{$themeSlug}] is not present in the resolved deployment profile."
                );
            }

            $installed['theme'] = $this->installComponent(
                'theme',
                $theme,
                $baseContext
            );
        }

        return $installed;
    }

    /**
     * Install and enable one Website Type component.
     */
    protected function installComponent(
        string $productType,
        array $item,
        array $baseContext
    ): array {
        $slug = trim((string) (
            $item['slug']
            ?? $item['product_slug']
            ?? ''
        ));

        if ($slug === '') {
            throw new InvalidArgumentException(
                ucfirst($productType) . ' slug is required in Website Type composition.'
            );
        }

        $extractedPath = trim((string) (
            $item['extracted_path']
            ?? $item['staging_path']
            ?? ''
        ));

        if ($extractedPath === '') {
            throw new RuntimeException(
                "Website Type {$productType} [{$slug}] has no prepared package staging path."
            );
        }

        $manifest = is_array($item['manifest'] ?? null)
            ? $item['manifest']
            : [];

        $manifest = array_merge([
            'product_type' => $productType,
            'slug' => $slug,
        ], $manifest);

        if (!empty($item['version']) && empty($manifest['version'])) {
            $manifest['version'] = (string) $item['version'];
        }

        $componentContext = array_merge(
            $baseContext,
            is_array($item['context'] ?? null)
                ? $item['context']
                : [],
            [
                'website_type_component' => true,
                'website_type_component_type' => $productType,
                'website_type_component_slug' => $slug,
            ]
        );

        $installation = $this->installer->installAndEnable(
            $productType,
            $extractedPath,
            $manifest,
            $componentContext
        );

        return [
            'product_type' => $productType,
            'slug' => $slug,
            'version' => $manifest['version'] ?? null,
            'installation' => $installation,
        ];
    }

    /**
     * @return array<int, array>
     */
    protected function items(array $profile, string $key): array
    {
        $items = $profile[$key] ?? [];

        if ($items === null) {
            return [];
        }

        if (!is_array($items)) {
            throw new InvalidArgumentException(
                "Website Type profile [{$key}] must be an array."
            );
        }

        return array_values(array_filter(
            $items,
            static fn ($item) => is_array($item)
        ));
    }

    protected function findTheme(array $themes, string $slug): ?array
    {
        foreach ($themes as $theme) {
            $candidate = trim((string) (
                $theme['slug']
                ?? $theme['product_slug']
                ?? ''
            ));

            if ($candidate === $slug) {
                return $theme;
            }
        }

        return null;
    }

    protected function normalizeDeployment(string $deployment): string
    {
        $deployment = strtolower(trim($deployment));

        if (!in_array($deployment, ['saas', 'off_server'], true)) {
            throw new InvalidArgumentException(
                'Website Type deployment must be either [saas] or [off_server].'
            );
        }

        return $deployment;
    }
}
