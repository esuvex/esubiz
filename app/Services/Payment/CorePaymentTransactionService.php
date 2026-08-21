<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class CorePaymentTransactionService
{
    public function create(array $data): object
    {
        $reference = $data['reference']
            ?? 'PAY-' . strtoupper(Str::random(16));

        $id = DB::table('payment_transactions')->insertGetId([
            'workspace_id' => $data['workspace_id'] ?? 0,
            'wallet_id' => $data['wallet_id'] ?? null,
            'payment_provider_id' => $data['payment_provider_id'] ?? null,
            'reference' => $reference,
            'amount' => $data['amount'],
            'currency' => strtoupper($data['currency'] ?? 'NGN'),
            'status' => $data['status'] ?? 'pending',
            'payload' => isset($data['payload'])
                ? json_encode($data['payload'])
                : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('payment_transactions')
            ->where('id', $id)
            ->first();
    }

    public function updateStatus(
        int $transactionId,
        string $status,
        array $payload = []
    ): object {
        $transaction = DB::table('payment_transactions')
            ->where('id', $transactionId)
            ->first();

        if (!$transaction) {
            throw new RuntimeException(
                "Payment transaction {$transactionId} not found."
            );
        }

        DB::table('payment_transactions')
            ->where('id', $transactionId)
            ->update([
                'status' => $status,
                'payload' => $payload
                    ? json_encode($payload)
                    : $transaction->payload,
                'updated_at' => now(),
            ]);

        return DB::table('payment_transactions')
            ->where('id', $transactionId)
            ->first();
    }
}
