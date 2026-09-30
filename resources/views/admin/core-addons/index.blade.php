
<style>
    /* ESUBIZ_MARKETPLACE_FEATURED_TOGGLE_V608 */
    .esu-featured-toggle {
        position: relative;
        display: inline-block;
        width: 44px;
        height: 24px;
        flex: 0 0 44px;
        cursor: pointer;
    }

    .esu-featured-toggle input[type="checkbox"] {
        position: absolute;
        width: 1px;
        height: 1px;
        opacity: 0;
        pointer-events: none;
    }

    .esu-featured-toggle-track {
        position: absolute;
        inset: 0;
        border-radius: 9999px;
        background: #cbd5e1;
        transition: background-color .2s ease;
    }

    .esu-featured-toggle-track::after {
        content: "";
        position: absolute;
        top: 4px;
        left: 4px;
        width: 16px;
        height: 16px;
        border-radius: 9999px;
        background: #fff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .22);
        transition: transform .2s ease;
    }

    .esu-featured-toggle input[type="checkbox"]:checked + .esu-featured-toggle-track {
        background: #2563eb;
    }

    .esu-featured-toggle input[type="checkbox"]:checked + .esu-featured-toggle-track::after {
        transform: translateX(20px);
    }

    .esu-featured-toggle input[type="checkbox"]:focus-visible + .esu-featured-toggle-track {
        outline: 3px solid rgba(37, 99, 235, .22);
        outline-offset: 2px;
    }
</style>

@extends('admin.layouts.app')

@section('content')

