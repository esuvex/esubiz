<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OfflinePaymentController extends Controller
{
    public function index()
    {
        $methods = DB::table('offline_payment_methods')
            ->whereNull('deleted_at')
            ->orderBy('priority')
            ->orderBy('name')
            ->get();

        /*
         * Offline Payment Management
         *
         * Every offline marketplace payment is represented by a
         * payment_attempt. The attempt is the review/audit record while
         * the payment transaction carries the marketplace order context.
         */
        $offlinePayments = DB::table('payment_attempts as attempts')
            ->join(
                'payment_transactions as transactions',
                'transactions.id',
                '=',
                'attempts.payment_transaction_id'
            )
            ->leftJoin(
                'offline_payment_methods as methods',
                'methods.id',
                '=',
                'attempts.payment_method_id'
            )
            ->whereNull('attempts.deleted_at')
            ->whereRaw(
                "JSON_UNQUOTE(JSON_EXTRACT(attempts.metadata, '$.payment_mode')) = ?",
                ['offline']
            )
            ->select(
                'attempts.id',
                'attempts.status',
                'attempts.amount',
                'attempts.gateway_reference',
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
            ->paginate(10, ['*'], 'offline_page')
            ->through(function ($payment) {
                $payment->metadata = $payment->metadata
                    ? json_decode($payment->metadata, true)
                    : [];

                $payment->payload = $payment->payload
                    ? json_decode($payment->payload, true)
                    : [];

                /*
                 * Determine whether this offline payment belongs to a
                 * Marketplace purchase or Wallet Funding.
                 */
                $payment->wallet_funding_id =
                    $payment->metadata['wallet_funding_id']
                    ?? $payment->payload['wallet_funding_id']
                    ?? null;

                $payment->is_wallet_funding =
                    !empty($payment->wallet_funding_id)
                    || (
                        ($payment->metadata['payment_context'] ?? null)
                        === 'wallet_funding'
                    )
                    || (
                        ($payment->payload['payment_mode'] ?? null)
                        === 'wallet_funding'
                    );

                $payment->order_id =
                    $payment->metadata['marketplace_order_id']
                    ?? $payment->payload['marketplace_order_id']
                    ?? null;

                /*
                 * Marketplace uses buyer_id.
                 * Wallet Funding uses user_id.
                 */
                $payment->buyer_id = $payment->is_wallet_funding
                    ? (
                        $payment->metadata['user_id']
                        ?? $payment->payload['user_id']
                        ?? null
                    )
                    : (
                        $payment->metadata['buyer_id']
                        ?? $payment->payload['buyer_id']
                        ?? null
                    );

                $payment->deployment_type =
                    $payment->metadata['deployment_type']
                    ?? $payment->payload['deployment_type']
                    ?? null;

                $payment->website_id =
                    !$payment->is_wallet_funding
                    && $payment->deployment_type === 'saas'
                        ? (
                            $payment->metadata['website_id']
                            ?? $payment->payload['website_id']
                            ?? null
                        )
                        : null;

                $payment->user = $payment->buyer_id
                    ? DB::table('users')
                        ->where('id', $payment->buyer_id)
                        ->first()
                    : null;

                $payment->funding = $payment->wallet_funding_id
                    ? DB::table('wallet_fundings')
                        ->where(
                            'id',
                            (int) $payment->wallet_funding_id
                        )
                        ->whereNull('deleted_at')
                        ->first()
                    : null;

                $payment->order = !$payment->is_wallet_funding
                    && $payment->order_id
                    ? DB::table('marketplace_orders as orders')
                        ->leftJoin(
                            'marketplace_listings as listings',
                            'listings.id',
                            '=',
                            'orders.marketplace_listing_id'
                        )
                        ->where('orders.id', $payment->order_id)
                        ->select(
                            'orders.id',
                            'orders.reference',
                            'orders.payment_status',
                            'orders.status',
                            'listings.title as product_title',
                            'listings.product_type'
                        )
                        ->first()
                    : null;

                $payment->website = $payment->website_id
                    ? DB::table('websites')
                        ->where('id', $payment->website_id)
                        ->first()
                    : null;

                return $payment;
            });

        return view(
            'admin.offline-payment.index',
            compact('methods', 'offlinePayments')
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => [
                'required',
                'in:bank_transfer,cash,manual,other',
            ],
            'instructions' => ['nullable', 'string'],
            'priority' => ['nullable', 'integer', 'min:1'],
            'receipt_upload_label' => ['nullable', 'string', 'max:255'],
            'receipt_upload_help' => [
                'nullable',
                'string',
                'max:255',
            ],

            'usage_contexts' => [
                'nullable',
                'array',
            ],

            'usage_contexts.*' => [
                'string',
                'in:user_marketplace_checkout,user_wallet_funding,user_checkout_link,developer_marketplace_checkout,developer_wallet_funding,developer_checkout_link',
            ],

            'conversion_type' => [
                'nullable',
                'in:none,fiat,crypto',
            ],

            'conversion_provider' => [
                'nullable',
                'in:frankfurter,coingecko,manual',
            ],

            'conversion_target' => [
                'nullable',
                'string',
                'max:30',
            ],

            'manual_conversion_rate' => [
                'nullable',
                'numeric',
                'gt:0',
            ],

            'markup_type' => [
                'nullable',
                'in:percentage,fixed',
            ],

            'markup_value' => [
                'nullable',
                'numeric',
                'min:0',
            ],
        ]);

        DB::table('offline_payment_methods')->insert([
            'uuid' => (string) Str::uuid(),
            'name' => $data['name'],
            'slug' => Str::slug($data['name']) . '-' . Str::lower(Str::random(6)),
            'type' => $data['type'],
            'instructions' => $data['instructions'] ?? null,
            'receipt_upload_enabled' => $request->boolean('receipt_upload_enabled'),
            'receipt_upload_label' => $data['receipt_upload_label'] ?? null,
            'receipt_upload_help' => $data['receipt_upload_help'] ?? null,
            'settings' => json_encode([]),

            'usage_contexts' => json_encode(
                array_values(
                    array_intersect(
                        $data['usage_contexts'] ?? [],
                        [
                            'user_checkout',
                            'developer_checkout',
                            'marketplace_checkout',
                            'wallet_funding',
                            'checkout_link',
                        ]
                    )
                )
            ),

            'conversion_enabled' =>
                $request->boolean(
                    'conversion_enabled'
                ),

            'conversion_type' =>
                $request->boolean(
                    'conversion_enabled'
                )
                    ? (
                        $data['conversion_type']
                            ?? 'none'
                    )
                    : 'none',

            'conversion_provider' =>
                $request->boolean(
                    'conversion_enabled'
                )
                    ? (
                        $data['conversion_provider']
                            ?? null
                    )
                    : null,

            'conversion_target' =>
                $request->boolean(
                    'conversion_enabled'
                )
                    && !empty(
                        $data['conversion_target']
                    )
                        ? strtoupper(
                            trim(
                                $data[
                                    'conversion_target'
                                ]
                            )
                        )
                        : null,

            'manual_conversion_rate' =>
                (
                    $request->boolean(
                        'conversion_enabled'
                    )
                    && (
                        $data['conversion_provider']
                            ?? null
                    ) === 'manual'
                )
                    ? (
                        isset(
                            $data[
                                'manual_conversion_rate'
                            ]
                        )
                        && $data[
                            'manual_conversion_rate'
                        ] !== ''
                            ? (float) $data[
                                'manual_conversion_rate'
                            ]
                            : null
                    )
                    : null,

            'markup_enabled' =>
                $request->boolean(
                    'markup_enabled'
                ),

            'markup_type' =>
                $data['markup_type']
                    ?? 'percentage',

            'markup_value' =>
                max(
                    0,
                    (float) (
                        $data['markup_value']
                            ?? 0
                    )
                ),

            'is_active' =>
                $request->boolean('is_active'),
            'priority' => $data['priority'] ?? 1,
            'is_default' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('admin.payment-gateways.offline.index')
            ->with('success', 'Offline payment method added successfully.');
    }

    public function update(Request $request, int $method)
    {
        $paymentMethod = DB::table('offline_payment_methods')
            ->where('id', $method)
            ->whereNull('deleted_at')
            ->first();

        abort_unless($paymentMethod, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => [
                'required',
                'in:bank_transfer,cash,manual,other',
            ],
            'instructions' => ['nullable', 'string'],
            'priority' => [
                'nullable',
                'integer',
                'min:1',
            ],

            'usage_contexts' => [
                'nullable',
                'array',
            ],

            'usage_contexts.*' => [
                'string',
                'in:user_marketplace_checkout,user_wallet_funding,user_checkout_link,developer_marketplace_checkout,developer_wallet_funding,developer_checkout_link',
            ],

            'conversion_type' => [
                'nullable',
                'in:none,fiat,crypto',
            ],

            'conversion_provider' => [
                'nullable',
                'in:frankfurter,coingecko,manual',
            ],

            'conversion_target' => [
                'nullable',
                'string',
                'max:30',
            ],

            'manual_conversion_rate' => [
                'nullable',
                'numeric',
                'gt:0',
            ],

            'markup_type' => [
                'nullable',
                'in:percentage,fixed',
            ],

            'markup_value' => [
                'nullable',
                'numeric',
                'min:0',
            ],
        ]);

        $isDefault = $request->boolean('is_default');

        DB::transaction(function () use (
            $method,
            $data,
            $request,
            $isDefault
        ) {
            if ($isDefault) {
                DB::table('offline_payment_methods')
                    ->whereNull('deleted_at')
                    ->update([
                        'is_default' => false,
                        'updated_at' => now(),
                    ]);
            }

            DB::table('offline_payment_methods')
                ->where('id', $method)
                ->update([
                    'name' => $data['name'],
                    'type' => $data['type'],
                    'instructions' => $data['instructions'] ?? null,
                    'receipt_upload_enabled' => $request->boolean('receipt_upload_enabled'),
                    'receipt_upload_label' => $data['receipt_upload_label'] ?? null,
                    'receipt_upload_help' => $data['receipt_upload_help'] ?? null,
                    'usage_contexts' => json_encode(
                        array_values(
                            array_intersect(
                                $data['usage_contexts']
                                    ?? [],
                                [
                                    'user_marketplace_checkout',
                                    'user_wallet_funding',
                                    'user_checkout_link',

                                    'developer_marketplace_checkout',
                                    'developer_wallet_funding',
                                    'developer_checkout_link',
                                ]
                            )
                        )
                    ),

                    'conversion_enabled' =>
                        $request->boolean(
                            'conversion_enabled'
                        ),

                    'conversion_type' =>
                        $request->boolean(
                            'conversion_enabled'
                        )
                            ? (
                                $data[
                                    'conversion_type'
                                ] ?? 'none'
                            )
                            : 'none',

                    'conversion_provider' =>
                        $request->boolean(
                            'conversion_enabled'
                        )
                            ? (
                                $data[
                                    'conversion_provider'
                                ] ?? null
                            )
                            : null,

                    'conversion_target' =>
                        $request->boolean(
                            'conversion_enabled'
                        )
                        && !empty(
                            $data[
                                'conversion_target'
                            ]
                        )
                            ? strtoupper(
                                trim(
                                    $data[
                                        'conversion_target'
                                    ]
                                )
                            )
                            : null,

                    'manual_conversion_rate' =>
                        (
                            $request->boolean(
                                'conversion_enabled'
                            )
                            && (
                                $data[
                                    'conversion_provider'
                                ] ?? null
                            ) === 'manual'
                        )
                            ? (
                                isset(
                                    $data[
                                        'manual_conversion_rate'
                                    ]
                                )
                                && $data[
                                    'manual_conversion_rate'
                                ] !== ''
                                    ? (float) $data[
                                        'manual_conversion_rate'
                                    ]
                                    : null
                            )
                            : null,

                    'markup_enabled' =>
                        $request->boolean(
                            'markup_enabled'
                        ),

                    'markup_type' =>
                        $data['markup_type']
                            ?? 'percentage',

                    'markup_value' =>
                        max(
                            0,
                            (float) (
                                $data[
                                    'markup_value'
                                ] ?? 0
                            )
                        ),

                    'is_active' =>
                        $request->boolean('is_active'),

                    'priority' =>
                        $data['priority'] ?? 1,

                    'is_default' =>
                        $isDefault,

                    'updated_at' => now(),
                ]);
        });

        return redirect()
            ->route('admin.payment-gateways.offline.index')
            ->with('success', 'Offline payment method updated successfully.');
    }

    public function destroy(int $method)
    {
        $paymentMethod = DB::table('offline_payment_methods')
            ->where('id', $method)
            ->whereNull('deleted_at')
            ->first();

        abort_unless($paymentMethod, 404);

        DB::table('offline_payment_methods')
            ->where('id', $method)
            ->update([
                'deleted_at' => now(),
                'updated_at' => now(),
            ]);

        return redirect()
            ->route('admin.payment-gateways.offline.index')
            ->with('success', 'Offline payment method removed successfully.');
    }
}
