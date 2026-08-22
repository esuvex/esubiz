@extends('frontend.layouts.app')

@section('content')

<div class="min-h-screen bg-slate-50 py-16">
    <div class="mx-auto max-w-3xl px-6 lg:px-8">

        <div class="mb-6">
            <a
                href="{{ route('marketplace.developer.checkout', [
                    'productType' => $metadata['product_type'] ?? 'addon',
                    'productId' => $metadata['product_id'] ?? 0,
                ]) }}"
                class="text-sm font-bold text-blue-600 hover:text-blue-700"
            >
                ← Back to Checkout
            </a>
        </div>

        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">

            <div class="border-b border-slate-100 px-6 py-7 sm:px-8">
                <p class="text-sm font-bold uppercase tracking-wide text-blue-600">
                    Offline Payment
                </p>

                <h1 class="mt-2 text-2xl font-black text-slate-900">
                    Complete Your Payment
                </h1>

                <p class="mt-2 text-sm leading-6 text-slate-500">
                    Follow the payment instructions below. Your purchase will remain
                    pending until the payment is reviewed and confirmed.
                </p>
            </div>

            <div class="space-y-6 px-6 py-7 sm:px-8">

                <div class="rounded-2xl bg-slate-50 p-5">

                    <div class="flex items-center justify-between gap-4">
                        <span class="text-sm font-semibold text-slate-500">
                            Payment Method
                        </span>

                        <span class="text-sm font-black text-slate-900">
                            {{ $paymentMethod->name }}
                        </span>
                    </div>

                    <div class="mt-4 flex items-center justify-between gap-4">
                        <span class="text-sm font-semibold text-slate-500">
                            Amount
                        </span>

                        <span class="text-lg font-black text-slate-900">
                            {{ $transaction->currency }}
                            {{ number_format((float) $transaction->amount, 2) }}
                        </span>
                    </div>

                    <div class="mt-4 flex items-center justify-between gap-4">
                        <span class="text-sm font-semibold text-slate-500">
                            Payment Reference
                        </span>

                        <span class="break-all text-sm font-bold text-slate-900">
                            {{ $transaction->reference }}
                        </span>
                    </div>

                </div>

                @if(session('status'))
                    <div class="rounded-2xl border border-blue-200 bg-blue-50 p-4 text-sm font-semibold text-blue-700">
                        {{ session('status') }}
                    </div>
                @endif

                <div>

                    <h2 class="text-lg font-black text-slate-900">
                        Payment Instructions
                    </h2>

                    <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-5 text-sm leading-7 text-slate-600">
                        {!! nl2br(e(
                            $paymentMethod->instructions
                            ?? 'Please follow the payment instructions provided by the administrator.'
                        )) !!}
                    </div>

                </div>

                @if($paymentMethod->receipt_upload_enabled)

                    <div class="rounded-2xl border border-slate-200 p-5">

                        <h2 class="text-lg font-black text-slate-900">
                            Payment Receipt
                        </h2>

                        <p class="mt-2 text-sm leading-6 text-slate-500">
                            {{ $paymentMethod->receipt_upload_help
                                ?? 'Upload your payment receipt or proof of payment.' }}
                        </p>

                        <div class="mt-5">

                            <label class="block text-sm font-bold text-slate-700">
                                {{ $paymentMethod->receipt_upload_label
                                    ?? 'Upload payment receipt' }}
                            </label>

                            <input
                                type="file"
                                disabled
                                class="mt-2 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-500"
                            >

                            <p class="mt-2 text-xs text-slate-400">
                                Receipt submission will be connected next.
                            </p>

                        </div>

                    </div>

                @endif

                <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm leading-6 text-amber-800">

                    <strong>Important:</strong>
                    Do not make another payment using a different method while this
                    transaction is pending. Keep your payment reference for your records.

                </div>

            </div>

        </div>

    </div>
</div>

@endsection
