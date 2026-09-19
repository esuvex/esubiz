@extends('admin.layouts.app')

@section('title', 'Marketplace Settings')

@section('content')
@php
    $settingValue = function (string $key, $default = false) use ($marketplaceSettings) {
        $setting = $marketplaceSettings->firstWhere('key', $key);

        if (!$setting) {
            return $default;
        }

        $value = $setting->value;

        return is_array($value) && array_key_exists('value', $value)
            ? $value['value']
            : ($value ?? $default);
    };

    $productLabel = fn (string $type) =>
        ucwords(str_replace('_', ' ', $type));
@endphp

<div class="space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-slate-900">
            Marketplace Settings
        </h1>
        <p class="mt-1 text-sm text-slate-500">
            Universal controls for the Esubiz Marketplace.
        </p>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- MARKETPLACE SETTINGS TABS --}}
    <div
        id="marketplaceSettingsTabs"
        class="mb-6 overflow-x-auto rounded-2xl border border-slate-200 bg-white p-2 shadow-sm"
    >
        <div class="flex min-w-max gap-2">
            @foreach([
                'general' => 'General',
                'products' => 'Products',
                'developer' => 'Developer Controls',
                'developer_commission' => 'Developer Commission',
                'expenses' => 'Product Expenses',
                'taxes' => 'Product Taxes',
                'referrals' => 'Referrals',
                'categories' => 'Categories',
            ] as $tabKey => $tabLabel)
                <button
                    type="button"
                    data-marketplace-tab="{{ $tabKey }}"
                    class="marketplace-tab rounded-xl px-4 py-2.5 text-sm font-semibold transition
                        {{ $tabKey === 'general'
                            ? 'bg-blue-600 text-white'
                            : 'text-slate-600 hover:bg-blue-50 hover:text-blue-700' }}"
                >
                    {{ $tabLabel }}
                </button>
            @endforeach
        </div>
    </div>

    <div data-marketplace-panel="general" class="marketplace-settings-panel space-y-6">
    {{-- GENERAL --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-5">
            <h2 class="text-lg font-semibold text-slate-900">
                General
            </h2>
            <p class="mt-1 text-sm text-slate-500">
                Marketplace-wide availability controls.
            </p>
        </div>

        <form
            method="POST"
            action="{{ route('admin.marketplace.settings.general.update') }}"
            class="space-y-5"
        >
            @csrf

            <label class="flex items-center justify-between gap-6">
                <div>
                    <div class="text-sm font-semibold text-slate-800">
                        Marketplace
                    </div>
                    <div class="text-xs text-slate-500">
                        Enable or disable Marketplace availability.
                    </div>
                </div>

                <span class="flex items-center gap-3">
                    <input type="hidden" name="marketplace_enabled" value="0">

                    <span class="text-xs font-semibold text-slate-500">Inactive</span>
                    <span class="relative inline-flex h-7 w-12 shrink-0">
                        <input
                            type="checkbox"
                            name="marketplace_enabled"
                            value="1"
                            @checked((bool) $settingValue('marketplace_enabled', true))
                            class="peer sr-only"
                        >
                        <span
                            class="pointer-events-none absolute inset-0 rounded-full transition-colors duration-200"
                            style="background:#cbd5e1;"
                        ></span>
                        <span
                            class="esubiz-toggle-knob pointer-events-none absolute left-1 top-1 h-5 w-5 rounded-full bg-white shadow-md transition-transform duration-200"
                        ></span>
                    </span>
                    <span class="text-xs font-semibold text-blue-600">Active</span>

                    
                </span>
            </label>

            <label class="flex items-center justify-between gap-6 border-t border-slate-100 pt-5">
                <div>
                    <div class="text-sm font-semibold text-slate-800">
                        Developer Sales
                    </div>
                    <div class="text-xs text-slate-500">
                        Allow approved Developer products to be sold through Marketplace.
                    </div>
                </div>

                <span class="flex items-center gap-3">
                    <input type="hidden" name="developer_sales_enabled" value="0">

                    <span class="text-xs font-semibold text-slate-500">Inactive</span>
                    <span class="relative inline-flex h-7 w-12 shrink-0">
                        <input
                            type="checkbox"
                            name="developer_sales_enabled"
                            value="1"
                            @checked((bool) $settingValue('developer_sales_enabled', true))
                            class="peer sr-only"
                        >
                        <span
                            class="pointer-events-none absolute inset-0 rounded-full transition-colors duration-200"
                            style="background:#cbd5e1;"
                        ></span>
                        <span
                            class="esubiz-toggle-knob pointer-events-none absolute left-1 top-1 h-5 w-5 rounded-full bg-white shadow-md transition-transform duration-200"
                        ></span>
                    </span>
                    <span class="text-xs font-semibold text-blue-600">Active</span>

                    
                </span>
            </label>

            <label class="flex items-center justify-between gap-6 border-t border-slate-100 pt-5">
                <div>
                    <div class="text-sm font-semibold text-slate-800">
                        Developer Reseller
                    </div>
                    <div class="text-xs text-slate-500">
                        Allow Developers to resell eligible Esubiz products.
                    </div>
                </div>

                <span class="flex items-center gap-3">
                    <input type="hidden" name="developer_reseller_enabled" value="0">

                    <span class="text-xs font-semibold text-slate-500">Inactive</span>
                    <span class="relative inline-flex h-7 w-12 shrink-0">
                        <input
                            type="checkbox"
                            name="developer_reseller_enabled"
                            value="1"
                            @checked((bool) $settingValue('developer_reseller_enabled', false))
                            class="peer sr-only"
                        >
                        <span
                            class="pointer-events-none absolute inset-0 rounded-full transition-colors duration-200"
                            style="background:#cbd5e1;"
                        ></span>
                        <span
                            class="esubiz-toggle-knob pointer-events-none absolute left-1 top-1 h-5 w-5 rounded-full bg-white shadow-md transition-transform duration-200"
                        ></span>
                    </span>
                    <span class="text-xs font-semibold text-blue-600">Active</span>

                    
                </span>
            </label>

            <div class="flex justify-end">
                <button
                    type="submit"
                    class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700"
                >
                    Save General Settings
                </button>
            </div>
        </form>
    </div>


    </div>

    <div data-marketplace-panel="developer" class="marketplace-settings-panel hidden space-y-6">
        @php
            $centralPrimaryCurrency = app(
                \App\Services\Platform\EsubizCurrencyPricingService::class
            )->primaryCurrency();
        @endphp

        <form
            method="POST"
            action="{{ route('admin.marketplace.settings.developer-product-policy.update') }}"
            class="space-y-6"
        >
            @csrf

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 p-6">
                    <h2 class="text-lg font-semibold text-slate-900">
                        Build & Publish Permissions
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Control which Marketplace products Central Admin and Developers may build or publish.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">Product</th>
                                <th class="px-5 py-3 text-center font-semibold text-slate-600">Admin Build</th>
                                <th class="px-5 py-3 text-center font-semibold text-slate-600">Developer Build</th>
                                <th class="px-5 py-3 text-center font-semibold text-slate-600">Developer Publish</th>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">SaaS Max</th>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">Off-server Max</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($developerProductTypes as $productType)
                                @php
                                    $policy = $developerPolicies->get($productType);
                                    $policyIndex = 'build_' . $loop->index;
                                @endphp
                                <tr>
                                    <td class="px-5 py-4 font-medium text-slate-800">
                                        <input type="hidden" name="policies[{{ $policyIndex }}][product_type]" value="{{ $productType }}">
                                        <input type="hidden" name="policies[{{ $policyIndex }}][admin_build_enabled]" value="0">
                                        <input type="hidden" name="policies[{{ $policyIndex }}][developer_build_enabled]" value="0">
                                        <input type="hidden" name="policies[{{ $policyIndex }}][developer_publish_enabled]" value="0">
                                        {{ $productLabel($productType) }}
                                    </td>

                                    <td class="px-5 py-4 text-center">
                                        <input type="checkbox"
                                               name="policies[{{ $policyIndex }}][admin_build_enabled]"
                                               value="1"
                                               @checked((bool) ($policy->admin_build_enabled ?? false))
                                               class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                    </td>

                                    <td class="px-5 py-4 text-center">
                                        <input type="checkbox"
                                               name="policies[{{ $policyIndex }}][developer_build_enabled]"
                                               value="1"
                                               @checked((bool) ($policy->developer_build_enabled ?? false))
                                               class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                    </td>

                                    <td class="px-5 py-4 text-center">
                                        <input type="checkbox"
                                               name="policies[{{ $policyIndex }}][developer_publish_enabled]"
                                               value="1"
                                               @checked((bool) ($policy->developer_publish_enabled ?? false))
                                               class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                    </td>

                                    <td class="px-5 py-4">
                                        <div class="flex min-w-[150px] items-center rounded-lg border border-slate-300 bg-white">
                                            <span class="border-r border-slate-300 px-3 text-slate-500">{{ $centralPrimaryCurrency }}</span>
                                            <input type="number"
                                                   step="0.01"
                                                   min="0"
                                                   name="policies[{{ $policyIndex }}][saas_max_price]"
                                                   value="{{ $policy->saas_max_price ?? '' }}"
                                                   placeholder="No maximum"
                                                   class="w-full border-0 px-3 py-2 focus:ring-0">
                                        </div>
                                    </td>

                                    <td class="px-5 py-4">
                                        <div class="flex min-w-[150px] items-center rounded-lg border border-slate-300 bg-white">
                                            <span class="border-r border-slate-300 px-3 text-slate-500">{{ $centralPrimaryCurrency }}</span>
                                            <input type="number"
                                                   step="0.01"
                                                   min="0"
                                                   name="policies[{{ $policyIndex }}][off_server_max_price]"
                                                   value="{{ $policy->off_server_max_price ?? '' }}"
                                                   placeholder="No maximum"
                                                   class="w-full border-0 px-3 py-2 focus:ring-0">
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-8 text-center text-slate-500">
                                        No buildable Marketplace products are registered.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 p-6">
                    <h2 class="text-lg font-semibold text-slate-900">
                        Developer Reseller Eligibility
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Select the Esubiz products Developers may resell when Developer Reseller is enabled in General settings.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">Esubiz Product</th>
                                <th class="px-5 py-3 text-center font-semibold text-slate-600">Reseller Eligible</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($developerResellTypes as $productType)
                                @php
                                    $policy = $developerPolicies->get($productType);
                                    $policyIndex = 'resell_' . $loop->index;
                                @endphp
                                <tr>
                                    <td class="px-5 py-4 font-medium text-slate-800">
                                        <input type="hidden" name="policies[{{ $policyIndex }}][product_type]" value="{{ $productType }}">
                                        <input type="hidden" name="policies[{{ $policyIndex }}][developer_resell_enabled]" value="0">
                                        {{ $productLabel($productType) }}
                                    </td>

                                    <td class="px-5 py-4 text-center">
                                        <input type="checkbox"
                                               name="policies[{{ $policyIndex }}][developer_resell_enabled]"
                                               value="1"
                                               @checked((bool) ($policy->developer_resell_enabled ?? false))
                                               class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="2" class="px-5 py-8 text-center text-slate-500">
                                        No Esubiz reseller products are currently registered.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit"
                        class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                    Save Developer Controls
                </button>
            </div>
        </form>
    </div>

    <div data-marketplace-panel="products" class="marketplace-settings-panel hidden space-y-6">
        <form method="POST" action="{{ route('admin.marketplace.settings.availability.update') }}">
            @csrf

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 p-6">
                    <h2 class="text-lg font-semibold text-slate-900">
                        Product Visibility & Availability
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Control whether each product type is shown and available through Marketplace.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">Product Type</th>
                                <th class="px-5 py-3 text-center font-semibold text-slate-600">Visible</th>
                                <th class="px-5 py-3 text-center font-semibold text-slate-600">Available</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            @foreach($productTypes as $productType)
                                @php
                                    $availability = $settingValue(
                                        'product_availability.' . $productType,
                                        ['is_visible' => true, 'is_available' => true]
                                    );

                                    $availability = is_array($availability) ? $availability : [];
                                    $productIndex = $loop->index;
                                @endphp

                                <tr>
                                    <td class="px-5 py-4 font-medium text-slate-800">
                                        <input type="hidden"
                                               name="products[{{ $productIndex }}][product_type]"
                                               value="{{ $productType }}">
                                        {{ $productLabel($productType) }}
                                    </td>

                                    <td class="px-5 py-4 text-center">
                                        <input type="hidden"
                                               name="products[{{ $productIndex }}][is_visible]"
                                               value="0">
                                        <input type="checkbox"
                                               name="products[{{ $productIndex }}][is_visible]"
                                               value="1"
                                               @checked((bool) ($availability['is_visible'] ?? true))
                                               class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                    </td>

                                    <td class="px-5 py-4 text-center">
                                        <input type="hidden"
                                               name="products[{{ $productIndex }}][is_available]"
                                               value="0">
                                        <input type="checkbox"
                                               name="products[{{ $productIndex }}][is_available]"
                                               value="1"
                                               @checked((bool) ($availability['is_available'] ?? true))
                                               class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-end border-t border-slate-200 px-5 py-4">
                    <button type="submit"
                            class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                        Save Product Settings
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div data-marketplace-panel="developer_commission" class="marketplace-settings-panel hidden space-y-6">
        <form method="POST" action="{{ route('admin.marketplace.settings.financial.update') }}">
            @csrf

            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <div class="border-b border-slate-200 p-6">
                    <h2 class="text-lg font-semibold text-slate-900">Developer Commission</h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Configure the Developer commission earned when a Developer's own Marketplace product is sold. Esubiz share is calculated automatically.
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">Product</th>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">Developer Share %</th>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">Esubiz Share %</th>
                                <th class="px-5 py-3 text-center font-semibold text-slate-600">Active</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            @foreach($developerProductTypes as $productType)
                                @php
                                    $financial = $financialRules->firstWhere('product_type', $productType);
                                    $developerShare = (float) ($financial?->developer_share_percent ?? 0);
                                    $financialIndex = $loop->index;
                                @endphp

                                <tr>
                                    <td class="px-5 py-4 font-medium text-slate-800">
                                        <input type="hidden"
                                               name="financial_rules[{{ $financialIndex }}][product_type]"
                                               value="{{ $productType }}">
                                        {{ $productLabel($productType) }}
                                    </td>

                                    <td class="px-5 py-4">
                                        <input type="number"
                                               name="financial_rules[{{ $financialIndex }}][developer_share_percent]"
                                               data-developer-share
                                               value="{{ $financial?->developer_share_percent ?? 0 }}"
                                               min="0"
                                               max="100"
                                               step="0.01"
                                               required
                                               class="w-28 rounded-lg border border-slate-300 px-3 py-2">
                                    </td>

                                    <td class="px-5 py-4 text-slate-700" data-esubiz-share>
                                        {{ number_format(max(0, 100 - $developerShare), 2) }}%
                                    </td>

                                    <td class="px-5 py-4 text-center">
                                        <input type="hidden"
                                               name="financial_rules[{{ $financialIndex }}][is_active]"
                                               value="0">
                                        <input type="checkbox"
                                               name="financial_rules[{{ $financialIndex }}][is_active]"
                                               value="1"
                                               @checked($financial ? $financial->is_active : true)
                                               class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-end border-t border-slate-200 px-5 py-4">
                    <button type="submit"
                            class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                        Save Developer Commission
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div data-marketplace-panel="expenses" class="marketplace-settings-panel hidden space-y-6">
{{-- PRODUCT EXPENSE RULES --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-5">
            <h2 class="text-lg font-semibold text-slate-900">
                Product Expenses
            </h2>
            <p class="mt-1 text-sm text-slate-500">
                Add one or more direct expenses to each Marketplace product. The expense name is used in Central financial and ledger records.
            </p>
        </div>

        <form
            id="marketplaceExpenseForm"
            method="POST"
            action="{{ route('admin.marketplace.settings.expenses.store') }}"
            data-store-action="{{ route('admin.marketplace.settings.expenses.store') }}"
            class="grid gap-4 lg:grid-cols-5"
        >
            @csrf

            <input
                type="hidden"
                id="marketplaceExpenseMethod"
                name="_method"
                value="POST"
            >

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">
                    Product
                </label>
                <select
                    name="product_type"
                    required
                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5"
                >
                    @foreach($productTypes as $productType)
                        <option value="{{ $productType }}">
                            {{ $productLabel($productType) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">
                    Expense Name
                </label>
                <input
                    type="text"
                    name="name"
                    maxlength="120"
                    required
                    placeholder="Provider Cost"
                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5"
                >
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">
                    Calculation
                </label>
                <select
                    name="calculation_type"
                    required
                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5"
                >
                    <option value="percentage">Percentage</option>
                    <option value="fixed">Fixed Amount</option>
                </select>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">
                    Value
                </label>
                <input
                    type="number"
                    name="value"
                    min="0"
                    step="0.0001"
                    required
                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5"
                >
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">
                    Sort Order
                </label>
                <input
                    type="number"
                    name="sort_order"
                    min="0"
                    value="0"
                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5"
                >
            </div>

            <div class="lg:col-span-5 flex flex-wrap items-center justify-end gap-3">
                <label class="inline-flex shrink-0 items-center gap-2 cursor-pointer">
                    <span class="inline-flex items-center">
                        <input type="hidden" name="is_active" value="0">
                        <input
                            type="checkbox"
                            name="is_active"
                            value="1"
                            checked
                            class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                        >
                    </span>
                    <span class="text-sm font-medium text-slate-700">
                        Active
                    </span>
                </label>

                <div class="flex shrink-0 items-center gap-2">
                    <button
                        type="button"
                        id="cancelExpenseEdit"
                        class="hidden w-auto shrink-0 rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold leading-5 text-slate-700 hover:bg-slate-50"
                    >
                        Cancel Edit
                    </button>

                    <button
                        type="submit"
                        id="marketplaceExpenseSubmit"
                        class="w-auto shrink-0 rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold leading-5 text-white hover:bg-blue-700"
                    >
                        Add Expense
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-5 py-3 text-left font-semibold text-slate-600">Product</th>
                        <th class="px-5 py-3 text-left font-semibold text-slate-600">Expense</th>
                        <th class="px-5 py-3 text-left font-semibold text-slate-600">Calculation</th>
                        <th class="px-5 py-3 text-left font-semibold text-slate-600">Value</th>
                        <th class="px-5 py-3 text-left font-semibold text-slate-600">Status</th>
                        <th class="px-5 py-3 text-right font-semibold text-slate-600">Actions</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse($expenseRules->flatten(1) as $expense)
                        <tr>
                            <td class="px-5 py-4 font-medium text-slate-800">
                                {{ $productLabel($expense->product_type) }}
                            </td>

                            <td class="px-5 py-4 text-slate-700">
                                {{ $expense->name }}
                            </td>

                            <td class="px-5 py-4 text-slate-600">
                                {{ $expense->calculation_type === 'percentage'
                                    ? 'Percentage'
                                    : 'Fixed Amount' }}
                            </td>

                            <td class="px-5 py-4 text-slate-700">
                                @if($expense->calculation_type === 'percentage')
                                    {{ number_format((float) $expense->value, 2) }}%
                                @else
                                    {{ $centralCurrency }} {{ number_format((float) $expense->value, 2) }}
                                @endif
                            </td>

                            <td class="px-5 py-4">
                                <span class="rounded-full px-2.5 py-1 text-xs font-medium
                                    {{ $expense->is_active
                                        ? 'bg-green-100 text-green-700'
                                        : 'bg-slate-100 text-slate-600' }}">
                                    {{ $expense->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>

                            <td class="px-5 py-4 text-right">
                                <button
                                    type="button"
                                    data-expense-edit
                                    data-update-action="{{ route(
                                        'admin.marketplace.settings.expenses.update',
                                        $expense
                                    ) }}"
                                    data-expense-product="{{ $expense->product_type }}"
                                    data-expense-name="{{ $expense->name }}"
                                    data-expense-calculation="{{ $expense->calculation_type }}"
                                    data-expense-value="{{ $expense->value }}"
                                    data-expense-active="{{ $expense->is_active ? '1' : '0' }}"
                                    data-expense-sort="{{ $expense->sort_order }}"
                                    class="mr-3 text-sm font-medium text-blue-600 hover:text-blue-700"
                                >
                                    Edit
                                </button>

                                <form
                                    method="POST"
                                    action="{{ route(
                                        'admin.marketplace.settings.expenses.destroy',
                                        $expense
                                    ) }}"
                                    class="inline"
                                    onsubmit="return confirm('Delete this Marketplace expense?');"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="text-sm font-medium text-red-600 hover:text-red-700"
                                    >
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="6"
                                class="px-5 py-10 text-center text-sm text-slate-500"
                            >
                                No product expenses have been configured yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    

    </div>

    <div data-marketplace-panel="taxes" class="marketplace-settings-panel hidden space-y-6">
        <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 p-6">
                <h2 class="text-lg font-semibold text-slate-900">
                    Product Taxes
                </h2>
                <p class="mt-1 text-sm text-slate-500">
                    Attach an active Central Tax to each Marketplace product and choose whether the tax is inclusive or exclusive.
                </p>
            </div>

            @if($centralTaxes->isEmpty())
                <div class="p-6">
                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-4 text-sm text-slate-600">
                        No Central taxes configured.
                    </div>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">Product</th>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">Central Tax</th>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">Exclusive</th>
                                <th class="px-5 py-3 text-left font-semibold text-slate-600">Active</th>
                                <th class="px-5 py-3 text-right font-semibold text-slate-600">Action</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            @foreach($productTypes as $productType)
                                @php
                                    $productTax = $productTaxes->get($productType);
                                @endphp

                                <tr>
                                    <form
                                        method="POST"
                                        action="{{ route('admin.marketplace.settings.product-taxes.update') }}"
                                    >
                                        @csrf

                                        <input
                                            type="hidden"
                                            name="product_type"
                                            value="{{ $productType }}"
                                        >

                                        <td class="px-5 py-4 font-medium text-slate-800">
                                            {{ $productLabel($productType) }}
                                        </td>

                                        <td class="px-5 py-4">
                                            <select
                                                name="central_tax_id"
                                                required
                                                class="min-w-[190px] rounded-lg border border-slate-300 px-3 py-2"
                                            >
                                                <option value="">Select tax</option>

                                                @foreach($centralTaxes as $tax)
                                                    <option
                                                        value="{{ $tax->id }}"
                                                        @selected((int) ($productTax?->central_tax_id ?? 0) === (int) $tax->id)
                                                    >
                                                        {{ $tax->name }} ({{ number_format((float) $tax->rate, 2) }}%)
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>

                                        <td class="px-5 py-4">
                                            <label class="relative inline-flex cursor-pointer">
                                                <input type="hidden" name="is_exclusive" value="0">
                                                <input
                                                    type="checkbox"
                                                    name="is_exclusive"
                                                    value="1"
                                                    @checked((bool) ($productTax?->is_exclusive ?? false))
                                                    class="peer sr-only"
                                                >
                                                <span class="h-6 w-11 rounded-full bg-slate-300 transition peer-checked:bg-blue-600"></span>
                                                <span class="absolute left-1 top-1 h-4 w-4 rounded-full bg-white transition peer-checked:translate-x-5"></span>
                                            </label>
                                            <div class="mt-1 text-xs text-slate-500">
                                                Left = Inclusive · Right = Exclusive
                                            </div>
                                        </td>

                                        <td class="px-5 py-4">
                                            <label class="relative inline-flex cursor-pointer">
                                                <input type="hidden" name="is_active" value="0">
                                                <input
                                                    type="checkbox"
                                                    name="is_active"
                                                    value="1"
                                                    @checked($productTax ? $productTax->is_active : true)
                                                    class="peer sr-only"
                                                >
                                                <span class="h-6 w-11 rounded-full bg-slate-300 transition peer-checked:bg-blue-600"></span>
                                                <span class="absolute left-1 top-1 h-4 w-4 rounded-full bg-white transition peer-checked:translate-x-5"></span>
                                            </label>
                                        </td>

                                        <td class="px-5 py-4">
                                            <div class="flex justify-end gap-2">
                                                @if($productTax)
                                                    <button
                                                        type="submit"
                                                        form="delete-product-tax-{{ $productTax->id }}"
                                                        class="rounded-lg border border-red-200 px-3 py-2 font-semibold text-red-600 hover:bg-red-50"
                                                    >
                                                        Remove
                                                    </button>
                                                @endif

                                                <button
                                                    type="submit"
                                                    class="rounded-lg border border-slate-300 px-4 py-2 font-semibold text-slate-700 hover:bg-slate-50"
                                                >
                                                    Save
                                                </button>
                                            </div>
                                        </td>
                                    </form>
                                </tr>

                                @if($productTax)
                                    <form
                                        id="delete-product-tax-{{ $productTax->id }}"
                                        method="POST"
                                        action="{{ route('admin.marketplace.settings.product-taxes.destroy', $productTax) }}"
                                        class="hidden"
                                    >
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <div data-marketplace-panel="referrals" class="marketplace-settings-panel hidden space-y-6">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="mb-5">
                <h2 class="text-lg font-semibold text-slate-900">
                    Referral Commission
                </h2>
                <p class="mt-1 text-sm text-slate-500">
                    Configure the Level 1 Marketplace commission for each product and Central user role.
                </p>
            </div>

            @if($referralRoles->isEmpty())
                <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                    No active Central user roles are currently available.
                </div>
            @else
                <form
                    id="marketplaceReferralForm"
                    method="POST"
                    action="{{ route('admin.marketplace.settings.referrals.store') }}"
                    data-store-action="{{ route('admin.marketplace.settings.referrals.store') }}"
                    class="space-y-5"
                >
                    @csrf

                    <input
                        type="hidden"
                        name="_method"
                        id="marketplaceReferralMethod"
                        value="POST"
                    >

                    <div class="max-w-xl">
                        <label class="mb-1 block text-sm font-medium text-slate-700">
                            Product
                        </label>

                        <select
                            name="product_type"
                            required
                            class="w-full rounded-xl border border-slate-300 px-4 py-2.5"
                        >
                            @foreach($productTypes as $productType)
                                <option value="{{ $productType }}">
                                    {{ $productLabel($productType) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-sm font-medium text-slate-700">
                            User Roles
                        </label>

                        <div
                            id="marketplaceReferralRoleSelector"
                            class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
                        >
                            @foreach($referralRoles as $role)
                                <label
                                    class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 transition hover:border-blue-300 hover:bg-blue-50/40"
                                >
                                    <input
                                        type="checkbox"
                                        class="marketplace-referral-role h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                        value="{{ $role->slug }}"
                                        data-role-name="{{ $role->name }}"
                                    >

                                    <span class="text-sm font-medium text-slate-700">
                                        {{ $role->name }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div
                        id="marketplaceReferralRoleRows"
                        class="space-y-3"
                    >
                        <div
                            id="marketplaceReferralEmptyRoles"
                            class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-5 py-6 text-center text-sm text-slate-500"
                        >
                            Select one or more user roles to configure their commission.
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button
                            type="submit"
                            id="marketplaceReferralSubmit"
                            class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700"
                        >
                            Save Referral Commission
                        </button>
                    </div>
                </form>
            @endif
        </div>

        <div
            id="marketplaceReferralTable"
            class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
        >
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-5 py-3 text-left font-semibold text-slate-600">Product</th>
                            <th class="px-5 py-3 text-left font-semibold text-slate-600">User Role</th>
                            <th class="px-5 py-3 text-left font-semibold text-slate-600">Calculation</th>
                            <th class="px-5 py-3 text-left font-semibold text-slate-600">Commission</th>
                            <th class="px-5 py-3 text-left font-semibold text-slate-600">Status</th>
                            <th class="px-5 py-3 text-right font-semibold text-slate-600">Action</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($referralRules as $productRow)
                            @php
                                $rules = $productRow->rules ?? collect();

                                $editRules = $rules->map(function ($rule) use ($referralRoles) {
                                    $role = $referralRoles->firstWhere(
                                        'slug',
                                        $rule->referrer_role
                                    );

                                    return [
                                        'referrer_role' => $rule->referrer_role,
                                        'role_name' => $role?->name
                                            ?? ucwords(str_replace('_', ' ', $rule->referrer_role)),
                                        'calculation_type' => $rule->calculation_type,
                                        'commission_value' => $rule->commission_value,
                                        'is_active' => (bool) $rule->is_active,
                                    ];
                                })->values();
                            @endphp

                            <tr>
                                <td class="px-5 py-4 align-top font-medium text-slate-800">
                                    {{ $productLabel($productRow->product_type) }}
                                </td>

                                <td class="px-5 py-4 align-top text-slate-700">
                                    <div class="space-y-1">
                                        @foreach($rules as $rule)
                                            @php
                                                $role = $referralRoles->firstWhere(
                                                    'slug',
                                                    $rule->referrer_role
                                                );
                                            @endphp
                                            <div>
                                                {{ $role?->name
                                                    ?? ucwords(str_replace('_', ' ', $rule->referrer_role)) }}
                                            </div>
                                        @endforeach
                                    </div>
                                </td>

                                <td class="px-5 py-4 align-top text-slate-600">
                                    <div class="space-y-1">
                                        @foreach($rules as $rule)
                                            <div>
                                                {{ $rule->calculation_type === 'fixed'
                                                    ? 'Fixed'
                                                    : 'Percentage' }}
                                            </div>
                                        @endforeach
                                    </div>
                                </td>

                                <td class="px-5 py-4 align-top text-slate-700">
                                    <div class="space-y-1">
                                        @foreach($rules as $rule)
                                            <div>
                                                @if($rule->calculation_type === 'fixed')
                                                    {{ $centralCurrency }}
                                                    {{ number_format((float) $rule->commission_value, 2) }}
                                                @else
                                                    {{ number_format((float) $rule->commission_value, 2) }}%
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </td>

                                <td class="px-5 py-4 align-top">
                                    <div class="space-y-1">
                                        @foreach($rules as $rule)
                                            <div>
                                                <span class="rounded-full px-2.5 py-1 text-xs font-medium
                                                    {{ $rule->is_active
                                                        ? 'bg-green-100 text-green-700'
                                                        : 'bg-slate-100 text-slate-600' }}">
                                                    {{ $rule->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>

                                <td class="px-5 py-4 text-right align-top">
                                    <button
                                        type="button"
                                        data-referral-edit
                                        data-update-action="{{ route(
                                            'admin.marketplace.settings.referrals.update',
                                            $productRow->id
                                        ) }}"
                                        data-referral-product="{{ $productRow->product_type }}"
                                        data-referral-rules='@json($editRules)'
                                        class="text-sm font-semibold text-blue-600 hover:text-blue-700"
                                    >
                                        Edit
                                    </button>

                                    <form
                                        method="POST"
                                        action="{{ route(
                                            'admin.marketplace.settings.referrals.destroy',
                                            $productRow->id
                                        ) }}"
                                        class="ml-3 inline"
                                        onsubmit="return confirm('Delete all referral commissions for this product?');"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button
                                            type="submit"
                                            class="text-sm font-semibold text-red-600 hover:text-red-700"
                                        >
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-10 text-center text-sm text-slate-500">
                                    No Marketplace referral commissions configured.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($referralRules->hasPages())
                <div class="flex items-center justify-between border-t border-slate-200 px-5 py-4">
                    <span class="text-sm text-slate-500">
                        Showing {{ $referralRules->firstItem() }}–{{ $referralRules->lastItem() }}
                        of {{ $referralRules->total() }}
                    </span>

                    <div class="flex gap-2">
                        @if($referralRules->previousPageUrl())
                            <a
                                href="{{ $referralRules->previousPageUrl() }}"
                                data-referral-page
                                class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                            >
                                Previous
                            </a>
                        @endif

                        @if($referralRules->nextPageUrl())
                            <a
                                href="{{ $referralRules->nextPageUrl() }}"
                                data-referral-page
                                class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50"
                            >
                                Next
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div data-marketplace-panel="categories" class="marketplace-settings-panel hidden space-y-6">
    {{-- CATEGORIES --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-5">
            <h2 class="text-lg font-semibold text-slate-900">
                Marketplace Categories
            </h2>
            <p class="mt-1 text-sm text-slate-500">
                Products may belong to multiple categories.
            </p>
        </div>

        <form
            id="marketplaceCategoryForm"
            method="POST"
            action="{{ route('admin.marketplace.categories.store') }}"
            data-store-action="{{ route('admin.marketplace.categories.store') }}"
            class="grid gap-4 lg:grid-cols-2"
        >
            @csrf

            <input
                type="hidden"
                id="marketplaceCategoryMethod"
                name="_method"
                value="POST"
            >

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">
                    Category Name
                </label>
                <input
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    required
                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5"
                    placeholder="General"
                >
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">
                    Sort Order
                </label>
                <input
                    type="number"
                    name="sort_order"
                    value="{{ old('sort_order', 0) }}"
                    min="0"
                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5"
                >
            </div>

            <div class="lg:col-span-2">
                <label class="mb-2 block text-sm font-medium text-slate-700">
                    Compatible Product Types
                </label>

                <details class="group relative">
                    <summary
                        class="flex cursor-pointer list-none items-center justify-between rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-slate-700 hover:border-blue-400"
                    >
                        <span>Select compatible product types</span>
                        <span class="text-slate-400 transition-transform group-open:rotate-180">⌄</span>
                    </summary>

                    <div class="absolute z-30 mt-2 max-h-72 w-full overflow-y-auto rounded-xl border border-slate-200 bg-white p-2 shadow-xl">
                        @foreach($productTypes as $productType)
                            <label class="flex cursor-pointer items-center gap-3 rounded-lg px-3 py-2 text-sm text-slate-700 hover:bg-slate-50">
                                <input
                                    type="checkbox"
                                    name="product_types[]"
                                    value="{{ $productType }}"
                                    @checked(in_array($productType, old('product_types', []), true))
                                    class="h-4 w-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                >
                                <span>{{ $productLabel($productType) }}</span>
                            </label>
                        @endforeach
                    </div>
                </details>

                <p class="mt-2 text-xs text-slate-500">
                    No selection means this category is universal.
                </p>
            </div>

            <div class="lg:col-span-2">
                <label class="mb-1 block text-sm font-medium text-slate-700">
                    Description
                </label>
                <textarea
                    name="description"
                    rows="3"
                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5"
                >{{ old('description') }}</textarea>
            </div>

            <div class="lg:col-span-2 flex items-center justify-between">
                <label class="inline-flex items-center gap-3">
                    <input type="hidden" name="is_active" value="0">
                    <input
                        type="checkbox"
                        name="is_active"
                        value="1"
                        @checked((bool) old('is_active', true))
                        class="h-5 w-5 rounded border-slate-300"
                    >
                    <span class="text-sm font-medium text-slate-700">
                        Active
                    </span>
                </label>

                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        id="cancelCategoryEdit"
                        class="hidden rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                    >
                        Cancel Edit
                    </button>

                    <button
                        type="submit"
                        id="marketplaceCategorySubmit"
                        class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700"
                    >
                        Add Category
                    </button>
                </div>
            </div>
        </form>
    </div>

    <div
        id="marketplaceCategoryTable"
        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
    >
        @include('admin.marketplace.settings.partials.category-table')
    </div>
    </div>


</div>

<script>
(function () {
    const tabs = document.querySelectorAll('[data-marketplace-tab]');
    const panels = document.querySelectorAll('[data-marketplace-panel]');

    if (!tabs.length || !panels.length) {
        return;
    }

    function activateMarketplaceTab(tabName) {
        tabs.forEach(function (button) {
            const active =
                button.dataset.marketplaceTab === tabName;

            button.classList.toggle('bg-blue-600', active);
            button.classList.toggle('text-white', active);

            button.classList.toggle(
                'text-slate-600',
                !active
            );

            button.classList.toggle(
                'hover:bg-blue-50',
                !active
            );

            button.classList.toggle(
                'hover:text-blue-700',
                !active
            );
        });

        panels.forEach(function (panel) {
            panel.classList.toggle(
                'hidden',
                panel.dataset.marketplacePanel !== tabName
            );
        });

        const url = new URL(window.location.href);
        url.searchParams.set('settings_tab', tabName);

        window.history.replaceState(
            {},
            '',
            url.toString()
        );
    }

    tabs.forEach(function (button) {
        button.addEventListener('click', function () {
            activateMarketplaceTab(
                button.dataset.marketplaceTab
            );
        });
    });

    const requestedTab = new URL(
        window.location.href
    ).searchParams.get('settings_tab');

    const initialTab = requestedTab
        && document.querySelector(
            '[data-marketplace-panel="' + requestedTab + '"]'
        )
            ? requestedTab
            : 'general';

    activateMarketplaceTab(initialTab);
})();
</script>

<script>
function resetMarketplaceCategoryForm() {
    const form = document.getElementById('marketplaceCategoryForm');

    if (!form) {
        return;
    }

    form.action = form.dataset.storeAction;

    document.getElementById('marketplaceCategoryMethod').value = 'POST';
    document.getElementById('marketplaceCategorySubmit').textContent =
        'Add Category';

    document.getElementById('cancelCategoryEdit')
        .classList.add('hidden');

    form.reset();

    const active = form.querySelector('[name="is_active"]');
    const activeCheckboxes = form.querySelectorAll(
        'input[type="checkbox"][name="is_active"]'
    );

    activeCheckboxes.forEach(function (checkbox) {
        checkbox.checked = true;
    });

    form.querySelector('[name="sort_order"]').value = 0;
}

document.addEventListener('click', function (event) {
    const editButton = event.target.closest('[data-category-edit]');

    if (editButton) {
        const form = document.getElementById('marketplaceCategoryForm');
        let selectedTypes = [];

        try {
            selectedTypes = JSON.parse(
                editButton.getAttribute('data-category-types') || '[]'
            );
        } catch (error) {
            selectedTypes = [];
        }

        const data = {
            name: editButton.getAttribute('data-category-name') || '',
            description:
                editButton.getAttribute('data-category-description') || '',
            product_types: selectedTypes,
            is_active:
                editButton.getAttribute('data-category-active') === '1',
            sort_order:
                editButton.getAttribute('data-category-sort') || '0'
        };

        form.action =
            editButton.getAttribute('data-update-action');

        document.getElementById('marketplaceCategoryMethod').value = 'PUT';
        document.getElementById('marketplaceCategorySubmit').textContent =
            'Save Category';

        document.getElementById('cancelCategoryEdit')
            .classList.remove('hidden');

        form.querySelector('[name="name"]').value = data.name || '';
        form.querySelector('[name="sort_order"]').value =
            data.sort_order ?? 0;
        form.querySelector('[name="description"]').value =
            data.description || '';

        const activeCheckbox = form.querySelector(
            'input[type="checkbox"][name="is_active"]'
        );

        if (activeCheckbox) {
            activeCheckbox.checked = Boolean(data.is_active);
        }

        const normalizedSelectedTypes = Array.isArray(data.product_types)
            ? data.product_types
            : [];

        form.querySelectorAll(
            'input[name="product_types[]"]'
        ).forEach(function (checkbox) {
            checkbox.checked =
                normalizedSelectedTypes.includes(checkbox.value);
        });

        form.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });

        return;
    }

    if (event.target.closest('#cancelCategoryEdit')) {
        resetMarketplaceCategoryForm();
    }
});

document.addEventListener('click', function (event) {
    const editButton = event.target.closest('[data-expense-edit]');

    if (editButton) {
        const form = document.getElementById('marketplaceExpenseForm');
        const data = {
            product_type: editButton.dataset.expenseProduct,
            name: editButton.dataset.expenseName,
            calculation_type: editButton.dataset.expenseCalculation,
            value: editButton.dataset.expenseValue,
            is_active: editButton.dataset.expenseActive === '1',
            sort_order: editButton.dataset.expenseSort
        };

        form.action = editButton.dataset.updateAction;

        document.getElementById('marketplaceExpenseMethod').value = 'PUT';
        document.getElementById('marketplaceExpenseSubmit').textContent =
            'Save Expense';
        document.getElementById('cancelExpenseEdit')
            .classList.remove('hidden');

        form.querySelector('[name="product_type"]').value =
            data.product_type;
        form.querySelector('[name="name"]').value = data.name || '';
        form.querySelector('[name="calculation_type"]').value =
            data.calculation_type || 'percentage';
        form.querySelector('[name="value"]').value = data.value ?? 0;
        form.querySelector('[name="sort_order"]').value =
            data.sort_order ?? 0;

        const active = form.querySelector(
            'input[type="checkbox"][name="is_active"]'
        );

        if (active) {
            active.checked = Boolean(data.is_active);
        }

        form.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });

        return;
    }

    if (event.target.closest('#cancelExpenseEdit')) {
        const form = document.getElementById('marketplaceExpenseForm');

        form.action = form.dataset.storeAction;
        form.reset();

        document.getElementById('marketplaceExpenseMethod').value = 'POST';
        document.getElementById('marketplaceExpenseSubmit').textContent =
            'Add Expense';
        document.getElementById('cancelExpenseEdit')
            .classList.add('hidden');

        form.querySelector('[name="sort_order"]').value = 0;

        const active = form.querySelector(
            'input[type="checkbox"][name="is_active"]'
        );

        if (active) {
            active.checked = true;
        }
    }
});