<div class="min-h-full bg-slate-50 px-6 py-8 lg:px-8">

    <div class="mb-8 flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">

        <div>
            <div class="mb-2 text-xs font-bold uppercase tracking-[0.18em] text-blue-600">
                Esubiz Core Commerce
            </div>

            <h1 class="text-3xl font-bold tracking-tight text-slate-900">
                Add-ons & Bundles
            </h1>

            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                Configure Core extensions for SaaS rentals and off-server licenses.
            </p>
        </div>

        <div class="flex gap-3">
            <button type="button"
                    onclick="document.getElementById('addon-form').classList.remove('hidden')"
                    class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">
                + Add Add-on
            </button>

            <button type="button"
                    onclick="document.getElementById('bundle-form').classList.remove('hidden')"
                    class="rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                + Create Bundle
            </button>
        </div>
    </div>


    @if(session('success'))
        <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-700">
            {{ session('success') }}
        </div>
    @endif


    {{-- ESUBIZ_ADDON_BUNDLE_MANAGEMENT_TABLES_V1 --}}

    {{-- ADD-ON REGISTRY --}}
    <div class="mb-8 rounded-3xl border border-slate-200 bg-white shadow-sm"
         data-ajax-table="addons">

        <div class="border-b border-slate-100 px-6 py-5">
            <h2 class="text-lg font-bold text-slate-900">Add-ons</h2>
            <p class="mt-1 text-sm text-slate-500">
                {{ $addons->total() }} configured extensions
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">

                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Add-on</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Availability</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">SaaS Price</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Off-server Price</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100 bg-white">

                    @forelse($addons as $addon)

                        @php

                            $addonPreviewUrl = !empty($addon->preview_image)
                                ? route('marketplace.addons.preview', ['type' => 'addon', 'id' => $addon->id])
                                : null;

                            $addonModalData = [
                                'type' => 'addon',
                                'name' => $addon->name,
                                'description' => $addon->description,
                                'preview' => $addonPreviewUrl,
                                'implementation_type' => $addon->implementation_type ?? 'allocation',
                            ];
                        @endphp

                        <tr class="hover:bg-slate-50/70">

                            <td class="px-6 py-4">
                                <div class="flex min-w-[230px] items-center gap-3">

                                    @if($addonPreviewUrl)
                                        <img src="{{ $addonPreviewUrl }}"
                                             alt=""
                                             class="h-12 w-12 rounded-xl border border-slate-200 object-cover">
                                    @else
                                        <div class="flex h-12 w-12 items-center justify-center rounded-xl border border-slate-200 bg-slate-100 text-sm font-black text-slate-400">
                                            {{ strtoupper(substr($addon->name, 0, 1)) }}
                                        </div>
                                    @endif

                                    <div>
                                        <div class="font-bold text-slate-900">
                                            {{ $addon->name }}
                                        </div>
                                        <div class="mt-0.5 text-xs text-slate-400">
                                            {{ $addon->key }}
                                        </div>
                                    </div>

                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold
                                    {{ ($addon->implementation_type ?? 'allocation') === 'package'
                                        ? 'bg-violet-50 text-violet-700'
                                        : 'bg-blue-50 text-blue-700' }}">
                                    {{ ucfirst($addon->implementation_type ?? 'allocation') }}
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1.5">

                                    @if($addon->saas_available)
                                        <span class="rounded-full bg-blue-50 px-2 py-1 text-xs font-semibold text-blue-700">
                                            SaaS
                                        </span>
                                    @endif

                                    @if($addon->off_server_available)
                                        <span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700">
                                            Off-server
                                        </span>
                                    @endif

                                    @if(!$addon->saas_available && !$addon->off_server_available)
                                        <span class="text-sm text-slate-400">—</span>
                                    @endif

                                </div>
                            </td>

                            <td class="px-6 py-4 text-sm font-semibold text-slate-700">
                                @if($addon->saas_available && $addon->saas_price !== null)
                                    {{ $addon->saas_currency }} {{ number_format($addon->saas_price, 2) }}
                                @else
                                    —
                                @endif
                            </td>

                            <td class="px-6 py-4 text-sm font-semibold text-slate-700">
                                @if($addon->off_server_available && $addon->off_server_price !== null)
                                    {{ $addon->off_server_currency }} {{ number_format($addon->off_server_price, 2) }}
                                @else
                                    —
                                @endif
                            </td>

                            <td class="px-6 py-4">
                                <form method="POST"
                                      action="{{ route('admin.core-addons.addons.toggle', $addon->id) }}">
                                    @csrf

                                    <button type="submit"
                                            title="{{ $addon->is_active ? 'Deactivate' : 'Activate' }}"
                                            class="relative inline-flex h-6 w-11 items-center rounded-full transition
                                                {{ $addon->is_active ? 'bg-blue-600' : 'bg-slate-300' }}">

                                        <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition
                                            {{ $addon->is_active ? 'translate-x-6' : 'translate-x-1' }}">
                                        </span>
                                    </button>
                                </form>
                            </td>

                            <td class="px-6 py-4 text-right">
                                <div class="relative inline-block text-left" data-action-menu>

                                    <button type="button"
                                            data-action-menu-button
                                            class="flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-lg font-black text-slate-600 hover:bg-slate-50">
                                        ⋮
                                    </button>

                                    <div data-action-menu-panel
                                         class="absolute right-0 z-40 mt-2 hidden w-48 overflow-hidden rounded-xl border border-slate-200 bg-white py-1 text-left shadow-xl">

                                        <button type="button"
                                                data-view-features='@json($addonModalData)'
                                                class="block w-full px-4 py-2.5 text-left text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                            View Add-on
                                        </button>

                                        <a href="{{ route('admin.core-addons.edit', $addon->id) }}"
                                           class="block px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                            Edit Add-on
                                        </a>

                                        <form method="POST"
                                              action="{{ route('admin.core-addons.addons.destroy', $addon->id) }}"
                                              onsubmit="return confirm('Permanently delete this add-on? This cannot be undone.');">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    class="block w-full px-4 py-2.5 text-left text-sm font-semibold text-red-600 hover:bg-red-50">
                                                Delete Add-on
                                            </button>
                                        </form>

                                    </div>
                                </div>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="7"
                                class="px-6 py-12 text-center text-sm text-slate-500">
                                No Core add-ons configured.
                            </td>
                        </tr>

                    @endforelse

                </tbody>
            </table>
        </div>

        <div class="flex items-center justify-between border-t border-slate-100 px-6 py-4">

            <div class="text-xs text-slate-500">
                Page {{ $addons->currentPage() }} of {{ max(1, $addons->lastPage()) }}
            </div>

            <div class="flex gap-2">

                @if($addons->previousPageUrl())
                    <a href="{{ $addons->previousPageUrl() }}"
                       data-ajax-page
                       class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50">
                        Previous
                    </a>
                @endif

                @if($addons->nextPageUrl())
                    <a href="{{ $addons->nextPageUrl() }}"
                       data-ajax-page
                       class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50">
                        Next
                    </a>
                @endif

            </div>
        </div>
    </div>


    {{-- BUNDLES --}}
    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm"
         data-ajax-table="bundles">

        <div class="border-b border-slate-100 px-6 py-5">
            <h2 class="text-lg font-bold text-slate-900">Add-on Bundles</h2>
            <p class="mt-1 text-sm text-slate-500">
                {{ $bundles->total() }} configured bundles
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">

                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Bundle</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Included Add-ons</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Availability</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">SaaS Price</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Off-server Price</th>
                        <th class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100 bg-white">

                    @forelse($bundles as $bundle)

                        @php
                            $bundleItems = DB::table('core_addon_bundle_items')
                                ->join('core_addons', 'core_addons.id', '=', 'core_addon_bundle_items.addon_id')
                                ->where('core_addon_bundle_items.bundle_id', $bundle->id)
                                ->select(
                                    'core_addons.id as addon_id',
                                    'core_addons.name',
                                    'core_addon_bundle_items.allocation',
                                    'core_addon_bundle_items.is_unlimited'
                                )
                                ->get();

                            $bundlePreviewUrl = !empty($bundle->preview_image)
                                ? route('marketplace.addons.preview', ['type' => 'bundle', 'id' => $bundle->id])
                                : null;

                            $bundleModalData = [
                                'type' => 'bundle',
                                'name' => $bundle->name,
                                'description' => $bundle->description,
                                'preview' => $bundlePreviewUrl,
                                'items' => $bundleItems->map(fn ($item) => [
                                    'name' => $item->name,
                                    'allocation' => $item->is_unlimited
                                        ? 'Unlimited'
                                        : ($item->allocation ?? 'Default'),
                                ])->values()->toArray(),
                            ];

                            $bundleCategoryIds = $bundle->catalog_product_id
                                ? DB::table('catalog_product_marketplace_category')
                                    ->where('catalog_product_id', $bundle->catalog_product_id)
                                    ->pluck('marketplace_category_id')
                                    ->map(fn ($id) => (int) $id)
                                    ->values()
                                    ->all()
                                : [];

                            $bundleEditData = [
                                'id' => $bundle->id,
                                'key' => $bundle->key,
                                'name' => $bundle->name,
                                'category' => $bundle->category,
                                'description' => $bundle->description,
                                'preview' => $bundlePreviewUrl,
                                'marketplace_category_ids' => $bundleCategoryIds,
                                'saas_available' => (bool) $bundle->saas_available,
                                'saas_price' => $bundle->saas_price,
                                'saas_currency' => $bundle->saas_currency,
                                'saas_billing_period' => $bundle->saas_billing_period,
                                'saas_billing_interval' => $bundle->saas_billing_interval,
                                'off_server_available' => (bool) $bundle->off_server_available,
                                'off_server_price' => $bundle->off_server_price,
                                'off_server_currency' => $bundle->off_server_currency,
                                'items' => $bundleItems->map(fn ($item) => [
                                    'addon_id' => $item->addon_id,
                                    'allocation' => $item->allocation,
                                    'is_unlimited' => (bool) $item->is_unlimited,
                                ])->values()->toArray(),
                            ];
                        @endphp

                        <tr class="hover:bg-slate-50/70">

                            <td class="px-6 py-4">
                                <div class="flex min-w-[230px] items-center gap-3">

                                    @if($bundlePreviewUrl)
                                        <img src="{{ $bundlePreviewUrl }}"
                                             alt=""
                                             class="h-12 w-12 rounded-xl border border-slate-200 object-cover">
                                    @else
                                        <div class="flex h-12 w-12 items-center justify-center rounded-xl border border-violet-100 bg-violet-50 text-sm font-black text-violet-600">
                                            {{ strtoupper(substr($bundle->name, 0, 1)) }}
                                        </div>
                                    @endif

                                    <div>
                                        <div class="font-bold text-slate-900">
                                            {{ $bundle->name }}
                                        </div>
                                        <div class="mt-0.5 text-xs text-slate-400">
                                            {{ $bundle->key }}
                                        </div>
                                    </div>

                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex max-w-[260px] flex-wrap gap-1.5">

                                    @forelse($bundleItems->take(3) as $item)
                                        <span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-600">
                                            {{ $item->name }}
                                        </span>
                                    @empty
                                        <span class="text-sm text-slate-400">—</span>
                                    @endforelse

                                    @if($bundleItems->count() > 3)
                                        <span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-500">
                                            +{{ $bundleItems->count() - 3 }}
                                        </span>
                                    @endif

                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1.5">

                                    @if($bundle->saas_available)
                                        <span class="rounded-full bg-blue-50 px-2 py-1 text-xs font-semibold text-blue-700">
                                            SaaS
                                        </span>
                                    @endif

                                    @if($bundle->off_server_available)
                                        <span class="rounded-full bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700">
                                            Off-server
                                        </span>
                                    @endif

                                </div>
                            </td>

                            <td class="px-6 py-4 text-sm font-semibold text-slate-700">
                                @if($bundle->saas_available && $bundle->saas_price !== null)
                                    {{ $bundle->saas_currency }} {{ number_format($bundle->saas_price, 2) }}
                                @else
                                    —
                                @endif
                            </td>

                            <td class="px-6 py-4 text-sm font-semibold text-slate-700">
                                @if($bundle->off_server_available && $bundle->off_server_price !== null)
                                    {{ $bundle->off_server_currency }} {{ number_format($bundle->off_server_price, 2) }}
                                @else
                                    —
                                @endif
                            </td>

                            <td class="px-6 py-4">
                                <form method="POST"
                                      action="{{ route('admin.core-addons.bundles.toggle', $bundle->id) }}">
                                    @csrf

                                    <button type="submit"
                                            title="{{ $bundle->is_active ? 'Deactivate' : 'Activate' }}"
                                            class="relative inline-flex h-6 w-11 items-center rounded-full transition
                                                {{ $bundle->is_active ? 'bg-blue-600' : 'bg-slate-300' }}">

                                        <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition
                                            {{ $bundle->is_active ? 'translate-x-6' : 'translate-x-1' }}">
                                        </span>
                                    </button>
                                </form>
                            </td>

                            <td class="px-6 py-4 text-right">
                                <div class="relative inline-block text-left" data-action-menu>

                                    <button type="button"
                                            data-action-menu-button
                                            class="flex h-9 w-9 items-center justify-center rounded-xl border border-slate-200 bg-white text-lg font-black text-slate-600 hover:bg-slate-50">
                                        ⋮
                                    </button>

                                    <div data-action-menu-panel
                                         class="absolute right-0 z-40 mt-2 hidden w-48 overflow-hidden rounded-xl border border-slate-200 bg-white py-1 text-left shadow-xl">

                                        <button type="button"
                                                data-view-features='@json($bundleModalData)'
                                                class="block w-full px-4 py-2.5 text-left text-sm font-semibold text-slate-700 hover:bg-slate-50">
                                            View Bundle
                                        </button>

                                        <button type="button"
                                                class="bundle-edit-btn block w-full px-4 py-2.5 text-left text-sm font-semibold text-slate-700 hover:bg-slate-50"
                                                data-bundle="{{ base64_encode(json_encode($bundleEditData)) }}">
                                            Edit Bundle
                                        </button>

                                        <form method="POST"
                                              action="{{ route('admin.core-addons.bundles.destroy', $bundle->id) }}"
                                              onsubmit="return confirm('Permanently delete this bundle? This cannot be undone.');">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit"
                                                    class="block w-full px-4 py-2.5 text-left text-sm font-semibold text-red-600 hover:bg-red-50">
                                                Delete Bundle
                                            </button>
                                        </form>

                                    </div>
                                </div>
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="7"
                                class="px-6 py-12 text-center text-sm text-slate-500">
                                No Add-on bundles configured.
                            </td>
                        </tr>

                    @endforelse

                </tbody>
            </table>
        </div>

        <div class="flex items-center justify-between border-t border-slate-100 px-6 py-4">

            <div class="text-xs text-slate-500">
                Page {{ $bundles->currentPage() }} of {{ max(1, $bundles->lastPage()) }}
            </div>

            <div class="flex gap-2">

                @if($bundles->previousPageUrl())
                    <a href="{{ $bundles->previousPageUrl() }}"
                       data-ajax-page
                       class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50">
                        Previous
                    </a>
                @endif

                @if($bundles->nextPageUrl())
                    <a href="{{ $bundles->nextPageUrl() }}"
                       data-ajax-page
                       class="rounded-xl border border-slate-200 px-4 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50">
                        Next
                    </a>
                @endif

            </div>
        </div>
    </div>


    {{-- VIEW FEATURES MODAL --}}
    <div id="addon-features-modal"
         class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-950/60 p-4"
         aria-hidden="true">

        <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-3xl bg-white shadow-2xl">

            <div class="flex items-center justify-between border-b border-slate-100 px-6 py-5">

                <div>
                    <div class="text-xs font-bold uppercase tracking-wider text-blue-600">
                        Product Features
                    </div>

                    <h3 data-features-title
                        class="mt-1 text-xl font-black text-slate-900"></h3>
                </div>

                <button type="button"
                        data-close-features
                        class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 text-xl text-slate-500 hover:bg-slate-50">
                    ×
                </button>

            </div>

            <div class="min-w-0 w-full max-w-full overflow-hidden p-4 sm:p-6">

                <img data-features-preview
                     class="mb-5 hidden max-h-72 w-full rounded-2xl border border-slate-200 object-cover"
                     alt="">

                <p data-features-description
                   class="mb-6 hidden text-sm leading-6 text-slate-600"></p>

                <div data-features-section>
                    <div class="mb-3 text-xs font-bold uppercase tracking-wider text-slate-400">
                        Features
                    </div>

                    <div data-features-list class="space-y-3"></div>
                </div>

                <div data-bundle-items-section class="mt-6 hidden">

                    <div class="mb-3 text-xs font-bold uppercase tracking-wider text-slate-400">
                        Included Add-ons
                    </div>

                    <div data-bundle-items class="flex flex-wrap gap-2"></div>

                </div>

            </div>

            <div class="flex justify-end border-t border-slate-100 px-6 py-4">
                <button type="button"
                        data-close-features
                        class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-bold text-white">
                    Close
                </button>
            </div>

        </div>
    </div>

</div>

