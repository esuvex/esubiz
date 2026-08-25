@extends('admin.layouts.app')

@section('title', 'Manual Payout')

@section('content')
<div
    x-data="{ addManual: false }"
    class="mx-auto max-w-7xl space-y-8 px-4 py-8 sm:px-6 lg:px-8"
>

    <div>
        <a
            href="{{ route('admin.site-settings.payout.index') }}"
            class="inline-flex items-center gap-2 text-sm font-black text-blue-600 hover:text-blue-700"
        >
            ← Back to Payout
        </a>

        <div class="mt-5 flex flex-col gap-4 md:flex-row md:items-end md:justify-between">

            <div>
                <h1 class="text-2xl font-black text-slate-900">
                    Manual Payout
                </h1>

                <p class="mt-1 text-sm text-slate-500">
                    Create unlimited manual payout methods and manage manual withdrawal requests.
                </p>
            </div>

            <button
                type="button"
                @click="addManual = true"
                class="rounded-xl bg-blue-600 px-4 py-3 text-sm font-black text-white hover:bg-blue-700"
            >
                Add Manual Payout Method
            </button>

        </div>
    </div>


    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-700">
            {{ session('success') }}
        </div>
    @endif


    @if($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
            <div class="font-black">Please correct the following:</div>

            <ul class="mt-2 list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    {{-- Manual methods --}}
    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">

        <div class="border-b border-slate-100 px-6 py-5">
            <h2 class="text-lg font-black text-slate-900">
                Manual Payout Methods
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Methods created by Admin and made available to users and developers.
            </p>
        </div>

        <div class="grid gap-6 p-6 md:grid-cols-2 xl:grid-cols-3">

            @forelse($manualMethods as $method)

                @php
                    $currencies = json_decode(
                        $method->supported_currencies ?? '[]',
                        true
                    ) ?: [];

                    $countries = json_decode(
                        $method->supported_countries ?? '[]',
                        true
                    ) ?: [];
                @endphp

                <div
                    x-data="{ editOpen: false }"
                    class="rounded-3xl border border-slate-200 bg-slate-50 p-5"
                >

                    <div class="flex items-start justify-between gap-4">

                        <div>
                            <h3 class="text-lg font-black text-slate-900">
                                {{ $method->name }}
                            </h3>

                            <div class="mt-1 text-xs font-bold uppercase tracking-wide text-slate-400">
                                {{ ucwords(str_replace('_', ' ', $method->type)) }}
                            </div>
                        </div>

                        <span
                            class="rounded-full px-3 py-1 text-xs font-black
                            {{ $method->is_active
                                ? 'bg-emerald-50 text-emerald-700'
                                : 'bg-slate-100 text-slate-500' }}"
                        >
                            {{ $method->is_active ? 'Active' : 'Disabled' }}
                        </span>

                    </div>

                    <div class="mt-5 space-y-3 text-sm">

                        <div class="flex justify-between gap-4">
                            <span class="text-slate-500">
                                Currencies
                            </span>

                            <span class="text-right font-bold text-slate-800">
                                {{ $currencies
                                    ? implode(', ', $currencies)
                                    : '—' }}
                            </span>
                        </div>

                        <div class="flex justify-between gap-4">
                            <span class="text-slate-500">
                                Countries
                            </span>

                            <span class="text-right font-bold text-slate-800">
                                {{ $countries
                                    ? implode(', ', $countries)
                                    : '—' }}
                            </span>
                        </div>

                        <div class="flex justify-between gap-4">
                            <span class="text-slate-500">
                                Fee
                            </span>

                            <span class="text-right font-bold text-slate-800">
                                {{ number_format(
                                    (float) $method->fixed_fee,
                                    2
                                ) }}
                                +
                                {{ number_format(
                                    (float) $method->percentage_fee,
                                    2
                                ) }}%
                            </span>
                        </div>

                        <div class="flex justify-between gap-4">
                            <span class="text-slate-500">
                                Minimum
                            </span>

                            <span class="font-bold text-slate-800">
                                {{ number_format(
                                    (float) $method->minimum_amount,
                                    2
                                ) }}
                            </span>
                        </div>

                        <div class="flex justify-between gap-4">
                            <span class="text-slate-500">
                                Maximum
                            </span>

                            <span class="font-bold text-slate-800">
                                {{ $method->maximum_amount !== null
                                    ? number_format(
                                        (float) $method->maximum_amount,
                                        2
                                    )
                                    : 'Unlimited' }}
                            </span>
                        </div>

                        <div class="flex justify-between gap-4">
                            <span class="text-slate-500">
                                Processing
                            </span>

                            <span class="font-bold text-slate-800">
                                {{ (int) $method->processing_time }} min
                            </span>
                        </div>

                        <div class="flex justify-between gap-4">
                            <span class="text-slate-500">
                                Priority
                            </span>

                            <span class="font-bold text-slate-800">
                                {{ $method->priority }}
                            </span>
                        </div>

                        <div class="flex justify-between gap-4">
                            <span class="text-slate-500">
                                Default
                            </span>

                            <span class="font-bold text-slate-800">
                                {{ $method->is_default ? 'Yes' : 'No' }}
                            </span>
                        </div>

                        <div class="flex justify-between gap-4">
                            <span class="text-slate-500">
                                Conversion
                            </span>

                            <span class="text-right font-bold text-slate-800">
                                @if($method->conversion_enabled)
                                    {{ ucfirst($method->conversion_type) }}
                                    →
                                    {{ $method->conversion_target ?? '—' }}
                                @else
                                    None
                                @endif
                            </span>
                        </div>

                        @if($method->conversion_enabled)
                            <div class="flex justify-between gap-4">
                                <span class="text-slate-500">
                                    Rate Source
                                </span>

                                <span class="text-right font-bold text-slate-800">
                                    {{ match($method->conversion_provider) {
                                        'frankfurter' => 'Frankfurter',
                                        'coingecko' => 'CoinGecko',
                                        'manual' => 'Manual Rate',
                                        default => '—',
                                    } }}
                                </span>
                            </div>
                        @endif

                    </div>

                    <div class="mt-6 flex gap-3">

                        <button
                            type="button"
                            @click="editOpen = true"
                            class="flex-1 rounded-xl bg-blue-600 px-4 py-3 text-sm font-black text-white hover:bg-blue-700"
                        >
                            Edit
                        </button>

                        <form
                            method="POST"
                            action="{{ route(
                                'admin.site-settings.payout.manual.destroy',
                                $method->id
                            ) }}"
                            class="flex-1"
                            onsubmit="return confirm('Remove this payout method?')"
                        >
                            @csrf
                            @method('DELETE')

                            <button
                                type="submit"
                                class="w-full rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-black text-red-600 hover:bg-red-100"
                            >
                                Remove
                            </button>
                        </form>

                    </div>


                    {{-- Edit modal --}}
                    <template x-teleport="body">

                        <div
                            x-show="editOpen"
                            x-cloak
                            class="fixed inset-0 z-[9999] overflow-y-auto bg-slate-950/50 p-4 sm:p-6"
                        >

                            <div
                                @click.outside="editOpen = false"
                                class="my-6 mx-auto max-h-[calc(100vh-3rem)] w-full max-w-2xl overflow-y-auto rounded-3xl bg-white p-6 shadow-2xl sm:my-8 sm:max-h-[calc(100vh-4rem)]"
                            >

                                <div class="flex items-center justify-between gap-4">

                                    <div>
                                        <h3 class="text-xl font-black text-slate-900">
                                            Edit {{ $method->name }}
                                        </h3>

                                        <p class="mt-1 text-sm text-slate-500">
                                            Update this manual payout method.
                                        </p>
                                    </div>

                                    <button
                                        type="button"
                                        @click="editOpen = false"
                                        class="rounded-xl px-3 py-2 text-slate-400 hover:bg-slate-100"
                                    >
                                        ✕
                                    </button>

                                </div>


                                <form
                                    method="POST"
                                    action="{{ route(
                                        'admin.site-settings.payout.manual.update',
                                        $method->id
                                    ) }}"
                                    class="mt-6 space-y-4"
                                >

                                    @csrf
                                    @method('PUT')


                                    <div>
                                        <label class="text-xs font-black uppercase tracking-wide text-slate-500">
                                            Method Name
                                        </label>

                                        <input
                                            type="text"
                                            name="name"
                                            value="{{ $method->name }}"
                                            required
                                            class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3"
                                        >
                                    </div>


                                    <div>
                                        <label class="text-xs font-black uppercase tracking-wide text-slate-500">
                                            Type
                                        </label>

                                        <select
                                            name="type"
                                            required
                                            class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3"
                                        >
                                            @foreach([
                                                'bank_transfer' => 'Bank Transfer',
                                                'wallet' => 'Wallet',
                                                'paypal' => 'PayPal',
                                                'crypto' => 'Crypto',
                                                'mobile_money' => 'Mobile Money',
                                                'stripe_connect' => 'Stripe Connect',
                                                'other' => 'Other',
                                            ] as $value => $label)

                                                <option
                                                    value="{{ $value }}"
                                                    @selected($method->type === $value)
                                                >
                                                    {{ $label }}
                                                </option>

                                            @endforeach
                                        </select>
                                    </div>


                                    <div class="grid gap-4 sm:grid-cols-2">

                                        <input
                                            type="text"
                                            name="supported_currencies"
                                            value="{{ implode(', ', $currencies) }}"
                                            placeholder="Currencies: NGN, USD"
                                            class="rounded-xl border border-slate-300 px-4 py-3"
                                        >

                                        <input
                                            type="text"
                                            name="supported_countries"
                                            value="{{ implode(', ', $countries) }}"
                                            placeholder="Countries: NG, GH"
                                            class="rounded-xl border border-slate-300 px-4 py-3"
                                        >

                                    </div>


                                    <div class="grid gap-4 sm:grid-cols-2">

                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            name="fixed_fee"
                                            value="{{ $method->fixed_fee }}"
                                            placeholder="Fixed fee"
                                            class="rounded-xl border border-slate-300 px-4 py-3"
                                        >

                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            name="percentage_fee"
                                            value="{{ $method->percentage_fee }}"
                                            placeholder="Percentage fee"
                                            class="rounded-xl border border-slate-300 px-4 py-3"
                                        >

                                    </div>


                                    <div class="grid gap-4 sm:grid-cols-2">

                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            name="minimum_amount"
                                            value="{{ $method->minimum_amount }}"
                                            placeholder="Minimum payout"
                                            class="rounded-xl border border-slate-300 px-4 py-3"
                                        >

                                        <input
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            name="maximum_amount"
                                            value="{{ $method->maximum_amount }}"
                                            placeholder="Maximum payout"
                                            class="rounded-xl border border-slate-300 px-4 py-3"
                                        >

                                    </div>


                                    <div class="grid gap-4 sm:grid-cols-2">

                                        <input
                                            type="number"
                                            min="0"
                                            name="processing_time"
                                            value="{{ $method->processing_time }}"
                                            placeholder="Processing time"
                                            class="rounded-xl border border-slate-300 px-4 py-3"
                                        >

                                        <input
                                            type="number"
                                            min="1"
                                            name="priority"
                                            value="{{ $method->priority }}"
                                            class="rounded-xl border border-slate-300 px-4 py-3"
                                        >

                                    </div>




                                    @php
                                        $existingFormFields = json_decode(
                                            $method->form_fields ?? '[]',
                                            true
                                        ) ?: [];

                                        $builderFields = collect(
                                            $existingFormFields
                                        )->map(function ($field) {
                                            return [
                                                'label' =>
                                                    $field['label'] ?? '',
                                                'type' =>
                                                    $field['type'] ?? 'text',
                                                'required' =>
                                                    (bool) (
                                                        $field['required']
                                                            ?? false
                                                    ),
                                                'placeholder' =>
                                                    $field['placeholder']
                                                        ?? '',
                                                'help_text' =>
                                                    $field['help_text']
                                                        ?? '',
                                                'options' =>
                                                    implode(
                                                        "\n",
                                                        $field['options']
                                                            ?? []
                                                    ),
                                            ];
                                        })->values();
                                    @endphp

                                    <div
                                        x-data="{
                                            fields: @js($builderFields),

                                            addField() {
                                                this.fields.push({
                                                    label: '',
                                                    type: 'text',
                                                    required: false,
                                                    placeholder: '',
                                                    help_text: '',
                                                    options: ''
                                                });
                                            },

                                            removeField(index) {
                                                this.fields.splice(index, 1);
                                            },

                                            needsOptions(type) {
                                                return [
                                                    'select',
                                                    'radio',
                                                    'checkbox'
                                                ].includes(type);
                                            }
                                        }"
                                        class="rounded-2xl border border-slate-200 bg-white p-4"
                                    >
                                        <div class="flex items-center justify-between gap-4">

                                            <div>
                                                <div class="font-black text-slate-900">
                                                    Custom Payout Form Fields
                                                </div>

                                                <div class="mt-1 text-xs text-slate-500">
                                                    Build the form users/developers must complete for this payout method.
                                                </div>
                                            </div>

                                            <button
                                                type="button"
                                                @click="addField()"
                                                class="rounded-xl bg-blue-600 px-3 py-2 text-xs font-black text-white hover:bg-blue-700"
                                            >
                                                + Add Field
                                            </button>

                                        </div>

                                        <div
                                            x-show="fields.length === 0"
                                            class="mt-4 rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-5 text-center text-xs text-slate-500"
                                        >
                                            No custom fields added yet.
                                        </div>

                                        <div class="mt-4 space-y-4">

                                            <template
                                                x-for="(field, index) in fields"
                                                :key="index"
                                            >
                                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">

                                                    <div class="flex items-center justify-between gap-4">

                                                        <div class="text-sm font-black text-slate-800">
                                                            Field
                                                            <span x-text="index + 1"></span>
                                                        </div>

                                                        <button
                                                            type="button"
                                                            @click="removeField(index)"
                                                            class="text-xs font-black text-red-600 hover:text-red-700"
                                                        >
                                                            Remove
                                                        </button>

                                                    </div>

                                                    <div class="mt-4 grid gap-4 sm:grid-cols-2">

                                                        <div>
                                                            <label class="text-xs font-black uppercase tracking-wide text-slate-500">
                                                                Label
                                                            </label>

                                                            <input
                                                                type="text"
                                                                x-model="field.label"
                                                                :name="`form_fields[${index}][label]`"
                                                                required
                                                                placeholder="Account Number"
                                                                class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3"
                                                            >
                                                        </div>

                                                        <div>
                                                            <label class="text-xs font-black uppercase tracking-wide text-slate-500">
                                                                Field Type
                                                            </label>

                                                            <select
                                                                x-model="field.type"
                                                                :name="`form_fields[${index}][type]`"
                                                                class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3"
                                                            >
                                                                <option value="text">Text</option>
                                                                <option value="number">Number</option>
                                                                <option value="email">Email</option>
                                                                <option value="tel">Phone</option>
                                                                <option value="textarea">Textarea</option>
                                                                <option value="select">Dropdown</option>
                                                                <option value="radio">Radio</option>
                                                                <option value="checkbox">Checkbox</option>
                                                                <option value="date">Date</option>
                                                                <option value="url">URL</option>
                                                                <option value="password">Password</option>
                                                                <option value="hidden">Hidden</option>
                                                                <option value="instructions">Instructions</option>
                                                            </select>
                                                        </div>

                                                    </div>

                                                    <div class="mt-4 grid gap-4 sm:grid-cols-2">

                                                        <div>
                                                            <label class="text-xs font-black uppercase tracking-wide text-slate-500">
                                                                Placeholder
                                                            </label>

                                                            <input
                                                                type="text"
                                                                x-model="field.placeholder"
                                                                :name="`form_fields[${index}][placeholder]`"
                                                                placeholder="Enter value"
                                                                class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3"
                                                            >
                                                        </div>

                                                        <div>
                                                            <label class="text-xs font-black uppercase tracking-wide text-slate-500">
                                                                Help Text
                                                            </label>

                                                            <input
                                                                type="text"
                                                                x-model="field.help_text"
                                                                :name="`form_fields[${index}][help_text]`"
                                                                placeholder="Optional explanation"
                                                                class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3"
                                                            >
                                                        </div>

                                                    </div>

                                                    <div
                                                        x-show="needsOptions(field.type)"
                                                        x-cloak
                                                        class="mt-4"
                                                    >
                                                        <label class="text-xs font-black uppercase tracking-wide text-slate-500">
                                                            Options
                                                        </label>

                                                        <textarea
                                                            x-model="field.options"
                                                            :name="`form_fields[${index}][options]`"
                                                            rows="4"
                                                            placeholder="One option per line&#10;GTBank&#10;Access Bank&#10;UBA"
                                                            class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3"
                                                        ></textarea>

                                                        <p class="mt-1 text-[11px] text-slate-400">
                                                            One option per line or separated by commas.
                                                        </p>
                                                    </div>

                                                    <label class="mt-4 flex items-center gap-2 text-sm font-bold text-slate-700">
                                                        <input
                                                            type="checkbox"
                                                            x-model="field.required"
                                                            :name="`form_fields[${index}][required]`"
                                                            value="1"
                                                        >
                                                        Required field
                                                    </label>

                                                </div>
                                            </template>

                                        </div>

                                    </div>

                                    <div
                                        x-data="{
                                            enabled: @js((bool) $method->conversion_enabled),
                                            type: @js($method->conversion_type ?? 'none'),
                                            provider: @js($method->conversion_provider ?? '')
                                        }"
                                        class="rounded-2xl border border-slate-200 bg-slate-50 p-4"
                                    >
                                        <div class="flex items-center justify-between gap-4">
                                            <div>
                                                <div class="font-black text-slate-900">
                                                    Currency / Crypto Conversion
                                                </div>

                                                <div class="mt-1 text-xs text-slate-500">
                                                    Admin-only rate configuration.
                                                </div>
                                            </div>

                                            <label class="flex items-center gap-2 text-sm font-bold text-slate-700">
                                                <input
                                                    type="checkbox"
                                                    name="conversion_enabled"
                                                    value="1"
                                                    x-model="enabled"
                                                    @checked($method->conversion_enabled)
                                                >
                                                Enable
                                            </label>
                                        </div>

                                        <div
                                            x-show="enabled"
                                            x-cloak
                                            class="mt-4 space-y-4"
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
                                                    <option value="none">None</option>
                                                    <option value="fiat">Fiat Currency</option>
                                                    <option value="crypto">Cryptocurrency</option>
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
                                                        Select rate source
                                                    </option>

                                                    <option
                                                        value="frankfurter"
                                                        x-show="type === 'fiat'"
                                                    >
                                                        Frankfurter — Free Fiat API
                                                    </option>

                                                    <option
                                                        value="coingecko"
                                                        x-show="type === 'crypto'"
                                                    >
                                                        CoinGecko — Free Crypto API
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
                                                    value="{{ $method->conversion_target }}"
                                                    placeholder="USD, GBP, BTC, ETH, USDT"
                                                    class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 uppercase"
                                                >
                                            </div>

                                            <div x-show="provider === 'manual'" x-cloak>
                                                <label class="text-xs font-black uppercase tracking-wide text-slate-500">
                                                    Manual Conversion Rate
                                                </label>

                                                <input
                                                    type="number"
                                                    step="0.000000000001"
                                                    min="0.000000000001"
                                                    name="manual_conversion_rate"
                                                    value="{{ $method->manual_conversion_rate }}"
                                                    placeholder="Example: 0.000625"
                                                    class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3"
                                                >

                                                <p class="mt-1 text-[11px] text-slate-400">
                                                    1 wallet base-currency unit = this amount of the payout currency/asset.
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex flex-wrap gap-5">

                                        <label class="flex items-center gap-2 text-sm font-bold text-slate-700">
                                            <input
                                                type="checkbox"
                                                name="is_active"
                                                value="1"
                                                @checked($method->is_active)
                                            >
                                            Active
                                        </label>

                                        <label class="flex items-center gap-2 text-sm font-bold text-slate-700">
                                            <input
                                                type="checkbox"
                                                name="is_default"
                                                value="1"
                                                @checked($method->is_default)
                                            >
                                            Default
                                        </label>

                                    </div>


                                    
                                    @php
                                        $payoutUsage = json_decode(
                                            $method->usage_contexts ?? '[]',
                                            true
                                        ) ?: [];
                                    @endphp

                                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">

                                        <div class="font-black text-slate-900">
                                            Allowed Payout Uses
                                        </div>

                                        <p class="mt-1 text-xs text-slate-500">
                                            Choose where this payout method is available.
                                        </p>

                                        <div class="mt-4 space-y-3">

                                            <label class="flex items-center gap-3 text-sm font-bold text-slate-700">
                                                <input
                                                    type="checkbox"
                                                    name="usage_contexts[]"
                                                    value="user_payout"
                                                    @checked(in_array("user_payout", $payoutUsage, true))
                                                >
                                                User Payout
                                            </label>

                                            <label class="flex items-center gap-3 text-sm font-bold text-slate-700">
                                                <input
                                                    type="checkbox"
                                                    name="usage_contexts[]"
                                                    value="developer_payout"
                                                    @checked(in_array("developer_payout", $payoutUsage, true))
                                                >
                                                Developer / Off-server Payout
                                            </label>

                                            <label class="flex items-center gap-3 text-sm font-bold text-slate-700">
                                                <input
                                                    type="checkbox"
                                                    name="usage_contexts[]"
                                                    value="marketplace_payout"
                                                    @checked(in_array("marketplace_payout", $payoutUsage, true))
                                                >
                                                Marketplace / Seller Payout
                                            </label>

                                        </div>
                                    </div>


                                    <div
                                        x-data="{
                                            enabled: @js((bool) ($method->markdown_enabled ?? false)),
                                            type: @js($method->markdown_type ?? 'percentage')
                                        }"
                                        class="rounded-2xl border border-slate-200 bg-slate-50 p-4"
                                    >

                                        <div class="flex items-center justify-between gap-4">

                                            <div>
                                                <div class="font-black text-slate-900">
                                                    Payout Markdown
                                                </div>

                                                <p class="mt-1 text-xs text-slate-500">
                                                    Reduce the converted payout amount before settlement.
                                                </p>
                                            </div>

                                            <label class="flex items-center gap-2 text-sm font-bold text-slate-700">
                                                <input
                                                    type="checkbox"
                                                    name="markdown_enabled"
                                                    value="1"
                                                    x-model="enabled"
                                                    @checked($method->markdown_enabled ?? false)
                                                >
                                                Enable
                                            </label>

                                        </div>

                                        <div
                                            x-show="enabled"
                                            x-cloak
                                            class="mt-4 grid gap-4 sm:grid-cols-2"
                                        >

                                            <div>
                                                <label class="text-xs font-black uppercase tracking-wide text-slate-500">
                                                    Markdown Type
                                                </label>

                                                <select
                                                    name="markdown_type"
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
                                                    Markdown Value
                                                </label>

                                                <input
                                                    type="number"
                                                    name="markdown_value"
                                                    value="{{ $method->markdown_value ?? 0 }}"
                                                    min="0"
                                                    step="0.00000001"
                                                    class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3"
                                                >

                                                <p class="mt-1 text-[11px] text-slate-400">
                                                    Percentage uses %. Fixed uses the converted payout currency/asset.
                                                </p>
                                            </div>

                                        </div>
                                    </div>