document.addEventListener('click', function (event) {
    const editButton = event.target.closest('[data-referral-edit]');

    if (editButton) {
        const form = document.getElementById('marketplaceReferralForm');
        const selector = document.getElementById('marketplaceReferralRoleSelector');

        if (!form || !selector) {
            return;
        }

        let rules = [];

        try {
            rules = JSON.parse(editButton.dataset.referralRules || '[]');
        } catch (error) {
            return;
        }

        form.action = editButton.dataset.updateAction;

        document.getElementById('marketplaceReferralMethod').value = 'PUT';
        document.getElementById('marketplaceReferralSubmit').textContent =
            'Save Referral Commission';

        form.querySelector('[name="product_type"]').value =
            editButton.dataset.referralProduct || '';

        selector.querySelectorAll('.marketplace-referral-role').forEach(function (checkbox) {
            checkbox.checked = rules.some(function (rule) {
                return rule.referrer_role === checkbox.value;
            });
        });

        selector.dispatchEvent(new Event('change', {
            bubbles: true
        }));

        rules.forEach(function (rule) {
            const roleInput = form.querySelector(
                `input[type="hidden"][name$="[referrer_role]"][value="${CSS.escape(rule.referrer_role)}"]`
            );

            if (!roleInput) {
                return;
            }

            const row = roleInput.closest('.marketplace-referral-role-row');

            if (!row) {
                return;
            }

            const calculation = row.querySelector(
                'select[name$="[calculation_type]"]'
            );

            const value = row.querySelector(
                'input[name$="[commission_value]"]'
            );

            const active = row.querySelector(
                'input[type="checkbox"][name$="[is_active]"]'
            );

            if (calculation) {
                calculation.value = rule.calculation_type || 'percentage';
            }

            if (value) {
                value.value = rule.commission_value ?? 0;
            }

            if (active) {
                active.checked = Boolean(rule.is_active);
            }
        });

        form.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });

        return;
    }
});

