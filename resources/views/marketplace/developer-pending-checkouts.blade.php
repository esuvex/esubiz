@extends('admin.layouts.app')

@section('content')
<div class="min-h-screen bg-slate-50">
    <div class="mx-auto max-w-6xl px-6 py-10">

        <div class="mb-8">
            <div class="text-[10px] font-black uppercase tracking-[0.2em] text-blue-600">
                Developer Marketplace
            </div>

            <h1 class="mt-2 text-2xl font-black text-slate-900">
                Pending Checkouts
            </h1>

            <p class="mt-2 text-sm text-slate-500">
                Complete payment for your pending marketplace purchases.
            </p>
        </div>

        @if($checkouts->isEmpty())

            <div class="rounded-3xl border border-slate-200 bg-white p-10 text-center shadow-sm">
                <div class="text-lg font-black text-slate-900">
                    No pending checkouts
                </div>

                <p class="mt-2 text-sm text-slate-500">
                    You currently have no marketplace purchases waiting for payment.
                </p>

                <a
                    href="{{ route('marketplace.developer.addons') }}"
                    class="mt-6 inline-flex rounded-xl bg-blue-600 px-5 py-3 text-xs font-black !text-white shadow-sm transition hover:bg-blue-700"
                    style="color:#fff !important;"
                >
                    Browse Marketplace
                </a>
            </div>

        @else

            <div class="space-y-4">

                @foreach($checkouts as $checkout)

                    <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md">

                        <div class="flex flex-col gap-5 md:flex-row md:items-center md:justify-between">

                            <div>
                                <div class="text-[10px] font-black uppercase tracking-wider text-blue-600">
                                    {{ str_replace('_', ' ', $checkout->product_type) }}
                                </div>

                                <h2 class="mt-1 text-lg font-black text-slate-900">
                                    {{ $checkout->listing_title }}
                                </h2>

                                <div class="mt-2 text-xs font-semibold text-slate-400">
                                    Order {{ $checkout->reference }}
                                </div>
                            </div>

                            <div class="text-left md:text-right">
                                <div class="text-xl font-black text-slate-900">
                                    {{ $checkout->currency }}
                                    {{ number_format($checkout->amount, 2) }}
                                </div>

                                <div class="mt-1 text-[10px] font-black uppercase text-amber-600">
                                    Payment Pending
                                </div>
                            </div>

                        </div>

                        <div class="mt-5 flex flex-col gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:items-center sm:justify-between">

                            <div class="text-xs text-slate-500">
                                @if($checkout->transaction_reference)
                                    Transaction:
                                    <span class="font-bold text-slate-700">
                                        {{ $checkout->transaction_reference }}
                                    </span>
                                @else
                                    Payment transaction pending
                                @endif
                            </div>

                            <a
                                href="{{ route('marketplace.developer.pending-checkouts.continue', [
                                    'order' => $checkout->id,
                                    'transaction' => $checkout->transaction_reference,
                                ]) }}"
                                class="inline-flex justify-center rounded-xl bg-blue-600 px-5 py-3 text-xs font-black !text-white shadow-sm transition-all duration-300 hover:-translate-y-0.5 hover:bg-blue-700 hover:shadow-md"
                                style="color:#fff !important;"
                            >
                                Continue Payment
                            </a>

                        </div>

                    </div>

                @endforeach

            </div>

        @endif

    </div>
</div>
@endsection
