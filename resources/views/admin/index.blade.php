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
                12,540
            </h2>

            <p class="mt-8 text-xl opacity-90">
                +8.2% this month
            </p>

        </div>

        <div class="rounded-3xl bg-gradient-to-r from-emerald-600 to-green-400 text-white p-8 shadow-xl">

            <p class="text-2xl opacity-90">
                Creator Accounts
            </p>

            <h2 class="text-6xl font-bold mt-5">
                2,184
            </h2>

            <p class="mt-8 text-xl opacity-90">
                156 active today
            </p>

        </div>

        <div class="rounded-3xl bg-gradient-to-r from-orange-500 to-amber-400 text-white p-8 shadow-xl">

            <p class="text-2xl opacity-90">
                Revenue
            </p>

            <h2 class="text-6xl font-bold mt-5">
                ₦45.2M
            </h2>

            <p class="mt-8 text-xl opacity-90">
                Current month
            </p>

        </div>

        <div class="rounded-3xl bg-gradient-to-r from-violet-600 to-fuchsia-500 text-white p-8 shadow-xl">

            <p class="text-2xl opacity-90">
                Marketplace
            </p>

            <h2 class="text-6xl font-bold mt-5">
                628
            </h2>

            <p class="mt-8 text-xl opacity-90">
                Products
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

            <div class="mt-8 h-96 rounded-2xl border-2 border-dashed border-slate-300 flex items-center justify-center text-slate-400">

                Chart Coming Next

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
