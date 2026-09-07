<?php

namespace App\Services\Website\Deployment;

use App\Models\WebsiteType;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

/**
 * ESUBIZ_WEBSITE_TYPE_DEPLOYMENT_PROFILE_SERVICE_V1
 *
 * Authoritative resolver for Admin-managed Website Type composition.
 *
 * A Website Type may have independent:
 *
 * - saas
 * - off_server
 *
 * deployment profiles.
 *
 * Each profile may contain:
 *
 * - themes
 * - one default theme
 * - modules
 * - addons
 * - bundles
 *
 * No Website Type names are hardcoded here.
 */
class WebsiteTypeDeploymentProfileService
{
    /**
     * Resolve a Website Type deployment profile.
     */
    public function resolve(
        WebsiteType|int|string $websiteType,
        string $deployment,
        ?string $selectedTheme = null
    ): array {
        $type = $this->resolveWebsiteType($websiteType);
        $deployment = $this->normalizeDeployment($deployment);

        $profile = DB::table('website_type_deployment_profiles')
            ->where('website_type_id', $type->id)
            ->where('deployment_type', $deployment)
            ->where('is_enabled', true)
            ->first();

        if (!$profile) {
            throw new RuntimeException(
                "Website Type [{$type->slug}] has no enabled [{$deployment}] deployment profile."
            );
        }

        $rows = DB::table('website_type_deployment_components as c')
            ->join(
                'catalog_products as p',
                'p.id',
                '=',
                'c.catalog_product_id'
            )
            ->where(
                'c.website_type_deployment_profile_id',
                $profile->id
            )
            ->where('p.is_active', true)
            ->orderBy('c.sort_order')
            ->orderBy('c.id')
            ->select([
                'c.id as assignment_id',
                'c.catalog_product_id',
                'c.component_type',
                'c.is_required',
                'c.sort_order',
                'c.configuration as assignment_configuration',

                'p.name',
                'p.slug',
                'p.product_type',
            ])
            ->get();

        $components = [
            'themes' => [],
            'modules' => [],
            'addons' => [],
            'bundles' => [],
        ];

        foreach ($rows as $row) {
            $componentType = $this->normalizeComponentType(
                (string) $row->component_type
            );

            $catalogType = $this->normalizeCatalogProductType(
                (string) $row->product_type
            );

            if ($componentType !== $catalogType) {
                throw new RuntimeException(
                    "Website Type component [{$row->slug}] is assigned as [{$componentType}] but its catalog product type is [{$row->product_type}]."
                );
            }

            $key = match ($componentType) {
                'theme' => 'themes',
                'module' => 'modules',
                'addon' => 'addons',
                'bundle' => 'bundles',
            };

            $components[$key][] = [
                'assignment_id' => (int) $row->assignment_id,
                'catalog_product_id' => (int) $row->catalog_product_id,
                'product_type' => $componentType,
                'name' => $row->name,
                'slug' => $row->slug,
                'is_required' => (bool) $row->is_required,
                'sort_order' => (int) $row->sort_order,
                'configuration' => $this->decodeJson(
                    $row->assignment_configuration
                ),
            ];
        }

        $defaultTheme = $this->resolveDefaultTheme(
            $profile->default_theme_catalog_product_id,
            $components['themes']
        );

        $selectedTheme = $this->normalizeSelectedTheme(
            $selectedTheme
        );

        if ($selectedTheme !== null) {
            $allowed = collect($components['themes'])
                ->contains(
                    fn (array $theme) =>
                        $theme['slug'] === $selectedTheme
                );

            if (!$allowed) {
                throw new InvalidArgumentException(
                    "Theme [{$selectedTheme}] is not assigned to Website Type [{$type->slug}] for [{$deployment}] deployment."
                );
            }
        }

        return [
            'profile_id' => (int) $profile->id,

            'website_type_id' => (int) $type->id,
            'website_type' => $type->slug,

            'deployment' => $deployment,
            'is_enabled' => (bool) $profile->is_enabled,

            'themes' => $components['themes'],
            'default_theme' => $defaultTheme,
            'selected_theme' => $selectedTheme,

            'modules' => $components['modules'],
            'addons' => $components['addons'],
            'bundles' => $components['bundles'],

            'configuration' => $this->decodeJson(
                $profile->configuration
            ),

            'source' => 'admin_website_type_profile',
        ];
    }

