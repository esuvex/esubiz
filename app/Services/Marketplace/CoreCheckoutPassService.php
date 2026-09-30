<?php

namespace App\Services\Marketplace;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class CoreCheckoutPassService
{
    public function issue(int $orderId, int $websiteId, string $origin): string
    {
        if ($orderId < 1 || $websiteId < 1 ||
            !in_array($origin, ['saas_website', 'off_server_website'], true)) {
            throw new RuntimeException('Invalid Core checkout context.');
        }

        $session = DB::table('marketplace_checkout_sessions')
            ->where('marketplace_order_id', $orderId)
            ->where('website_id', $websiteId)
            ->where('checkout_origin', $origin)
            ->where('wallet_allowed', false)
            ->whereNull('deleted_at')
            ->latest('id')
            ->first();

        $order = DB::table('marketplace_orders')
            ->where('id', $orderId)
            ->first();

        if (!$session || !$order ||
            (int) $session->user_id !== (int) $order->buyer_id ||
            $order->payment_status !== 'pending') {
            throw new RuntimeException('Core checkout order is unavailable.');
        }

        $token = Str::random(64);

        DB::table('core_checkout_passes')->insert([
            'marketplace_order_id' => $orderId,
            'website_id' => $websiteId,
            'checkout_origin' => $origin,
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addMinutes(30),
            'revoked_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $token;
    }

    public function resolve(string $token, int $orderId): object
    {
        if ($orderId < 1 || !preg_match('/^[A-Za-z0-9]{64}$/D', $token)) {
            throw new RuntimeException('Invalid Core checkout pass.');
        }

        $pass = DB::table('core_checkout_passes')
            ->where('marketplace_order_id', $orderId)
            ->where('token_hash', hash('sha256', $token))
            ->whereNull('revoked_at')
            ->where('expires_at', '>', now())
            ->first();

        if (!$pass) {
            throw new RuntimeException('Core checkout pass has expired or is invalid.');
        }

        $session = DB::table('marketplace_checkout_sessions')
            ->where('marketplace_order_id', $orderId)
            ->where('website_id', (int) $pass->website_id)
            ->where('checkout_origin', $pass->checkout_origin)
            ->where('wallet_allowed', false)
            ->whereNull('deleted_at')
            ->latest('id')
            ->first();

        $order = DB::table('marketplace_orders')
            ->where('id', $orderId)
            ->first();

        if (!$session || !$order ||
            (int) $session->user_id !== (int) $order->buyer_id) {
            throw new RuntimeException('Core checkout order no longer matches its pass.');
        }

        return $pass;
    }
}
