@extends('admin.layouts.app')

@section('title', 'Platform Console')

@section('content')

<div class="max-w-full overflow-x-hidden space-y-8">

    <!-- Header -->

    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">

        <div>

            

            

        </div>
</div>



    <!-- Stats -->

    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">

        <div class="rounded-3xl bg-gradient-to-r from-blue-600 to-blue-400 text-white p-8 shadow-xl">

            <p class="text-2xl opacity-90">Total Users</p>

            <h2 class="text-6xl font-bold mt-5">
                {{ number_format($totalUsers) }}
            </h2>

            <p class="mt-8 text-xl opacity-90">
                Registered accounts
            </p>

        </div>

        
<div class="rounded-3xl bg-gradient-to-r from-orange-500 to-amber-400 text-white p-8 shadow-xl">

            <p class="text-2xl opacity-90">
                Revenue
            </p>

            <h2 class="font-bold mt-5 text-2xl tracking-tight break-words">
                ₦{{ number_format($currentMonthRevenue, 2) }}
            </h2>

            <p class="mt-8 text-xl opacity-90">
                Processed revenue this month
            </p>

        </div>


        <div class="rounded-3xl p-6 shadow-xl"
     style="background: linear-gradient(135deg, #10b981 0%, #16a34a 100%);">

    <p class=" font-semibold text-white text-2xl font-semibold text-white">
        Expenses
    </p>

    <p class="mt-3 text-2xl font-bold tracking-tight text-white break-words">
        ₦{{ number_format($currentMonthExpenses, 2) }}
    </p>

    <p class="mt-2  text-white/90  text-white/90 text-base font-medium text-white">
        Processed this month
    </p>

</div>

        <div class="rounded-3xl bg-gradient-to-r from-violet-600 to-fuchsia-500 text-white p-8 shadow-xl">

            <p class="text-2xl opacity-90">
                Active Websites
            </p>

            <h2 class="text-6xl font-bold mt-5">
                {{ number_format($activeWebsites) }}
            </h2>

            <p class="mt-8 text-xl opacity-90">
                Of {{ number_format($totalWebsites) }} total websites
            </p>

        </div>

    </div>



    <!-- Bottom -->

    <div class="grid gap-6 xl:grid-cols-3">

        <div class="xl:col-span-2 bg-white rounded-3xl shadow p-8">

            <div class="flex justify-between items-center">

                <h2 class="text-3xl font-bold">
                    Revenue Overview
                </h2>

                <span class="text-slate-500">
                    Last 30 Days
                </span>

            </div>

            <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-4">

                <div class="rounded-2xl bg-slate-50 p-6">
                    <p class="text-sm text-slate-500">Total Processed Revenue</p>
                    <p class="mt-2 text-3xl font-bold text-slate-800">
                        ₦{{ number_format($totalRevenue, 2) }}
                    </p>
                </div>

                <div class="rounded-2xl bg-slate-50 p-6">
                    <p class="text-sm text-slate-500">API Requests</p>
                    <p class="mt-2 text-3xl font-bold text-slate-800">
                        {{ number_format($apiRequests) }}
                    </p>
                </div>

                <div class="rounded-2xl bg-slate-50 p-6">
                    <p class="text-sm text-slate-500">Successful API Requests</p>
                    <p class="mt-2 text-3xl font-bold text-slate-800">
                        {{ number_format($successfulApiRequests) }}
                    </p>
                </div>

                <div class="rounded-2xl bg-slate-50 p-6">
                    <p class="text-sm text-slate-500">Marketplace Revenue</p>
                    <p class="mt-2 text-3xl font-bold text-slate-800">
                        ₦{{ number_format($marketplaceRevenue, 2) }}
                    </p>
                </div>

            </div>

        </div>

        <div class="bg-white rounded-3xl shadow p-8">

            <h2 class="text-3xl font-bold">
                Quick Actions
            </h2>

            <div class="mt-8 space-y-4">

                <a href="{{ route('websites.create') }}"
                   class="block w-full rounded-2xl bg-slate-100 py-5 text-center font-medium hover:bg-slate-200 transition">

                    Create Website

                </a>

                <button class="w-full rounded-2xl bg-slate-100 py-5 hover:bg-slate-200">

                    Add Creator

                </button>

                <button class="w-full rounded-2xl bg-slate-100 py-5 hover:bg-slate-200">

                    Marketplace

                </button>

                <button class="w-full rounded-2xl bg-slate-100 py-5 hover:bg-slate-200">

                    View Reports

                </button>

            
