@extends('admin.layouts.app')


<style>
/* Esubiz mobile checkout */
@media (max-width: 767px) {

    html,
    body {
        overflow-x: hidden !important;
        max-width: 100% !important;
    }

    /* Prevent checkout grid/cards from creating horizontal overflow */
    .marketplace-checkout,
    .marketplace-checkout * {
        min-width: 0;
        box-sizing: border-box;
    }

    .marketplace-checkout {
        width: 100% !important;
        max-width: 100% !important;
        overflow-x: hidden;
    }

    .marketplace-checkout form,
    .marketplace-checkout input,
    .marketplace-checkout select,
    .marketplace-checkout button {
        max-width: 100% !important;
    }

    .marketplace-checkout input,
    .marketplace-checkout select {
        width: 100% !important;
    }

    .marketplace-checkout button {
        width: 100%;
    }

    /* Coupon controls stack cleanly on small screens */
    .marketplace-checkout .coupon-row {
        display: flex;
        flex-direction: column;
        width: 100%;
        gap: 0.75rem;
    }

    .marketplace-checkout .coupon-row input {
        width: 100% !important;
        min-width: 0 !important;
    }

    .marketplace-checkout .coupon-row button {
        width: 100% !important;
        min-width: 0 !important;
    }

    /* Summary card should never exceed viewport */
    .marketplace-checkout .summary-card {
        width: 100% !important;
        max-width: 100% !important;
    }
}
</style>

@section('content')