<button
                                        type="submit"
                                        class="w-full rounded-xl bg-blue-600 px-4 py-3 font-black text-white hover:bg-blue-700"
                                    >
                                        Save Changes
                                    </button>

                                </form>

                            </div>

                        </div>

                    </template>

                </div>

            @empty

                <div class="md:col-span-2 xl:col-span-3 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-10 text-center text-sm text-slate-500">
                    No manual payout methods created yet.
                </div>

            @endforelse

        </div>


        @if($manualMethods->hasPages())

            <div class="border-t border-slate-100 px-6 py-4">
                {{ $manualMethods
                    ->appends(request()->except('manual_page'))
                    ->links() }}
            </div>

        @endif

    </section>


    {{-- Payout requests --}}
    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">

        <div class="border-b border-slate-100 px-6 py-5">
            <h2 class="text-lg font-black text-slate-900">
                Payout Requests
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Review submitted payout details and approve or reject manual withdrawals.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">

                <thead class="bg-slate-50">
                    <tr class="text-left text-[10px] font-black uppercase tracking-wider text-slate-500">
                        <th class="px-5 py-4">User</th>
                        <th class="px-5 py-4">Reference</th>
                        <th class="px-5 py-4">Method</th>
                        <th class="px-5 py-4">Requested</th>
                        <th class="px-5 py-4">Status</th>
                        <th class="px-5 py-4">Details</th>
                        <th class="px-5 py-4">Date</th>
                        <th class="px-5 py-4 text-right">Action</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">

                    @forelse($requests as $requestRow)

                        @php
                            $requestMeta = json_decode(
                                $requestRow->metadata ?? '{}',
                                true
                            ) ?: [];
                        @endphp

                        <tr
                            x-data="{
                                detailsOpen: false,
                                rejectOpen: false
                            }"
                            class="align-top hover:bg-slate-50"
                        >

                            <td class="px-5 py-4">
                                <div class="font-black text-slate-900">
                                    {{ $requestRow->user_name ?? 'Unknown User' }}
                                </div>

                                <div class="mt-1 text-xs text-slate-500">
                                    {{ $requestRow->user_email ?? '—' }}
                                </div>
                            </td>

                            <td class="px-5 py-4">
                                <code class="text-xs font-bold text-slate-600">
                                    {{ $requestRow->reference }}
                                </code>
                            </td>

                            <td class="px-5 py-4 font-bold text-slate-700">
                                {{ $requestRow->payout_method_name ?? '—' }}
                            </td>

                            <td class="px-5 py-4">
                                <div class="font-black text-slate-900">
                                    {{ $requestMeta['wallet_currency'] ?? 'NGN' }}
                                    {{ number_format(
                                        (float) $requestRow->requested_amount,
                                        2
                                    ) }}
                                </div>

                                @if(!empty($requestMeta['fee']))
                                    <div class="mt-1 text-xs text-slate-400">
                                        Fee:
                                        {{ $requestMeta['wallet_currency'] ?? 'NGN' }}
                                        {{ number_format(
                                            (float) $requestMeta['fee'],
                                            2
                                        ) }}
                                    </div>
                                @endif
                            </td>

                            <td class="px-5 py-4">
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black capitalize text-slate-700">
                                    {{ str_replace(
                                        '_',
                                        ' ',
                                        $requestRow->status
                                    ) }}
                                </span>
                            </td>

                            <td class="px-5 py-4">

                                <button
                                    type="button"
                                    @click="detailsOpen = true"
                                    class="rounded-xl border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-black text-blue-700 hover:bg-blue-100"
                                >
                                    View Details
                                </button>


                                <template x-teleport="body">
                                    <div
                                        x-show="detailsOpen"
                                        x-cloak
                                        class="fixed inset-0 z-[9999] overflow-y-auto bg-slate-950/50 p-4 sm:p-6"
                                    >
                                        <div
                                            @click.outside="detailsOpen = false"
                                            class="my-6 mx-auto max-h-[calc(100vh-3rem)] w-full max-w-lg overflow-y-auto rounded-3xl bg-white p-6 shadow-2xl sm:my-8 sm:max-h-[calc(100vh-4rem)]"
                                        >

                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <h3 class="text-xl font-black text-slate-900">
                                                        Payout Details
                                                    </h3>

                                                    <p class="mt-1 text-xs text-slate-500">
                                                        {{ $requestRow->reference }}
                                                    </p>
                                                </div>

                                                <button
                                                    type="button"
                                                    @click="detailsOpen = false"
                                                    class="rounded-xl px-3 py-2 text-xl text-slate-400 hover:bg-slate-100"
                                                >
                                                    &times;
                                                </button>
                                            </div>


                                            <div class="mt-6 space-y-4">

                                                <div class="rounded-2xl bg-slate-50 p-4">
                                                    <div class="text-xs font-black uppercase text-slate-400">
                                                        Customer
                                                    </div>

                                                    <div class="mt-2 font-black text-slate-900">
                                                        {{ $requestRow->user_name ?? 'Unknown User' }}
                                                    </div>

                                                    <div class="text-sm text-slate-500">
                                                        {{ $requestRow->user_email ?? '—' }}
                                                    </div>
                                                </div>


                                                <div class="grid grid-cols-2 gap-4">

                                                    <div class="rounded-2xl border border-slate-200 p-4">
                                                        <div class="text-xs text-slate-400">
                                                            Method
                                                        </div>

                                                        <div class="mt-1 font-black text-slate-900">
                                                            {{ $requestRow->payout_method_name ?? '—' }}
                                                        </div>
                                                    </div>

                                                    <div class="rounded-2xl border border-slate-200 p-4">
                                                        <div class="text-xs text-slate-400">
                                                            Payout Currency
                                                        </div>

                                                        <div class="mt-1 font-black text-slate-900">
                                                            {{ $requestMeta['payout_currency'] ?? '—' }}
                                                        </div>
                                                    </div>

                                                </div>


                                                <div class="grid grid-cols-2 gap-4">

                                                    <div class="rounded-2xl border border-slate-200 p-4">
                                                        <div class="text-xs text-slate-400">
                                                            Requested
                                                        </div>

                                                        <div class="mt-1 font-black text-slate-900">
                                                            {{ $requestMeta['wallet_currency'] ?? 'NGN' }}
                                                            {{ number_format(
                                                                (float) $requestRow->requested_amount,
                                                                2
                                                            ) }}
                                                        </div>
                                                    </div>

                                                    <div class="rounded-2xl border border-slate-200 p-4">
                                                        <div class="text-xs text-slate-400">
                                                            Fee
                                                        </div>

                                                        <div class="mt-1 font-black text-slate-900">
                                                            {{ $requestMeta['wallet_currency'] ?? 'NGN' }}
                                                            {{ number_format(
                                                                (float) (
                                                                    $requestMeta['fee'] ?? 0
                                                                ),
                                                                2
                                                            ) }}
                                                        </div>
                                                    </div>

                                                </div>


                                                <div class="rounded-2xl border border-slate-200 p-4">
                                                    <div class="text-xs font-black uppercase tracking-wide text-slate-400">
                                                        Submitted Payout Details
                                                    </div>

                                                    <div class="mt-3 whitespace-pre-wrap break-words text-sm font-medium text-slate-800">{{ $requestMeta['account_details'] ?? 'No payout details supplied.' }}</div>
                                                </div>


                                                @if(!empty($requestMeta['payout_fields']))
                                                    <div class="rounded-2xl border border-slate-200 p-4">

                                                        <div class="text-xs font-black uppercase tracking-wide text-slate-400">
                                                            Submitted Form Fields
                                                        </div>

                                                        <div class="mt-4 space-y-3">

                                                            @foreach(
                                                                $requestMeta['payout_fields']
                                                                as $submittedField
                                                            )

                                                                <div class="border-b border-slate-100 pb-3 last:border-0 last:pb-0">

                                                                    <div class="text-xs font-bold text-slate-400">
                                                                        {{ $submittedField['label']
                                                                            ?? $submittedField['key']
                                                                            ?? 'Field' }}
                                                                    </div>

                                                                    <div class="mt-1 break-words text-sm font-black text-slate-800">
                                                                        @if(is_array(
                                                                            $submittedField['value']
                                                                                ?? null
                                                                        ))
                                                                            {{ implode(
                                                                                ', ',
                                                                                $submittedField['value']
                                                                            ) }}
                                                                        @else
                                                                            {{ $submittedField['value']
                                                                                ?? '—' }}
                                                                        @endif
                                                                    </div>

                                                                </div>

                                                            @endforeach

                                                        </div>

                                                    </div>
                                                @endif


                                                @if(!empty($requestRow->rejection_reason))
                                                    <div class="rounded-2xl border border-red-200 bg-red-50 p-4">
                                                        <div class="text-xs font-black uppercase text-red-500">
                                                            Rejection Reason
                                                        </div>

                                                        <div class="mt-2 text-sm text-red-700">
                                                            {{ $requestRow->rejection_reason }}
                                                        </div>
                                                    </div>
                                                @endif

                                            </div>

                                        </div>
                                    </div>
                                </template>


                                <template x-teleport="body">
                                    <div
                                        x-show="rejectOpen"
                                        x-cloak
                                        class="fixed inset-0 z-[9999] overflow-y-auto bg-slate-950/50 p-4 sm:p-6"
                                    >
                                        <div
                                            @click.outside="rejectOpen = false"
                                            class="my-6 mx-auto max-h-[calc(100vh-3rem)] w-full max-w-md overflow-y-auto rounded-3xl bg-white p-6 shadow-2xl sm:my-8 sm:max-h-[calc(100vh-4rem)]"
                                        >
                                            <h3 class="text-xl font-black text-slate-900">
                                                Reject Payout
                                            </h3>

                                            <p class="mt-1 text-sm text-slate-500">
                                                Reserved funds will be returned to the user's wallet.
                                            </p>

                                            <form
                                                method="POST"
                                                action="{{ route(
                                                    'admin.site-settings.payout.request.reject',
                                                    $requestRow->id
                                                ) }}"
                                                class="mt-5 space-y-4"
                                            >
                                                @csrf

                                                <textarea
                                                    name="rejection_reason"
                                                    rows="4"
                                                    required
                                                    placeholder="Reason for rejecting this payout request"
                                                    class="w-full rounded-xl border border-slate-300 px-4 py-3"
                                                ></textarea>

                                                <div class="flex gap-3">
                                                    <button
                                                        type="button"
                                                        @click="rejectOpen = false"
                                                        class="flex-1 rounded-xl border border-slate-200 px-4 py-3 font-black text-slate-700"
                                                    >
                                                        Cancel
                                                    </button>

                                                    <button
                                                        type="submit"
                                                        class="flex-1 rounded-xl bg-red-600 px-4 py-3 font-black text-white"
                                                    >
                                                        Reject
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </template>

                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-xs text-slate-500">
                                {{ \Carbon\Carbon::parse(
                                    $requestRow->created_at
                                )->format('d M Y, h:i A') }}
                            </td>

                            <td class="px-5 py-4 text-right">

                                @if(!in_array(
                                    strtolower((string) $requestRow->status),
                                    [
                                        'completed',
                                        'paid',
                                        'rejected',
                                        'cancelled',
                                        'canceled',
                                        'failed'
                                    ],
                                    true
                                ))

                                    <div class="flex justify-end gap-2">

                                        <form
                                            method="POST"
                                            action="{{ route(
                                                'admin.site-settings.payout.request.complete',
                                                $requestRow->id
                                            ) }}"
                                            onsubmit="return confirm(
                                                'Approve this payout and confirm that it has been paid?'
                                            )"
                                        >
                                            @csrf

                                            <button
                                                type="submit"
                                                class="rounded-xl bg-blue-600 px-3 py-2 text-xs font-black text-white hover:bg-blue-700"
                                            >
                                                Approve
                                            </button>
                                        </form>

                                        <button
                                            type="button"
                                            @click="rejectOpen = true"
                                            class="rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-xs font-black text-red-600 hover:bg-red-100"
                                        >
                                            Reject
                                        </button>

                                    </div>

                                @else

                                    <span class="text-xs font-bold capitalize text-slate-400">
                                        {{ $requestRow->status }}
                                    </span>

                                @endif

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td
                                colspan="8"
                                class="px-6 py-12 text-center text-sm text-slate-500"
                            >
                                No manual payout requests yet.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>
        </div>


        @if($requests->hasPages())
            <div class="border-t border-slate-100 px-6 py-4">
                {{ $requests
                    ->appends(request()->except('request_page'))
                    ->links() }}
            </div>
        @endif

    </section>


    {{-- Add Manual modal --}}
    <template x-teleport="body">

        <div
            x-show="addManual"
            x-cloak
            class="fixed inset-0 z-[9999] overflow-y-auto bg-slate-950/50 p-4 sm:p-6"
        >

            <div
                @click.outside="addManual = false"
                class="my-6 mx-auto max-h-[calc(100vh-3rem)] w-full max-w-2xl overflow-y-auto rounded-3xl bg-white p-6 shadow-2xl sm:my-8 sm:max-h-[calc(100vh-4rem)]"
            >

                <div class="flex items-center justify-between gap-4">

                    <div>
                        <h3 class="text-xl font-black text-slate-900">
                            Add Manual Payout Method
                        </h3>

                        <p class="mt-1 text-sm text-slate-500">
                            Create another manual payout option.
                        </p>
                    </div>

                    <button
                        type="button"
                        @click="addManual = false"
                        class="rounded-xl px-3 py-2 text-slate-400 hover:bg-slate-100"
                    >
                        ✕
                    </button>

                </div>


                <form
                    method="POST"
                    action="{{ route(
                        'admin.site-settings.payout.manual.store'
                    ) }}"
                    class="mt-6 space-y-4"
                >

                    @csrf


                    <div>
                        <label class="text-xs font-black uppercase tracking-wide text-slate-500">
                            Method Name
                        </label>

                        <input
                            type="text"
                            name="name"
                            required
                            placeholder="Bank Transfer"
                            class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3"
                        >
                    </div>


                    <div>
                        <label class="text-xs font-black uppercase tracking-wide text-slate-500">
                            Type
                        </label>

                        <select
                            name="type"
                            required
                            class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3"
                        >
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="wallet">Wallet</option>
                            <option value="paypal">PayPal</option>
                            <option value="crypto">Crypto</option>
                            <option value="mobile_money">Mobile Money</option>
                            <option value="stripe_connect">Stripe Connect</option>
                            <option value="other">Other</option>
                        </select>
                    </div>


                    <div class="grid gap-4 sm:grid-cols-2">

                        <input
                            type="text"
                            name="supported_currencies"
                            placeholder="Currencies: NGN, USD"
                            class="rounded-xl border border-slate-300 px-4 py-3"
                        >

                        <input
                            type="text"
                            name="supported_countries"
                            placeholder="Countries: NG, GH"
                            class="rounded-xl border border-slate-300 px-4 py-3"
                        >

                    </div>


                    <div class="grid gap-4 sm:grid-cols-2">

                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            name="fixed_fee"
                            placeholder="Fixed fee"
                            class="rounded-xl border border-slate-300 px-4 py-3"
                        >

                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            name="percentage_fee"
                            placeholder="Percentage fee"
                            class="rounded-xl border border-slate-300 px-4 py-3"
                        >

                    </div>


                    <div class="grid gap-4 sm:grid-cols-2">

                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            name="minimum_amount"
                            placeholder="Minimum payout"
                            class="rounded-xl border border-slate-300 px-4 py-3"
                        >

                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            name="maximum_amount"
                            placeholder="Maximum payout"
                            class="rounded-xl border border-slate-300 px-4 py-3"
                        >

                    </div>


                    <div class="grid gap-4 sm:grid-cols-2">

                        <input
                            type="number"
                            min="0"
                            name="processing_time"
                            placeholder="Processing time (minutes)"
                            class="rounded-xl border border-slate-300 px-4 py-3"
                        >

                        <input
                            type="number"
                            min="1"
                            name="priority"
                            value="10"
                            class="rounded-xl border border-slate-300 px-4 py-3"
                        >

                    </div>




                    <div
                        x-data="{
                            fields: [],

                            addField() {
                                this.fields.push({
                                    label: '',
                                    type: 'text',
                                    required: false,
                                    placeholder: '',
                                    help_text: '',
                                    options: ''
                                });
                            },

                            removeField(index) {
                                this.fields.splice(index, 1);
                            },

                            needsOptions(type) {
                                return [
                                    'select',
                                    'radio',
                                    'checkbox'
                                ].includes(type);
                            }
                        }"
                        class="rounded-2xl border border-slate-200 bg-white p-4"
                    >

                        <div class="flex items-center justify-between gap-4">

                            <div>
                                <div class="font-black text-slate-900">
                                    Custom Payout Form Fields
                                </div>

                                <div class="mt-1 text-xs text-slate-500">
                                    Build the form users/developers will complete for this payout method.
                                </div>
                            </div>

                            <button
                                type="button"
                                @click="addField()"
                                class="rounded-xl bg-blue-600 px-3 py-2 text-xs font-black text-white hover:bg-blue-700"
                            >
                                + Add Field
                            </button>

                        </div>

                        <div
                            x-show="fields.length === 0"
                            class="mt-4 rounded-xl border border-dashed border-slate-300 bg-slate-50 px-4 py-5 text-center text-xs text-slate-500"
                        >
                            No custom fields added yet.
                        </div>

                        <div class="mt-4 space-y-4">

                            <template
                                x-for="(field, index) in fields"
                                :key="index"
                            >
                                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">

                                    <div class="flex items-center justify-between gap-4">

                                        <div class="text-sm font-black text-slate-800">
                                            Field
                                            <span x-text="index + 1"></span>
                                        </div>

                                        <button
                                            type="button"
                                            @click="removeField(index)"
                                            class="text-xs font-black text-red-600 hover:text-red-700"
                                        >
                                            Remove
                                        </button>

                                    </div>

                                    <div class="mt-4 grid gap-4 sm:grid-cols-2">

                                        <div>
                                            <label class="text-xs font-black uppercase tracking-wide text-slate-500">
                                                Label
                                            </label>

                                            <input
                                                type="text"
                                                x-model="field.label"
                                                :name="`form_fields[${index}][label]`"
                                                required
                                                placeholder="Account Number"
                                                class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3"
                                            >
                                        </div>

                                        <div>
                                            <label class="text-xs font-black uppercase tracking-wide text-slate-500">
                                                Field Type
                                            </label>

                                            <select
                                                x-model="field.type"
                                                :name="`form_fields[${index}][type]`"
                                                class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3"
                                            >
                                                <option value="text">Text</option>
                                                <option value="number">Number</option>
                                                <option value="email">Email</option>
                                                <option value="tel">Phone</option>
                                                <option value="textarea">Textarea</option>
                                                <option value="select">Dropdown</option>
                                                <option value="radio">Radio</option>
                                                <option value="checkbox">Checkbox</option>
                                                <option value="date">Date</option>
                                                <option value="url">URL</option>
                                                <option value="password">Password</option>
                                                <option value="hidden">Hidden</option>
                                                <option value="instructions">Instructions</option>
                                            </select>
                                        </div>

                                    </div>

                                    <div class="mt-4 grid gap-4 sm:grid-cols-2">

                                        <input
                                            type="text"
                                            x-model="field.placeholder"
                                            :name="`form_fields[${index}][placeholder]`"
                                            placeholder="Placeholder"
                                            class="rounded-xl border border-slate-300 bg-white px-4 py-3"
                                        >

                                        <input
                                            type="text"
                                            x-model="field.help_text"
                                            :name="`form_fields[${index}][help_text]`"
                                            placeholder="Help text"
                                            class="rounded-xl border border-slate-300 bg-white px-4 py-3"
                                        >

                                    </div>

                                    <div
                                        x-show="needsOptions(field.type)"
                                        x-cloak
                                        class="mt-4"
                                    >
                                        <label class="text-xs font-black uppercase tracking-wide text-slate-500">
                                            Options
                                        </label>

                                        <textarea
                                            x-model="field.options"
                                            :name="`form_fields[${index}][options]`"
                                            rows="4"
                                            placeholder="One option per line"
                                            class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3"
                                        ></textarea>
                                    </div>

                                    <label class="mt-4 flex items-center gap-2 text-sm font-bold text-slate-700">
                                        <input
                                            type="checkbox"
                                            x-model="field.required"
                                            :name="`form_fields[${index}][required]`"
                                            value="1"
                                        >
                                        Required field
                                    </label>

                                </div>
                            </template>

                        </div>

                    </div>

                    <div
                        x-data="{
                            enabled: false,
                            type: 'none',
                            provider: ''
                        }"
                        class="rounded-2xl border border-slate-200 bg-slate-50 p-4"
                    >
                        <div class="flex items-center justify-between gap-4">

                            <div>
                                <div class="font-black text-slate-900">
                                    Currency / Crypto Conversion
                                </div>

                                <div class="mt-1 text-xs text-slate-500">
                                    Configure a free rate source for this payout method.
                                </div>
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
                            class="mt-4 space-y-4"
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
                                    <option value="none">None</option>
                                    <option value="fiat">Fiat Currency</option>
                                    <option value="crypto">Cryptocurrency</option>
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
                                        Select rate source
                                    </option>

                                    <option
                                        value="frankfurter"
                                        x-show="type === 'fiat'"
                                    >
                                        Frankfurter — Free Fiat API
                                    </option>

                                    <option
                                        value="coingecko"
                                        x-show="type === 'crypto'"
                                    >
                                        CoinGecko — Free Crypto API
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
                                    placeholder="USD, GBP, BTC, ETH, USDT"
                                    class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 uppercase"
                                >
                            </div>

                            <div x-show="provider === 'manual'" x-cloak>
                                <label class="text-xs font-black uppercase tracking-wide text-slate-500">
                                    Manual Conversion Rate
                                </label>

                                <input
                                    type="number"
                                    step="0.000000000001"
                                    min="0.000000000001"
                                    name="manual_conversion_rate"
                                    placeholder="Example: 0.000625"
                                    class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3"
                                >

                                <p class="mt-1 text-[11px] text-slate-400">
                                    1 wallet base-currency unit = this amount of the payout currency/asset.
                                </p>
                            </div>

                        </div>
                    </div>

                    <div class="flex flex-wrap gap-5">

                        <label class="flex items-center gap-2 text-sm font-bold text-slate-700">
                            <input
                                type="checkbox"
                                name="is_active"
                                value="1"
                            >
                            Active
                        </label>

                        <label class="flex items-center gap-2 text-sm font-bold text-slate-700">
                            <input
                                type="checkbox"
                                name="is_default"
                                value="1"
                            >
                            Default
                        </label>

                    </div>


                    
                                    @php $payoutUsage = []; @endphp

                                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">

                                        <div class="font-black text-slate-900">
                                            Allowed Payout Uses
                                        </div>

                                        <p class="mt-1 text-xs text-slate-500">
                                            Choose where this payout method is available.
                                        </p>

                                        <div class="mt-4 space-y-3">

                                            <label class="flex items-center gap-3 text-sm font-bold text-slate-700">
                                                <input
                                                    type="checkbox"
                                                    name="usage_contexts[]"
                                                    value="user_payout"
                                                    checked
                                                >
                                                User Payout
                                            </label>

                                            <label class="flex items-center gap-3 text-sm font-bold text-slate-700">
                                                <input
                                                    type="checkbox"
                                                    name="usage_contexts[]"
                                                    value="developer_payout"
                                                    checked
                                                >
                                                Developer / Off-server Payout
                                            </label>

                                            <label class="flex items-center gap-3 text-sm font-bold text-slate-700">
                                                <input
                                                    type="checkbox"
                                                    name="usage_contexts[]"
                                                    value="marketplace_payout"
                                                    
                                                >
                                                Marketplace / Seller Payout
                                            </label>

                                        </div>
                                    </div>


                                    <div
                                        x-data="{
                                            enabled: false,
                                            type: 'percentage'
                                        }"
                                        class="rounded-2xl border border-slate-200 bg-slate-50 p-4"
                                    >

                                        <div class="flex items-center justify-between gap-4">

                                            <div>
                                                <div class="font-black text-slate-900">
                                                    Payout Markdown
                                                </div>

                                                <p class="mt-1 text-xs text-slate-500">
                                                    Reduce the converted payout amount before settlement.
                                                </p>
                                            </div>

                                            <label class="flex items-center gap-2 text-sm font-bold text-slate-700">
                                                <input
                                                    type="checkbox"
                                                    name="markdown_enabled"
                                                    value="1"
                                                    x-model="enabled"
                                                    
                                                >
                                                Enable
                                            </label>

                                        </div>

                                        <div
                                            x-show="enabled"
                                            x-cloak
                                            class="mt-4 grid gap-4 sm:grid-cols-2"
                                        >

                                            <div>
                                                <label class="text-xs font-black uppercase tracking-wide text-slate-500">
                                                    Markdown Type
                                                </label>

                                                <select
                                                    name="markdown_type"
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
                                                    Markdown Value
                                                </label>

                                                <input
                                                    type="number"
                                                    name="markdown_value"
                                                    value="0"
                                                    min="0"
                                                    step="0.00000001"
                                                    class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3"
                                                >

                                                <p class="mt-1 text-[11px] text-slate-400">
                                                    Percentage uses %. Fixed uses the converted payout currency/asset.
                                                </p>
                                            </div>

                                        </div>
                                    </div>

<button
                        type="submit"
                        class="w-full rounded-xl bg-blue-600 px-4 py-3 font-black text-white hover:bg-blue-700"
                    >
                        Create Payout Method
                    </button>

                </form>

            </div>

        </div>

    </template>

</div>
@endsection
