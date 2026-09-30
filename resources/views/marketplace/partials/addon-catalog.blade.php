{{-- ESUBIZ_SHARED_ADDON_MARKETPLACE_CATALOG_V831 --}}
@php
    $esuProducts = collect($marketplaceProducts ?? []);
    $esuCategories = collect($marketplaceCategories ?? []);

    $esuBackUrl = $marketplaceBackUrl ?? null;
    $esuBackLabel = $marketplaceBackLabel ?? 'Back to Add-ons';

    $esuCoreContext = (bool) ($marketplaceCoreContext ?? false);
@endphp

<style>
    [data-addon-marketplace-navigation] button.bg-blue-800,
    [data-addon-marketplace-navigation] button.bg-blue-800:hover,
    [data-addon-marketplace-navigation] button.bg-blue-800:focus {
        background-color: #1e40af !important;
        color: #fff !important;
    }

    [data-addon-marketplace-navigation] button.bg-blue-600,
    [data-addon-marketplace-navigation] button.bg-blue-600:hover,
    [data-addon-marketplace-navigation] button.bg-blue-600:focus {
        background-color: #2563eb !important;
        color: #fff !important;
    }

    .addon-marketplace-preview {
        height: 176px;
    }

    @media (max-width: 639px) {
        #addonMarketplaceGrid .addon-marketplace-card {
            min-height: 0 !important;
            height: auto !important;
        }

        #addonMarketplaceGrid .addon-marketplace-preview {
            height: 100px !important;
            min-height: 100px !important;
            max-height: 100px !important;
        }

        #addonMarketplaceGrid .addon-marketplace-preview img {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
        }
    }
</style>

