@extends('admin.layouts.app')

@section('title', 'Create Gift Card')

@section('content')
<div class="min-h-full bg-slate-50 px-6 py-8">
    <div class="mx-auto max-w-5xl">

        <div class="mb-5">
            <a href="{{ route('admin.payment-gateways.gift-card') }}"
               class="inline-flex items-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm transition hover:bg-slate-50">
                ← Back to Gift Cards
            </a>
        </div>

        <div class="mb-8">
            <p class="text-xs font-black uppercase tracking-[0.2em] text-amber-600">
                Payment Gateways
            </p>

            <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900">
                Create Gift Card
            </h1>

            <p class="mt-3 max-w-3xl text-sm leading-6 text-slate-500">
                Create an Esubiz gift card and configure how and where it can be used.
            </p>
        </div>

        @if($errors->any())
            <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4">
                <ul class="space-y-1 text-sm font-semibold text-red-700">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST"
              action="{{ route('admin.payment-gateways.gift-card.store') }}"
              class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm">
            @csrf

            <div class="grid gap-6 md:grid-cols-2">

                <div class="md:col-span-2">
                    <label class="text-xs font-bold text-slate-500">
                        Gift Card Name
                    </label>

                    <input
                        type="text"
                        name="name"
                        value="{{ old('name', 'Esubiz Gift Card') }}"
                        class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-amber-500"
                        required>
                </div>

                <div>
                    <label class="text-xs font-bold text-slate-500">
                        Amount
                    </label>

                    <input
                        type="number"
                        name="amount"
                        min="0.01"
                        step="0.01"
                        value="{{ old('amount') }}"
                        class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-amber-500"
                        required>
                </div>

                <div>
                    <label class="text-xs font-bold text-slate-500">
                        Currency
                    </label>

                    <input
                        type="text"
                        name="currency"
                        maxlength="3"
                        value="{{ old('currency', 'NGN') }}"
                        class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm uppercase outline-none focus:border-amber-500"
                        required>
                </div>

                <div>
                    <label class="text-xs font-bold text-slate-500">
                        Usage Limit
                    </label>

                    <input
                        type="number"
                        name="usage_limit"
                        min="1"
                        value="{{ old('usage_limit') }}"
                        placeholder="Unlimited"
                        class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-amber-500">
                </div>

                <div>
                    <label class="text-xs font-bold text-slate-500">
                        Starts At
                    </label>

                    <input
                        type="datetime-local"
                        name="starts_at"
                        value="{{ old('starts_at') }}"
                        class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-amber-500">
                </div>

                <div>
                    <label class="text-xs font-bold text-slate-500">
                        Expires At
                    </label>

                    <input
                        type="datetime-local"
                        name="expires_at"
                        value="{{ old('expires_at') }}"
                        class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-amber-500">
                </div>

            </div>

            <div class="mt-8 border-t border-slate-100 pt-7">
                <h2 class="text-lg font-black text-slate-900">
                    Usage Rules
                </h2>

                <div class="mt-5 grid gap-4 md:grid-cols-2">

                    <label class="flex items-center justify-between rounded-2xl border border-slate-200 p-5">
                        <div>
                            <div class="font-black text-slate-900">
                                Checkout
                            </div>
                            <div class="mt-1 text-sm text-slate-500">
                                Allow this card to pay for products and services.
                            </div>
                        </div>

                        <input
                            type="checkbox"
                            name="usable_at_checkout"
                            value="1"
                            class="h-5 w-5"
                            checked>
                    </label>

                    <label class="flex items-center justify-between rounded-2xl border border-slate-200 p-5">
                        <div>
                            <div class="font-black text-slate-900">
                                Wallet Funding
                            </div>
                            <div class="mt-1 text-sm text-slate-500">
                                Allow this card to fund a user's wallet.
                            </div>
                        </div>

                        <input
                            type="checkbox"
                            name="usable_for_wallet_funding"
                            value="1"
                            class="h-5 w-5"
                            checked>
                    </label>

                    <label class="flex items-center justify-between rounded-2xl border border-slate-200 p-5">
                        <div>
                            <div class="font-black text-slate-900">
                                Partial Redemption
                            </div>
                            <div class="mt-1 text-sm text-slate-500">
                                Allow remaining balance to be used later.
                            </div>
                        </div>

                        <input
                            type="checkbox"
                            name="allow_partial_redemption"
                            value="1"
                            class="h-5 w-5"
                            checked>
                    </label>

                    <label class="flex items-center justify-between rounded-2xl border border-slate-200 p-5">
                        <div>
                            <div class="font-black text-slate-900">
                                Enabled
                            </div>
                            <div class="mt-1 text-sm text-slate-500">
                                Make the card immediately usable.
                            </div>
                        </div>

                        <input
                            type="checkbox"
                            name="enabled"
                            value="1"
                            class="h-5 w-5"
                            checked>
                    </label>

                </div>
            </div>

            <div class="mt-8 flex justify-end">
                <button
                    type="submit"
                    class="rounded-xl bg-amber-600 px-6 py-3 text-sm font-black text-white hover:bg-amber-700">
                    Create Gift Card
                </button>
            </div>

        </form>

    </div>
</div>
@endsection
