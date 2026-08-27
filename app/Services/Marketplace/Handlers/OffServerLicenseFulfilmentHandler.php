<?php

namespace App\Services\Marketplace\Handlers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * ============================================================
 * OFF-SERVER LICENSE-ONLY MARKETPLACE FULFILMENT
 * ============================================================
 *
 * Successful payment issues ONE pending Central license.
 *
 * It does NOT:
 *
 * - bind a domain
 * - activate the license
 * - create a Central Website
 *
 * The customer can use an existing Core/Website Type ZIP and
 * manually enter this new license during installation.
 */
class OffServerLicenseFulfilmentHandler
{
    public function fulfil(
        object $order,
        object $listing
    ): array {

        $orderId =
            (int) (
                $order->id
                ?? 0
            );

        if ($orderId <= 0) {
            throw new RuntimeException(
                'Marketplace order is invalid.'
            );
        }


        $buyerId =
            (int) (
                $order->buyer_id
                ?? 0
            );

        if ($buyerId <= 0) {
            throw new RuntimeException(
                'License purchase has no valid Esubiz buyer.'
            );
        }


        $productId =
            (int) (
                $listing->product_id
                ?? 0
            );

        if ($productId <= 0) {
            throw new RuntimeException(
                'License product is invalid.'
            );
        }


        $product =
            DB::table(
                'off_server_license_products'
            )
                ->where(
                    'id',
                    $productId
                )
                ->where(
                    'is_active',
                    true
                )
                ->first();


        if (!$product) {
            throw new RuntimeException(
                'Off-server license product was not found.'
            );
        }


        if (
            (int) $product->domains_per_license
            !== 1
        ) {
            throw new RuntimeException(
                'Off-server licenses must be limited to one domain.'
            );
        }


        return DB::transaction(
            function () use (
                $order,
                $listing,
                $product,
                $orderId,
                $buyerId
            ) {

                /*
                 * ------------------------------------------------
                 * IDEMPOTENCY
                 * ------------------------------------------------
                 *
                 * One Marketplace order can issue exactly one
                 * license even when payment callbacks repeat.
                 */
                $existing =
                    DB::table(
                        'off_server_license_registrations'
                    )
                        ->where(
                            'marketplace_order_id',
                            $orderId
                        )
                        ->lockForUpdate()
                        ->first();


                if ($existing) {

                    return [
                        'success' =>
                            true,

                        'fulfilled' =>
                            true,

                        'already_issued' =>
                            true,

                        'registration_id' =>
                            $existing->id,

                        'license_key' =>
                            $existing->license_key,

                        'license_type' =>
                            $existing->license_type,

                        'status' =>
                            $existing->status,

                        'domain_locked' =>
                            !empty(
                                $existing->registered_domain
                            ),

                        'registered_domain' =>
                            $existing->registered_domain,

                        'website_id' =>
                            $existing->website_id,
                    ];
                }


                do {

                    $licenseKey =
                        'CORE-'
                        . strtoupper(
                            Str::random(32)
                        );


                    $exists =
                        DB::table(
                            'off_server_license_registrations'
                        )
                            ->where(
                                'license_key',
                                $licenseKey
                            )
                            ->exists();

                } while ($exists);


                $registrationId =
                    DB::table(
                        'off_server_license_registrations'
                    )
                        ->insertGetId([
                            'uuid' =>
                                (string) Str::uuid(),

                            'user_id' =>
                                $buyerId,

                            'license_product_id' =>
                                $product->id,

                            'marketplace_order_id' =>
                                $orderId,

                            'license_type' =>
                                $product->license_type,

                            /*
                             * First successful installation later
                             * establishes these values.
                             */
                            'website_id' =>
                                null,

                            'license_key' =>
                                $licenseKey,

                            'registered_domain' =>
                                null,

                            'domain_hash' =>
                                null,

                            'installation_uuid' =>
                                null,

                            'api_application_id' =>
                                null,

                            'status' =>
                                'pending',

                            'activated_at' =>
                                null,

                            'last_verified_at' =>
                                null,

                            'revoked_at' =>
                                null,

                            'metadata' =>
                                json_encode([
                                    'marketplace_order_id' =>
                                        $orderId,

                                    'marketplace_order_reference' =>
                                        $order->reference
                                        ?? null,

                                    'marketplace_listing_id' =>
                                        $listing->id
                                        ?? null,

                                    'catalog_product_id' =>
                                        $listing->catalog_product_id
                                        ?? null,

                                    'license_product_id' =>
                                        $product->id,

                                    'license_product_name' =>
                                        $product->name,

                                    'license_type' =>
                                        $product->license_type,

                                    'domains_per_license' =>
                                        1,

                                    'issued_by' =>
                                        'marketplace_license_purchase',
                                ],
                                JSON_UNESCAPED_SLASHES),

                            'created_at' =>
                                now(),

                            'updated_at' =>
                                now(),
                        ]);


                return [
                    'success' =>
                        true,

                    'fulfilled' =>
                        true,

                    'already_issued' =>
                        false,

                    'registration_id' =>
                        $registrationId,

                    'license_key' =>
                        $licenseKey,

                    'license_type' =>
                        $product->license_type,

                    'status' =>
                        'pending',

                    'domain_locked' =>
                        false,

                    'registered_domain' =>
                        null,

                    'website_id' =>
                        null,
                ];
            }
        );
    }
}