<script>
(function () {
    const modal = document.getElementById('addon-features-modal');

    function closeMenus(except = null) {
        document.querySelectorAll('[data-action-menu-panel]').forEach(function (panel) {
            if (panel !== except) panel.classList.add('hidden');
        });
    }

    function closeModal() {
        if (!modal) return;

        modal.classList.add('hidden');
        modal.classList.remove('flex');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
    }

    function openModal(data) {
        if (!modal) return;

        const title = modal.querySelector('[data-features-title]');
        const description = modal.querySelector('[data-features-description]');
        const preview = modal.querySelector('[data-features-preview]');
        const featureSection = modal.querySelector('[data-features-section]');
        const featureList = modal.querySelector('[data-features-list]');
        const bundleSection = modal.querySelector('[data-bundle-items-section]');
        const bundleItems = modal.querySelector('[data-bundle-items]');

        title.textContent = data.name || 'Product';

        if (data.description) {
            description.textContent = data.description;
            description.classList.remove('hidden');
        } else {
            description.textContent = '';
            description.classList.add('hidden');
        }

        if (data.preview) {
            preview.src = data.preview;
            preview.classList.remove('hidden');
        } else {
            preview.removeAttribute('src');
            preview.classList.add('hidden');
        }

        featureList.innerHTML = '';

        const features = Array.isArray(data.features) ? data.features : [];

        if (features.length) {
            featureSection.classList.remove('hidden');

            features.forEach(function (feature) {
                const card = document.createElement('div');
                card.className =
                    'rounded-2xl border border-slate-200 bg-slate-50 p-4';

                const name = document.createElement('div');
                name.className = 'font-bold text-slate-900';
                name.textContent = feature.name || 'Feature';
                card.appendChild(name);

                if (feature.description) {
                    const description = document.createElement('div');
                    description.className =
                        'mt-1 text-sm leading-6 text-slate-600';
                    description.textContent = feature.description;
                    card.appendChild(description);
                }

                if (feature.allocation) {
                    const allocation = document.createElement('div');
                    allocation.className =
                        'mt-3 inline-flex rounded-full bg-blue-50 px-2.5 py-1 text-xs font-bold text-blue-700';
                    allocation.textContent = feature.allocation;
                    card.appendChild(allocation);
                }

                featureList.appendChild(card);
            });
        } else {
            featureSection.classList.add('hidden');
        }

        bundleItems.innerHTML = '';

        const items = Array.isArray(data.items) ? data.items : [];

        if (data.type === 'bundle' && items.length) {
            bundleSection.classList.remove('hidden');

            items.forEach(function (item) {
                const chip = document.createElement('span');
                chip.className =
                    'rounded-full bg-slate-100 px-3 py-2 text-xs font-semibold text-slate-700';
                chip.textContent =
                    item.name + (item.allocation ? ' · ' + item.allocation : '');

                bundleItems.appendChild(chip);
            });
        } else {
            bundleSection.classList.add('hidden');
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
    }

    document.addEventListener('click', function (event) {
        const menuButton = event.target.closest('[data-action-menu-button]');

        if (menuButton) {
            event.preventDefault();
            event.stopPropagation();

            const menu = menuButton.closest('[data-action-menu]');
            const panel = menu ? menu.querySelector('[data-action-menu-panel]') : null;

            if (!panel) return;

            const shouldOpen = panel.classList.contains('hidden');

            closeMenus();

            if (shouldOpen) panel.classList.remove('hidden');

            return;
        }

        const featuresButton = event.target.closest('[data-view-features]');

        if (featuresButton) {
            event.preventDefault();

            try {
                const data = JSON.parse(
                    featuresButton.getAttribute('data-view-features') || '{}'
                );

                closeMenus();
                openModal(data);
            } catch (error) {
                console.error('Unable to load product features.', error);
            }

            return;
        }

        if (event.target.closest('[data-close-features]')) {
            closeModal();
            return;
        }

        if (modal && event.target === modal) {
            closeModal();
            return;
        }

        closeMenus();
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeMenus();
            closeModal();
        }
    });

    document.addEventListener('click', async function (event) {
        const link = event.target.closest('[data-ajax-page]');

        if (!link) return;

        event.preventDefault();

        const current = link.closest('[data-ajax-table]');
        const type = current ? current.getAttribute('data-ajax-table') : null;

        if (!current || !type) return;

        try {
            const response = await fetch(link.href, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            });

            if (!response.ok) {
                window.location.href = link.href;
                return;
            }

            const html = await response.text();
            const nextDocument =
                new DOMParser().parseFromString(html, 'text/html');

            const replacement =
                nextDocument.querySelector(
                    '[data-ajax-table="' + type + '"]'
                );

            if (!replacement) {
                window.location.href = link.href;
                return;
            }

            current.replaceWith(replacement);
            window.history.replaceState({}, '', link.href);

        } catch (error) {
            window.location.href = link.href;
        }
    });
})();
</script>

<script>
(function () {
    const root = document.querySelector('[data-bundle-addon-selector]');
    if (!root) return;

    const search = root.querySelector('[data-bundle-addon-search]');
    const rows = Array.from(root.querySelectorAll('[data-bundle-addon-row]'));
    const count = root.querySelector('[data-bundle-selected-count]');
    const noResults = root.querySelector('[data-bundle-no-results]');

    function refreshBundleSelector() {
        let selected = 0;

        rows.forEach(function (row) {
            const checkbox = row.querySelector('[data-bundle-addon-checkbox]');
            const allocation = row.querySelector('[data-bundle-allocation-wrap]');

            if (!checkbox) return;

            if (checkbox.checked) {
                selected++;
                allocation?.classList.remove('hidden');
                row.classList.add('bg-blue-50');
            } else {
                allocation?.classList.add('hidden');
                row.classList.remove('bg-blue-50');
            }
        });

        if (count) count.textContent = selected;
    }

    function filterBundleAddons() {
        const query = String(search?.value || '').trim().toLowerCase();
        let visible = 0;

        rows.forEach(function (row) {
            const haystack = String(row.dataset.search || '').toLowerCase();
            const show = !query || haystack.includes(query);

            row.classList.toggle('hidden', !show);

            if (show) visible++;
        });

        if (noResults) {
            noResults.classList.toggle('hidden', visible !== 0);
        }
    }

    root.addEventListener('change', function (event) {
        if (event.target.matches('[data-bundle-addon-checkbox]')) {
            refreshBundleSelector();
        }
    });

    search?.addEventListener('input', filterBundleAddons);

    window.refreshBundleAddonSelector = refreshBundleSelector;

    refreshBundleSelector();
})();
</script>

