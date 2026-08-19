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
            <h2 class="text-lg font-bold text-slate-900">Create Core Add-on</h2>
            <p class="mt-1 text-sm text-slate-500">
                Define what this add-on unlocks and how it is sold.
            </p>
        </div>

        <form method="POST" action="{{ route('admin.core-addons.addons.store') }}" class="p-6">
            @csrf

            <div class="grid gap-5 md:grid-cols-2">

                <div>
                    <label class="text-sm font-semibold text-slate-700">Name</label>
                    <input name="name" required
                           class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm">
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Key</label>
                    <input name="key" required placeholder="crm_advanced"
                           class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm">
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Category</label>
                    <input name="category"
                           class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm">
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Parent Capability</label>
                    <input name="parent_capability" placeholder="crm"
                           class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm">
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Entitlement Type</label>
                    <select name="entitlement_type"
                            class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm">
                        <option value="feature">Feature</option>
                        <option value="quantity">Quantity</option>
                        <option value="credits">Credits</option>
                        <option value="storage">Storage</option>
                        <option value="boolean">Boolean</option>
                    </select>
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Allocation Unit</label>
                    <input name="allocation_unit" placeholder="records"
                           class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm">
                </div>

                <div>
                    <label class="text-sm font-semibold text-slate-700">Default Allocation</label>
                    <input name="default_allocation" type="number" min="0" step="0.01"
                           class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm">
                </div>

                <div class="flex items-center gap-3 pt-7">
                    <input name="is_unlimited" value="1" type="checkbox" class="rounded">
                    <label class="text-sm font-semibold text-slate-700">Unlimited allocation</label>
                </div>

            </div>

            <div class="mt-6">
                <label class="text-sm font-semibold text-slate-700">Description</label>
                <textarea name="description" rows="3"
                          class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm"></textarea>
            </div>

            <div class="mt-6">
                <label class="text-sm font-semibold text-slate-700">
                    Capabilities / Functions Unlocked
                </label>

                <input name="capabilities[]"
                       placeholder="Example: crm_reports, crm_automation, advanced_invoices"
                       class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm">

                <p class="mt-1 text-xs text-slate-400">
                    Enter the capability keys this product unlocks, separated by commas if needed.
                </p>
            </div>

            <div class="mt-8 grid gap-6 md:grid-cols-2">

                <div class="rounded-2xl border border-blue-100 bg-blue-50 p-5">
                    <div class="font-bold text-blue-900">SaaS Rental / Subscription</div>

                    <label class="mt-4 flex items-center gap-2 text-sm">
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

                    <p class="mt-3 text-xs text-violet-700">
                        Off-server purchases use the Core license entitlement model.
                    </p>
                </div>

            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button"
                        onclick="document.getElementById('addon-form').classList.add('hidden')"
                        class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-semibold text-slate-600">
                    Cancel
                </button>

                <button type="submit"
                        class="rounded-xl bg-slate-900 px-6 py-3 text-sm font-semibold text-white">
                    Save Add-on
                </button>
            </div>

        </form>
    </div>


    {{-- BUNDLE FORM --}}
    <div id="bundle-form" class="mb-8 hidden rounded-3xl border border-slate-200 bg-white shadow-sm">

        <div class="border-b border-slate-100 px-6 py-5">
            <h2 class="text-lg font-bold text-slate-900">Create Add-on Bundle</h2>
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
                    <label class="text-sm font-semibold text-slate-700">Bundle Key</label>
                    <input name="key" required placeholder="crm_growth_bundle"
                           class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 text-sm">
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
            <h2 class="text-lg font-bold text-slate-900">Core Add-ons</h2>
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
                                <button class="rounded-xl border border-slate-300 px-4 py-2 text-xs font-semibold text-slate-700">
                                    {{ $addon->is_active ? 'Deactivate' : 'Activate' }}
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
            <h2 class="text-lg font-bold text-slate-900">Add-on Bundles</h2>
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

                        <div class="mt-5">
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

@endsection
