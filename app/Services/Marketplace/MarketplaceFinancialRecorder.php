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

        if (!$listing) {
            return;
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
        $websiteId = $deploymentType === 'saas'
            ? ($checkout->website_id ?? null)
            : null;

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
            $order->currency ?: 'NGN',
            (int) $order->buyer_id,
            $websiteId,
            'purchase',
            [
                'marketplace_order_id' => $order->id,
                'marketplace_order_reference' => $order->reference,
                'payment_transaction_id' => $paymentTransactionId,
                'deployment_type' => $deploymentType,
                'developer_id' => $listing->developer_id ?? null,
                'financial_account_developer_id' =>
                    $listing->developer_id ?? null,
            ]
        );
    }
}