document.addEventListener('click', async function (event) {
    const categoryLink = event.target.closest(
        '#marketplaceCategoryTable a[data-category-page]'
    );

    const referralLink = event.target.closest(
        '#marketplaceReferralTable a[data-referral-page]'
    );

    const link = categoryLink || referralLink;

    if (!link) {
        return;
    }

    event.preventDefault();

    const targetId = categoryLink
        ? 'marketplaceCategoryTable'
        : 'marketplaceReferralTable';

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
        const parser = new DOMParser();
        const responseDocument = parser.parseFromString(
            html,
            'text/html'
        );

        const nextContent = responseDocument.getElementById(
            targetId
        );

        if (!nextContent) {
            window.location.href = link.href;
            return;
        }

        document.getElementById(targetId).innerHTML =
            nextContent.innerHTML;

        window.history.replaceState({}, '', link.href);
    } catch (error) {
        window.location.href = link.href;
    }
});

    document.addEventListener('input', function (event) {
        const input = event.target.closest('[data-developer-share]');

        if (!input) return;

        const form = input.closest('form');
        const output = form?.querySelector('[data-esubiz-share]');

        if (!output) return;

        let developerShare = parseFloat(input.value || '0');

        if (!Number.isFinite(developerShare)) {
            developerShare = 0;
        }

        developerShare = Math.min(100, Math.max(0, developerShare));

        output.textContent =
            (100 - developerShare).toFixed(2) + '%';
    });

