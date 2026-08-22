<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class MarketplaceController extends Controller
{
    public function index()
    {
        return view('marketplace.index', [
            'addons' => DB::table('core_addons')
                ->where('is_active', true)
                ->whereNull('deleted_at')
                ->orderBy('name')
                ->get(),

            'bundles' => DB::table('core_addon_bundles')
                ->where('is_active', true)
                ->whereNull('deleted_at')
                ->orderBy('name')
                ->get(),

            'bundleItems' => DB::table('core_addon_bundle_items as items')
                ->join('core_addons as addons', 'addons.id', '=', 'items.addon_id')
                ->whereIn(
                    'items.bundle_id',
                    DB::table('core_addon_bundles')
                        ->where('is_active', true)
                        ->whereNull('deleted_at')
                        ->pluck('id')
                )
                ->where('addons.is_active', true)
                ->whereNull('addons.deleted_at')
                ->select(
                    'items.bundle_id',
                    'addons.id as addon_id',
                    'addons.name',
                    'items.allocation',
                    'items.is_unlimited'
                )
                ->orderBy('addons.name')
                ->get()
                ->groupBy('bundle_id'),
        ]);
    }


    public function developerCheckout(string $productType, int $productId)
    {
        if (!in_array($productType, ['addon', 'bundle'], true)) {
            abort(404);
        }

        $table = $productType === 'addon'
            ? 'core_addons'
            : 'core_addon_bundles';

        $product = DB::table($table)
            ->where('id', $productId)
            ->where('is_active', true)
            ->where('off_server_available', true)
            ->whereNull('deleted_at')
            ->first();

        if (!$product) {
            abort(404);
        }

        /*
         * Online checkout options are represented once per enabled
         * payment provider. Individual payment channels such as card
         * and bank transfer remain available inside the provider.
         */
        $onlineGateways = DB::table('payment_providers as providers')
            ->join(
                'payment_methods as methods',
                'methods.payment_provider_id',
                '=',
                'providers.id'
            )
            ->where('providers.is_active', true)
            ->whereNull('providers.deleted_at')
            ->where('methods.is_active', true)
            ->whereNull('methods.deleted_at')
            ->select(
                'providers.id',
                'providers.uuid',
                'providers.name',
                'providers.slug',
                'providers.type'
            )
            ->groupBy(
                'providers.id',
                'providers.uuid',
                'providers.name',
                'providers.slug',
                'providers.type'
            )
            ->orderBy('providers.name')
            ->get();

        /*
         * Offline methods are independent payment options and are
         * therefore loaded directly from offline_payment_methods.
         */
        $offlineMethods = DB::table('offline_payment_methods')
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->orderBy('priority')
            ->orderBy('name')
            ->get();

        return view('marketplace.checkout', [
            'website' => !empty($order->website_id)
                ? DB::table('websites')
                    ->where('id', $order->website_id)
                    ->first()
                : null,
            'product' => $product,
            'productType' => $productType,
            'onlineGateways' => $onlineGateways,
            'offlineMethods' => $offlineMethods,
        ]);
    }

    public function developerCheckoutSubmit(Request $request)
    {
        $data = $request->validate([
            'product_type' => ['required', 'string', 'max:100'],
            'product_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'min:1'],
            'payment_option' => ['required', 'string', 'max:255'],
        ]);

        $listingProductType = match ($data['product_type']) {
            'addon' => 'core_addon',
            'bundle' => 'core_bundle',
            default => $data['product_type'],
        };

        $listing = $this->resolveMarketplaceListing(
            $listingProductType,
            (int) $data['product_id']
        );

        $productTable = match ($data['product_type']) {
            'addon' => 'core_addons',
            'bundle' => 'core_addon_bundles',
            default => null,
        };

        if (!$productTable) {
            abort(422, 'Unsupported marketplace product type.');
        }

        $product = DB::table($productTable)
            ->where('id', $data['product_id'])
            ->where('is_active', true)
            ->where('off_server_available', true)
            ->whereNull('deleted_at')
            ->first();

        abort_unless($product, 404);

        /*
         * The marketplace listing is the commercial representation of the
         * product. If a developer-enabled Core product does not yet have
         * its marketplace listing, resolve the published listing from the
         * canonical product identity before continuing.
         */
        if (!$listing) {
            $listing = $this->resolveMarketplaceListing(
                $listingProductType,
                (int) $product->id
            );
        }

        abort_unless(
            $listing,
            422,
            'This developer product is not yet available as a marketplace listing.'
        );

        $unitPrice = (float) ($product->off_server_price ?? 0);
        $amount = $unitPrice * (int) $data['quantity'];
        $currency = $product->off_server_currency ?? 'NGN';

        $optionParts = explode(':', $data['payment_option'], 2);

        abort_unless(
            count($optionParts) === 2 &&
            in_array($optionParts[0], ['online', 'offline'], true),
            422,
            'Invalid payment option.'
        );

        [$paymentMode, $paymentIdentifier] = $optionParts;

        $paymentProvider = null;
        $paymentMethod = null;

        if ($paymentMode === 'online') {
            $paymentProvider = DB::table('payment_providers')
                ->where('slug', $paymentIdentifier)
                ->where('is_active', true)
                ->whereNull('deleted_at')
                ->first();

            abort_unless(
                $paymentProvider,
                422,
                'Selected payment gateway is unavailable.'
            );
        }

        if ($paymentMode === 'offline') {
            $paymentMethod = DB::table('offline_payment_methods')
                ->where('id', (int) $paymentIdentifier)
                ->where('is_active', true)
                ->whereNull('deleted_at')
                ->first();

            abort_unless(
                $paymentMethod,
                422,
                'Selected offline payment method is unavailable.'
            );
        }

        $orderReference = 'DEV-' . strtoupper(
            \Illuminate\Support\Str::random(12)
        );

        /*
         * Developer/off-server marketplace purchases do not require an
         * Esubiz workspace. Their financial context is the marketplace
         * order itself.
         *
         * This is intentionally separate from User/SaaS checkout.
         */
        $developerWorkspaceId = $listing->workspace_id ?? null;

        $pendingOrder = DB::table('marketplace_orders')->insertGetId([
            'marketplace_listing_id' => $listing->id,
            'vendor_id' => $listing->vendor_id,
            'workspace_id' => $developerWorkspaceId,
            'buyer_id' => auth()->id(),
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'reference' => $orderReference,
            'amount' => $amount,
            'commission_amount' => 0,
            'vendor_amount' => $amount,
            'currency' => $currency,
            'payment_status' => 'pending',
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $checkoutSessionId = DB::table('marketplace_checkout_sessions')
            ->insertGetId([
                'user_id' => auth()->id(),
                'account_mode' => 'developer',
                'product_type' => $data['product_type'],
                'product_id' => $data['product_id'],
                'quantity' => (int) $data['quantity'],
                'unit_price' => $unitPrice,
                'total_amount' => $amount,
                'currency' => $currency,
                'payment_method_id' => null,
                'payment_provider_id' => $paymentProvider?->id,
                'marketplace_order_id' => $pendingOrder,
                'deployment_type' => 'off_server',
                'status' => 'pending_payment',
                'is_commissionable' => true,
                'expires_at' => now()->addHours(24),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        $transactionReference = 'DEV-TXN-' . strtoupper(
            \Illuminate\Support\Str::random(12)
        );

        $paymentTransactionId = DB::table('payment_transactions')
            ->insertGetId([
                'workspace_id' => $developerWorkspaceId,
                'wallet_id' => null,
                'payment_provider_id' => $paymentProvider?->id,
                'reference' => $transactionReference,
                'amount' => $amount,
                'currency' => $currency,
                'status' => 'pending',
                'payload' => json_encode([
                    'marketplace_order_id' => $pendingOrder,
                    'marketplace_order_reference' => $orderReference,
                    'payment_mode' => $paymentMode,
                    'payment_provider' => $paymentProvider?->slug,
                    'payment_provider_id' => $paymentProvider?->id,
                    'offline_payment_method_id' => $paymentMethod?->id,
                    'offline_payment_method' => $paymentMethod?->slug,
                    'product_type' => $data['product_type'],
                    'product_id' => $data['product_id'],
                    'quantity' => $data['quantity'],
                    'buyer_id' => auth()->id(),
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        DB::table('marketplace_checkout_sessions')
            ->where('id', $checkoutSessionId)
            ->update([
                'payment_transaction_id' => $paymentTransactionId,
                'updated_at' => now(),
            ]);

        /*
         * Create the payment attempt before contacting the gateway.
         */
        $attemptService = app(
            \App\Services\Payment\CorePaymentAttemptService::class
        );

        $attemptMetadata = [
            'marketplace_order_id' => $pendingOrder,
            'marketplace_order_reference' => $orderReference,
            'product_type' => $data['product_type'],
            'product_id' => $data['product_id'],
            'quantity' => $data['quantity'],
            'buyer_id' => auth()->id(),
            'payment_mode' => $paymentMode,
            'offline_payment_method_id' => $paymentMethod?->id,
        ];

        $attempt = $attemptService->create(
            (int) $paymentTransactionId,
            $paymentProvider?->id,
            null,
            (float) $amount,
            $attemptMetadata
        );

        /*
         * ONLINE:
         * Initialize the selected gateway and redirect directly to
         * the gateway's hosted payment page.
         */
        if ($paymentMode === 'online') {
            $gatewayManager = app(
                \App\Services\Payment\CorePaymentGatewayManager::class
            );

            $gateway = $gatewayManager->resolve(
                $paymentProvider->slug
            );

            $customer = DB::table('users')
                ->where('id', auth()->id())
                ->first();

            $providerCredentials = $paymentProvider->credentials
                ? (json_decode($paymentProvider->credentials, true) ?: [])
                : [];

            $result = $gateway->initialize(
                DB::table('payment_transactions')
                    ->where('id', $paymentTransactionId)
                    ->first(),
                $attempt,
                [
                    'payment_provider' => $paymentProvider->slug,
                    'secret_key' => $providerCredentials['secret_key'] ?? null,
                    'public_key' => $providerCredentials['public_key'] ?? null,
                    'email' => $customer->email ?? null,
                    'customer_name' => $customer->name ?? null,
                    'callback_url' => route(
                        'marketplace.payment-status',
                        ['order' => $pendingOrder]
                    ),
                    'return_url' => route(
                        'marketplace.payment-status',
                        ['order' => $pendingOrder]
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

        /*
         * OFFLINE:
         * Leave the transaction pending. The offline payment review
         * workflow will confirm or reject it.
         */
        return redirect()
            ->route('marketplace.developer.checkout', [
                'productType' => $data['product_type'],
                'productId' => $data['product_id'],
                'quantity' => $data['quantity'],
                'transaction' => $transactionReference,
            ])
            ->with(
                'status',
                'Offline payment selected. Complete the payment and submit your receipt.'
            );
    }


    public function saasPayment(Request $request)
    {
        $data = $request->validate([
            'order_id' => ['required', 'integer'],
            'payment_option' => ['required', 'string', 'max:150'],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'coupon_code' => ['nullable', 'string', 'max:100'],
        ]);

        $quantity = (int) ($data['quantity'] ?? 1);

        abort_unless(
            $quantity === 1,
            422,
            'SaaS checkout quantity must be one.'
        );

        $order = DB::table('marketplace_orders')
            ->where('id', $data['order_id'])
            ->where('buyer_id', auth()->id())
            ->where('payment_status', 'pending')
            ->whereIn('status', ['pending', 'processing'])
            ->first();

        abort_unless($order, 404);

        [$paymentMode, $paymentIdentifier] = array_pad(
            explode(':', $data['payment_option'], 2),
            2,
            null
        );

        abort_unless(
            in_array($paymentMode, ['online', 'offline', 'wallet'], true),
            422,
            'Invalid payment method.'
        );

        abort_unless(
            $paymentIdentifier !== null || $paymentMode === 'wallet',
            422,
            'Payment method is not selected.'
        );

        /*
         * ONLINE PAYMENT
         *
         * Reuse the central Esubiz gateway manager. SaaS checkout does
         * not implement gateway-specific payment logic itself.
         */
        if ($paymentMode === 'online') {

            $paymentProvider = DB::table('payment_providers')
                ->where('slug', $paymentIdentifier)
                ->where('is_active', true)
                ->whereNull('deleted_at')
                ->first();

            abort_unless(
                $paymentProvider,
                422,
                'The selected payment gateway is unavailable.'
            );

            $providerCredentials = $paymentProvider->credentials
                ? (json_decode($paymentProvider->credentials, true) ?: [])
                : [];

            $transactionReference = 'SAAS-TXN-' . strtoupper(
                \Illuminate\Support\Str::random(12)
            );

            $paymentTransactionId = DB::table('payment_transactions')
                ->insertGetId([
                    'workspace_id' => $order->workspace_id,
                    'wallet_id' => null,
                    'payment_provider_id' => $paymentProvider->id,
                    'reference' => $transactionReference,
                    'amount' => $order->amount,
                    'currency' => $order->currency,
                    'status' => 'pending',
                    'payload' => json_encode([
                        'marketplace_order_id' => $order->id,
                        'marketplace_order_reference' => $order->reference,
                        'product_type' => $order->product_type ?? null,
                        'product_id' => $order->product_id ?? null,
                        'quantity' => 1,
                        'buyer_id' => auth()->id(),
                        'payment_mode' => 'online',
                        'payment_provider' => $paymentProvider->slug,
                        'payment_provider_id' => $paymentProvider->id,
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

            $attemptService = app(
                \App\Services\Payment\CorePaymentAttemptService::class
            );

            $attempt = $attemptService->create(
                (int) $paymentTransactionId,
                (int) $paymentProvider->id,
                null,
                (float) $order->amount,
                [
                    'marketplace_order_id' => $order->id,
                    'marketplace_order_reference' => $order->reference,
                    'product_type' => $order->product_type ?? null,
                    'product_id' => $order->product_id ?? null,
                    'quantity' => 1,
                    'buyer_id' => auth()->id(),
                    'payment_mode' => 'online',
                ]
            );

            $gatewayManager = app(
                \App\Services\Payment\CorePaymentGatewayManager::class
            );

            $gateway = $gatewayManager->resolve(
                $paymentProvider->slug
            );

            $customer = DB::table('users')
                ->where('id', auth()->id())
                ->first();

            $result = $gateway->initialize(
                DB::table('payment_transactions')
                    ->where('id', $paymentTransactionId)
                    ->first(),
                $attempt,
                [
                    'payment_provider' => $paymentProvider->slug,
                    'payment_provider_id' => $paymentProvider->id,
                    'secret_key' => $providerCredentials['secret_key'] ?? null,
                    'public_key' => $providerCredentials['public_key'] ?? null,
                    'email' => $customer->email ?? null,
                    'customer_name' => $customer->name ?? null,
                    'callback_url' => route(
                        'marketplace.payment-status',
                        ['order' => $order->id]
                    ),
                    'return_url' => route(
                        'marketplace.payment-status',
                        ['order' => $order->id]
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

        /*
         * Offline and wallet payment paths will use the same SaaS order
         * and transaction lifecycle. They are intentionally handled
         * separately from hosted online gateway initialization.
         */
        abort(422, 'The selected payment method is not yet connected.');

    }


    public function developerPayment(Request $request)
    {
        $data = $request->validate([
            'transaction_reference' => ['required', 'string'],
        ]);

        $transaction = DB::table('payment_transactions')
            ->where('reference', $data['transaction_reference'])
            ->where('status', 'pending')
            ->first();

        abort_unless($transaction, 404);

        $payload = $transaction->payload
            ? json_decode($transaction->payload, true)
            : [];

        $gatewaySlug = $payload['payment_provider'] ?? null;

        abort_unless($gatewaySlug, 422, 'Payment gateway is not configured.');

        $gatewayManager = app(
            \App\Services\Payment\CorePaymentGatewayManager::class
        );

        $gateway = $gatewayManager->resolve($gatewaySlug);

        $attemptService = app(
            \App\Services\Payment\CorePaymentAttemptService::class
        );

        $attemptMetadata = [
            'marketplace_order_id' => $payload['marketplace_order_id'] ?? null,
            'marketplace_order_reference' => $payload['marketplace_order_reference'] ?? null,
            'product_type' => $payload['product_type'] ?? null,
            'product_id' => $payload['product_id'] ?? null,
            'quantity' => $payload['quantity'] ?? null,
            'buyer_id' => $payload['buyer_id'] ?? auth()->id(),
            'payment_mode' => $payload['payment_mode'] ?? 'online',
        ];

        $attempt = $attemptService->create(
            (int) $transaction->id,
            (int) $transaction->payment_provider_id,
            (int) $transaction->payment_method_id,
            (float) $transaction->amount,
            $attemptMetadata
        );

        $providerCredentials = $transaction->payment_provider_id
            ? DB::table('payment_providers')
                ->where('id', $transaction->payment_provider_id)
                ->value('credentials')
            : null;

        $providerCredentials = $providerCredentials
            ? (json_decode($providerCredentials, true) ?: [])
            : [];

        $result = $gateway->initialize(
            $transaction,
            $attempt,
            [
                'payment_method' => $payload['payment_method'] ?? null,
                'payment_method_id' => $transaction->payment_method_id,
                'payment_provider' => $gatewaySlug,
                'secret_key' => $providerCredentials['secret_key'] ?? null,
                'public_key' => $providerCredentials['public_key'] ?? null,
                'return_url' => route('marketplace.payment-status', [
                    'order' => $transaction->marketplace_order_id,
                ]),
                'callback_url' => route('marketplace.payment-status', [
                    'order' => $transaction->marketplace_order_id,
                ]),
            ]
        );

        $attemptService->update(
            (int) $attempt->id,
            'processing',
            $result['reference'] ?? null,
            $result
        );

        return response()->json([
            'status' => 'initialized',
            'reference' => $transaction->reference,
            'gateway' => $gatewaySlug,
            'attempt' => $attempt->uuid,
            'payment' => $result,
        ]);
    }


    public function developerLibrary()
    {
        $orders = DB::table('marketplace_orders as orders')
            ->join(
                'marketplace_listings as listings',
                'listings.id',
                '=',
                'orders.marketplace_listing_id'
            )
            ->where('orders.buyer_id', auth()->id())
            ->where('orders.payment_status', 'paid')
            ->whereIn('orders.status', ['completed', 'fulfilled'])
            ->select(
                'orders.*',
                'listings.product_type',
                'listings.product_id',
                'listings.title as product_title',
                'listings.slug as product_slug'
            )
            ->latest('orders.updated_at')
            ->get();

        return view('marketplace.developer-library', [
            'orders' => $orders,
        ]);
    }


    public function developerPendingCheckouts()
    {
        $checkouts = DB::table('marketplace_orders as orders')
            ->join('marketplace_listings as listings', 'listings.id', '=', 'orders.marketplace_listing_id')
            ->leftJoin('payment_transactions as transactions', function ($join) {
                $join->on(
                    DB::raw("JSON_UNQUOTE(JSON_EXTRACT(transactions.payload, '$.marketplace_order_id'))"),
                    '=',
                    DB::raw('CAST(orders.id AS CHAR)')
                );
            })
            ->where('orders.buyer_id', auth()->id())
            ->where('orders.payment_status', 'pending')
            ->where('orders.status', 'pending')
            ->select(
                'orders.*',
                'listings.product_type',
                'listings.product_id',
                'listings.title as listing_title',
                'listings.slug as listing_slug',
                'transactions.reference as transaction_reference',
                'transactions.payment_provider_id',
                'transactions.status as transaction_status'
            )
            ->latest('orders.created_at')
            ->get();

        return view('marketplace.developer-pending-checkouts', [
            'checkouts' => $checkouts,
        ]);
    }


    public function continueDeveloperCheckout($order)
    {
        $order = DB::table('marketplace_orders as orders')
            ->join(
                'marketplace_listings as listings',
                'listings.id',
                '=',
                'orders.marketplace_listing_id'
            )
            ->leftJoin('payment_transactions as transactions', function ($join) {
                $join->on(
                    DB::raw("JSON_UNQUOTE(JSON_EXTRACT(transactions.payload, '$.marketplace_order_id'))"),
                    '=',
                    DB::raw('CAST(orders.id AS CHAR)')
                );
            })
            ->where('orders.id', $order)
            ->where('orders.buyer_id', auth()->id())
            ->where('orders.payment_status', 'pending')
            ->where('orders.status', 'pending')
            ->select(
                'orders.id',
                'orders.reference',
                'orders.amount',
                'orders.currency',
                'listings.product_type',
                'listings.product_id',
                'transactions.reference as transaction_reference',
                'transactions.payload as transaction_payload'
            )
            ->first();

        abort_unless($order, 404);

        $productType = match ($order->product_type) {
            'core_addon' => 'addon',
            'core_bundle' => 'bundle',
            default => $order->product_type,
        };

        $transactionPayload = [];

        if (!empty($order->transaction_payload)) {
            $transactionPayload = json_decode(
                $order->transaction_payload,
                true
            ) ?: [];
        }

        $quantity = max(
            1,
            (int) ($transactionPayload['quantity'] ?? 1)
        );

        return redirect()->route('marketplace.developer.checkout', [
            'productType' => $productType,
            'productId' => $order->product_id,
            'quantity' => $quantity,
            'transaction' => $order->transaction_reference,
            'order' => $order->id,
        ]);
    }


    public function adminCheckoutSessions()
    {
        $sessions = DB::table('marketplace_checkout_sessions as sessions')
            ->leftJoin('users', 'users.id', '=', 'sessions.user_id')
            ->leftJoin('marketplace_orders as orders', 'orders.id', '=', 'sessions.marketplace_order_id')
            ->leftJoin('payment_transactions as transactions', 'transactions.id', '=', 'sessions.payment_transaction_id')
            ->whereIn('sessions.status', [
                'active',
                'pending_payment',
            ])
            ->select(
                'sessions.*',
                'users.name as buyer_name',
                'users.email as buyer_email',
                'orders.reference as order_reference',
                'transactions.reference as transaction_reference'
            )
            ->latest('sessions.created_at')
            ->get();

        return view('admin.marketplace.checkout-sessions', [
            'sessions' => $sessions,
        ]);
    }


    public function adminDeleteCheckoutSession($session)
    {
        $checkout = DB::table('marketplace_checkout_sessions')
            ->where('id', $session)
            ->first();

        abort_unless($checkout, 404);

        if (in_array($checkout->status, ['completed'], true)) {
            abort(403, 'Completed checkout sessions cannot be deleted.');
        }

        $orderId = $checkout->marketplace_order_id;
        $transactionId = $checkout->payment_transaction_id;

        DB::transaction(function () use ($checkout, $orderId, $transactionId) {

            DB::table('marketplace_checkout_sessions')
                ->where('id', $checkout->id)
                ->update([
                    'status' => 'cancelled',
                    'updated_at' => now(),
                ]);

            if ($transactionId) {
                DB::table('payment_transactions')
                    ->where('id', $transactionId)
                    ->where('status', 'pending')
                    ->update([
                        'status' => 'cancelled',
                        'updated_at' => now(),
                    ]);
            }

            if ($orderId) {
                DB::table('marketplace_orders')
                    ->where('id', $orderId)
                    ->where('payment_status', 'pending')
                    ->whereIn('status', ['pending', 'processing'])
                    ->update([
                        'payment_status' => 'failed',
                        'status' => 'cancelled',
                        'updated_at' => now(),
                    ]);
            }
        });

        return redirect()
            ->route('admin.marketplace.checkout-sessions')
            ->with('status', 'Checkout session cancelled successfully.');
    }

    public function addons()
    {
        $addons = DB::table('core_addons')
            ->where('is_active', true)
            ->where('saas_available', true)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();

        $bundles = DB::table('core_addon_bundles')
            ->where('is_active', true)
            ->where('saas_available', true)
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->get();

        $bundleItems = DB::table('core_addon_bundle_items')
            ->join('core_addons', 'core_addon_bundle_items.addon_id', '=', 'core_addons.id')
            ->select(
                'core_addon_bundle_items.*',
                'core_addons.name',
                'core_addons.capabilities',
                'core_addons.default_allocation',
                'core_addons.allocation_unit'
            )
            ->get()
            ->groupBy('bundle_id');

        $capabilityAllocations = DB::table('core_addon_capability_allocations')
            ->join(
                'core_addons',
                'core_addon_capability_allocations.addon_id',
                '=',
                'core_addons.id'
            )
            ->select(
                'core_addon_capability_allocations.*',
                'core_addons.allocation_unit'
            )
            ->get()
            ->map(function ($allocation) {
                $key = (string) $allocation->capability_key;

                $allocation->display_name = ucwords(
                    str_replace(['_', '-'], ' ', preg_replace('/^crm_/', '', $key))
                );

                // The unit displayed in Marketplace comes exclusively
                // from the Admin-configured Add-on allocation unit.
                $allocation->display_unit =
                    $allocation->allocation_unit ?: null;

                $allocation->addon_allocation_unit =
                    $allocation->allocation_unit ?: null;

                return $allocation;
            })
            ->groupBy('addon_id');

        /*
         * Attach the actual capability allocations to every core add-on.
         * This is the authoritative source for quantities/unlimited status.
         */
        foreach ($addons as $addon) {
            $allocations = collect($capabilityAllocations->get($addon->id, []));

            if ($allocations->isEmpty()) {
                $capabilities = json_decode($addon->capabilities ?? '[]', true) ?: [];

                $allocations = collect($capabilities)->map(function ($key) {
                    return (object) [
                        'capability_key' => $key,
                        'allocation' => null,
                        'is_unlimited' => false,
                        'display_name' => ucwords(
                            str_replace(['_', '-'], ' ', preg_replace('/^crm_/', '', $key))
                        ),
                        'display_unit' => null,
                    ];
                });
            }

            $addon->capability_allocations = $allocations;
        }

        /*
         * Attach the same capability allocation data to every bundle item.
         * Bundle allocation controls remain authoritative for the included
         * add-on itself, while capability allocations describe what that
         * add-on actually provides.
         */
        foreach ($bundleItems as $items) {
            foreach ($items as $item) {
                $item->capability_allocations =
                    collect($capabilityAllocations->get($item->addon_id, []));
            }
        }

        return view('marketplace.index', [
            'addons' => $addons,
            'bundles' => $bundles,
            'bundleItems' => $bundleItems,
            'capabilityAllocations' => $capabilityAllocations,
        ]);
    }

    protected function resolveMarketplaceListing(
        string $productType,
        int $productId
    ): ?\App\Models\MarketplaceListing {
        /*
         * Generic marketplace resolver.
         *
         * The checkout must remain product-agnostic. Normalize the
         * human-facing product type to the canonical marketplace type,
         * then resolve the published marketplace listing.
         */
        $canonicalType = match ($productType) {
            'addon',
            'core_addon',
            'core-addon' => 'core_addon',

            'bundle',
            'core_bundle',
            'core-bundle' => 'core_bundle',

            default => $productType,
        };

        $listing = \App\Models\MarketplaceListing::query()
            ->where('product_type', $canonicalType)
            ->where('product_id', $productId)
            ->where('status', 'published')
            ->first();

        /*
         * Fallback for marketplace products whose checkout already sends
         * the canonical product ID/type. This keeps direct addon checkout
         * independent of the UI's product-type naming.
         */
        if (!$listing && $canonicalType !== $productType) {
            $listing = \App\Models\MarketplaceListing::query()
                ->where('product_type', $productType)
                ->where('product_id', $productId)
                ->where('status', 'published')
                ->first();
        }

        return $listing;
    }

    public function developerAddons()
    {
        $addons = DB::table('core_addons')
            ->where('is_active', true)
            ->where('off_server_available', true)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();

        $capabilityAllocations = DB::table('core_addon_capability_allocations')
            ->whereIn('addon_id', $addons->pluck('id'))
            ->get()
            ->groupBy('addon_id');

        foreach ($addons as $addon) {
            $addon->capability_allocations =
                collect($capabilityAllocations->get($addon->id, []));
        }

        $bundles = DB::table('core_addon_bundles')
            ->where('is_active', true)
            ->where('off_server_available', true)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();

        $bundleItems = DB::table('core_addon_bundle_items as items')
            ->join('core_addons as addons', 'addons.id', '=', 'items.addon_id')
            ->whereIn('items.bundle_id', $bundles->pluck('id'))
            ->where('addons.is_active', true)
            ->whereNull('addons.deleted_at')
            ->select(
                'items.bundle_id',
                'addons.id as addon_id',
                'addons.name',
                'items.allocation',
                'items.is_unlimited'
            )
            ->orderBy('addons.name')
            ->get()
            ->groupBy('bundle_id');

        return view('marketplace.developer-addons', [
            'addons' => $addons,
            'bundles' => $bundles,
            'bundleItems' => $bundleItems,
        ]);
    }

    public function checkout(Request $request)
    {
        $data = $request->validate([
            'product_type' => ['required', 'string', 'max:100'],
            'product_id' => ['required', 'integer'],
            'deployment_type' => ['required', 'in:saas,off_server'],
            'website_id' => ['nullable', 'integer'],
        ]);

        $website = DB::table('websites')
            ->where('id', $data['website_id'])
            ->when(
                session('account_mode', 'user') === 'developer',
                fn ($query) => $query->where('developer_id', auth()->id()),
                fn ($query) => $query->where('owner_id', auth()->id())
            )
            ->first();

        abort_unless($website, 403);

        $listing = $this->resolveMarketplaceListing(
            $data['product_type'],
            (int) $data['product_id']
        );

        abort_unless($listing, 404);

        $productResolver = app(\App\Services\Marketplace\MarketplaceProductResolver::class);

        $product = $productResolver->resolve(
            $data['product_type'],
            (int) $data['product_id']
        );

        abort_unless($product, 404);

        $available = $productResolver->available(
            $product,
            $data['deployment_type']
        );

        abort_unless($available, 422);

        $price = $productResolver->price(
            $product,
            $data['deployment_type']
        );

        $currency = $productResolver->currency(
            $product,
            $data['deployment_type']
        );

        abort_unless($price !== null && (float) $price >= 0, 422);

        /*
         * The marketplace product resolver is the single source of truth
         * for SaaS pricing. Keep the checkout amount aligned with it.
         */
        $amount = (float) $price;

        $orderReference = 'MKT-' . strtoupper(bin2hex(random_bytes(6)));

        $checkoutSessionId = DB::table('marketplace_checkout_sessions')->insertGetId([
            'user_id' => auth()->id(),
            'account_mode' => session('account_mode', 'user'),
            'product_type' => $data['product_type'],
            'product_id' => (int) $data['product_id'],
            'deployment_type' => $data['deployment_type'],
            'website_id' => $website->id,
            'workspace_id' => $website->workspace_id,
            'quantity' => 1,
            'unit_price' => $amount,
            'total_amount' => $amount,
            'currency' => $currency,
            'is_commissionable' => true,
            'status' => 'pending_payment',
            'expires_at' => now()->addHours(24),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $orderId = DB::table('marketplace_orders')->insertGetId([
            'marketplace_listing_id' => $listing->id,
            'vendor_id' => $listing->vendor_id,
            'workspace_id' => $website->workspace_id,
            'buyer_id' => auth()->id(),
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'reference' => $orderReference,
            'amount' => $price,
            'commission_amount' => 0,
            'vendor_amount' => $price,
            'currency' => strtoupper($currency ?: 'NGN'),
            'status' => 'pending',
            'payment_status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('marketplace_checkout_sessions')
            ->where('id', $checkoutSessionId)
            ->update([
                'marketplace_order_id' => $orderId,
                'updated_at' => now(),
            ]);

        return redirect()
            ->route('marketplace.checkout', ['order' => $orderId])
            ->with('success', 'Marketplace order created. Payment is still pending.');
    }

    public function paymentStatus(int $order)
    {
        $record = DB::table('marketplace_orders')
            ->where('id', $order)
            ->where('buyer_id', auth()->id())
            ->first();

        abort_unless($record, 404);

        /*
         * Synchronize the originating marketplace checkout session from
         * the canonical marketplace payment state.
         *
         * This is gateway-agnostic. Paystack, Flutterwave, offline,
         * wallet and future payment methods all converge here.
         */
        if ($record->payment_status === 'paid') {
            $paymentTransaction = DB::table('payment_transactions')
                ->where('payload', 'like', '%"marketplace_order_id":' . $record->id . '%')
                ->latest('id')
                ->first();

            if ($paymentTransaction) {
                DB::table('marketplace_checkout_sessions')
                    ->where('payment_transaction_id', $paymentTransaction->id)
                    ->where('status', 'pending_payment')
                    ->update([
                        'status' => 'completed',
                        'updated_at' => now(),
                    ]);
            }
        }

        /*
         * Resolve the payment transaction belonging to this marketplace
         * order. The transaction payload is intentionally product-agnostic.
         */
        $transaction = DB::table('payment_transactions')
            ->whereRaw(
                "JSON_UNQUOTE(JSON_EXTRACT(payload, '$.marketplace_order_id')) = ?",
                [(string) $record->id]
            )
            ->latest('id')
            ->first();

        /*
         * ONLINE:
         * Verify through the central gateway interface. This applies to
         * every registered online gateway without gateway-specific logic.
         */
        if (
            $transaction &&
            $transaction->status === 'pending' &&
            $transaction->payment_provider_id
        ) {
            $payload = $transaction->payload
                ? (json_decode($transaction->payload, true) ?: [])
                : [];

            $paymentMode = $payload['payment_mode'] ?? 'online';

            if ($paymentMode === 'online') {
                $provider = DB::table('payment_providers')
                    ->where('id', $transaction->payment_provider_id)
                    ->where('is_active', true)
                    ->whereNull('deleted_at')
                    ->first();

                if ($provider) {
                    $gatewayManager = app(
                        \App\Services\Payment\CorePaymentGatewayManager::class
                    );

                    $gateway = $gatewayManager->resolve($provider->slug);

                    $attempt = DB::table('payment_attempts')
                        ->where('payment_transaction_id', $transaction->id)
                        ->whereIn('status', ['initiated', 'processing'])
                        ->latest('id')
                        ->first();

                    if ($attempt) {
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

                        $verificationStatus = $verification['status'] ?? 'failed';

                        if ($verificationStatus === 'successful') {
                            app(
                                \App\Services\Payment\CorePaymentAttemptService::class
                            )->update(
                                (int) $attempt->id,
                                'successful',
                                $verification['gateway_reference']
                                    ?? $attempt->gateway_reference,
                                $verification
                            );

                            DB::table('payment_transactions')
                                ->where('id', $transaction->id)
                                ->update([
                                    'status' => 'successful',
                                    'updated_at' => now(),
                                ]);

                            DB::table('marketplace_orders')
                                ->where('id', $record->id)
                                ->update([
                                    'payment_status' => 'paid',
                                    'status' => 'processing',
                                    'updated_at' => now(),
                                ]);

                            $record->payment_status = 'paid';
                            $record->status = 'processing';

                            /*
                             * Record the successful marketplace payment as
                             * Esubiz platform revenue before fulfilment.
                             *
                             * The platform sale service feeds the existing
                             * CoreTransactionService financial records so
                             * the amount appears in Admin revenue, income
                             * and ledger reporting.
                             */
                            $listing = DB::table('marketplace_listings')
                                ->where('id', $record->marketplace_listing_id)
                                ->first();

                            if ($listing) {
                                app(
                                    \App\Services\Core\EsubizPlatformSaleService::class
                                )->record(
                                    $record->workspace_id ?? null,
                                    match ($listing->product_type) {
                                        'core_addon', 'addon' => 'addons',
                                        'core_bundle', 'bundle' => 'addons',
                                        'theme' => 'themes',
                                        'module' => 'modules',
                                        'subscription' => 'subscriptions',
                                        'hybrid' => 'hybrid',
                                        'ai_credits' => 'ai_credits',
                                        'sms_credits' => 'sms_credits',
                                        'email_credits' => 'email_credits',
                                        'whatsapp_credits' => 'whatsapp_credits',
                                        default => 'marketplace',
                                    },
                                    $listing->product_type,
                                    (int) $listing->product_id,
                                    $listing->title,
                                    (float) $record->amount,
                                    $record->currency ?: 'NGN',
                                    auth()->id(),
                                    $record->website_id ?? null,
                                    'purchase',
                                    [
                                        'marketplace_order_id' => $record->id,
                                        'marketplace_order_reference' => $record->reference,
                                        'payment_transaction_id' => $transaction->id ?? null,
                                        'deployment_type' => $record->deployment_type ?? 'saas',
                                        'developer_id' => $listing->developer_id ?? null,
                                        'financial_account_developer_id' => $listing->developer_id ?? null,
                                    ]
                                );
                            }
                        }
                    }
                }
            }
        }

        /*
         * FULFILMENT:
         * Once payment is confirmed, use the existing generic marketplace
         * fulfilment manager. Do not hard-code gateway behaviour here.
         */
        if (
            $record->payment_status === 'paid' &&
            $record->status !== 'fulfilled'
        ) {
            $listing = DB::table('marketplace_listings')
                ->where('id', $record->marketplace_listing_id)
                ->first();

            if ($listing) {
                $orderForFulfilment = (object) array_merge(
                    (array) $record,
                    [
                        'deployment_type' => $record->deployment_type
                            ?? (str_starts_with((string) $record->reference, 'DEV-')
                                ? 'off_server'
                                : 'saas'),
                        'website_id' => $record->website_id ?? null,
                        'workspace_id' => $record->workspace_id ?? null,
                    ]
                );

                app(
                    \App\Services\Marketplace\MarketplaceFulfilmentManager::class
                )->fulfil($orderForFulfilment, $listing);

                DB::table('marketplace_orders')
                    ->where('id', $record->id)
                    ->update([
                        'status' => 'completed',
                        'updated_at' => now(),
                    ]);

                $record->status = 'completed';
            }
        }

        /*
         * The payment-success page is presentation only.
         * Payment verification and fulfilment have already completed above.
         *
         * Determine the destination from the originating transaction
         * context so Developer and SaaS purchases are not treated alike.
         */
        $successPayload = $transaction?->payload
            ? (json_decode($transaction->payload, true) ?: [])
            : [];

        $deploymentType = $successPayload['deployment_type']
            ?? ($record->deployment_type ?? null)
            ?? (str_starts_with((string) $record->reference, 'DEV-')
                ? 'off_server'
                : 'saas');

        $successDestination = $deploymentType === 'off_server'
            ? route('marketplace.developer.library')
            : ($successPayload['website_id'] ?? $record->website_id ?? null
                ? url('/dashboard')
                : route('marketplace.index'));

        if ($record->payment_status === 'paid') {
            return view('marketplace.payment-success', [
                'order' => $record,
                'paid' => true,
                'entitlementActivated' =>
                    $record->status === 'completed'
                    || $record->status === 'fulfilled',
                'deploymentType' => $deploymentType,
                'successDestination' => $successDestination,
            ]);
        }

        return view('marketplace.payment-success', [
            'order' => $record,
            'paid' => false,
            'entitlementActivated' => false,
            'deploymentType' => $deploymentType,
            'successDestination' => $successDestination,
        ]);
}

    public function pendingCheckouts()
    {
        $orders = DB::table('marketplace_orders')
            ->join('marketplace_listings', 'marketplace_listings.id', '=', 'marketplace_orders.marketplace_listing_id')
            ->where('marketplace_orders.buyer_id', auth()->id())
            ->where('marketplace_orders.payment_status', 'pending')
            ->select(
                'marketplace_orders.*',
                'marketplace_listings.product_type',
                'marketplace_listings.title as listing_title',
                'marketplace_listings.slug as listing_slug'
            )
            ->latest('marketplace_orders.created_at')
            ->get();

        return view('marketplace.pending-checkouts', [
            'orders' => $orders,
        ]);
    }

    public function checkoutPage(int $order)
    {
        $order = DB::table('marketplace_orders')
            ->join(
                'marketplace_listings',
                'marketplace_listings.id',
                '=',
                'marketplace_orders.marketplace_listing_id'
            )
            ->where('marketplace_orders.id', $order)
            ->where('marketplace_orders.buyer_id', auth()->id())
            ->select(
                'marketplace_orders.*',
                'marketplace_listings.product_type',
                'marketplace_listings.product_id',
                'marketplace_listings.title as listing_title',
                'marketplace_listings.slug as listing_slug'
            )
            ->first();

        abort_unless($order, 404);

        $resolver = app(
            \App\Services\Marketplace\MarketplaceProductResolver::class
        );

        $product = $resolver->resolve(
            $order->product_type,
            (int) $order->product_id
        );

        abort_unless($product, 404);

        $price = $resolver->price($product, 'saas');
        $currency = $resolver->currency($product, 'saas');

        abort_unless($price !== null, 422);

        $onlineGateways = DB::table('payment_providers as providers')
            ->join(
                'payment_methods as methods',
                'methods.payment_provider_id',
                '=',
                'providers.id'
            )
            ->where('providers.is_active', true)
            ->whereNull('providers.deleted_at')
            ->where('methods.is_active', true)
            ->whereNull('methods.deleted_at')
            ->select(
                'providers.id',
                'providers.uuid',
                'providers.name',
                'providers.slug',
                'providers.type'
            )
            ->groupBy(
                'providers.id',
                'providers.uuid',
                'providers.name',
                'providers.slug',
                'providers.type'
            )
            ->orderBy('providers.name')
            ->get();

        $offlineMethods = DB::table('offline_payment_methods')
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->orderBy('priority')
            ->orderBy('name')
            ->get();

        $wallet = DB::table('wallets')
            ->where('user_id', auth()->id())
            ->whereIn('type', ['customer', 'developer'])
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->orderByRaw("CASE WHEN type = 'customer' THEN 0 ELSE 1 END")
            ->first();

        if (!$wallet) {
            $walletId = DB::table('wallets')->insertGetId([
                'user_id' => auth()->id(),
                'workspace_id' => $order->workspace_id ?? null,
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'name' => 'Esubiz Wallet',
                'type' => 'customer',
                'currency' => $currency ?: 'NGN',
                'available_balance' => 0,
                'pending_balance' => 0,
                'reserved_balance' => 0,
                'is_default' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $wallet = DB::table('wallets')
                ->where('id', $walletId)
                ->first();
        }

        return view('marketplace.checkout', [
            'order' => $order,
            'product' => $product,
            'productType' => $order->product_type,
            'onlineGateways' => $onlineGateways,
            'offlineMethods' => $offlineMethods,
            'saasCheckout' => true,
            'price' => (float) $price,
            'currency' => $currency ?: 'NGN',
            'quantity' => 1,
            'walletEnabled' => (bool) ($wallet->is_active ?? false),
            'walletBalance' => (float) ($wallet->available_balance ?? 0),
            'walletCurrency' => $wallet->currency ?? ($currency ?: 'NGN'),
        ]);
    }

    public function developer()
    {
        return view('marketplace.developer', [
            'addons' => DB::table('core_addons')
                ->where('is_active', true)
                ->whereNull('deleted_at')
                ->orderBy('name')
                ->get(),

            'bundles' => DB::table('core_addon_bundles')
                ->where('is_active', true)
                ->whereNull('deleted_at')
                ->orderBy('name')
                ->get(),
        ]);
    }
}
