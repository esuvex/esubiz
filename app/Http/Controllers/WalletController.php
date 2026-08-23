<?php

namespace App\Http\Controllers;

use App\Services\Core\WalletService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    public function index(Request $request, WalletService $wallets)
    {
        $wallet = $wallets->ensureUserWallet(
            (int) auth()->id(),
            'NGN'
        );

        $transactions = DB::table('wallet_transactions as transactions')
            ->leftJoin(
                'payout_requests as payout_requests',
                function ($join) {
                    $join->on(
                        'payout_requests.id',
                        '=',
                        'transactions.source_id'
                    )
                    ->where(
                        'transactions.source_type',
                        '=',
                        'payout_request'
                    );
                }
            )
            ->where(
                'transactions.wallet_id',
                $wallet->id
            )
            ->select([
                'transactions.*',

                /*
                 * Payout requests have a richer business lifecycle than
                 * wallet_transactions:
                 *
                 * pending -> approved -> completed/rejected.
                 *
                 * Keep the low-level wallet ledger status untouched,
                 * but expose the actual payout status to the Wallet UI.
                 */
                DB::raw(
                    "CASE
                        WHEN transactions.source_type = 'payout_request'
                             AND payout_requests.id IS NOT NULL
                        THEN payout_requests.status
                        ELSE transactions.status
                    END AS display_status"
                ),
            ])
            ->orderByDesc('transactions.created_at')
            ->paginate(
                10,
                ['*'],
                'transactions_page'
            );

        $fundings = DB::table('wallet_fundings')
            ->where('wallet_id', $wallet->id)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $settings = DB::table('wallet_settings')
            ->whereNull('workspace_id')
            ->first();

        $walletFundingContext =
            session('account_mode') === 'developer'
                ? 'developer_wallet_funding'
                : 'user_wallet_funding';

        $onlineGateways = DB::table('payment_providers')
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get()
            ->filter(function ($gateway) use ($walletFundingContext) {
                if ($gateway->usage_contexts === null) {
                    return true;
                }

                $contexts = json_decode(
                    $gateway->usage_contexts,
                    true
                );

                return is_array($contexts)
                    && in_array(
                        $walletFundingContext,
                        $contexts,
                        true
                    );
            })
            ->values();

        $offlineMethods = DB::table('offline_payment_methods')
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->orderBy('priority')
            ->orderBy('name')
            ->get()
            ->filter(function ($method) use ($walletFundingContext) {
                if ($method->usage_contexts === null) {
                    return true;
                }

                $contexts = json_decode(
                    $method->usage_contexts,
                    true
                );

                return is_array($contexts)
                    && in_array(
                        $walletFundingContext,
                        $contexts,
                        true
                    );
            })
            ->values();

        /*
         * User and Developer payout availability is controlled by
         * payout_methods.usage_contexts.
         *
         * NULL = legacy method, allowed everywhere.
         * []   = explicitly allowed nowhere.
         */
        $payoutUsageContext =
            session('account_mode') === 'developer'
                ? 'developer_payout'
                : 'user_payout';

        $payoutMethods = DB::table('payout_methods as methods')
            ->leftJoin(
                'payment_providers as providers',
                'providers.id',
                '=',
                'methods.payment_provider_id'
            )
            ->whereNull('methods.deleted_at')
            ->where('methods.is_active', true)
            ->where(function ($query) {
                $query->whereNull('methods.payment_provider_id')
                    ->orWhere('providers.is_active', true);
            })
            ->select([
                'methods.*',
                'providers.name as provider_name',
                'providers.slug as provider_slug',
            ])
            ->orderByDesc('methods.is_default')
            ->orderBy('methods.priority')
            ->orderBy('methods.name')
            ->get()
            ->filter(function ($method) use ($payoutUsageContext) {
                /*
                 * Existing payout methods created before Allowed Uses
                 * was introduced remain available until Admin saves
                 * an explicit usage selection.
                 */
                if ($method->usage_contexts === null) {
                    return true;
                }

                $contexts = json_decode(
                    $method->usage_contexts,
                    true
                );

                if (!is_array($contexts)) {
                    return false;
                }

                return in_array(
                    $payoutUsageContext,
                    $contexts,
                    true
                );
            })
            ->values();

        return view('wallet.index', compact(
            'wallet',
            'transactions',
            'fundings',
            'settings',
            'onlineGateways',
            'offlineMethods',
            'payoutMethods'
        ));
    }

    public function fund(Request $request, WalletService $wallets)
    {
        $wallet = $wallets->ensureUserWallet(
            (int) auth()->id(),
            'NGN'
        );

        $settings = DB::table('wallet_settings')
            ->whereNull('workspace_id')
            ->first();

        abort_unless(
            !$settings || (bool) $settings->funding_enabled,
            422,
            'Wallet funding is currently unavailable.'
        );

        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'],
            'payment_option' => ['required', 'string', 'max:150'],
        ]);

        $amount = (float) $data['amount'];

        if ($settings) {
            if (
                $settings->minimum_funding !== null
                && $amount < (float) $settings->minimum_funding
            ) {
                abort(
                    422,
                    'Funding amount is below the minimum allowed.'
                );
            }

            if (
                $settings->maximum_funding !== null
                && $amount > (float) $settings->maximum_funding
            ) {
                abort(
                    422,
                    'Funding amount exceeds the maximum allowed.'
                );
            }
        }

        $fee = 0.0;

        if ($settings && (float) $settings->funding_fee > 0) {
            if ($settings->funding_fee_type === 'percentage') {
                $fee = round(
                    $amount * ((float) $settings->funding_fee / 100),
                    2
                );
            } elseif ($settings->funding_fee_type === 'fixed') {
                $fee = (float) $settings->funding_fee;
            }
        }

        [$mode, $identifier] = array_pad(
            explode(':', $data['payment_option'], 2),
            2,
            null
        );

        abort_unless(
            in_array($mode, ['online', 'offline'], true) && $identifier,
            422,
            'Invalid wallet funding payment method.'
        );

        $provider = null;
        $offlineMethod = null;

        if ($mode === 'online') {
            $provider = DB::table('payment_providers')
                ->where('slug', $identifier)
                ->where('is_active', true)
                ->whereNull('deleted_at')
                ->first();

            abort_unless(
                $provider,
                422,
                'Selected payment gateway is unavailable.'
            );

            $requiredFundingContext =
                session('account_mode') === 'developer'
                    ? 'developer_wallet_funding'
                    : 'user_wallet_funding';

            if ($provider->usage_contexts !== null) {
                $allowedContexts = json_decode(
                    $provider->usage_contexts,
                    true
                );

                abort_unless(
                    is_array($allowedContexts)
                    && in_array(
                        $requiredFundingContext,
                        $allowedContexts,
                        true
                    ),
                    422,
                    'This gateway is not enabled for wallet funding in your account mode.'
                );
            }
        }


        if ($mode === 'gift_card') {
            $data = $request->validate([
                'amount' => ['required', 'numeric', 'min:0.01'],
                'gift_card_code' => ['required', 'string', 'max:100'],
            ]);

            $wallet = \App\Models\Wallet::query()
                ->where('user_id', auth()->id())
                ->where('type', 'customer')
                ->where('currency', 'NGN')
                ->where('is_active', true)
                ->whereNull('deleted_at')
                ->firstOrFail();

            $giftCards = app(
                \App\Services\Core\GiftCardService::class
            );

            $validation = $giftCards->validateCard(
                $data['gift_card_code']
            );

            abort_unless(
                $validation['valid'] ?? false,
                422,
                $validation['message'] ?? 'Gift Card is invalid.'
            );

            $card = $validation['card'] ?? null;

            abort_unless($card, 422, 'Gift Card could not be resolved.');

            abort_unless(
                (bool) $card->usable_for_wallet_funding,
                422,
                'This Gift Card cannot be used to fund a wallet.'
            );

            abort_unless(
                strtoupper($card->currency) === strtoupper($wallet->currency),
                422,
                'Gift Card currency does not match wallet currency.'
            );

            abort_unless(
                (float) $card->remaining_balance >= (float) $data['amount'],
                422,
                'Gift Card balance is insufficient.'
            );

            return DB::transaction(function () use (
                $data,
                $wallet,
                $giftCards
            ) {
                $funding = \App\Models\WalletFunding::forceCreate([
                    'wallet_id' => $wallet->id,
                    'workspace_id' => $wallet->workspace_id,
                    'user_id' => auth()->id(),
                    'uuid' => (string) \Illuminate\Support\Str::uuid(),
                    'reference' => 'WFD-' . strtoupper(
                        \Illuminate\Support\Str::random(16)
                    ),
                    'amount' => (float) $data['amount'],
                    'fee' => 0,
                    'net_amount' => (float) $data['amount'],
                    'currency' => strtoupper($wallet->currency),
                    'method' => 'offline',
                    'gateway_reference' => null,
                    'payload' => [
                        'payment_mode' => 'wallet_funding',
                        'funding_payment_mode' => 'gift_card',
                        'gift_card_code' => strtoupper(
                            trim($data['gift_card_code'])
                        ),
                    ],
                    'status' => 'pending',
                ]);

                $redemption = $giftCards->redeem(
                    $data['gift_card_code'],
                    (float) $data['amount'],
                    (int) auth()->id(),
                    'wallet_funding',
                    'wallet_funding',
                    (int) $funding->id,
                    [
                        'wallet_id' => $wallet->id,
                        'funding_reference' => $funding->reference,
                    ]
                );

                $funding->update([
                    'gateway_reference' =>
                        $redemption->reference ?? null,
                    'payload' => array_merge(
                        (array) ($funding->payload ?? []),
                        [
                            'gift_card_id' =>
                                $redemption->gift_card_id ?? null,
                            'gift_card_transaction_id' =>
                                $redemption->id ?? null,
                            'gift_card_reference' =>
                                $redemption->reference ?? null,
                        ]
                    ),
                ]);

                app(
                    \App\Services\Core\WalletService::class
                )->creditFunding(
                    $funding,
                    $redemption
                );

                return redirect()
                    ->route('account.wallet')
                    ->with(
                        'success',
                        'Wallet funded successfully with Gift Card.'
                    );
            });
        }

        if ($mode === 'offline') {
            $offlineMethod = DB::table('offline_payment_methods')
                ->where('id', (int) $identifier)
                ->where('is_active', true)
                ->whereNull('deleted_at')
                ->first();

            abort_unless(
                $offlineMethod,
                422,
                'Selected offline payment method is unavailable.'
            );

            $requiredFundingContext =
                session('account_mode') === 'developer'
                    ? 'developer_wallet_funding'
                    : 'user_wallet_funding';

            if ($offlineMethod->usage_contexts !== null) {
                $allowedContexts = json_decode(
                    $offlineMethod->usage_contexts,
                    true
                );

                abort_unless(
                    is_array($allowedContexts)
                    && in_array(
                        $requiredFundingContext,
                        $allowedContexts,
                        true
                    ),
                    422,
                    'This offline payment method is not enabled for wallet funding in your account mode.'
                );
            }
        }

        /*
         * User-entered amount remains the amount credited to the wallet.
         *
         * funding fee is part of the base payment obligation.
         * Payment gateway conversion and markup are then applied to
         * that payment obligation.
         *
         * Example:
         * NGN 10,000 wallet funding
         * + NGN 100 funding fee
         * = NGN 10,100 source payment
         *
         * Then:
         * conversion -> gateway currency
         * markup -> final gateway charge
         */
        $netAmount = $amount;
        $basePaymentAmount = $amount + $fee;

        $paymentGatewayMethod =
            $mode === 'online'
                ? $provider
                : $offlineMethod;

        try {
            $paymentCalculation = app(
                \App\Services\Core\PaymentConversionService::class
            )->calculate(
                $paymentGatewayMethod,
                strtoupper($wallet->currency ?: 'NGN'),
                $basePaymentAmount
            );
        } catch (\Throwable $e) {
            abort(
                422,
                $e->getMessage()
            );
        }

        $paymentAmount = (float) (
            $paymentCalculation['payment_amount']
                ?? $basePaymentAmount
        );

        $paymentCurrency = strtoupper(
            $paymentCalculation['to_currency']
                ?? $wallet->currency
                ?? 'NGN'
        );

        $funding = \App\Models\WalletFunding::create([
            'wallet_id' => $wallet->id,
            'workspace_id' => $wallet->workspace_id,
            'user_id' => auth()->id(),
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'reference' => 'WFD-' . strtoupper(
                \Illuminate\Support\Str::random(16)
            ),
            'amount' => $amount,
            'fee' => $fee,
            'net_amount' => $netAmount,
            'currency' => $wallet->currency,
            /*
             * wallet_fundings.method is the canonical funding method,
             * not the selected provider identifier.
             *
             * Provider/method IDs remain in payload and payment records.
             */
            'method' => $mode === 'online'
                ? match ($provider->slug) {
                    'paystack' => 'paystack',
                    'flutterwave' => 'flutterwave',
                    'stripe' => 'stripe',
                    'paypal' => 'paypal',
                    'crypto', 'nowpayments' => 'crypto',
                    default => 'card',
                }
                : 'offline',
            'gateway_reference' => null,
            'payload' => [
                'payment_mode' => $mode,
                'payment_provider_id' => $provider?->id,
                'payment_provider' => $provider?->slug,
                'offline_payment_method_id' => $offlineMethod?->id,
                'offline_payment_method' => $offlineMethod?->slug,
                'wallet_credit_amount' => $amount,
                'funding_fee' => $fee,

                'source_payment_amount' =>
                    $basePaymentAmount,

                'payment_total' =>
                    $paymentAmount,

                'payment_currency' =>
                    $paymentCurrency,

                'payment_conversion' =>
                    $paymentCalculation,
            ],
            'status' => 'pending',
        ]);

        $paymentTransactionId = DB::table('payment_transactions')
            ->insertGetId([
                'workspace_id' => $wallet->workspace_id,
                'wallet_id' => $wallet->id,
                'payment_provider_id' => $provider?->id,
                'reference' => 'WFD-TXN-' . strtoupper(
                    \Illuminate\Support\Str::random(12)
                ),
                'amount' => $paymentAmount,
                'currency' => $paymentCurrency,
                'status' => 'pending',
                'payload' => json_encode([
                    'payment_mode' => 'wallet_funding',
                    'funding_payment_mode' => $mode,
                    'wallet_funding_id' => $funding->id,
                    'wallet_id' => $wallet->id,
                    'user_id' => auth()->id(),
                    'funding_reference' => $funding->reference,
                    'offline_payment_method_id' => $offlineMethod?->id,
                    'wallet_credit_amount' => $amount,
                    'funding_fee' => $fee,

                    'source_payment_amount' =>
                        $basePaymentAmount,

                    'source_payment_currency' =>
                        strtoupper(
                            $wallet->currency ?: 'NGN'
                        ),

                    'payment_total' =>
                        $paymentAmount,

                    'payment_currency' =>
                        $paymentCurrency,

                    'payment_conversion' =>
                        $paymentCalculation,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        $attemptService = app(
            \App\Services\Payment\CorePaymentAttemptService::class
        );

        $attempt = $attemptService->create(
            (int) $paymentTransactionId,
            $provider?->id,
            $offlineMethod?->id,
            $paymentAmount,
            [
                'payment_mode' => $mode === 'offline'
                    ? 'offline'
                    : 'wallet_funding',
                'payment_context' => 'wallet_funding',
                'wallet_funding_id' => $funding->id,
                'wallet_id' => $wallet->id,
                'user_id' => auth()->id(),
                'offline_payment_method_id' => $offlineMethod?->id,
            ]
        );

        if ($mode === 'offline') {
            return redirect()->route(
                'marketplace.developer.offline-payment',
                ['attempt' => $attempt->id]
            );
        }

        $gatewayManager = app(
            \App\Services\Payment\CorePaymentGatewayManager::class
        );

        $gateway = $gatewayManager->resolve($provider->slug);

        $customer = DB::table('users')
            ->where('id', auth()->id())
            ->first();

        $credentials = $provider->credentials
            ? (json_decode($provider->credentials, true) ?: [])
            : [];

        $result = $gateway->initialize(
            DB::table('payment_transactions')
                ->where('id', $paymentTransactionId)
                ->first(),
            $attempt,
            [
                'payment_provider' => $provider->slug,
                'secret_key' => $credentials['secret_key'] ?? null,
                'public_key' => $credentials['public_key'] ?? null,
                'email' => $customer->email ?? null,
                'customer_name' => $customer->name ?? null,
                'callback_url' => route(
                    'account.wallet.funding.status',
                    ['funding' => $funding->id]
                ),
                'return_url' => route(
                    'account.wallet.funding.status',
                    ['funding' => $funding->id]
                ),
            ]
        );

        $attemptService->update(
            (int) $attempt->id,
            'processing',
            $result['gateway_reference']
                ?? $result['reference']
                ?? null,
            $result
        );

        $authorizationUrl =
            $result['authorization_url']
            ?? $result['link']
            ?? null;

        abort_unless(
            $authorizationUrl,
            502,
            'Payment gateway did not return a checkout URL.'
        );

        return redirect()->away($authorizationUrl);
    }

    public function fundingStatus(
        int $funding,
        WalletService $wallets
    ) {
        $funding = \App\Models\WalletFunding::query()
            ->where('id', $funding)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if ($funding->status === 'successful') {
            return redirect()
                ->route('account.wallet')
                ->with(
                    'success',
                    'Wallet funding completed successfully.'
                );
        }

        $transaction = DB::table('payment_transactions')
            ->where('wallet_id', $funding->wallet_id)
            ->whereRaw(
                "JSON_UNQUOTE(JSON_EXTRACT(payload, '$.wallet_funding_id')) = ?",
                [(string) $funding->id]
            )
            ->latest('id')
            ->first();

        abort_unless($transaction, 404);

        $attempt = DB::table('payment_attempts')
            ->where(
                'payment_transaction_id',
                $transaction->id
            )
            ->latest('id')
            ->first();

        abort_unless($attempt, 404);

        $provider = DB::table('payment_providers')
            ->where('id', $transaction->payment_provider_id)
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->first();

        abort_unless($provider, 422);

        $gatewayManager = app(
            \App\Services\Payment\CorePaymentGatewayManager::class
        );

        $gateway = $gatewayManager->resolve($provider->slug);

        $credentials = $provider->credentials
            ? (json_decode($provider->credentials, true) ?: [])
            : [];

        $verification = $gateway->verify(
            $transaction,
            $attempt,
            [
                'payment_provider' => $provider->slug,
                'secret_key' => $credentials['secret_key'] ?? null,
                'public_key' => $credentials['public_key'] ?? null,
            ]
        );

        $verifiedStatus = strtolower(
            (string) (
                $verification['status']
                ?? $verification['payment_status']
                ?? ''
            )
        );

        if (in_array(
            $verifiedStatus,
            ['success', 'successful', 'completed', 'paid'],
            true
        )) {
            DB::transaction(function () use (
                $funding,
                $transaction,
                $attempt,
                $verification,
                $wallets
            ) {
                DB::table('payment_attempts')
                    ->where('id', $attempt->id)
                    ->update([
                        'status' => 'successful',
                        'gateway_response' => json_encode($verification),
                        'updated_at' => now(),
                    ]);

                DB::table('payment_transactions')
                    ->where('id', $transaction->id)
                    ->update([
                        'status' => 'successful',
                        'updated_at' => now(),
                    ]);

                $funding->update([
                    'status' => 'processing',
                    'gateway_reference' =>
                        $verification['gateway_reference']
                        ?? $verification['reference']
                        ?? $funding->gateway_reference,
                ]);

                $wallets->creditFunding(
                    $funding->fresh(),
                    null
                );
            });

            return redirect()
                ->route('account.wallet')
                ->with(
                    'success',
                    'Wallet funded successfully.'
                );
        }

        return redirect()
            ->route('account.wallet')
            ->with(
                'error',
                'Wallet funding has not been confirmed yet.'
            );
    }

    public function validateGiftCardFunding(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:100'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['required', 'string', 'size:3'],
        ]);

        try {
            $giftCards = app(
                \App\Services\Core\GiftCardService::class
            );

            $card = $giftCards->validateCard(
                $data['code']
            );

            if (!($card['valid'] ?? false)) {
                return response()->json([
                    'valid' => false,
                    'message' => $card['message']
                        ?? 'Gift Card is invalid.',
                ], 422);
            }

            $giftCard = $card['card'] ?? null;

            if (!$giftCard) {
                return response()->json([
                    'valid' => false,
                    'message' => 'Gift Card could not be resolved.',
                ], 422);
            }

            if (!(bool) $giftCard->usable_for_wallet_funding) {
                return response()->json([
                    'valid' => false,
                    'message' =>
                        'This Gift Card cannot be used to fund a wallet.',
                    'balance' =>
                        (float) $giftCard->remaining_balance,
                    'currency' => $giftCard->currency,
                ], 422);
            }

            if (
                strtoupper($giftCard->currency)
                !== strtoupper($data['currency'])
            ) {
                return response()->json([
                    'valid' => false,
                    'message' =>
                        'Gift Card currency does not match your wallet.',
                    'balance' =>
                        (float) $giftCard->remaining_balance,
                    'currency' => $giftCard->currency,
                ], 422);
            }

            if (
                (float) $giftCard->remaining_balance
                < (float) $data['amount']
            ) {
                return response()->json([
                    'valid' => false,
                    'message' =>
                        'Gift Card balance is insufficient.',
                    'balance' =>
                        (float) $giftCard->remaining_balance,
                    'currency' => $giftCard->currency,
                ], 422);
            }

            return response()->json([
                'valid' => true,
                'message' =>
                    'Gift Card is valid for wallet funding.',
                'balance' =>
                    (float) $giftCard->remaining_balance,
                'currency' => $giftCard->currency,
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'valid' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }



    public function requestPayout(
        Request $request,
        WalletService $wallets
    ) {
        $data = $request->validate([
            'payout_method_id' => ['required', 'integer'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payout_currency' => ['required', 'string', 'max:20'],
            'account_details' => ['nullable', 'string', 'max:5000'],
            'payout_fields' => ['nullable', 'array'],
            'payout_fields.*' => ['nullable'],
        ]);

        $settings = DB::table('wallet_settings')
            ->whereNull('workspace_id')
            ->first();

        abort_unless(
            !$settings || (bool) $settings->payout_enabled,
            422,
            'Wallet withdrawals are currently unavailable.'
        );

        $wallet = $wallets->ensureUserWallet(
            (int) auth()->id(),
            'NGN'
        );

        $method = DB::table('payout_methods as methods')
            ->leftJoin(
                'payment_providers as providers',
                'providers.id',
                '=',
                'methods.payment_provider_id'
            )
            ->where('methods.id', (int) $data['payout_method_id'])
            ->where('methods.is_active', true)
            ->whereNull('methods.deleted_at')
            ->where(function ($query) {
                $query->whereNull('methods.payment_provider_id')
                    ->orWhere('providers.is_active', true);
            })
            ->select([
                'methods.*',
                'providers.slug as provider_slug',
                'providers.name as provider_name',
            ])
            ->first();

        abort_unless(
            $method,
            422,
            'Selected payout method is unavailable.'
        );

        /*
         * Enforce Allowed Payout Uses server-side as well.
         * This prevents a hidden/disabled payout method from being
         * submitted directly by manipulating the request.
         */
        $requiredPayoutContext =
            session('account_mode') === 'developer'
                ? 'developer_payout'
                : 'user_payout';

        if ($method->usage_contexts !== null) {
            $allowedPayoutContexts = json_decode(
                $method->usage_contexts,
                true
            );

            abort_unless(
                is_array($allowedPayoutContexts)
                && in_array(
                    $requiredPayoutContext,
                    $allowedPayoutContexts,
                    true
                ),
                422,
                'This payout method is not available for your account type.'
            );
        }

        /*
         * Validate Admin-configured custom payout fields.
         */
        $configuredFields = json_decode(
            $method->form_fields ?? '[]',
            true
        ) ?: [];

        $submittedFields = $data['payout_fields'] ?? [];
        $resolvedFields = [];

        foreach ($configuredFields as $field) {
            $key = $field['key'] ?? null;

            if (!$key) {
                continue;
            }

            $type = $field['type'] ?? 'text';
            $required = (bool) ($field['required'] ?? false);

            /*
             * Instruction-only fields do not collect user input.
             */
            if ($type === 'instructions') {
                continue;
            }

            $value = $submittedFields[$key] ?? null;

            if ($required) {
                abort_if(
                    $value === null
                    || $value === ''
                    || $value === [],
                    422,
                    ($field['label'] ?? 'Required field')
                        . ' is required.'
                );
            }

            if ($value === null || $value === '') {
                continue;
            }

            if ($type === 'email') {
                abort_unless(
                    filter_var($value, FILTER_VALIDATE_EMAIL),
                    422,
                    ($field['label'] ?? 'Email')
                        . ' must be a valid email address.'
                );
            }

            if ($type === 'number') {
                abort_unless(
                    is_numeric($value),
                    422,
                    ($field['label'] ?? 'Number')
                        . ' must be numeric.'
                );
            }

            if ($type === 'url') {
                abort_unless(
                    filter_var($value, FILTER_VALIDATE_URL),
                    422,
                    ($field['label'] ?? 'URL')
                        . ' must be a valid URL.'
                );
            }

            if (
                in_array(
                    $type,
                    ['select', 'radio', 'checkbox'],
                    true
                )
                && !empty($field['options'])
            ) {
                $allowed = $field['options'];

                if (is_array($value)) {
                    foreach ($value as $selected) {
                        abort_unless(
                            in_array($selected, $allowed, true),
                            422,
                            'Invalid option selected for '
                                . ($field['label'] ?? 'field')
                                . '.'
                        );
                    }
                } else {
                    abort_unless(
                        in_array($value, $allowed, true),
                        422,
                        'Invalid option selected for '
                            . ($field['label'] ?? 'field')
                            . '.'
                    );
                }
            }

            $resolvedFields[] = [
                'key' => $key,
                'label' => $field['label'] ?? $key,
                'type' => $type,
                'value' => $value,
            ];
        }

        $amount = round((float) $data['amount'], 2);

        $globalMinimum = (float) (
            $settings->minimum_payout ?? 0
        );

        $globalMaximum = !empty($settings->maximum_payout)
            ? (float) $settings->maximum_payout
            : null;

        $methodMinimum = (float) (
            $method->minimum_amount ?? 0
        );

        $methodMaximum = $method->maximum_amount !== null
            ? (float) $method->maximum_amount
            : null;

        $minimum = max(
            $globalMinimum,
            $methodMinimum
        );

        $maximums = array_values(
            array_filter(
                [
                    $globalMaximum,
                    $methodMaximum,
                ],
                fn ($value) => $value !== null
            )
        );

        $maximum = $maximums
            ? min($maximums)
            : null;

        abort_unless(
            $amount >= $minimum,
            422,
            'Payout amount is below the minimum allowed.'
        );

        if ($maximum !== null) {
            abort_unless(
                $amount <= $maximum,
                422,
                'Payout amount exceeds the maximum allowed.'
            );
        }

        $supportedCurrencies = json_decode(
            $method->supported_currencies ?? '[]',
            true
        ) ?: [];

        $targetCurrency = strtoupper(
            trim($data['payout_currency'])
        );

        if ($supportedCurrencies) {
            abort_unless(
                in_array(
                    $targetCurrency,
                    array_map('strtoupper', $supportedCurrencies),
                    true
                ),
                422,
                'Selected payout currency is not supported by this method.'
            );
        }

        $globalFee = 0;

        if ($settings) {
            if ($settings->payout_fee_type === 'fixed') {
                $globalFee =
                    (float) $settings->payout_fee;
            } elseif (
                $settings->payout_fee_type === 'percentage'
            ) {
                $globalFee =
                    $amount
                    * ((float) $settings->payout_fee / 100);
            }
        }

        $methodFee =
            (float) $method->fixed_fee
            + (
                $amount
                * ((float) $method->percentage_fee / 100)
            );

        $fee = round(
            $globalFee + $methodFee,
            2
        );

        /*
         * Payout fee is additionally charged to wallet.
         */
        $walletDebit = round(
            $amount + $fee,
            2
        );

        abort_unless(
            (float) $wallet->available_balance >= $walletDebit,
            422,
            'Insufficient wallet balance for this payout and fee.'
        );

        $currency = DB::table('currencies')
            ->where('code', $wallet->currency)
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->first();

        return DB::transaction(function () use (
            $wallets,
            $wallet,
            $method,
            $amount,
            $fee,
            $walletDebit,
            $targetCurrency,
            $data,
            $currency,
            $resolvedFields
        ) {
            $wallets->reserveForPayout(
                $wallet,
                $walletDebit
            );

            $reference =
                'PYO-' . strtoupper(
                    \Illuminate\Support\Str::random(16)
                );

            $payoutRequestId = DB::table('payout_requests')
                ->insertGetId([
                'user_id' => auth()->id(),
                'workspace_id' => $wallet->workspace_id,
                'payout_method_id' => $method->id,
                'payout_id' => null,
                'uuid' => (string)
                    \Illuminate\Support\Str::uuid(),
                'reference' => $reference,
                'currency_id' => $currency->id ?? null,
                'requested_amount' => $amount,
                'approved_amount' => null,
                'reviewed_by' => null,
                'reviewed_at' => null,
                'status' => $method->payment_provider_id
                    ? 'processing'
                    : 'pending',
                'rejection_reason' => null,
                'notes' => null,
                'metadata' => json_encode([
                    'wallet_id' => $wallet->id,
                    'wallet_currency' => $wallet->currency,
                    'requested_payout_amount' => $amount,
                    'payout_currency' => $targetCurrency,
                    'fee' => $fee,
                    'reserved_wallet_amount' => $walletDebit,
                    'payout_method_name' => $method->name,
                    'payout_method_type' => $method->type,
                    'payment_provider_id' =>
                        $method->payment_provider_id,
                    'payment_provider' =>
                        $method->provider_slug,
                    'processing_mode' =>
                        $method->payment_provider_id
                            ? 'automatic'
                            : 'manual',
                    'account_details' =>
                        trim((string) ($data['account_details'] ?? '')),

                    'payout_fields' =>
                        $resolvedFields,

                    'account_mode' =>
                        session('account_mode', 'user'),
                ]),
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ]);

            /*
             * Immediately expose the payout request in the same Wallet
             * Transaction log used for credits and completed debits.
             *
             * The balance has already moved from available to reserved.
             * This row therefore represents the pending withdrawal.
             */
            $reservedWallet = DB::table('wallets')
                ->where('id', $wallet->id)
                ->first();

            DB::table('wallet_transactions')->insert([
                'wallet_id' => $wallet->id,
                'workspace_id' => $wallet->workspace_id,
                'user_id' => auth()->id(),
                'uuid' => (string)
                    \Illuminate\Support\Str::uuid(),
                'reference' => $reference,
                'type' => 'payout',
                'direction' => 'debit',
                'amount' => $walletDebit,
                'balance_before' =>
                    (float) $reservedWallet->available_balance
                    + $walletDebit,
                'balance_after' =>
                    (float) $reservedWallet->available_balance,
                'currency' => $wallet->currency,
                'source_type' => 'payout_request',
                'source_id' => $payoutRequestId,
                'status' => $method->payment_provider_id
                    ? 'processing'
                    : 'pending',
                'description' =>
                    'Payout request - ' . $method->name,
                'metadata' => json_encode([
                    'payout_request_id' => $payoutRequestId,
                    'requested_amount' => $amount,
                    'fee' => $fee,
                    'reserved_wallet_amount' => $walletDebit,
                    'payout_method' => $method->name,
                    'payout_currency' => $targetCurrency,
                ]),
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
            ]);

            return redirect()
                ->route('account.wallet')
                ->with(
                    'success',
                    $method->payment_provider_id
                        ? 'Payout request submitted for automatic processing.'
                        : 'Payout request submitted and is awaiting review.'
                );
        });
    }


    public function previewPayoutConversion(
        Request $request,
        \App\Services\Core\PayoutConversionService $converter
    ) {
        $data = $request->validate([
            'payout_method_id' => [
                'required',
                'integer',
            ],
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
            ],
        ]);

        $wallet = \App\Models\Wallet::query()
            ->where('user_id', auth()->id())
            ->where('type', 'customer')
            ->where('currency', 'NGN')
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->firstOrFail();

        $method = DB::table('payout_methods')
            ->where(
                'id',
                (int) $data['payout_method_id']
            )
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->first();

        abort_unless(
            $method,
            422,
            'Selected payout method is unavailable.'
        );

        try {
            $conversion = $converter->convert(
                $method,
                $wallet->currency,
                (float) $data['amount']
            );

            /*
             * Never expose Admin provider/API configuration.
             */
            return response()->json([
                'success' => true,
                'converted' =>
                    (bool) $conversion['converted'],
                'from_currency' =>
                    $conversion['from_currency'],
                'to_currency' =>
                    $conversion['to_currency'],
                'source_amount' =>
                    $conversion['source_amount'],
                'rate' =>
                    $conversion['rate'],
                'receive_amount' =>
                    $conversion['receive_amount'],
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Unable to calculate the payout conversion right now.',
            ], 422);
        }
    }

}
