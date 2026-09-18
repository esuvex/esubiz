@extends('tenant.admin.layout')

@section('title', 'Module Marketplace')

@section('content')
<div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">

    {{-- ESUBIZ_CORE_MODULE_MARKETPLACE_V1 --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <a
                href="{{ route(
                    'tenant.cms.modules.index',
                    ['subdomain' => $website->subdomain]
                ) }}"
                class="mb-3 inline-flex text-sm font-bold text-blue-600 hover:text-blue-700"
            >
                ← Installed Modules
            </a>

            <h1 class="text-2xl font-black tracking-tight text-slate-900">
                Module Marketplace
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                Discover functionality compatible with this website.
            </p>
        </div>
    </div>

    @if($modules->isEmpty())
        <div class="rounded-2xl border border-slate-200 bg-white px-6 py-16 text-center shadow-sm">
            <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-slate-100 text-2xl">
                ◫
            </div>

            <h2 class="mt-4 text-base font-black text-slate-900">
                Module Marketplace
            </h2>

            <p class="mx-auto mt-2 max-w-lg text-sm leading-6 text-slate-500">
                Marketplace modules will appear here from the authoritative Esubiz catalog.
            </p>
        </div>
    @else
        <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
            @foreach($modules as $module)
                @php
                    $name = data_get(
                        $module,
                        'name',
                        data_get($module, 'product_slug', 'Module')
                    );

                    $description = data_get(
                        $module,
                        'description',
                        ''
                    );

                    $installed = (bool) data_get(
                        $module,
                        'installed',
                        false
                    );
                @endphp

                <article class="flex flex-col rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div class="grid h-11 w-11 place-items-center rounded-xl bg-blue-50 font-black text-blue-600">
                            ◫
                        </div>

                        @if($installed)
                            <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-bold text-blue-700">
                                Installed
                            </span>
                        @endif
                    </div>

                    <h2 class="mt-4 text-base font-black text-slate-900">
                        {{ $name }}
                    </h2>

                    @if($description)
                        <p class="mt-2 flex-1 text-sm leading-6 text-slate-500">
                            {{ $description }}
                        </p>
                    @endif

                    <div class="mt-5 border-t border-slate-100 pt-4">
                        @if($installed)
                            <a
                                href="{{ route(
                                    'tenant.cms.modules.index',
                                    ['subdomain' => $website->subdomain]
                                ) }}"
                                class="inline-flex w-full items-center justify-center rounded-xl bg-slate-100 px-4 py-2.5 text-sm font-bold text-slate-700"
                            >
                                Manage Module
                            </a>
                        @else
                            <button
                                type="button"
                                disabled
                                class="inline-flex w-full items-center justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-bold text-white opacity-60"
                            >
                                View Module
                            </button>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</div>
@endsection
