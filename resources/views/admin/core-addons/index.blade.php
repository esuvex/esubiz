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
                    onclick="document.getElementById('addon-form').classList.toggle('hidden')"
                    class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">
                + Add Add-on
            </button>

            <button type="button"
                    onclick="document.getElementById('bundle-form').classList.toggle('hidden')"
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


    {{-- ADD-ON FORM --}}
    <div id="addon-form" class="mb-8 hidden rounded-3xl border border-slate-200 bg-white shadow-sm">

        <div class="border-b border-slate-100 px-6 py-5">
            <h2 class="text-lg font-bold text-slate-900">Create Addon</h2>
            <p class="mt-1 text-sm text-slate-500">
                Define what this add-on unlocks and how it is sold.
            </p>
        </div>

        <form method="POST" action="{{ route('admin.core-addons.addons.store') }}" class="p-6">
    @csrf

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
         class="mt-6 hidden rounded-2xl border border-slate-200 bg-white p-5">

        <div class="mb-4">
            <div class="font-bold text-slate-900">Add-on Allocation</div>
            <p class="mt-1 text-xs text-slate-500">
                Configure the additional amount this add-on contributes each time it is purchased or rented.
            </p>
        </div>

        <div id="addon-allocation-list" class="space-y-4"></div>
    </div>

    <div class="mt-6">
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
                    <option value="week">Week</option>
                </select>
            </div>
        </div>

        <div class="rounded-2xl border border-violet-100 bg-violet-50 p-5">
            <div class="font-bold text-violet-900">Off-server License</div>

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

    <div class="mt-6 flex justify-end">
        <button type="submit"
                class="rounded-xl bg-slate-900 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">
            Save Add-on
        </button>
    </div>
