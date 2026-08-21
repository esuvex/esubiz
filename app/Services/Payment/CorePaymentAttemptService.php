<?php

namespace App\Services\Payment;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class CorePaymentAttemptService
{
    public function create(
        int $transactionId,
        ?int $providerId = null,
        ?int $paymentMethodId = null,
        ?float $amount = null,
        array $metadata = []
    ): object {
        $transaction = DB::table('payment_transactions')
            ->where('id', $transactionId)
            ->first();

        if (!$transaction) {
            throw new RuntimeException(
                "Payment transaction {$transactionId} not found."
            );
        }

        $attemptNumber = ((int) DB::table('payment_attempts')
            ->where('payment_transaction_id', $transactionId)
            ->max('attempt_number')) + 1;

        $id = DB::table('payment_attempts')->insertGetId([
            'payment_transaction_id' => $transactionId,
            'payment_provider_id' => $providerId,
            'payment_method_id' => $paymentMethodId,
            'uuid' => (string) Str::uuid(),
            'attempt_number' => $attemptNumber,
            'gateway_reference' => null,
            'gateway_response' => null,
            'amount' => $amount ?? $transaction->amount,
            'status' => 'initiated',
            'failure_code' => null,
            'failure_reason' => null,
            'metadata' => $metadata
                ? json_encode($metadata)
                : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DB::table('payment_attempts')
            ->where('id', $id)
            ->first();
    }

    public function update(
        int $attemptId,
        string $status,
        ?string $gatewayReference = null,
        array $gatewayResponse = [],
        ?string $failureCode = null,
        ?string $failureReason = null
    ): object {
        $attempt = DB::table('payment_attempts')
            ->where('id', $attemptId)
            ->first();

        if (!$attempt) {
            throw new RuntimeException(
                "Payment attempt {$attemptId} not found."
            );
        }

        DB::table('payment_attempts')
            ->where('id', $attemptId)
            ->update([
                'status' => $status,
                'gateway_reference' =>
                    $gatewayReference ?? $attempt->gateway_reference,
                'gateway_response' => $gatewayResponse
                    ? json_encode($gatewayResponse)
                    : $attempt->gateway_response,
                'failure_code' =>
                    $failureCode ?? $attempt->failure_code,
                'failure_reason' =>
                    $failureReason ?? $attempt->failure_reason,
                'updated_at' => now(),
            ]);

        return DB::table('payment_attempts')
            ->where('id', $attemptId)
            ->first();
    }
}
