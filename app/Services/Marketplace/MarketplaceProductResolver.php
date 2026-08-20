<?php

namespace App\Services\Marketplace;

use Illuminate\Support\Facades\DB;

class MarketplaceProductResolver
{
    protected array $products = [];

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

    public function available(object $product, string $deploymentType): bool
    {
        $field = $deploymentType === 'saas'
            ? 'saas_available'
            : 'off_server_available';

        return property_exists($product, $field)
            ? (bool) $product->{$field}
            : true;
    }

    public function price(object $product, string $deploymentType): ?float
    {
        $field = $deploymentType === 'saas'
            ? 'saas_price'
            : 'off_server_price';

        return property_exists($product, $field)
            ? ($product->{$field} !== null ? (float) $product->{$field} : null)
            : 0.0;
    }

    public function currency(object $product, string $deploymentType): string
    {
        $field = $deploymentType === 'saas'
            ? 'saas_currency'
            : 'off_server_currency';

        return strtoupper($product->{$field} ?? 'NGN');
    }
}
