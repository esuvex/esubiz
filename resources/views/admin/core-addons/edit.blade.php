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
              class="space-y-6">

            @csrf
            @method('PUT')


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

                    </div>


                    <div class="md:col-span-2 grid grid-cols-1 gap-4 md:grid-cols-2">

                        <label class="flex items-center gap-3">
                            <input type="hidden" name="saas_available" value="0">
                            <input type="checkbox" name="saas_available" value="1"
                                {{ old('saas_available', $addon->saas_available) ? 'checked' : '' }}>
                            <span class="text-sm font-bold text-slate-700">Available for SaaS websites</span>
                        </label>

                        <label class="flex items-center gap-3">
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


                    <div>

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


                    <div>

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




            {{-- ESUBIZ_ADDON_RESOURCE_SALES_TRIGGER_FORM_V1 --}}
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
            @endphp

            <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">

                <div>
                    <h2 class="text-lg font-black text-slate-900">
                        Resource Sales Trigger
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Recommend this Add-on when a website approaches the selected Core resource limit.
                    </p>
                </div>

                <div class="mt-6 grid gap-5 md:grid-cols-2">

                    <div class="md:col-span-2">
                        <label class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">

                            <input type="hidden"
                                   name="sales_trigger_enabled"
                                   value="0">

                            <input type="checkbox"
                                   name="sales_trigger_enabled"
                                   value="1"
                                   {{ old(
                                       'sales_trigger_enabled',
                                       $salesTrigger['enabled'] ?? false
                                   ) ? 'checked' : '' }}>

                            <span>
                                <span class="block text-sm font-bold text-slate-700">
                                    Enable resource sales trigger
                                </span>

                                <span class="block text-xs text-slate-500">
                                    Shows this Add-on as an upgrade recommendation when its configured resource reaches the trigger level.
                                </span>
                            </span>

                        </label>
                    </div>


                    <div>
                        <label class="text-sm font-bold text-slate-700">
                            Core Resource
                        </label>

                        <select
                            name="sales_trigger_resource"
                            class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3">

                            <option value="">
                                No resource selected
                            </option>

                            @foreach($capabilities as $capability)

                                @php
                                    $triggerKey =
                                        $capability->limit_key
                                        ?? $capability->capability_key
                                        ?? $capability->feature_key
                                        ?? $capability->key
                                        ?? $capability->name;

                                    $triggerType =
                                        $capability->type
                                        ?? $capability->entitlement_type
                                        ?? 'feature';

                                    $selectedTrigger =
                                        old(
                                            'sales_trigger_resource',
                                            $salesTrigger['resource'] ?? ''
                                        );
                                @endphp

                                <option
                                    value="{{ $triggerKey }}"
                                    {{ (string) $selectedTrigger === (string) $triggerKey ? 'selected' : '' }}
                                >
                                    {{ ucwords(str_replace(['.', '_'], ' ', $triggerKey)) }}
                                    — {{ ucfirst($triggerType) }}
                                </option>

                            @endforeach

                        </select>

                        <p class="mt-2 text-xs text-slate-500">
                            Resources come directly from the Core capability registry.
                        </p>
                    </div>


                    <div>
                        <label class="text-sm font-bold text-slate-700">
                            Trigger At
                        </label>

                        <div class="mt-2 flex items-center gap-2">

                            <input
                                type="number"
                                name="sales_trigger_threshold"
                                min="0"
                                max="100"
                                step="1"
                                value="{{ old(
                                    'sales_trigger_threshold',
                                    $salesTrigger['threshold_percentage'] ?? 80
                                ) }}"
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3">

                            <span class="flex h-[50px] items-center rounded-xl border border-slate-200 bg-slate-50 px-4 text-sm font-bold text-slate-500">
                                %
                            </span>

                        </div>
                    </div>


                    <div class="md:col-span-2">

                        <div class="grid gap-4 md:grid-cols-2">

                            <label class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">

                                <input type="hidden"
                                       name="sales_trigger_saas"
                                       value="0">

                                <input type="checkbox"
                                       name="sales_trigger_saas"
                                       value="1"
                                       {{ old(
                                           'sales_trigger_saas',
                                           in_array('saas', $triggerDeployments, true)
                                       ) ? 'checked' : '' }}>

                                <span class="text-sm font-bold text-slate-700">
                                    Trigger for SaaS websites
                                </span>

                            </label>


                            <label class="flex items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">

                                <input type="hidden"
                                       name="sales_trigger_off_server"
                                       value="0">

                                <input type="checkbox"
                                       name="sales_trigger_off_server"
                                       value="1"
                                       {{ old(
                                           'sales_trigger_off_server',
                                           in_array('off_server', $triggerDeployments, true)
                                       ) ? 'checked' : '' }}>

                                <span class="text-sm font-bold text-slate-700">
                                    Trigger for off-server websites
                                </span>

                            </label>

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