</script>
@endsection

<style>
    [data-marketplace-panel="general"] input[type="checkbox"].peer:checked + span {
        background: #2563eb !important;
    }

    [data-marketplace-panel="general"] input[type="checkbox"].peer:checked ~ .esubiz-toggle-knob {
        transform: translateX(20px);
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const selector = document.getElementById('marketplaceReferralRoleSelector');
    const rows = document.getElementById('marketplaceReferralRoleRows');

    if (!selector || !rows) {
        return;
    }

    const renderRows = function () {
        const selected = Array.from(
            selector.querySelectorAll('.marketplace-referral-role:checked')
        );

        if (!selected.length) {
            rows.innerHTML = `
                <div
                    id="marketplaceReferralEmptyRoles"
                    class="rounded-xl border border-dashed border-slate-300 bg-slate-50 px-5 py-6 text-center text-sm text-slate-500"
                >
                    Select one or more user roles to configure their commission.
                </div>
            `;
            return;
        }

        rows.innerHTML = selected.map(function (checkbox, index) {
            const slug = checkbox.value;
            const name = checkbox.dataset.roleName;

            return `
                <div class="marketplace-referral-role-row rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <input
                        type="hidden"
                        name="roles[${index}][referrer_role]"
                        value="${slug}"
                    >

                    <div class="grid gap-4 lg:grid-cols-[1.4fr_1fr_1fr_auto] lg:items-end">
                        <div>
                            <div class="text-xs font-medium uppercase tracking-wide text-slate-500">
                                User Role
                            </div>
                            <div class="mt-2 font-semibold text-slate-800">
                                ${name}
                            </div>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">
                                Calculation
                            </label>

                            <select
                                name="roles[${index}][calculation_type]"
                                required
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5"
                            >
                                <option value="percentage">Percentage</option>
                                <option value="fixed">Fixed</option>
                            </select>
                        </div>

                        <div>
                            <label class="mb-1 block text-sm font-medium text-slate-700">
                                Value
                            </label>

                            <input
                                type="number"
                                name="roles[${index}][commission_value]"
                                min="0"
                                step="0.01"
                                required
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5"
                            >
                        </div>

                        <label class="flex cursor-pointer items-center gap-3 pb-2">
                            <input
                                type="hidden"
                                name="roles[${index}][is_active]"
                                value="0"
                            >

                            <span class="relative inline-flex h-7 w-12 shrink-0">
                                <input
                                    type="checkbox"
                                    name="roles[${index}][is_active]"
                                    value="1"
                                    checked
                                    class="peer sr-only referral-premium-toggle"
                                >

                                <span
                                    class="referral-toggle-track pointer-events-none absolute inset-0 rounded-full"
                                ></span>

                                <span
                                    class="referral-toggle-knob pointer-events-none absolute left-1 top-1 h-5 w-5 rounded-full bg-white shadow-md"
                                ></span>
                            </span>

                            <span class="text-sm font-medium text-slate-700">
                                Active
                            </span>
                        </label>
                    </div>
                </div>
            `;
        }).join('');
    };

    selector.addEventListener('change', renderRows);
    renderRows();
});
</script>

<style>
    .referral-toggle-track {
        background: #cbd5e1;
        transition: background-color .2s ease;
    }

    .referral-toggle-knob {
        transition: transform .2s ease;
    }

    .referral-premium-toggle:checked ~ .referral-toggle-track {
        background: #2563eb;
    }

    .referral-premium-toggle:checked ~ .referral-toggle-knob {
        transform: translateX(20px);
    }
</style>
