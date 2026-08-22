<?php

namespace App\Services\Core;

use App\Models\Wallet;
use App\Models\WalletFunding;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class WalletService
{
    /**
     * Return the user's existing wallet.
     *
     * Wallets are created automatically during account registration.
     */
    public function userWallet(
        int $userId,
        string $currency = 'NGN'
    ): Wallet {
        $wallet = Wallet::query()
            ->where('user_id', $userId)
            ->where('type', 'customer')
            ->where('currency', strtoupper($currency))
            ->where('is_active', true)
            ->first();

        if (!$wallet) {
            throw new RuntimeException(
                'Active user wallet was not found.'
            );
        }

        return $wallet;
    }

    /**
     * Confirm a wallet funding record and credit the existing wallet.
     *
     * This is the common settlement point for online and offline
     * wallet funding. The gateway/payment layer confirms the payment;
     * this method performs the wallet balance movement exactly once.
     *
     * Wallet funding is a wallet movement and does NOT create
     * a revenue_event.
     */
    public function creditFunding(
        WalletFunding $funding,
        ?object $source = null
    ): WalletTransaction {
        return DB::transaction(function () use ($funding, $source) {

            $funding = WalletFunding::query()
                ->lockForUpdate()
                ->findOrFail($funding->id);

            if ($funding->status === 'successful') {
                throw new RuntimeException(
                    'This wallet funding has already been completed.'
                );
            }

            if (!in_array($funding->status, [
                'pending',
                'processing',
            ], true)) {
                throw new RuntimeException(
                    'This wallet funding cannot be completed.'
                );
            }

            $wallet = Wallet::query()
                ->lockForUpdate()
                ->findOrFail($funding->wallet_id);

            if (!$wallet->is_active) {
                throw new RuntimeException(
                    'This wallet is not active.'
                );
            }

            $before = (float) $wallet->available_balance;
            $amount = (float) $funding->net_amount;
            $after = $before + $amount;

            $wallet->update([
                'available_balance' => $after,
            ]);

            $transaction = WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'workspace_id' => $wallet->workspace_id,
                'user_id' => $funding->user_id,
                'uuid' => (string) Str::uuid(),
                'reference' => 'WLT-' . strtoupper(Str::random(16)),
                'type' => 'deposit',
                'direction' => 'credit',
                'amount' => $amount,
                'balance_before' => $before,
                'balance_after' => $after,
                'currency' => $funding->currency,
                'source_type' => $source
                    ? get_class($source)
                    : WalletFunding::class,
                'source_id' => $source?->getKey() ?? $funding->id,
                'status' => 'completed',
                'description' => 'Wallet funding',
                'metadata' => [
                    'funding_id' => $funding->id,
                    'funding_reference' => $funding->reference,
                    'method' => $funding->method,
                    'gateway_reference' => $funding->gateway_reference,
                ],
            ]);

            $funding->update([
                'status' => 'successful',
            ]);

            return $transaction;
        });
    }

    /**
     * Debit an existing wallet for an actual wallet payment.
     *
     * The consuming payment/revenue service is responsible for
     * recording any related business financial event.
     */
    public function debitForPayment(
        Wallet $wallet,
        float $amount,
        ?object $source = null,
        ?string $description = null
    ): WalletTransaction {
        return $this->debit(
            $wallet,
            $amount,
            'payment',
            $source,
            $description ?: 'Wallet payment'
        );
    }

    /**
     * Debit an existing wallet for an approved payout.
     */
    public function debitForPayout(
        Wallet $wallet,
        float $amount,
        ?object $source = null,
        ?string $description = null
    ): WalletTransaction {
        return $this->debit(
            $wallet,
            $amount,
            'payout',
            $source,
            $description ?: 'Wallet payout'
        );
    }

    protected function debit(
        Wallet $wallet,
        float $amount,
        string $type,
        ?object $source = null,
        ?string $description = null
    ): WalletTransaction {
        if ($amount <= 0) {
            throw new RuntimeException(
                'Wallet debit amount must be greater than zero.'
            );
        }

        return DB::transaction(function () use (
            $wallet,
            $amount,
            $type,
            $source,
            $description
        ) {
            $wallet = Wallet::query()
                ->lockForUpdate()
                ->findOrFail($wallet->id);

            if (!$wallet->is_active) {
                throw new RuntimeException(
                    'This wallet is not active.'
                );
            }

            $before = (float) $wallet->available_balance;

            if ($before < $amount) {
                throw new RuntimeException(
                    'Insufficient wallet balance.'
                );
            }

            $after = $before - $amount;

            $wallet->update([
                'available_balance' => $after,
            ]);

            return WalletTransaction::create([
                'wallet_id' => $wallet->id,
                'workspace_id' => $wallet->workspace_id,
                'user_id' => $wallet->user_id,
                'uuid' => (string) Str::uuid(),
                'reference' => 'WLT-' . strtoupper(Str::random(16)),
                'type' => $type,
                'direction' => 'debit',
                'amount' => $amount,
                'balance_before' => $before,
                'balance_after' => $after,
                'currency' => $wallet->currency,
                'source_type' => $source
                    ? get_class($source)
                    : null,
                'source_id' => $source?->getKey(),
                'status' => 'completed',
                'description' => $description,
                'metadata' => [],
            ]);
        });
    }
}
