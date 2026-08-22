<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Gift Card Validator - Esubiz</title>

    @vite(['resources/css/app.css','resources/js/app.js'])

</head>

<body class="bg-white text-slate-900 antialiased">

    {{-- Main Esubiz Public Navigation --}}
    @include('frontend.partials.navbar')

    {{-- Gift Card Validator --}}
    <main class="min-h-[70vh] bg-slate-50 px-6 py-16 lg:py-20">

        <div class="mx-auto max-w-3xl">

            <div class="text-center">

                <p class="text-xs font-black uppercase tracking-[0.2em] text-blue-600">
                    Esubiz Gift Cards
                </p>

                <h1 class="mt-3 text-3xl font-black tracking-tight text-slate-900 sm:text-4xl">
                    Check Gift Card
                </h1>

                <p class="mx-auto mt-4 max-w-2xl text-sm leading-7 text-slate-500 sm:text-base">
                    Enter your Esubiz gift card code to check its validity,
                    remaining balance and available usage.
                </p>

            </div>

            <div class="mt-10 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">

                <form
                    method="POST"
                    action="{{ route('gift-card.validate.check') }}"
                    id="gift-card-validator-form"
                >

                    @csrf

                    <label
                        for="gift-card-code"
                        class="text-sm font-black text-slate-800"
                    >
                        Gift Card Code
                    </label>

                    <div class="mt-3 flex flex-col gap-3 sm:flex-row">

                        <input
                            id="gift-card-code"
                            type="text"
                            name="code"
                            value="{{ old('code', $code ?? '') }}"
                            placeholder="ENTER GIFT CARD CODE"
                            required
                            autocomplete="off"
                            spellcheck="false"
                            class="min-w-0 flex-1 rounded-xl border border-slate-200 px-4 py-3 text-sm font-bold uppercase text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                        >

                        <button
                            type="submit"
                            class="rounded-xl bg-blue-600 px-7 py-3 text-sm font-black text-white transition hover:bg-blue-700"
                        >
                            Check Card
                        </button>

                    </div>

                    @error('code')
                        <p class="mt-3 text-sm font-semibold text-red-600">
                            {{ $message }}
                        </p>
                    @enderror

                </form>

                @isset($result)

                    <div class="mt-6 rounded-2xl border p-5
                        {{ $result['valid']
                            ? 'border-emerald-200 bg-emerald-50'
                            : 'border-red-200 bg-red-50' }}">

                        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">

                            <div>

                                <p class="text-xs font-black uppercase tracking-wider
                                    {{ $result['valid']
                                        ? 'text-emerald-700'
                                        : 'text-red-700' }}">
                                    {{ $result['valid'] ? 'Valid Gift Card' : 'Gift Card Status' }}
                                </p>

                                <p class="mt-2 text-sm font-bold
                                    {{ $result['valid']
                                        ? 'text-emerald-800'
                                        : 'text-red-800' }}">
                                    {{ $result['message'] }}
                                </p>

                            </div>

                            <span class="w-fit rounded-full px-3 py-1 text-xs font-black uppercase
                                {{ $result['valid']
                                    ? 'bg-emerald-100 text-emerald-700'
                                    : 'bg-red-100 text-red-700' }}">
                                {{ str_replace('_', ' ', $result['status']) }}
                            </span>

                        </div>

                        @if($result['valid'])

                            <div class="mt-6 grid gap-5 border-t border-emerald-200 pt-5 sm:grid-cols-2">

                                <div>
                                    <p class="text-xs font-bold text-slate-500">
                                        Remaining Balance
                                    </p>

                                    <p class="mt-1 text-xl font-black text-slate-900">
                                        {{ $result['currency'] }}
                                        {{ number_format($result['balance'], 2) }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs font-bold text-slate-500">
                                        Usage
                                    </p>

                                    <p class="mt-1 text-lg font-black text-slate-900">
                                        {{ $result['usage_count'] }}
                                        /
                                        {{ $result['usage_limit'] ?? 'Unlimited' }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs font-bold text-slate-500">
                                        Checkout
                                    </p>

                                    <p class="mt-1 text-sm font-black text-slate-900">
                                        {{ $result['usable_at_checkout'] ? 'Available' : 'Not available' }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-xs font-bold text-slate-500">
                                        Wallet Funding
                                    </p>

                                    <p class="mt-1 text-sm font-black text-slate-900">
                                        {{ $result['usable_for_wallet_funding'] ? 'Available' : 'Not available' }}
                                    </p>
                                </div>

                            </div>

                        @endif

                    </div>

                @endisset

            </div>

        </div>

    </main>

    {{-- Main Esubiz Public Footer --}}
    @include('frontend.partials.footer')

</body>

</html>
