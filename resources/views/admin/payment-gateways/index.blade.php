@extends('admin.layouts.app')

@section('title', 'Payment Gateways')

@section('content')
<div class="min-h-full bg-slate-50 px-6 py-8">
    <div class="mx-auto max-w-6xl">

        <div class="mb-8">
            <p class="text-xs font-black uppercase tracking-[0.2em] text-blue-600">
                Site Settings
            </p>

            <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900">
                Payment Gateways
            </h1>

            <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-500">
                Configure how the central Esubiz checkout system accepts and
                processes payments across subscriptions, commissions, hybrid
                plans, credits, marketplace products and other platform sales.
            </p>
        </div>

        <div class="grid gap-6 md:grid-cols-2">

            <a
                href="{{ route('admin.payment-gateways.online.index') }}"
                class="group rounded-3xl border border-slate-200 bg-white p-7 shadow-sm transition duration-200 hover:-translate-y-1 hover:shadow-xl"
            >
                <div class="flex items-start justify-between">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                  d="M3 10h18M5 6h14a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2z"/>
                        </svg>
                    </div>

                    <span class="text-sm font-bold text-slate-400 transition group-hover:text-blue-600">
                        →
                    </span>
                </div>

                <h2 class="mt-6 text-xl font-black text-slate-900">
                    Online Gateway
                </h2>

                <p class="mt-3 text-sm leading-6 text-slate-500">
                    Configure online payment providers such as Paystack,
                    Flutterwave and other supported gateways.
                </p>

                <div class="mt-6 inline-flex items-center text-sm font-black text-blue-600">
                    Manage online gateways
                    <span class="ml-2 transition group-hover:translate-x-1">→</span>
                </div>
            </a>

            <a
                href="{{ route('admin.payment-gateways.offline.index') }}"
                class="group rounded-3xl border border-slate-200 bg-white p-7 shadow-sm transition duration-200 hover:-translate-y-1 hover:shadow-xl"
            >
                <div class="flex items-start justify-between">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-700">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                  d="M4 6h16M4 10h16M7 14h10M7 18h6"/>
                        </svg>
                    </div>

                    <span class="text-sm font-bold text-slate-400 transition group-hover:text-slate-700">
                        →
                    </span>
                </div>

                <h2 class="mt-6 text-xl font-black text-slate-900">
                    Offline Gateway
                </h2>

                <p class="mt-3 text-sm leading-6 text-slate-500">
                    Configure manual payment methods such as bank transfer,
                    deposit and other offline payment options.
                </p>

                <div class="mt-6 inline-flex items-center text-sm font-black text-slate-700">
                    Manage offline gateways
                    <span class="ml-2 transition group-hover:translate-x-1">→</span>
                </div>
            </a>

        </div>

    </div>
</div>
@endsection
