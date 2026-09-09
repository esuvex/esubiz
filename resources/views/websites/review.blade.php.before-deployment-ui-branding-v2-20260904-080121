@extends('admin.layouts.app')

@section('title', 'Review & Deploy')

@section('content')

@php
    $step = 4;
    $steps = 5;

    $wizard = $wizard ?? [];

    $websiteType = $wizard['type'] ?? $website->type ?? '—';

    $websiteName = $wizard['name'] ?? $website->name ?? '—';

    $subdomain = $wizard['subdomain'] ?? $website->subdomain ?? null;
    $domain = $wizard['domain'] ?? $website->domain ?? null;

    $plan = $wizard['plan'] ?? '—';

    $adminName = $wizard['admin_name'] ?? '—';
    $adminEmail = $wizard['admin_email'] ?? '—';
    $adminPhone = $wizard['admin_phone'] ?? '—';
    $adminPassword = $wizard['password'] ?? '—';
@endphp

<form method="POST" action="{{ route('websites.deploy', $website) }}">

    @csrf

    <div class="rounded-3xl bg-slate-100 p-8">

        {{-- Header --}}

        <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">

            <div>

                <h1 class="text-4xl font-bold text-slate-900">
                    Review Your Website
                </h1>

                <p class="mt-3 text-slate-500">
                    Check your website details and administrator account before deployment.
                </p>

            </div>

            <div class="rounded-2xl bg-white px-6 py-5 shadow-sm">

                <div class="text-sm text-slate-500">
                    Step
                </div>

                <div class="text-3xl font-bold text-blue-600">
                    {{ $step }} / {{ $steps }}
                </div>

            </div>

        </div>


        {{-- Progress --}}

        <div class="mt-10 flex items-center justify-between gap-2 overflow-x-auto">

            @for($i = 1; $i <= $steps; $i++)

                <div class="flex shrink-0 justify-center">

                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-full border-2 text-sm font-bold
                        {{ $i <= $step
                            ? 'border-blue-600 bg-blue-600 text-white'
                            : 'border-slate-300 bg-white text-slate-500' }}">

                        {{ $i }}

                    </div>

                </div>

            @endfor

        </div>


        {{-- ================================================================ --}}
        {{-- SECTION 1 — WEBSITE SETUP                                       --}}
        {{-- ================================================================ --}}

        <div class="mt-10 rounded-3xl bg-white p-8 shadow-sm">

            <h2 class="text-2xl font-bold text-slate-900">
                Website Setup
            </h2>

            <p class="mt-2 text-slate-500">
                These are the main settings for your new website.
            </p>

            <div class="mt-8 divide-y divide-slate-100">

                {{-- Website Type --}}

                <div class="flex flex-col gap-2 py-5 sm:flex-row sm:items-center sm:justify-between">

                    <span class="text-slate-500">
                        Website Type
                    </span>

                    <span class="font-bold capitalize text-slate-900">
                        {{ $websiteType }}
                    </span>

                </div>


                {{-- Website Name --}}

                <div class="flex flex-col gap-2 py-5 sm:flex-row sm:items-center sm:justify-between">

                    <span class="text-slate-500">
                        Website Name
                    </span>

                    <span class="font-bold text-slate-900">
                        {{ $websiteName }}
                    </span>

                </div>


                {{-- Website Domain --}}

                <div class="flex flex-col gap-2 py-5 sm:flex-row sm:items-center sm:justify-between">

                    <span class="text-slate-500">
                        Website Domain
                    </span>

                    <span class="break-all font-bold text-blue-600">

                        @if($domain)

                            https://{{ $domain }}

                        @elseif($subdomain)

                            https://{{ $subdomain }}.esubiz.com

                        @else

                            —

                        @endif

                    </span>

                </div>


                {{-- Plan --}}

                <div class="flex flex-col gap-2 py-5 sm:flex-row sm:items-center sm:justify-between">

                    <span class="text-slate-500">
                        Plan
                    </span>

                    <span class="font-bold capitalize text-slate-900">
                        {{ $plan }}
                    </span>

                </div>

            </div>

        </div>


        {{-- ================================================================ --}}
        {{-- SECTION 2 — ADMINISTRATOR ACCOUNT                              --}}
        {{-- ================================================================ --}}

        <div class="mt-6 rounded-3xl bg-white p-8 shadow-sm">

            <h2 class="text-2xl font-bold text-slate-900">
                Administrator Account
            </h2>

            <p class="mt-2 text-slate-500">
                These credentials will be used to access and manage your website.
            </p>

            <div class="mt-8 divide-y divide-slate-100">

                {{-- Name --}}

                <div class="flex flex-col gap-2 py-5 sm:flex-row sm:items-center sm:justify-between">

                    <span class="text-slate-500">
                        Full Name
                    </span>

                    <span class="font-semibold text-slate-900">
                        {{ $adminName }}
                    </span>

                </div>


                {{-- Email --}}

                <div class="flex flex-col gap-2 py-5 sm:flex-row sm:items-center sm:justify-between">

                    <span class="text-slate-500">
                        Email Address
                    </span>

                    <span class="break-all font-semibold text-slate-900">
                        {{ $adminEmail }}
                    </span>

                </div>


                {{-- Phone --}}

                <div class="flex flex-col gap-2 py-5 sm:flex-row sm:items-center sm:justify-between">

                    <span class="text-slate-500">
                        Phone Number
                    </span>

                    <span class="font-semibold text-slate-900">
                        {{ $adminPhone }}
                    </span>

                </div>


                {{-- Password --}}

                <div class="flex flex-col gap-2 py-5 sm:flex-row sm:items-center sm:justify-between">

                    <span class="text-slate-500">
                        Password
                    </span>

                    <span class="break-all font-semibold text-slate-900">
                        {{ $adminPassword }}
                    </span>

                </div>

            </div>

        </div>


        @if($errors->has('deployment'))
            <div class="mb-6 rounded-3xl border border-red-200 bg-red-50 p-6 shadow-sm">
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-red-100 text-xl font-bold text-red-700">
                        !
                    </div>

                    <div>
                        <h2 class="text-lg font-bold text-red-900">
                            Deployment failed
                        </h2>

                        <p class="mt-1 text-sm leading-6 text-red-800">
                            {{ $errors->first('deployment') }}
                        </p>

                        <p class="mt-2 text-sm text-red-700">
                            Your website has not been lost. You can review the details and click Deploy Website again to retry.
                        </p>
                    </div>
                </div>
            </div>
        @endif

        {{-- Deployment Notice --}}

        <div class="mt-10 rounded-3xl border border-blue-200 bg-blue-50 p-8">

            <h3 class="text-xl font-bold text-blue-700">
                Ready to Deploy
            </h3>

            <p class="mt-3 leading-7 text-slate-600">
                Everything is ready. Click the button below to deploy your website.
            </p>

        </div>


        {{-- Navigation --}}

        <div class="mt-10 flex flex-col gap-4 border-t border-slate-200 pt-8 sm:flex-row sm:items-center sm:justify-between">

            <a
                href="{{ route('websites.plan', $website) }}"
                class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-6 py-3 font-medium text-slate-700">

                ← Back

            </a>

            <button
                type="submit"
                class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-8 py-3 font-semibold text-white transition hover:bg-blue-700">

                🚀 Deploy Website

            </button>

        </div>

    </div>

</form>

@endsection
