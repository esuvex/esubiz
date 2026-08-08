@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')

@php
    $draft = $draft ?? null;

    $currentStep = $draft?->current_step ?? 0;
    $totalSteps = 9;

    $resumeStep = $currentStep > 0
        ? min($currentStep + 1, $totalSteps)
        : 1;

    $continueRoute = null;

    if ($draft) {
        $continueRoutes = [
            1 => 'websites.theme',
            2 => 'websites.information',
            3 => 'websites.plan',
            4 => 'websites.domain',
            5 => 'websites.address',
            6 => 'websites.administrator',
            7 => 'websites.review',
            8 => 'websites.review',
            9 => 'websites.review',
        ];

        $continueRoute = $continueRoutes[$currentStep] ?? 'websites.theme';
    }

    $draftName = $draft?->name ?? 'New Business Website';

    $progress = $resumeStep > 0
        ? min(100, round(($resumeStep / $totalSteps) * 100))
        : 0;
@endphp

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
                    Build, launch and manage all your business websites from one dashboard.
                    Continue existing drafts or launch a brand-new website in just a few steps.
                </p>

            </div>

            <div class="flex-shrink-0">

                <a
                    href="{{ route('websites.create') }}"
                    class="inline-flex items-center rounded-2xl bg-blue-600 px-8 py-5 text-lg font-semibold text-white shadow-xl transition duration-300 hover:scale-105 hover:bg-blue-700"
                >
                    + Create New Website
                </a>

            </div>

        </div>

    </div>

    <!-- ======================================= -->
    <!-- CONTINUE SETUP -->
    <!-- ======================================= -->

    @if($draft)

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
                        {{ $draftName }} has been automatically saved.
                    </p>

                </div>

                <div class="text-right">

                    <p class="text-sm text-slate-500">
                        Continue from
                    </p>

                    <h3 class="mt-2 text-4xl font-bold text-slate-900">
                        Step {{ $resumeStep }} of {{ $totalSteps }}
                    </h3>

                    <div class="mt-4 h-2 w-64 overflow-hidden rounded-full bg-amber-200">

                        <div
                            class="h-full rounded-full bg-blue-600 transition-all duration-500"
                            style="width: {{ $progress }}%;"
                        ></div>

                    </div>

                    <a
                        href="{{ route($continueRoute, $draft) }}"
                        class="mt-6 inline-flex rounded-2xl bg-blue-600 px-8 py-4 font-semibold text-white transition hover:bg-blue-700"
                    >
                        Continue →
                    </a>

                </div>

            </div>

        </div>

    @endif

    <!-- ======================================= -->
    <!-- STATS -->
    <!-- ======================================= -->

    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
    <!-- ======================================= -->
    <!-- STATS -->
    <!-- ======================================= -->

    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">

        <div class="rounded-3xl bg-white p-8 shadow">

            <p class="text-slate-500">
                Websites
            </p>

            <h2 class="mt-4 text-4xl font-bold">
                {{ auth()->user()->websites()->where('status', '!=', 'draft')->count() }}
            </h2>

        </div>

        <div class="rounded-3xl bg-white p-8 shadow">

            <p class="text-slate-500">
                Drafts
            </p>

            <h2 class="mt-4 text-4xl font-bold">
                {{ auth()->user()->websites()->where('status', 'draft')->count() }}
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

                <a
                    href="{{ route('websites.create') }}"
                    class="rounded-xl bg-blue-600 px-5 py-3 font-semibold text-white hover:bg-blue-700"
                >
                    + New Website
                </a>

            </div>

            <div class="divide-y">

                @if($draft)

                    <div class="flex items-center justify-between px-8 py-6">

                        <div>

                            <h3 class="text-xl font-semibold">
                                {{ $draftName }}
                            </h3>

                            <p class="mt-1 text-slate-500">
                                Draft • Continue from Step {{ $resumeStep }} of {{ $totalSteps }}
                            </p>

                        </div>

                        <a
                            href="{{ route($continueRoute, $draft) }}"
                            class="rounded-xl bg-amber-500 px-5 py-3 font-semibold text-white hover:bg-amber-600"
                        >
                            Continue
                        </a>

                    </div>

                @else

                    <div class="px-8 py-10 text-center">

                        <p class="text-slate-500">
                            You don't have any website drafts yet.
                        </p>

                        <a
                            href="{{ route('websites.create') }}"
                            class="mt-5 inline-flex rounded-xl bg-blue-600 px-6 py-3 font-semibold text-white"
                        >
                            Create Your First Website
                        </a>

                    </div>

                @endif

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

                <a
                    href="{{ route('websites.create') }}"
                    class="block rounded-2xl border p-5 hover:border-blue-500"
                >
                    🌐 Create Website
                </a>

                <a
                    href="#"
                    class="block rounded-2xl border p-5"
                >
                    🌍 Connect Domain
                </a>

                <a
                    href="#"
                    class="block rounded-2xl border p-5"
                >
                    📦 Upgrade Plan
                </a>

                <a
                    href="#"
                    class="block rounded-2xl border p-5"
                >
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

            @if($draft)

                <div class="flex items-center justify-between px-8 py-5">

                    <div>

                        <h3 class="font-semibold">
                            Website draft saved
                        </h3>

                        <p class="text-slate-500">
                            {{ $draftName }} • Continue from Step {{ $resumeStep }} of {{ $totalSteps }}
                        </p>

                    </div>

                    <span class="text-sm text-slate-400">
                        {{ $draft->last_saved_at?->diffForHumans() ?? 'Recently' }}
                    </span>

                </div>

            @endif

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
