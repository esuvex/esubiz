@extends('admin.layouts.app')


@section('content')

<div class="mb-6">
    <a href="{{ route('admin.payment-gateways.index') }}"
       class="inline-flex items-center gap-2 text-sm font-black text-blue-600 hover:text-blue-700">
        <span>←</span>
        Back to Gateways
    </a>
</div>


<div class="min-h-screen bg-slate-50 px-6 py-8">
    <div class="mx-auto max-w-6xl">

        @if(session('success'))
            <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        <div class="mb-8">
            <div class="text-xs font-black uppercase tracking-[0.2em] text-blue-600">
                Site Settings
            </div>

            <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900">
                Online Payment Gateways
            </h1>

            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-500">
                Configure the payment providers used by the central Esubiz
                checkout system. Product modules do not connect to gateways
                directly.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 space-y-6">

            @forelse($gateways as $gateway)

                @php
                    $credentials = $gateway->credentials
                        ? json_decode($gateway->credentials, true)
                        : [];

                    $settings = $gateway->settings
                        ? json_decode($gateway->settings, true)
                        : [];

                    $environment = $settings['environment']
                        ?? 'sandbox';
                @endphp

                <form
                    method="POST"
                    action="{{ route('admin.payment-gateways.online.update', $gateway->id) }}"
                    class="group overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm
                       transition-all duration-300 ease-out
                       hover:-translate-y-1 hover:shadow-xl
                       motion-safe:animate-[fadeInUp_.45s_ease-out_both]"
                >
                    @csrf

                    <div class="flex flex-col gap-5 border-b border-slate-100 px-6 py-6 md:flex-row md:items-center md:justify-between">

                        <div>
                            <div class="flex items-center gap-3">
                                <h2 class="text-xl font-black text-slate-900">
                                    {{ $gateway->name }}
                                </h2>

                                @if($gateway->is_active)
                                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-[10px] font-black uppercase tracking-wider text-emerald-700">
                                        Enabled
                                    </span>
                                @else
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-[10px] font-black uppercase tracking-wider text-slate-500">
                                        Disabled
                                    </span>
                                @endif
                            </div>

                            <p class="mt-1 text-sm text-slate-500">
                                {{ $gateway->slug }}
                            </p>
                        </div>

                        <div
                            x-data="{ enabled: {{ $gateway->is_active ? 'true' : 'false' }} }"
                            class="inline-flex items-center gap-3"
                        >
                            <input
                                type="hidden"
                                name="is_active"
                                :value="enabled ? '1' : '0'"
                            >

                            <button
                                type="button"
                                @click="enabled = !enabled"
                                :aria-pressed="enabled.toString()"
                                class="relative flex h-7 w-12 shrink-0 items-center rounded-full border-2 p-0.5 transition-all duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                :class="enabled ? 'bg-blue-600 border-blue-600' : 'bg-gray-500 border-gray-600'"
                                aria-label="Toggle gateway"
                            >
                                <span
                                    class="block h-6 w-6 rounded-full bg-white shadow-md transition-transform duration-200 ease-in-out"
                                    :style="enabled ? 'transform: translateX(20px)' : 'transform: translateX(0px)'"
                                ></span>
                            </button>

                            <span
                                class="text-sm font-bold transition-colors duration-200"
                                :class="enabled ? 'text-blue-700' : 'text-slate-600'"
                                x-text="enabled ? 'Gateway enabled' : 'Gateway disabled'"
                            ></span>
                        </div>

                    </div>

                    @php
                        $credentialFields = match ($gateway->slug) {
                            'paystack' => [
                                ['key' => 'public_key', 'label' => 'Public Key', 'type' => 'text'],
                                ['key' => 'secret_key', 'label' => 'Secret Key', 'type' => 'password'],
                            ],

                            'flutterwave' => [
                                ['key' => 'public_key', 'label' => 'Public Key', 'type' => 'text'],
                                ['key' => 'secret_key', 'label' => 'Secret Key', 'type' => 'password'],
                            ],

                            'paypal' => [
                                ['key' => 'client_id', 'label' => 'Client ID', 'type' => 'text'],
                                ['key' => 'client_secret', 'label' => 'Client Secret', 'type' => 'password'],
                            ],

                            'nowpayments' => [
                                ['key' => 'api_key', 'label' => 'API Key', 'type' => 'password'],
                            ],

                            default => [
                                ['key' => 'public_key', 'label' => 'Public / API Key', 'type' => 'text'],
                                ['key' => 'secret_key', 'label' => 'Secret / Private Key', 'type' => 'password'],
                            ],
                        };
                    @endphp

                    <div class="grid gap-6 px-6 py-6 md:grid-cols-2">

                        @foreach($credentialFields as $field)
                            <div>
                                <label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">
                                    {{ $field['label'] }}
                                </label>

                                <input
                                    type="{{ $field['type'] }}"
                                    name="credentials[{{ $field['key'] }}]"
                                    value="{{ $field['type'] === 'password' && !empty($credentials[$field['key']]) ? '••••••••' : ($credentials[$field['key']] ?? '') }}"
                                    autocomplete="new-password"
                                    class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white"
                                    placeholder="Enter {{ strtolower($field['label']) }}"
                                >

                                @if($field['type'] === 'password')
                                    <p class="mt-2 text-xs text-slate-400">
                                        Leave blank to keep the existing credential.
                                    </p>
                                @endif
                            </div>
                        @endforeach

                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">
                                Environment
                            </label>

                            <select
                                name="settings[environment]"
                                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-900 outline-none focus:border-blue-500 focus:bg-white"
                            >
                                <option value="sandbox" {{ $environment === 'sandbox' ? 'selected' : '' }}>
                                    Sandbox / Test
                                </option>

                                <option value="production" {{ $environment === 'production' ? 'selected' : '' }}>
                                    Production / Live
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">
                                Supported Currencies
                            </label>

                            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-600">
                                {{ $gateway->supported_currencies ?: 'Configured by gateway' }}
                            </div>
                        </div>

                    </div>


                    @php
                        $gatewayUsage = json_decode(
                            $gateway->usage_contexts ?? '[]',
                            true
                        );

                        /*
                         * NULL means legacy gateway.
                         * Default to all contexts checked until Admin
                         * saves an explicit selection.
                         */
                        if ($gateway->usage_contexts === null) {
                            $gatewayUsage = [
                                'user_marketplace_checkout',
                                'user_wallet_funding',
                                'user_checkout_link',

                                'developer_marketplace_checkout',
                                'developer_wallet_funding',
                                'developer_checkout_link',
                            ];
                        }

                        $gatewayUsage = is_array($gatewayUsage)
                            ? $gatewayUsage
                            : [];
                    @endphp

                    <div class="space-y-5 px-6 pb-6">

                        {{-- Allowed Uses --}}
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">

                            <h3 class="text-sm font-black text-slate-900">
                                Allowed Gateway Uses
                            </h3>

                            <p class="mt-1 text-xs text-slate-500">
                                Choose where this gateway can be used.
                            </p>

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
                                                $gatewayUsage,
                                                true
                                            ))
                                            class="h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
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
                                                $gatewayUsage,
                                                true
                                            ))
                                            class="h-5 w-5 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                        >

                                        {{ $label }}
                                    </label>

                                @endforeach

                            </div>
                        </div>


                        {{-- Conversion --}}
                        <div
                            x-data="{
                                enabled: @js(
                                    (bool) (
                                        $gateway->conversion_enabled
                                            ?? false
                                    )
                                ),
                                type: @js(
                                    $gateway->conversion_type
                                        ?? 'none'
                                ),
                                provider: @js(
                                    $gateway->conversion_provider
                                        ?? ''
                                )
                            }"
                            class="rounded-2xl border border-slate-200 bg-slate-50 p-5"
                        >

                            <div class="flex items-center justify-between gap-4">

                                <div>
                                    <h3 class="text-sm font-black text-slate-900">
                                        Currency / Crypto Conversion
                                    </h3>

                                    <p class="mt-1 text-xs text-slate-500">
                                        Configure automatic payment conversion.
                                    </p>
                                </div>

                                <label class="flex items-center gap-2 text-sm font-bold text-slate-700">
                                    <input
                                        type="checkbox"
                                        name="conversion_enabled"
                                        value="1"
                                        x-model="enabled"
                                        @checked(
                                            $gateway->conversion_enabled
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

                                <div>
                                    <label class="text-xs font-black uppercase tracking-wide text-slate-500">
                                        Conversion Type
                                    </label>

                                    <select
                                        name="conversion_type"
                                        x-model="type"
                                        class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3"
                                    >
                                        <option value="none">
                                            None
                                        </option>

                                        <option value="fiat">
                                            Fiat Currency
                                        </option>

                                        <option value="crypto">
                                            Cryptocurrency
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label class="text-xs font-black uppercase tracking-wide text-slate-500">
                                        Rate Source
                                    </label>

                                    <select
                                        name="conversion_provider"
                                        x-model="provider"
                                        class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3"
                                    >
                                        <option value="">
                                            Select source
                                        </option>

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
                                </div>

                                <div>
                                    <label class="text-xs font-black uppercase tracking-wide text-slate-500">
                                        Target Currency / Asset
                                    </label>

                                    <input
                                        type="text"
                                        name="conversion_target"
                                        value="{{ $gateway->conversion_target }}"
                                        placeholder="USD, GBP, USDT, BTC"
                                        class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 uppercase"
                                    >
                                </div>

                                <div
                                    x-show="provider === 'manual'"
                                    x-cloak
                                >
                                    <label class="text-xs font-black uppercase tracking-wide text-slate-500">
                                        Manual Conversion Rate
                                    </label>

                                    <input
                                        type="number"
                                        step="0.000000000001"
                                        min="0.000000000001"
                                        name="manual_conversion_rate"
                                        value="{{ $gateway->manual_conversion_rate }}"
                                        class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3"
                                    >
                                </div>

                            </div>

                        </div>


                        {{-- Markup --}}
                        <div
                            x-data="{
                                enabled: @js(
                                    (bool) (
                                        $gateway->markup_enabled
                                            ?? false
                                    )
                                ),
                                type: @js(
                                    $gateway->markup_type
                                        ?? 'percentage'
                                )
                            }"
                            class="rounded-2xl border border-slate-200 bg-slate-50 p-5"
                        >

                            <div class="flex items-center justify-between gap-4">

                                <div>
                                    <h3 class="text-sm font-black text-slate-900">
                                        Payment Markup
                                    </h3>

                                    <p class="mt-1 text-xs text-slate-500">
                                        Add a percentage or fixed amount after conversion.
                                    </p>
                                </div>

                                <label class="flex items-center gap-2 text-sm font-bold text-slate-700">
                                    <input
                                        type="checkbox"
                                        name="markup_enabled"
                                        value="1"
                                        x-model="enabled"
                                        @checked(
                                            $gateway->markup_enabled
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

                                <div>
                                    <label class="text-xs font-black uppercase tracking-wide text-slate-500">
                                        Markup Type
                                    </label>

                                    <select
                                        name="markup_type"
                                        x-model="type"
                                        class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3"
                                    >
                                        <option value="percentage">
                                            Percentage
                                        </option>

                                        <option value="fixed">
                                            Fixed
                                        </option>
                                    </select>
                                </div>

                                <div>
                                    <label class="text-xs font-black uppercase tracking-wide text-slate-500">
                                        Markup Value
                                    </label>

                                    <input
                                        type="number"
                                        step="0.00000001"
                                        min="0"
                                        name="markup_value"
                                        value="{{ $gateway->markup_value ?? 0 }}"
                                        class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3"
                                    >
                                </div>

                            </div>

                        </div>

                    </div>

                    <div class="flex justify-end border-t border-slate-100 bg-slate-50 px-6 py-4">
                        <button
                            type="submit"
                            class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-black text-white
                               shadow-sm transition-all duration-200
                               hover:bg-blue-700 hover:shadow-lg
                               active:scale-[0.98]"
                        >
                            Save Gateway Settings
                        </button>
                    </div>

                </form>

            @empty

                <div class="rounded-3xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center">
                    <div class="text-lg font-black text-slate-900">
                        No online payment gateways configured
                    </div>

                    <p class="mt-2 text-sm text-slate-500">
                        Add a payment provider to make it available to the
                        central Esubiz checkout system.
                    </p>
                </div>

            @endforelse

        </div>

    </div>
</div>

<style>
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(12px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

@endsection
