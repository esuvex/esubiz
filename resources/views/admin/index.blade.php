@extends('admin.layouts.app')

@section('title', 'Platform Console')

@section('content')

<div class="max-w-full overflow-x-hidden space-y-8">

    <!-- Header -->

    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">

        <div>

            <h1 class="text-5xl font-bold text-slate-800">
                Platform Console
            </h1>

            <p class="mt-3 text-xl text-slate-500">
                Welcome back, manage the entire Esubiz ecosystem.
            </p>

        </div>

        <div class="flex gap-4">

            <a href="{{ route('websites.create') }}"
               class="inline-flex items-center px-8 py-4 rounded-2xl bg-blue-600 text-white font-semibold shadow hover:bg-blue-700 transition">

                + Create Website

            </a>

            <button class="px-8 py-4 rounded-2xl bg-white border shadow font-semibold hover:bg-slate-50">

                View Reports

            </button>

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

        <div class="rounded-3xl bg-gradient-to-r from-emerald-600 to-green-400 text-white p-8 shadow-xl">

            <p class="text-2xl opacity-90">
                Developer Accounts
            </p>

            <h2 class="text-6xl font-bold mt-5">
                {{ number_format($developerAccounts) }}
            </h2>

            <p class="mt-8 text-xl opacity-90">
                Registered developers
            </p>

        </div>

        <div class="rounded-3xl bg-gradient-to-r from-orange-500 to-amber-400 text-white p-8 shadow-xl">

            <p class="text-2xl opacity-90">
                Revenue
            </p>

            <h2 class="text-6xl font-bold mt-5">
                ₦{{ number_format($currentMonthRevenue, 2) }}
            </h2>

            <p class="mt-8 text-xl opacity-90">
                Processed revenue this month
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

            </div>

        </div>

    </div>

</div>

@endsection
