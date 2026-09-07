<?php

namespace App\Services\Website\Recipes;

use App\Models\WebsiteType;
use App\Services\Website\Deployment\WebsiteTypeDeploymentProfileService;
use InvalidArgumentException;
use RuntimeException;

/**
 * ESUBIZ_WEBSITE_TYPE_RECIPE_PROFILE_V2
 *
 * Transitional Website Type recipe resolver.
 *
 * The current config/website_recipes.php remains available while the
 * Admin-managed Website Type deployment-profile schema is being built.
 *
 * Once that database configuration becomes authoritative, this service
 * can resolve the same normalized profile shape from the database without
 * changing the Website Type composition/provisioning engine.
 */
class RecipeService
{
    public function __construct(
        protected WebsiteTypeDeploymentProfileService $deploymentProfiles
    ) {
    }
    /**
     * ESUBIZ_ADMIN_WEBSITE_TYPES_AUTHORITY_V4
     *
     * Admin-saved Website Types are the authoritative Website Type registry.
     *
     * No Website Type name/slug is hardcoded here. Any future Website Type
     * created by Admin automatically becomes discoverable by the provisioning
     * architecture without adding another PHP config key.
     */
    public function websiteTypes()
    {
        return WebsiteType::query()
            ->orderBy('name')
            ->get();
    }

    /**
     * Resolve one actual Admin-saved Website Type by ID or slug.
     */
    public function websiteType(WebsiteType|int|string $websiteType): WebsiteType
    {
        if ($websiteType instanceof WebsiteType) {
            return $websiteType;
        }

        if (
            is_int($websiteType)
            || (is_string($websiteType) && ctype_digit($websiteType))
        ) {
            $type = WebsiteType::query()->find((int) $websiteType);
        } else {
            $slug = trim((string) $websiteType);

            if ($slug === '') {
                throw new InvalidArgumentException(
                    'Website Type is required.'
                );
            }

            $type = WebsiteType::query()
                ->where('slug', $slug)
                ->first();
        }

        if (!$type) {
            throw new RuntimeException(
                "Admin-saved Website Type [{$websiteType}] was not found."
            );
        }

        return $type;
    }

    /**
     * Legacy composition fallback only.
     *
     * The config file is NOT the Website Type registry. It may temporarily
     * supply old composition defaults for an Admin-saved Website Type until
     * that Website Type receives its Admin-managed deployment profile.
     */
    public function get(string $websiteType): array
    {
        $type = $this->websiteType($websiteType);

        return config("website_recipes.{$type->slug}", []);
    }

    /**
     * Resolve the current recipe into the normalized Website Type
     * deployment-profile shape consumed by the composition engine.
     *
     * NOTE:
     * Legacy recipes currently contain product slugs only. They do not
     * yet contain package/staging paths. Those paths will be resolved by
     * the Website Type package/component resolver rather than invented here.
     */
    public function deploymentProfile(
        string $websiteType,
        string $deployment = 'saas',
        ?string $selectedTheme = null
    ): array {
        $websiteType = trim($websiteType);
        $deployment = strtolower(trim($deployment));

        if ($websiteType === '') {
            throw new InvalidArgumentException(
                'Website Type is required when resolving its deployment profile.'
            );
        }

        if (!in_array($deployment, ['saas', 'off_server'], true)) {
            throw new InvalidArgumentException(
                'Website Type deployment must be either [saas] or [off_server].'
            );
        }

        /*
         * ESUBIZ_ADMIN_WEBSITE_TYPE_PROFILE_AUTHORITY_V3
         *
         * Admin-managed deployment profiles are authoritative.
         *
         * The legacy config recipe is used only while a Website Type has
         * not yet received an Admin-managed profile for this deployment.
         */
        try {
            return $this->deploymentProfiles->resolve(
                $websiteType,
                $deployment,
                $selectedTheme
            );
        } catch (RuntimeException $e) {
            if (!str_contains(
                $e->getMessage(),
                'has no enabled'
            )) {
                throw $e;
            }
        }

        $recipe = $this->get($websiteType);

        $themes = $this->normalizeProducts(
            $recipe['themes'] ?? []
        );

        $modules = $this->normalizeProducts(
            $recipe['modules'] ?? []
        );

        $addons = $this->normalizeProducts(
            $recipe['addons'] ?? []
        );

        $bundles = $this->normalizeProducts(
            $recipe['bundles']
                ?? $recipe['addon_bundles']
                ?? []
        );

        $configuredDefault = trim((string) (
            $recipe['default_theme'] ?? ''
        ));

        $defaultTheme = $configuredDefault !== ''
            ? $configuredDefault
            : ($themes[0]['slug'] ?? null);

        $selectedTheme = $selectedTheme !== null
            ? trim($selectedTheme)
            : null;

        if ($selectedTheme === '') {
            $selectedTheme = null;
        }

        return [
            'website_type' => $websiteType,
            'deployment' => $deployment,

            'themes' => $themes,
            'default_theme' => $defaultTheme,
            'selected_theme' => $selectedTheme,

            'modules' => $modules,
            'addons' => $addons,
            'bundles' => $bundles,

            /*
             * Preserve recipe-level defaults for the later Website Type
             * configuration stage without making them installer products.
             */
            'pages' => array_values(
                is_array($recipe['pages'] ?? null)
                    ? $recipe['pages']
                    : []
            ),

            'configuration' => is_array($recipe['configuration'] ?? null)
                ? $recipe['configuration']
                : [],

            'source' => 'legacy_recipe',
        ];
    }

    /**
     * Normalize slug-based legacy configuration without pretending
     * that package paths/manifests have already been resolved.
     *
     * @return array<int, array{slug:string}>
     */
    protected function normalizeProducts(mixed $products): array
    {
        if (!is_array($products)) {
            return [];
        }

        $normalized = [];

        foreach ($products as $product) {
            if (is_string($product)) {
                $slug = trim($product);

                if ($slug !== '') {
                    $normalized[] = ['slug' => $slug];
                }

                continue;
            }

            if (!is_array($product)) {
                continue;
            }

            $slug = trim((string) (
                $product['slug']
                ?? $product['product_slug']
                ?? ''
            ));

            if ($slug === '') {
                continue;
            }

            $normalized[] = array_merge(
                $product,
                ['slug' => $slug]
            );
        }

        return $normalized;
    }
}