<script>
document.addEventListener('click', function (event) {
    const button = event.target.closest('.bundle-edit-btn');

    if (!button) return;

    event.preventDefault();

    const raw = button.getAttribute('data-bundle');

    if (!raw) {
        alert('Unable to load bundle data.');
        return;
    }

    let bundle;

    try {
        bundle = JSON.parse(atob(raw));
    } catch (error) {
        console.error('Bundle data error:', error);
        alert('Unable to load bundle data.');
        return;
    }

    const form = document.querySelector('#bundle-form form');

    if (!form) {
        alert('Bundle form not found.');
        return;
    }

    form.action = '/admin/core-addons/bundles/' + bundle.id;

    let method = form.querySelector('input[name="_method"]');

    if (!method) {
        method = document.createElement('input');
        method.type = 'hidden';
        method.name = '_method';
        form.appendChild(method);
    }

    method.value = 'PUT';

    const setValue = (name, value) => {
        const input = form.querySelector('[name="' + name + '"]');
        if (input) input.value = value ?? '';
    };

    setValue('name', bundle.name);
    setValue('key', bundle.key);
    setValue('category', bundle.category);
    setValue('description', bundle.description);
    setValue('saas_price', bundle.saas_price);
    setValue('saas_currency', bundle.saas_currency);
    setValue('saas_billing_period', bundle.saas_billing_period);
    setValue('saas_billing_interval', bundle.saas_billing_interval);
    setValue('off_server_price', bundle.off_server_price);
    setValue('off_server_currency', bundle.off_server_currency);

    const saas = form.querySelector(
        'input[type="checkbox"][name="saas_available"]'
    );

    const offserver = form.querySelector(
        'input[type="checkbox"][name="off_server_available"]'
    );

    if (saas) saas.checked = !!bundle.saas_available;
    if (offserver) offserver.checked = !!bundle.off_server_available;

    const selectedCategoryIds = (bundle.marketplace_category_ids || [])
        .map(id => String(id));

    form.querySelectorAll('input[name="marketplace_category_ids[]"]')
        .forEach(function (input) {
            input.checked = selectedCategoryIds.includes(String(input.value));
        });

    const categoryPicker = form.querySelector('[data-category-picker]');

    if (categoryPicker) {
        const categoryLabel = categoryPicker.querySelector(
            '[data-category-picker-label]'
        );

        const selectedNames = Array.from(
            categoryPicker.querySelectorAll(
                'input[name="marketplace_category_ids[]"]:checked'
            )
        ).map(function (input) {
            const option = input.closest('[data-category-option]');
            const label = option?.querySelector('span');
            return label ? label.textContent.trim() : '';
        }).filter(Boolean);

        if (categoryLabel) {
            categoryLabel.textContent = selectedNames.length
                ? selectedNames.join(', ')
                : 'Select Marketplace Categories';
        }
    }

    form.querySelectorAll('input[type="checkbox"][name^="items["]')
        .forEach(input => input.checked = false);

    form.querySelectorAll('input[name^="items["][name$="[allocation]"]')
        .forEach(input => input.value = '');

    (bundle.items || []).forEach(function (item) {
        const addonId = item.addon_id;

        const checkbox = form.querySelector(
            'input[type="checkbox"][name="items[' + addonId + '][addon_id]"]'
        );

        const allocation = form.querySelector(
            'input[name="items[' + addonId + '][allocation]"]'
        );

        if (checkbox) checkbox.checked = true;
        if (allocation) allocation.value = item.allocation ?? '';
    });

    if (typeof window.refreshBundleAddonSelector === 'function') {
        window.refreshBundleAddonSelector();
    }

    const featuredToggle = form.querySelector('[data-bundle-featured]');

    if (featuredToggle) {
        featuredToggle.checked =
            Number(bundle.marketplace_featured ?? 0) === 1;
    }

    const previewImage = form.querySelector('[data-preview-image]');
    const previewPlaceholder = form.querySelector('[data-preview-placeholder]');
    const previewInput = form.querySelector('[data-preview-input]');

    if (previewInput) {
        previewInput.value = '';
    }

    if (previewImage && bundle.preview) {
        previewImage.src = bundle.preview;
        previewImage.classList.remove('hidden');
        previewPlaceholder?.classList.add('hidden');
    } else if (previewImage) {
        previewImage.removeAttribute('src');
        previewImage.classList.add('hidden');
        previewPlaceholder?.classList.remove('hidden');
    }

    const heading = document.querySelector('#bundle-form h2');

    if (heading) {
        heading.textContent = 'Edit Addon Bundle';
    }

    const submit = form.querySelector('button[type="submit"]');

    if (submit) {
        submit.textContent = 'Update Bundle';
    }

    const bundleForm = document.getElementById('bundle-form');

    if (bundleForm) {
        bundleForm.classList.remove('hidden');

        bundleForm.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }
});
</script>





    {{-- ADD-ON FORM --}}
    <div id="addon-form"
         class="fixed inset-0 z-[100] hidden bg-slate-950/50 backdrop-blur-sm"
         onclick="if(event.target === this) this.classList.add('hidden')">
        <div class="mx-auto flex min-h-full max-w-6xl items-start justify-center sm:items-center">
            <div class="flex max-h-[calc(100dvh-1.5rem)] w-full max-w-5xl flex-col overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-2xl sm:max-h-[calc(100dvh-3rem)]">

                <div class="relative z-10 flex shrink-0 items-start justify-between gap-4 border-b border-slate-100 bg-white px-5 py-4 sm:px-6 sm:py-5">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Create Add-on</h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Define what this add-on unlocks and how it is sold.
                        </p>
                    </div>

                    <button type="button"
                            onclick="document.getElementById('addon-form').classList.add('hidden')"
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-slate-200 text-xl leading-none text-slate-500 hover:bg-slate-50 hover:text-slate-900"
                            aria-label="Close">
                        &times;
                    </button>
                </div>

                <div class="w-full overflow-visible">

        <form method="POST"
              action="{{ route('admin.core-addons.addons.store') }}"
              class="p-6"
              enctype="multipart/form-data"
              x-data="{
                  addonType: @js(old('implementation_type', 'allocation')),
                  placements: @js(old('addon_placements', [])),
                  dashboardCondition: @js(old('dashboard_sales_trigger.condition_type', 'resource_threshold')),
                  setAddonType(type) {
                      this.addonType = type;

                      if (type === 'package') {
                          this.placements = this.placements.filter(
                              placement => placement !== 'dashboard'
                          );
                      }
                  }
              }">
    @csrf

    {{-- ESUBIZ_ADDON_IMPLEMENTATION_TYPE_V1 --}}
    <div class="mb-6 rounded-2xl border border-slate-200 bg-slate-50 p-5">

        <input type="hidden"
               name="implementation_type"
               :value="addonType">

        <div class="mb-4">
            <div class="text-sm font-black text-slate-900">Add-on Type</div>
            <p class="mt-1 text-xs text-slate-500">
                Choose whether this Add-on extends a SaaS Core allowance or installs functionality.
            </p>
        </div>

        <div class="flex items-center gap-4">
            <button type="button"
                    @click="setAddonType('allocation')"
                    class="text-sm font-bold transition"
                    :class="addonType === 'allocation' ? 'text-slate-900' : 'text-slate-400'">
                Allocation
            </button>

            <button type="button"
                    @click="setAddonType(addonType === 'allocation' ? 'package' : 'allocation')"
                    class="relative h-7 w-12 shrink-0 rounded-full transition"
                    :class="addonType === 'package' ? 'bg-blue-600' : 'bg-slate-300'"
                    role="switch"
                    :aria-checked="addonType === 'package'">
                <span class="absolute top-1 h-5 w-5 rounded-full bg-white shadow transition-all"
                      :class="addonType === 'package' ? 'left-6' : 'left-1'"></span>
            </button>

            <button type="button"
                    @click="setAddonType('package')"
                    class="text-sm font-bold transition"
                    :class="addonType === 'package' ? 'text-blue-600' : 'text-slate-400'">
                Package
            </button>
        </div>

        <div class="mt-4 rounded-xl border border-slate-200 bg-white px-4 py-3 text-xs text-slate-600">
            <span x-show="addonType === 'allocation'">
                SaaS Core allowance. No package files or off-server allocation.
            </span>
            <span x-show="addonType === 'package'" x-cloak>
                Installable functionality. Package source will be configured below.
            </span>
        </div>
    </div>

    {{-- ESUBIZ_PACKAGE_ADDON_SOURCE_V1 --}}
    <div x-show="addonType === 'package'"
         x-cloak
         class="mb-6">
        @include('admin.components.product-source-toggle', [
            'product' => 'Add-on',
            'mode' => 'folder',
            'folderName' => 'package_name',
            'folderValue' => old('package_name', ''),
            'folderPlaceholder' => 'Search/select Add-on package folder',
            'uploadName' => 'package_file',
            'accept' => '.zip,application/zip',
            'help' => 'Package source is required only for Package Add-ons. Allocation Add-ons do not install package files.',
        ])
    </div>

    <div class="grid gap-5 md:grid-cols-2">

        <div>
            <label class="text-sm font-semibold text-slate-700">Add-on Name</label>
            <input id="addon-display-name"
                   name="name"
                   required
                   placeholder="CRM Growth"
                   class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm">
        </div>

        <div>
            <label class="text-sm font-semibold text-slate-700">Generated Key</label>
            <input id="addon-generated-key"
                   name="key"
                   readonly
                   class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm">
        </div>

        <div class="md:col-span-2">
            <label class="text-sm font-semibold text-slate-700">Core Feature</label>
            <select id="addon-core-feature"
                    name="parent_capability"
                    required
                    class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm">
                <option value="">Select a Core feature</option>
                @foreach($featureLimits->pluck('feature_key')->filter()->unique()->sort() as $feature)
                    <option value="{{ $feature }}">
                        {{ ucwords(str_replace('_', ' ', $feature)) }}
                    </option>
                @endforeach
            </select>
        </div>

    </div>

    <input id="addon-entitlement-type"
           type="hidden"
           name="entitlement_type"
           value="feature">

    <div id="addon-entitlement-badge"
         class="mt-4 hidden rounded-xl border border-blue-100 bg-blue-50 px-4 py-3 text-sm font-semibold text-blue-700">
        Entitlement inherited from Core
    </div>

    <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-5">
        <div class="font-bold text-slate-900">Functions Unlocked</div>
        <p class="mt-1 text-xs text-slate-500">
            Select one or more functions registered under the selected Core feature.
        </p>

        <div id="addon-functions"
             class="mt-4 grid gap-3 md:grid-cols-2">
            <div class="rounded-xl border border-dashed border-slate-200 p-4 text-sm text-slate-400 md:col-span-2">
                Select a Core feature first.
            </div>
        </div>
    </div>

    <div id="addon-allocation-box"
         x-show="addonType === 'allocation'"
         x-cloak
         class="mt-6 rounded-2xl border border-slate-200 bg-white p-5">

        <div class="mb-4">
            <div class="font-bold text-slate-900">Add-on Allocation</div>
            <p class="mt-1 text-xs text-slate-500">
                Configure the additional amount this add-on contributes each time it is purchased or rented.
            </p>
        </div>

        <div id="addon-allocation-list" class="space-y-4"></div>
    </div>

    {{-- ESUBIZ_CREATE_ADDON_PLACEMENT_ADMIN_CONTENT_V1 --}}
<div class="mb-5 rounded-2xl border border-slate-200 bg-white p-5">
    <div class="mb-4">
        <div class="text-sm font-black text-slate-900">
            Premium Feature Content
        </div>
        <div class="mt-1 text-xs text-slate-500">
            Optional content shown with this Add-on in its selected placements.
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <div>
            <label class="mb-1 block text-xs font-bold text-slate-700">
                Premium Title
            </label>
            <input
                type="text"
                name="placement_title"
                value="{{ old('placement_title') }}"
                maxlength="255"
                class="w-full rounded-xl border-slate-300 text-sm"
                placeholder="Optional">
        </div>

        <div>
            <label class="mb-1 block text-xs font-bold text-slate-700">
                CTA Text
            </label>
            <input
                type="text"
                name="placement_cta_text"
                value="{{ old('placement_cta_text') }}"
                maxlength="100"
                class="w-full rounded-xl border-slate-300 text-sm"
                placeholder="Optional">
        </div>

        <div class="lg:col-span-2">
            <label class="mb-1 block text-xs font-bold text-slate-700">
                Premium Description
            </label>
            <textarea
                name="placement_description"
                rows="3"
                maxlength="1000"
                class="w-full rounded-xl border-slate-300 text-sm"
                placeholder="Optional">{{ old('placement_description') }}</textarea>
        </div>
    </div>
