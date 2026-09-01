<?php

namespace App\Services\Marketplace;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CoreAddonMarketplaceListingSyncService
{
    /*
     * ESUBIZ_CORE_ADDON_MARKETPLACE_LISTING_SYNC_V1
     *
     * Every Core Add-on must have a Marketplace listing so all
     * Marketplace and placement checkout paths resolve identically.
     */
    public function syncById(int $addonId): ?object
    {
        $addon = DB::table('core_addons')
            ->where('id', $addonId)
            ->whereNull('deleted_at')
            ->first();

        if (!$addon) {
            return null;
        }

        $existing = DB::table('marketplace_listings')
            ->where('product_type', 'core_addon')
            ->where('product_id', $addonId)
            ->whereNull('deleted_at')
            ->first();

        $price = null;

        if (
            (int) ($addon->saas_available ?? 0) === 1
            && $addon->saas_price !== null
        ) {
            $price = (float) $addon->saas_price;
        } elseif (
            (int) ($addon->off_server_available ?? 0) === 1
            && $addon->off_server_price !== null
        ) {
            $price = (float) $addon->off_server_price;
        }

        $currency = trim((string) (
            $addon->saas_currency
            ?? $addon->off_server_currency
            ?? 'NGN'
        ));

        if ($currency === '') {
            $currency = 'NGN';
        }

        $payload = [
            'product_type' => 'core_addon',
            'product_id' => $addonId,
            'title' => (string) $addon->name,
            'slug' => 'core-addon-' . $addonId,
            'summary' => $addon->description ?: null,
            'description' => $addon->description ?: null,
            'price' => $price ?? 0,
            'currency' => strtoupper($currency),
            'status' => (int) ($addon->is_active ?? 0) === 1
                ? 'published'
                : 'draft',
            'updated_at' => now(),
        ];

        if ($existing) {
            DB::table('marketplace_listings')
                ->where('id', $existing->id)
                ->update($payload);

            return DB::table('marketplace_listings')
                ->where('id', $existing->id)
                ->first();
        }

        $payload = array_merge([
            'vendor_id' => 1,
            'catalog_product_id' => null,
            'product_key' => null,
            'workspace_id' => null,
            'uuid' => (string) Str::uuid(),
            'commission_rate' => 0,
            'featured' => 0,
            'created_at' => now(),
            'deleted_at' => null,
        ], $payload);

        $id = DB::table('marketplace_listings')->insertGetId($payload);

        return DB::table('marketplace_listings')
            ->where('id', $id)
            ->first();
    }

    public function syncAll(): array
    {
        $ids = DB::table('core_addons')
            ->whereNull('deleted_at')
            ->pluck('id');

        $createdOrUpdated = 0;

        foreach ($ids as $id) {
            if ($this->syncById((int) $id)) {
                $createdOrUpdated++;
            }
        }

        return [
            'total' => $ids->count(),
            'synced' => $createdOrUpdated,
        ];
    }
}
