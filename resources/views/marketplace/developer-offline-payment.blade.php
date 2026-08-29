@extends('admin.layouts.app')
{{-- ESUBIZ_OFFLINE_PAYMENT_LAYOUT_FIX_V1 --}}

@section('content')

<div class="min-h-screen bg-slate-50 py-16">
    <div class="mx-auto max-w-3xl px-6 lg:px-8">

        {{-- ESUBIZ_UNIFIED_OFFLINE_PAYMENT_FRONTEND_V1 --}}
        <div class="mb-6">
            @php
                $offlineBackUrl =
                    !empty($order?->id)
                        ? route(
                            'marketplace.checkout',
                            ['order' => $order->id]
                        )
                        : (
                            !empty($metadata['product_id'])
                                ? route(
                                    'marketplace.developer.checkout',
                                    [
                                        'productType' =>
                                            $metadata['product_type']
                                            ?? 'addon',

                                        'productId' =>
                                            $metadata['product_id'],
                                    ]
                                )
                                : url()->previous()
                        );
            @endphp

            <a
                href="{{ $offlineBackUrl }}"
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

                        {{-- ESUBIZ_OFFLINE_RECEIPT_CONFIRMATION_MODAL_V4 --}}
                        @if(session('offline_payment_success') || !empty($metadata['receipt_path']))

                            @php
                                $isDeveloperMode =
                                    session('account_mode') === 'developer';

                                $dashboardUrl = $isDeveloperMode
                                    ? route('developer.dashboard')
                                    : route('user.dashboard');

                                $websitesUrl = $isDeveloperMode
                                    ? route('developer.dashboard')
                                    : route('user.websites.index');
                            @endphp

                            <div
                                class="fixed inset-0 z-[9999] flex items-center justify-center bg-slate-950/60 px-4"
                                role="dialog"
                                aria-modal="true"
                                aria-labelledby="offline-payment-confirmation-title"
                            >
                                <div class="w-full max-w-md rounded-3xl bg-white p-7 shadow-2xl">

                                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-amber-100">
                                        <svg
                                            class="h-7 w-7 text-amber-700"
                                            viewBox="0 0 24 24"
                                            fill="none"
                                            stroke="currentColor"
                                            stroke-width="2"
                                        >
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                d="M12 6v6l4 2"
                                            />
                                            <circle cx="12" cy="12" r="9"/>
                                        </svg>
                                    </div>

                                    <h2
                                        id="offline-payment-confirmation-title"
                                        class="mt-5 text-2xl font-black text-slate-900"
                                    >
                                        Awaiting Admin Verification
                                    </h2>

                                    <p class="mt-3 text-sm leading-6 text-slate-600">
                                        {{ session(
                                            'offline_payment_success',
                                            'Your payment receipt has been submitted successfully and is awaiting administrator verification.'
                                        ) }}
                                    </p>

                                    @if(!empty($metadata['receipt_original_name']))
                                        <p class="mt-3 text-xs font-semibold text-slate-500">
                                            Receipt:
                                            {{ $metadata['receipt_original_name'] }}
                                        </p>
                                    @endif

                                    <div class="mt-7 grid grid-cols-2 gap-3">

                                        <a
                                            href="{{ $websitesUrl }}"
                                            class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-bold text-slate-800 transition hover:bg-slate-50"
                                        >
                                            Websites
                                        </a>

                                        <a
                                            href="{{ $dashboardUrl }}"
                                            class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-3 text-sm font-bold text-white transition hover:bg-slate-800"
                                        >
                                            Dashboard
                                        </a>

                                    </div>

                                </div>
                            </div>

                        @endif

                        {{-- ESUBIZ_OFFLINE_RECEIPT_UPLOAD_V1 --}}
                        <form
                            method="POST"
                            action="{{ route('marketplace.developer.offline-payment.receipt', $attempt->id) }}"
                            enctype="multipart/form-data"
                            class="mt-5"
                        >
                            @csrf

                            <label class="block text-sm font-bold text-slate-700">
                                {{ $paymentMethod->receipt_upload_label
                                    ?? 'Upload payment receipt' }}
                            </label>

                            <input
                                type="file"
                                name="receipt"
                                accept=".jpg,.jpeg,.png,.pdf,image/jpeg,image/png,application/pdf"
                                required
                                class="mt-2 block w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600"
                            >

                            @error('receipt')
                                <p class="mt-2 text-sm font-semibold text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror

                            <p class="mt-2 text-xs text-slate-400">
                                JPG, JPEG, PNG or PDF. Maximum file size: 10 MB.
                            </p>

                            <button
                                type="submit"
                                class="mt-4 inline-flex items-center justify-center rounded-xl bg-slate-900 px-5 py-3 text-sm font-bold text-white transition hover:bg-slate-800"
                            >
                                Submit Payment Receipt
                            </button>
                        </form>

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
