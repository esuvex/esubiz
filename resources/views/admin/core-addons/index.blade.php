@extends('admin.layouts.app')

@section('content')
    <div class="space-y-8">

        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-wider text-blue-400">
                    Core Commerce
                </p>
                <h1 class="mt-1 text-3xl font-semibold text-white">
                    Add-ons & Bundles
                </h1>
                <p class="mt-2 text-sm text-slate-400">
                    Configure Core extensions for SaaS rentals and off-server licenses.
                </p>
            </div>
        </div>

        @if(session('success'))
            <div class="rounded-2xl border border-emerald-500/20 bg-emerald-500/10 p-4 text-sm text-emerald-300">
                {{ session('success') }}
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-2">

            <div class="rounded-3xl border border-slate-800 bg-slate-900/80 p-6 shadow-xl">
                <h2 class="text-lg font-semibold text-white">Core Add-ons</h2>
                <p class="mt-1 text-sm text-slate-400">
                    {{ $addons->count() }} configured add-ons
                </p>

                <div class="mt-6 space-y-3">
                    @forelse($addons as $addon)
                        <div class="rounded-2xl border border-slate-800 bg-slate-950/60 p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="font-medium text-white">{{ $addon->name }}</div>
                                    <div class="text-xs text-slate-500">{{ $addon->key }}</div>
                                </div>

                                <span class="rounded-full bg-slate-800 px-3 py-1 text-xs text-slate-300">
                                    {{ $addon->entitlement_type }}
                                </span>
                            </div>

                            <div class="mt-3 flex gap-2 text-xs">
                                @if($addon->saas_available)
                                    <span class="rounded-full bg-blue-500/10 px-2.5 py-1 text-blue-300">SaaS</span>
                                @endif

                                @if($addon->off_server_available)
                                    <span class="rounded-full bg-violet-500/10 px-2.5 py-1 text-violet-300">Off-server</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-700 p-8 text-center text-sm text-slate-500">
                            No Core add-ons configured yet.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="rounded-3xl border border-slate-800 bg-slate-900/80 p-6 shadow-xl">
                <h2 class="text-lg font-semibold text-white">Bundles</h2>
                <p class="mt-1 text-sm text-slate-400">
                    {{ $bundles->count() }} configured bundles
                </p>

                <div class="mt-6 space-y-3">
                    @forelse($bundles as $bundle)
                        <div class="rounded-2xl border border-slate-800 bg-slate-950/60 p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="font-medium text-white">{{ $bundle->name }}</div>
                                    <div class="text-xs text-slate-500">{{ $bundle->key }}</div>
                                </div>

                                <span class="rounded-full bg-slate-800 px-3 py-1 text-xs text-slate-300">
                                    Bundle
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-slate-700 p-8 text-center text-sm text-slate-500">
                            No bundles configured yet.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>
@endsection