<div class="min-h-screen bg-slate-50 px-4 py-8 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl">

        @if($esuBackUrl)
            <div class="mb-6">
                <a
                    href="{{ $esuBackUrl }}"
                    class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-black text-slate-700 shadow-sm transition hover:border-blue-200 hover:text-blue-600 hover:shadow-md"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    {{ $esuBackLabel }}
                </a>
            </div>
        @endif

        <div class="mb-8">
            <div class="text-xs font-black uppercase tracking-[.18em] text-blue-600">
                Esubiz Marketplace
            </div>

            <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950">
                Add-ons Marketplace
            </h1>

            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                Extend your website with individual Add-ons or complete Bundles.
            </p>
        </div>

        <div class="mb-8 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <input
                id="addonMarketplaceSearch"
                type="search"
                placeholder="Search Add-ons and Bundles..."
                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white"
            >
        </div>

        {{-- Unified main/sub-tab navigation --}}
        <div class="mb-6" data-addon-marketplace-navigation>
            <div
                class="flex gap-2 overflow-x-auto pb-2"
                data-addon-ranking-tabs
            >
                @foreach([
                    'all' => 'All Products',
                    'categories' => 'Categories',
                    'featured' => 'Featured',
                    'purchased' => 'Most Purchased',
                ] as $esuTabKey => $esuTabLabel)
                    <button
                        type="button"
                        data-addon-ranking-tab="{{ $esuTabKey }}"
                        class="shrink-0 rounded-xl border border-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition
                            {{ $esuTabKey === 'all'
                                ? 'bg-blue-800 hover:bg-blue-800'
                                : 'bg-blue-600 hover:bg-blue-600' }}"
                    >
                        {{ $esuTabLabel }}
                    </button>
                @endforeach
            </div>

            {{-- All Products sub-tabs --}}
            <div class="mt-3" data-addon-type-panel>
                <div
                    class="flex gap-2 overflow-x-auto pb-1"
                    data-addon-type-tabs
                >
                    @foreach([
                        'all' => 'All',
                        'addon' => 'Add-ons',
                        'bundle' => 'Bundles',
                    ] as $esuTypeKey => $esuTypeLabel)
                        <button
                            type="button"
                            data-addon-type-tab="{{ $esuTypeKey }}"
                            class="shrink-0 rounded-xl border border-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition
                                {{ $esuTypeKey === 'all'
                                    ? 'bg-blue-800 hover:bg-blue-800'
                                    : 'bg-blue-600 hover:bg-blue-600' }}"
                        >
                            {{ $esuTypeLabel }}
                        </button>
                    @endforeach
                </div>
            </div>

            {{-- Categories sub-tabs --}}
            <div
                class="mt-3 hidden"
                data-addon-category-panel
            >
                <div
                    class="flex gap-2 overflow-x-auto pb-1"
                    data-addon-category-tabs
                >
                    <button
                        type="button"
                        data-addon-category-tab="all"
                        class="shrink-0 rounded-xl border border-blue-600 bg-blue-800 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800"
                    >
                        All Categories
                    </button>

                    @foreach($esuCategories as $esuCategory)
                        @php
                            $esuCategoryId = is_array($esuCategory)
                                ? ($esuCategory['id'] ?? null)
                                : ($esuCategory->id ?? null);

                            $esuCategoryName = is_array($esuCategory)
                                ? ($esuCategory['name'] ?? null)
                                : ($esuCategory->name ?? null);
                        @endphp

                        @if($esuCategoryId && $esuCategoryName)
                            <button
                                type="button"
                                data-addon-category-tab="{{ $esuCategoryId }}"
                                class="shrink-0 rounded-xl border border-blue-600 bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-600"
                            >
                                {{ $esuCategoryName }}
                            </button>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>

        <div
            id="addonMarketplaceGrid"
            class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-5 xl:grid-cols-3"
        >
            @forelse($esuProducts as $product)
                @php
                    $type = strtolower((string) ($product['type'] ?? 'addon'));
                    $isBundle = $type === 'bundle';

                    $productCategories = collect(
                        $product['categories']
                        ?? data_get($product, 'marketplace.categories', [])
                    );

                    $categoryIds = $productCategories
                        ->map(function ($category) {
                            return is_array($category)
                                ? ($category['id'] ?? null)
                                : ($category->id ?? null);
                        })
                        ->filter()
                        ->map(fn ($id) => (int) $id)
                        ->unique()
                        ->values();

                    $featured = (bool) (
                        $product['featured']
                        ?? data_get($product, 'marketplace.featured', false)
                    );

                    $purchaseCount = (int) (
                        $product['purchase_count']
                        ?? data_get($product, 'marketplace.purchase_count', 0)
                    );

                    $createdAt =
                        $product['marketplace_created_at']
                        ?? data_get($product, 'marketplace.created_at')
                        ?? null;

                    $createdTimestamp = $createdAt
                        ? \Illuminate\Support\Carbon::parse($createdAt)->timestamp
                        : 0;

                    $preview =
                        $product['preview']
                        ?? $product['preview_url']
                        ?? $product['preview_image']
                        ?? null;

                    $price = $product['price'] ?? null;
                    $currency = strtoupper(
                        trim((string) ($product['currency'] ?? ''))
                    );

                    $billingLabel = data_get($product, 'billing.label');

                    if (!$billingLabel) {
                        $billingPeriod =
                            $product['billing_period']
                            ?? null;

                        $billingInterval =
                            $product['billing_interval']
                            ?? null;

                        if ($billingPeriod && $billingInterval) {
                            $billingLabel =
                                (int) $billingPeriod === 1
                                    ? strtolower((string) $billingInterval)
                                    : (
                                        (int) $billingPeriod
                                        . ' '
                                        . strtolower((string) $billingInterval)
                                        . 's'
                                    );
                        }
                    }

                    $features = collect(
                        $product['features']
                        ?? $product['capability_allocations']
                        ?? []
                    )->map(function ($allocation) use ($product) {
                        $allocation = is_array($allocation)
                            ? $allocation
                            : (array) $allocation;

                        if (
                            array_key_exists('name', $allocation)
                            && array_key_exists('value', $allocation)
                        ) {
                            return [
                                'name' => $allocation['name'],
                                'value' => $allocation['value'],
                            ];
                        }

                        $name =
                            $allocation['display_name']
                            ?? $allocation['capability_name']
                            ?? $allocation['name']
                            ?? $allocation['capability_key']
                            ?? 'Feature';

                        $value = (bool) (
                            $allocation['is_unlimited']
                            ?? false
                        )
                            ? 'Unlimited'
                            : (
                                isset($allocation['allocation'])
                                && $allocation['allocation'] !== null
                                    ? rtrim(
                                        rtrim(
                                            number_format(
                                                (float) $allocation['allocation'],
                                                2
                                            ),
                                            '0'
                                        ),
                                        '.'
                                    )
                                    : null
                            );

                        $unit =
                            $allocation['display_unit']
                            ?? $product['allocation_unit']
                            ?? null;

                        return [
                            'name' => $name,
                            'value' => trim(
                                ($value ?? '')
                                . ($unit ? ' ' . $unit : '')
                            ),
                        ];
                    })->values()->all();

                    $items = collect(
                        $product['items']
                        ?? $product['bundle_items']
                        ?? []
                    )->map(function ($item) {
                        $item = is_array($item)
                            ? $item
                            : (array) $item;

                        if (
                            array_key_exists('name', $item)
                            && array_key_exists('value', $item)
                        ) {
                            return [
                                'name' => $item['name'],
                                'value' => $item['value'],
                            ];
                        }

                        $value = (bool) (
                            $item['is_unlimited']
                            ?? false
                        )
                            ? 'Unlimited'
                            : (
                                isset($item['allocation'])
                                && $item['allocation'] !== null
                                    ? rtrim(
                                        rtrim(
                                            number_format(
                                                (float) $item['allocation'],
                                                2
                                            ),
                                            '0'
                                        ),
                                        '.'
                                    )
                                    : 'Included'
                            );

                        $unit = $item['allocation_unit'] ?? null;

                        if (
                            $value !== 'Unlimited'
                            && $value !== 'Included'
                            && $unit
                        ) {
                            $value .= ' ' . $unit;
                        }

                        return [
                            'name' => $item['name'] ?? 'Add-on',
                            'value' => $value,
                        ];
                    })->values()->all();

                    $modalProduct = [
                        'type' => $type,
                        'id' => (int) ($product['id'] ?? 0),
                        'name' => $product['name'] ?? 'Product',
                        'description' => $product['description'] ?? null,
                        'preview' => $preview,
                        'features' => $features,
                        'items' => $items,
                    ];

                    $entitled = (bool) ($product['entitled'] ?? false);
                    $installed = (bool) ($product['installed'] ?? false);
                    $canPurchase = (bool) ($product['can_purchase'] ?? true);
                @endphp

                <article
                    class="addon-marketplace-card flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-md"
                    data-product-type="{{ $type }}"
                    data-product-id="{{ (int) ($product['id'] ?? 0) }}"
                    data-category-ids="{{ $categoryIds->implode(',') }}"
                    data-featured="{{ $featured ? '1' : '0' }}"
                    data-purchase-count="{{ $purchaseCount }}"
                    data-created-at="{{ $createdTimestamp }}"
                    data-original-order="{{ $loop->index }}"
                    data-search="{{ strtolower(
                        ($product['name'] ?? '')
                        . ' '
                        . ($product['description'] ?? '')
                        . ' '
                        . $type
                    ) }}"
                >
                    <div class="addon-marketplace-preview relative overflow-hidden border-b border-slate-200 bg-slate-100">
                        @if($preview)
                            <img
                                src="{{ $preview }}"
                                alt="{{ $product['name'] ?? 'Product' }} preview"
                                class="h-full w-full object-cover"
                                loading="lazy"
                            >
                        @else
                            <div class="flex h-full items-center justify-center bg-gradient-to-br from-slate-950 via-slate-900 to-blue-950 text-white">
                                <div class="text-center">
                                    <div class="mx-auto grid h-12 w-12 place-items-center rounded-xl bg-white/10 text-xl">
                                        ◈
                                    </div>

                                    <div class="mt-3 text-sm font-black">
                                        {{ $product['name'] ?? 'Esubiz Product' }}
                                    </div>

                                    <div class="mt-1 text-[10px] font-bold text-slate-300">
                                        Preview unavailable
                                    </div>
                                </div>
                            </div>
                        @endif

                        <span class="absolute left-4 top-4 rounded-full bg-white/95 px-3 py-1 text-[9px] font-black uppercase tracking-wide text-slate-700 shadow-sm">
                            {{ $isBundle ? 'Bundle' : 'Add-on' }}
                        </span>
                    </div>

                    <div class="flex flex-1 flex-col p-3 sm:p-5">
                        <h2 class="line-clamp-1 text-sm font-black text-slate-950 sm:text-lg">
                            {{ $product['name'] ?? 'Esubiz Product' }}
                        </h2>

                        @if(!empty($product['description']))
                            <p class="mt-1 line-clamp-1 text-xs leading-5 text-slate-500 sm:mt-2 sm:line-clamp-3 sm:text-sm sm:leading-6">
                                {{ $product['description'] }}
                            </p>
                        @endif

                        <div class="mt-2 rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1.5 sm:mt-5 sm:rounded-xl sm:p-3">
                            <div class="text-[9px] font-black uppercase tracking-[.14em] text-slate-400">
                                Price
                            </div>

                            <div class="mt-1 text-sm font-black text-slate-950 sm:text-lg">
                                @if($price === null || (float) $price <= 0)
                                    Free
                                @else
                                    {{ $currency }}
                                    {{ number_format((float) $price, 2) }}

                                    @if($billingLabel)
                                        <span class="text-[11px] font-bold text-slate-500 sm:text-sm">
                                            / {{ $billingLabel }}
                                        </span>
                                    @endif
                                @endif
                            </div>
                        </div>

                        <div class="mt-auto grid grid-cols-2 gap-2 pt-2 sm:pt-5">
                            <button
                                type="button"
                                class="addon-features-trigger rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-black text-slate-700 transition hover:bg-slate-50"
                                data-product='@json($modalProduct)'
                            >
                                View Features
                            </button>

                            @if($esuCoreContext && $installed)
                                <a
                                    href="{{ $marketplaceManageUrl ?? $esuBackUrl ?? '#' }}"
                                    class="inline-flex items-center justify-center rounded-xl bg-slate-950 px-4 py-2.5 text-xs font-black text-white transition hover:bg-slate-800"
                                >
                                    Manage
                                </a>
                            @elseif(
                                $esuCoreContext
                                && in_array($type, ['addon', 'bundle'], true)
                                && $canPurchase
                            )
                                <button
                                    type="button"
                                    class="addon-core-buy-trigger rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-black text-white transition hover:bg-blue-700 disabled:cursor-wait disabled:opacity-60"
                                    data-product-type="{{ $type }}"
                                    data-product-id="{{ (int) ($product['id'] ?? 0) }}"
                                >
                                    {{ $price === null || (float) $price <= 0 ? 'Get' : 'Purchase' }}
                                </button>
                            @else
                                <button
                                    type="button"
                                    class="addon-buy-trigger rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-black text-white transition hover:bg-blue-700"
                                    data-product-type="{{ $type }}"
                                    data-product-id="{{ (int) ($product['id'] ?? 0) }}"
                                    data-product-name="{{ $product['name'] ?? 'Product' }}"
                                >
                                    {{ $price === null || (float) $price <= 0 ? 'Get' : 'Purchase' }}
                                </button>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center">
                    <div class="text-lg font-black text-slate-900">
                        No Add-ons or Bundles available
                    </div>
                </div>
            @endforelse
        </div>

        <div
            id="addonMarketplaceEmpty"
            class="mt-5 hidden rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center"
        >
            <div class="text-lg font-black text-slate-900">
                No products match your search
            </div>
        </div>
    </div>
