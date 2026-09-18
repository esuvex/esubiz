@extends('tenant.admin.layout')

@section('title', 'Modules')

@section('content')
<div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">

    {{-- ESUBIZ_CORE_MODULE_HUB_V1 --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-black tracking-tight text-slate-900">
                Modules
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                Manage the modules installed on this website.
            </p>
        </div>

        <a
            href="{{ route(
                'tenant.cms.modules.marketplace',
                ['subdomain' => $website->subdomain]
            ) }}"
            class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-blue-700"
        >
            Module Marketplace
        </a>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-5 py-4 sm:px-6">
            <h2 class="text-base font-black text-slate-900">
                Installed Modules
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Modules installed through Website Type provisioning or the Module Marketplace appear here.
            </p>
        </div>

        @if($modules->isEmpty())
            <div class="px-6 py-16 text-center">
                <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-slate-100 text-2xl">
                    ◫
                </div>

                <h3 class="mt-4 text-base font-black text-slate-900">
                    No modules installed
                </h3>

                <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500">
                    Browse the Module Marketplace to add functionality to this website.
                </p>

                <a
                    href="{{ route(
                        'tenant.cms.modules.marketplace',
                        ['subdomain' => $website->subdomain]
                    ) }}"
                    class="mt-5 inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-blue-700"
                >
                    Browse Modules
                </a>
            </div>
        @else
            <div class="divide-y divide-slate-100">
                @foreach($modules as $module)
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
                    @endphp

                    <div class="flex flex-col gap-4 px-5 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <h3 class="font-black text-slate-900">
                                    {{ $name }}
                                </h3>

                                <span
                                    class="rounded-full px-2.5 py-1 text-xs font-bold {{ $enabled ? 'bg-blue-50 text-blue-700' : 'bg-slate-100 text-slate-500' }}"
                                >
                                    {{ $enabled ? 'Active' : 'Inactive' }}
                                </span>
                            </div>

                            @if($description !== '')
                                <p class="mt-1 max-w-2xl text-sm text-slate-500">
                                    {{ $description }}
                                </p>
                            @endif

                            <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs font-medium text-slate-400">
                                <span>
                                    {{ $module['product_slug'] ?? '' }}
                                </span>

                                <span>
                                    Version {{ $module['product_version'] ?? '—' }}
                                </span>
                            </div>
                        </div>

                        <div class="flex shrink-0 items-center gap-2">
                            <button
                                type="button"
                                disabled
                                class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-400"
                            >
                                Configure
                            </button>

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
                                    class="relative inline-flex h-7 w-12 items-center rounded-full transition {{ $enabled ? 'bg-blue-600' : 'bg-slate-300' }}"
                                    title="{{ $enabled ? 'Disable module' : 'Enable module' }}"
                                    aria-label="{{ $enabled ? 'Disable module' : 'Enable module' }}"
                                >
                                    <span
                                        class="inline-block h-5 w-5 rounded-full bg-white shadow transition-transform {{ $enabled ? 'translate-x-6' : 'translate-x-1' }}"
                                    ></span>
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
