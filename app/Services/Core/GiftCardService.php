<?php

namespace App\Services\Core;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class GiftCardService
{
    /**
     * Issue a new Esubiz gift card.
     */
    public function issue(
        float $amount,
        string $currency = 'NGN',
        ?string $name = null,
        ?int $issuedToUserId = null,
        ?int $createdBy = null,
        array $settings = [],
        array $metadata = []
    ): object {
        if ($amount <= 0) {
            throw new RuntimeException(
                'Gift card amount must be greater than zero.'
            );
        }

        return DB::transaction(function () use (
            $amount,
            $currency,
            $name,
            $issuedToUserId,
            $createdBy,
            $settings,
            $metadata
        ) {
            $card = DB::table('gift_cards')->insertGetId([
                'uuid' => (string) Str::uuid(),
                'code' => $this->generateCode(),
                'name' => $name ?: 'Esubiz Gift Card',
                'amount_type' => 'fixed',
                'initial_amount' => $amount,
                'remaining_balance' => $amount,
                'currency' => strtoupper($currency),
                'usage_limit' => $settings['usage_limit'] ?? null,
                'usage_count' => 0,
                'starts_at' => $settings['starts_at'] ?? null,
                'expires_at' => $settings['expires_at'] ?? null,
                'enabled' => $settings['enabled'] ?? true,
                'usable_at_checkout' =>
                    $settings['usable_at_checkout'] ?? true,
                'usable_for_wallet_funding' =>
                    $settings['usable_for_wallet_funding'] ?? true,
                'allow_partial_redemption' =>
                    $settings['allow_partial_redemption'] ?? true,
                'status' => 'active',
                'issued_to_user_id' => $issuedToUserId,
                'created_by' => $createdBy,
                'settings' => json_encode($settings),
                'metadata' => json_encode($metadata),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $giftCard = DB::table('gift_cards')
                ->where('id', $card)
                ->first();

            DB::table('gift_card_transactions')->insert([
                'gift_card_id' => $giftCard->id,
                'user_id' => $issuedToUserId,
                'uuid' => (string) Str::uuid(),
                'reference' => 'GCI-' . strtoupper(Str::random(16)),
                'type' => 'issuance',
                'amount' => $amount,
                'balance_before' => 0,
                'balance_after' => $amount,
                'currency' => strtoupper($currency),
                'usage_context' => 'issuance',
                'metadata' => json_encode([]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return $giftCard;
        });
    }

    /**
     * Validate a gift card for a particular use.
     */
    public function validate(
        string $code,
        float $amount,
        string $context = 'checkout'
    ): object {
        if ($amount <= 0) {
            throw new RuntimeException(
                'Gift card amount must be greater than zero.'
            );
        }

        $card = DB::table('gift_cards')
            ->where('code', strtoupper(trim($code)))
            ->whereNull('deleted_at')
            ->first();

        if (!$card) {
            throw new RuntimeException(
                'Gift card not found.'
            );
        }

        $this->assertUsable($card, $context);

        if ($card->remaining_balance <= 0) {
            throw new RuntimeException(
                'This gift card has no remaining balance.'
            );
        }

        if (
            !$card->allow_partial_redemption
            && $amount > (float) $card->remaining_balance
        ) {
            throw new RuntimeException(
                'This gift card does not allow partial redemption.'
            );
        }

        if (
            $card->usage_limit !== null
            && $card->usage_count >= $card->usage_limit
        ) {
            throw new RuntimeException(
                'This gift card has reached its usage limit.'
            );
        }

        return $card;
    }

    /**
     * Redeem a gift card atomically.
     *
     * Returns the actual amount redeemed.
     */
    public function redeem(
        string $code,
        float $requestedAmount,
        int $userId,
        string $context = 'checkout',
        ?string $referenceType = null,
        ?int $referenceId = null,
        array $metadata = []
    ): object {
        if ($requestedAmount <= 0) {
            throw new RuntimeException(
                'Redemption amount must be greater than zero.'
            );
        }

        return DB::transaction(function () use (
            $code,
            $requestedAmount,
            $userId,
            $context,
            $referenceType,
            $referenceId,
            $metadata
        ) {
            $card = DB::table('gift_cards')
                ->where('code', strtoupper(trim($code)))
                ->whereNull('deleted_at')
                ->lockForUpdate()
                ->first();

            if (!$card) {
                throw new RuntimeException(
                    'Gift card not found.'
                );
            }

            $this->assertUsable($card, $context);

            $remaining = (float) $card->remaining_balance;

            if ($remaining <= 0) {
                throw new RuntimeException(
                    'This gift card has no remaining balance.'
                );
            }

            if (
                $card->usage_limit !== null
                && $card->usage_count >= $card->usage_limit
            ) {
                throw new RuntimeException(
                    'This gift card has reached its usage limit.'
                );
            }

            if (
                !$card->allow_partial_redemption
                && $requestedAmount > $remaining
            ) {
                throw new RuntimeException(
                    'This gift card does not allow partial redemption.'
                );
            }

            $redeemed = min($requestedAmount, $remaining);
            $balanceAfter = $remaining - $redeemed;
            $usageAfter = ((int) $card->usage_count) + 1;

            $status = $balanceAfter <= 0
                ? 'exhausted'
                : 'active';

            DB::table('gift_cards')
                ->where('id', $card->id)
                ->update([
                    'remaining_balance' => $balanceAfter,
                    'usage_count' => $usageAfter,
                    'status' => $status,
                    'updated_at' => now(),
                ]);

            $transactionId = DB::table('gift_card_transactions')
                ->insertGetId([
                    'gift_card_id' => $card->id,
                    'user_id' => $userId,
                    'uuid' => (string) Str::uuid(),
                    'reference' => 'GCR-' . strtoupper(Str::random(16)),
                    'type' => 'redemption',
                    'amount' => $redeemed,
                    'balance_before' => $remaining,
                    'balance_after' => $balanceAfter,
                    'currency' => $card->currency,
                    'reference_type' => $referenceType,
                    'reference_id' => $referenceId,
                    'usage_context' => $context,
                    'metadata' => json_encode($metadata),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

            return (object) [
                'gift_card_id' => $card->id,
                'transaction_id' => $transactionId,
                'reference' => DB::table('gift_card_transactions')
                    ->where('id', $transactionId)
                    ->value('reference'),
                'amount' => $redeemed,
                'remaining_balance' => $balanceAfter,
                'currency' => $card->currency,
                'status' => $status,
            ];
        });
    }

    protected function assertUsable(
        object $card,
        string $context
    ): void {
        if (!$card->enabled) {
            throw new RuntimeException(
                'This gift card is disabled.'
            );
        }

        if ($card->status !== 'active') {
            throw new RuntimeException(
                'This gift card is not active.'
            );
        }

        if (
            $card->starts_at !== null
            && now()->lt($card->starts_at)
        ) {
            throw new RuntimeException(
                'This gift card is not yet valid.'
            );
        }

        if (
            $card->expires_at !== null
            && now()->gt($card->expires_at)
        ) {
            throw new RuntimeException(
                'This gift card has expired.'
            );
        }

        if (
            $context === 'checkout'
            && !$card->usable_at_checkout
        ) {
            throw new RuntimeException(
                'This gift card cannot be used at checkout.'
            );
        }

        if (
            $context === 'wallet_funding'
            && !$card->usable_for_wallet_funding
        ) {
            throw new RuntimeException(
                'This gift card cannot be used for wallet funding.'
            );
        }
    }

    protected function generateCode(): string
    {
        do {
            $code =
                'ESU-' .
                strtoupper(Str::random(4)) . '-' .
                strtoupper(Str::random(4)) . '-' .
                strtoupper(Str::random(4)) . '-' .
                strtoupper(Str::random(4));
        } while (
            DB::table('gift_cards')
                ->where('code', $code)
                ->exists()
        );

        return $code;
    }
}
