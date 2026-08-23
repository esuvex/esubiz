@extends('admin.layouts.app')

@section('title', 'Automatic Payout')

@section('content')
<div class="mx-auto max-w-7xl space-y-8 px-4 py-8 sm:px-6 lg:px-8">

    <div>
        <a
            href="{{ route('admin.site-settings.payout.index') }}"
            class="inline-flex items-center gap-2 text-sm font-black text-blue-600 hover:text-blue-700"
        >
            ← Back to Payout
        </a>

        <div class="mt-5">
            <h1 class="text-2xl font-black text-slate-900">
                Automatic Payout
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                Manage provider-connected payout methods processed automatically by Esubiz.
            </p>
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


    <div class="grid gap-6 lg:grid-cols-3">

        @forelse($automaticMethods as $method)

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

            <form
                method="POST"
                action="{{ route(
                    'admin.site-settings.payout.automatic.update',
                    $method->id
                ) }}"
                class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"
            >
                @csrf

                <div class="flex items-start justify-between gap-4">

                    <div>
                        <h2 class="text-lg font-black text-slate-900">
                            {{ $method->name }}
                        </h2>

                        <div class="mt-1 text-xs font-bold uppercase tracking-wide text-slate-400">
                            {{ ucwords(str_replace('_', ' ', $method->type)) }}
                        </div>
                    </div>

                    <label class="flex items-center gap-2 text-xs font-black text-slate-600">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            @checked($method->is_active)
                            class="rounded border-slate-300"
                        >
                        Enabled
                    </label>

                </div>

                @if(!$method->provider_is_active)
                    <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-3 py-2 text-xs font-bold text-amber-700">
                        Linked provider is disabled under Payment Gateways.
                    </div>
                @endif


                <div class="mt-5 space-y-4">

                    <div>
                        <label class="text-xs font-black uppercase tracking-wide text-slate-500">
                            Supported Currencies
                        </label>

                        <input
                            type="text"
                            name="supported_currencies"
                            value="{{ implode(', ', $currencies) }}"
                            class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3"
                        >
                    </div>

                    <div>
                        <label class="text-xs font-black uppercase tracking-wide text-slate-500">
                            Supported Countries
                        </label>

                        <input
                            type="text"
                            name="supported_countries"
                            value="{{ implode(', ', $countries) }}"
                            class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3"
                        >
                    </div>


                    <div class="grid grid-cols-2 gap-3">

                        <div>
                            <label class="text-xs font-black uppercase tracking-wide text-slate-500">
                                Fixed Fee
                            </label>

                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                name="fixed_fee"
                                value="{{ $method->fixed_fee }}"
                                class="mt-2 w-full rounded-xl border border-slate-300 px-3 py-3"
                            >
                        </div>

                        <div>
                            <label class="text-xs font-black uppercase tracking-wide text-slate-500">
                                Percentage Fee
                            </label>

                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                name="percentage_fee"
                                value="{{ $method->percentage_fee }}"
                                class="mt-2 w-full rounded-xl border border-slate-300 px-3 py-3"
                            >
                        </div>

                    </div>


                    <div class="grid grid-cols-2 gap-3">

                        <div>
                            <label class="text-xs font-black uppercase tracking-wide text-slate-500">
                                Minimum
                            </label>

                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                name="minimum_amount"
                                value="{{ $method->minimum_amount }}"
                                class="mt-2 w-full rounded-xl border border-slate-300 px-3 py-3"
                            >
                        </div>

                        <div>
                            <label class="text-xs font-black uppercase tracking-wide text-slate-500">
                                Maximum
                            </label>

                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                name="maximum_amount"
                                value="{{ $method->maximum_amount }}"
                                placeholder="Unlimited"
                                class="mt-2 w-full rounded-xl border border-slate-300 px-3 py-3"
                            >
                        </div>

                    </div>


                    <div class="grid grid-cols-2 gap-3">

                        <div>
                            <label class="text-xs font-black uppercase tracking-wide text-slate-500">
                                Processing Time
                            </label>

                            <input
                                type="number"
                                min="0"
                                name="processing_time"
                                value="{{ $method->processing_time }}"
                                class="mt-2 w-full rounded-xl border border-slate-300 px-3 py-3"
                            >

                            <div class="mt-1 text-[11px] text-slate-400">
                                Minutes. 0 = immediate.
                            </div>
                        </div>

                        <div>
                            <label class="text-xs font-black uppercase tracking-wide text-slate-500">
                                Priority
                            </label>

                            <input
                                type="number"
                                min="1"
                                name="priority"
                                value="{{ $method->priority }}"
                                class="mt-2 w-full rounded-xl border border-slate-300 px-3 py-3"
                            >
                        </div>

                    </div>


                    <label class="flex items-center gap-2 text-sm font-bold text-slate-700">
                        <input
                            type="checkbox"
                            name="is_default"
                            value="1"
                            @checked($method->is_default)
                            class="rounded border-slate-300"
                        >

                        Default payout method
                    </label>


                    
                    @php
                        $payoutUsage = json_decode(
                            $method->usage_contexts ?? '[]',
                            true
                        ) ?: [];
                    @endphp

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">

                        <div class="text-sm font-black text-slate-900">
                            Allowed Payout Uses
                        </div>

                        <div class="mt-3 space-y-2">

                            <label class="flex items-center gap-2 text-xs font-bold text-slate-700">
                                <input
                                    type="checkbox"
                                    name="usage_contexts[]"
                                    value="user_payout"
                                    @checked(in_array(
                                        'user_payout',
                                        $payoutUsage,
                                        true
                                    ))
                                >
                                User Payout
                            </label>

                            <label class="flex items-center gap-2 text-xs font-bold text-slate-700">
                                <input
                                    type="checkbox"
                                    name="usage_contexts[]"
                                    value="developer_payout"
                                    @checked(in_array(
                                        'developer_payout',
                                        $payoutUsage,
                                        true
                                    ))
                                >
                                Developer / Off-server Payout
                            </label>

                            <label class="flex items-center gap-2 text-xs font-bold text-slate-700">
                                <input
                                    type="checkbox"
                                    name="usage_contexts[]"
                                    value="marketplace_payout"
                                    @checked(in_array(
                                        'marketplace_payout',
                                        $payoutUsage,
                                        true
                                    ))
                                >
                                Marketplace / Seller Payout
                            </label>

                        </div>
                    </div>


                    <div
                        x-data="{
                            enabled: @js(
                                (bool) (
                                    $method->markdown_enabled
                                        ?? false
                                )
                            ),
                            type: @js(
                                $method->markdown_type
                                    ?? 'percentage'
                            )
                        }"
                        class="rounded-2xl border border-slate-200 bg-slate-50 p-4"
                    >

                        <div class="flex items-center justify-between gap-3">

                            <div>
                                <div class="text-sm font-black text-slate-900">
                                    Payout Markdown
                                </div>

                                <div class="mt-1 text-[11px] text-slate-500">
                                    Applied after payout conversion.
                                </div>
                            </div>

                            <label class="flex items-center gap-2 text-xs font-black text-slate-600">
                                <input
                                    type="checkbox"
                                    name="markdown_enabled"
                                    value="1"
                                    x-model="enabled"
                                    @checked(
                                        $method->markdown_enabled
                                            ?? false
                                    )
                                >
                                Enable
                            </label>

                        </div>

                        <div
                            x-show="enabled"
                            x-cloak
                            class="mt-4 space-y-3"
                        >

                            <select
                                name="markdown_type"
                                x-model="type"
                                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm"
                            >
                                <option value="percentage">
                                    Percentage
                                </option>

                                <option value="fixed">
                                    Fixed
                                </option>
                            </select>

                            <input
                                type="number"
                                name="markdown_value"
                                value="{{ $method->markdown_value ?? 0 }}"
                                min="0"
                                step="0.00000001"
                                class="w-full rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm"
                            >

                        </div>
                    </div>


<button
                        type="submit"
                        class="w-full rounded-xl bg-slate-900 px-4 py-3 text-sm font-black text-white hover:bg-slate-800"
                    >
                        Save {{ $method->name }}
                    </button>

                </div>

            </form>

        @empty

            <div class="lg:col-span-3 rounded-3xl border border-dashed border-slate-300 bg-white p-12 text-center text-sm text-slate-500">
                No automatic payout methods configured.
            </div>

        @endforelse

    </div>

</div>
@endsection
