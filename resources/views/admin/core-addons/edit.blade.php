
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

<div class="min-h-screen bg-slate-100 px-6 py-8">

    <div class="mx-auto max-w-6xl">

        <div class="mb-8 flex items-center justify-between">

            <div>
                <div class="text-xs font-black uppercase tracking-widest text-blue-600">
                    Core Commerce
                </div>

                <h1 class="mt-2 text-3xl font-black text-slate-900">
                    Edit Add-on
                </h1>

                <p class="mt-2 text-sm text-slate-500">
                    Configure the functions and allocation this add-on unlocks.
                </p>
            </div>

            <a href="{{ route('admin.core-addons.index') }}"
               class="rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-bold text-slate-700">
                Back
            </a>

        </div>


        <form method="POST"
              action="{{ route('admin.core-addons.update', $addon->id) }}"
              class="space-y-6"
              enctype="multipart/form-data"
              x-data="{
                  addonType: @js(old('implementation_type', $addon->implementation_type ?? 'allocation')),
                  placements: @js(old('addon_placements', array_values($selectedPlacements ?? []))),
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
            @method('PUT')

            {{-- ESUBIZ_ADDON_IMPLEMENTATION_TYPE_EDIT_V1 --}}
            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
                 >

                <input type="hidden"
                       name="implementation_type"
                       :value="addonType">

                <div>
                    <h2 class="text-lg font-black text-slate-900">Add-on Type</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Choose whether this Add-on extends a SaaS Core allowance or installs functionality.
                    </p>
                </div>

                <div class="mt-5 flex items-center gap-4">
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

                <div class="mt-4 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-xs text-slate-600">
                    <span x-show="addonType === 'allocation'">
                        SaaS Core allowance. Central controls the resource allocation and Dashboard Trigger.
                    </span>

                    <span x-show="addonType === 'package'" x-cloak>
                        Installable functionality for SaaS and/or off-server Core.
                    </span>
                </div>
            </div>


            {{-- ESUBIZ_PACKAGE_ADDON_SOURCE_EDIT_V1 --}}
            <div x-show="addonType === 'package'"
                 x-cloak>
                @include('admin.components.product-source-toggle', [
                    'product' => 'Add-on',
                    'mode' => 'folder',
                    'folderName' => 'package_name',
                    'folderValue' => old('package_name', $addon->package_name ?? ''),
                    'folderPlaceholder' => 'Search/select Add-on package folder',
                    'uploadName' => 'package_file',
                    'accept' => '.zip,application/zip',
                    'help' => 'Package source applies only to Package Add-ons. Allocation Add-ons do not install package files.',
                ])
            </div>

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

                <h2 class="text-lg font-black text-slate-900">
                    Add-on Details
                </h2>

                <div class="mt-5 grid gap-5 md:grid-cols-2">

                    <div class="md:col-span-2">

                        <label class="text-sm font-bold text-slate-700">
                            Add-on Name
                        </label>

                        <input
                            type="text"
                            name="name"
                            value="{{ $addon->name }}"
                            required
                            class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3">

                    </div>


                    <div class="md:col-span-2">

                        <label class="text-sm font-bold text-slate-700">
                            Description
                        </label>

                        <textarea
                            name="description"
                            rows="4"
                            class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3">{{ $addon->description }}</textarea>

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

        $esuSavedMarketplaceCategories = $addon->catalog_product_id
            ? DB::table('catalog_product_marketplace_category')
                ->where('catalog_product_id', $addon->catalog_product_id)
                ->pluck('marketplace_category_id')
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all()
            : [];

        $esuSelectedMarketplaceCategories = collect(
            old('marketplace_category_ids', $esuSavedMarketplaceCategories)
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



                    {{-- ESUBIZ_PRODUCT_PREVIEW_FEATURES_V1 --}}
                    @php

                        $savedPreviewUrl = !empty($addon->preview_image)
        ? route('marketplace.addons.preview', [
            'type' => 'addon',
            'id' => $addon->id,
        ])
        : null;
                    @endphp

                    <div class="md:col-span-2 grid gap-6 lg:grid-cols-2">

                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                            <div class="text-sm font-bold text-slate-900">
                                Preview Photo
                            </div>

                            <p class="mt-1 text-xs text-slate-500">
                                Product image shown with this Add-on.
                            </p>

                            <label class="mt-4 block cursor-pointer rounded-2xl border-2 border-dashed border-slate-300 bg-white p-4 text-center transition hover:border-blue-400">

                                <img
                                    data-preview-image
                                    src="{{ $savedPreviewUrl ?: '' }}"
                                    class="mx-auto h-44 w-full rounded-xl object-cover {{ $savedPreviewUrl ? '' : 'hidden' }}"
                                    alt="Preview">

                                <div
                                    data-preview-placeholder
                                    class="py-8 {{ $savedPreviewUrl ? 'hidden' : '' }}">
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

                    <div class="md:col-span-2 grid grid-cols-1 gap-4 md:grid-cols-2">

                        <label class="flex items-center gap-3">
                            <input type="hidden" name="saas_available" value="0">
                            <input type="checkbox" name="saas_available" value="1"
                                {{ old('saas_available', $addon->saas_available) ? 'checked' : '' }}>
                            <span class="text-sm font-bold text-slate-700">Available for SaaS websites</span>
                        </label>

                        <label class="flex items-center gap-3"
 x-show="addonType === 'package'" x-cloak>
                            <input type="hidden" name="off_server_available" value="0">
                            <input type="checkbox" name="off_server_available" value="1"
                                {{ old('off_server_available', $addon->off_server_available) ? 'checked' : '' }}>
                            <span class="text-sm font-bold text-slate-700">Available for off-server websites</span>
                        </label>

                    </div>


                    <div>

                        <label class="text-sm font-bold text-slate-700">
                            SaaS Price
                        </label>

                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            name="saas_price"
                            value="{{ old('saas_price', $addon->saas_price ?? 0) }}"
                            class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3">

                    </div>


                    <div>

                        <label class="text-sm font-bold text-slate-700">
                            SaaS Period
                        </label>

                        <input
                            type="text"
                            name="saas_billing_period"
                        value="{{ old('saas_billing_period', $addon->saas_billing_period) }}"
                        class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3">

                    </div>


                    <div
 x-show="addonType === 'allocation'" x-cloak>

                        <label class="text-sm font-bold text-slate-700">
                            Allocation Unit
                        </label>

                        <input
                            type="text"
                            name="allocation_unit"
                            value="{{ old('allocation_unit', $addon->allocation_unit) }}"
                            placeholder="e.g. g, GB, records, users"
                            class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3">

                    </div>


                    <div
 x-show="addonType === 'package'" x-cloak>

                        <label class="text-sm font-bold text-slate-700">
                            Off-server License Price
                        </label>

                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            name="off_server_price"
                            value="{{ $addon->off_server_price ?? '' }}"
                            class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3">

                    </div>



                    <div class="flex items-end">
                        <label class="flex w-full items-center justify-between gap-4 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                            <div>
                                <div class="text-sm font-bold text-slate-700">
                                    Marketplace Featured
                                </div>
                                <div class="mt-1 text-xs text-slate-500">
                                    Feature this Add-on prominently in the Marketplace.
                                </div>
                            </div>

                            <label class="esu-featured-toggle" aria-label="Marketplace Featured">
                                <input type="hidden" name="featured" value="0">
                                <input
                                    type="checkbox"
                                    name="featured"
                                    value="1"
                                    class="peer sr-only"
                                    {{ old('featured', $addon->marketplace_featured ?? false) ? 'checked' : '' }}>

                                <span class="esu-featured-toggle-track"></label>
                            </span>
                        </label>
                    </div>

                    <div class="flex items-end">

                        <label class="flex w-full items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">

                            <input
                                type="checkbox"
                                name="is_active"
                                value="1"
                                {{ $addon->is_active ? 'checked' : '' }}>

                            <span class="text-sm font-bold text-slate-700">
                                Active
                            </span>

                        </label>

                    </div>

                </div>

            </div>




            {{-- ESUBIZ_ADDON_RESOURCE_SETTINGS_AND_SALES_TRIGGER_V1 --}}
            @php
                $addonMetadata =
                    json_decode(
                        (string) ($addon->metadata ?? ''),
                        true
                    ) ?: [];

                $salesTrigger =
                    $addonMetadata['sales_trigger']
                    ?? [];

                $triggerDeployments =
                    $salesTrigger['deployment_types']
                    ?? [];

                /*
                 * ESUBIZ_CENTRAL_RESOURCE_SETTINGS_FORM_READ_V4
                 *
                 * The controller is the single authoritative source for
                 * central Resource Settings.
                 *
                 * Legacy Add-on metadata remains fallback only when no
                 * central resource has been resolved.
                 */
                $legacyResourceSettings =
                    $addonMetadata['resource_settings']
                    ?? [];

                $controllerResourceSettings =
                    is_array($resourceSettings ?? null)
                        ? $resourceSettings
                        : [];

                $resourceSettings =
                    $controllerResourceSettings
                        ?: $legacyResourceSettings;

                $configuredResource =
                    $resourceSettings['resource_key']
                    ?? $resourceSettings['resource']
                    ?? $salesTrigger['resource']
                    ?? $legacyResourceSettings['resource']
                    ?? '';

                $resourceDeployments =
                    $resourceSettings['deployment_types']
                    ?? array_values(array_filter([
                        !empty($resourceSettings['saas'])
                            ? 'saas'
                            : null,

                        !empty($resourceSettings['off_server'])
                            ? 'off_server'
                            : null,
                    ]));
            @endphp

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

                <div
 x-show="addonType === 'allocation'" x-cloak>
                    <h2 class="text-lg font-black text-slate-900">
                        Resource Settings &amp; Sales Trigger
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Register this Add-on's Core capability as a managed resource and configure its independent dashboard and sales thresholds.
                    </p>
                </div>


                <div class="mt-6 grid gap-5 md:grid-cols-2">

                    <div class="md:col-span-2">
                        <label class="flex items-center gap-3 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3">

                            {{-- ESUBIZ_RESOURCE_ENABLED_CHECKBOX_FIX_V2 --}}
                            @php
                                /*
                                 * Persisted Resource Settings are authoritative
                                 * on a normal Edit-page load.
                                 *
                                 * Old input is used only when Laravel returns
                                 * this form after validation.
                                 */
                                $resourceEnabledChecked =
                                    session()->hasOldInput()
                                        ? (bool) old(
                                            'resource_enabled',
                                            false
                                        )
                                        : (bool) (
                                            $resourceSettings['enabled']
                                            ?? false
                                        );
                            @endphp

                            <input type="checkbox"
                                   name="resource_enabled"
                                   value="1"
                                   {{ $resourceEnabledChecked ? 'checked' : '' }}>

                            <span>
                                <span class="block text-sm font-bold text-slate-700">
                                    Enable as managed resource
                                </span>

                                <span class="block text-xs text-slate-500">
                                    Makes this capability available to the Resources system. Core remains responsible for its default allocation.
                                </span>
                            </span>

                        </label>
                    </div>


                    <div>
                        <label class="text-sm font-bold text-slate-700">
                            Core Resource
                        </label>

                        <select
                            name="resource_key"
                            class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3">

                            <option value="">
                                Select Core capability
                            </option>

                            @foreach($capabilities as $capability)

                                @php
                                    $resourceKey =
                                        $capability->limit_key
                                        ?? $capability->capability_key
                                        ?? $capability->feature_key
                                        ?? $capability->key
                                        ?? $capability->name;

                                    $resourceType =
                                        $capability->type
                                        ?? $capability->entitlement_type
                                        ?? 'feature';

                                    $selectedResource =
                                        old(
                                            'resource_key',
                                            $configuredResource
                                        );
                                @endphp

                                <option
                                    value="{{ $resourceKey }}"
                                    {{ (string) $selectedResource === (string) $resourceKey ? 'selected' : '' }}
                                >
                                    {{ ucwords(str_replace(['.', '_'], ' ', $resourceKey)) }}
                                    — {{ ucfirst($resourceType) }}
                                </option>

                            @endforeach

                        </select>

                        <p class="mt-2 text-xs text-slate-500">
                            Selecting a capability here does not change its Core default allocation.
                        </p>
                    </div>


                    <div>
                        <label class="text-sm font-bold text-slate-700">
                            Dashboard Display At
                        </label>

                        <div class="mt-2 flex items-center gap-2">

                            <input
                                type="number"
                                name="resource_dashboard_threshold"
                                min="0"
                                max="100"
                                step="1"
                                value="{{ old(
                                    'resource_dashboard_threshold',
                                    $resourceSettings['dashboard_threshold_percentage']
                                        ?? 50
                                ) }}"
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3">

                            <span class="flex h-[50px] items-center rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm font-bold text-slate-500">
                                %
                            </span>

                        </div>

                        <p class="mt-2 text-xs text-slate-500">
                            The resource card appears on the website dashboard when usage reaches this percentage.
                        </p>
                    </div>


                    <div class="md:col-span-2">

                        <div class="grid gap-4 md:grid-cols-2">

                            <label class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">

                                <input type="hidden"
                                       name="resource_saas"
                                       value="0">

                                <input type="checkbox"
                                       name="resource_saas"
                                       value="1"
                                       {{ old(
                                           'resource_saas',
                                           in_array('saas', $resourceDeployments, true)
                                       ) ? 'checked' : '' }}>

                                <span class="text-sm font-bold text-slate-700">
                                    Resource visible for SaaS
                                </span>

                            </label>


                            <label class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">

                                <input type="hidden"
                                       name="resource_off_server"
                                       value="0">

                                <input type="checkbox"
                                       name="resource_off_server"
                                       value="1"
                                       {{ old(
                                           'resource_off_server',
                                           in_array('off_server', $resourceDeployments, true)
                                       ) ? 'checked' : '' }}>

                                <span class="text-sm font-bold text-slate-700">
                                    Resource visible for off-server
                                </span>

                            </label>

                        </div>

                    </div>


                    {{-- ESUBIZ_EDIT_ADDON_PLACEMENT_ADMIN_CONTENT_V2 --}}
