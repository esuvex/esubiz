<?php

namespace App\Services\Marketplace;

use App\Services\Credits\WebsiteCreditService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CreditVolumeFulfilmentService
{
    public function __construct(
        protected WebsiteCreditService $credits
    ) {
    }

    public function fulfil(object $order): array
    {
        $orderId = (int) ($order->id ?? 0);

        if ($orderId < 1) {
            throw new RuntimeException('A Marketplace order is required.');
        }

        $paidOrder = DB::table('marketplace_orders')
            ->where('id', $orderId)
            ->first();

        if (!$paidOrder || $paidOrder->payment_status !== 'paid') {
            throw new RuntimeException('Credits require a confirmed paid order.');
        }

        $purchase = DB::table('marketplace_credit_volume_purchases')
            ->where('marketplace_order_id', $orderId)
            ->first();

        if (!$purchase) {
            throw new RuntimeException('Credit volume purchase was not found.');
        }

        $session = DB::table('marketplace_checkout_sessions')
            ->where('marketplace_order_id', $orderId)
            ->where('website_id', $purchase->website_id)
            ->where('deployment_type', $purchase->deployment_type)
            ->where('user_id', $paidOrder->buyer_id)
            ->whereNull('deleted_at')
            ->latest('id')
            ->first();

        if (!$session ||
            (string) $session->product_type !== 'credit_volume' ||
            (int) $session->product_id !== (int) $purchase->tier_id ||
            (int) $session->quantity !== (int) $purchase->credit_quantity) {
            throw new RuntimeException('Credit purchase checkout identity does not match.');
        }

        if ((int) round((float) $paidOrder->amount * 100) !==
                (int) round((float) $purchase->total_amount * 100) ||
            strtoupper((string) $paidOrder->currency) !==
                strtoupper((string) $purchase->currency)) {
            throw new RuntimeException('Paid amount does not match the credit purchase.');
        }

        $quantity = (int) $purchase->credit_quantity;
        if ($quantity < 1 || $quantity > 100000000) {
            throw new RuntimeException('Invalid purchased credit quantity.');
        }

        $transaction = $this->credits->centralCredit(
            (int) $purchase->website_id,
            (string) $purchase->credit_type,
            $quantity,
            'marketplace-credit-volume:' . $orderId,
            [
                'user_id' => (int) $paidOrder->buyer_id,
                'source_type' => 'marketplace_order',
                'source_id' => $orderId,
                'metadata' => [
                    'type' => 'marketplace_credit_volume_purchase',
                    'credit_type' => $purchase->credit_type,
                    'credit_quantity' => $quantity,
                    'tier_id' => (int) $purchase->tier_id,
                    'unit_price' => $purchase->unit_price,
                    'total_amount' => $purchase->total_amount,
                    'currency' => $purchase->currency,
                    'deployment_type' => $purchase->deployment_type,
                    'website_id' => (int) $purchase->website_id,
                ],
            ]
        );

        return [
            'fulfilled' => true,
            'already_processed' => (bool) ($transaction['already_processed'] ?? false),
            'website_id' => (int) $purchase->website_id,
            'credit_type' => $purchase->credit_type,
            'credits_added' => $quantity,
            'transaction_id' => $transaction['transaction_id'] ?? null,
        ];
    }
}
