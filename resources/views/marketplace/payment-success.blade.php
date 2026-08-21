@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-slate-50 px-4 py-12">
    <div class="mx-auto max-w-2xl">

        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">

            <div class="px-6 py-10 text-center sm:px-10">

                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100">
                    <svg class="h-8 w-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 13l4 4L19 7"/>
                    </svg>
                </div>

                <h1 class="mt-6 text-2xl font-black text-slate-900">
                    Payment Completed
                </h1>

                <p class="mx-auto mt-3 max-w-lg text-sm leading-6 text-slate-500">
                    Your payment was received successfully and your marketplace order has been processed.
                </p>

                <div class="mx-auto mt-8 max-w-md rounded-2xl border border-slate-200 bg-slate-50 p-5 text-left">

                    <div class="flex items-center justify-between gap-4 border-b border-slate-200 pb-3">
                        <span class="text-xs font-semibold text-slate-500">
                            Order Reference
                        </span>

                        <span class="text-xs font-black text-slate-900">
                            {{ $order->reference ?? '—' }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between gap-4 pt-3">
                        <span class="text-xs font-semibold text-slate-500">
                            Payment Status
                        </span>

                        <span class="text-xs font-black uppercase text-emerald-600">
                            Paid
                        </span>
                    </div>

                </div>

                @if(!empty($entitlementActivated))
                    <div class="mt-6 rounded-2xl border border-emerald-100 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700">
                        Your product entitlement has been activated successfully.
                    </div>
                @else
                    <div class="mt-6 rounded-2xl border border-blue-100 bg-blue-50 px-5 py-4 text-sm font-semibold text-blue-700">
                        Your payment has been recorded and your order is being processed.
                    </div>
                @endif

                <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-center">

                    <a
                        href="{{ route('marketplace.developer.library') }}"
                        class="rounded-xl bg-blue-600 px-6 py-3 text-sm font-black text-white transition hover:bg-blue-700"
                    >
                        Go to My Library
                    </a>

                    <a
                        href="{{ route('developer.marketplace') }}"
                        class="rounded-xl border border-slate-200 bg-white px-6 py-3 text-sm font-black text-slate-700 transition hover:bg-slate-50"
                    >
                        Back to Marketplace
                    </a>

                </div>

            </div>

        </div>

    </div>
</div>
@endsection