</div>

{{-- Shared Features modal --}}
<div
    id="addonFeaturesModal"
    class="fixed inset-0 z-[3500] hidden overflow-y-auto bg-slate-950/70 p-3 backdrop-blur-sm sm:p-6"
    aria-hidden="true"
>
    <div
        class="mx-auto w-full max-w-3xl overflow-hidden rounded-3xl bg-white shadow-2xl"
        style="display:flex;flex-direction:column;position:fixed;top:calc(76px + env(safe-area-inset-top, 0px));bottom:calc(12px + env(safe-area-inset-bottom, 0px));left:12px;right:12px;width:auto;max-width:768px;margin:0 auto;"
    >
        <div class="relative z-20 flex shrink-0 items-center justify-between gap-4 border-b border-slate-200 bg-white px-5 py-4 sm:px-6">
            <div>
                <div
                    id="addonFeaturesType"
                    class="text-[10px] font-black uppercase tracking-[.16em] text-blue-600"
                >
                    Add-on
                </div>

                <h2
                    id="addonFeaturesTitle"
                    class="mt-1 text-xl font-black text-slate-950"
                >
                    Product
                </h2>
            </div>

            <button
                type="button"
                data-addon-features-close
                class="grid h-10 w-10 place-items-center rounded-xl bg-slate-100 text-xl font-black text-slate-700 transition hover:bg-slate-200"
                aria-label="Close"
            >
                ×
            </button>
        </div>

        <div
            class="p-5 sm:p-6"
            style="flex:1 1 auto;min-height:0;overflow-y:auto;overscroll-behavior:contain;"
        >
            <img
                id="addonFeaturesPreview"
                src=""
                alt=""
                class="mb-5 hidden max-h-[360px] w-full rounded-2xl border border-slate-200 object-cover"
            >

            <p
                id="addonFeaturesDescription"
                class="hidden text-sm leading-6 text-slate-600"
            ></p>

            <div id="addonFeaturesSection" class="mt-6">
                <div class="mb-3 text-xs font-black uppercase tracking-[.14em] text-slate-400">
                    Features
                </div>

                <div
                    id="addonFeaturesList"
                    class="grid gap-3 sm:grid-cols-2"
                ></div>
            </div>
        </div>
    </div>
