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

    public function addons()
    {
        $addons = DB::table('core_addons')
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();

        $bundles = DB::table('core_addon_bundles')
            ->where('is_active', true)
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
            ->get()
            ->groupBy('addon_id');

        /*
         * Attach the actual capability allocations to every core add-on.
         * This is the authoritative source for quantities/unlimited status.
         */
        foreach ($addons as $addon) {
            $addon->capability_allocations =
                collect($capabilityAllocations->get($addon->id, []));
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