</div>


            {{-- ESUBIZ_PRODUCT_PREVIEW_FEATURES_V1 --}}
            <div class="mt-6 grid gap-6 lg:grid-cols-2">

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="text-sm font-bold text-slate-900">
                                Preview Photo
                            </div>
                            <p class="mt-1 text-xs text-slate-500">
                                Product image shown with this Add-on.
                            </p>
                        </div>
                    </div>

                    <label class="mt-4 block cursor-pointer rounded-2xl border-2 border-dashed border-slate-300 bg-white p-4 text-center transition hover:border-blue-400">
                        <img
                            data-preview-image
                            class="mx-auto hidden h-44 w-full rounded-xl object-cover"
                            alt="Preview">

                        <div data-preview-placeholder class="py-8">
                            <div class="text-sm font-bold text-slate-700">
                                Upload Preview Photo
                            </div>
                            <div class="mt-1 text-xs text-slate-400">
                                JPG, PNG or WEBP · Maximum 5MB
                            </div>
                        </div>

                        <input
                            type="file"
                            name="preview_image"
                            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                            class="hidden"
                            data-preview-input>
                    </label>
                </div>

            </div>
            </div>

{{-- ESUBIZ_CREATE_ADDON_UNIVERSAL_PLACEMENT_UI_V1 --}}
    <div class="mt-6 space-y-6">

        {{-- Resource Settings --}}
        <div x-show="addonType === 'allocation'"
             x-cloak
             class="rounded-2xl border border-slate-200 bg-white p-5">

            <div>
                <div class="font-bold text-slate-900">
                    Allocation Resource Settings &amp; Sales Trigger
                </div>

                <p class="mt-1 text-xs text-slate-500">
                    Register the selected Core function as a managed resource when this Add-on extends a measurable limit.
                </p>
            </div>

            <label class="mt-5 flex items-center gap-3 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3">

                <input
                    type="checkbox"
                    name="resource_enabled"
                    value="1"
                    {{ old('resource_enabled') ? 'checked' : '' }}>

                <span>
                    <span class="block text-sm font-bold text-slate-700">
                        Enable as managed resource
                    </span>

                    <span class="block text-xs text-slate-500">
                        Core remains responsible for the default allocation.
                    </span>
                </span>

            </label>

            <div class="mt-5 grid gap-5 md:grid-cols-2">

                <div>
                    <label class="text-sm font-bold text-slate-700">
                        Core Resource
                    </label>

                    <select
                        name="resource_key"
                        class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm">

                        <option value="">
                            Select Core capability
                        </option>

                        @foreach($featureLimits as $capability)
                            @php
                                $createResourceKey =
                                    $capability->limit_key
                                    ?? $capability->capability_key
                                    ?? $capability->key
                                    ?? '';

                                $createResourceType =
                                    $capability->entitlement_type
                                    ?? 'feature';
                            @endphp

                            @if($createResourceKey)
                                <option
                                    value="{{ $createResourceKey }}"
                                    {{ (string) old('resource_key') === (string) $createResourceKey ? 'selected' : '' }}>
                                    {{ ucwords(str_replace(['.', '_'], ' ', $createResourceKey)) }}
                                    — {{ ucfirst($createResourceType) }}
                                </option>
                            @endif
                        @endforeach

                    </select>
                </div>

                <div>
                    <label class="text-sm font-bold text-slate-700">
                        Dashboard Threshold
                    </label>

                    <div class="mt-2 flex gap-2">
                        <input
                            type="number"
                            name="resource_dashboard_threshold"
                            min="0"
                            max="100"
                            step="1"
                            value="{{ old('resource_dashboard_threshold', 80) }}"
                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm">

                        <span class="flex items-center rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm font-bold text-slate-500">
                            %
                        </span>
                    </div>
                </div>

            </div>

            <input type="hidden" name="resource_saas" value="1">
            <input type="hidden" name="resource_off_server" value="0">

            <div class="mt-5 rounded-xl border border-blue-100 bg-blue-50 px-4 py-3">
                <div class="text-sm font-bold text-blue-900">
                    SaaS Core Allocation
                </div>
                <p class="mt-1 text-xs leading-5 text-blue-700">
                    Central controls this allocation. It appears in Website Add-ons
                    and is added to the Core Resource Monitor allowance.
                </p>
            </div>

        </div>


        {{-- Placement --}}
        <div class="rounded-2xl border border-slate-200 bg-white p-5">

            <div class="font-bold text-slate-900">
                Placement
            </div>

            <p class="mt-1 text-xs text-slate-500">
                Select every universal location where this Add-on should connect.
            </p>

            @php
                $createSelectedPlacements =
                    (array) old('addon_placements', []);

                $createPlacements = [
                    'dashboard' => [
                        'label' => 'Dashboard',
                        'description' => 'Resource sales recommendation.',
                    ],
                    'settings' => [
                        'label' => 'Settings',
                        'description' => 'Feature appears in Settings.',
                    ],
                    'page_builder' => [
                        'label' => 'Page Builder',
                        'description' => 'Feature appears in Page Builder.',
                    ],
                    'widgets' => [
                        'label' => 'Widgets',
                        'description' => 'Feature appears in Widgets.',
                    ],
                ];
            @endphp

            <div class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">

                @foreach($createPlacements as $placementKey => $placement)

                    <label
                        x-show="addonType === 'allocation' || @js($placementKey) !== 'dashboard'"
                        x-cloak
                        class="flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-4">

                        <input
                            type="checkbox"
                            name="addon_placements[]"
                            value="{{ $placementKey }}"
                            x-model="placements"
                            {{ in_array(
                                $placementKey,
                                $createSelectedPlacements,
                                true
                            ) ? 'checked' : '' }}
                            class="mt-0.5 h-4 w-4 rounded border-slate-300">

                        <span>
                            <span class="block text-sm font-bold text-slate-700">
                                {{ $placement['label'] }}
                            </span>

                            <span class="mt-1 block text-xs leading-5 text-slate-500">
                                {{ $placement['description'] }}
                            </span>
                        </span>

                    </label>

                @endforeach

            </div>


            {{-- Dashboard Sales Trigger --}}
            <div
                x-show="addonType === 'allocation' && placements.includes('dashboard')"
                x-cloak
                class="mt-5 rounded-xl border border-blue-100 bg-blue-50 p-4">

                <div class="font-bold text-blue-900">
                    Dashboard Sales Trigger
                </div>

                <p class="mt-1 text-xs text-blue-700">
                    Controls when the website owner sees the resource purchase recommendation.
                </p>

                <div class="mt-4 grid gap-4 md:grid-cols-3">

                    <div>
                        <label class="text-sm font-bold text-slate-700">
                            Trigger
                        </label>

                        <select
                            name="dashboard_sales_trigger[condition_type]"
                            x-model="dashboardCondition"
                            class="mt-2 w-full rounded-xl border border-blue-200 bg-white px-4 py-3 text-sm">

                            <option value="resource_threshold">
                                Resource Threshold
                            </option>

                            <option value="limit_reached">
                                Limit Reached
                            </option>

                        </select>
                    </div>

                    <div>
                        <label class="text-sm font-bold text-slate-700">
                            Resource
                        </label>

                        <input
                            type="text"
                            name="dashboard_sales_trigger[resource_key]"
                            value="{{ old(
                                'dashboard_sales_trigger.resource_key'
                            ) }}"
                            placeholder="e.g. storage or bandwidth"
                            class="mt-2 w-full rounded-xl border border-blue-200 bg-white px-4 py-3 text-sm">
                    </div>

                    <div
                        x-show="dashboardCondition === 'resource_threshold'"
                        x-cloak>

                        <label class="text-sm font-bold text-slate-700">
                            Threshold
                        </label>

                        <div class="mt-2 flex gap-2">
                            <input
                                type="number"
                                name="dashboard_sales_trigger[threshold_percentage]"
                                min="0"
                                max="100"
                                step="1"
                                value="{{ old(
                                    'dashboard_sales_trigger.threshold_percentage',
                                    80
                                ) }}"
                                class="w-full rounded-xl border border-blue-200 bg-white px-4 py-3 text-sm">

                            <span class="flex items-center rounded-xl border border-blue-200 bg-white px-3 text-sm font-bold text-slate-500">
                                %
                            </span>
                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <div class="mt-6"
         x-show="addonType === 'allocation'"
         x-cloak>
        <label class="text-sm font-semibold text-slate-700">Allocation Unit</label>
        <input
            name="allocation_unit"
            type="text"
            placeholder="e.g. g, GB, users, records"
            class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm">
        <p class="mt-1 text-xs text-slate-500">
            Enter the unit exactly as it should appear in the marketplace.
        </p>
    </div>

    <div class="mt-6">
        <label class="text-sm font-semibold text-slate-700">Description</label>
        <textarea name="description" rows="3"
                  class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm"></textarea>
    </div>

    <div class="mt-8 grid gap-6 md:grid-cols-2">

        <div class="rounded-2xl border border-blue-100 bg-blue-50 p-5">
            <div class="font-bold text-blue-900">SaaS Rental / Subscription</div>

            <label class="mt-4 flex items-center gap-2 text-sm">
                <input type="hidden" name="saas_available" value="0">
                <input name="saas_available" value="1" type="checkbox">
                Available to SaaS websites
            </label>

            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                <input name="saas_price" type="number" step="0.01" min="0"
                       placeholder="Price"
                       class="rounded-xl border border-blue-200 px-3 py-2 text-sm">

                <input name="saas_currency" value="NGN" maxlength="3"
                       class="rounded-xl border border-blue-200 px-3 py-2 text-sm">
            </div>

            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                <input name="saas_billing_period" type="number" min="1"
                       placeholder="Period"
                       class="rounded-xl border border-blue-200 px-3 py-2 text-sm">

                <select name="saas_billing_interval"
                        class="rounded-xl border border-blue-200 px-3 py-2 text-sm">
                    <option value="month">Month</option>
                    <option value="year">Year</option>
                    <option value="week">Week</option>
                </select>
            </div>
        </div>

        <div x-show="addonType === 'package'"
             x-cloak
             class="rounded-2xl border border-violet-100 bg-violet-50 p-5">
            <div class="font-bold text-violet-900">Off-server License</div>

            <label class="mt-4 flex items-center gap-2 text-sm">
                <input type="hidden" name="off_server_available" value="0">
                <input name="off_server_available" value="1" type="checkbox">
                Available to off-server websites
            </label>

            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                <input name="off_server_price" type="number" step="0.01" min="0"
                       placeholder="One-off price"
                       class="rounded-xl border border-violet-200 px-3 py-2 text-sm">

                <input name="off_server_currency" value="NGN" maxlength="3"
                       class="rounded-xl border border-violet-200 px-3 py-2 text-sm">
            </div>
        </div>

    </div>


    <div class="mt-6 flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4">
        <div>
            <div class="text-sm font-bold text-slate-800">Marketplace Featured</div>
            <div class="mt-1 text-xs text-slate-500">
                Feature this product prominently in the Marketplace.
            </div>
        </div>

        <label class="esu-featured-toggle" aria-label="Marketplace Featured">
            <input type="hidden" name="featured" value="0">
            <input type="checkbox"
                   name="featured"
                   value="1"
                   class="peer sr-only"
                   checked>
            <span class="esu-featured-toggle-track"></span>
        </label>
    </div>

    <div class="mt-6 flex justify-end">
        <button type="button" data-product-form-cancel
                onclick="document.getElementById('addon-form').classList.add('hidden')"
                style="margin-right:12px"
                class="rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
            Cancel
        </button>
        <button type="submit"
                class="rounded-xl bg-slate-900 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">
            Save Add-on
        </button>
    </div>
