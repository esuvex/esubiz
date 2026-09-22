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

        $query =
            DB::table($table)
                ->where(
                    'id',
                    $id
                )
                ->where(
                    'is_active',
                    true
                );

        /*
         * Marketplace product registries do not all use
         * Laravel SoftDeletes.
         *
         * Apply the deleted_at constraint only when the
         * underlying product table actually provides it.
         *
         * This keeps the resolver generic for credit packages
         * and future pluggable Marketplace product types.
         */
        if (
            \Illuminate\Support\Facades\Schema::hasColumn(
                $table,
                'deleted_at'
            )
        ) {
            $query->whereNull(
                'deleted_at'
            );
        }

        return $query->first();
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

        /*
         * Generic credit packages are shared commercial products.
         *
         * The same package can be purchased from:
         *
         * - Esubiz SaaS user account
         * - SaaS website admin
         * - Esubiz developer/off-server account
         * - off-server website admin
         *
         * The checkout session's deployment_type + website_id
         * determine where fulfilment goes.
         */
        if (
            property_exists(
                $product,
                'credit_type'
            )
            && property_exists(
                $product,
                'credit_quantity'
            )
        ) {
            return true;
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
         * Credit packages have one central commercial price.
         *
         * SaaS and off-server entry points share the same package,
         * checkout and fulfilment experience.
         */
        if (
            property_exists(
                $product,
                'credit_type'
            )
            && property_exists(
                $product,
                'credit_quantity'
            )
            && property_exists(
                $product,
                'price'
            )
        ) {
            return (float) $product->price;
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

    /**
     * Central primary currency is the universal Marketplace fallback.
     */
    protected function primaryCurrency(): string
    {
        return strtoupper(
            (string) config('platform.currency.primary', 'NGN')
        );
    }

    /**
     * Resolve Marketplace billing duration.
     *
     * Returned structure:
     * [
     *     'interval' => 6,
     *     'period' => 'month',
     *     'label' => '6 months',
     * ]
     *
     * Off-server products are one-time purchases and therefore return null.
     */
    public function billing(object $product, string $deploymentType): ?array
    {
        if ($deploymentType !== 'saas') {
            return null;
        }

        if (property_exists($product, 'audience')) {
            $row = DB::table('currency_prices')
                ->where('priceable_id', $product->id)
                ->where('priceable_type', 'catalog_product')
                ->where('scope_type', 'saas')
                ->where('is_active', true)
                ->whereNull('deleted_at')
                ->orderByDesc('id')
                ->first([
                    'billing_period',
                    'conditions',
                ]);

            if (!$row) {
                return null;
            }

            $periodMap = [
                'daily' => 'day',
                'weekly' => 'week',
                'monthly' => 'month',
                'quarterly' => 'month',
                'yearly' => 'year',
            ];

            $period = $periodMap[$row->billing_period ?? ''] ?? null;

            if (!$period) {
                return null;
            }

            $conditions = $row->conditions;

            if (is_string($conditions)) {
                $conditions = json_decode($conditions, true) ?: [];
            }

            if (!is_array($conditions)) {
                $conditions = [];
            }

            $interval = max(
                1,
                (int) ($conditions['billing_interval'] ?? 1)
            );

            if (($row->billing_period ?? null) === 'quarterly') {
                $interval *= 3;
            }

            return [
                'interval' => $interval,
                'period' => $period,
                'label' => $interval === 1
                    ? $period
                    : $interval . ' ' . $period . 's',
            ];
        }

        $rawPeriod = property_exists($product, 'saas_billing_period')
            ? $product->saas_billing_period
            : null;

        $rawInterval = property_exists($product, 'saas_billing_interval')
            ? $product->saas_billing_interval
            : null;

        if (!$rawPeriod && !$rawInterval) {
            return null;
        }

        /*
         * Core registry products historically use:
         * saas_billing_period   = numeric quantity
         * saas_billing_interval = month/year/etc.
         */
        if (is_numeric($rawPeriod)) {
            $interval = max(1, (int) $rawPeriod);
            $period = strtolower(trim((string) $rawInterval));
        } else {
            $interval = is_numeric($rawInterval)
                ? max(1, (int) $rawInterval)
                : 1;

            $period = strtolower(trim((string) $rawPeriod));
        }

        $period = match ($period) {
            'daily', 'day', 'days' => 'day',
            'weekly', 'week', 'weeks' => 'week',
            'monthly', 'month', 'months' => 'month',
            'quarterly', 'quarter', 'quarters' => 'quarter',
            'yearly', 'annual', 'annually', 'year', 'years' => 'year',
            default => null,
        };

        if (!$period) {
            return null;
        }

        return [
            'interval' => $interval,
            'period' => $period,
            'label' => $interval === 1
                ? $period
                : $interval . ' ' . $period . 's',
        ];
    }

    public function currency(object $product, string $deploymentType): string
    {
        if (!in_array($deploymentType, ['saas', 'off_server'], true)) {
            return $this->primaryCurrency();
        }

        /*
         * Credit package currency is centrally configured.
         */
        if (
            property_exists(
                $product,
                'credit_type'
            )
            && property_exists(
                $product,
                'currency'
            )
        ) {
            return strtoupper(
                (string) (
                    $product->currency
                    ?: $this->primaryCurrency()
                )
            );
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

            return $this->primaryCurrency();
        }

        $field = $deploymentType === 'saas'
            ? 'saas_currency'
            : 'off_server_currency';

        return strtoupper($product->{$field} ?? $this->primaryCurrency());
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

    /**
     * Resolve canonical Admin-managed Marketplace categories
     * assigned to a catalog product.
     *
     * This deliberately reads the canonical Marketplace category
     * pivot instead of legacy product-specific category fields.
     */
    public function categories(
        object $product,
        ?string $productType = null
    ): array {
        $catalogProductId = null;

        if (isset($product->catalog_product_id)) {
            $catalogProductId = (int) $product->catalog_product_id;
        } elseif (
            isset($product->id)
            && (
                isset($product->product_type)
                || property_exists($product, 'audience')
            )
        ) {
            $catalogProductId = (int) $product->id;
        }

        if (!$catalogProductId) {
            return [];
        }

        $query = \DB::table('marketplace_categories as mc')
            ->join(
                'catalog_product_marketplace_category as cpmc',
                'cpmc.marketplace_category_id',
                '=',
                'mc.id'
            )
            ->where(
                'cpmc.catalog_product_id',
                $catalogProductId
            )
            ->where('mc.is_active', true);

        $rows = $query
            ->orderBy('mc.sort_order')
            ->orderBy('mc.name')
            ->get([
                'mc.id',
                'mc.name',
                'mc.slug',
                'mc.product_types',
            ]);

        return $rows
            ->filter(function ($category) use ($productType) {
                if (!$productType) {
                    return true;
                }

                $types = $category->product_types;

                if (is_string($types)) {
                    $decoded = json_decode($types, true);
                    $types = is_array($decoded) ? $decoded : [];
                }

                if (!is_array($types) || $types === []) {
                    return true;
                }

                $types = collect($types)
                    ->map(
                        fn ($type) =>
                            strtolower(trim((string) $type))
                    )
                    ->filter()
                    ->values()
                    ->all();

                return in_array(
                    strtolower(trim($productType)),
                    $types,
                    true
                );
            })
            ->map(
                fn ($category) => [
                    'id' => (int) $category->id,
                    'name' => (string) $category->name,
                    'slug' => (string) $category->slug,
                ]
            )
            ->values()
            ->all();
    }

}