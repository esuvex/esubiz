@extends('admin.layouts.app')

@section('title', 'Offline Payment Gateway')


@section('content')

<div class="mb-6">
    <a href="{{ route('admin.payment-gateways.index') }}"
       class="inline-flex items-center gap-2 text-sm font-black text-blue-600 hover:text-blue-700">
        <span>←</span>
        Back to Gateways
    </a>
</div>


<div class="space-y-8">

    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
            <ul class="list-disc space-y-1 pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-900">
                Offline Payment Gateway
            </h1>

            <p class="mt-1 max-w-2xl text-sm leading-6 text-slate-500">
                Configure payment methods that require customers to complete
                payment outside the online checkout process.
            </p>
        </div>

        <button
            type="button"
            onclick="document.getElementById('add-offline-method').classList.toggle('hidden')"
            class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-black text-white shadow-sm transition-all hover:bg-blue-700 hover:shadow-lg active:scale-[0.98]"
        >
            Add Offline Method
        </button>
    </div>

    <div
        id="add-offline-method"
        class="hidden overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm"
    >
        <div class="border-b border-slate-100 px-6 py-5">
            <h2 class="text-lg font-black text-slate-900">
                Add Offline Payment Method
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Create another offline payment option for the central checkout.
            </p>
        </div>

        <form method="POST" action="{{ route('admin.payment-gateways.offline.store') }}">
            @csrf

            <div class="grid gap-6 px-6 py-6 md:grid-cols-2">

                <div>
                    <label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">
                        Method Name
                    </label>

                    <input
                        type="text"
                        name="name"
                        required
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white"
                        placeholder="e.g. Bank Deposit"
                    >
                </div>

                <div>
                    <label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">
                        Method Type
                    </label>

                    <select
                        name="type"
                        required
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-900 outline-none focus:border-blue-500 focus:bg-white"
                    >
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="cash">Cash</option>
                        <option value="manual">Manual Payment</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">
                        Priority
                    </label>

                    <input
                        type="number"
                        name="priority"
                        min="1"
                        value="1"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-900 outline-none focus:border-blue-500 focus:bg-white"
                    >
                </div>

                <div class="flex items-center">
                    <label class="inline-flex cursor-pointer items-center gap-3">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            class="h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                        >

                        <span class="text-sm font-bold text-slate-700">
                            Enable method
                        </span>
                    </label>
                </div>

                <div class="md:col-span-2">
                    <label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">
                        Payment Instructions
                    </label>

                    <textarea
                        name="instructions"
                        rows="5"
                        class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white"
                        placeholder="Enter the instructions customers should follow after selecting this payment method."
                    ></textarea>
                </div>

                <div class="md:col-span-2 rounded-2xl border border-slate-200 bg-slate-50 p-5">
                    <div class="mb-5">
                        <h3 class="text-sm font-black text-slate-900">
                            Receipt Upload
                        </h3>

                        <p class="mt-1 text-xs leading-5 text-slate-500">
                            Allow customers to upload proof of payment after completing this offline payment.
                        </p>
                    </div>

                    <label class="mb-5 inline-flex cursor-pointer items-center gap-3">
                        <input
                            type="hidden"
                            name="receipt_upload_enabled"
                            value="0"
                        >

                        <input
                            type="checkbox"
                            name="receipt_upload_enabled"
                            value="1"
                            class="h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                        >

                        <span class="text-sm font-bold text-slate-700">
                            Require receipt upload
                        </span>
                    </label>

                    <div class="grid gap-5 md:grid-cols-2">

                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">
                                Upload Label
                            </label>

                            <input
                                type="text"
                                name="receipt_upload_label"
                                value="Upload payment receipt"
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-900 outline-none focus:border-blue-500"
                                placeholder="Upload payment receipt"
                            >
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">
                                Customer Help Text
                            </label>

                            <input
                                type="text"
                                name="receipt_upload_help"
                                value="Upload your payment receipt or proof of payment."
                                class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-900 outline-none focus:border-blue-500"
                                placeholder="Upload your payment receipt or proof of payment."
                            >
                        </div>

                    </div>
                </div>

            </div>


            <div class="space-y-5 px-6 pb-6">

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                    <h3 class="text-sm font-black text-slate-900">
                        Allowed Gateway Uses
                    </h3>

                    <div class="mt-4 grid gap-3 sm:grid-cols-2">

                        <div class="sm:col-span-2">
                            <div class="mb-2 text-xs font-black uppercase tracking-wider text-blue-600">
                                User Mode
                            </div>
                        </div>

                        @foreach([
                            'user_marketplace_checkout'
                                => 'Marketplace Checkout',

                            'user_wallet_funding'
                                => 'Wallet Funding',

                            'user_checkout_link'
                                => 'Checkout Link',
                        ] as $context => $label)

                            <label class="flex items-center gap-3 text-sm font-bold text-slate-700">
                                <input
                                    type="checkbox"
                                    name="usage_contexts[]"
                                    value="{{ $context }}"
                                    checked
                                >

                                {{ $label }}
                            </label>

                        @endforeach


                        <div class="mt-3 sm:col-span-2">
                            <div class="mb-2 text-xs font-black uppercase tracking-wider text-violet-600">
                                Developer Mode
                            </div>
                        </div>

                        @foreach([
                            'developer_marketplace_checkout'
                                => 'Marketplace Checkout',

                            'developer_wallet_funding'
                                => 'Wallet Funding',

                            'developer_checkout_link'
                                => 'Checkout Link',
                        ] as $context => $label)

                            <label class="flex items-center gap-3 text-sm font-bold text-slate-700">
                                <input
                                    type="checkbox"
                                    name="usage_contexts[]"
                                    value="{{ $context }}"
                                    checked
                                >

                                {{ $label }}
                            </label>

                        @endforeach

                    </div>
                </div>


                <div
                    x-data="{
                        enabled: false,
                        type: 'none',
                        provider: ''
                    }"
                    class="rounded-2xl border border-slate-200 bg-slate-50 p-5"
                >

                    <div class="flex items-center justify-between gap-4">

                        <div>
                            <h3 class="text-sm font-black text-slate-900">
                                Currency / Crypto Conversion
                            </h3>
                        </div>

                        <label class="flex items-center gap-2 text-sm font-bold text-slate-700">
                            <input
                                type="checkbox"
                                name="conversion_enabled"
                                value="1"
                                x-model="enabled"
                            >

                            Enable
                        </label>

                    </div>

                    <div
                        x-show="enabled"
                        x-cloak
                        class="mt-5 grid gap-4 sm:grid-cols-2"
                    >

                        <select
                            name="conversion_type"
                            x-model="type"
                            class="rounded-xl border border-slate-300 bg-white px-4 py-3"
                        >
                            <option value="none">None</option>
                            <option value="fiat">Fiat Currency</option>
                            <option value="crypto">Cryptocurrency</option>
                        </select>

                        <select
                            name="conversion_provider"
                            x-model="provider"
                            class="rounded-xl border border-slate-300 bg-white px-4 py-3"
                        >
                            <option value="">Select rate source</option>

                            <option
                                value="frankfurter"
                                x-show="type === 'fiat'"
                            >
                                Frankfurter
                            </option>

                            <option
                                value="coingecko"
                                x-show="type === 'crypto'"
                            >
                                CoinGecko
                            </option>

                            <option value="manual">
                                Manual Rate
                            </option>
                        </select>

                        <input
                            type="text"
                            name="conversion_target"
                            placeholder="USD, GBP, USDT, BTC"
                            class="rounded-xl border border-slate-300 bg-white px-4 py-3 uppercase"
                        >

                        <input
                            x-show="provider === 'manual'"
                            x-cloak
                            type="number"
                            step="0.000000000001"
                            min="0.000000000001"
                            name="manual_conversion_rate"
                            placeholder="Manual rate"
                            class="rounded-xl border border-slate-300 bg-white px-4 py-3"
                        >

                    </div>
                </div>


                <div
                    x-data="{
                        enabled: false,
                        type: 'percentage'
                    }"
                    class="rounded-2xl border border-slate-200 bg-slate-50 p-5"
                >

                    <div class="flex items-center justify-between gap-4">

                        <h3 class="text-sm font-black text-slate-900">
                            Payment Markup
                        </h3>

                        <label class="flex items-center gap-2 text-sm font-bold text-slate-700">
                            <input
                                type="checkbox"
                                name="markup_enabled"
                                value="1"
                                x-model="enabled"
                            >

                            Enable
                        </label>

                    </div>

                    <div
                        x-show="enabled"
                        x-cloak
                        class="mt-5 grid gap-4 sm:grid-cols-2"
                    >

                        <select
                            name="markup_type"
                            x-model="type"
                            class="rounded-xl border border-slate-300 bg-white px-4 py-3"
                        >
                            <option value="percentage">Percentage</option>
                            <option value="fixed">Fixed</option>
                        </select>

                        <input
                            type="number"
                            step="0.00000001"
                            min="0"
                            name="markup_value"
                            value="0"
                            class="rounded-xl border border-slate-300 bg-white px-4 py-3"
                        >

                    </div>

                </div>

            </div>

            <div class="flex justify-end border-t border-slate-100 bg-slate-50 px-6 py-4">
                <button
                    type="submit"
                    class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-black text-white shadow-sm transition-all hover:bg-blue-700 hover:shadow-lg active:scale-[0.98]"
                >
                    Add Payment Method
                </button>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

        @forelse($methods as $method)

            <form
                method="POST"
                action="{{ route('admin.payment-gateways.offline.update', $method->id) }}"
                x-data="{ enabled: {{ $method->is_active ? 'true' : 'false' }} }"
                class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition-all duration-300 ease-out hover:-translate-y-1 hover:shadow-xl"
            >
                @csrf

                <input
                    type="hidden"
                    name="is_active"
                    :value="enabled ? '1' : '0'"
                >

                <div class="border-b border-slate-100 px-6 py-6">

                    <div class="flex items-start justify-between gap-4">

                        <div>
                            <h2 class="text-xl font-black text-slate-900">
                                {{ $method->name }}
                            </h2>

                            <p class="mt-1 text-xs font-bold uppercase tracking-wider text-slate-400">
                                {{ str_replace('_', ' ', $method->type) }}
                            </p>
                        </div>

                        <div class="flex items-center gap-3">

                            <span
                                class="rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-wider"
                                :class="enabled
                                    ? 'bg-blue-50 text-blue-700'
                                    : 'bg-slate-100 text-slate-500'"
                                x-text="enabled ? 'Enabled' : 'Disabled'"
                            ></span>

                            <button
                                type="button"
                                @click="enabled = !enabled"
                                :aria-pressed="enabled.toString()"
                                class="relative flex h-7 w-12 shrink-0 items-center rounded-full border-2 p-0.5 transition-all duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                :class="enabled
                                    ? 'bg-blue-600 border-blue-600'
                                    : 'bg-gray-500 border-gray-600'"
                                aria-label="Toggle payment method"
                            >
                                <span
                                    class="block h-6 w-6 rounded-full bg-white shadow-md transition-transform duration-200 ease-in-out"
                                    :style="enabled
                                        ? 'transform: translateX(20px)'
                                        : 'transform: translateX(0px)'"
                                ></span>
                            </button>

                        </div>

                    </div>

                </div>

                <div class="grid gap-6 px-6 py-6 md:grid-cols-2">

                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">
                            Method Name
                        </label>

                        <input
                            type="text"
                            name="name"
                            value="{{ $method->name }}"
                            required
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900 outline-none focus:border-blue-500 focus:bg-white"
                        >
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">
                            Type
                        </label>

                        <select
                            name="type"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-900 outline-none focus:border-blue-500 focus:bg-white"
                        >
                            @foreach([
                                'bank_transfer' => 'Bank Transfer',
                                'cash' => 'Cash',
                                'manual' => 'Manual Payment',
                                'other' => 'Other',
                            ] as $value => $label)
                                <option
                                    value="{{ $value }}"
                                    {{ $method->type === $value ? 'selected' : '' }}
                                >
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">
                            Priority
                        </label>

                        <input
                            type="number"
                            name="priority"
                            min="1"
                            value="{{ $method->priority }}"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900 outline-none focus:border-blue-500 focus:bg-white"
                        >
                    </div>

                    <div class="flex items-center">
                        <label class="inline-flex cursor-pointer items-center gap-3">
                            <input
                                type="hidden"
                                name="is_default"
                                value="0"
                            >

                            <input
                                type="checkbox"
                                name="is_default"
                                value="1"
                                {{ $method->is_default ? 'checked' : '' }}
                                class="h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                            >

                            <span class="text-sm font-bold text-slate-700">
                                Default method
                            </span>
                        </label>
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">
                            Payment Instructions
                        </label>

                        <textarea
                            name="instructions"
                            rows="5"
                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white"
                            placeholder="Enter customer payment instructions..."
                        >{{ $method->instructions }}</textarea>
                    </div>

                    <div class="md:col-span-2 rounded-2xl border border-slate-200 bg-slate-50 p-5">
                        <div class="mb-5">
                            <h3 class="text-sm font-black text-slate-900">
                                Receipt Upload
                            </h3>

                            <p class="mt-1 text-xs leading-5 text-slate-500">
                                Allow customers to upload proof of payment after completing this offline payment.
                            </p>
                        </div>

                        <label class="mb-5 inline-flex cursor-pointer items-center gap-3">
                            <input
                                type="hidden"
                                name="receipt_upload_enabled"
                                value="0"
                            >

                            <input
                                type="checkbox"
                                name="receipt_upload_enabled"
                                value="1"
                                {{ $method->receipt_upload_enabled ? 'checked' : '' }}
                                class="h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                            >

                            <span class="text-sm font-bold text-slate-700">
                                Require receipt upload
                            </span>
                        </label>

                        <div class="grid gap-5 md:grid-cols-2">

                            <div>
                                <label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">
                                    Upload Label
                                </label>

                                <input
                                    type="text"
                                    name="receipt_upload_label"
                                    value="{{ $method->receipt_upload_label ?? 'Upload payment receipt' }}"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-900 outline-none focus:border-blue-500"
                                    placeholder="Upload payment receipt"
                                >
                            </div>

                            <div>
                                <label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">
                                    Customer Help Text
                                </label>

                                <input
                                    type="text"
                                    name="receipt_upload_help"
                                    value="{{ $method->receipt_upload_help ?? 'Upload your payment receipt or proof of payment.' }}"
                                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-medium text-slate-900 outline-none focus:border-blue-500"
                                    placeholder="Upload your payment receipt or proof of payment."
                                >
                            </div>

                        </div>
                    </div>

                </div>


                @php
                    $methodUsage = json_decode(
                        $method->usage_contexts ?? '[]',
                        true
                    );

                    if ($method->usage_contexts === null) {
                        $methodUsage = [
                            'user_marketplace_checkout',
                            'user_wallet_funding',
                            'user_checkout_link',

                            'developer_marketplace_checkout',
                            'developer_wallet_funding',
                            'developer_checkout_link',
                        ];
                    }

                    $methodUsage = is_array($methodUsage)
                        ? $methodUsage
                        : [];
                @endphp

                <div class="space-y-5 px-6 pb-6">

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">

                        <h3 class="text-sm font-black text-slate-900">
                            Allowed Gateway Uses
                        </h3>

                        <div class="mt-4 grid gap-3 sm:grid-cols-2">

                            <div class="sm:col-span-2">
                                <div class="mb-2 text-xs font-black uppercase tracking-wider text-blue-600">
                                    User Mode
                                </div>
                            </div>

                            @foreach([
                                'user_marketplace_checkout'
                                    => 'Marketplace Checkout',

                                'user_wallet_funding'
                                    => 'Wallet Funding',

                                'user_checkout_link'
                                    => 'Checkout Link',
                            ] as $context => $label)

                                <label class="flex items-center gap-3 text-sm font-bold text-slate-700">
                                    <input
                                        type="checkbox"
                                        name="usage_contexts[]"
                                        value="{{ $context }}"
                                        @checked(in_array(
                                            $context,
                                            $methodUsage,
                                            true
                                        ))
                                    >

                                    {{ $label }}
                                </label>

                            @endforeach


                            <div class="mt-3 sm:col-span-2">
                                <div class="mb-2 text-xs font-black uppercase tracking-wider text-violet-600">
                                    Developer Mode
                                </div>
                            </div>

                            @foreach([
                                'developer_marketplace_checkout'
                                    => 'Marketplace Checkout',

                                'developer_wallet_funding'
                                    => 'Wallet Funding',

                                'developer_checkout_link'
                                    => 'Checkout Link',
                            ] as $context => $label)

                                <label class="flex items-center gap-3 text-sm font-bold text-slate-700">
                                    <input
                                        type="checkbox"
                                        name="usage_contexts[]"
                                        value="{{ $context }}"
                                        @checked(in_array(
                                            $context,
                                            $methodUsage,
                                            true
                                        ))
                                    >

                                    {{ $label }}
                                </label>

                            @endforeach

                        </div>
                    </div>


                    <div
                        x-data="{
                            enabled: @js(
                                (bool) (
                                    $method->conversion_enabled
                                        ?? false
                                )
                            ),
                            type: @js(
                                $method->conversion_type
                                    ?? 'none'
                            ),
                            provider: @js(
                                $method->conversion_provider
                                    ?? ''
                            )
                        }"
                        class="rounded-2xl border border-slate-200 bg-slate-50 p-5"
                    >

                        <div class="flex items-center justify-between gap-4">

                            <h3 class="text-sm font-black text-slate-900">
                                Currency / Crypto Conversion
                            </h3>

                            <label class="flex items-center gap-2 text-sm font-bold text-slate-700">
                                <input
                                    type="checkbox"
                                    name="conversion_enabled"
                                    value="1"
                                    x-model="enabled"
                                    @checked(
                                        $method->conversion_enabled
                                            ?? false
                                    )
                                >

                                Enable
                            </label>

                        </div>

                        <div
                            x-show="enabled"
                            x-cloak
                            class="mt-5 grid gap-4 sm:grid-cols-2"
                        >

                            <select
                                name="conversion_type"
                                x-model="type"
                                class="rounded-xl border border-slate-300 bg-white px-4 py-3"
                            >
                                <option
                                    value="none"
                                    @selected(($method->conversion_type ?? 'none') === 'none')
                                >
                                    None
                                </option>

                                <option
                                    value="fiat"
                                    @selected(($method->conversion_type ?? 'none') === 'fiat')
                                >
                                    Fiat Currency
                                </option>

                                <option
                                    value="crypto"
                                    @selected(($method->conversion_type ?? 'none') === 'crypto')
                                >
                                    Cryptocurrency
                                </option>
                            </select>

                            <select
                                name="conversion_provider"
                                x-model="provider"
                                class="rounded-xl border border-slate-300 bg-white px-4 py-3"
                            >
                                <option
                                    value=""
                                    @selected(empty($method->conversion_provider))
                                >
                                    Select rate source
                                </option>

                                <option
                                    value="frankfurter"
                                    x-show="type === 'fiat'"
                                    @selected(
                                        ($method->conversion_provider ?? '')
                                            === 'frankfurter'
                                    )
                                >
                                    Frankfurter
                                </option>

                                <option
                                    value="coingecko"
                                    x-show="type === 'crypto'"
                                    @selected(
                                        ($method->conversion_provider ?? '')
                                            === 'coingecko'
                                    )
                                >
                                    CoinGecko
                                </option>

                                <option
                                    value="manual"
                                    @selected(
                                        ($method->conversion_provider ?? '')
                                            === 'manual'
                                    )
                                >
                                    Manual Rate
                                </option>
                            </select>

                            <input
                                type="text"
                                name="conversion_target"
                                value="{{ $method->conversion_target }}"
                                placeholder="USD, GBP, USDT, BTC"
                                class="rounded-xl border border-slate-300 bg-white px-4 py-3 uppercase"
                            >

                            <input
                                x-show="provider === 'manual'"
                                x-cloak
                                type="number"
                                step="0.000000000001"
                                min="0.000000000001"
                                name="manual_conversion_rate"
                                value="{{ $method->manual_conversion_rate }}"
                                class="rounded-xl border border-slate-300 bg-white px-4 py-3"
                            >

                        </div>

                    </div>


                    <div
                        x-data="{
                            enabled: @js(
                                (bool) (
                                    $method->markup_enabled
                                        ?? false
                                )
                            ),
                            type: @js(
                                $method->markup_type
                                    ?? 'percentage'
                            )
                        }"
                        class="rounded-2xl border border-slate-200 bg-slate-50 p-5"
                    >

                        <div class="flex items-center justify-between gap-4">

                            <h3 class="text-sm font-black text-slate-900">
                                Payment Markup
                            </h3>

                            <label class="flex items-center gap-2 text-sm font-bold text-slate-700">
                                <input
                                    type="checkbox"
                                    name="markup_enabled"
                                    value="1"
                                    x-model="enabled"
                                    @checked(
                                        $method->markup_enabled
                                            ?? false
                                    )
                                >

                                Enable
                            </label>

                        </div>

                        <div
                            x-show="enabled"
                            x-cloak
                            class="mt-5 grid gap-4 sm:grid-cols-2"
                        >

                            <select
                                name="markup_type"
                                x-model="type"
                                class="rounded-xl border border-slate-300 bg-white px-4 py-3"
                            >
                                <option
                                    value="percentage"
                                    @selected(
                                        ($method->markup_type ?? 'percentage')
                                            === 'percentage'
                                    )
                                >
                                    Percentage
                                </option>

                                <option
                                    value="fixed"
                                    @selected(
                                        ($method->markup_type ?? 'percentage')
                                            === 'fixed'
                                    )
                                >
                                    Fixed
                                </option>
                            </select>

                            <input
                                type="number"
                                step="0.00000001"
                                min="0"
                                name="markup_value"
                                value="{{ $method->markup_value ?? 0 }}"
                                class="rounded-xl border border-slate-300 bg-white px-4 py-3"
                            >

                        </div>

                    </div>

                </div>

                <div class="flex items-center justify-between border-t border-slate-100 bg-slate-50 px-6 py-4">

                    <button
                        type="button"
                        onclick="if(confirm('Remove this offline payment method?')) document.getElementById('delete-offline-{{ $method->id }}').submit()"
                        class="rounded-xl px-4 py-3 text-sm font-black text-red-600 transition hover:bg-red-50"
                    >
                        Remove
                    </button>

                    <button
                        type="submit"
                        class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-black text-white shadow-sm transition-all hover:bg-blue-700 hover:shadow-lg active:scale-[0.98]"
                    >
                        Save Method
                    </button>

                </div>

            </form>

            <form
                id="delete-offline-{{ $method->id }}"
                method="POST"
                action="{{ route('admin.payment-gateways.offline.destroy', $method->id) }}"
                class="hidden"
            >
                @csrf
                @method('DELETE')
            </form>

        @empty

            <div class="rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center lg:col-span-2">
                <div class="text-lg font-black text-slate-900">
                    No offline payment methods configured
                </div>

                <p class="mt-2 text-sm text-slate-500">
                    Add your first offline payment method to make it available for configuration.
                </p>
            </div>

        @endforelse

    </div>

    {{-- =====================================================
         Offline Payment Management
    ====================================================== --}}
    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">

        <div class="flex flex-col gap-3 border-b border-slate-100 px-6 py-6 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-xl font-black text-slate-900">
                    Offline Payment Management
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Review customer payment proofs and approve or reject offline marketplace payments.
                </p>
            </div>

            <div class="rounded-full bg-slate-100 px-4 py-2 text-xs font-black text-slate-600">
                {{ $offlinePayments->count() }} Payments
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">

                <thead class="bg-slate-50">
                    <tr class="text-left text-[10px] font-black uppercase tracking-wider text-slate-500">
                        <th class="px-5 py-4">Customer</th>
                        <th class="px-5 py-4">Order / Product</th>
                        <th class="px-5 py-4">Website</th>
                        <th class="px-5 py-4">Method</th>
                        <th class="px-5 py-4">Amount</th>
                        <th class="px-5 py-4">Reference</th>
                        <th class="px-5 py-4">Receipt</th>
                        <th class="px-5 py-4">Status</th>
                        <th class="px-5 py-4">Date</th>
                        <th class="px-5 py-4 text-right">Action</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">

                    @forelse($offlinePayments as $payment)
                        <tr class="align-top hover:bg-slate-50">

                            {{-- Customer --}}
                            <td class="px-5 py-4">
                                <div class="font-black text-slate-900">
                                    {{ $payment->user->name ?? 'Unknown User' }}
                                </div>

                                @if(!empty($payment->user->email))
                                    <div class="mt-1 text-xs text-slate-500">
                                        {{ $payment->user->email }}
                                    </div>
                                @endif

                                <div class="mt-1 text-[10px] font-black uppercase tracking-wide text-slate-400">
                                    {{ $payment->deployment_type === 'off_server'
                                        ? 'Developer / Off-server'
                                        : 'SaaS' }}
                                </div>
                            </td>

                            {{-- Order / Product --}}
                            <td class="px-5 py-4">
                                @if(!empty($payment->is_wallet_funding))
                                    <div class="font-black text-slate-900">
                                        Wallet Funding
                                    </div>

                                    <div class="mt-1 text-xs text-slate-500">
                                        {{ $payment->funding->reference
                                            ?? 'Wallet Funding' }}
                                    </div>
                                @else
                                    <div class="font-black text-slate-900">
                                        {{ $payment->order->product_title ?? 'Marketplace Product' }}
                                    </div>

                                    <div class="mt-1 text-xs text-slate-500">
                                        {{ $payment->order->reference
                                            ?? ('Order #' . ($payment->order_id ?? '—')) }}
                                    </div>
                                @endif
                            </td>

                            {{-- Website --}}
                            <td class="px-5 py-4">
                                @if($payment->deployment_type === 'saas' && $payment->website)
                                    <div class="font-bold text-slate-900">
                                        {{ $payment->website->name }}
                                    </div>

                                    <div class="mt-1 text-xs text-slate-500">
                                        {{ $payment->website->domain
                                            ?? $payment->website->subdomain
                                            ?? '' }}
                                    </div>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>

                            {{-- Method --}}
                            <td class="px-5 py-4">
                                <div class="font-bold text-slate-700">
                                    {{ $payment->payment_method_name ?? 'Offline Payment' }}
                                </div>
                            </td>

                            {{-- Amount --}}
                            <td class="whitespace-nowrap px-5 py-4 font-black text-slate-900">
                                {{ strtoupper($payment->currency ?? 'NGN') }}
                                {{ number_format((float) $payment->amount, 2) }}
                            </td>

                            {{-- Reference --}}
                            <td class="px-5 py-4">
                                <code class="break-all rounded-lg bg-slate-100 px-2 py-1 text-[11px] font-bold text-slate-600">
                                    {{ $payment->transaction_reference }}
                                </code>
                            </td>

                            {{-- Receipt --}}
                            <td class="px-5 py-4">
                                @if(!empty($payment->metadata['receipt_path']))
                                    <div
                                        x-data="{
                                            open: false,
                                            receiptUrl: @js(
                                                route(
                                                    'admin.payment-gateways.offline-payments.receipt',
                                                    ['attempt' => $payment->id]
                                                )
                                            )
                                        }"
                                    >
                                        <button
                                            type="button"
                                            @click="open = true"
                                            class="inline-flex rounded-xl border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-black text-blue-700 hover:bg-blue-100"
                                        >
                                            View Receipt
                                        </button>

                                        <template x-teleport="body">
                                            <div
                                                x-show="open"
                                                x-cloak
                                                @keydown.escape.window="open = false"
                                                class="fixed inset-0 z-[9999] flex items-center justify-center p-4 sm:p-6"
                                            >
                                                <div
                                                    class="absolute inset-0 bg-slate-950/70 backdrop-blur-sm"
                                                    @click="open = false"
                                                ></div>

                                                <div
                                                    x-show="open"
                                                    x-transition
                                                    class="relative z-10 flex max-h-[85vh] w-full max-w-lg flex-col overflow-hidden rounded-2xl bg-white shadow-2xl"
                                                >
                                                    <div class="flex items-center justify-between border-b border-slate-200 px-5 py-4 sm:px-6">
                                                        <div>
                                                            <h3 class="text-lg font-black text-slate-900">
                                                                Payment Receipt
                                                            </h3>

                                                            <p class="mt-1 text-xs text-slate-500">
                                                                {{ $payment->metadata['receipt_original_name'] ?? 'Customer payment proof' }}
                                                            </p>
                                                        </div>

                                                        <button
                                                            type="button"
                                                            @click="open = false"
                                                            class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 text-xl font-bold text-slate-500 hover:bg-slate-100 hover:text-slate-900"
                                                        >
                                                            &times;
                                                        </button>
                                                    </div>

                                                    <div class="flex max-h-[65vh] min-h-[280px] items-center justify-center overflow-auto bg-slate-100 p-4">
                                                        <img
                                                            :src="open ? receiptUrl : ''"
                                                            alt="Payment receipt"
                                                            class="max-h-[58vh] max-w-full rounded-xl border border-slate-200 bg-white object-contain shadow-sm"
                                                        >
                                                    </div>

                                                    <div class="flex justify-end border-t border-slate-200 bg-white px-5 py-4 sm:px-6">
                                                        <button
                                                            type="button"
                                                            @click="open = false"
                                                            class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-black text-white hover:bg-slate-800"
                                                        >
                                                            Close
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>

                                    @if(!empty($payment->metadata['receipt_original_name']))
                                        <div class="mt-1 max-w-[160px] truncate text-[10px] text-slate-400">
                                            {{ $payment->metadata['receipt_original_name'] }}
                                        </div>
                                    @endif
                                @else
                                    <span class="text-xs font-semibold text-slate-400">
                                        Not submitted
                                    </span>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td class="px-5 py-4">
                                @php
                                    $offlineStatus = strtolower($payment->status);
                                @endphp

                                <span class="inline-flex rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-wide
                                    {{ $offlineStatus === 'successful'
                                        ? 'bg-emerald-50 text-emerald-700'
                                        : ($offlineStatus === 'failed'
                                            ? 'bg-red-50 text-red-700'
                                            : ($offlineStatus === 'processing'
                                                ? 'bg-blue-50 text-blue-700'
                                                : 'bg-amber-50 text-amber-700')) }}">
                                    {{ $offlineStatus === 'initiated'
                                        ? 'Awaiting Proof'
                                        : ($offlineStatus === 'processing'
                                            ? 'Awaiting Review'
                                            : ucfirst($offlineStatus)) }}
                                </span>
                            </td>

                            {{-- Date --}}
                            <td class="whitespace-nowrap px-5 py-4 text-xs text-slate-500">
                                {{ \Carbon\Carbon::parse($payment->created_at)->format('d M Y') }}
                                <div class="mt-1">
                                    {{ \Carbon\Carbon::parse($payment->created_at)->format('h:i A') }}
                                </div>
                            </td>

                            {{-- Actions --}}
                            <td class="px-5 py-4 text-right">
                                @if(in_array(strtolower((string) $payment->status), [
                                    'initiated',
                                    'processing',
                                    'pending',
                                    'pending_review',
                                    'awaiting_review'
                                ], true))
                                    <div class="flex min-w-[190px] justify-end gap-2">

                                        <form
                                            method="POST"
                                            action="{{ route('admin.payment-gateways.offline-payments.mark-paid', ['attempt' => $payment->id]) }}"
                                            onsubmit="return confirm('Confirm that this offline payment has been received?');"
                                        >
                                            @csrf

                                            <button
                                                type="submit"
                                                class="rounded-xl bg-emerald-600 px-4 py-2 text-xs font-black text-white hover:bg-emerald-700"
                                            >
                                                Approve
                                            </button>
                                        </form>

                                        <form
                                            method="POST"
                                            action="{{ route('admin.payment-gateways.offline-payments.reject', ['attempt' => $payment->id]) }}"
                                            onsubmit="return confirm('Reject this offline payment?');"
                                        >
                                            @csrf

                                            <button
                                                type="submit"
                                                class="rounded-xl border border-red-200 bg-white px-4 py-2 text-xs font-black text-red-600 hover:bg-red-50"
                                            >
                                                Reject
                                            </button>
                                        </form>

                                    </div>
                                @else
                                    <span class="text-xs font-semibold text-slate-400">
                                        Reviewed
                                    </span>
                                @endif
                            </td>

                        </tr>

                    @empty
                        <tr>
                            <td
                                colspan="10"
                                class="px-6 py-14 text-center text-sm text-slate-500"
                            >
                                No offline marketplace payments have been submitted yet.
                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>

        @if($offlinePayments->hasPages())
            <div class="border-t border-slate-100 px-6 py-5">
                {{ $offlinePayments->links() }}
            </div>
        @endif

    </div>

</div>

@endsection