</form>
                </div>
            </div>
        </div>
    </div>


{{-- BUNDLE FORM --}}
    <div id="bundle-form"
         class="fixed inset-0 z-[100] hidden overflow-y-auto bg-slate-950/50 backdrop-blur-sm"
         onclick="if(event.target === this) this.classList.add('hidden')">
        <div class="flex min-h-full w-full items-start justify-center p-3 sm:p-6">
            <div class="my-3 w-full max-w-5xl overflow-visible rounded-3xl border border-slate-200 bg-white shadow-2xl sm:my-6">

                <div class="relative z-10 flex shrink-0 items-start justify-between gap-4 border-b border-slate-100 bg-white px-5 py-4 sm:px-6 sm:py-5">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Create Add-on Bundle</h2>
                        <p class="mt-1 text-sm text-slate-500">
                            Package existing Core add-ons into one product.
                        </p>
                    </div>

                    <button type="button"
                            onclick="document.getElementById('bundle-form').classList.add('hidden')"
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-slate-200 text-xl leading-none text-slate-500 hover:bg-slate-50 hover:text-slate-900"
                            aria-label="Close">
                        &times;
                    </button>
                </div>

                <div class="min-h-0 flex-1 overflow-x-hidden overflow-y-auto overscroll-contain">

        <form method="POST"
              action="{{ route('admin.core-addons.bundles.store') }}"
              enctype="multipart/form-data"
              class="min-w-0 w-full max-w-full p-4 sm:p-6">
            @csrf

            <div class="grid gap-5 md:grid-cols-2">

                <div>
                    <label class="text-sm font-semibold text-slate-700">Bundle Name</label>
                    <input name="name" required
                           class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm">
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Generated Bundle Key</label>
                    <input id="bundle-generated-key"
                           name="key"
                           readonly
                           required
                           class="mt-2 w-full rounded-xl border border-slate-300 bg-slate-50 px-4 py-3 text-sm">
                </div>


<div class="space-y-2" data-marketplace-category-field>
    <label class="block text-sm font-semibold text-slate-700">
        Marketplace Categories
    </label>

    @php
        $esuMarketplaceCategories = \App\Models\MarketplaceCategory::query()
            ->active()
            ->ordered()
            ->get()
            ->filter(
                fn ($category) =>
                    $category->supportsProductType('addon')
                    || $category->supportsProductType('addons')
                    || $category->supportsProductType('bundle')
                    || $category->supportsProductType('bundles')
            )
            ->values();

        $esuSelectedMarketplaceCategories = collect(
            old('marketplace_category_ids', [])
        )->map(fn ($id) => (int) $id)->all();
    @endphp

    <div class="relative" data-category-picker>
        <button
            type="button"
            data-category-picker-button
            class="flex min-h-[46px] w-full items-center justify-between gap-3 rounded-xl border border-slate-300 bg-white px-4 py-3 text-left text-sm text-slate-700"
        >
            <span data-category-picker-label>Select Marketplace Categories</span>
            <span aria-hidden="true">⌄</span>
        </button>

        <div
            data-category-picker-menu
            class="absolute left-0 right-0 z-50 mt-2 hidden overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl"
        >
            <div class="border-b border-slate-100 p-3">
                <input
                    type="search"
                    data-category-search
                    placeholder="Search categories..."
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                >
            </div>

            <div class="max-h-64 overflow-y-auto p-2">
                @forelse($esuMarketplaceCategories as $esuCategory)
                    <label
                        data-category-option
                        data-category-name="{{ strtolower($esuCategory->name) }}"
                        class="flex cursor-pointer items-center gap-3 rounded-lg px-3 py-2 hover:bg-slate-50"
                    >
                        <input
                            type="checkbox"
                            name="marketplace_category_ids[]"
                            value="{{ $esuCategory->id }}"
                            @checked(in_array((int) $esuCategory->id, $esuSelectedMarketplaceCategories, true))
                            class="h-4 w-4 rounded border-slate-300"
                        >
                        <span class="text-sm font-medium text-slate-700">
                            {{ $esuCategory->name }}
                        </span>
                    </label>
                @empty
                    <div class="px-3 py-4 text-sm text-slate-500">
                        No active Marketplace Categories are available.
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <p class="text-xs text-slate-500">
        Products appear under the selected category tabs in the Marketplace.
    </p>
