<?php

namespace App\Services\Marketplace;

use App\Models\Website;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CreditVolumeCheckoutService
{
    public function __construct(
        protected CreditVolumePricingService $pricing
    ) {
    }

    public function create(
        Website $website,
        int $buyerId,
        string $creditType,
        int $quantity,
        string $checkoutOrigin,
        string $accountMode = 'user',
        ?string $returnUrl = null
    ): int {
        $deployment = strtolower(trim((string) $website->deployment_type));

        if (!in_array($deployment, ['saas', 'off_server'], true) ||
            $buyerId < 1 ||
            !in_array($checkoutOrigin, [
                'central_account', 'saas_website', 'off_server_website',
            ], true)) {
            throw new InvalidArgumentException('Invalid credit checkout context.');
        }

        $quote = $this->pricing->quote(
            $creditType,
            $quantity,
            $deployment
        );

        return DB::transaction(function () use (
            $website,
            $buyerId,
            $checkoutOrigin,
            $accountMode,
            $returnUrl,
            $quote,
            $deployment
        ) {
            $amount = $quote['total'];
            $reference = 'CRED-' . strtoupper(Str::random(16));

            $orderId = DB::table('marketplace_orders')->insertGetId([
                'marketplace_listing_id' => null,
                'vendor_id' => null,
                'workspace_id' => $website->workspace_id,
                'buyer_id' => $buyerId,
                'uuid' => (string) Str::uuid(),
                'reference' => $reference,
                'amount' => $amount,
                'commission_amount' => 0,
                'vendor_amount' => $amount,
                'currency' => $quote['currency'],
                'payment_status' => 'pending',
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('marketplace_checkout_sessions')->insert([
                'user_id' => $buyerId,
                'account_mode' => $accountMode,
                'checkout_origin' => $checkoutOrigin,
                'return_url' => $returnUrl,
                'return_area' => 'credits',
                'wallet_allowed' => $checkoutOrigin === 'central_account',
                'deployment_type' => $deployment,
                'product_type' => 'credit_volume',
                'product_id' => $quote['tier_id'],
                'website_id' => $website->id,
                'workspace_id' => $website->workspace_id,
                'quantity' => $quote['quantity'],
                'unit_price' => $quote['unit_price'],
                'total_amount' => $amount,
                'currency' => $quote['currency'],
                'marketplace_order_id' => $orderId,
                'is_commissionable' => false,
                'status' => 'pending_payment',
                'expires_at' => now()->addHours(24),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('marketplace_credit_volume_purchases')->insert([
                'marketplace_order_id' => $orderId,
                'website_id' => $website->id,
                'tier_id' => $quote['tier_id'],
                'credit_type' => $quote['credit_type'],
                'deployment_type' => $deployment,
                'credit_quantity' => $quote['quantity'],
                'currency' => $quote['currency'],
                'unit_price' => $quote['unit_price'],
                'total_amount' => $amount,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return $orderId;
        });
    }
}