<div class="marketplace-checkout min-h-screen bg-slate-50 px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-6xl">

        {{-- Header --}}
        <div class="mb-8">
            <div class="text-[10px] font-black uppercase tracking-[0.22em] text-blue-600">
                Esubiz Marketplace
            </div>

            <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950">
                Checkout
            </h1>

            <p class="mt-2 text-sm text-slate-500">
                Review your purchase and select a payment method.
            </p>
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
                {{ session('error') }}
            </div>
        @endif


        <div
            x-data="{
                quantity: 1,
                unitPrice: {{ (float) ($price ?? $product->off_server_price ?? 0) }},
                coupon: '',
                couponApplied: false,
                discount: 0,
                paymentMethod: '',

                get subtotal() {
                    return Math.max(1, Number(this.quantity || 1)) * this.unitPrice;
                },

                get total() {
                    return Math.max(0, this.subtotal - Number(this.discount || 0));
                }
            }"
            class="grid gap-6 lg:grid-cols-3"
        >

            {{-- Purchase --}}
            <div class="lg:col-span-2 rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">

                <div class="text-[10px] font-black uppercase tracking-[0.18em] text-blue-600">
                    Marketplace Product
                </div>

                <div class="mt-2 flex items-start justify-between gap-5">
                    <div>
                        <h2 class="text-2xl font-black text-slate-950">
                            {{ $listing->title ?? $product->name ?? 'Marketplace Product' }}
                        </h2>

                        @if(!empty($listing->description ?? null) || !empty($listing->summary ?? null) || !empty($product->description ?? null))
                            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                                {{ $listing->description ?? $listing->summary ?? $product->description }}
                            </p>
                        @endif
                    </div>

                    <div class="shrink-0 rounded-xl bg-blue-50 px-3 py-2 text-[10px] font-black uppercase text-blue-700">
                        {{ $productType ?? 'product' }}
                    </div>
                </div>

                {{-- Website --}}
                @if(isset($website) && $website)
                    <div class="mt-8 rounded-2xl border border-blue-100 bg-blue-50 p-5">
                        <div class="text-[10px] font-black uppercase tracking-wider text-blue-600">
                            Website
                        </div>

                        <div class="mt-2 text-lg font-black text-slate-950">
                            {{ $website->name ?? 'Esubiz Website' }}
                        </div>

                        @if(!empty($website->domain))
                            <div class="mt-1 text-sm text-slate-500">
                                {{ $website->domain }}
                            </div>
                        @elseif(!empty($website->subdomain))
                            <div class="mt-1 text-sm text-slate-500">
                                {{ $website->subdomain }}
                            </div>
                        @endif
                    </div>
                @endif
                {{-- Included Product Items --}}
                @if(($includedItems ?? collect())->isNotEmpty())
                    <div
                        class="mt-6 rounded-2xl border border-slate-200 p-5"
                        x-data="{ showAllItems: false }"
                    >
                        <div class="text-xs font-black uppercase tracking-wider text-slate-400">
                            Included in this purchase
                        </div>

                        <div class="mt-4 overflow-hidden rounded-xl border border-slate-100">
                            <div class="grid grid-cols-[1fr_auto] gap-6 border-b border-slate-100 bg-slate-50 px-4 py-3">
                                <div class="text-xs font-black uppercase tracking-wider text-slate-500">
                                    Included
                                </div>

                                <div class="text-right text-xs font-black uppercase tracking-wider text-slate-500">
                                    Allocation
                                </div>
                            </div>

                            <div class="divide-y divide-slate-100">
                                @php
                                    $includedRows = collect();

                                    foreach (($includedItems ?? collect()) as $item) {
                                        if (in_array($productType ?? '', ['bundle', 'core_bundle', 'core-bundle'], true)) {
                                            $unlimited = !empty($item->bundle_is_unlimited)
                                                || !empty($item->is_unlimited);

                                            $allocation = $item->allocation
                                                ?? $item->default_allocation;

                                            $includedRows->push((object) [
                                                'name' => $item->name,
                                                'allocation' => $unlimited
                                                    ? 'Unlimited'
                                                    : ($allocation !== null
                                                        ? rtrim(rtrim(number_format((float) $allocation, 2), '0'), '.')
                                                            . ' ' . ($item->bundle_unit_name ?? $item->allocation_unit ?? '')
                                                        : 'Included'),
                                            ]);
                                        } elseif (!empty($item->capability_allocations) && $item->capability_allocations->isNotEmpty()) {
                                            foreach ($item->capability_allocations as $capability) {
                                                $includedRows->push((object) [
                                                    'name' => $capability->display_name
                                                        ?? ucwords(
                                                            str_replace(
                                                                ['_', '-'],
                                                                ' ',
                                                                preg_replace('/^crm_/', '', $capability->capability_key ?? '')
                                                            )
                                                        ),
                                                    'allocation' => !empty($capability->is_unlimited)
                                                        ? 'Unlimited'
                                                        : ($capability->allocation !== null
                                                            ? rtrim(rtrim(number_format((float) $capability->allocation, 2), '0'), '.')
                                                                . ' ' . ($capability->display_unit ?? $item->allocation_unit ?? '')
                                                            : 'Included'),
                                                ]);
                                            }
                                        } else {
                                            $unlimited = !empty($item->bundle_is_unlimited)
                                                || !empty($item->is_unlimited);

                                            $allocation = $item->allocation
                                                ?? $item->default_allocation;

                                            $includedRows->push((object) [
                                                'name' => $item->name,
                                                'allocation' => $unlimited
                                                    ? 'Unlimited'
                                                    : ($allocation !== null
                                                        ? rtrim(rtrim(number_format((float) $allocation, 2), '0'), '.')
                                                            . ' ' . ($item->bundle_unit_name ?? $item->allocation_unit ?? '')
                                                        : 'Included'),
                                            ]);
                                        }
                                    }
                                @endphp

                                @foreach($includedRows as $index => $row)
                                    <div
                                        class="grid grid-cols-[1fr_auto] items-center gap-6 px-4 py-3"
                                        @if($index >= 5)
                                            x-show="showAllItems"
                                            x-cloak
                                        @endif
                                    >
                                        <div class="text-sm font-bold text-slate-900">
                                            {{ $row->name }}
                                        </div>

                                        <div class="text-right text-sm font-bold text-slate-900">
                                            {{ $row->allocation }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        @if($includedRows->count() > 5)
                            <div class="mt-4 text-center">
                                <button
                                    type="button"
                                    x-show="!showAllItems"
                                    @click="showAllItems = true"
                                    class="text-sm font-black text-blue-600 transition hover:text-blue-700"
                                >
                                    See More
                                </button>

                                <button
                                    type="button"
                                    x-show="showAllItems"
                                    @click="showAllItems = false"
                                    class="text-sm font-black text-blue-600 transition hover:text-blue-700"
                                >
                                    See Less
                                </button>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Purchase Summary --}}

                <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-5">
                    <div class="text-xs font-black uppercase tracking-wider text-slate-400">
                        Purchase Summary
                    </div>

                    <div class="mt-4 space-y-3">
                        <div class="flex items-center justify-between gap-6">
                            <span class="text-sm font-semibold text-slate-500">
                                Unit Price
                            </span>

                            <span class="text-sm font-black text-slate-900">
                                {{ $currency ?? $listing->currency ?? 'NGN' }}
                                {{ number_format((float) ($price ?? $listing->price ?? 0), 2) }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between gap-6">
                            <span class="text-sm font-semibold text-slate-500">
                                Quantity
                            </span>

                            <span
                                class="text-sm font-black text-slate-900"
                                x-text="quantity"
                            >
                                1
                            </span>
                        </div>

                        <div class="border-t border-slate-200 pt-3">
                            <div class="flex items-center justify-between gap-6">
                                <span class="text-sm font-black text-slate-900">
                                    Total
                                </span>

                                <span class="text-lg font-black text-slate-900">
                                    {{ $currency ?? $listing->currency ?? 'NGN' }}
                                    <span
                                        x-text="Number(total).toLocaleString(undefined, {
                                            minimumFractionDigits: 2,
                                            maximumFractionDigits: 2
                                        })"
                                    >
                                        {{ number_format((float) ($price ?? $listing->price ?? 0), 2) }}
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Coupon --}}
                <div class="mt-6 rounded-2xl border border-slate-200 p-5">
                    <div class="text-xs font-black uppercase tracking-wider text-slate-400">
                        Coupon Code
                    </div>

                    <div class="coupon-row mt-3 flex gap-3">
                        <input
                            type="text"
                            x-model="coupon"
                            placeholder="Enter coupon code"
                            class="min-w-0 flex-1 rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold outline-none focus:border-blue-500"
                        >

                        <button
                            type="button"
                            class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-black text-white transition hover:bg-slate-800">
                            Apply
                        </button>
                    </div>

                    <p class="mt-2 text-xs text-slate-400">
                        Enter a valid coupon code to apply an eligible discount.
                    </p>
                </div>


                {{-- Payment Methods --}}
                <div class="mt-6 rounded-2xl border border-slate-200 p-5">

                    <div class="text-xs font-black uppercase tracking-wider text-slate-400">
                        Payment Methods
                    </div>

                    <select
                        x-model="paymentMethod"
                        class="mt-3 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-800 outline-none focus:border-blue-500"
                    >
                        <option value="" disabled>
                            Select payment method
                        </option>

                        {{-- Online gateways --}}
                        @foreach(($onlineGateways ?? collect())->unique('slug') as $gateway)
                            <option value="online:{{ $gateway->slug }}">
                                {{ $gateway->name }} — Online Payment
                            </option>
                        @endforeach

                        {{-- Offline payment methods --}}
                        @foreach(($offlineMethods ?? collect()) as $method)
                            <option value="offline:{{ $method->id }}">
                                {{ $method->name }}
                                @if(!empty($method->description))
                                    — {{ $method->description }}
                                @else
                                    — Offline Payment
                                @endif
                            </option>
                        @endforeach

                        {{-- Wallet --}}
                        @if($walletEnabled ?? false)
                            <option value="wallet">
                                Wallet — {{ $walletCurrency ?? 'NGN' }}
                                {{ number_format((float) ($walletBalance ?? 0), 2) }}
                            </option>
                        @endif
                    </select>

                    @if(
                        ($onlineGateways ?? collect())->isEmpty()
                        && ($offlineMethods ?? collect())->isEmpty()
                        && !($walletEnabled ?? false)
                    )
                        <div class="mt-3 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm font-semibold text-amber-700">
                            No payment methods are currently available.
                        </div>
                    @endif

                </div>

                <form
                    method="POST"
                    action="{{ route('marketplace.payment') }}"
                    class="mt-6"
                >
                    @csrf

                    <input type="hidden"
                           name="checkout_context"
                           value="{{ ($saasCheckout ?? false) ? 'saas' : 'off_server' }}">

                    @if(($saasCheckout ?? false) && !empty($order?->id))
                        <input type="hidden"
                               name="order_id"
                               value="{{ $order->id }}">
                    @endif

                    <input type="hidden"
                           name="deployment_type"
                           value="{{ ($saasCheckout ?? false) ? 'saas' : 'off_server' }}">


                    <input type="hidden" name="product_type" value="{{ $productType }}">
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="payment_option" x-bind:value="paymentMethod">
                    <input type="hidden" name="quantity" x-bind:value="quantity">

                    <button
                        type="submit"
                        class="w-full rounded-2xl bg-blue-600 px-5 py-4 text-sm font-black text-white transition hover:bg-blue-700"
                    >
                        Continue to Payment
                    </button>
                </form>

            </div>

        </div>

    </div>
</div>

@endsection