    /**
     * Create or update one deployment profile.
     *
     * Component assignments are saved separately through syncComponents().
     */
    public function saveProfile(
        WebsiteType|int|string $websiteType,
        string $deployment,
        array $data = []
    ): int {
        $type = $this->resolveWebsiteType($websiteType);
        $deployment = $this->normalizeDeployment($deployment);

        $existing = DB::table('website_type_deployment_profiles')
            ->where('website_type_id', $type->id)
            ->where('deployment_type', $deployment)
            ->first();

        $values = [
            'is_enabled' => array_key_exists('is_enabled', $data)
                ? (bool) $data['is_enabled']
                : true,

            'configuration' => array_key_exists('configuration', $data)
                ? $this->encodeJson($data['configuration'])
                : ($existing->configuration ?? null),

            'updated_at' => now(),
        ];

        if ($existing) {
            DB::table('website_type_deployment_profiles')
                ->where('id', $existing->id)
                ->update($values);

            return (int) $existing->id;
        }

        $values['website_type_id'] = $type->id;
        $values['deployment_type'] = $deployment;
        $values['created_at'] = now();

        return (int) DB::table(
            'website_type_deployment_profiles'
        )->insertGetId($values);
    }

    /**
     * Replace component assignments for one profile.
     *
     * Expected:
     *
     * [
     *   ['catalog_product_id' => 1, 'component_type' => 'theme'],
     *   ['catalog_product_id' => 2, 'component_type' => 'module'],
     * ]
     */
    public function syncComponents(
        int $profileId,
        array $components
    ): void {
        DB::transaction(function () use ($profileId, $components) {
            $profile = DB::table('website_type_deployment_profiles')
                ->where('id', $profileId)
                ->first();

            if (!$profile) {
                throw new RuntimeException(
                    "Website Type deployment profile [{$profileId}] does not exist."
                );
            }

            $prepared = [];
            $seen = [];

            foreach ($components as $index => $component) {
                if (!is_array($component)) {
                    throw new InvalidArgumentException(
                        'Website Type component assignments must be arrays.'
                    );
                }

                $catalogProductId = (int) (
                    $component['catalog_product_id'] ?? 0
                );

                if ($catalogProductId <= 0) {
                    throw new InvalidArgumentException(
                        'A valid catalog_product_id is required for every Website Type component.'
                    );
                }

                if (isset($seen[$catalogProductId])) {
                    throw new InvalidArgumentException(
                        "Catalog product [{$catalogProductId}] is assigned more than once to the same Website Type deployment profile."
                    );
                }

                $seen[$catalogProductId] = true;

                $componentType = $this->normalizeComponentType(
                    (string) (
                        $component['component_type'] ?? ''
                    )
                );

                $product = DB::table('catalog_products')
                    ->where('id', $catalogProductId)
                    ->where('is_active', true)
                    ->first();

                if (!$product) {
                    throw new RuntimeException(
                        "Active catalog product [{$catalogProductId}] was not found."
                    );
                }

                $catalogType = $this->normalizeCatalogProductType(
                    (string) $product->product_type
                );

                if ($componentType !== $catalogType) {
                    throw new InvalidArgumentException(
                        "Catalog product [{$product->slug}] cannot be assigned as [{$componentType}]."
                    );
                }

                $prepared[] = [
                    'website_type_deployment_profile_id' => $profileId,
                    'catalog_product_id' => $catalogProductId,
                    'component_type' => $componentType,
                    'is_required' => array_key_exists(
                        'is_required',
                        $component
                    )
                        ? (bool) $component['is_required']
                        : true,
                    'sort_order' => (int) (
                        $component['sort_order'] ?? $index
                    ),
                    'configuration' => $this->encodeJson(
                        $component['configuration'] ?? []
                    ),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            DB::table('website_type_deployment_components')
                ->where(
                    'website_type_deployment_profile_id',
                    $profileId
                )
                ->delete();

            if ($prepared !== []) {
                DB::table(
                    'website_type_deployment_components'
                )->insert($prepared);
            }

            /*
             * Existing default theme is no longer valid if Admin removed it.
             */
            $defaultThemeId = $profile
                ->default_theme_catalog_product_id;

            if ($defaultThemeId !== null
                && !isset($seen[(int) $defaultThemeId])) {
                DB::table('website_type_deployment_profiles')
                    ->where('id', $profileId)
                    ->update([
                        'default_theme_catalog_product_id' => null,
                        'updated_at' => now(),
                    ]);
            }
        });
    }

    /**
     * Set exactly one assigned theme as the profile default.
     */
    public function setDefaultTheme(
        int $profileId,
        int $catalogProductId
    ): void {
        $assignment = DB::table(
            'website_type_deployment_components'
        )
            ->where(
                'website_type_deployment_profile_id',
                $profileId
            )
            ->where(
                'catalog_product_id',
                $catalogProductId
            )
            ->where('component_type', 'theme')
            ->first();

        if (!$assignment) {
            throw new InvalidArgumentException(
                'The default theme must already be assigned to this Website Type deployment profile.'
            );
        }

        DB::table('website_type_deployment_profiles')
            ->where('id', $profileId)
            ->update([
                'default_theme_catalog_product_id' =>
                    $catalogProductId,
                'updated_at' => now(),
            ]);
    }

    protected function resolveWebsiteType(
        WebsiteType|int|string $websiteType
    ): WebsiteType {
        if ($websiteType instanceof WebsiteType) {
            return $websiteType;
        }

        $query = WebsiteType::query();

        if (is_int($websiteType)
            || (is_string($websiteType)
                && ctype_digit($websiteType))) {
            $type = $query->find((int) $websiteType);
        } else {
            $slug = trim((string) $websiteType);

            if ($slug === '') {
                throw new InvalidArgumentException(
                    'Website Type is required.'
                );
            }

            $type = $query->where('slug', $slug)->first();
        }

        if (!$type) {
            throw new RuntimeException(
                "Website Type [{$websiteType}] was not found."
            );
        }

        return $type;
    }

    protected function resolveDefaultTheme(
        mixed $defaultThemeId,
        array $themes
    ): ?string {
        if ($defaultThemeId === null) {
            return null;
        }

        foreach ($themes as $theme) {
            if ((int) $theme['catalog_product_id']
                === (int) $defaultThemeId) {
                return $theme['slug'];
            }
        }

        throw new RuntimeException(
            'Website Type default theme is not assigned to its deployment profile.'
        );
    }

    protected function normalizeDeployment(
        string $deployment
    ): string {
        $deployment = strtolower(trim($deployment));

        if (!in_array(
            $deployment,
            ['saas', 'off_server'],
            true
        )) {
            throw new InvalidArgumentException(
                'Website Type deployment must be [saas] or [off_server].'
            );
        }

        return $deployment;
    }

    protected function normalizeComponentType(
        string $type
    ): string {
        $type = strtolower(trim($type));

        $type = match ($type) {
            'add-on', 'add_on' => 'addon',
            'addon_bundle',
            'add-on-bundle',
            'add_on_bundle' => 'bundle',
            default => $type,
        };

        if (!in_array(
            $type,
            ['theme', 'module', 'addon', 'bundle'],
            true
        )) {
            throw new InvalidArgumentException(
                "Unsupported Website Type component [{$type}]."
            );
        }

        return $type;
    }

    protected function normalizeCatalogProductType(
        string $type
    ): string {
        return $this->normalizeComponentType($type);
    }

    protected function normalizeSelectedTheme(
        ?string $theme
    ): ?string {
        if ($theme === null) {
            return null;
        }

        $theme = trim($theme);

        return $theme !== '' ? $theme : null;
    }

    protected function decodeJson(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (!is_string($value) || trim($value) === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }

    protected function encodeJson(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (!is_array($value)) {
            throw new InvalidArgumentException(
                'Website Type configuration must be an array.'
            );
        }

        return json_encode(
            $value,
            JSON_UNESCAPED_SLASHES
            | JSON_UNESCAPED_UNICODE
        );
    }
}
