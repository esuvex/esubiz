<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OffServerLicenseProductSeeder extends Seeder
{
    public function run(): void
    {
        /*
         * --------------------------------------------------------
         * LICENSE PRODUCT
         * --------------------------------------------------------
         *
         * Editable later from Central Admin.
         *
         * One purchase = one pending license = one future domain.
         */

        $product =
            DB::table(
                'off_server_license_products'
            )
                ->where(
                    'slug',
                    'esubiz-core-off-server-license'
                )
                ->first();


        if (!$product) {

            $productId =
                DB::table(
                    'off_server_license_products'
                )
                    ->insertGetId([
                        'catalog_product_id' =>
                            null,

                        'name' =>
                            'Esubiz Core Off-Server License',

                        'slug' =>
                            'esubiz-core-off-server-license',

                        'description' =>
                            'One valid Esubiz Core off-server license for one domain. Domain is permanently locked only after successful installation.',

                        'license_type' =>
                            'core',

                        /*
                         * Initial editable price.
                         *
                         * Change this from Central Admin later.
                         */
                        'price' =>
                            25000,

                        'currency' =>
                            'NGN',

                        'domains_per_license' =>
                            1,

                        'is_active' =>
                            true,

                        'sort_order' =>
                            1,

                        'metadata' =>
                            json_encode([
                                'marketplace_category' =>
                                    'license',

                                'license_only' =>
                                    true,

                                'manual_entry_during_installation' =>
                                    true,

                                'activation_stage' =>
                                    'after_successful_installation',
                            ],
                            JSON_UNESCAPED_SLASHES),

                        'created_at' =>
                            now(),

                        'updated_at' =>
                            now(),
                    ]);

        } else {

            $productId =
                $product->id;
        }


        /*
         * --------------------------------------------------------
         * MARKETPLACE LISTING
         * --------------------------------------------------------
         */

        $listing =
            DB::table(
                'marketplace_listings'
            )
                ->where(
                    'product_type',
                    'off_server_license'
                )
                ->where(
                    'product_id',
                    $productId
                )
                ->whereNull(
                    'deleted_at'
                )
                ->first();


        $product =
            DB::table(
                'off_server_license_products'
            )
                ->where(
                    'id',
                    $productId
                )
                ->first();


        $data = [
            'vendor_id' =>
                1,

            'catalog_product_id' =>
                $product->catalog_product_id,

            'product_type' =>
                'off_server_license',

            'product_id' =>
                $productId,

            'product_key' =>
                'off_server_license:'
                . $productId,

            'workspace_id' =>
                null,

            'title' =>
                $product->name,

            'slug' =>
                $product->slug,

            'summary' =>
                'Valid Esubiz Core license for one off-server domain.',

            'description' =>
                $product->description,

            'price' =>
                $product->price,

            'currency' =>
                $product->currency,

            'commission_rate' =>
                0,

            'featured' =>
                false,

            'status' =>
                'published',

            'updated_at' =>
                now(),
        ];


        if ($listing) {

            DB::table(
                'marketplace_listings'
            )
                ->where(
                    'id',
                    $listing->id
                )
                ->update(
                    $data
                );

        } else {

            $data['uuid'] =
                (string) Str::uuid();

            $data['created_at'] =
                now();

            DB::table(
                'marketplace_listings'
            )
                ->insert(
                    $data
                );
        }
    }
}