</div>

@if(!$esuCoreContext)
    {{-- Central purchase modal --}}
    <div
        id="addonPurchaseModal"
        class="fixed inset-0 z-[3500] hidden overflow-y-auto bg-slate-950/70 p-3 backdrop-blur-sm sm:p-6"
        aria-hidden="true"
    >
        <div class="mx-auto my-auto w-full max-w-lg overflow-hidden rounded-3xl bg-white shadow-2xl">
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4">
                <div>
                    <div class="text-[10px] font-black uppercase tracking-[.16em] text-blue-600">
                        Checkout
                    </div>

                    <h2
                        id="addonPurchaseTitle"
                        class="mt-1 text-xl font-black text-slate-950"
                    >
                        Product
                    </h2>
                </div>

                <button
                    type="button"
                    data-addon-purchase-close
                    class="grid h-10 w-10 place-items-center rounded-xl bg-slate-100 text-xl font-black text-slate-700"
                >
                    ×
                </button>
            </div>

            <form
                method="POST"
                action="{{ route('marketplace.checkout.create') }}"
                class="p-6"
            >
                @csrf

                <input
                    id="addonPurchaseType"
                    type="hidden"
                    name="product_type"
                >

                <input
                    id="addonPurchaseId"
                    type="hidden"
                    name="product_id"
                >

                <input
                    type="hidden"
                    name="deployment_type"
                    value="saas"
                >

                <label class="text-xs font-black uppercase tracking-wider text-slate-500">
                    Website
                </label>

                <select
                    name="website_id"
                    required
                    class="mt-2 w-full rounded-xl border-slate-200 bg-white text-sm focus:border-blue-500 focus:ring-blue-500"
                >
                    <option value="">
                        Select website
                    </option>

                    @foreach(
                        \App\Models\Website::query()
                            ->where('owner_id', auth()->id())
                            ->orderBy('name')
                            ->get()
                        as $esuWebsite
                    )
                        <option value="{{ $esuWebsite->id }}">
                            {{ $esuWebsite->name }}
                        </option>
                    @endforeach
                </select>

                <button
                    type="submit"
                    class="mt-5 w-full rounded-xl bg-blue-600 px-4 py-3 text-sm font-black text-white hover:bg-blue-700"
                >
                    Continue to Checkout
                </button>
            </form>
        </div>
    </div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {
    const featureModal =
        document.getElementById('addonFeaturesModal');

    const purchaseModal =
        document.getElementById('addonPurchaseModal');

    const closeFeature = () => {
        featureModal?.classList.add('hidden');
        featureModal?.setAttribute('aria-hidden', 'true');
        document.documentElement.classList.remove('overflow-hidden');
    };

    const closePurchase = () => {
        purchaseModal?.classList.add('hidden');
        purchaseModal?.setAttribute('aria-hidden', 'true');
        document.documentElement.classList.remove('overflow-hidden');
    };

    document
        .querySelectorAll('.addon-features-trigger')
        .forEach(button => {
            button.addEventListener('click', function () {
                const product =
                    JSON.parse(this.dataset.product || '{}');

                document.getElementById(
                    'addonFeaturesTitle'
                ).textContent =
                    product.name || 'Product';

                document.getElementById(
                    'addonFeaturesType'
                ).textContent =
                    product.type === 'bundle'
                        ? 'Bundle'
                        : 'Add-on';

                const description =
                    document.getElementById(
                        'addonFeaturesDescription'
                    );

                description.textContent =
                    product.description || '';

                description.classList.toggle(
                    'hidden',
                    !product.description
                );

                const preview =
                    document.getElementById(
                        'addonFeaturesPreview'
                    );

                if (product.preview) {
                    preview.src = product.preview;
                    preview.alt =
                        (product.name || 'Product')
                        + ' preview';
                    preview.classList.remove('hidden');
                } else {
                    preview.removeAttribute('src');
                    preview.classList.add('hidden');
                }

                const list =
                    document.getElementById(
                        'addonFeaturesList'
                    );

                list.innerHTML = '';

                const rows =
                    product.type === 'bundle'
                        ? (product.items || [])
                        : (product.features || []);

                rows.forEach(row => {
                    const item =
                        document.createElement('div');

                    item.className =
                        'flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3';

                    const name =
                        document.createElement('span');

                    name.className =
                        'text-sm font-bold text-slate-800';

                    name.textContent =
                        row.name || 'Feature';

                    const value =
                        document.createElement('span');

                    value.className =
                        'whitespace-nowrap rounded-full bg-white px-2.5 py-1 text-[10px] font-black text-blue-700 ring-1 ring-slate-200';

                    value.textContent =
                        row.value || 'Included';

                    item.append(name, value);
                    list.appendChild(item);
                });

                if (!rows.length) {
                    const empty =
                        document.createElement('div');

                    empty.className =
                        'sm:col-span-2 rounded-xl border border-dashed border-slate-200 p-5 text-center text-sm text-slate-400';

                    empty.textContent =
                        'No additional feature details available.';

                    list.appendChild(empty);
                }

                featureModal?.classList.remove('hidden');
                featureModal?.setAttribute(
                    'aria-hidden',
                    'false'
                );

                document.documentElement.classList.add(
                    'overflow-hidden'
                );
            });
        });

    document
        .querySelectorAll('[data-addon-features-close]')
        .forEach(button =>
            button.addEventListener(
                'click',
                closeFeature
            )
        );

    featureModal?.addEventListener('click', event => {
        if (event.target === featureModal) {
            closeFeature();
        }
    });

    @if(!$esuCoreContext)
        document
            .querySelectorAll('.addon-buy-trigger')
            .forEach(button => {
                button.addEventListener(
                    'click',
                    function () {
                        document.getElementById(
                            'addonPurchaseType'
                        ).value =
                            this.dataset.productType
                            || 'addon';

                        document.getElementById(
                            'addonPurchaseId'
                        ).value =
                            this.dataset.productId
                            || '';

                        document.getElementById(
                            'addonPurchaseTitle'
                        ).textContent =
                            this.dataset.productName
                            || 'Product';

                        purchaseModal?.classList.remove(
                            'hidden'
                        );

                        purchaseModal?.setAttribute(
                            'aria-hidden',
                            'false'
                        );

                        document.documentElement.classList.add(
                            'overflow-hidden'
                        );
                    }
                );
            });

        document
            .querySelectorAll(
                '[data-addon-purchase-close]'
            )
            .forEach(button =>
                button.addEventListener(
                    'click',
                    closePurchase
                )
            );

        purchaseModal?.addEventListener(
            'click',
            event => {
                if (event.target === purchaseModal) {
                    closePurchase();
                }
            }
        );
    @else
        const coreCheckoutUrl =
            @json($marketplaceCoreCheckoutUrl ?? null);

        document
            .querySelectorAll('.addon-core-buy-trigger')
            .forEach(button => {
                button.addEventListener(
                    'click',
                    async function () {
                        if (!coreCheckoutUrl) {
                            return;
                        }

                        const originalText =
                            this.textContent;

                        this.disabled = true;
                        this.textContent = 'Please wait...';

                        try {
                            const response = await fetch(
                                coreCheckoutUrl,
                                {
                                    method: 'POST',
                                    headers: {
                                        'Accept':
                                            'application/json',
                                        'Content-Type':
                                            'application/json',
                                        'X-CSRF-TOKEN':
                                            document.querySelector(
                                                'meta[name="csrf-token"]'
                                            )?.content || '',
                                    },
                                    body: JSON.stringify({
                                        product_type:
                                            this.dataset
                                                .productType,
                                        product_id:
                                            Number(
                                                this.dataset
                                                    .productId
                                            ),
                                    }),
                                }
                            );

                            const rawBody =
                                await response.text();

                            let data = {};

                            try {
                                data = rawBody
                                    ? JSON.parse(rawBody)
                                    : {};
                            } catch (parseError) {
                                data = {};
                            }

                            if (
                                !response.ok
                                || !data.url
                            ) {
                                const validationMessage =
                                    data.errors
                                        ? Object.values(data.errors)
                                            .flat()
                                            .join(' ')
                                        : '';

                                throw new Error(
                                    validationMessage
                                    || data.message
                                    || (
                                        'Checkout failed (HTTP '
                                        + response.status
                                        + '). '
                                        + rawBody.substring(0, 300)
                                    )
                                );
                            }

                            if (!window.EsubizCoreCheckout) {
                                throw new Error('Please refresh this page to open checkout.');
                            }
                            window.EsubizCoreCheckout.open(data.url);
                        } catch (error) {
                            alert(
                                error.message
                                || 'Unable to start checkout.'
                            );

                            this.disabled = false;
                            this.textContent =
                                originalText;
                        }
                    }
                );
            });
    @endif

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') {
            closeFeature();
            closePurchase();
        }
    });
});

