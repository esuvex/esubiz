@extends('tenant.admin.layouts.app')

@section('title', 'Partners / Investors')

@section('content')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between mb-8">

        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                Partners / Investors
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Configure each Partner or Investor's investment
                share and Profit / Loss calculation basis.
            </p>
        </div>

        <a
            href="/admin/users"
            class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50"
        >
            Back to Users
        </a>

    </div>


    @if(session('success'))
        <div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif


    @if($errors->any())
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <ul class="list-disc pl-5 space-y-1">
                @foreach($errors->all() as $error)
                    <li>
                        {{ $error }}
                    </li>
                @endforeach
            </ul>
        </div>
    @endif


    <div class="mb-7 grid grid-cols-1 md:grid-cols-3 gap-4">

        <div class="rounded-2xl border border-blue-100 bg-blue-50 p-5">
            <div class="text-xs font-bold uppercase tracking-wide text-blue-700">
                Partners / Investors
            </div>

            <div class="mt-2 text-3xl font-black text-blue-950">
                {{ $partners->count() }}
            </div>

            <div class="mt-2 text-xs text-blue-700">
                Users currently assigned the Partner / Investor role.
            </div>
        </div>

        <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-5">
            <div class="text-xs font-bold uppercase tracking-wide text-emerald-700">
                Active Investments
            </div>

            <div class="mt-2 text-3xl font-black text-emerald-950">
                {{
                    $partners
                        ->filter(
                            fn ($partner) =>
                                (bool) (
                                    $partner->investment_is_active
                                    ?? false
                                )
                        )
                        ->count()
                }}
            </div>

            <div class="mt-2 text-xs text-emerald-700">
                Partner investment configurations currently active.
            </div>
        </div>

        <div class="rounded-2xl border border-amber-100 bg-amber-50 p-5">
            <div class="text-xs font-bold uppercase tracking-wide text-amber-700">
                Calculation
            </div>

            <div class="mt-2 text-lg font-black text-amber-950">
                Gross or Net
            </div>

            <div class="mt-2 text-xs text-amber-700">
                Profit / Loss basis is configured separately for each Partner.
            </div>
        </div>

    </div>


    @if($partners->isEmpty())

        <div class="rounded-2xl border border-gray-200 bg-white p-10 text-center shadow-sm">

            <h2 class="text-lg font-bold text-gray-900">
                No Partners / Investors
            </h2>

            <p class="mt-2 text-sm text-gray-500">
                Assign the Partners / Investors role to a Core user
                before configuring an investment share.
            </p>

            <a
                href="/admin/users"
                class="mt-5 inline-flex items-center justify-center rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-gray-800"
            >
                Manage Users
            </a>

        </div>

    @else

        <div class="space-y-5">

            @foreach($partners as $partner)

                @php
                    $percentage =
                        old(
                            'partner_investment_percentage',
                            $partner->investment_percentage
                            ?? '0.0000'
                        );

                    $basis =
                        old(
                            'partner_profit_basis',
                            $partner->profit_basis
                            ?? 'net'
                        );

                    $active =
                        (bool) old(
                            'partner_is_active',
                            $partner->investment_is_active
                            ?? false
                        );

                    $notes =
                        old(
                            'partner_notes',
                            $partner->notes
                            ?? ''
                        );
                @endphp

                <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">

                    <div class="flex flex-col gap-3 border-b border-gray-100 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">

                        <div class="min-w-0">

                            <h2 class="truncate text-base font-bold text-gray-900">
                                {{ $partner->name }}
                            </h2>

                            <p class="mt-1 truncate text-xs text-gray-500">
                                {{ $partner->email }}
                            </p>

                        </div>

                        <div class="flex flex-wrap items-center gap-2">

                            @if((bool) $partner->user_is_active)
                                <span class="rounded-full bg-green-50 px-3 py-1 text-xs font-bold text-green-700">
                                    Account Active
                                </span>
                            @else
                                <span class="rounded-full bg-red-50 px-3 py-1 text-xs font-bold text-red-700">
                                    Account Inactive
                                </span>
                            @endif

                            @if($active)
                                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700">
                                    Investment Active
                                </span>
                            @else
                                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-bold text-gray-600">
                                    Investment Inactive
                                </span>
                            @endif

                        </div>

                    </div>


                    <form
                        method="POST"
                        action="/admin/users/partners/{{ $partner->id }}"
                        class="p-6"
                    >
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 lg:grid-cols-4 gap-5">

                            <div>

                                <label
                                    class="block text-sm font-semibold text-gray-700"
                                >
                                    Investment Share
                                </label>

                                <div class="relative mt-2">

                                    <input
                                        type="number"
                                        name="partner_investment_percentage"
                                        min="0"
                                        max="100"
                                        step="0.0001"
                                        value="{{ $percentage }}"
                                        required
                                        class="block w-full rounded-lg border-gray-300 pr-10 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    >

                                    <span class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-sm font-bold text-gray-400">
                                        %
                                    </span>

                                </div>

                                <p class="mt-2 text-xs text-gray-500">
                                    Share applied to the selected
                                    Profit / Loss basis.
                                </p>

                            </div>


                            <div>

                                <label
                                    class="block text-sm font-semibold text-gray-700"
                                >
                                    Profit / Loss Basis
                                </label>

                                <select
                                    name="partner_profit_basis"
                                    required
                                    class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option
                                        value="gross"
                                        @selected($basis === 'gross')
                                    >
                                        Gross
                                    </option>

                                    <option
                                        value="net"
                                        @selected($basis === 'net')
                                    >
                                        Net
                                    </option>
                                </select>

                                <p class="mt-2 text-xs text-gray-500">
                                    Determines which signed business
                                    result is used for this Partner.
                                </p>

                            </div>


                            <div>

                                <label
                                    class="block text-sm font-semibold text-gray-700"
                                >
                                    Investment Status
                                </label>

                                <input
                                    type="hidden"
                                    name="partner_is_active"
                                    value="0"
                                >

                                <label class="mt-3 inline-flex items-center gap-3">

                                    <input
                                        type="checkbox"
                                        name="partner_is_active"
                                        value="1"
                                        @checked($active)
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                    >

                                    <span class="text-sm font-medium text-gray-700">
                                        Active
                                    </span>

                                </label>

                                <p class="mt-3 text-xs text-gray-500">
                                    Inactive investments do not receive
                                    new Partner share postings.
                                </p>

                            </div>


                            <div>

                                <label
                                    class="block text-sm font-semibold text-gray-700"
                                >
                                    Notes
                                </label>

                                <textarea
                                    name="partner_notes"
                                    rows="3"
                                    maxlength="2000"
                                    class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >{{ $notes }}</textarea>

                            </div>

                        </div>


                        <div class="mt-6 flex justify-end">

                            <button
                                type="submit"
                                class="inline-flex items-center justify-center rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-semibold text-white hover:bg-gray-800"
                            >
                                Save Investment Configuration
                            </button>

                        </div>

                    </form>

                </section>

            @endforeach

        </div>

    @endif

</div>

@endsection
