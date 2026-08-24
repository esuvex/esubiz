<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OfflinePaymentReviewController extends Controller
{
    public function receipt(int $attemptId)
    {
        $attempt = DB::table('payment_attempts')
            ->where('id', $attemptId)
            ->whereNull('deleted_at')
            ->first();

        abort_unless($attempt, 404);

        $metadata = $attempt->metadata
            ? json_decode($attempt->metadata, true)
            : [];

        $path = $metadata['receipt_path'] ?? null;

        abort_unless($path, 404, 'No receipt has been submitted.');

        $disk = \Illuminate\Support\Facades\Storage::disk('public');

        abort_unless(
            $disk->exists($path),
            404,
            'Receipt file could not be found.'
        );

        return response()->file(
            $disk->path($path),
            [
                'Content-Disposition' =>
                    'inline; filename="' .
                    basename(
                        $metadata['receipt_original_name']
                        ?? $path
                    ) .
                    '"',
            ]
        );
    }

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

            /*
             * Wallet funding is another consumer of the central offline
             * payment approval system.
             *
             * Once Admin confirms the payment, settle the existing funding
             * through WalletService. creditFunding() performs the balance
             * movement and wallet transaction exactly once.
             */
            $walletFundingId = $metadata['wallet_funding_id']
                ?? $payload['wallet_funding_id']
                ?? null;

            if ($walletFundingId) {
                $funding = \App\Models\WalletFunding::query()
                    ->where('id', (int) $walletFundingId)
                    ->lockForUpdate()
                    ->first();

                abort_unless(
                    $funding,
                    404,
                    'Wallet funding record could not be resolved for this payment.'
                );

                if ($funding->status !== 'successful') {
                    app(\App\Services\Core\WalletService::class)
                        ->creditFunding($funding);
                }

                return;
            }

            /*
             * Unified marketplace offline approval.
             *
             * New unified offline attempts store the canonical marketplace
             * context on the attempt metadata. Keep payload as a fallback
             * for older payment records.
             */
            $orderId = $metadata['marketplace_order_id']
                ?? $payload['marketplace_order_id']
                ?? null;

            abort_unless(
                $orderId,
                422,
                'Marketplace order could not be resolved for this payment.'
            );

            $order = DB::table('marketplace_orders')
                ->where('id', $orderId)
                ->lockForUpdate()
                ->first();

            abort_unless($order, 404);

            /*
             * Complete the commercial order exactly once.
             *
             * Developer Library reads paid completed/fulfilled orders.
             * SaaS fulfilment also receives the same canonical completed
             * order state used by the other unified payment methods.
             */
            if ($order->payment_status !== 'paid') {
                DB::table('marketplace_orders')
                    ->where('id', $order->id)
                    ->update([
                        'payment_status' => 'paid',
                        'status' => 'completed',
                        'updated_at' => now(),
                    ]);
            }

            /*
             * Complete the matching checkout session while preserving the
             * SaaS website/off-server context already stored on the session.
             */
            DB::table('marketplace_checkout_sessions')
                ->where('marketplace_order_id', $order->id)
                ->where('payment_transaction_id', $attempt->payment_transaction_id)
                ->update([
                    'status' => 'completed',
                    'updated_at' => now(),
                ]);

            /*
             * Offline payment is now commercially successful.
             * Record it through the exact same Esubiz financial path used
             * by successful Marketplace payments.
             */
            app(
                \App\Services\Marketplace\MarketplaceFinancialRecorder::class
            )->record(
                (int) $order->id,
                (int) $attempt->payment_transaction_id
            );
        });

        return redirect()
            ->route('admin.payment-gateways.offline.index')
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
            ->route('admin.payment-gateways.offline.index')
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
