<?php

namespace App\Services\Marketplace;

use App\Models\WebsiteType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * ESUBIZ_WEBSITE_TYPE_CATALOG_V1
 *
 * Keeps Website Types represented in the canonical Marketplace registry.
 *
 * Architecture:
 *
 * - Website Type remains the source/wizard identity.
 * - Website Type represents the prepared Core distribution:
 *   Core + configured Theme(s) + Module(s) + configuration.
 * - catalog_products provides the canonical commercial product ID used
 *   by Marketplace entitlement and licensing infrastructure.
 * - Core is NOT exposed as a separate Developer Wizard product.
 */
class WebsiteTypeCatalogService
{
    /**
     * Synchronize one Website Type into catalog_products.
     */
    public function sync(WebsiteType $websiteType): object
    {
        if (!$websiteType->exists) {
            throw new RuntimeException(
                'Website Type must exist before Marketplace synchronization.'
            );
        }

        $slug = trim((string) $websiteType->slug);

        if ($slug === '') {
            throw new RuntimeException(
                'Website Type requires a slug before Marketplace synchronization.'
            );
        }

        $existing = DB::table('catalog_products')
            ->where('product_type', 'website_type')
            ->where('slug', $slug)
            ->whereNull('deleted_at')
            ->first();

        $audience = $websiteType->show_in_developer_wizard
            ? (
                $websiteType->show_in_user_wizard
                    ? 'both'
                    : 'developer'
            )
            : 'saas';

        $data = [
            'name' => $websiteType->name,
            'slug' => $slug,
            'description' => $websiteType->description,
            'product_type' => 'website_type',
            'credit_quantity' => null,
            'audience' => $audience,
            'fulfilment_type' => 'instant',
            'is_featured' => false,
            'is_active' => (bool) $websiteType->is_active,
            'is_public' => true,
            'updated_at' => now(),
        ];

        if ($existing) {
            DB::table('catalog_products')
                ->where('id', $existing->id)
                ->update($data);

            return DB::table('catalog_products')
                ->where('id', $existing->id)
                ->first();
        }

        $data['uuid'] = (string) Str::uuid();
        $data['catalog_category_id'] = null;
        $data['created_at'] = now();

        $id = DB::table('catalog_products')
            ->insertGetId($data);

        return DB::table('catalog_products')
            ->where('id', $id)
            ->first();
    }

    /**
     * Resolve the canonical Marketplace product for a Website Type.
     *
     * Synchronization is intentional here so payment fulfilment cannot
     * fail merely because an older Website Type predates catalog_products.
     */
    public function resolve(WebsiteType $websiteType): object
    {
        return $this->sync($websiteType);
    }

    /**
     * Synchronize every non-deleted Website Type.
     *
     * Useful for initial migration/backfill and future maintenance.
     *
     * @return array<int,object>
     */
    public function syncAll(): array
    {
        $products = [];

        WebsiteType::query()
            ->orderBy('id')
            ->each(function (WebsiteType $websiteType) use (&$products) {
                $products[] = $this->sync($websiteType);
            });

        return $products;
    }
}
