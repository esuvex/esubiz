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

                <span class="relative inline-flex">
                    <input type="hidden" name="marketplace_enabled" value="0">
                    <input
                        type="checkbox"
                        name="marketplace_enabled"
                        value="1"
                        @checked((bool) $settingValue('marketplace_enabled', true))
                        class="peer sr-only"
                    >
                    <span class="h-6 w-11 rounded-full bg-slate-300 transition peer-checked:bg-blue-600"></span>
                    <span class="absolute left-1 top-1 h-4 w-4 rounded-full bg-white transition peer-checked:translate-x-5"></span>
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

                <span class="relative inline-flex">
                    <input type="hidden" name="developer_sales_enabled" value="0">
                    <input
                        type="checkbox"
                        name="developer_sales_enabled"
                        value="1"
                        @checked((bool) $settingValue('developer_sales_enabled', true))
                        class="peer sr-only"
                    >
                    <span class="h-6 w-11 rounded-full bg-slate-300 transition peer-checked:bg-blue-600"></span>
                    <span class="absolute left-1 top-1 h-4 w-4 rounded-full bg-white transition peer-checked:translate-x-5"></span>
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

    {{-- FINANCIAL RULES --}}
    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 p-6">
            <h2 class="text-lg font-semibold text-slate-900">
                Developer Sales & Expenses
            </h2>
            <p class="mt-1 text-sm text-slate-500">
                Configure Developer revenue share and Marketplace expense allocation by product type.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-5 py-3 text-left font-semibold text-slate-600">
                            Product Type
                        </th>
                        <th class="px-5 py-3 text-left font-semibold text-slate-600">
                            Developer Share %
                        </th>
                        <th class="px-5 py-3 text-left font-semibold text-slate-600">
                            Esubiz Share %
                        </th>
                        <th class="px-5 py-3 text-left font-semibold text-slate-600">
                            Expense %
                        </th>
                        <th class="px-5 py-3 text-left font-semibold text-slate-600">
                            Active
                        </th>
                        <th class="px-5 py-3 text-right font-semibold text-slate-600">
                            Action
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @foreach($productTypes as $productType)
                        @php
                            $financial = $financialRules->firstWhere(
                                'product_type',
                                $productType
                            );

                            $developerShare = (float) (
                                $financial?->developer_share_percent ?? 0
                            );
                        @endphp

                        <tr>
                            <form
                                method="POST"
                                action="{{ route('admin.marketplace.settings.financial.update') }}"
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
                                    <input
                                        type="number"
                                        name="developer_share_percent"
                                        value="{{ $financial?->developer_share_percent ?? 0 }}"
                                        min="0"
                                        max="100"
                                        step="0.01"
                                        required
                                        class="w-28 rounded-lg border border-slate-300 px-3 py-2"
                                    >
                                </td>

                                <td class="px-5 py-4 text-slate-700">
                                    {{ number_format(max(0, 100 - $developerShare), 2) }}%
                                </td>

                                <td class="px-5 py-4">
                                    <input
                                        type="number"
                                        name="expense_percent"
                                        value="{{ $financial?->expense_percent ?? 0 }}"
                                        min="0"
                                        max="100"
                                        step="0.01"
                                        required
                                        class="w-28 rounded-lg border border-slate-300 px-3 py-2"
                                    >
                                </td>

                                <td class="px-5 py-4">
                                    <input type="hidden" name="is_active" value="0">
                                    <input
                                        type="checkbox"
                                        name="is_active"
                                        value="1"
                                        @checked($financial ? $financial->is_active : true)
                                        class="h-5 w-5 rounded border-slate-300"
                                    >
                                </td>

                                <td class="px-5 py-4 text-right">
                                    <button
                                        type="submit"
                                        class="rounded-lg border border-slate-300 px-4 py-2 font-semibold text-slate-700 hover:bg-slate-50"
                                    >
                                        Save
                                    </button>
                                </td>
                            </form>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- REFERRAL OVERRIDES --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-5">
            <h2 class="text-lg font-semibold text-slate-900">
                Referral Commission Overrides
            </h2>
            <p class="mt-1 text-sm text-slate-500">
                Overrides only Level 1 commission for the selected product type and referrer role. All other Central Referral Plan rules remain authoritative.
            </p>
        </div>

        <form
            method="POST"
            action="{{ route('admin.marketplace.settings.referrals.store') }}"
            class="grid gap-4 lg:grid-cols-4"
        >
            @csrf

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">
                    Product Type
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
                    Referrer Role
                </label>
                <input
                    type="text"
                    name="referrer_role"
                    required
                    maxlength="80"
                    placeholder="user"
                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5"
                >
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-700">
                    Level 1 Commission %
                </label>
                <input
                    type="number"
                    name="level_one_commission_percent"
                    min="0"
                    max="100"
                    step="0.01"
                    required
                    class="w-full rounded-xl border border-slate-300 px-4 py-2.5"
                >
            </div>

            <div class="flex items-end gap-4">
                <label class="mb-2 inline-flex items-center gap-2">
                    <input type="hidden" name="is_active" value="0">
                    <input
                        type="checkbox"
                        name="is_active"
                        value="1"
                        checked
                        class="h-5 w-5 rounded border-slate-300"
                    >
                    <span class="text-sm font-medium text-slate-700">
                        Active
                    </span>
                </label>

                <button
                    type="submit"
                    class="ml-auto rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700"
                >
                    Save Rule
                </button>
            </div>
        </form>
    </div>

    <div
        id="marketplaceReferralTable"
        class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
    >
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-5 py-3 text-left font-semibold text-slate-600">Product Type</th>
                        <th class="px-5 py-3 text-left font-semibold text-slate-600">Referrer Role</th>
                        <th class="px-5 py-3 text-left font-semibold text-slate-600">Level 1</th>
                        <th class="px-5 py-3 text-left font-semibold text-slate-600">Status</th>
                        <th class="px-5 py-3 text-right font-semibold text-slate-600">Action</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse($referralRules as $rule)
                        <tr>
                            <td class="px-5 py-4">
                                {{ $productLabel($rule->product_type) }}
                            </td>
                            <td class="px-5 py-4">
                                {{ ucwords(str_replace('_', ' ', $rule->referrer_role)) }}
                            </td>
                            <td class="px-5 py-4">
                                {{ number_format((float) $rule->level_one_commission_percent, 2) }}%
                            </td>
                            <td class="px-5 py-4">
                                {{ $rule->is_active ? 'Active' : 'Inactive' }}
                            </td>
                            <td class="px-5 py-4 text-right">
                                <form
                                    method="POST"
                                    action="{{ route('admin.marketplace.settings.referrals.destroy', $rule) }}"
                                    class="inline"
                                    onsubmit="return confirm('Remove this referral override?')"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="font-semibold text-red-600 hover:text-red-700"
                                    >
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-slate-500">
                                No Marketplace referral overrides configured.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($referralRules->hasPages())
            <div class="flex items-center justify-between border-t border-slate-200 px-5 py-4">
                <div class="text-xs text-slate-500">
                    Showing {{ $referralRules->firstItem() }}–{{ $referralRules->lastItem() }}
                    of {{ $referralRules->total() }}
                </div>

                <div class="flex gap-2">
                    @if($referralRules->previousPageUrl())
                        <a
                            href="{{ $referralRules->previousPageUrl() }}"
                            data-referral-page
                            class="rounded-lg border border-slate-300 px-3 py-2 text-sm"
                        >
                            Previous
                        </a>
                    @endif

                    @if($referralRules->nextPageUrl())
                        <a
                            href="{{ $referralRules->nextPageUrl() }}"
                            data-referral-page
                            class="rounded-lg border border-slate-300 px-3 py-2 text-sm"
                        >
                            Next
                        </a>
                    @endif
                </div>
            </div>
        @endif
    </div>

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
            method="POST"
            action="{{ route('admin.marketplace.categories.store') }}"
            class="grid gap-4 lg:grid-cols-2"
        >
            @csrf

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

                <div class="flex flex-wrap gap-4">
                    @foreach($productTypes as $productType)
                        <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                            <input
                                type="checkbox"
                                name="product_types[]"
                                value="{{ $productType }}"
                                @checked(in_array($productType, old('product_types', []), true))
                                class="rounded border-slate-300"
                            >
                            {{ $productLabel($productType) }}
                        </label>
                    @endforeach
                </div>

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

                <button
                    type="submit"
                    class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700"
                >
                    Add Category
                </button>
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

<script>
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
</script>
@endsection
