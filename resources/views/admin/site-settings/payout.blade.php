@extends('admin.layouts.app')

@section('title', 'Payout')

@section('content')
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">

    <div class="mb-8">
        <h1 class="text-2xl font-black text-slate-900">
            Payout
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            Manage automatic and manual payout options for Esubiz wallets.
        </p>
    </div>

    <div class="grid gap-6 md:grid-cols-2">

        <a
            href="{{ route('admin.site-settings.payout.automatic.index') }}"
            class="group rounded-3xl border border-slate-200 bg-white p-7 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
        >
            <div class="flex items-start justify-between gap-5">
                <div>
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 text-xl">
                        ⚡
                    </div>

                    <h2 class="text-xl font-black text-slate-900">
                        Automatic Payout
                    </h2>

                    <p class="mt-2 text-sm leading-6 text-slate-500">
                        Manage Flutterwave, PayPal, NOWPayments and future automatic payout providers.
                    </p>
                </div>

                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-600">
                    {{ $automaticCount }}
                </span>
            </div>

            <div class="mt-6 text-sm font-black text-blue-600 group-hover:text-blue-700">
                Manage Automatic Payout →
            </div>
        </a>


        <a
            href="{{ route('admin.site-settings.payout.manual.index') }}"
            class="group rounded-3xl border border-slate-200 bg-white p-7 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
        >
            <div class="flex items-start justify-between gap-5">
                <div>
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-50 text-xl">
                        🏦
                    </div>

                    <h2 class="text-xl font-black text-slate-900">
                        Manual Payout
                    </h2>

                    <p class="mt-2 text-sm leading-6 text-slate-500">
                        Create and manage unlimited manual payout methods and review payout requests.
                    </p>
                </div>

                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-600">
                    {{ $manualCount }}
                </span>
            </div>

            <div class="mt-6 text-sm font-black text-blue-600 group-hover:text-blue-700">
                Manage Manual Payout →
            </div>
        </a>

    </div>

</div>
@endsection
