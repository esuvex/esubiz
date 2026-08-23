@extends('admin.layouts.app')

@section('content')

<div class="mx-auto max-w-7xl space-y-6">

    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-black text-slate-900">
            Wallet
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            Fund your Esubiz wallet, manage withdrawals and review your transactions.
        </p>
    </div>

    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-bold text-red-700">
            {{ session('error') }}
        </div>
    @endif

    {{-- Wallet Summary --}}
    <div class="grid gap-5 lg:grid-cols-3">

        <div class="rounded-3xl bg-slate-900 p-6 text-white shadow-lg lg:col-span-2">
            <div class="text-xs font-black uppercase tracking-[0.18em] text-slate-400">
                Available Balance
            </div>

            <div class="mt-4 text-4xl font-black tracking-tight">
                {{ strtoupper($wallet->currency) }}
                {{ number_format((float) $wallet->available_balance, 2) }}
            </div>

            <div class="mt-8 flex flex-wrap gap-3 text-xs">
                <div class="rounded-xl bg-white/10 px-4 py-3">
                    <div class="text-slate-400">Pending</div>
                    <div class="mt-1 font-black text-white">
                        {{ strtoupper($wallet->currency) }}
                        {{ number_format((float) $wallet->pending_balance, 2) }}
                    </div>
                </div>

                <div class="rounded-xl bg-white/10 px-4 py-3">
                    <div class="text-slate-400">Reserved</div>
                    <div class="mt-1 font-black text-white">
                        {{ strtoupper($wallet->currency) }}
                        {{ number_format((float) $wallet->reserved_balance, 2) }}
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="text-xs font-black uppercase tracking-wider text-slate-400">
                Wallet Details
            </div>

            <div class="mt-5 space-y-4">
                <div>
                    <div class="text-xs text-slate-400">Wallet Name</div>
                    <div class="mt-1 font-black text-slate-900">
                        {{ $wallet->name }}
                    </div>
                </div>

                <div>
                    <div class="text-xs text-slate-400">Currency</div>
                    <div class="mt-1 font-black text-slate-900">
                        {{ strtoupper($wallet->currency) }}
                    </div>
                </div>

                <div>
                    <div class="text-xs text-slate-400">Status</div>
                    <div class="mt-1">
                        <span class="inline-flex rounded-full bg-emerald-50 px-3 py-1 text-[10px] font-black uppercase text-emerald-700">
                            Active
                        </span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Actions --}}
    <div class="grid gap-5 md:grid-cols-2">

        {{-- Fund --}}
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 text-xl font-black text-blue-600">
                +
            </div>

            <h2 class="mt-5 text-lg font-black text-slate-900">
                Fund Wallet
            </h2>

            <p class="mt-2 text-sm leading-6 text-slate-500">
                Add funds to your Esubiz wallet using an available payment method.
            </p>

            @if($settings && !$settings->funding_enabled)
                <div class="mt-5 rounded-xl bg-amber-50 px-4 py-3 text-xs font-bold text-amber-700">
                    Wallet funding is currently unavailable.
                </div>
            @else
                <div
                    x-data="{
                        open: false,
                        amount: '',
                        paymentOption: '',
                        giftCardCode: '',
                        giftCardValidating: false,
                        giftCardValid: false,
                        giftCardMessage: '',
                        giftCardBalance: null,

                        feeType: @js($settings->funding_fee_type ?? 'free'),
                        feeValue: Number(@js((float) ($settings->funding_fee ?? 0))),

                        get numericAmount() {
                            return Number(this.amount || 0);
                        },

                        get fundingFee() {
                            if (this.numericAmount <= 0) {
                                return 0;
                            }

                            if (this.feeType === 'percentage') {
                                return (
                                    this.numericAmount *
                                    (this.feeValue / 100)
                                );
                            }

                            if (this.feeType === 'fixed') {
                                return this.feeValue;
                            }

                            return 0;
                        },

                        get paymentTotal() {
                            return this.numericAmount + this.fundingFee;
                        },

                        resetGiftCard() {
                            this.giftCardValid = false;
                            this.giftCardMessage = '';
                            this.giftCardBalance = null;
                        },

                        async validateGiftCard() {
                            this.resetGiftCard();

                            if (!this.giftCardCode.trim()) {
                                this.giftCardMessage =
                                    'Enter a Gift Card code.';
                                return;
                            }

                            if (this.numericAmount <= 0) {
                                this.giftCardMessage =
                                    'Enter the amount you want to fund first.';
                                return;
                            }

                            this.giftCardValidating = true;

                            try {
                                const response = await fetch(
                                    @js(route('account.wallet.gift-card.validate')),
                                    {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'Accept': 'application/json',
                                            'X-CSRF-TOKEN':
                                                document.querySelector(
                                                    'meta[name=csrf-token]'
                                                )?.content || ''
                                        },
                                        body: JSON.stringify({
                                            code: this.giftCardCode.trim(),
                                            amount: this.paymentTotal,
                                            currency: @js($wallet->currency)
                                        })
                                    }
                                );

                                const data = await response.json();

                                this.giftCardValid =
                                    response.ok &&
                                    data.valid === true;

                                this.giftCardBalance =
                                    data.balance !== undefined
                                        ? Number(data.balance)
                                        : null;

                                this.giftCardMessage =
                                    data.message ||
                                    (
                                        this.giftCardValid
                                            ? 'Gift Card is valid.'
                                            : 'Gift Card could not be validated.'
                                    );
                            } catch (error) {
                                this.giftCardValid = false;
                                this.giftCardMessage =
                                    'Unable to validate Gift Card. Please try again.';
                            } finally {
                                this.giftCardValidating = false;
                            }
                        }
                    }"
                >
                    <button
                        type="button"
                        @click="open = true"
                        class="mt-6 w-full rounded-xl bg-blue-600 px-5 py-3 text-sm font-black text-white hover:bg-blue-700"
                    >
                        Fund Wallet
                    </button>

                    <template x-teleport="body">
                        <div
                            x-show="open"
                            x-cloak
                            @keydown.escape.window="open = false"
                            class="fixed inset-0 z-[9999] overflow-y-auto p-4 sm:p-6"
                        >
                            <div
                                class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm"
                                @click="open = false"
                            ></div>

                            <div
                                x-show="open"
                                x-transition
                                class="relative z-10 my-6 mx-auto max-h-[calc(100vh-3rem)] w-full max-w-md overflow-y-auto rounded-3xl bg-white shadow-2xl sm:my-8 sm:max-h-[calc(100vh-4rem)]"
                            >
                                <div class="sticky top-0 z-10 flex items-center justify-between border-b border-slate-100 bg-white px-6 py-5">
                                    <div>
                                        <h3 class="text-lg font-black text-slate-900">
                                            Fund Wallet
                                        </h3>

                                        <p class="mt-1 text-xs text-slate-500">
                                            Add money to your Esubiz wallet.
                                        </p>
                                    </div>

                                    <button
                                        type="button"
                                        @click="open = false"
                                        class="flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 text-xl text-slate-500 hover:bg-slate-100"
                                    >
                                        &times;
                                    </button>
                                </div>

                                <form
                                    method="POST"
                                    action="{{ route('account.wallet.fund') }}"
                                    class="space-y-5 px-6 py-6"
                                >
                                    @csrf

                                    {{-- Amount --}}
                                    <div>
                                        <label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">
                                            Amount
                                        </label>

                                        <div class="flex items-center gap-3">
                                            <span class="shrink-0 text-sm font-black text-slate-600">
                                                {{ strtoupper($wallet->currency) }}
                                            </span>

                                            <input
                                                type="number"
                                                name="amount"
                                                x-model="amount"
                                                @input="resetGiftCard()"
                                                min="{{ $settings->minimum_funding ?? 1 }}"
                                                @if(!empty($settings->maximum_funding))
                                                    max="{{ $settings->maximum_funding }}"
                                                @endif
                                                step="0.01"
                                                required
                                                placeholder="0.00"
                                                class="min-w-0 flex-1 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-900 outline-none focus:border-blue-500 focus:bg-white"
                                            >
                                        </div>

                                        @if($settings)
                                            <div class="mt-2 text-[11px] text-slate-400">
                                                @if((float) $settings->minimum_funding > 0)
                                                    Minimum:
                                                    {{ strtoupper($wallet->currency) }}
                                                    {{ number_format((float) $settings->minimum_funding, 2) }}
                                                @endif

                                                @if(!empty($settings->maximum_funding))
                                                    <span class="mx-1">•</span>
                                                    Maximum:
                                                    {{ strtoupper($wallet->currency) }}
                                                    {{ number_format((float) $settings->maximum_funding, 2) }}
                                                @endif
                                            </div>
                                        @endif
                                    </div>

                                    {{-- Fee summary --}}
                                    <div
                                        x-show="numericAmount > 0"
                                        x-cloak
                                        class="rounded-2xl border border-slate-200 bg-slate-50 p-4"
                                    >
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-slate-500">
                                                Wallet Credit
                                            </span>

                                            <span class="font-black text-slate-900">
                                                {{ strtoupper($wallet->currency) }}
                                                <span x-text="numericAmount.toLocaleString(undefined, {
                                                    minimumFractionDigits: 2,
                                                    maximumFractionDigits: 2
                                                })"></span>
                                            </span>
                                        </div>

                                        <div class="mt-3 flex items-center justify-between text-sm">
                                            <span class="text-slate-500">
                                                Funding Fee
                                            </span>

                                            <span class="font-black text-slate-900">
                                                {{ strtoupper($wallet->currency) }}
                                                <span x-text="fundingFee.toLocaleString(undefined, {
                                                    minimumFractionDigits: 2,
                                                    maximumFractionDigits: 2
                                                })"></span>
                                            </span>
                                        </div>

                                        <div class="mt-3 border-t border-slate-200 pt-3">
                                            <div class="flex items-center justify-between">
                                                <span class="text-sm font-black text-slate-700">
                                                    Total to Pay
                                                </span>

                                                <span class="text-base font-black text-blue-600">
                                                    {{ strtoupper($wallet->currency) }}
                                                    <span x-text="paymentTotal.toLocaleString(undefined, {
                                                        minimumFractionDigits: 2,
                                                        maximumFractionDigits: 2
                                                    })"></span>
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Payment method --}}
                                    <div>
                                        <label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">
                                            Payment Method
                                        </label>

                                        <select
                                            name="payment_option"
                                            x-model="paymentOption"
                                            @change="resetGiftCard()"
                                            required
                                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-900 outline-none focus:border-blue-500 focus:bg-white"
                                        >
                                            <option value="">
                                                Select payment method
                                            </option>

                                            @foreach($onlineGateways as $gateway)
                                                <option value="online:{{ $gateway->slug }}">
                                                    {{ $gateway->name }}
                                                </option>
                                            @endforeach

                                            @foreach($offlineMethods as $method)
                                                <option value="offline:{{ $method->id }}">
                                                    {{ $method->name }}
                                                </option>
                                            @endforeach

                                            <option value="giftcard">
                                                Gift Card
                                            </option>
                                        </select>
                                    </div>

                                    {{-- Gift Card validation --}}
                                    <div
                                        x-show="paymentOption === 'giftcard'"
                                        x-cloak
                                        class="rounded-2xl border border-slate-200 bg-slate-50 p-4"
                                    >
                                        <label class="block text-xs font-black uppercase tracking-wider text-slate-500">
                                            Gift Card Code
                                        </label>

                                        <div class="mt-3 flex gap-2">
                                            <input
                                                type="text"
                                                name="gift_card_code"
                                                x-model="giftCardCode"
                                                @input="resetGiftCard()"
                                                :required="paymentOption === 'giftcard'"
                                                placeholder="Enter Gift Card code"
                                                autocomplete="off"
                                                class="min-w-0 flex-1 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-900 outline-none focus:border-blue-500"
                                            >

                                            <button
                                                type="button"
                                                @click="validateGiftCard()"
                                                :disabled="giftCardValidating || !giftCardCode.trim()"
                                                class="rounded-xl bg-slate-900 px-4 py-3 text-xs font-black text-white hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-50"
                                            >
                                                <span x-show="!giftCardValidating">
                                                    Validate
                                                </span>

                                                <span x-show="giftCardValidating" x-cloak>
                                                    Checking...
                                                </span>
                                            </button>
                                        </div>

                                        <div
                                            x-show="giftCardMessage"
                                            x-cloak
                                            class="mt-3 rounded-xl border bg-white p-3"
                                            :class="giftCardValid
                                                ? 'border-emerald-200'
                                                : 'border-red-200'"
                                        >
                                            <div
                                                class="text-xs font-bold"
                                                :class="giftCardValid
                                                    ? 'text-emerald-700'
                                                    : 'text-red-700'"
                                                x-text="giftCardMessage"
                                            ></div>

                                            <div
                                                x-show="giftCardBalance !== null"
                                                class="mt-2 flex items-center justify-between"
                                            >
                                                <span class="text-xs text-slate-500">
                                                    Available Balance
                                                </span>

                                                <span class="text-sm font-black text-slate-900">
                                                    {{ strtoupper($wallet->currency) }}
                                                    <span x-text="Number(giftCardBalance || 0).toLocaleString(undefined, {
                                                        minimumFractionDigits: 2,
                                                        maximumFractionDigits: 2
                                                    })"></span>
                                                </span>
                                            </div>
                                        </div>

                                        <input
                                            type="hidden"
                                            name="gift_card_validated"
                                            :value="giftCardValid ? '1' : '0'"
                                        >
                                    </div>

                                    <div class="flex gap-3 border-t border-slate-100 pt-5">
                                        <button
                                            type="button"
                                            @click="open = false"
                                            class="flex-1 rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 hover:bg-slate-50"
                                        >
                                            Cancel
                                        </button>

                                        <button
                                            type="submit"
                                            :disabled="
                                                !paymentOption ||
                                                numericAmount <= 0 ||
                                                (
                                                    paymentOption === 'giftcard' &&
                                                    !giftCardValid
                                                )
                                            "
                                            class="flex-1 rounded-xl bg-blue-600 px-5 py-3 text-sm font-black text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                                        >
                                            Continue
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </template>
                </div>
            @endif
        </div>

        {{-- Withdraw --}}
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-slate-100 text-xl font-black text-slate-700">
                −
            </div>

            <h2 class="mt-5 text-lg font-black text-slate-900">
                Withdraw
            </h2>

            <p class="mt-2 text-sm leading-6 text-slate-500">
                Request withdrawal of available wallet funds to your approved payout method.
            </p>

            @if($settings && !$settings->payout_enabled)

                <div class="mt-5 rounded-xl bg-amber-50 px-4 py-3 text-xs font-bold text-amber-700">
                    Wallet withdrawals are currently unavailable.
                </div>

            @elseif($payoutMethods->isEmpty())

                <div class="mt-5 rounded-xl bg-amber-50 px-4 py-3 text-xs font-bold text-amber-700">
                    No payout method is currently available.
                </div>

            @else

                <div
                    x-data="{
                        open: false,
                        amount: '',
                        methodId: '',
                        payoutCurrency: '',
                        accountDetails: '',

                        conversionLoading: false,
                        conversionError: '',
                        conversionResult: null,

                        methods: @js(
                            $payoutMethods->map(function ($method) {
                                return [
                                    'id' => (int) $method->id,
                                    'name' => $method->name,
                                    'type' => $method->type,
                                    'provider' => $method->provider_slug,
                                    'automatic' =>
                                        !empty($method->payment_provider_id),
                                    'fixed_fee' =>
                                        (float) $method->fixed_fee,
                                    'percentage_fee' =>
                                        (float) $method->percentage_fee,
                                    'minimum_amount' =>
                                        (float) ($method->minimum_amount ?? 0),
                                    'maximum_amount' =>
                                        $method->maximum_amount !== null
                                            ? (float) $method->maximum_amount
                                            : null,
                                    'currencies' =>
                                        json_decode(
                                            $method->supported_currencies
                                                ?? '[]',
                                            true
                                        ) ?: [],

                                    'conversion_enabled' =>
                                        (bool) (
                                            $method->conversion_enabled
                                                ?? false
                                        ),

                                    'conversion_target' =>
                                        $method->conversion_target
                                            ?? null,

                                    'form_fields' =>
                                        json_decode(
                                            $method->form_fields ?? '[]',
                                            true
                                        ) ?: [],
                                ];
                            })->values()
                        ),

                        globalFeeType:
                            @js($settings->payout_fee_type ?? 'free'),

                        globalFeeValue:
                            Number(
                                @js(
                                    (float) (
                                        $settings->payout_fee ?? 0
                                    )
                                )
                            ),

                        globalMinimum:
                            Number(
                                @js(
                                    (float) (
                                        $settings->minimum_payout ?? 0
                                    )
                                )
                            ),

                        globalMaximum:
                            @js(
                                !empty($settings->maximum_payout)
                                    ? (float) $settings->maximum_payout
                                    : null
                            ),

                        walletBalance:
                            Number(
                                @js(
                                    (float) $wallet->available_balance
                                )
                            ),

                        get selectedMethod() {
                            return this.methods.find(
                                item =>
                                    String(item.id) ===
                                    String(this.methodId)
                            ) || null;
                        },

                        get numericAmount() {
                            return Number(this.amount || 0);
                        },

                        get methodMinimum() {
                            return Number(
                                this.selectedMethod?.minimum_amount || 0
                            );
                        },

                        get methodMaximum() {
                            const value =
                                this.selectedMethod?.maximum_amount;

                            return value === null ||
                                   value === undefined
                                ? null
                                : Number(value);
                        },

                        get effectiveMinimum() {
                            return Math.max(
                                this.globalMinimum || 0,
                                this.methodMinimum || 0
                            );
                        },

                        get effectiveMaximum() {
                            const values = [];

                            if (
                                this.globalMaximum !== null &&
                                this.globalMaximum !== undefined
                            ) {
                                values.push(
                                    Number(this.globalMaximum)
                                );
                            }

                            if (
                                this.methodMaximum !== null &&
                                this.methodMaximum !== undefined
                            ) {
                                values.push(
                                    Number(this.methodMaximum)
                                );
                            }

                            return values.length
                                ? Math.min(...values)
                                : null;
                        },

                        get globalFee() {
                            if (this.numericAmount <= 0) {
                                return 0;
                            }

                            if (
                                this.globalFeeType ===
                                'percentage'
                            ) {
                                return (
                                    this.numericAmount *
                                    (this.globalFeeValue / 100)
                                );
                            }

                            if (
                                this.globalFeeType === 'fixed'
                            ) {
                                return this.globalFeeValue;
                            }

                            return 0;
                        },

                        get methodFee() {
                            if (
                                !this.selectedMethod ||
                                this.numericAmount <= 0
                            ) {
                                return 0;
                            }

                            return (
                                Number(
                                    this.selectedMethod.fixed_fee || 0
                                ) +
                                (
                                    this.numericAmount *
                                    (
                                        Number(
                                            this.selectedMethod
                                                .percentage_fee || 0
                                        ) / 100
                                    )
                                )
                            );
                        },

                        get totalFee() {
                            return this.globalFee + this.methodFee;
                        },

                        get totalDebit() {
                            return this.numericAmount + this.totalFee;
                        },

                        get amountValid() {
                            if (this.numericAmount <= 0) {
                                return false;
                            }

                            if (
                                this.numericAmount <
                                this.effectiveMinimum
                            ) {
                                return false;
                            }

                            if (
                                this.effectiveMaximum !== null &&
                                this.numericAmount >
                                this.effectiveMaximum
                            ) {
                                return false;
                            }

                            if (
                                this.totalDebit >
                                this.walletBalance
                            ) {
                                return false;
                            }

                            return true;
                        },

                        get selectedCurrencies() {
                            return this.selectedMethod?.currencies || [];
                        },

                        get selectedFormFields() {
                            return this.selectedMethod?.form_fields || [];
                        },

                        get needsConversion() {
                            return Boolean(
                                this.selectedMethod
                                    ?.conversion_enabled
                            );
                        },

                        resetConversion() {
                            this.conversionLoading = false;
                            this.conversionError = '';
                            this.conversionResult = null;
                        },

                        async previewConversion() {
                            this.resetConversion();

                            if (
                                !this.selectedMethod ||
                                !this.needsConversion ||
                                this.numericAmount <= 0
                            ) {
                                return;
                            }

                            this.conversionLoading = true;

                            try {
                                const response = await fetch(
                                    @js(
                                        route(
                                            'account.wallet.payout.preview-conversion'
                                        )
                                    ),
                                    {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type':
                                                'application/json',
                                            'Accept':
                                                'application/json',
                                            'X-CSRF-TOKEN':
                                                document
                                                    .querySelector(
                                                        'meta[name=csrf-token]'
                                                    )
                                                    ?.content || ''
                                        },
                                        body: JSON.stringify({
                                            payout_method_id:
                                                this.methodId,
                                            amount:
                                                this.numericAmount
                                        })
                                    }
                                );

                                const data =
                                    await response.json();

                                if (
                                    !response.ok ||
                                    data.success !== true
                                ) {
                                    throw new Error(
                                        data.message ||
                                        'Unable to calculate conversion.'
                                    );
                                }

                                this.conversionResult = data;

                                if (data.to_currency) {
                                    this.payoutCurrency =
                                        data.to_currency;
                                }

                            } catch (error) {
                                this.conversionError =
                                    error.message ||
                                    'Unable to calculate payout conversion.';
                            } finally {
                                this.conversionLoading = false;
                            }
                        },

                        methodChanged() {
                            this.accountDetails = '';
                            this.resetConversion();

                            if (
                                this.selectedMethod
                                    ?.conversion_target
                            ) {
                                this.payoutCurrency =
                                    this.selectedMethod
                                        .conversion_target;
                            } else {
                                this.payoutCurrency =
                                    this.selectedCurrencies[0] || '';
                            }

                            this.previewConversion();
                        }
                    }"
                >

                    <button
                        type="button"
                        @click="open = true"
                        class="mt-6 w-full rounded-xl bg-slate-900 px-5 py-3 text-sm font-black text-white hover:bg-slate-800"
                    >
                        Withdraw
                    </button>


                    <template x-teleport="body">

                        <div
                            x-show="open"
                            x-cloak
                            @keydown.escape.window="open = false"
                            class="fixed inset-0 z-[9999] overflow-y-auto p-4 sm:p-6"
                        >

                            <div
                                class="absolute inset-0 bg-slate-950/60 backdrop-blur-sm"
                                @click="open = false"
                            ></div>


                            <div
                                x-show="open"
                                x-transition
                                class="relative z-10 my-6 mx-auto max-h-[calc(100vh-3rem)] w-full max-w-md overflow-y-auto rounded-3xl bg-white shadow-2xl sm:my-8 sm:max-h-[calc(100vh-4rem)]"
                            >

                                <div class="sticky top-0 z-10 flex items-center justify-between border-b border-slate-100 bg-white px-6 py-5">

                                    <div>
                                        <h3 class="text-lg font-black text-slate-900">
                                            Withdraw
                                        </h3>

                                        <p class="mt-1 text-xs text-slate-500">
                                            Withdraw available wallet funds.
                                        </p>
                                    </div>

                                    <button
                                        type="button"
                                        @click="open = false"
                                        class="flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 text-xl text-slate-500 hover:bg-slate-100"
                                    >
                                        &times;
                                    </button>

                                </div>


                                <form
                                    method="POST"
                                    action="{{ route(
                                        'account.wallet.payout.request'
                                    ) }}"
                                    class="space-y-5 px-6 py-6"
                                >

                                    @csrf


                                    {{-- Payout method --}}
                                    <div>

                                        <label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">
                                            Payout Method
                                        </label>

                                        <select
                                            name="payout_method_id"
                                            x-model="methodId"
                                            @change="methodChanged()"
                                            required
                                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-900 outline-none focus:border-blue-500 focus:bg-white"
                                        >

                                            <option value="">
                                                Select payout method
                                            </option>

                                            @foreach($payoutMethods as $method)

                                                <option
                                                    value="{{ $method->id }}"
                                                >
                                                    {{ $method->name }}
                                                    —
                                                    {{ $method->payment_provider_id
                                                        ? 'Automatic'
                                                        : 'Manual' }}
                                                </option>

                                            @endforeach

                                        </select>

                                    </div>


                                    {{-- Amount --}}
                                    <div>

                                        <label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">
                                            Amount
                                        </label>

                                        <div class="flex items-center gap-3">

                                            <span class="shrink-0 text-sm font-black text-slate-600">
                                                {{ strtoupper($wallet->currency) }}
                                            </span>

                                            <input
                                                type="number"
                                                name="amount"
                                                x-model="amount"
                                                @input.debounce.500ms="previewConversion()"
                                                step="0.01"
                                                min="0.01"
                                                required
                                                placeholder="0.00"
                                                class="min-w-0 flex-1 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-900 outline-none focus:border-blue-500 focus:bg-white"
                                            >

                                        </div>


                                        <div
                                            x-show="selectedMethod"
                                            x-cloak
                                            class="mt-2 text-[11px] text-slate-400"
                                        >

                                            <span
                                                x-show="effectiveMinimum > 0"
                                            >
                                                Minimum:
                                                {{ strtoupper($wallet->currency) }}
                                                <span
                                                    x-text="effectiveMinimum.toLocaleString()"
                                                ></span>
                                            </span>

                                            <span
                                                x-show="
                                                    effectiveMinimum > 0 &&
                                                    effectiveMaximum !== null
                                                "
                                                class="mx-1"
                                            >
                                                •
                                            </span>

                                            <span
                                                x-show="
                                                    effectiveMaximum !== null
                                                "
                                            >
                                                Maximum:
                                                {{ strtoupper($wallet->currency) }}
                                                <span
                                                    x-text="Number(
                                                        effectiveMaximum
                                                    ).toLocaleString()"
                                                ></span>
                                            </span>

                                        </div>

                                    </div>


                                    {{-- Payout currency --}}
                                    <div x-show="selectedMethod" x-cloak>

                                        <label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">
                                            Payout Currency
                                        </label>

                                        <select
                                            name="payout_currency"
                                            x-model="payoutCurrency"
                                            required
                                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-900 outline-none focus:border-blue-500 focus:bg-white"
                                        >

                                            <template
                                                x-for="currency in selectedCurrencies"
                                                :key="currency"
                                            >
                                                <option
                                                    :value="currency"
                                                    x-text="currency"
                                                ></option>
                                            </template>

                                        </select>

                                    </div>


                                    {{-- Conversion preview --}}
                                    <div
                                        x-show="
                                            selectedMethod &&
                                            needsConversion
                                        "
                                        x-cloak
                                        class="rounded-2xl border border-blue-100 bg-blue-50 p-4"
                                    >

                                        <div class="text-xs font-black uppercase tracking-wider text-blue-500">
                                            Estimated Payout
                                        </div>

                                        <div
                                            x-show="conversionLoading"
                                            class="mt-2 text-sm font-bold text-blue-700"
                                        >
                                            Calculating current rate...
                                        </div>

                                        <div
                                            x-show="
                                                !conversionLoading &&
                                                conversionResult
                                            "
                                            x-cloak
                                            class="mt-3"
                                        >
                                            <div class="text-2xl font-black text-slate-900">
                                                <span
                                                    x-text="
                                                        conversionResult
                                                            ?.to_currency
                                                    "
                                                ></span>

                                                <span
                                                    x-text="
                                                        Number(
                                                            conversionResult
                                                                ?.receive_amount
                                                                || 0
                                                        ).toLocaleString(
                                                            undefined,
                                                            {
                                                                maximumFractionDigits:
                                                                    12
                                                            }
                                                        )
                                                    "
                                                ></span>
                                            </div>

                                            <div class="mt-2 text-xs text-slate-500">
                                                Based on the current conversion rate.
                                                Final payout value may vary slightly when processed.
                                            </div>
                                        </div>

                                        <div
                                            x-show="
                                                !conversionLoading &&
                                                conversionError
                                            "
                                            x-cloak
                                            class="mt-3 rounded-xl bg-red-50 px-3 py-2 text-xs font-bold text-red-600"
                                            x-text="conversionError"
                                        ></div>

                                    </div>


                                    {{-- Dynamic manual payout fields --}}
                                    <div
                                        x-show="
                                            selectedMethod &&
                                            selectedFormFields.length > 0
                                        "
                                        x-cloak
                                        class="space-y-4"
                                    >

                                        <template
                                            x-for="field in selectedFormFields"
                                            :key="field.key"
                                        >

                                            <div>

                                                <template
                                                    x-if="
                                                        field.type ===
                                                        'instructions'
                                                    "
                                                >
                                                    <div class="rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm text-blue-700">
                                                        <div
                                                            class="font-black"
                                                            x-text="field.label"
                                                        ></div>

                                                        <div
                                                            x-show="
                                                                field.help_text
                                                            "
                                                            class="mt-1 text-xs"
                                                            x-text="
                                                                field.help_text
                                                            "
                                                        ></div>
                                                    </div>
                                                </template>


                                                <template
                                                    x-if="
                                                        ![
                                                            'instructions',
                                                            'textarea',
                                                            'select',
                                                            'radio',
                                                            'checkbox'
                                                        ].includes(
                                                            field.type
                                                        )
                                                    "
                                                >
                                                    <div>
                                                        <label
                                                            class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500"
                                                        >
                                                            <span
                                                                x-text="
                                                                    field.label
                                                                "
                                                            ></span>

                                                            <span
                                                                x-show="
                                                                    field.required
                                                                "
                                                                class="text-red-500"
                                                            >
                                                                *
                                                            </span>
                                                        </label>

                                                        <input
                                                            :type="
                                                                field.type ===
                                                                'tel'
                                                                    ? 'tel'
                                                                    : field.type
                                                            "
                                                            :name="
                                                                `payout_fields[${field.key}]`
                                                            "
                                                            :required="
                                                                field.required
                                                            "
                                                            :placeholder="
                                                                field.placeholder
                                                                    || ''
                                                            "
                                                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-900 outline-none focus:border-blue-500 focus:bg-white"
                                                        >

                                                        <p
                                                            x-show="
                                                                field.help_text
                                                            "
                                                            class="mt-2 text-[11px] text-slate-400"
                                                            x-text="
                                                                field.help_text
                                                            "
                                                        ></p>
                                                    </div>
                                                </template>


                                                <template
                                                    x-if="
                                                        field.type ===
                                                        'textarea'
                                                    "
                                                >
                                                    <div>
                                                        <label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">
                                                            <span
                                                                x-text="
                                                                    field.label
                                                                "
                                                            ></span>

                                                            <span
                                                                x-show="
                                                                    field.required
                                                                "
                                                                class="text-red-500"
                                                            >
                                                                *
                                                            </span>
                                                        </label>

                                                        <textarea
                                                            :name="
                                                                `payout_fields[${field.key}]`
                                                            "
                                                            :required="
                                                                field.required
                                                            "
                                                            :placeholder="
                                                                field.placeholder
                                                                    || ''
                                                            "
                                                            rows="4"
                                                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-900 outline-none focus:border-blue-500 focus:bg-white"
                                                        ></textarea>

                                                        <p
                                                            x-show="
                                                                field.help_text
                                                            "
                                                            class="mt-2 text-[11px] text-slate-400"
                                                            x-text="
                                                                field.help_text
                                                            "
                                                        ></p>
                                                    </div>
                                                </template>


                                                <template
                                                    x-if="
                                                        field.type ===
                                                        'select'
                                                    "
                                                >
                                                    <div>
                                                        <label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">
                                                            <span
                                                                x-text="
                                                                    field.label
                                                                "
                                                            ></span>

                                                            <span
                                                                x-show="
                                                                    field.required
                                                                "
                                                                class="text-red-500"
                                                            >
                                                                *
                                                            </span>
                                                        </label>

                                                        <select
                                                            :name="
                                                                `payout_fields[${field.key}]`
                                                            "
                                                            :required="
                                                                field.required
                                                            "
                                                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-bold text-slate-900 outline-none focus:border-blue-500 focus:bg-white"
                                                        >
                                                            <option value="">
                                                                Select option
                                                            </option>

                                                            <template
                                                                x-for="
                                                                    option
                                                                    in
                                                                    field.options
                                                                "
                                                                :key="
                                                                    option
                                                                "
                                                            >
                                                                <option
                                                                    :value="
                                                                        option
                                                                    "
                                                                    x-text="
                                                                        option
                                                                    "
                                                                ></option>
                                                            </template>
                                                        </select>
                                                    </div>
                                                </template>


                                                <template
                                                    x-if="
                                                        field.type ===
                                                        'radio'
                                                    "
                                                >
                                                    <div>
                                                        <label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">
                                                            <span
                                                                x-text="
                                                                    field.label
                                                                "
                                                            ></span>

                                                            <span
                                                                x-show="
                                                                    field.required
                                                                "
                                                                class="text-red-500"
                                                            >
                                                                *
                                                            </span>
                                                        </label>

                                                        <div class="space-y-2">
                                                            <template
                                                                x-for="
                                                                    option
                                                                    in
                                                                    field.options
                                                                "
                                                                :key="
                                                                    option
                                                                "
                                                            >
                                                                <label class="flex items-center gap-2 text-sm text-slate-700">
                                                                    <input
                                                                        type="radio"
                                                                        :name="
                                                                            `payout_fields[${field.key}]`
                                                                        "
                                                                        :value="
                                                                            option
                                                                        "
                                                                        :required="
                                                                            field.required
                                                                        "
                                                                    >

                                                                    <span
                                                                        x-text="
                                                                            option
                                                                        "
                                                                    ></span>
                                                                </label>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </template>


                                                <template
                                                    x-if="
                                                        field.type ===
                                                        'checkbox'
                                                    "
                                                >
                                                    <div>
                                                        <label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">
                                                            <span
                                                                x-text="
                                                                    field.label
                                                                "
                                                            ></span>

                                                            <span
                                                                x-show="
                                                                    field.required
                                                                "
                                                                class="text-red-500"
                                                            >
                                                                *
                                                            </span>
                                                        </label>

                                                        <div class="space-y-2">
                                                            <template
                                                                x-for="
                                                                    option
                                                                    in
                                                                    field.options
                                                                "
                                                                :key="
                                                                    option
                                                                "
                                                            >
                                                                <label class="flex items-center gap-2 text-sm text-slate-700">
                                                                    <input
                                                                        type="checkbox"
                                                                        :name="
                                                                            `payout_fields[${field.key}][]`
                                                                        "
                                                                        :value="
                                                                            option
                                                                        "
                                                                    >

                                                                    <span
                                                                        x-text="
                                                                            option
                                                                        "
                                                                    ></span>
                                                                </label>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </template>

                                            </div>

                                        </template>

                                    </div>


                                    {{-- Fallback payout details --}}
                                    <div
                                        x-show="
                                            selectedMethod &&
                                            selectedFormFields.length === 0
                                        "
                                        x-cloak
                                    >

                                        <label class="mb-2 block text-xs font-black uppercase tracking-wider text-slate-500">
                                            Payout Details
                                        </label>

                                        <textarea
                                            name="account_details"
                                            x-model="accountDetails"
                                            rows="4"
                                            placeholder="Enter the payout details required for this method."
                                            class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-medium text-slate-900 outline-none focus:border-blue-500 focus:bg-white"
                                        ></textarea>

                                    </div>


                                    {{-- Summary --}}
                                    <div
                                        x-show="
                                            selectedMethod &&
                                            numericAmount > 0
                                        "
                                        x-cloak
                                        class="rounded-2xl border border-slate-200 bg-slate-50 p-4"
                                    >

                                        <div class="flex items-center justify-between text-sm">

                                            <span class="text-slate-500">
                                                Payout Amount
                                            </span>

                                            <span class="font-black text-slate-900">
                                                {{ strtoupper($wallet->currency) }}
                                                <span
                                                    x-text="numericAmount.toLocaleString(
                                                        undefined,
                                                        {
                                                            minimumFractionDigits: 2,
                                                            maximumFractionDigits: 2
                                                        }
                                                    )"
                                                ></span>
                                            </span>

                                        </div>


                                        <div class="mt-3 flex items-center justify-between text-sm">

                                            <span class="text-slate-500">
                                                Payout Fee
                                            </span>

                                            <span class="font-black text-slate-900">
                                                {{ strtoupper($wallet->currency) }}
                                                <span
                                                    x-text="totalFee.toLocaleString(
                                                        undefined,
                                                        {
                                                            minimumFractionDigits: 2,
                                                            maximumFractionDigits: 2
                                                        }
                                                    )"
                                                ></span>
                                            </span>

                                        </div>


                                        <div class="mt-3 border-t border-slate-200 pt-3">

                                            <div class="flex items-center justify-between">

                                                <span class="text-sm font-black text-slate-700">
                                                    Total Wallet Debit
                                                </span>

                                                <span class="text-base font-black text-blue-600">
                                                    {{ strtoupper($wallet->currency) }}
                                                    <span
                                                        x-text="totalDebit.toLocaleString(
                                                            undefined,
                                                            {
                                                                minimumFractionDigits: 2,
                                                                maximumFractionDigits: 2
                                                            }
                                                        )"
                                                    ></span>
                                                </span>

                                            </div>

                                        </div>


                                        <div
                                            x-show="
                                                totalDebit >
                                                walletBalance
                                            "
                                            class="mt-3 rounded-xl bg-red-50 px-3 py-2 text-xs font-bold text-red-600"
                                        >
                                            Insufficient available wallet balance.
                                        </div>

                                    </div>


                                    <div class="flex gap-3 border-t border-slate-100 pt-5">

                                        <button
                                            type="button"
                                            @click="open = false"
                                            class="flex-1 rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700 hover:bg-slate-50"
                                        >
                                            Cancel
                                        </button>

                                        <button
                                            type="submit"
                                            :disabled="
                                                !selectedMethod ||
                                                !amountValid ||
                                                !payoutCurrency ||
                                                (
                                                    selectedFormFields.length === 0 &&
                                                    !accountDetails.trim()
                                                ) ||
                                                conversionLoading ||
                                                (
                                                    needsConversion &&
                                                    !conversionResult
                                                )
                                            "
                                            class="flex-1 rounded-xl bg-blue-600 px-5 py-3 text-sm font-black text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
                                        >
                                            Submit
                                        </button>

                                    </div>

                                </form>

                            </div>

                        </div>

                    </template>

                </div>

            @endif
        </div>

    </div>

    {{-- Transactions --}}
    <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">

        <div class="border-b border-slate-100 px-6 py-5">
            <h2 class="text-lg font-black text-slate-900">
                Wallet Transactions
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Your wallet credit and debit history.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">

                <thead class="bg-slate-50">
                    <tr class="text-left text-[10px] font-black uppercase tracking-wider text-slate-500">
                        <th class="px-6 py-4">Transaction</th>
                        <th class="px-6 py-4">Type</th>
                        <th class="px-6 py-4">Amount</th>
                        <th class="px-6 py-4">Balance Before</th>
                        <th class="px-6 py-4">Balance After</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Date</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">

                    @forelse($transactions as $transaction)
                        <tr class="hover:bg-slate-50">

                            <td class="px-6 py-4">
                                <div class="font-black text-slate-900">
                                    {{ $transaction->description ?? ucfirst(str_replace('_', ' ', $transaction->type ?? 'Transaction')) }}
                                </div>

                                @if(!empty($transaction->reference))
                                    <div class="mt-1 text-xs text-slate-400">
                                        {{ $transaction->reference }}
                                    </div>
                                @endif
                            </td>

                            <td class="px-6 py-4">
                                <span class="font-bold text-slate-600">
                                    {{ ucfirst(str_replace('_', ' ', $transaction->type ?? 'transaction')) }}
                                </span>
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 font-black
                                {{ ($transaction->direction ?? '') === 'credit'
                                    ? 'text-emerald-600'
                                    : 'text-slate-900' }}">
                                {{ ($transaction->direction ?? '') === 'credit' ? '+' : '-' }}
                                {{ strtoupper($wallet->currency) }}
                                {{ number_format((float) $transaction->amount, 2) }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-slate-500">
                                {{ number_format((float) ($transaction->balance_before ?? 0), 2) }}
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 font-bold text-slate-900">
                                {{ number_format((float) ($transaction->balance_after ?? 0), 2) }}
                            </td>

                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-[10px] font-black uppercase text-slate-600">
                                    {{ $transaction->display_status
                                        ?? $transaction->status
                                        ?? 'completed' }}
                                </span>
                            </td>

                            <td class="whitespace-nowrap px-6 py-4 text-xs text-slate-500">
                                {{ \Carbon\Carbon::parse($transaction->created_at)->format('d M Y, h:i A') }}
                            </td>

                        </tr>

                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-sm text-slate-500">
                                No wallet transactions yet.
                            </td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
            <div class="border-t border-slate-100 px-6 py-5">
                {{ $transactions->links() }}
            </div>
        @endif

    </div>

</div>

@endsection
