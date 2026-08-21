@extends('admin.layouts.app')

@section('content')
<div class="min-h-screen bg-slate-50">
    <div class="mx-auto max-w-6xl px-6 py-10">

        <div class="mb-8">
            <div class="text-[10px] font-black uppercase tracking-[0.2em] text-blue-600">
                Developer Marketplace
            </div>

            <h1 class="mt-2 text-2xl font-black text-slate-900">
                Checkout
            </h1>

            <p class="mt-2 text-sm text-slate-500">
                Review your marketplace purchase before proceeding to payment.
            </p>
        </div>

        @php
            $quantity = max(1, (int) request('quantity', 1));
            $price = (float) ($product->off_server_price ?? 0);
            $total = $price * $quantity;
            $currency = $product->off_server_currency ?? 'NGN';
        @endphp

        <div class="grid gap-6 lg:grid-cols-3">

            {{-- PRODUCT --}}
            <div class="lg:col-span-2 rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">

                <div class="flex items-start justify-between gap-5">

                    <div>
                        <div class="text-[10px] font-black uppercase tracking-[0.18em] text-blue-600">
                            Off-server marketplace product
                        </div>

                        <h2 class="mt-2 text-xl font-black text-slate-900">
                            {{ $product->name }}
                        </h2>

                        @if(!empty($product->description))
                            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                                {{ $product->description }}
                            </p>
                        @endif
                    </div>

                    <div class="shrink-0 rounded-xl bg-blue-50 px-3 py-2 text-[10px] font-black uppercase text-blue-700">
                        {{ $productType }}
                    </div>

                </div>

                <div class="mt-8 rounded-2xl border border-slate-100 bg-slate-50 p-5">

                    <div class="flex items-center justify-between">
                        <span class="text-sm font-semibold text-slate-600">
                            Quantity
                        </span>

                        <span class="text-lg font-black text-slate-900">
                            {{ $quantity }}
                        </span>
                    </div>

                    <div class="mt-4 flex items-center justify-between border-t border-slate-200 pt-4">
                        <span class="text-sm font-semibold text-slate-600">
                            Unit price
                        </span>

                        <span class="font-black text-slate-900">
                            {{ $currency }} {{ number_format($price, 2) }}
                        </span>
                    </div>

                </div>

            </div>

            {{-- CHECKOUT SUMMARY --}}
            <div
                x-data="{
                    quantity: {{ $quantity }},
                    unitPrice: {{ $price }},
                    paymentMethod: '{{ ($onlineGateways ?? collect())->isNotEmpty()
                        ? 'online:' . $onlineGateways->first()->slug
                        : (($offlineMethods ?? collect())->isNotEmpty()
                            ? 'offline:' . $offlineMethods->first()->id
                            : '') }}',
                    get total() {
                        return Math.max(1, Number(this.quantity || 1)) * this.unitPrice;
                    }
                }"
                class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm"
            >

                <div class="text-sm font-bold text-slate-500">
                    Order Summary
                </div>

                <div class="mt-5 flex items-start justify-between gap-4">
                    <span class="text-sm font-semibold text-slate-700">
                        {{ $product->name }}
                    </span>

                    <span class="text-sm font-black text-slate-900">
                        × <span x-text="Math.max(1, Number(quantity || 1))"></span>
                    </span>
                </div>

                <div class="mt-5 border-t border-slate-100 pt-5">

                    <div class="text-xs font-black uppercase tracking-wider text-slate-400">
                        Quantity
                    </div>

                    <div class="mt-3 flex items-center gap-2">

                        <button
                            type="button"
                            @click="quantity = Math.max(1, Number(quantity || 1) - 1)"
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-lg font-black text-slate-700 transition hover:bg-slate-50"
                        >
                            −
                        </button>

                        <input
                            type="number"
                            min="1"
                            step="1"
                            x-model.number="quantity"
                            @input="quantity = Math.max(1, Number(quantity || 1))"
                            class="h-10 w-full rounded-xl border border-slate-200 bg-white px-3 text-center text-sm font-black text-slate-900"
                        >

                        <button
                            type="button"
                            @click="quantity = Math.max(1, Number(quantity || 1) + 1)"
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white text-lg font-black text-slate-700 transition hover:bg-slate-50"
                        >
                            +
                        </button>

                    </div>

                </div>

                <div class="mt-5 border-t border-slate-100 pt-5">

                    <div class="flex items-center justify-between">
                        <span class="text-sm font-bold text-slate-500">
                            Unit price
                        </span>

                        <span class="text-sm font-black text-slate-900">
                            {{ $currency }} {{ number_format($price, 2) }}
                        </span>
                    </div>

                    <div class="mt-4 flex items-center justify-between">
                        <span class="text-sm font-bold text-slate-500">
                            Total
                        </span>

                        <span class="text-xl font-black text-slate-900">
                            {{ $currency }}
                            <span
                                x-text="Number(total).toLocaleString(undefined, {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                })"
                            ></span>
                        </span>
                    </div>

                </div>

                <div class="mt-6 border-t border-slate-100 pt-6">

                    <div class="text-xs font-black uppercase tracking-wider text-slate-400">
                        Payment Method
                    </div>

                    <div class="mt-3 space-y-2">

                        {{-- ONLINE GATEWAYS --}}
                        @foreach(($onlineGateways ?? collect()) as $gateway)

                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 transition hover:border-blue-300 hover:bg-blue-50">

                                <input
                                    type="radio"
                                    name="payment_option_selector"
                                    value="online:{{ $gateway->slug }}"
                                    x-model="paymentMethod"
                                    class="h-4 w-4 text-blue-600"
                                >

                                <span class="min-w-0 flex-1">

                                    <span class="block text-sm font-bold text-slate-800">
                                        {{ $gateway->name }}
                                    </span>

                                    <span class="mt-0.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                                        Online payment
                                    </span>

                                </span>

                            </label>

                        @endforeach

                        {{-- OFFLINE METHODS --}}
                        @foreach(($offlineMethods ?? collect()) as $method)

                            <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 transition hover:border-blue-300 hover:bg-blue-50">

                                <input
                                    type="radio"
                                    name="payment_option_selector"
                                    value="offline:{{ $method->id }}"
                                    x-model="paymentMethod"
                                    class="h-4 w-4 text-blue-600"
                                >

                                <span class="min-w-0 flex-1">

                                    <span class="block text-sm font-bold text-slate-800">
                                        {{ $method->name }}
                                    </span>

                                    <span class="mt-0.5 block text-[10px] font-semibold uppercase tracking-wide text-slate-400">
                                        Offline payment
                                    </span>

                                </span>

                            </label>

                        @endforeach

                        @if(($onlineGateways ?? collect())->isEmpty() && ($offlineMethods ?? collect())->isEmpty())

                            <div class="rounded-xl border border-amber-100 bg-amber-50 px-4 py-3 text-xs font-semibold text-amber-700">
                                No enabled payment methods are currently available.
                            </div>

                        @endif

                    </div>

                </div>

                <form method="POST" action="{{ route('marketplace.developer.checkout.submit') }}">
                    @csrf

                    <input type="hidden" name="product_type" value="{{ $productType }}">
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="quantity" :value="quantity">
                    <input type="hidden" name="payment_option" :value="paymentMethod">
                    
                    
                    <input type="hidden" name="payment_option" :value="paymentMethod">

                    <div class="mt-7">
                        <button
                            type="submit"
                            @click="quantity = Math.max(1, Number(quantity || 1))"
                            class="w-full rounded-xl bg-blue-600 px-5 py-3 text-sm font-black !text-white shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:bg-blue-700 hover:shadow-md"
                            style="color:#fff !important;"
                        >
                            Proceed to Payment
                        </button>
                    </div>
                </form>

                <p class="mt-3 text-center text-[11px] leading-5 text-slate-400">
                    Select your preferred enabled payment method to continue.
                </p>

            </div>

        </div>

    </div>
</div>
@endsection