/* Unified Marketplace filtering */
document.addEventListener('DOMContentLoaded', function () {
    const grid =
        document.getElementById('addonMarketplaceGrid');

    const search =
        document.getElementById('addonMarketplaceSearch');

    const empty =
        document.getElementById('addonMarketplaceEmpty');

    const mainRoot =
        document.querySelector(
            '[data-addon-ranking-tabs]'
        );

    const typePanel =
        document.querySelector(
            '[data-addon-type-panel]'
        );

    const typeRoot =
        document.querySelector(
            '[data-addon-type-tabs]'
        );

    const categoryPanel =
        document.querySelector(
            '[data-addon-category-panel]'
        );

    const categoryRoot =
        document.querySelector(
            '[data-addon-category-tabs]'
        );

    if (!grid) return;

    const cards = Array.from(
        grid.querySelectorAll(
            '.addon-marketplace-card'
        )
    );

    const mainButtons = mainRoot
        ? Array.from(
            mainRoot.querySelectorAll(
                '[data-addon-ranking-tab]'
            )
        )
        : [];

    const typeButtons = typeRoot
        ? Array.from(
            typeRoot.querySelectorAll(
                '[data-addon-type-tab]'
            )
        )
        : [];

    const categoryButtons = categoryRoot
        ? Array.from(
            categoryRoot.querySelectorAll(
                '[data-addon-category-tab]'
            )
        )
        : [];

    let activeMain = 'all';
    let activeType = 'all';
    let activeCategory = 'all';
    let searchQuery = '';

    function setActive(buttons, activeButton) {
        buttons.forEach(function (button) {
            const active =
                button === activeButton;

            button.classList.toggle(
                'bg-blue-800',
                active
            );

            button.classList.toggle(
                'bg-blue-600',
                !active
            );

            button.classList.add(
                'border',
                'border-blue-600',
                'text-white'
            );

            button.classList.toggle(
                'shadow-sm',
                active
            );

            button.style.setProperty(
                'background-color',
                active ? '#1e40af' : '#2563eb',
                'important'
            );
            button.style.setProperty(
                'color',
                '#ffffff',
                'important'
            );
        });
    }

    function updatePanels() {
        typePanel?.classList.toggle(
            'hidden',
            activeMain !== 'all'
        );

        categoryPanel?.classList.toggle(
            'hidden',
            activeMain !== 'categories'
        );
    }

    function applyMarketplaceState() {
        let visible = 0;

        cards.forEach(function (card) {
            const productType =
                String(
                    card.dataset.productType || ''
                );

            const categories =
                String(
                    card.dataset.categoryIds || ''
                )
                    .split(',')
                    .map(value => value.trim())
                    .filter(Boolean);

            const searchMatch =
                !searchQuery
                || String(
                    card.dataset.search || ''
                ).includes(searchQuery);

            let mainMatch = true;

            if (activeMain === 'all') {
                mainMatch =
                    activeType === 'all'
                    || productType === activeType;
            }

            if (activeMain === 'categories') {
                mainMatch =
                    activeCategory === 'all'
                    || categories.includes(
                        activeCategory
                    );
            }

            if (activeMain === 'featured') {
                mainMatch =
                    card.dataset.featured === '1';
            }

            const show =
                searchMatch
                && mainMatch;

            card.classList.toggle(
                'hidden',
                !show
            );

            if (show) {
                visible++;
            }
        });

        const sorted = [...cards].sort(
            function (a, b) {
                if (activeMain === 'purchased') {
                    const purchases =
                        Number(
                            b.dataset.purchaseCount
                            || 0
                        )
                        - Number(
                            a.dataset.purchaseCount
                            || 0
                        );

                    if (purchases !== 0) {
                        return purchases;
                    }

                    return (
                        Number(
                            b.dataset.createdAt
                            || 0
                        )
                        - Number(
                            a.dataset.createdAt
                            || 0
                        )
                    );
                }

                return (
                    Number(
                        a.dataset.originalOrder
                        || 0
                    )
                    - Number(
                        b.dataset.originalOrder
                        || 0
                    )
                );
            }
        );

        sorted.forEach(
            card => grid.appendChild(card)
        );

        empty?.classList.toggle(
            'hidden',
            visible > 0
        );
    }

    search?.addEventListener(
        'input',
        function () {
            searchQuery =
                this.value
                    .trim()
                    .toLowerCase();

            applyMarketplaceState();
        }
    );

    mainButtons.forEach(function (button) {
        button.addEventListener(
            'click',
            function () {
                activeMain =
                    String(
                        button.dataset
                            .addonRankingTab
                        || 'all'
                    );

                setActive(
                    mainButtons,
                    button
                );

                updatePanels();
                applyMarketplaceState();
            }
        );
    });

    typeButtons.forEach(function (button) {
        button.addEventListener(
            'click',
            function () {
                activeType =
                    String(
                        button.dataset
                            .addonTypeTab
                        || 'all'
                    );

                setActive(
                    typeButtons,
                    button
                );

                applyMarketplaceState();
            }
        );
    });

    categoryButtons.forEach(
        function (button) {
            button.addEventListener(
                'click',
                function () {
                    activeCategory =
                        String(
                            button.dataset
                                .addonCategoryTab
                            || 'all'
                        );

                    setActive(
                        categoryButtons,
                        button
                    );

                    applyMarketplaceState();
                }
            );
        }
    );

    const initialMain =
        mainButtons.find(
            button =>
                button.dataset
                    .addonRankingTab === 'all'
        );

    const initialType =
        typeButtons.find(
            button =>
                button.dataset
                    .addonTypeTab === 'all'
        );

    const initialCategory =
        categoryButtons.find(
            button =>
                button.dataset
                    .addonCategoryTab === 'all'
        );

    if (initialMain) {
        setActive(
            mainButtons,
            initialMain
        );
    }

    if (initialType) {
        setActive(
            typeButtons,
            initialType
        );
    }

    if (initialCategory) {
        setActive(
            categoryButtons,
            initialCategory
        );
    }

    updatePanels();
    applyMarketplaceState();
});
</script>