</form>

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
    </div>


    {{-- BUNDLE FORM --}}
    <div id="bundle-form" class="mb-8 hidden rounded-3xl border border-slate-200 bg-white shadow-sm">

        <div class="border-b border-slate-100 px-6 py-5">
            <h2 class="text-lg font-bold text-slate-900">Create Addon Bundle</h2>
            <p class="mt-1 text-sm text-slate-500">
                Package existing Core add-ons into one product.
            </p>
        </div>

        <form method="POST" action="{{ route('admin.core-addons.bundles.store') }}" class="p-6">
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

                <div>
                    <label class="text-sm font-semibold text-slate-700">Category</label>
                    <input name="category"
                           class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm">
                </div>

            </div>

            <div class="mt-6">
                <label class="text-sm font-semibold text-slate-700">Description</label>
                <textarea name="description" rows="3"
                          class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm"></textarea>
            </div>

            <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-5">
                <div class="mb-4 font-bold text-slate-900">
                    Add-ons in this Bundle
                </div>

                @forelse($addons as $addon)
                    <label class="mb-3 flex items-center justify-between gap-4 rounded-xl border border-slate-200 bg-white p-3">

                        <span class="flex items-center gap-3">
                            <input type="checkbox"
                                   name="items[{{ $addon->id }}][addon_id]"
                                   value="{{ $addon->id }}">

                            <span>
                                <span class="block text-sm font-semibold text-slate-800">
                                    {{ $addon->name }}
                                </span>

                                <span class="block text-xs text-slate-400">
                                    {{ $addon->key }}
                                </span>
                            </span>
                        </span>

                        <input type="number"
                               name="items[{{ $addon->id }}][allocation]"
                               min="0"
                               step="0.01"
                               placeholder="Allocation"
                               class="w-32 rounded-lg border border-slate-200 px-3 py-2 text-xs">

                    </label>
                @empty
                    <p class="text-sm text-slate-500">
                        Create add-ons before creating a bundle.
                    </p>
                @endforelse
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

            <div class="mt-6 flex justify-end">
                <button type="submit"
                        class="rounded-xl bg-slate-900 px-6 py-3 text-sm font-semibold text-white">
                    Save Bundle
                </button>
            </div>

        </form>
    </div>


    {{-- ADD-ON REGISTRY --}}
    <div class="mb-8 rounded-3xl border border-slate-200 bg-white shadow-sm">

        <div class="border-b border-slate-100 px-6 py-5">
            <h2 class="text-lg font-bold text-slate-900">Addons</h2>
            <p class="mt-1 text-sm text-slate-500">
                {{ $addons->count() }} configured extensions
            </p>
        </div>

        <div class="p-6 space-y-3">

            @forelse($addons as $addon)

                <details class="group rounded-2xl border border-slate-200 bg-slate-50">

                    <summary class="flex cursor-pointer list-none items-center justify-between p-5">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-semibold text-slate-900">
                                    {{ $addon->name }}
                                </span>

                                <span class="rounded-full bg-slate-200 px-2.5 py-1 text-[11px] font-semibold text-slate-600">
                                    {{ $addon->entitlement_type }}
                                </span>

                                <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold
                                    {{ $addon->is_active
                                        ? 'bg-emerald-50 text-emerald-700'
                                        : 'bg-slate-200 text-slate-500' }}">
                                    {{ $addon->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>

                            <div class="mt-1 text-xs text-slate-400">
                                {{ $addon->key }}
                            </div>
                        </div>

                        <span class="text-slate-400 transition group-open:rotate-180">
                            ↓
                        </span>
                    </summary>

                    <div class="border-t border-slate-200 bg-white p-5">

                        <div class="grid gap-4 md:grid-cols-4 text-sm">

                            <div>
                                <span class="text-slate-400">Parent</span>
                                <div class="font-semibold text-slate-800">
                                    {{ $addon->parent_capability ?: '—' }}
                                </div>
                            </div>

                            <div>
                                <span class="text-slate-400">Allocation</span>
                                <div class="font-semibold text-slate-800">
                                    {{ $addon->is_unlimited ? 'Unlimited' : ($addon->default_allocation ?? '—') }}
                                    {{ $addon->allocation_unit }}
                                </div>
                            </div>

                            <div>
                                <span class="text-slate-400">SaaS</span>
                                <div class="font-semibold text-slate-800">
                                    {{ $addon->saas_available ? 'Available' : 'Disabled' }}
                                </div>
                            </div>

                            <div>
                                <span class="text-slate-400">Off-server</span>
                                <div class="font-semibold text-slate-800">
                                    {{ $addon->off_server_available ? 'Available' : 'Disabled' }}
                                </div>
                            </div>

                        </div>

                        <div class="mt-5 flex gap-2">

                            <form method="POST"
                                  action="{{ route('admin.core-addons.addons.toggle', $addon->id) }}">
                                @csrf
                                <a href="{{ route('admin.core-addons.edit', $addon->id) }}"
       class="inline-flex items-center rounded-xl border border-blue-300 px-4 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-50">
    Edit Add-on
</a>
<button class="rounded-xl border border-slate-300 px-4 py-2 text-xs font-semibold text-slate-700">
                                    {{ $addon->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
<form method="POST"
      action="{{ route('admin.core-addons.addons.destroy', $addon->id) }}"
      class="inline"
      onsubmit="return confirm('Permanently delete this add-on? This cannot be undone.');">
    @csrf
    @method('DELETE')
    <button type="submit"
            class="rounded-xl border border-red-300 px-4 py-2 text-xs font-semibold text-red-600">
        Delete Add-on
    </button>
</form>


                        </div>

                    </div>

                </details>

            @empty

                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-10 text-center">
                    <div class="font-semibold text-slate-700">
                        No Core add-ons configured
                    </div>
                    <p class="mt-1 text-sm text-slate-500">
                        Use Add Add-on above to create the first one.
                    </p>
                </div>

            @endforelse

        </div>
    </div>


    {{-- BUNDLES --}}
    <div class="rounded-3xl border border-slate-200 bg-white shadow-sm">

        <div class="border-b border-slate-100 px-6 py-5">
            <h2 class="text-lg font-bold text-slate-900">Addon Bundles</h2>
            <p class="mt-1 text-sm text-slate-500">
                {{ $bundles->count() }} configured bundles
            </p>
        </div>

        <div class="p-6 space-y-3">

            @forelse($bundles as $bundle)

                <details class="group rounded-2xl border border-slate-200 bg-slate-50">

                    <summary class="flex cursor-pointer list-none items-center justify-between p-5">

                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-slate-900">
                                    {{ $bundle->name }}
                                </span>

                                <span class="rounded-full bg-violet-50 px-2.5 py-1 text-[11px] font-semibold text-violet-700">
                                    Bundle
                                </span>

                                <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold
                                    {{ $bundle->is_active
                                        ? 'bg-emerald-50 text-emerald-700'
                                        : 'bg-slate-200 text-slate-500' }}">
                                    {{ $bundle->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>

                            <div class="mt-1 text-xs text-slate-400">
                                {{ $bundle->key }}
                            </div>
                        </div>

                        <span class="text-slate-400 transition group-open:rotate-180">
                            ↓
                        </span>

                    </summary>

                    <div class="border-t border-slate-200 bg-white p-5">

                        <div class="grid gap-4 md:grid-cols-4 text-sm">

                            <div>
                                <span class="text-slate-400">SaaS</span>
                                <div class="font-semibold text-slate-800">
                                    {{ $bundle->saas_available ? 'Available' : 'Disabled' }}
                                </div>
                            </div>

                            <div>
                                <span class="text-slate-400">Off-server</span>
                                <div class="font-semibold text-slate-800">
                                    {{ $bundle->off_server_available ? 'Available' : 'Disabled' }}
                                </div>
                            </div>

                            <div>
                                <span class="text-slate-400">SaaS Price</span>
                                <div class="font-semibold text-slate-800">
                                    {{ $bundle->saas_price !== null ? $bundle->saas_currency . ' ' . number_format($bundle->saas_price, 2) : '—' }}
                                </div>
                            </div>

                            <div>
                                <span class="text-slate-400">License Price</span>
                                <div class="font-semibold text-slate-800">
                                    {{ $bundle->off_server_price !== null ? $bundle->off_server_currency . ' ' . number_format($bundle->off_server_price, 2) : '—' }}
                                </div>
                            </div>

                        </div>

                        <div class="mt-5">

                            <div class="mb-2 text-xs font-bold uppercase tracking-wider text-slate-400">
                                Included Add-ons
                            </div>

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
                            @endphp

                            <div class="flex flex-wrap gap-2">

                                @forelse($bundleItems as $item)

                                    <span class="rounded-full bg-slate-100 px-3 py-1.5 text-xs font-medium text-slate-700">
                                        {{ $item->name }}
                                        ·
                                        {{ $item->is_unlimited ? 'Unlimited' : ($item->allocation ?? 'Default') }}
                                    </span>

                                @empty

                                    <span class="text-sm text-slate-400">
                                        No add-ons assigned yet.
                                    </span>

                                @endforelse

                            </div>

                        </div>

                        @php
                            $bundleEditData = [
                                'id' => $bundle->id,
                                'key' => $bundle->key,
                                'name' => $bundle->name,
                                'category' => $bundle->category,
                                'description' => $bundle->description,
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

                        <div class="mt-5 flex flex-wrap gap-2">

                            <button
                                type="button"
                                class="bundle-edit-btn rounded-xl border border-blue-300 px-4 py-2 text-xs font-semibold text-blue-700 hover:bg-blue-50"
                                data-bundle="{{ base64_encode(json_encode($bundleEditData)) }}">
                                Edit Bundle
                            </button>

                            <form method="POST"
                                  action="{{ route('admin.core-addons.bundles.destroy', $bundle->id) }}"
                                  onsubmit="return confirm('Permanently delete this bundle? This cannot be undone.');">
                                @csrf
                                @method('DELETE')

                                <button type="submit"
                                        class="rounded-xl border border-red-300 px-4 py-2 text-xs font-semibold text-red-600 hover:bg-red-50">
                                    Delete Bundle
                                </button>
                            </form>

                            <form method="POST"
                                  action="{{ route('admin.core-addons.bundles.toggle', $bundle->id) }}">
                                @csrf

                                <button class="rounded-xl border border-slate-300 px-4 py-2 text-xs font-semibold text-slate-700">
                                    {{ $bundle->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>

                        </div>

                    </div>

                </details>

            @empty

                <div class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-10 text-center">
                    <div class="font-semibold text-slate-700">
                        No bundles configured
                    </div>
                    <p class="mt-1 text-sm text-slate-500">
                        Use Create Bundle above to package Core add-ons.
                    </p>
                </div>

            @endforelse

        </div>
    </div>

</div>

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

    const saas = form.querySelector('[name="saas_available"]');

    const offserver = form.querySelector('[name="off_server_available"]');

    if (saas) saas.checked = !!bundle.saas_available;
    if (offserver) offserver.checked = !!bundle.off_server_available;

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

@endsection
