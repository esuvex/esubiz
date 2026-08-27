<?php

namespace App\Services\Marketplace\Handlers;

use App\Services\Credits\WebsiteCreditService;
use App\Support\Credits\CreditTypeRegistry;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CreditPackageFulfilmentHandler
{
    public function __construct(
        protected WebsiteCreditService $credits,
        protected CreditTypeRegistry $registry
    ) {
    }


    public function fulfil(
        object $order,
        object $listing
    ): array {

        /*
        |--------------------------------------------------------------------------
        | PAYMENT SAFETY
        |--------------------------------------------------------------------------
        |
        | MarketplaceFulfilmentManager should only call this after payment,
        | but the handler protects itself as well.
        |
        */

        if (
            isset($order->payment_status)
            && $order->payment_status !== 'paid'
        ) {
            throw new RuntimeException(
                'Credit packages can only be fulfilled after successful payment.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | TARGET WEBSITE
        |--------------------------------------------------------------------------
        |
        | Both paths resolve here:
        |
        | SaaS:
        |   Esubiz User Account OR SaaS website admin
        |
        | Off-server:
        |   Esubiz Developer account OR off-server website admin
        |
        | In every case website_id identifies the authoritative
        | Central Esubiz website record that owns the credit balance.
        |
        */

        $websiteId =
            (int) (
                $order->website_id
                ?? 0
            );


        if ($websiteId <= 0) {
            throw new RuntimeException(
                'A target website is required for a credit package purchase.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | RESOLVE CREDIT PACKAGE
        |--------------------------------------------------------------------------
        |
        | Prefer the listing product_id.
        |
        | Compatibility fallback:
        | catalog_product_id may identify the associated credit package.
        |
        */

        $package = null;


        if (
            !empty(
                $listing->product_id
            )
        ) {
            $package =
                DB::table(
                    'credit_packages'
                )
                ->where(
                    'id',
                    (int) $listing->product_id
                )
                ->where(
                    'is_active',
                    true
                )
                ->first();
        }


        if (
            !$package
            && !empty(
                $listing->catalog_product_id
            )
        ) {
            $package =
                DB::table(
                    'credit_packages'
                )
                ->where(
                    'catalog_product_id',
                    (int) $listing->catalog_product_id
                )
                ->where(
                    'is_active',
                    true
                )
                ->first();
        }


        if (!$package) {
            throw new RuntimeException(
                'The credit package for this marketplace purchase could not be resolved.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | GENERIC CREDIT TYPE
        |--------------------------------------------------------------------------
        */

        $creditType =
            trim(
                (string) (
                    $package->credit_type
                    ?? ''
                )
            );


        if (
            $creditType === ''
            || !$this->registry->has(
                $creditType
            )
        ) {
            throw new RuntimeException(
                "Unsupported credit type [{$creditType}]."
            );
        }


        $packageCredits =
            (float) (
                $package->credit_quantity
                ?? 0
            );


        if ($packageCredits <= 0) {
            throw new RuntimeException(
                'The credit package has no valid credit quantity.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | MARKETPLACE QUANTITY
        |--------------------------------------------------------------------------
        |
        | Example:
        | 500-credit package x quantity 3 = 1,500 credits.
        |
        */

        $purchaseQuantity =
            max(
                1,
                (int) (
                    $order->quantity
                    ?? 1
                )
            );


        $totalCredits =
            $packageCredits
            * $purchaseQuantity;


        /*
        |--------------------------------------------------------------------------
        | IDEMPOTENCY
        |--------------------------------------------------------------------------
        |
        | One paid marketplace order must NEVER credit twice,
        | even if payment verification / fulfilment is retried.
        |
        */

        $orderIdentity =
            $order->reference
            ?? $order->uuid
            ?? $order->id
            ?? null;


        if (!$orderIdentity) {
            throw new RuntimeException(
                'Marketplace order has no fulfilment identity.'
            );
        }


        $requestKey =
            'marketplace-credit:'
            . $orderIdentity;


        /*
        |--------------------------------------------------------------------------
        | CHECKPOINT 8 — AUTHORITATIVE CENTRAL SERVICE CREDIT
        |--------------------------------------------------------------------------
        |
        | Marketplace purchases now credit the Central Esubiz service
        | ledger directly.
        |
        | SaaS and off-server websites may display/use this balance,
        | but neither deployment may create authoritative credits in
        | its own local database.
        |
        | The Marketplace order identity remains the idempotency key.
        |
        */

        $transaction =
            $this->credits->centralCredit(
                $websiteId,
                $creditType,
                $totalCredits,
                $requestKey,
                [
                    'user_id' =>
                        $order->buyer_id
                        ?? $order->user_id
                        ?? null,

                    'installation_id' =>
                        $order->installation_id
                        ?? null,

                    'source_type' =>
                        'marketplace_order',

                    'source_id' =>
                        isset($order->id)
                            ? (int) $order->id
                            : null,

                    'metadata' => [
                        'type' =>
                            'marketplace_credit_purchase',

                        'description' =>
                            (
                                $package->name
                                ?? 'Credit Package'
                            )
                            . ' purchase',

                        'marketplace_order_id' =>
                            $order->id
                            ?? null,

                        'marketplace_order_reference' =>
                            $order->reference
                            ?? null,

                        'marketplace_listing_id' =>
                            $listing->id
                            ?? null,

                        'catalog_product_id' =>
                            $package->catalog_product_id
                            ?? null,

                        'credit_package_id' =>
                            $package->id,

                        'credit_type' =>
                            $creditType,

                        'package_credit_quantity' =>
                            $packageCredits,

                        'purchase_quantity' =>
                            $purchaseQuantity,

                        'total_credits' =>
                            $totalCredits,

                        'deployment_type' =>
                            $order->deployment_type
                            ?? null,

                        'workspace_id' =>
                            $order->workspace_id
                            ?? null,
                    ],
                ]
            );


        return [
            'success' =>
                true,

            'fulfilled' =>
                true,

            /*
             * Repeated payment verification/fulfilment returns the
             * existing Central transaction without adding credits.
             */
            'already_processed' =>
                (bool) (
                    $transaction['already_processed']
                    ?? false
                ),

            'product_type' =>
                'credit_package',

            'credit_type' =>
                $creditType,

            'website_id' =>
                $websiteId,

            'package_id' =>
                $package->id,

            'package_name' =>
                $package->name,

            'package_credits' =>
                $packageCredits,

            'quantity' =>
                $purchaseQuantity,

            'credits_added' =>
                (float) (
                    $transaction['amount']
                    ?? $totalCredits
                ),

            'balance_before' =>
                (float) (
                    $transaction['balance_before']
                    ?? 0
                ),

            'balance_after' =>
                (float) (
                    $transaction['balance_after']
                    ?? 0
                ),

            'transaction_id' =>
                $transaction['transaction_id']
                ?? null,

            /*
             * Central service-credit transactions use request_key
             * as their immutable external transaction identity.
             */
            'transaction_reference' =>
                $transaction['request_key']
                ?? $requestKey,

            'central_service_ledger' =>
                true,
        ];
    }
}
