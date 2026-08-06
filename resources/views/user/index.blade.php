@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')

<div class="space-y-8">

     <!-- ======================================= -->
<!-- HERO -->
<!-- ======================================= -->

<div class="rounded-3xl bg-gradient-to-br from-slate-950 via-slate-900 to-blue-900 p-10 text-white shadow-2xl">

    <div class="flex flex-col gap-8 lg:flex-row lg:items-center lg:justify-between">

        <div>

            <p class="text-blue-300 uppercase tracking-[0.3em] text-sm font-semibold">
                ESUBIZ WEBSITE BUILDER
            </p>

            <h1 class="mt-4 text-5xl font-bold leading-tight text-white">
                Welcome back, {{ auth()->user()->name }}
            </h1>

            <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-300">
                Build, deploy and manage all your business websites from one dashboard.
                Continue existing drafts or launch a brand-new website in just a few steps.
            </p>

        </div>

        <div class="flex-shrink-0">

            <a href="{{ route('websites.create') }}"
               class="inline-flex items-center rounded-2xl bg-blue-600 px-8 py-5 text-lg font-semibold text-white shadow-xl transition duration-300 hover:scale-105 hover:bg-blue-700">

                + Create New Website

            </a>

        </div>

    </div>

</div>

    <!-- ======================================= -->
    <!-- CONTINUE SETUP -->
    <!-- ======================================= -->

    <div class="rounded-3xl border border-amber-200 bg-amber-50 p-8">

        <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">

            <div>

                <span class="rounded-full bg-amber-200 px-4 py-2 text-xs font-bold uppercase tracking-wider text-amber-900">

                    Draft Website

                </span>

                <h2 class="mt-5 text-3xl font-bold text-slate-900">

                    Continue Website Setup

                </h2>

                <p class="mt-3 text-slate-600">

                    Your website draft has been automatically saved.

                </p>

            </div>

            <div class="text-right">

                <p class="text-sm text-slate-500">

                    Progress

                </p>

                <h3 class="mt-2 text-4xl font-bold">

                    Step 4 of 8

                </h3>

                <a href="{{ route('websites.create') }}"
                   class="mt-6 inline-flex rounded-2xl bg-blue-600 px-8 py-4 font-semibold text-white hover:bg-blue-700">

                    Continue →

                </a>

            </div>

        </div>

    </div>

    <!-- ======================================= -->
    <!-- STATS -->
    <!-- ======================================= -->

    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">

        <div class="rounded-3xl bg-white p-8 shadow">

            <p class="text-slate-500">

                Websites

            </p>

            <h2 class="mt-4 text-4xl font-bold">

                5

            </h2>

        </div>

        <div class="rounded-3xl bg-white p-8 shadow">

            <p class="text-slate-500">

                Drafts

            </p>

            <h2 class="mt-4 text-4xl font-bold">

                1

            </h2>

        </div>

        <div class="rounded-3xl bg-white p-8 shadow">

            <p class="text-slate-500">

                Subscription

            </p>

            <h2 class="mt-4 text-2xl font-bold">

                Pro Plan

            </h2>

        </div>

        <div class="rounded-3xl bg-white p-8 shadow">

            <p class="text-slate-500">

                Wallet

            </p>

            <h2 class="mt-4 text-3xl font-bold">

                ₦25,000

            </h2>

        </div>

    </div>
    <!-- ======================================= -->
    <!-- MY WEBSITES -->
    <!-- ======================================= -->

    <div class="grid gap-8 lg:grid-cols-3">

        <div class="lg:col-span-2 rounded-3xl bg-white shadow">

            <div class="flex items-center justify-between border-b px-8 py-6">

                <div>

                    <h2 class="text-2xl font-bold">

                        My Websites

                    </h2>

                    <p class="mt-1 text-slate-500">

                        Manage all your business websites.

                    </p>

                </div>

                <a href="{{ route('websites.create') }}"
                   class="rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white hover:bg-blue-700">

                    + New Website

                </a>

            </div>

            <div class="divide-y">

                <div class="flex items-center justify-between px-8 py-6">

                    <div>

                        <h3 class="text-xl font-semibold">

                            Esuvex

                        </h3>

                        <p class="mt-1 text-slate-500">

                            esuvex.com

                        </p>

                    </div>

                    <span class="rounded-full bg-green-100 px-4 py-2 text-sm font-semibold text-green-700">

                        Active

                    </span>

                </div>

                <div class="flex items-center justify-between px-8 py-6">

                    <div>

                        <h3 class="text-xl font-semibold">

                            Greenwood Interior Academy

                        </h3>

                        <p class="mt-1 text-slate-500">

                            greenwood.esubiz.com

                        </p>

                    </div>

                    <span class="rounded-full bg-green-100 px-4 py-2 text-sm font-semibold text-green-700">

                        Active

                    </span>

                </div>

                <div class="flex items-center justify-between px-8 py-6">

                    <div>

                        <h3 class="text-xl font-semibold">

                            New Business Website

                        </h3>

                        <p class="mt-1 text-slate-500">

                            Draft saved automatically

                        </p>

                    </div>

                    <a href="{{ route('websites.create') }}"
                       class="rounded-xl bg-amber-500 px-5 py-3 font-semibold text-white">

                        Continue

                    </a>

                </div>

            </div>

        </div>

        <!-- ======================================= -->
        <!-- QUICK ACTIONS -->
        <!-- ======================================= -->

        <div class="rounded-3xl bg-white p-8 shadow">

            <h2 class="text-2xl font-bold">

                Quick Actions

            </h2>

            <div class="mt-8 space-y-4">

                <a href="{{ route('websites.create') }}"
                   class="block rounded-2xl border p-5 hover:border-blue-500">

                    🌐 Create Website

                </a>

                <a href="#"
                   class="block rounded-2xl border p-5">

                    🌍 Connect Domain

                </a>

                <a href="#"
                   class="block rounded-2xl border p-5">

                    📦 Upgrade Plan

                </a>

                <a href="#"
                   class="block rounded-2xl border p-5">

                    💬 Contact Support

                </a>

            </div>

        </div>

    </div>
    <!-- ======================================= -->
    <!-- RECENT ACTIVITY -->
    <!-- ======================================= -->

    <div class="rounded-3xl bg-white shadow">

        <div class="border-b px-8 py-6">

            <h2 class="text-2xl font-bold">

                Recent Activity

            </h2>

        </div>

        <div class="divide-y">

            <div class="flex items-center justify-between px-8 py-5">

                <div>

                    <h3 class="font-semibold">

                        Website draft saved

                    </h3>

                    <p class="text-slate-500">

                        New Business Website • Step 4 of 8

                    </p>

                </div>

                <span class="text-sm text-slate-400">

                    Just now

                </span>

            </div>

            <div class="flex items-center justify-between px-8 py-5">

                <div>

                    <h3 class="font-semibold">

                        SSL Certificate Installed

                    </h3>

                    <p class="text-slate-500">

                        Esuvex

                    </p>

                </div>

                <span class="text-sm text-slate-400">

                    Today

                </span>

            </div>

            <div class="flex items-center justify-between px-8 py-5">

                <div>

                    <h3 class="font-semibold">

                        CRM Activated

                    </h3>

                    <p class="text-slate-500">

                        Greenwood Interior Academy

                    </p>

                </div>

                <span class="text-sm text-slate-400">

                    Yesterday

                </span>

            </div>

        </div>

    </div>

</div>

@endsection