</div>


            </div>

            <div class="mt-6">
                <label class="text-sm font-semibold text-slate-700">Description</label>
                <textarea name="description" rows="3"
                          class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm"></textarea>
            </div>

            {{-- ESUBIZ_PRODUCT_PREVIEW_FEATURES_V1 --}}
            <div class="mt-6 grid gap-6 lg:grid-cols-2">

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="text-sm font-bold text-slate-900">
                                Preview Photo
                            </div>
                            <p class="mt-1 text-xs text-slate-500">
                                Product image shown with this Add-on.
                            </p>
                        </div>
                    </div>

                    <label class="mt-4 block cursor-pointer rounded-2xl border-2 border-dashed border-slate-300 bg-white p-4 text-center transition hover:border-blue-400">
                        <img
                            data-preview-image
                            class="mx-auto hidden h-44 w-full rounded-xl object-cover"
                            alt="Preview">

                        <div data-preview-placeholder class="py-8">
                            <div class="text-sm font-bold text-slate-700">
                                Upload Preview Photo
                            </div>
                            <div class="mt-1 text-xs text-slate-400">
                                JPG, PNG or WEBP · Maximum 5MB
                            </div>
                        </div>

                        <input
                            type="file"
                            name="preview_image"
                            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                            class="hidden"
                            data-preview-input>
                    </label>
                </div>

            </div>

            <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-4 sm:p-5"
                 data-bundle-addon-selector>

                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <div class="font-bold text-slate-900">
                            Add-ons in this Bundle
                        </div>
                        <p class="mt-1 text-xs text-slate-500">
                            Search and select the Add-ons included in this bundle.
                        </p>
                    </div>

                    <div class="shrink-0 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-600">
                        <span data-bundle-selected-count>0</span> selected
                    </div>
                </div>

                <div class="relative mt-4">
                    <input type="search"
                           data-bundle-addon-search
                           placeholder="Search Add-ons..."
                           autocomplete="off"
                           class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 pr-10 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100">

                    <div class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-slate-400">
                        ⌕
                    </div>
                </div>

                <div class="mt-3 max-h-72 overflow-y-auto overscroll-contain rounded-xl border border-slate-200 bg-white"
                     data-bundle-addon-list>

                    @forelse($addons as $addon)
                        <div class="border-b border-slate-100 p-3 last:border-b-0"
                             data-bundle-addon-row
                             data-search="{{ strtolower($addon->name . ' ' . $addon->key) }}">

                            <div class="flex min-w-0 items-center gap-3">

                                <input type="checkbox"
                                       name="items[{{ $addon->id }}][addon_id]"
                                       value="{{ $addon->id }}"
                                       data-bundle-addon-checkbox
                                       class="h-4 w-4 shrink-0 rounded border-slate-300 text-blue-600 focus:ring-blue-500">

                                <label class="min-w-0 flex-1 cursor-pointer">
                                    <span class="block truncate text-sm font-semibold text-slate-800">
                                        {{ $addon->name }}
                                    </span>

                                    <span class="block truncate text-xs text-slate-400">
                                        {{ $addon->key }}
                                    </span>
                                </label>

                                <div class="hidden w-32 shrink-0"
                                     data-bundle-allocation-wrap>
                                    <input type="number"
                                           name="items[{{ $addon->id }}][allocation]"
                                           min="0"
                                           step="0.01"
                                           placeholder="Allocation"
                                           class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs outline-none focus:border-blue-500">
                                </div>

                            </div>
                        </div>
                    @empty
                        <div class="p-5 text-sm text-slate-500">
                            Create Add-ons before creating a bundle.
                        </div>
                    @endforelse

                    <div class="hidden p-5 text-center text-sm text-slate-400"
                         data-bundle-no-results>
                        No matching Add-ons.
                    </div>
                </div>

            </div>
            <div class="mt-6 grid gap-6 md:grid-cols-2">

                <div class="rounded-2xl border border-blue-100 bg-blue-50 p-5">

                    <div class="font-bold text-blue-900">
                        SaaS Rental / Subscription
                    </div>

                    <label class="mt-4 flex items-center gap-2 text-sm">
                        <input type="hidden" name="saas_available" value="0">
                <input name="saas_available" value="1" type="checkbox">
                        Available to SaaS websites
                    </label>

                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <input name="saas_price" type="number" step="0.01" min="0"
                               placeholder="Price"
                               class="rounded-xl border border-blue-200 px-3 py-2 text-sm">

                        <input name="saas_currency" value="NGN" maxlength="3"
                               class="rounded-xl border border-blue-200 px-3 py-2 text-sm">
                    </div>

                    <div class="mt-3 grid grid-cols-2 gap-3">
                        <input name="saas_billing_period" type="number" min="1"
                               placeholder="Period"
                               class="rounded-xl border border-blue-200 px-3 py-2 text-sm">

                        <select name="saas_billing_interval"
                                class="rounded-xl border border-blue-200 px-3 py-2 text-sm">
                            <option value="month">Month</option>
                            <option value="year">Year</option>
                        </select>
                    </div>

                </div>

                <div class="rounded-2xl border border-violet-100 bg-violet-50 p-5">

                    <div class="font-bold text-violet-900">
                        Off-server License
                    </div>

                    <label class="mt-4 flex items-center gap-2 text-sm">
                        <input type="hidden" name="off_server_available" value="0">
                <input name="off_server_available" value="1" type="checkbox">
                        Available to off-server websites
                    </label>

                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <input name="off_server_price" type="number" step="0.01" min="0"
                               placeholder="One-off price"
                               class="rounded-xl border border-violet-200 px-3 py-2 text-sm">

                        <input name="off_server_currency" value="NGN" maxlength="3"
                               class="rounded-xl border border-violet-200 px-3 py-2 text-sm">
                    </div>

                </div>

            </div>


    <div class="mt-6 flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-5 py-4">
        <div>
            <div class="text-sm font-bold text-slate-800">Marketplace Featured</div>
            <div class="mt-1 text-xs text-slate-500">
                Feature this product prominently in the Marketplace.
            </div>
        </div>

        <label class="esu-featured-toggle" aria-label="Marketplace Featured">
            <input type="hidden" name="featured" value="0">
            <input type="checkbox"
                   name="featured"
                   value="1"
                   class="peer sr-only"
                   data-bundle-featured
                   checked>
            <span class="esu-featured-toggle-track"></span>
        </label>
    </div>

            <div class="mt-6 flex justify-end">
                <button type="button" data-product-form-cancel
                onclick="document.getElementById('bundle-form').classList.add('hidden')"
                style="margin-right:12px"
                class="rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 hover:bg-slate-50">
            Cancel
        </button>
        <button type="submit"
                        class="rounded-xl bg-slate-900 px-6 py-3 text-sm font-semibold text-white">
                    Save Bundle
                </button>
            </div>

        </form>
                </div>
            </div>
        </div>
    </div>

{{-- ADD-ON / BUNDLE FORM BEHAVIOUR --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const bundleName = document.querySelector('#bundle-form input[name="name"]');
    const bundleKey = document.getElementById('bundle-generated-key');

    function normalizeBundleKey(value) {
        return String(value || '')
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '_')
            .replace(/^_|_$/g, '');
    }

    if (bundleName && bundleKey) {
        bundleName.addEventListener('input', function () {
            bundleKey.value =
                normalizeBundleKey(this.value) + (this.value.trim() ? '_bundle' : '');
        });
    }

    const featureSelect = document.getElementById('addon-core-feature');
    const functionsBox = document.getElementById('addon-functions');
    const allocationBox = document.getElementById('addon-allocation-box');
    const allocationList = document.getElementById('addon-allocation-list');
    const entitlementInput = document.getElementById('addon-entitlement-type');
    const entitlementBadge = document.getElementById('addon-entitlement-badge');
    const generatedKey = document.getElementById('addon-generated-key');
    const generatedName = document.getElementById('addon-display-name');

    if (!featureSelect || !functionsBox) return;

    const limits = @json($featureLimits);

    function normalize(value) {
        return String(value || '')
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '_')
            .replace(/^_|_$/g, '');
    }

    function featureKey(row) {
        return row.feature_key || row.feature || row.core_feature_key ||
               row.feature_id || row.core_feature_id;
    }

    function limitKey(row) {
        return row.key || row.limit_key || row.name;
    }

    function typeOf(row) {
        return row.type || row.entitlement_type || 'feature';
    }

    function unitOf(row) {
        const type = typeOf(row);
        if (row.unit) return row.unit;
        if (type === 'credits') return 'credits';
        if (type === 'storage' || type === 'bandwidth') return 'GB';
        return 'records';
    }

    function defaultOf(row) {
        return row.default ?? row.default_value ?? row.default_limit ??
               row.limit ?? null;
    }

    function supportsUnlimited(row) {
        return typeOf(row) === 'unlimited' ||
               row.is_unlimited == 1 || row.is_unlimited === true ||
               row.allow_unlimited == 1 || row.allow_unlimited === true ||
               row.supports_unlimited == 1 || row.supports_unlimited === true ||
               row.unlimited == 1 || row.unlimited === true;
    }

    function title(value) {
        return String(value || '')
            .replace(/_/g, ' ')
            .replace(/\b\w/g, c => c.toUpperCase());
    }

    function renderAllocations() {
        const selected = [...functionsBox.querySelectorAll('.addon-function:checked')];
        allocationList.innerHTML = '';

        if (!selected.length) {
            allocationBox.classList.add('hidden');
            return;
        }

        allocationBox.classList.remove('hidden');

        selected.forEach(input => {
            const key = input.value;
            const type = input.dataset.type;
            const unit = input.dataset.unit;
            const coreDefault = input.dataset.coreDefault || '—';
            const unlimited = input.dataset.unlimited === '1';

            const card = document.createElement('div');
            card.className = 'rounded-2xl border border-slate-200 bg-slate-50 p-5';

            card.innerHTML = `
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <div class="text-sm font-bold text-slate-900">
                            ${title(key)}
                        </div>

                        <div class="mt-2 flex flex-wrap gap-2">
                            <span class="rounded-full bg-blue-50 px-3 py-1 text-[11px] font-semibold text-blue-700">
                                Entitlement: ${title(type)}
                            </span>

                            <span class="rounded-full bg-slate-100 px-3 py-1 text-[11px] font-semibold text-slate-600">
                                Core default: ${coreDefault} ${unit}
                            </span>

                            <span class="rounded-full bg-slate-100 px-3 py-1 text-[11px] font-semibold text-slate-600">
                                Unit: ${unit}
                            </span>
                        </div>
                    </div>

                    <div class="w-full lg:w-80">
                        <label class="block text-xs font-bold text-slate-700">
                            Add-on Allocation
                        </label>

                        <div class="mt-2 flex gap-2">
                            <input type="number"
                                   min="0"
                                   step="1"
                                   name="capability_allocations[${key}]"
                                   value="0"
                                   class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm">

                            <div class="flex min-w-24 items-center justify-center rounded-xl border border-slate-200 bg-white px-3 text-xs font-semibold text-slate-500">
                                ${unit}
                            </div>
                        </div>

                        <label class="mt-3 flex cursor-pointer items-center gap-3 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3">
                            <input
                                type="checkbox"
                                name="capability_unlimited[${key}]"
                                value="1"
                                class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                            >
                            <span>
                                <span class="block text-xs font-black text-slate-900">
                                    Unlimited allocation
                                </span>
                                <span class="block text-[11px] text-slate-500">
                                    No quantity limit for this function from this add-on.
                                </span>
                            </span>
                        </label>

                        <p class="mt-2 text-[11px] text-slate-400">
                            Every additional purchase/rental adds this amount again.
                        </p>
                    </div>
                </div>
            `;

            allocationList.appendChild(card);
        });
    }

    featureSelect.addEventListener('change', function () {
        const feature = this.value;

        functionsBox.innerHTML = '';
        allocationList.innerHTML = '';
        allocationBox.classList.add('hidden');

        if (!feature) {
            functionsBox.innerHTML = `
                <div class="rounded-xl border border-dashed border-slate-200 p-4 text-sm text-slate-400 md:col-span-2">
                    Select a Core feature first.
                </div>`;
            return;
        }

        const rows = limits.filter(row =>
            normalize(featureKey(row)) === normalize(feature)
        );

        if (!rows.length) {
            functionsBox.innerHTML = `
                <div class="rounded-xl border border-dashed border-slate-200 p-4 text-sm text-slate-400 md:col-span-2">
                    This Core feature has no separately registered extension functions.
                </div>`;

            entitlementInput.value = 'feature';
            entitlementBadge.textContent = 'Feature entitlement';
            entitlementBadge.classList.remove('hidden');
        } else {
            rows.forEach(row => {
                const key = limitKey(row);
                const type = typeOf(row);
                const unit = unitOf(row);
                const coreDefault = defaultOf(row);
                const unlimited = supportsUnlimited(row);

                const wrapper = document.createElement('label');
                wrapper.className =
                    'flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4 hover:border-blue-300 hover:bg-blue-50';

                wrapper.innerHTML = `
                    <input type="checkbox"
                           name="capabilities[]"
                           value="${key}"
                           data-type="${type}"
                           data-unit="${unit}"
                           data-core-default="${coreDefault ?? ''}"
                           data-unlimited="${unlimited ? '1' : '0'}"
                           class="addon-function mt-1 rounded border-slate-300">

                    <span class="min-w-0 flex-1">
                        <span class="block text-sm font-semibold text-slate-800">
                            ${title(row.name || key)}
                        </span>

                        <span class="mt-1 block text-xs text-slate-500">
                            Core default:
                            <strong>${coreDefault ?? '—'}</strong>
                            ${unit}
                        </span>

                        <span class="mt-1 block text-xs text-slate-400">
                            Entitlement: ${title(type)}
                        </span>
                    </span>
                `;

                functionsBox.appendChild(wrapper);
            });

            entitlementBadge.textContent = 'Entitlement inherited from Core';
            entitlementBadge.classList.remove('hidden');
        }

        generatedKey.value = normalize(feature) + '_' + Date.now();
    });

    functionsBox.addEventListener('change', function () {
        const selected = [...functionsBox.querySelectorAll('.addon-function:checked')];
        const types = [...new Set(selected.map(input => input.dataset.type))];

        entitlementInput.value = types.length === 1 ? types[0] : 'feature';
        renderAllocations();
    });

    generatedName.addEventListener('input', function () {
        const feature = featureSelect.value;
        if (feature && this.value.trim()) {
            generatedKey.value =
                normalize(feature) + '_' + normalize(this.value);
        }
    });
});
</script>


