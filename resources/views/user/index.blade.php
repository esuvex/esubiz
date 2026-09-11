@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')


@include('partials.central-dashboard-notices')
@php
    $draft = $draft ?? null;

    $currentStep = $draft?->current_step ?? 0;
    $totalSteps = 5;

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
    <!-- FAILED DEPLOYMENTS -->
    <!-- ======================================= -->

    @if($failedWebsites->count())

        <div class="space-y-4">

            @foreach($failedWebsites as $failedWebsite)

                <div class="rounded-3xl border border-red-200 bg-red-50 p-8">

                    <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">

                        <div>

                            <span class="rounded-full bg-red-200 px-4 py-2 text-xs font-bold uppercase tracking-wider text-red-800">
                                Deployment Failed
                            </span>

                            <h2 class="mt-5 text-3xl font-bold text-slate-900">
                                {{ $failedWebsite->name }}
                            </h2>

                            <p class="mt-3 max-w-2xl text-slate-600">
                                Your website setup is complete, but the deployment was not successful.
                                Your configuration has been preserved. You can retry the deployment from Step 5.
                            </p>

                        </div>

                        <div class="shrink-0">

                            <a
                                href="{{ route('websites.review', $failedWebsite) }}"
                                class="inline-flex items-center rounded-2xl bg-red-600 px-8 py-4 font-semibold text-white shadow-lg transition hover:bg-red-700"
                            >
                                Redeploy Website
                            </a>

                        </div>

                    </div>

                </div>

            @endforeach

        </div>

    @endif

    <!-- ======================================= -->
    <!-- STATS -->
    <!-- ======================================= -->

    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">

        <div class="rounded-3xl bg-white p-8 shadow">

            <p class="text-slate-500">
                Websites
            </p>

            <h2 class="mt-4 text-4xl font-bold">
                {{ $websiteCount }}
            </h2>

        </div>

        <div class="rounded-3xl bg-white p-8 shadow">

            <p class="text-slate-500">
                Drafts
            </p>

            <h2 class="mt-4 text-4xl font-bold">
                {{ $draftCount }}
            </h2>

        </div>

        <div class="rounded-3xl bg-white p-8 shadow">

            <p class="text-slate-500">
                Subscriptions
            </p>

            <h2 class="mt-4 text-4xl font-bold">
                {{ $subscriptionCount }}
            </h2>

        </div>

        <div class="rounded-3xl bg-white p-8 shadow">

            <p class="text-slate-500">
                Wallet
            </p>

            <h2 class="mt-4 text-3xl font-bold">
                ₦{{ number_format($walletBalance, 2) }}
            </h2>

        </div>

    </div>

    <!-- ======================================= -->
    <!-- ======================================= -->
    <!-- QUICK ACTIONS + RECENT ACTIVITY -->
    <!-- ======================================= -->

    <div class="grid gap-8 lg:grid-cols-2">

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
                    class="flex items-center rounded-2xl border p-5 transition hover:border-blue-500 hover:bg-slate-50"
                >
                    <span class="mr-4 text-xl">🌐</span>
                    <span class="font-medium">Create Website</span>
                </a>

                <a
                    href="#"
                    class="flex items-center rounded-2xl border p-5 transition hover:border-blue-500 hover:bg-slate-50"
                >
                    <span class="mr-4 text-xl">🌍</span>
                    <span class="font-medium">Connect Domain</span>
                </a>

                <a
                    href="#"
                    class="flex items-center rounded-2xl border p-5 transition hover:border-blue-500 hover:bg-slate-50"
                >
                    <span class="mr-4 text-xl">📦</span>
                    <span class="font-medium">Upgrade Plan</span>
                </a>

                <a
                    href="#"
                    class="flex items-center rounded-2xl border p-5 transition hover:border-blue-500 hover:bg-slate-50"
                >
                    <span class="mr-4 text-xl">💬</span>
                    <span class="font-medium">Contact Support</span>
                </a>

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

                @else

                    <div class="px-8 py-8 text-slate-500">
                        No recent activity.
                    </div>

                @endif

            </div>

        </div>

    </div>

@endsection
