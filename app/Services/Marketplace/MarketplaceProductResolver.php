<?php

namespace App\Services\Marketplace;

use Illuminate\Support\Facades\DB;

class MarketplaceProductResolver
{
    /**
     * Canonical marketplace product registry.
     *
     * All marketplace products resolve through this registry so the
     * unified checkout does not need product-specific checkout forms.
     *
     * Supported deployment models are evaluated from the resolved product:
     * - saas
     * - off_server
     * - both (when both availability flags are enabled)
     *
     * Existing aliases are intentionally retained for compatibility.
     */
    protected array $products = [
        // Core products
        'addon' => 'core_addons',
        'core_addon' => 'core_addons',

        'bundle' => 'core_addon_bundles',
        'core_bundle' => 'core_addon_bundles',

        'theme' => 'catalog_products',
        'module' => 'catalog_products',
        'website_type' => 'catalog_products',
        'credits' => 'catalog_products',
        'giftcard' => 'catalog_products',
        'gift_card' => 'catalog_products',

        // Shared commercial products
        'credit' => 'credit_packages',
        'credit_package' => 'credit_packages',

        'giftcard' => 'gift_cards',
        'gift_card' => 'gift_cards',
    ];

    public function register(string $type, string $table): void
    {
        $this->products[$type] = $table;
    }

    public function resolve(string $type, int $id): ?object
    {
        $table = $this->products[$type] ?? null;

        if (!$table) {
            return null;
        }

        return DB::table($table)
            ->where('id', $id)
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->first();
    }

    /**
     * Determine whether a product is available for the requested
     * deployment context.
     *
     * A product can support SaaS, off-server, or both simply by enabling
     * the corresponding availability flags on its canonical record.
     */
    public function available(object $product, string $deploymentType): bool
    {
        if (!in_array($deploymentType, ['saas', 'off_server'], true)) {
            return false;
        }

        /*
         * Core registry products use explicit availability flags.
         * Catalog products use audience:
         *   saas      = SaaS only
         *   developer = off-server only
         *   both      = shared
         */
        if (property_exists($product, 'audience')) {
            return match ($product->audience) {
                'saas' => $deploymentType === 'saas',
                'developer' => $deploymentType === 'off_server',
                'both' => true,
                default => false,
            };
        }

        $field = $deploymentType === 'saas'
            ? 'saas_available'
            : 'off_server_available';

        return property_exists($product, $field)
            ? (bool) $product->{$field}
            : true;
    }

    /**
     * Resolve the price for the selected deployment context.
     *
     * Shared products such as credits and gift cards can therefore use
     * exactly the same checkout pipeline while retaining separate pricing
     * where the product provides it.
     */
    public function price(object $product, string $deploymentType): ?float
    {
        if (!in_array($deploymentType, ['saas', 'off_server'], true)) {
            return null;
        }

        /*
         * Catalog products use currency_prices rather than the Core
         * saas_price/off_server_price columns.
         *
         * SaaS resolves against the SaaS pricing scope.
         * Developer/off-server resolves against the developer scope.
         */
        if (property_exists($product, 'audience')) {
            $scope = $deploymentType === 'saas'
                ? 'saas'
                : 'developer';

            $price = DB::table('currency_prices')
                ->where('priceable_id', $product->id)
                ->where('priceable_type', 'catalog_product')
                ->where('scope_type', $scope)
                ->where('is_active', true)
                ->whereNull('deleted_at')
                ->orderByDesc('id')
                ->value('price');

            return $price !== null ? (float) $price : null;
        }

        $field = $deploymentType === 'saas'
            ? 'saas_price'
            : 'off_server_price';

        return property_exists($product, $field)
            ? ($product->{$field} !== null ? (float) $product->{$field} : null)
            : 0.0;
    }

    public function currency(object $product, string $deploymentType): string
    {
        if (!in_array($deploymentType, ['saas', 'off_server'], true)) {
            return 'NGN';
        }

        if (property_exists($product, 'audience')) {
            $scope = $deploymentType === 'saas'
                ? 'saas'
                : 'developer';

            $currencyId = DB::table('currency_prices')
                ->where('priceable_id', $product->id)
                ->where('priceable_type', 'catalog_product')
                ->where('scope_type', $scope)
                ->where('is_active', true)
                ->whereNull('deleted_at')
                ->orderByDesc('id')
                ->value('currency_id');

            if ($currencyId) {
                $currency = DB::table('currencies')
                    ->where('id', $currencyId)
                    ->value('code');

                if ($currency) {
                    return strtoupper($currency);
                }
            }

            return 'NGN';
        }

        $field = $deploymentType === 'saas'
            ? 'saas_currency'
            : 'off_server_currency';

        return strtoupper($product->{$field} ?? 'NGN');
    }

    /**
     * Return the deployment contexts supported by a product.
     *
     * This gives the unified marketplace checkout a single capability
     * representation for future product types.
     */
    public function deploymentModes(object $product): array
    {
        $modes = [];

        if (!property_exists($product, 'saas_available') || $product->saas_available) {
            $modes[] = 'saas';
        }

        if (!property_exists($product, 'off_server_available') || $product->off_server_available) {
            $modes[] = 'off_server';
        }

        return $modes;
    }

    public function supportsBoth(object $product): bool
    {
        $modes = $this->deploymentModes($product);

        return in_array('saas', $modes, true)
            && in_array('off_server', $modes, true);
    }
}