<a
    href="{{ route('admin.financial-reports') }}"
    class="block rounded-xl bg-slate-900 px-5 py-3 text-center font-semibold text-white"
>
    Financial Reports
</a>

</div>

        </div>

    </div>

</div>


<div class="mt-8 rounded-3xl bg-white border border-slate-200 p-6 shadow-sm">

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

        <div>
            <h3 class="text-2xl font-bold text-slate-900">
                Income vs Expenses
            </h3>

            <p class="text-sm text-slate-500 mt-1">
                Esubiz platform income compared with recorded expenses.
            </p>
        </div>

        <form method="GET">
            <select
                name="period"
                onchange="this.form.submit()"
                class="rounded-xl border-slate-300 text-sm"
            >
                <option value="7d" @selected($incomeExpensePeriod === '7d')>
                    Last 7 days
                </option>

                <option value="30d" @selected($incomeExpensePeriod === '30d')>
                    Last 30 days
                </option>

                <option value="3m" @selected($incomeExpensePeriod === '3m')>
                    Last 3 months
                </option>

                <option value="6m" @selected($incomeExpensePeriod === '6m')>
                    Last 6 months
                </option>

                <option value="12m" @selected($incomeExpensePeriod === '12m')>
                    Last 12 months
                </option>
            </select>
        </form>

    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">

        <div class="rounded-2xl bg-emerald-50 p-5">
            <p class="text-sm text-emerald-700">
                Income
            </p>

            <p class="text-2xl font-bold text-emerald-700 mt-2">
                ₦{{ number_format($periodIncome, 2) }}
            </p>
        </div>

        <div class="rounded-2xl bg-red-50 p-5">
            <p class="text-sm text-red-700">
                Expenses
            </p>

            <p class="text-2xl font-bold text-red-700 mt-2">
                ₦{{ number_format($periodExpenses, 2) }}
            </p>
        </div>

        <div class="rounded-2xl bg-slate-100 p-5">
            <p class="text-sm text-slate-600">
                Net
            </p>

            <p class="text-2xl font-bold text-slate-900 mt-2">
                ₦{{ number_format($periodNet, 2) }}
            </p>
        </div>

    </div>

    @php
        $chartMax = max(
            collect($incomeExpenseChart)->max('income'),
            collect($incomeExpenseChart)->max('expenses'),
            1
        );
    @endphp

    <div class="mt-8 overflow-x-auto">

        <div
            class="flex items-end gap-2 min-w-max"
            style="height: 280px;"
        >

            @foreach($incomeExpenseChart as $point)

                @php
                    $incomeHeight =
                        ($point['income'] / $chartMax) * 220;

                    $expenseHeight =
                        ($point['expenses'] / $chartMax) * 220;
                @endphp

                <div class="flex flex-col items-center justify-end h-full">

                    <div class="flex items-end gap-1 h-[230px]">

                        <div
                            title="Income: ₦{{ number_format($point['income'], 2) }}"
                            class="w-3 bg-emerald-500 rounded-t"
                            style="height: {{ max($incomeHeight, $point['income'] > 0 ? 3 : 0) }}px;"
                        ></div>

                        <div
                            title="Expenses: ₦{{ number_format($point['expenses'], 2) }}"
                            class="w-3 bg-red-500 rounded-t"
                            style="height: {{ max($expenseHeight, $point['expenses'] > 0 ? 3 : 0) }}px;"
                        ></div>

                    </div>

                    <span class="text-[9px] text-slate-400 mt-2 whitespace-nowrap">
                        {{ $point['label'] }}
                    </span>

                </div>

            @endforeach

        </div>

    </div>

    <div class="flex items-center gap-5 mt-5 text-xs text-slate-500">

        <span class="flex items-center gap-2">
            <span class="w-3 h-3 rounded bg-emerald-500"></span>
            Income
        </span>

        <span class="flex items-center gap-2">
            <span class="w-3 h-3 rounded bg-red-500"></span>
            Expenses
        </span>

    </div>

</div>

@endsection
