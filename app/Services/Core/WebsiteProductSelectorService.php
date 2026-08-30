<?php

namespace App\Services\Core;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class WebsiteProductSelectorService
{
    /*
     * ESUBIZ_DYNAMIC_WEBSITE_PRODUCT_SELECTOR_V1
     *
     * Normalized catalogue tree used by:
     * - Central Admin Website Edit
     * - User/Developer Website Edit
     * - later SaaS Wizard
     * - later Developer Build Wizard
     *
     * UI structure:
     *
     * category
     *   -> product
     *       -> purchasable/grantable option
     */
    public function tree($website = null): array
    {
        $tree = [];

        /*
         * ---------------------------------------------------------
         * CREDITS
         * ---------------------------------------------------------
         *
         * Credits have an additional product-family layer:
         *
         * Credits
         *   -> AI Credits
         *       -> configured credit packages
         */
        $creditPackages = DB::table('credit_packages as packages')
            ->join(
                'catalog_products as products',
                'products.id',
                '=',
                'packages.catalog_product_id'
            )
            ->where('packages.is_active', true)
            ->where('products.is_active', true)
            ->whereNull('products.deleted_at')
            ->select([
                'packages.id as option_id',
                'packages.catalog_product_id',
                'packages.credit_type',
                'packages.name',
                'packages.credit_quantity',
                'packages.price',
                'packages.currency',
                'packages.sort_order',
                'products.product_type',
            ])
            ->orderBy('packages.credit_type')
            ->orderBy('packages.sort_order')
            ->orderBy('packages.credit_quantity')
            ->get();

        foreach ($creditPackages as $package) {
            $family = $this->creditFamily(
                (string) $package->credit_type
            );

            $tree['credits']['label'] = 'Credits';

            $tree['credits']['products'][$family]['label'] =
                $this->creditLabel($family);

            $tree['credits']['products'][$family]['options'][] = [
                'id' => (int) $package->option_id,

                /*
                 * The grant side currently uses credit_package_id.
                 * Checkout/product fulfilment can still retain the
                 * canonical catalog_product_id.
                 */
                'catalog_product_id' =>
                    (int) $package->catalog_product_id,

                // ESUBIZ_SELECTOR_CHECKOUT_IDENTITY_V1
                'checkout_product_id' =>
                    (int) $package->option_id,

                'checkout_product_type' =>
                    'credit_package',

                'product_type' =>
                    (string) $package->product_type,

                'name' =>
                    (string) $package->name,

                'quantity_value' =>
                    (int) $package->credit_quantity,

                'price' =>
                    (float) $package->price,

                'currency' =>
                    strtoupper(
                        (string) ($package->currency ?: 'NGN')
                    ),
            ];
        }

        /*
         * ---------------------------------------------------------
         * ESUBIZ_WEBSITE_PRODUCT_SOURCE_NORMALIZATION_V2
         * ---------------------------------------------------------
         *
         * Website Edit has one normalized selector, but products
         * remain authoritative in their existing product systems:
         *
         * Credits  -> credit_packages
         * Add-ons  -> core_addons
         * Bundles  -> core_addon_bundles
         * Other    -> catalog_products
         *
         * Plans remain outside this selector.
         */

        $deploymentType =
            !empty($website?->subdomain)
                ? 'saas'
                : 'off_server';

        /*
         * ---------------------------------------------------------
         * CORE ADD-ONS
         * ---------------------------------------------------------
         *
         * Example:
         *
         * Add-ons
         *   -> CRM
         *       -> CRM Starter Addon
         *       -> CRM Pro Addon
         */

        $addonQuery = DB::table('core_addons')
            ->where('is_active', true)
            ->whereNull('deleted_at');

        if ($deploymentType === 'saas') {
            $addonQuery->where('saas_available', true);
        } else {
            $addonQuery->where('off_server_available', true);
        }

        $coreAddons = $addonQuery
            ->orderBy('parent_capability')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'parent_capability',
                'entitlement_type',
                'default_allocation',
                'allocation_unit',
                'saas_price',
                'saas_currency',
                'saas_billing_interval',
                'off_server_price',
                'off_server_currency',
            ]);

        foreach ($coreAddons as $addon) {
            $family =
                strtolower(
                    trim(
                        (string) (
                            $addon->parent_capability
                            ?: $addon->name
                        )
                    )
                );

            $family = $family ?: ('addon-' . $addon->id);

            $tree['addons']['label'] = 'Add-ons';

            $tree['addons']['products'][$family]['label'] =
                $this->capabilityLabel($family);

            $price =
                $deploymentType === 'saas'
                    ? (float) $addon->saas_price
                    : (float) $addon->off_server_price;

            $currency =
                $deploymentType === 'saas'
                    ? ($addon->saas_currency ?: 'NGN')
                    : ($addon->off_server_currency ?: 'NGN');

            $tree['addons']['products'][$family]['options'][] = [
                'id' => (int) $addon->id,
                'catalog_product_id' => (int) $addon->id,
                'product_type' => 'core_addon',
                'name' => (string) $addon->name,
                'quantity_value' => null,
                'price' => $price,
                'currency' => strtoupper((string) $currency),
                'fulfilment_type' =>
                    $deploymentType === 'saas'
                        ? 'subscription'
                        : 'license',
                'deployment_type' => $deploymentType,
                'allocation' =>
                    $addon->default_allocation !== null
                        ? (float) $addon->default_allocation
                        : null,
                'allocation_unit' =>
                    $addon->allocation_unit,
                'billing_interval' =>
                    $deploymentType === 'saas'
                        ? $addon->saas_billing_interval
                        : null,
            ];
        }

        /*
         * ---------------------------------------------------------
         * CORE ADD-ON BUNDLES
         * ---------------------------------------------------------
         */

        $bundleQuery = DB::table('core_addon_bundles')
            ->where('is_active', true)
            ->whereNull('deleted_at');

        if ($deploymentType === 'saas') {
            $bundleQuery->where('saas_available', true);
        } else {
            $bundleQuery->where('off_server_available', true);
        }

        $coreBundles = $bundleQuery
            ->orderBy('category')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'category',
                'unit_name',
                'saas_price',
                'saas_currency',
                'saas_billing_interval',
                'off_server_price',
                'off_server_currency',
            ]);

        foreach ($coreBundles as $bundle) {
            $key = 'bundle:' . (int) $bundle->id;

            $tree['bundles']['label'] = 'Bundles';

            $tree['bundles']['products'][$key] = [
                'label' => (string) $bundle->name,

                'options' => [
                    [
                        'id' => (int) $bundle->id,
                        'catalog_product_id' => (int) $bundle->id,
                        'product_type' => 'core_bundle',
                        'name' => (string) $bundle->name,
                        'quantity_value' => null,
                        'price' =>
                            $deploymentType === 'saas'
                                ? (float) $bundle->saas_price
                                : (float) $bundle->off_server_price,
                        'currency' =>
                            strtoupper(
                                (string) (
                                    $deploymentType === 'saas'
                                        ? ($bundle->saas_currency ?: 'NGN')
                                        : ($bundle->off_server_currency ?: 'NGN')
                                )
                            ),
                        'fulfilment_type' =>
                            $deploymentType === 'saas'
                                ? 'subscription'
                                : 'license',
                        'deployment_type' => $deploymentType,
                        'billing_interval' =>
                            $deploymentType === 'saas'
                                ? $bundle->saas_billing_interval
                                : null,
                    ],
                ],
            ];
        }

        /*
         * ---------------------------------------------------------
         * OTHER CATALOG PRODUCTS
         * ---------------------------------------------------------
         *
         * Credit catalogue rows are excluded because credit_packages
         * already provide their package hierarchy.
         */

        $creditCatalogProductIds = DB::table('credit_packages')
            ->where('is_active', true)
            ->whereNotNull('catalog_product_id')
            ->pluck('catalog_product_id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $catalogProducts = DB::table('catalog_products')
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->when(
                !empty($creditCatalogProductIds),
                fn ($query) =>
                    $query->whereNotIn(
                        'id',
                        $creditCatalogProductIds
                    )
            )
            ->where(
                'product_type',
                'not like',
                '%credit%'
            )
            ->orderBy('product_type')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'product_type',
                'fulfilment_type',
                'credit_quantity',
            ]);

        foreach ($catalogProducts as $product) {
            $category =
                $this->categoryForProductType(
                    (string) $product->product_type
                );

            /*
             * Plans / subscriptions / hybrid products are controlled
             * exclusively through the dedicated Plan card.
             */
            if ($category === 'subscriptions') {
                continue;
            }

            $tree[$category]['label'] =
                $this->categoryLabel($category);

            $key =
                (string) $product->product_type
                . ':'
                . (int) $product->id;

            $tree[$category]['products'][$key] = [
                'label' => (string) $product->name,

                'options' => [
                    [
                        'id' => (int) $product->id,
                        'catalog_product_id' =>
                            (int) $product->id,
                        'product_type' =>
                            (string) $product->product_type,
                        'name' =>
                            (string) $product->name,
                        'quantity_value' => null,
                        'price' => null,
                        'currency' => null,
                        'fulfilment_type' =>
                            $product->fulfilment_type,
                    ],
                ],
            ];
        }

        /*
         * ---------------------------------------------------------
         * ESUBIZ_WEBSITE_THEME_MODULE_SELECTOR_V2
         * ---------------------------------------------------------
         *
         * Product categories remain stable and plug-and-play:
         *
         * Credits
         * Add-ons
         * Bundles
         * Themes
         * Modules
         *
         * Themes and Modules remain visible even before products
         * are published. Their Product dropdown will simply remain
         * empty until an eligible product exists.
         */

        $tree['themes']['label'] = 'Themes';
        $tree['themes']['products'] =
            $tree['themes']['products'] ?? [];

        $tree['modules']['label'] = 'Modules';
        $tree['modules']['products'] =
            $tree['modules']['products'] ?? [];

        /*
         * ---------------------------------------------------------
         * THEMES
         * ---------------------------------------------------------
         *
         * Source: theme_packages
         *
         * As soon as an active marketplace-ready Theme Package is
         * created, it enters Website Edit automatically.
         */

        if (
            DB::getSchemaBuilder()
                ->hasTable('theme_packages')
        ) {
            $themeQuery =
                DB::table('theme_packages')
                    ->where('is_active', true)
                    ->whereNull('deleted_at')
                    ->where('marketplace_ready', true)
                    ->where('marketplace_enabled', true);

            if ($deploymentType === 'saas') {
                $themeQuery->where(
                    'saas_available',
                    true
                );
            } else {
                $themeQuery->where(
                    'off_server_available',
                    true
                );
            }

            $themePackages =
                $themeQuery
                    ->orderBy('name')
                    ->orderByDesc('is_current')
                    ->get([
                        'id',
                        'catalog_product_id',
                        'name',
                        'version',
                        'parent_package_id',
                        'saas_price',
                        'saas_currency',
                        'saas_billing_interval',
                        'off_server_price',
                        'off_server_currency',
                    ]);

            foreach ($themePackages as $theme) {

                /*
                 * parent_package_id gives us a natural family grouping
                 * when several package/version options belong to one
                 * Theme. Otherwise the Theme Package becomes its own
                 * product family.
                 */

                $familyId =
                    (int) (
                        $theme->parent_package_id
                        ?: $theme->id
                    );

                $familyKey =
                    'theme:' . $familyId;

                if (
                    !isset(
                        $tree['themes']['products'][$familyKey]
                    )
                ) {
                    $tree['themes']['products'][$familyKey] = [
                        'label' =>
                            (string) $theme->name,

                        'options' => [],
                    ];
                }

                $price =
                    $deploymentType === 'saas'
                        ? (float) $theme->saas_price
                        : (float) $theme->off_server_price;

                $currency =
                    $deploymentType === 'saas'
                        ? ($theme->saas_currency ?: 'NGN')
                        : ($theme->off_server_currency ?: 'NGN');

                $tree['themes']['products'][$familyKey]['options'][] = [
                    'id' =>
                        (int) $theme->id,

                    'catalog_product_id' =>
                        $theme->catalog_product_id !== null
                            ? (int) $theme->catalog_product_id
                            : null,

                    'product_type' =>
                        'theme',

                    'name' =>
                        trim(
                            (string) $theme->name
                            . (
                                $theme->version
                                    ? ' — v' . $theme->version
                                    : ''
                            )
                        ),

                    'quantity_value' =>
                        null,

                    'price' =>
                        $price,

                    'currency' =>
                        strtoupper(
                            (string) $currency
                        ),

                    'fulfilment_type' =>
                        $deploymentType === 'saas'
                            ? 'subscription'
                            : 'license',

                    'deployment_type' =>
                        $deploymentType,

                    'billing_interval' =>
                        $deploymentType === 'saas'
                            ? $theme->saas_billing_interval
                            : null,
                ];
            }
        }

        /*
         * ---------------------------------------------------------
         * MODULES
         * ---------------------------------------------------------
         *
         * Source:
         * - marketplace_listings commercial record
         * - modules module identity
         *
         * A future published Module becomes selectable automatically.
         */

        if (
            DB::getSchemaBuilder()
                ->hasTable('marketplace_listings')
        ) {
            $moduleListings =
                DB::table('marketplace_listings')
                    ->whereNull('deleted_at')
                    ->where('status', 'active')
                    ->whereIn(
                        'product_type',
                        [
                            'module',
                            'modules',
                        ]
                    )
                    ->orderBy('title')
                    ->get([
                        'id',
                        'catalog_product_id',
                        'product_id',
                        'product_type',
                        'title',
                        'price',
                        'currency',
                    ]);

            foreach ($moduleListings as $module) {

                /*
                 * product_id is the authoritative Module when present.
                 * The listing ID remains the fallback commercial ID.
                 */

                $productId =
                    (int) (
                        $module->product_id
                        ?: $module->id
                    );

                $familyKey =
                    'module:' . $productId;

                if (
                    !isset(
                        $tree['modules']['products'][$familyKey]
                    )
                ) {
                    $tree['modules']['products'][$familyKey] = [
                        'label' =>
                            (string) $module->title,

                        'options' => [],
                    ];
                }

                $tree['modules']['products'][$familyKey]['options'][] = [
                    'id' =>
                        $productId,

                    'catalog_product_id' =>
                        $module->catalog_product_id !== null
                            ? (int) $module->catalog_product_id
                            : null,

                    'marketplace_listing_id' =>
                        (int) $module->id,

                    'product_type' =>
                        'module',

                    'name' =>
                        (string) $module->title,

                    'quantity_value' =>
                        null,

                    'price' =>
                        (float) $module->price,

                    'currency' =>
                        strtoupper(
                            (string) (
                                $module->currency
                                ?: 'NGN'
                            )
                        ),

                    'fulfilment_type' =>
                        null,

                    'deployment_type' =>
                        $deploymentType,
                ];
            }
        }

        /*
         * Remove any accidental empty groups and normalize arrays
         * for safe JSON serialization into the shared Blade view.
         */
        foreach ($tree as $category => &$group) {
            $group['products'] =
                array_values(
                    $group['products'] ?? []
                );
        }

        unset($group);

        // ESUBIZ_WEBSITE_PRODUCT_SELECTOR_EXCLUDE_PLANS_V1
        // Plans are managed exclusively by the dedicated Plan card.
        unset($tree['subscriptions']);

        return $tree;
    }

    private function creditFamily(
        string $type
    ): string {
        return match (
            strtolower(trim($type))
        ) {
            'ai',
            'ai_credit',
            'ai_credits'
                => 'ai',

            'sms',
            'sms_credit',
            'sms_credits'
                => 'sms',

            'email',
            'email_credit',
            'email_credits'
                => 'email',

            'whatsapp',
            'whatsapp_credit',
            'whatsapp_credits'
                => 'whatsapp',

            default =>
                strtolower(trim($type)),
        };
    }

    private function creditLabel(
        string $family
    ): string {
        return match ($family) {
            'ai' => 'AI Credits',
            'sms' => 'SMS Credits',
            'email' => 'Email Credits',
            'whatsapp' => 'WhatsApp Credits',

            default =>
                ucwords(
                    str_replace(
                        ['_', '-'],
                        ' ',
                        $family
                    )
                ),
        };
    }

    private function capabilityLabel(
        string $capability
    ): string {
        $value = strtolower(trim($capability));

        return match ($value) {
            'crm' => 'CRM',
            'hr' => 'HR',
            'pos' => 'POS',
            'api' => 'API',
            'ai' => 'AI',
            'sms' => 'SMS',
            'email' => 'Email',
            'whatsapp' => 'WhatsApp',
            'qr' => 'QR Code',

            default =>
                ucwords(
                    str_replace(
                        ['_', '-'],
                        ' ',
                        $value
                    )
                ),
        };
    }

    private function categoryForProductType(
        string $type
    ): string {
        return match (
            strtolower(trim($type))
        ) {
            'addon',
            'core_addon'
                => 'addons',

            'bundle',
            'core_bundle'
                => 'bundles',

            'theme'
                => 'themes',

            'module'
                => 'modules',

            'subscription',
            'plan',
            'hybrid'
                => 'subscriptions',

            default =>
                strtolower(trim($type))
                ?: 'other',
        };
    }

    private function categoryLabel(
        string $category
    ): string {
        return match ($category) {
            'addons' => 'Add-ons',
            'bundles' => 'Bundles',
            'themes' => 'Themes',
            'modules' => 'Modules',
            'subscriptions' => 'Subscriptions',
            'credits' => 'Credit',

            default =>
                ucwords(
                    str_replace(
                        ['_', '-'],
                        ' ',
                        $category
                    )
                ),
        };
    }
}
