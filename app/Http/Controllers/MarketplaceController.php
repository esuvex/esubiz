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

        $paymentMethods = DB::table('payment_methods as methods')
            ->join('payment_providers as providers', 'providers.id', '=', 'methods.payment_provider_id')
            ->where('methods.is_active', true)
            ->whereNull('methods.deleted_at')
            ->where('providers.is_active', true)
            ->whereNull('providers.deleted_at')
            ->orderBy('methods.priority')
            ->orderBy('methods.name')
            ->select(
                'methods.id',
                'methods.uuid',
                'methods.name',
                'methods.slug',
                'methods.type',
                'methods.supported_currencies',
                'methods.minimum_amount',
                'methods.maximum_amount',
                'methods.icon',
                'providers.id as provider_id',
                'providers.name as provider_name',
                'providers.slug as provider_slug',
                'providers.type as provider_type'
            )
            ->get();

        return view('marketplace.developer-checkout', [
            'product' => $product,
            'productType' => $productType,
            'paymentMethods' => $paymentMethods,
        ]);
    }


    public function developerCheckoutSubmit(Request $request)
    {
        $data = $request->validate([
            'product_type' => ['required', 'string', 'max:100'],
            'product_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'min:1'],
            'payment_method_id' => ['required', 'integer'],
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

        abort_unless($listing, 404);

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

        $unitPrice = (float) ($product->off_server_price ?? 0);
        $amount = $unitPrice * (int) $data['quantity'];
        $currency = $product->off_server_currency ?? 'NGN';

        $orderReference = 'DEV-' . strtoupper(\Illuminate\Support\Str::random(12));

        $paymentMethod = DB::table('payment_methods')
            ->where('id', $data['payment_method_id'])
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->first();

        abort_unless($paymentMethod, 422, 'Selected payment method is unavailable.');

        $paymentProvider = DB::table('payment_providers')
            ->where('id', $paymentMethod->payment_provider_id)
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->first();

        abort_unless($paymentProvider, 422, 'Selected payment provider is unavailable.');

        $pendingOrder = DB::table('marketplace_orders')->insertGetId([
            'marketplace_listing_id' => $listing->id,
            'vendor_id' => $listing->vendor_id,
            'workspace_id' => $listing->workspace_id,
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

        $checkoutSessionId = DB::table('marketplace_checkout_sessions')->insertGetId([
            'user_id' => auth()->id(),
            'account_mode' => 'developer',
            'product_type' => $data['product_type'],
            'product_id' => $data['product_id'],
            'quantity' => (int) $data['quantity'],
            'unit_price' => $unitPrice,
            'total_amount' => $amount,
            'currency' => $currency,
            'payment_method_id' => $paymentMethod->id,
            'payment_provider_id' => $paymentProvider->id,
            'marketplace_order_id' => $pendingOrder,
                'checkout_session_id' => $checkoutSessionId,
            'status' => 'pending_payment',
            'is_commissionable' => true,
            'expires_at' => now()->addHours(24),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $transactionReference = 'DEV-TXN-' . strtoupper(\Illuminate\Support\Str::random(12));

        $paymentTransactionId = DB::table('payment_transactions')->insertGetId([
            'workspace_id' => $listing->workspace_id,
            'wallet_id' => null,
            'payment_provider_id' => $paymentProvider->id,
            'reference' => $transactionReference,
            'amount' => $amount,
            'currency' => $currency,
            'status' => 'pending',
            'payload' => json_encode([
                'marketplace_order_id' => $pendingOrder,
                'marketplace_order_reference' => $orderReference,
                'payment_method_id' => $paymentMethod->id,
                'payment_method' => $paymentMethod->slug,
                'payment_provider' => $paymentProvider->slug,
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
                'status' => 'pending_payment',
                'updated_at' => now(),
            ]);

        return redirect()
            ->route('marketplace.developer.checkout', [
                'productType' => $data['product_type'],
                'productId' => $data['product_id'],
                'quantity' => $data['quantity'],
            ])
            ->with('status', 'Developer marketplace checkout created.');
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

        return response()->json([
            'status' => 'pending',
            'reference' => $transaction->reference,
            'message' => 'Developer payment transaction is ready for gateway processing.',
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
    ): \App\Models\MarketplaceListing {
        $listing = \App\Models\MarketplaceListing::query()
            ->where('product_type', $productType)
            ->where('product_id', $productId)
            ->where('status', 'published')
            ->first();

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

        $listing = DB::table('marketplace_listings')
            ->where('product_type', $data['product_type'])
            ->where('product_id', $data['product_id'])
            ->where('status', 'published')
            ->whereNull('deleted_at')
            ->first();

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

        $orderReference = 'MKT-' . strtoupper(bin2hex(random_bytes(6)));

        $checkoutSessionId = DB::table('marketplace_checkout_sessions')->insertGetId([
            'user_id' => auth()->id(),
            'account_mode' => 'user',
            'product_type' => $data['product_type'],
            'product_id' => (int) $data['product_id'],
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
            ->route('marketplace.checkout', $orderId)
            ->with('success', 'Marketplace order created. Payment is still pending.');
    }

    public function paymentStatus(int $order)
    {
        $record = DB::table('marketplace_orders')
            ->where('id', $order)
            ->where('buyer_id', auth()->id())
            ->first();

        abort_unless($record, 404);

        if ($record->payment_status === 'paid' && $record->status !== 'fulfilled') {
            $listing = DB::table('marketplace_listings')
                ->where('id', $record->marketplace_listing_id)
                ->first();

            if ($listing && in_array($listing->product_type, ['core_addon', 'core_bundle'], true)) {
                $productType = $listing->product_type === 'core_addon' ? 'addon' : 'bundle';

                $orderForFulfilment = (object) array_merge(
                    (array) $record,
                    [
                        'deployment_type' => $record->deployment_type ?? 'saas',
                        'website_id' => $record->website_id ?? null,
                        'workspace_id' => $record->workspace_id ?? null,
                    ]
                );

                app(\App\Services\Marketplace\MarketplaceFulfilmentManager::class)
                    ->fulfil($orderForFulfilment, $listing);

                DB::table('marketplace_orders')
                    ->where('id', $record->id)
                    ->update([
                        'status' => 'fulfilled',
                        'updated_at' => now(),
                    ]);

                $record->status = 'fulfilled';
            }
        }

        return response()->json([
            'success' => true,
            'order_id' => $record->id,
            'order_reference' => $record->reference ?? null,
            'status' => $record->status,
            'payment_status' => $record->payment_status,
            'paid' => $record->payment_status === 'paid',
            'entitlement_activated' => $record->payment_status === 'paid'
                && $record->status === 'fulfilled',
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
            ->join('marketplace_listings', 'marketplace_listings.id', '=', 'marketplace_orders.marketplace_listing_id')
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

        return view('marketplace.checkout', [
            'order' => $order,
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
