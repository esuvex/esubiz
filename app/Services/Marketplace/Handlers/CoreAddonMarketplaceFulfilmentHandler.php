<?php

namespace App\Services\Marketplace\Handlers;

use App\Services\Core\CoreAddonMarketplaceFulfilmentService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CoreAddonMarketplaceFulfilmentHandler
{
    public function __construct(
        protected CoreAddonMarketplaceFulfilmentService $addons
    ) {
    }

    public function fulfil(object $order, object $listing): array
    {
        $type = (string) ($listing->product_type ?? '');

        if (!in_array($type, ['core_addon', 'core_bundle'], true)) {
            throw new RuntimeException('Invalid Core Add-on product type.');
        }

        return DB::transaction(function () use ($order, $listing, $type): array {
            $paidOrder = DB::table('marketplace_orders')
                ->where('id', (int) ($order->id ?? 0))
                ->where('payment_status', 'paid')
                ->lockForUpdate()
                ->first();

            if (!$paidOrder || (int) $paidOrder->buyer_id < 1) {
                throw new RuntimeException(
                    'A paid Marketplace order is required for Core Add-on fulfilment.'
                );
            }

            $session = DB::table('marketplace_checkout_sessions')
                ->where('marketplace_order_id', (int) $paidOrder->id)
                ->where('user_id', (int) $paidOrder->buyer_id)
                ->where('product_type', $type)
                ->where('product_id', (int) $listing->product_id)
                ->whereNull('deleted_at')
                ->latest('id')
                ->first();

            if (
                !$session
                || !in_array($session->deployment_type, ['saas', 'off_server'], true)
            ) {
                throw new RuntimeException(
                    'A valid Core checkout session is required for fulfilment.'
                );
            }

            return $this->addons->fulfil(
                (int) $paidOrder->buyer_id,
                (int) $listing->product_id,
                $type,
                (string) $session->deployment_type,
                (int) $session->website_id ?: null,
                (int) $session->workspace_id ?: null,
                (int) $paidOrder->id,
                (string) $paidOrder->reference
            );
        });
    }
}
