<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class OfflinePaymentReviewController extends Controller
{
    public function markPaid(int $attemptId)
    {
        DB::transaction(function () use ($attemptId) {
            $attempt = DB::table('payment_attempts')
                ->where('id', $attemptId)
                ->whereNull('deleted_at')
                ->first();

            abort_unless($attempt, 404);

            $metadata = $attempt->metadata
                ? json_decode($attempt->metadata, true)
                : [];

            abort_unless(
                ($metadata['payment_mode'] ?? null) === 'offline',
                403
            );

            abort_unless(
                in_array($attempt->status, ['initiated', 'processing'], true),
                422,
                'This payment is no longer awaiting confirmation.'
            );

            DB::table('payment_attempts')
                ->where('id', $attempt->id)
                ->update([
                    'status' => 'successful',
                    'gateway_reference' =>
                        $attempt->gateway_reference
                        ?? 'OFFLINE-' . strtoupper(Str::random(12)),
                    'updated_at' => now(),
                ]);

            DB::table('payment_transactions')
                ->where('id', $attempt->payment_transaction_id)
                ->update([
                    'status' => 'successful',
                    'updated_at' => now(),
                ]);

            $transaction = DB::table('payment_transactions')
                ->where('id', $attempt->payment_transaction_id)
                ->first();

            if (!$transaction) {
                return;
            }

            $payload = $transaction->payload
                ? json_decode($transaction->payload, true)
                : [];

            $orderId = $payload['marketplace_order_id'] ?? null;

            if ($orderId) {
                DB::table('marketplace_orders')
                    ->where('id', $orderId)
                    ->where('payment_status', 'pending')
                    ->update([
                        'payment_status' => 'paid',
                        'status' => 'processing',
                        'updated_at' => now(),
                    ]);
            }
        });

        return redirect()
            ->route('admin.payment-gateways.offline-payments.index')
            ->with('success', 'Offline payment marked as paid successfully.');
    }

    public function reject(int $attemptId)
    {
        DB::transaction(function () use ($attemptId) {
            $attempt = DB::table('payment_attempts')
                ->where('id', $attemptId)
                ->whereNull('deleted_at')
                ->first();

            abort_unless($attempt, 404);

            $metadata = $attempt->metadata
                ? json_decode($attempt->metadata, true)
                : [];

            abort_unless(
                ($metadata['payment_mode'] ?? null) === 'offline',
                403
            );

            abort_unless(
                in_array($attempt->status, ['initiated', 'processing'], true),
                422,
                'This payment is no longer awaiting confirmation.'
            );

            DB::table('payment_attempts')
                ->where('id', $attempt->id)
                ->update([
                    'status' => 'failed',
                    'failure_code' => 'offline_payment_rejected',
                    'failure_reason' => 'Offline payment rejected by administrator.',
                    'updated_at' => now(),
                ]);

            DB::table('payment_transactions')
                ->where('id', $attempt->payment_transaction_id)
                ->update([
                    'status' => 'failed',
                    'updated_at' => now(),
                ]);
        });

        return redirect()
            ->route('admin.payment-gateways.offline-payments.index')
            ->with('success', 'Offline payment rejected.');
    }

    public function index()
    {
        $payments = DB::table('payment_attempts as attempts')
            ->join(
                'payment_transactions as transactions',
                'transactions.id',
                '=',
                'attempts.payment_transaction_id'
            )
            ->leftJoin(
                'payment_methods as methods',
                'methods.id',
                '=',
                'attempts.payment_method_id'
            )
            ->whereNull('attempts.deleted_at')
            ->whereIn('attempts.status', [
                'initiated',
                'processing',
                'failed',
                'successful',
            ])
            ->whereRaw(
                "JSON_UNQUOTE(JSON_EXTRACT(attempts.metadata, '$.payment_mode')) = ?",
                ['offline']
            )
            ->select(
                'attempts.id',
                'attempts.uuid',
                'attempts.status',
                'attempts.amount',
                'attempts.gateway_reference',
                'attempts.gateway_response',
                'attempts.metadata',
                'attempts.created_at',
                'attempts.updated_at',
                'transactions.reference as transaction_reference',
                'transactions.currency',
                'transactions.status as transaction_status',
                'transactions.payload',
                'methods.name as payment_method_name',
                'methods.type as payment_method_type'
            )
            ->orderByDesc('attempts.created_at')
            ->get()
            ->map(function ($payment) {
                $payment->metadata = $payment->metadata
                    ? json_decode($payment->metadata, true)
                    : [];

                $payment->payload = $payment->payload
                    ? json_decode($payment->payload, true)
                    : [];

                return $payment;
            });

        return view(
            'admin.offline-payments.index',
            compact('payments')
        );
    }
}