{{-- ESUBIZ_CENTRAL_PRODUCT_FORM_VIEWPORT_V1 --}}
<style>
#addon-form#addon-form,
#bundle-form#bundle-form {
    left: var(--product-form-left, 0px) !important;
    top: var(--product-form-top, 0px) !important;
    right: 0 !important;
    bottom: 0 !important;
    width: auto !important;
    height: auto !important;
    max-height: none !important;
    padding: 16px !important;
    overflow-x: hidden !important;
    overflow-y: auto !important;
    box-sizing: border-box;
}
#addon-form#addon-form > div,
#bundle-form#bundle-form > div {
    display: flex !important;
    align-items: flex-start !important;
    justify-content: center !important;
    width: 100% !important;
    min-height: 100% !important;
    height: auto !important;
    padding: 0 !important;
}
#addon-form#addon-form > div > div,
#bundle-form#bundle-form > div > div {
    width: 100% !important;
    max-width: 1024px !important;
    height: auto !important;
    max-height: none !important;
    margin: 0 auto !important;
    overflow: visible !important;
}
#addon-form#addon-form > div > div > div:last-child,
#bundle-form#bundle-form > div > div > div:last-child {
    overflow: visible !important;
    flex: none !important;
}
@media (max-width: 639px) {
    #addon-form#addon-form,
    #bundle-form#bundle-form { padding: 12px !important; }
    #addon-form#addon-form form { padding: 16px !important; }
}
</style>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modals = ['addon-form', 'bundle-form']
        .map(id => document.getElementById(id)).filter(Boolean);

    modals.forEach(modal => {
        if (modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }
    });

    function updateViewport() {
        const desktop = window.matchMedia('(min-width: 1024px)').matches;
        let left = 0;
        let top = 0;
        if (desktop) {
            document.querySelectorAll('aside').forEach(sidebar => {
                const rect = sidebar.getBoundingClientRect();
                if (rect.width > 0 && rect.left <= 1 && rect.right > 0) {
                    left = Math.max(left, rect.right);
                }
            });
            document.querySelectorAll('header').forEach(header => {
                const rect = header.getBoundingClientRect();
                if (rect.width > 0 && rect.top <= 1 && rect.bottom > 0) {
                    top = Math.max(top, rect.bottom);
                }
            });
        }
        modals.forEach(modal => {
            modal.style.setProperty('--product-form-left', left + 'px');
            modal.style.setProperty('--product-form-top', top + 'px');
        });
    }
    updateViewport();
    window.addEventListener('resize', updateViewport);
});
</script>

@endsection


<script>
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('[data-preview-input]').forEach(function (input) {
        input.addEventListener('change', function () {
            const scope = input.closest('label');
            const image = scope?.querySelector('[data-preview-image]');
            const placeholder = scope?.querySelector('[data-preview-placeholder]');
            const file = input.files?.[0];

            if (!file || !image) return;

            const reader = new FileReader();

            reader.onload = function (event) {
                image.src = event.target.result;
                image.classList.remove('hidden');
                placeholder?.classList.add('hidden');
            };

            reader.readAsDataURL(file);
        });
    });



});
</script>


{{-- ESUBIZ_MARKETPLACE_CATEGORY_PICKER_V565B --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-category-picker]').forEach(function (picker) {
        const button = picker.querySelector('[data-category-picker-button]');
        const menu = picker.querySelector('[data-category-picker-menu]');
        const search = picker.querySelector('[data-category-search]');
        const label = picker.querySelector('[data-category-picker-label]');

        if (!button || !menu || !label) return;

        const refresh = function () {
            const checked = Array.from(
                picker.querySelectorAll(
                    'input[name="marketplace_category_ids[]"]:checked'
                )
            );

            const names = checked.map(function (input) {
                const option = input.closest('[data-category-option]');
                const name = option ? option.querySelector('span') : null;
                return name ? name.textContent.trim() : '';
            }).filter(Boolean);

            label.textContent = names.length
                ? names.join(', ')
                : 'Select Marketplace Categories';
        };

        button.addEventListener('click', function () {
            menu.classList.toggle('hidden');
        });

        picker.querySelectorAll(
            'input[name="marketplace_category_ids[]"]'
        ).forEach(function (input) {
            input.addEventListener('change', refresh);
        });

        if (search) {
            search.addEventListener('input', function () {
                const term = search.value.trim().toLowerCase();

                picker.querySelectorAll('[data-category-option]')
                    .forEach(function (option) {
                        option.classList.toggle(
                            'hidden',
                            term !== ''
                            && !option.dataset.categoryName.includes(term)
                        );
                    });
            });
        }

        refresh();
    });
});
</script>

{{-- ESUBIZ_BUNDLE_MODAL_BODY_PORTAL_V611 --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const bundleModal = document.getElementById('bundle-form');

    if (bundleModal && bundleModal.parentElement !== document.body) {
        document.body.appendChild(bundleModal);
    }
});
</script>

{{-- ESUBIZ_BUNDLE_CENTRAL_LAYOUT_V615 --}}
<style>
    /*
     * Bundle modal must live inside the visible Central workspace,
     * not underneath the permanent desktop sidebar.
     */
    @media (min-width: 1024px) {
        #bundle-form {
            left: 300px !important;
            width: calc(100vw - 300px) !important;
            right: auto !important;
        }
    }

    @media (max-width: 1023px) {
        #bundle-form {
            left: 0 !important;
            right: 0 !important;
            width: 100vw !important;
        }
    }

    #bundle-form > div {
        box-sizing: border-box;
        width: 100%;
    }

    #bundle-form > div > div {
        box-sizing: border-box;
        max-width: 1024px;
        margin-left: auto;
        margin-right: auto;
    }
</style>

{{-- ESUBIZ_BUNDLE_FULL_VERTICAL_SCROLL_V616 --}}
<style>
    #bundle-form {
        overflow-x: hidden !important;
        overflow-y: auto !important;
        overscroll-behavior: contain;
        padding-top: 24px !important;
        padding-bottom: 48px !important;
        box-sizing: border-box;
    }

    #bundle-form > div {
        display: block !important;
        min-height: 0 !important;
        height: auto !important;
        padding: 0 16px !important;
    }

    #bundle-form > div > div {
        width: 100% !important;
        height: auto !important;
        max-height: none !important;
        margin-top: 0 !important;
        margin-bottom: 0 !important;
        overflow: visible !important;
    }

    #bundle-form form {
        height: auto !important;
        max-height: none !important;
        overflow: visible !important;
    }

    @media (max-width: 639px) {
        #bundle-form {
            padding-top: 12px !important;
            padding-bottom: 32px !important;
        }

        #bundle-form > div {
            padding: 0 12px !important;
        }
    }
</style>

{{-- ESUBIZ_BUNDLE_CENTRAL_HEADER_OFFSET_V617 --}}
<style>
    @media (min-width: 1024px) {
        #bundle-form {
            top: 80px !important;
            bottom: 0 !important;
            height: calc(100dvh - 80px) !important;
            max-height: calc(100dvh - 80px) !important;
            padding-top: 16px !important;
            padding-bottom: 48px !important;
        }
    }

    @media (max-width: 1023px) {
        #bundle-form {
            top: 0 !important;
            bottom: 0 !important;
            height: 100dvh !important;
            max-height: 100dvh !important;
        }
    }
</style>
