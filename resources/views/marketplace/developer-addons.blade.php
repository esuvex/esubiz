@extends('admin.layouts.app')

@section('content')
<div class="min-h-screen bg-slate-50">
    <div class="mx-auto max-w-7xl px-6 py-8">

        <div class="mb-8">
            <h1 class="text-2xl font-black text-slate-900">
                Add-ons
            </h1>
            <p class="mt-2 text-sm text-slate-500">
                Choose the Esubiz environment you want to manage add-ons for.
            </p>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">

            <a
                href="{{ route('marketplace.addons') }}"
                class="group relative overflow-hidden rounded-3xl border border-slate-200 bg-white p-8 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-blue-200 hover:shadow-xl"
            >
                <div class="flex items-start justify-between gap-6">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3v18m9-9H3" />
                        </svg>
                    </div>

                    <span class="rounded-full bg-blue-50 px-3 py-1 text-[10px] font-black uppercase tracking-wider text-blue-700">
                        SaaS
                    </span>
                </div>

                <div class="mt-8">
                    <h2 class="text-xl font-black text-slate-900">
                        SaaS Add-ons
                    </h2>
                    <p class="mt-3 max-w-lg text-sm leading-6 text-slate-500">
                        Browse and purchase add-ons and bundles for websites hosted and managed on Esubiz.
                    </p>
                </div>

                <div class="mt-8 flex items-center gap-2 text-sm font-black text-blue-600">
                    Open SaaS Add-ons
                    <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </a>

            <a
                href="/developer/marketplace/addons/off-server"
                class="group relative overflow-hidden rounded-3xl border border-slate-200 bg-white p-8 shadow-sm transition duration-300 hover:-translate-y-1 hover:border-blue-200 hover:shadow-xl"
            >
                <div class="flex items-start justify-between gap-6">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-700">
                        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </div>

                    <span class="rounded-full bg-slate-100 px-3 py-1 text-[10px] font-black uppercase tracking-wider text-slate-700">
                        Off-server
                    </span>
                </div>

                <div class="mt-8">
                    <h2 class="text-xl font-black text-slate-900">
                        Off-server Add-ons
                    </h2>
                    <p class="mt-3 max-w-lg text-sm leading-6 text-slate-500">
                        Browse add-ons and bundles available for Esubiz Core installations hosted outside Esubiz servers.
                    </p>
                </div>

                <div class="mt-8 flex items-center gap-2 text-sm font-black text-blue-600">
                    Open Off-server Add-ons
                    <svg class="h-4 w-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </a>

        </div>
    </div>
</div>
@endsection