@php
    /*
     * Controller-provided DB read-back is authoritative.
     * Empty Admin values intentionally remain empty.
     */
    $savedPlacementTitle =
        $universalPlacementContent['title']
        ?? '';

    $savedPlacementDescription =
        $universalPlacementContent['description']
        ?? '';

    $savedPlacementCta =
        $universalPlacementContent['cta_text']
        ?? '';
@endphp

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
                value="{{ old('placement_title', $savedPlacementTitle) }}"
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
                value="{{ old('placement_cta_text', $savedPlacementCta) }}"
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
                placeholder="Optional">{{ old('placement_description', $savedPlacementDescription) }}</textarea>
        </div>
    </div>
</div>

{{-- ESUBIZ_UNIVERSAL_ADDON_PLACEMENT_UI_V2 --}}
                    @php
                        /*
                         * Four central plug-and-play placements only.
                         *
                         * Legacy feature-owned locations stay registered
                         * temporarily for their existing runtime hooks, but
                         * Admin no longer configures them individually.
                         */
                        $universalPlacementKeys = [
                            'dashboard',
                            'settings',
                            'page_builder',
                            'widgets',
                        ];

                        $universalPlacementDefinitions = collect($triggerLocations)
                            ->filter(function ($location, $key) use ($universalPlacementKeys) {
                                $locationKey = $location['key'] ?? $key;

                                return in_array(
                                    $locationKey,
                                    $universalPlacementKeys,
                                    true
                                );
                            });

                        $savedUniversalTriggers = $salesTriggers
                            ->whereIn(
                                'location_key',
                                $universalPlacementKeys
                            )
                            ->keyBy('location_key');

                        $selectedPlacements = old('addon_placements');

                        if (!is_array($selectedPlacements)) {
                            $selectedPlacements = $savedUniversalTriggers
                                ->filter(
                                    fn ($trigger) =>
                                        (bool) ($trigger->is_active ?? false)
                                )
                                ->keys()
                                ->values()
                                ->all();
                        }

                        $dashboardTrigger =
                            $savedUniversalTriggers->get('dashboard');

                        $dashboardCondition = old(
                            'dashboard_sales_trigger.condition_type',
                            in_array(
                                $dashboardTrigger->condition_type ?? null,
                                [
                                    'resource_threshold',
                                    'limit_reached',
                                ],
                                true
                            )
                                ? $dashboardTrigger->condition_type
                                : 'resource_threshold'
                        );

                        $dashboardResourceKey = old(
                            'dashboard_sales_trigger.resource_key',
                            $dashboardTrigger->resource_key
                                ?? ''
                        );

                        $dashboardThreshold = old(
                            'dashboard_sales_trigger.threshold_percentage',
                            $dashboardTrigger->threshold_percentage
                                ?? 80
                        );

                        $dashboardRepeatPolicy = old(
                            'dashboard_sales_trigger.repeat_policy',
                            $dashboardTrigger->repeat_policy
                                ?? 'once_until_purchased'
                        );
                    @endphp

                    <div
                        class="md:col-span-2 border-t border-slate-200 pt-6"
                        x-data='{
                            placements: @json(array_values($selectedPlacements)),
                            dashboardCondition: @json($dashboardCondition)
                        }'>

                        {{-- PLACEMENT --}}
                        <div>
                            <h3 class="text-base font-black text-slate-900">
                                Placement
                            </h3>

                            <p class="mt-1 text-xs text-slate-500">
                                Choose every area where this Add-on should be recommended.
                            </p>
                        </div>

                        <div class="mt-5 rounded-2xl border border-slate-200 bg-slate-50 p-5">

                            {{-- ESUBIZ_UNIVERSAL_ADDON_PLACEMENT_CHECKBOXES_V1 --}}
                            <label class="text-sm font-bold text-slate-700">
                                Add-on Placement
                            </label>

                            <div class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">

                                @foreach($universalPlacementDefinitions as $placementKey => $placement)
                                    @php
                                        $actualPlacementKey =
                                            $placement['key']
                                            ?? $placementKey;

                                        $placementChecked =
                                            in_array(
                                                $actualPlacementKey,
                                                $selectedPlacements,
                                                true
                                            );
                                    @endphp

                                    <label
                                        class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-4 transition hover:border-blue-300">

                                        <input
                                            type="checkbox"
                                            name="addon_placements[]"
                                            value="{{ $actualPlacementKey }}"
                                            x-model="placements"
                                            class="h-4 w-4 rounded border-slate-300">

                                        <span class="text-sm font-bold text-slate-700">
                                            {{ $placement['label'] ?? ucfirst($actualPlacementKey) }}
                                        </span>

                                    </label>

                                @endforeach

                            </div>

                            <p class="mt-3 text-xs text-slate-500">
                                Select one or more locations where this Add-on should appear.
                            </p>

                        </div>


                        {{-- SALES TRIGGER --}}
                        <div
                            x-show="placements.includes('dashboard')"
                            x-cloak
                            class="mt-5 rounded-2xl border border-blue-200 bg-blue-50/40 p-5">

                            <h3 class="text-base font-black text-slate-900">
                                Sales Trigger
                            </h3>

                            <p class="mt-1 text-xs text-slate-500">
                                Controls when this Add-on is recommended on the Dashboard.
                            </p>

                            <div class="mt-5 grid gap-4 md:grid-cols-2">

                                <div>
                                    <label class="text-sm font-bold text-slate-700">
                                        Trigger
                                    </label>

                                    <select
                                        name="dashboard_sales_trigger[condition_type]"
                                        x-model="dashboardCondition"
                                        class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3">

                                        <option value="resource_threshold">
                                            Resource Threshold
                                        </option>

                                        <option value="limit_reached">
                                            Resource Limit Reached
                                        </option>

                                    </select>
                                </div>


                                <div
                                    x-show="
                                        dashboardCondition === 'resource_threshold'
                                        || dashboardCondition === 'limit_reached'
                                    "
                                    x-cloak>

                                    <label class="text-sm font-bold text-slate-700">
                                        Resource
                                    </label>

                                    <input
                                        type="text"
                                        name="dashboard_sales_trigger[resource_key]"
                                        value="{{ $dashboardResourceKey }}"
                                        placeholder="e.g. storage or bandwidth"
                                        class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3">

                                </div>


                                <div
                                    x-show="dashboardCondition === 'resource_threshold'"
                                    x-cloak>

                                    <label class="text-sm font-bold text-slate-700">
                                        Threshold
                                    </label>

                                    <div class="mt-2 flex items-center gap-2">

                                        <input
                                            type="number"
                                            min="0"
                                            max="100"
                                            step="1"
                                            name="dashboard_sales_trigger[threshold_percentage]"
                                            value="{{ $dashboardThreshold }}"
                                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3">

                                        <span class="flex h-[50px] items-center rounded-xl border border-slate-200 bg-white px-4 text-sm font-bold text-slate-500">
                                            %
                                        </span>

                                    </div>
                                </div>


                            </div>

                            <p class="mt-4 text-xs text-slate-500">
                                Resource recommendations automatically become eligible again when the newly purchased capacity later reaches its configured threshold or limit.
                            </p>

                        </div>

                    </div>

                    </div>

                </div>

            </div>


            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

                <h2 class="text-lg font-black text-slate-900">
                    Function Allocations
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    The amount configured here is added every time this add-on is purchased or rented.
                </p>


                <div class="mt-6 space-y-4">

                    @foreach($capabilities as $capability)

                        @php
                            $key = $capability->limit_key
                                ?? $capability->capability_key
                                ?? $capability->feature_key
                                ?? $capability->key
                                ?? $capability->name;

                            /*
                             * ESUBIZ_ADDON_ALLOCATION_EDIT_VALUE_V1
                             *
                             * Allocation rows are authoritative.
                             * Match the resolved Core key first, with
                             * capability aliases as safe fallbacks.
                             */
                            $allocationKeys = array_values(
                                array_unique(
                                    array_filter([
                                        $key,
                                        $capability->limit_key ?? null,
                                        $capability->capability_key ?? null,
                                        $capability->feature_key ?? null,
                                        $capability->key ?? null,
                                    ])
                                )
                            );

                            $allocation = null;

                            foreach ($allocationKeys as $allocationKey) {
                                if ($allocations->has($allocationKey)) {
                                    $allocation =
                                        $allocations->get($allocationKey);

                                    break;
                                }
                            }

                            $allocationValue =
                                $allocation
                                    ? ($allocation->allocation ?? 0)
                                    : 0;

                            /*
                             * ESUBIZ_ADDON_UNLIMITED_EDIT_STATE_V1
                             *
                             * Retain the saved unlimited state from the
                             * authoritative allocation record on edit.
                             */
                            $isAddonUnlimited =
                                $allocation
                                ? (bool) ($allocation->is_unlimited ?? false)
                                : false;

                            $type = $capability->type
                                ?? $capability->entitlement_type
                                ?? 'feature';

                            $unit = $capability->unit ?? 'records';

                            $coreDefault = $capability->default_value
                                ?? $capability->default
                                ?? $capability->limit
                                ?? null;

                            /*
                             * ESUBIZ_ADDON_SAVED_UNLIMITED_SUPPORT_V1
                             *
                             * A capability explicitly marked as supporting
                             * Unlimited can show the control normally.
                             *
                             * On edit, an existing allocation already saved
                             * as Unlimited must also keep that control visible.
                             */
                            $supportsUnlimited =
                                $isAddonUnlimited
                                || (bool) ($capability->supports_unlimited ?? false)
                                || (bool) ($capability->allow_unlimited ?? false)
                                || (bool) ($capability->is_unlimited ?? false)
                                || strtolower((string) $type) === 'unlimited';
                        @endphp

                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">

                            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">

                                <div class="min-w-0">

                                    <h3 class="text-sm font-black text-slate-900">
                                        {{ ucwords(str_replace('_', ' ', $key)) }}
                                    </h3>

                                    <div class="mt-3 flex flex-wrap gap-2">

                                        <span class="rounded-full bg-blue-100 px-3 py-1 text-[11px] font-bold text-blue-700">
                                            Entitlement: {{ ucfirst($type) }}
                                        </span>

                                        <span class="rounded-full bg-slate-200 px-3 py-1 text-[11px] font-bold text-slate-600">
                                            Core default:
                                            {{ $coreDefault ?? '—' }}
                                            {{ $unit }}
                                        </span>

                                        <span class="rounded-full bg-slate-200 px-3 py-1 text-[11px] font-bold text-slate-600">
                                            Unit: {{ $unit }}
                                        </span>

                                    </div>

                                </div>

                                <div class="w-full lg:max-w-xl">

                                    <label class="text-xs font-black text-slate-700">
                                        Add-on Allocation
                                    </label>

                                    <p class="mt-1 text-[11px] text-slate-500">
                                        Configure the additional amount this add-on contributes each time it is purchased or rented.
                                    </p>

                                    <div class="mt-2 flex gap-2">

                                        <input
                                            type="number"
                                            min="0"
                                            step="1"
                                            name="capability_allocations[{{ $key }}]"
                                            value="{{ $allocationValue }}"
                                            class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3">

                                        <span class="flex min-w-28 items-center justify-center rounded-xl border border-slate-200 bg-white px-3 text-xs font-bold text-slate-500">
                                            {{ $unit }}
                                        </span>

                                    </div>

                                    @if($supportsUnlimited)

                                        <label class="mt-3 flex cursor-pointer items-center gap-3 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3">

                                            <input
                                                type="checkbox"
                                                name="capability_unlimited[{{ $key }}]"
                                                value="1"
                                                {{-- ESUBIZ_ADDON_UNLIMITED_DIRECT_DB_STATE_V1 --}}
                                                {{
                                                    (
                                                        $allocations->has($key)
                                                        && (int) (
                                                            $allocations->get($key)->is_unlimited
                                                            ?? 0
                                                        ) === 1
                                                    )
                                                        ? 'checked'
                                                        : ''
                                                }}
                                                class="h-4 w-4 rounded border-slate-300">

                                            <span>
                                                <span class="block text-xs font-black text-slate-900">
                                                    Unlimited allocation
                                                </span>

                                                <span class="block text-[11px] text-slate-500">
                                                    No quantity limit for this function from this add-on.
                                                </span>
                                            </span>

                                        </label>

                                    @endif

                                    <p class="mt-2 text-[11px] text-slate-400">
                                        Every additional purchase or rental adds this configured amount again.
                                    </p>

                                </div>

                            </div>

                        </div>

                    @endforeach

                </div>

            </div>


            <div class="flex justify-end gap-3">

                <a href="{{ route('admin.core-addons.index') }}"
                   class="rounded-xl border border-slate-300 bg-white px-6 py-3 text-sm font-bold text-slate-700">
                    Cancel
                </a>

                <button
                    type="submit"
                    class="rounded-xl bg-blue-600 px-7 py-3 text-sm font-black text-white hover:bg-blue-700">
                    Save Add-on
                </button>

            </div>

        </form>

    </div>

