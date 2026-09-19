@extends('tenant.admin.layouts.app')

@section('title', 'Modules')

@section('content')
<div class="mx-auto max-w-7xl space-y-8">

    <div>
        <div class="text-xs font-black uppercase tracking-[.16em] text-blue-600">
            Installed Website Products
        </div>

        <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900">
            Modules
        </h1>

        <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
            Manage installed modules and expand this website through the Esubiz Module Marketplace.
        </p>
    </div>

    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    <section>
        <div class="mb-5">
            <div class="text-xs font-black uppercase tracking-[.16em] text-blue-600">
                Module Hub
            </div>

            <h2 class="mt-2 text-2xl font-black tracking-tight text-slate-900">
                Manage and expand your website
            </h2>

            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                Configure installed modules or discover additional functionality in the Module Marketplace.
            </p>
        </div>

        <div class="grid auto-rows-fr gap-5 lg:grid-cols-3">

            @forelse($modules as $module)
                @php
                    $enabled = (bool) ($module['is_enabled'] ?? false);

                    $metadata = is_array($module['metadata'] ?? null)
                        ? $module['metadata']
                        : [];

                    $name = trim((string) (
                        $module['name']
                        ?? $metadata['name']
                        ?? $module['product_slug']
                        ?? 'Module'
                    ));

                    $description = trim((string) (
                        $metadata['description']
                        ?? ''
                    ));

                    $previewUrl = trim((string) (
                        $module['preview_url']
                        ?? $metadata['preview_url']
                        ?? ''
                    ));
                @endphp

                <article class="flex h-full min-h-[360px] flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">

                    <div class="relative h-40 overflow-hidden bg-slate-100">
                        @if($previewUrl !== '')
                            <x-media.image
                                src="{{ $previewUrl }}"
                                alt="{{ $name }} module preview"
                                class="h-full w-full object-cover object-top"
                            />
                        @else
                            <div class="flex h-full items-center justify-center">
                                <div class="grid h-14 w-14 place-items-center rounded-2xl bg-white text-2xl font-black text-blue-600 shadow-sm">
                                    ◫
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="flex flex-1 flex-col p-5">

                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <div class="text-xs font-black uppercase tracking-wide text-blue-600">
                                    Installed Module
                                </div>

                                <h3 class="mt-1.5 text-lg font-black text-slate-900">
                                    {{ $name }}
                                </h3>

                                <p class="mt-1 text-xs font-bold text-slate-400">
                                    Version {{ $module['product_version'] ?? '—' }}
                                </p>
                            </div>

                            <div class="inline-flex overflow-hidden rounded-xl border border-slate-200 text-[10px] font-black uppercase">

                                <form
                                    method="POST"
                                    action="{{ route(
                                        $enabled
                                            ? 'tenant.cms.modules.disable'
                                            : 'tenant.cms.modules.enable',
                                        [
                                            'subdomain' => $website->subdomain,
                                            'module' => $module['product_slug'],
                                        ]
                                    ) }}"
                                >
                                    @csrf

                                    <button
                                        type="submit"
                                        class="px-3 py-2 {{
                                            $enabled
                                                ? 'bg-blue-600 text-white'
                                                : 'bg-white text-slate-600 hover:bg-blue-50 hover:text-blue-700'
                                        }}"
                                    >
                                        {{ $enabled ? 'Enabled' : 'Enable' }}
                                    </button>
                                </form>

                                @if($enabled)
                                    <form
                                        method="POST"
                                        action="{{ route(
                                            'tenant.cms.modules.disable',
                                            [
                                                'subdomain' => $website->subdomain,
                                                'module' => $module['product_slug'],
                                            ]
                                        ) }}"
                                    >
                                        @csrf

                                        <button
                                            type="submit"
                                            class="border-l border-slate-200 bg-white px-3 py-2 text-slate-600 hover:bg-red-50 hover:text-red-700"
                                        >
                                            Disable
                                        </button>
                                    </form>
                                @endif

                            </div>
                        </div>

                        @if($description !== '')
                            <p class="mt-4 text-sm leading-6 text-slate-500">
                                {{ $description }}
                            </p>
                        @endif

                        <div class="mt-auto flex flex-wrap gap-3 pt-6">

                            <a
                                href="/admin/ecommerce/settings"
                                class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-black text-white hover:bg-blue-700"
                            >
                                Configure
                            </a>

                            @if(!$enabled)
                                <button
                                    type="button"
                                    class="rounded-xl border border-red-200 bg-white px-5 py-2.5 text-sm font-black text-red-600 hover:bg-red-50"
                                    title="Delete module and all module-owned data"
                                >
                                    Delete
                                </button>
                            @endif

                        </div>
                    </div>
                </article>

            @empty
                <article class="flex min-h-[360px] h-full flex-col items-center justify-center rounded-3xl border border-dashed border-slate-300 bg-white p-7 text-center shadow-sm">

                    <div class="grid h-14 w-14 place-items-center rounded-2xl bg-slate-100 text-2xl">
                        ◫
                    </div>

                    <h3 class="mt-5 text-lg font-black text-slate-900">
                        No Module Installed
                    </h3>

                    <p class="mt-2 max-w-xs text-sm leading-6 text-slate-500">
                        Installed modules will appear here for configuration and management.
                    </p>
                </article>
            @endforelse


            {{-- Module Marketplace always follows installed module cards --}}
            <article class="relative flex h-full min-h-[360px] flex-col overflow-hidden rounded-3xl border border-slate-800 bg-slate-950 p-7 text-white shadow-sm">

                <div class="pointer-events-none absolute -right-16 -top-20 h-56 w-56 rounded-full bg-blue-600/20 blur-3xl"></div>

                <div class="relative flex h-full flex-col">

                    {{-- ESUBIZ_MODULE_MARKETPLACE_BRAND_LOGO_V1 --}}
                    <div class="flex h-10 items-center">
                        <img
                            src="{{ rtrim((string) config('services.esubiz.marketplace_url', config('app.url')), '/') . '/media/branding/esubiz-logo.png' }}"
                            alt="Esubiz"
                            class="h-7 w-auto max-w-[110px] object-contain brightness-0 invert"
                        >
                    </div>

                    <div class="mt-5 text-[10px] font-black uppercase tracking-[.18em] text-blue-300">
                        Esubiz Marketplace
                    </div>

                    <h3 class="mt-1.5 text-xl font-black text-white">
                        Module Marketplace
                    </h3>

                    <p class="mt-3 text-sm leading-6 text-slate-300">
                        Discover compatible modules and add new functionality to this website.
                    </p>

                    <div class="mt-auto pt-7">
                        <a
                            href="{{ route(
                                'tenant.cms.modules.marketplace',
                                ['subdomain' => $website->subdomain]
                            ) }}"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-black text-white transition hover:bg-blue-500"
                        >
                            Browse Module Marketplace
                            <span aria-hidden="true">→</span>
                        </a>

                        <p class="mt-3 text-[10px] leading-4 text-slate-400">
                            Browse modules available for this website from the Esubiz Marketplace.
                        </p>
                    </div>

                </div>
            </article>

        </div>
    </section>

</div>
@endsection
