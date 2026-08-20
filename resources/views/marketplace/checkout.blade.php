@extends('admin.layouts.app')

@section('content')

<div class="min-h-screen bg-slate-50 px-6 py-8">
    <div class="mx-auto max-w-3xl">

        @if(session('success'))
            <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        <div class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">

            <div class="text-xs font-black uppercase tracking-widest text-blue-600">
                Marketplace Checkout
            </div>

            <h1 class="mt-2 text-2xl font-black text-slate-900">
                {{ ucwords(str_replace('_', ' ', $order->product_type)) }}
            </h1>

            <div class="mt-8 grid gap-5 sm:grid-cols-2">

                <div class="rounded-2xl bg-slate-50 p-5">
                    <div class="text-xs text-slate-400">Product ID</div>
                    <div class="mt-1 font-bold text-slate-900">
                        #{{ $order->product_id }}
                    </div>
                </div>

                <div class="rounded-2xl bg-slate-50 p-5">
                    <div class="text-xs text-slate-400">Workspace</div>
                    <div class="mt-1 font-bold text-slate-900">
                        #{{ $order->workspace_id }}
                    </div>
                </div>

                <div class="rounded-2xl bg-slate-50 p-5">
                    <div class="text-xs text-slate-400">Product</div>
                    <div class="mt-1 font-bold text-slate-900">
                        {{ $order->listing_title }}
                    </div>
                </div>

                <div class="rounded-2xl bg-slate-50 p-5">
                    <div class="text-xs text-slate-400">Order status</div>
                    <div class="mt-1 font-bold text-amber-600">
                        Payment Pending
                    </div>
                </div>

            </div>

            <div class="mt-8 rounded-2xl border border-slate-200 p-6">
                <div class="text-sm text-slate-400">
                    Amount
                </div>

                <div class="mt-1 text-3xl font-black text-slate-900">
                    {{ $order->currency }}
                    {{ number_format((float) $order->amount, 2) }}
                </div>
            </div>

            <div class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 p-5">
                <div class="font-bold text-amber-900">
                    Payment gateway pending
                </div>

                <p class="mt-1 text-sm text-amber-700">
                    Your order has been created successfully, but no entitlement
                    will be activated until the payment gateway confirms payment.
                </p>
            </div>

        </div>
    </div>
</div>

@endsection
