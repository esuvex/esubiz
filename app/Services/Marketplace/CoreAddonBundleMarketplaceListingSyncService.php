<?php

namespace App\Services\Marketplace;

use App\Services\Platform\CentralSiteSettingsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CoreAddonBundleMarketplaceListingSyncService
{
    /*
     * ESUBIZ_CORE_ADDON_BUNDLE_MARKETPLACE_LISTING_SYNC_V1
     *
     * Gives every Core Add-on Bundle the same canonical Marketplace
     * commercial identity used by individual Core Add-ons.
     */
    public function syncById(int $bundleId): ?object
    {
        $bundle = DB::table('core_addon_bundles')
            ->where('id', $bundleId)
            ->whereNull('deleted_at')
            ->first();

        if (!$bundle) {
            return null;
        }

        $existing = DB::table('marketplace_listings')
            ->where('product_type', 'core_bundle')
            ->where('product_id', $bundleId)
            ->whereNull('deleted_at')
            ->first();

        $price = null;

        if (
            (int) ($bundle->saas_available ?? 0) === 1
            && $bundle->saas_price !== null
        ) {
            $price = (float) $bundle->saas_price;
        } elseif (
            (int) ($bundle->off_server_available ?? 0) === 1
            && $bundle->off_server_price !== null
        ) {
            $price = (float) $bundle->off_server_price;
        }

        /*
         * Product currency follows the Central system currency.
         * No Bundle-level currency authority is introduced here.
         */
        $centralSettings = app(CentralSiteSettingsService::class);

        $currency = strtoupper(
            trim(
                (string) $centralSettings->get(
                    'platform.currency.primary'
                )
            )
        );

        if ($currency === '') {
            throw new \RuntimeException(
                'Esubiz system default currency is not configured.'
            );
        }

        $payload = [
            'product_type' => 'core_bundle',
            'product_id' => $bundleId,
            'title' => (string) $bundle->name,
            'slug' => 'core-bundle-' . $bundleId,
            'summary' => $bundle->description ?: null,
            'description' => $bundle->description ?: null,
            'price' => $price ?? 0,
            'currency' => $currency,
            'status' => (int) ($bundle->is_active ?? 0) === 1
                ? 'published'
                : 'draft',
            'updated_at' => now(),
        ];

        if ($existing) {
            /*
             * Do not overwrite featured here.
             * It is an Admin-controlled Marketplace setting.
             */
            DB::table('marketplace_listings')
                ->where('id', $existing->id)
                ->update($payload);

            return DB::table('marketplace_listings')
                ->where('id', $existing->id)
                ->first();
        }

        $payload = array_merge([
            'vendor_id' => 1,
            'catalog_product_id' => $bundle->catalog_product_id ?? null,
            'product_key' => null,
            'workspace_id' => null,
            'uuid' => (string) Str::uuid(),
            'commission_rate' => 0,
            'featured' => 1,
            'created_at' => now(),
            'deleted_at' => null,
        ], $payload);

        $id = DB::table('marketplace_listings')
            ->insertGetId($payload);

        return DB::table('marketplace_listings')
            ->where('id', $id)
            ->first();
    }

    public function syncAll(): array
    {
        $ids = DB::table('core_addon_bundles')
            ->whereNull('deleted_at')
            ->pluck('id');

        $synced = 0;

        foreach ($ids as $id) {
            if ($this->syncById((int) $id)) {
                $synced++;
            }
        }

        return [
            'total' => $ids->count(),
            'synced' => $synced,
        ];
    }
}
