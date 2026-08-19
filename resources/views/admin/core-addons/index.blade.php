@extends('admin.layouts.app')

@section('content')

<div class="min-h-full bg-slate-50 px-6 py-8 lg:px-8">

    {{-- HEADER --}}
    <div class="mb-8 flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">

        <div>
            <div class="mb-2 text-xs font-bold uppercase tracking-[0.18em] text-blue-600">
                Esubiz Core Commerce
            </div>

            <h1 class="text-3xl font-bold tracking-tight text-slate-900">
                Add-ons & Bundles
            </h1>

            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                Configure the Core extensions available across SaaS and off-server deployments.
                Control capabilities, allocations, commercial models and availability from one central registry.
            </p>
        </div>

        <div class="flex gap-3">
            <button
                type="button"
                class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">
                + Add Add-on
            </button>

            <button
                type="button"
                class="rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50">
                + Create Bundle
            </button>
        </div>

    </div>


    {{-- SUMMARY --}}
    <div class="mb-8 grid gap-4 md:grid-cols-3">

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                Core Add-ons
            </div>
            <div class="mt-2 text-3xl font-bold text-slate-900">
                {{ $addons->count() }}
            </div>
            <div class="mt-1 text-sm text-slate-500">
                Configured extensions
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                Bundles
            </div>
            <div class="mt-2 text-3xl font-bold text-slate-900">
                {{ $bundles->count() }}
            </div>
            <div class="mt-1 text-sm text-slate-500">
                Configured product bundles
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="text-xs font-semibold uppercase tracking-wider text-slate-400">
                Commercial Model
            </div>
            <div class="mt-2 text-lg font-bold text-slate-900">
                SaaS + Off-server
            </div>
            <div class="mt-1 text-sm text-slate-500">
                Rental/subscription and one-off licensing
            </div>
        </div>

    </div>


    {{-- ADD-ONS --}}
    <div class="mb-8 rounded-3xl border border-slate-200 bg-white shadow-sm">

        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">
            <div>
                <h2 class="text-lg font-bold text-slate-900">
                    Core Add-ons
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Extensions that unlock additional Core capabilities.
                </p>
            </div>

            <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
                {{ $addons->count() }} configured
            </span>
        </div>

        <div class="p-6">

            @forelse($addons as $addon)

                <div class="mb-3 rounded-2xl border border-slate-200 bg-slate-50 p-5 transition hover:border-blue-200 hover:bg-white hover:shadow-sm">

                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

                        <div>
                            <div class="flex flex-wrap items-center gap-2">

                                <h3 class="font-semibold text-slate-900">
                                    {{ $addon->name }}
                                </h3>

                                <span class="rounded-full bg-slate-200 px-2.5 py-1 text-[11px] font-semibold text-slate-600">
                                    {{ $addon->entitlement_type }}
                                </span>

                                @if($addon->is_active)
                                    <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700">
                                        Active
                                    </span>
                                @else
                                    <span class="rounded-full bg-slate-200 px-2.5 py-1 text-[11px] font-semibold text-slate-500">
                                        Inactive
                                    </span>
                                @endif

                            </div>

                            <div class="mt-1 text-xs font-medium text-slate-400">
                                {{ $addon->key }}
                            </div>

                            @if($addon->parent_capability)
                                <div class="mt-3 text-sm text-slate-500">
                                    Extends:
                                    <span class="font-semibold text-slate-700">
                                        {{ $addon->parent_capability }}
                                    </span>
                                </div>
                            @endif
                        </div>


                        <div class="flex flex-wrap items-center gap-2">

                            @if($addon->saas_available)
                                <span class="rounded-full bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700">
                                    SaaS Rental
                                </span>
                            @endif

                            @if($addon->off_server_available)
                                <span class="rounded-full bg-violet-50 px-3 py-1.5 text-xs font-semibold text-violet-700">
                                    Off-server License
                                </span>
                            @endif

                            <button
                                type="button"
                                class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                Configure
                            </button>

                        </div>

                    </div>

                </div>

            @empty

                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center">
                    <div class="text-sm font-semibold text-slate-700">
                        No Core add-ons configured
                    </div>

                    <p class="mt-1 text-sm text-slate-500">
                        Add-ons created by Esubiz Admin will appear here.
                    </p>
                </div>

            @endforelse

        </div>

    </div>


    {{-- BUNDLES --}}
    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">

        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">

            <div>
                <h2 class="text-lg font-bold text-slate-900">
                    Add-on Bundles
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Package multiple Core add-ons into a single commercial product.
                </p>
            </div>

            <span class="rounded-full bg-violet-50 px-3 py-1 text-xs font-semibold text-violet-700">
                {{ $bundles->count() }} configured
            </span>

        </div>

        <div class="p-6">

            @forelse($bundles as $bundle)

                <div class="mb-3 rounded-2xl border border-slate-200 bg-slate-50 p-5">

                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">

                        <div>
                            <h3 class="font-semibold text-slate-900">
                                {{ $bundle->name }}
                            </h3>

                            <div class="mt-1 text-xs font-medium text-slate-400">
                                {{ $bundle->key }}
                            </div>
                        </div>

                        <div class="flex items-center gap-2">

                            @if($bundle->saas_available)
                                <span class="rounded-full bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700">
                                    SaaS
                                </span>
                            @endif

                            @if($bundle->off_server_available)
                                <span class="rounded-full bg-violet-50 px-3 py-1.5 text-xs font-semibold text-violet-700">
                                    Off-server
                                </span>
                            @endif

                            <button
                                type="button"
                                class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                                Configure
                            </button>

                        </div>

                    </div>

                </div>

            @empty

                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center">

                    <div class="text-sm font-semibold text-slate-700">
                        No bundles configured
                    </div>

                    <p class="mt-1 text-sm text-slate-500">
                        Create bundles when you are ready to package multiple add-ons.
                    </p>

                </div>

            @endforelse

        </div>

    </div>

</div>

@endsection