</div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const featureSelect = document.getElementById('edit-addon-core-feature');
    const functionsBox = document.getElementById('edit-addon-functions');

    if (!featureSelect || !functionsBox) return;

    const limits = @json($featureLimits ?? []);
    const existing = @json($allocations);

    function featureKey(row) {
        return row.feature_key
            || row.feature
            || row.core_feature_key
            || row.core_feature_id;
    }

    function limitKey(row) {
        return row.key
            || row.capability_key
            || row.limit_key
            || row.name;
    }

    function typeOf(row) {
        return row.type
            || row.entitlement_type
            || 'feature';
    }

    function unitOf(row) {
        const type = typeOf(row);

        if (row.unit) return row.unit;
        if (type === 'credits') return 'credits';
        if (type === 'storage') return 'GB';
        if (type === 'bandwidth') return 'GB';

        return 'records';
    }

    function defaultOf(row) {
        return row.default
            ?? row.default_value
            ?? row.default_limit
            ?? row.limit
            ?? null;
    }

    function supportsUnlimited(row) {
        return typeOf(row) === 'unlimited'
            || row.allow_unlimited === true
            || row.supports_unlimited === true
            || row.unlimited === true;
    }

    function title(value) {
        return String(value || '')
            .replace(/_/g, ' ')
            .replace(/\b\w/g, c => c.toUpperCase());
    }

    function existingAllocation(key) {
        if (!existing) return null;

        if (typeof existing.get === 'function') {
            return existing.get(key) || null;
        }

        return existing[key] || null;
    }

    function renderFunctions(feature) {

        functionsBox.innerHTML = '';

        if (!feature) {
            functionsBox.innerHTML = `
                <div class="rounded-xl border border-dashed border-slate-200 p-4 text-sm text-slate-400 md:col-span-2">
                    Select a Core feature first.
                </div>
            `;
            return;
        }

        const rows = limits.filter(row =>
            String(featureKey(row)) === String(feature)
        );

        if (!rows.length) {
            functionsBox.innerHTML = `
                <div class="rounded-xl border border-dashed border-slate-200 p-4 text-sm text-slate-400 md:col-span-2">
                    This Core feature has no separately registered extension functions.
                </div>
            `;
            return;
        }

        rows.forEach(row => {

            const key = limitKey(row);
            const type = typeOf(row);
            const unit = unitOf(row);
            const coreDefault = defaultOf(row);
            const unlimitedSupported = supportsUnlimited(row);
            const allocation = existingAllocation(key);

            const selected =
                !!allocation ||
                {{ Js::from($allocations->keys()->all()) }}.includes(key);

            const allocationValue =
                allocation?.allocation
                ?? allocation?.default_allocation
                ?? 0;

            const addonUnlimited =
                allocation?.is_unlimited === true ||
                allocation?.is_unlimited === 1;

            const wrapper = document.createElement('label');

            wrapper.className =
                'flex cursor-pointer items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4 hover:border-blue-300 hover:bg-blue-50';

            wrapper.innerHTML = `
                <input
                    type="checkbox"
                    name="capabilities[]"
                    value="${key}"
                    ${selected ? 'checked' : ''}
                    class="edit-addon-function mt-1 rounded border-slate-300">

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
    }

    featureSelect.addEventListener('change', function () {
        renderFunctions(this.value);
    });

    /*
     * Automatically determine the Core feature from the existing
     * capabilities when the edit page opens.
     */
    const currentFeature =
        featureSelect.value ||
        @json(
            old(
                'parent_capability',
                $addon->parent_capability
                ?? $addon->core_feature_key
                ?? $addon->parent_capability_key
                ?? $addon->feature_key
                ?? ''
            )
        );

    if (currentFeature) {
        featureSelect.value = currentFeature;
        renderFunctions(currentFeature);
    }

});
</script>

@endsection


{{-- ESUBIZ_DUPLICATE_UNLIMITED_JS_REMOVED_V1
The Function Allocations section already renders its Unlimited checkbox
server-side using the saved allocation record. The former JavaScript
duplicate control was intentionally removed.
--}}


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
