<?php

namespace App\Services\Marketplace;

use App\Services\Core\EsubizPlatformSaleService;
use Illuminate\Support\Facades\DB;

class MarketplaceFinancialRecorder
{
    public function record(
        int $orderId,
        int $paymentTransactionId
    ): void {
        /*
         * Idempotency:
         * a successful payment transaction must never create the same
         * Esubiz revenue record more than once.
         */
        $alreadyRecorded = DB::table('revenue_events')
            ->where('reference_type', 'payment_transaction')
            ->where('reference_id', $paymentTransactionId)
            ->exists();

        if ($alreadyRecorded) {
            return;
        }

        $order = DB::table('marketplace_orders')
            ->where('id', $orderId)
            ->first();

        if (!$order) {
            return;
        }

        $listing = DB::table('marketplace_listings')
            ->where('id', $order->marketplace_listing_id)
            ->first();

        /*
         * Volume credit orders are deliberately listingless. Their
         * immutable purchase snapshot supplies the product identity
         * needed for financial reporting. Credit fulfilment remains
         * exclusively in CreditVolumeFulfilmentService.
         */
        $volumePurchase = null;
        if (!$listing) {
            $volumePurchase = DB::table('marketplace_credit_volume_purchases')
                ->where('marketplace_order_id', $order->id)
                ->first();

            if (!$volumePurchase) {
                return;
            }

            $listing = (object) [
                'product_type' => 'credit_volume',
                'product_id' => (int) $volumePurchase->tier_id,
                'title' => strtoupper(str_replace('_credits', '', $volumePurchase->credit_type))
                    . ' credits (' . number_format((int) $volumePurchase->credit_quantity) . ')',
                'developer_id' => null,
            ];
        }

        $checkout = DB::table('marketplace_checkout_sessions')
            ->where('marketplace_order_id', $order->id)
            ->latest('id')
            ->first();

        $deploymentType = $checkout->deployment_type
            ?? (
                str_starts_with((string) $order->reference, 'DEV-')
                    ? 'off_server'
                    : 'saas'
            );

        /*
         * Off-server purchases intentionally have no website.
         * SaaS purchases retain the originating website.
         */
        $websiteId = $volumePurchase
            ? (int) $volumePurchase->website_id
            : ($deploymentType === 'saas'
                ? ($checkout->website_id ?? null)
                : null);

        $sourceType = match ($listing->product_type) {
            'core_addon', 'addon' => 'addons',
            'core_bundle', 'bundle' => 'addons',
            'theme' => 'themes',
            'module' => 'modules',
            'subscription' => 'subscriptions',
            'hybrid' => 'hybrid',
            'ai_credits' => 'ai_credits',
            'sms_credits' => 'sms_credits',
            'email_credits' => 'email_credits',
            'whatsapp_credits' => 'whatsapp_credits',
            default => 'marketplace',
        };

        app(EsubizPlatformSaleService::class)->record(
            $order->workspace_id ?? null,
            $sourceType,
            $listing->product_type,
            (int) $listing->product_id,
            $listing->title,
            (float) $order->amount,
            $order->currency ?: app(
                \App\Services\Platform\CentralSiteSettingsService::class
            )->primaryCurrency(),
            (int) $order->buyer_id,
            $websiteId,
            'purchase',
            [
                'marketplace_order_id' => $order->id,
                'marketplace_order_reference' => $order->reference,
                'payment_transaction_id' => $paymentTransactionId,
                'deployment_type' => $deploymentType,
                'credit_type' => $volumePurchase?->credit_type,
                'credit_quantity' => $volumePurchase?->credit_quantity,
                'credit_volume_tier_id' => $volumePurchase?->tier_id,
                'developer_id' => $listing->developer_id ?? null,
                'financial_account_developer_id' =>
                    $listing->developer_id ?? null,
            ]
        );
    }
}
