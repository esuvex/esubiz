@extends('admin.layouts.app')

@section('content')

{{-- ESUBIZ_ADDON_MOBILE_CARD_HEIGHT_V572 --}}
<style>
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


        <div class="mb-6">
            <a
                href="{{ route('developer.marketplace.addons') }}"
                class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-black text-slate-700 shadow-sm transition hover:border-blue-200 hover:text-blue-600 hover:shadow-md"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back to Add-ons
            </a>
        </div>

        <div class="mb-8">
            <div class="text-xs font-black uppercase tracking-[.18em] text-blue-600">
                Developer Marketplace
            </div>
            <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950">
                Off-server Add-ons Marketplace
            </h1>
            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                Purchase Add-ons and Bundles for your off-server Esubiz Core websites.
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

        @php
            $marketplaceProducts = collect();

            foreach ($addons as $addon) {
                $preview = !empty($addon->preview_image)
                    ? route('marketplace.addons.preview', [
                        'type' => 'addon',
                        'id' => $addon->id,
                    ])
                    : null;

                $features = collect($addon->capability_allocations ?? [])->map(function ($allocation) use ($addon) {
                    $name = $allocation->display_name
                        ?? $allocation->capability_name
                        ?? $allocation->name
                        ?? $allocation->capability_key
                        ?? 'Feature';

                    $value = (bool) ($allocation->is_unlimited ?? false)
                        ? 'Unlimited'
                        : (
                            isset($allocation->allocation) && $allocation->allocation !== null
                                ? rtrim(rtrim(number_format((float) $allocation->allocation, 2), '0'), '.')
                                : null
                        );

                    $unit = $allocation->display_unit
                        ?? $addon->allocation_unit
                        ?? null;

                    return [
                        'name' => $name,
                        'value' => trim(($value ?? '') . ($unit ? ' ' . $unit : '')),
                    ];
                })->values()->all();

                $marketplaceProducts->push([
                    'type' => 'addon',
                    'id' => $addon->id,
                    'catalog_product_id' => $addon->catalog_product_id ?? null,
                    'name' => $addon->name,
                    'description' => $addon->description,
                    'preview' => $preview,
                    'features' => $features,
                    'items' => [],
                    'price' => $addon->off_server_price,
                    'currency' => $addon->off_server_currency,
                    'billing_period' => $addon->off_server_billing_period ?? null,
                    'billing_interval' => $addon->off_server_billing_interval ?? null,
                    'featured' => (bool) ($addon->marketplace_featured ?? false),
                    'purchase_count' => (int) ($addon->marketplace_purchase_count ?? 0),
                    'marketplace_created_at' => $addon->marketplace_created_at ?? $addon->created_at ?? null,
                ]);
            }

            foreach ($bundles as $bundle) {
                $preview = !empty($bundle->preview_image)
                    ? route('marketplace.addons.preview', [
                        'type' => 'bundle',
                        'id' => $bundle->id,
                    ])
                    : null;

                $items = collect($bundleItems->get($bundle->id, []))->map(function ($item) {
                    return [
                        'name' => $item->name ?? 'Add-on',
                        'value' => (bool) ($item->is_unlimited ?? false)
                            ? 'Unlimited'
                            : (
                                isset($item->allocation) && $item->allocation !== null
                                    ? rtrim(rtrim(number_format((float) $item->allocation, 2), '0'), '.')
                                    : 'Included'
                            ),
                    ];
                })->values()->all();

                $marketplaceProducts->push([
                    'type' => 'bundle',
                    'id' => $bundle->id,
                    'catalog_product_id' => $bundle->catalog_product_id ?? null,
                    'name' => $bundle->name,
                    'description' => $bundle->description,
                    'preview' => $preview,
                    'features' => [],
                    'items' => $items,
                    'price' => $bundle->off_server_price,
                    'currency' => $bundle->off_server_currency,
                    'billing_period' => $bundle->off_server_billing_period ?? null,
                    'billing_interval' => $bundle->off_server_billing_interval ?? null,
                    'featured' => (bool) ($bundle->marketplace_featured ?? false),
                    'purchase_count' => (int) ($bundle->marketplace_purchase_count ?? 0),
                    'marketplace_created_at' => $bundle->marketplace_created_at ?? $bundle->created_at ?? null,
                ]);
            }
        @endphp

        {{-- ESUBIZ_ADDON_CATEGORY_TABS_V568B --}}
@php
    $esuAddonMarketplaceCategories =
        \App\Models\MarketplaceCategory::query()
            ->active()
            ->ordered()
            ->get()
            ->filter(
                fn ($category) =>
                    $category->supportsProductType('addon')
            )
            ->values();

    $esuProductCategoryMap = [];

    $esuCatalogIds = collect($addons ?? [])
        ->pluck('catalog_product_id')
        ->merge(
            collect($bundles ?? [])
                ->pluck('catalog_product_id')
        )
        ->filter()
        ->map(fn ($id) => (int) $id)
        ->unique()
        ->values();

    if ($esuCatalogIds->isNotEmpty()) {
        $esuProductCategoryMap =
            \Illuminate\Support\Facades\DB::table(
                'catalog_product_marketplace_category'
            )
                ->whereIn(
                    'catalog_product_id',
                    $esuCatalogIds->all()
                )
                ->get()
                ->groupBy('catalog_product_id')
                ->map(
                    fn ($rows) =>
                        $rows->pluck('marketplace_category_id')
                            ->map(fn ($id) => (int) $id)
                            ->values()
                            ->all()
                )
                ->all();

        $esuAssignedCategoryIds = collect($esuProductCategoryMap)
            ->flatten()
            ->map(fn ($id) => (int) $id)
            ->unique();

        $esuAddonMarketplaceCategories =
            $esuAddonMarketplaceCategories
                ->filter(
                    fn ($category) =>
                        $esuAssignedCategoryIds->contains(
                            (int) $category->id
                        )
                )
                ->values();
    } else {
        $esuAddonMarketplaceCategories = collect();
    }
@endphp

{{-- ESUBIZ_ADDON_RANKING_TABS_V590 --}}
<div class="mb-6" data-addon-ranking-filter>
    <div class="flex gap-2 overflow-x-auto pb-2" data-addon-ranking-tabs>
        <button type="button"
                data-addon-ranking-tab="all"
                class="shrink-0 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition">
            All Products
        </button>

        <button type="button"
                data-addon-ranking-tab="new"
                class="shrink-0 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:border-blue-300 hover:text-blue-600">
            New Products
        </button>

        <button type="button"
                data-addon-ranking-tab="featured"
                class="shrink-0 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:border-blue-300 hover:text-blue-600">
            Featured
        </button>

        <button type="button"
                data-addon-ranking-tab="purchased"
                class="shrink-0 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:border-blue-300 hover:text-blue-600">
            Most Purchased
        </button>
    </div>
</div>

<div class="mb-6" data-addon-category-filter>
    <div class="mb-2 text-sm font-semibold text-slate-700">
        Categories
    </div>

    <div
        class="flex gap-2 overflow-x-auto pb-2"
        data-addon-category-tabs
    >
        <button
            type="button"
            data-addon-category-tab="all"
            class="shrink-0 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition"
        >
            All
        </button>

    @foreach($esuAddonMarketplaceCategories as $esuCategory)
        <button
            type="button"
            data-addon-category-tab="{{ $esuCategory->id }}"
            class="shrink-0 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:border-blue-300 hover:text-blue-600"
        >
            {{ $esuCategory->name }}
        </button>
    @endforeach
    </div>
</div>

        <div id="addonMarketplaceGrid" class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-5 xl:grid-cols-3">
            @forelse($marketplaceProducts as $product)
                <article
                    class="addon-marketplace-card flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-md"
                    data-product-type="{{ $product['type'] }}"
                    data-product-id="{{ $product['id'] }}"
                    data-category-ids="{{ collect($esuProductCategoryMap[(int) ($product['catalog_product_id'] ?? 0)] ?? [])->implode(',') }}"
                    data-featured="{{ $product['featured'] ? '1' : '0' }}"
                    data-purchase-count="{{ $product['purchase_count'] }}"
                    data-created-at="{{ $product['marketplace_created_at'] ? \Illuminate\Support\Carbon::parse($product['marketplace_created_at'])->timestamp : 0 }}"
                    data-original-order="{{ $loop->index }}"
                    data-search="{{ strtolower($product['name'].' '.($product['description'] ?? '').' '.$product['type']) }}"
                >
                    <div class="addon-marketplace-preview relative overflow-hidden border-b border-slate-200 bg-slate-100">
                        @if($product['preview'])
                            <img
                                src="{{ $product['preview'] }}"
                                alt="{{ $product['name'] }} preview"
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
                                        {{ $product['name'] }}
                                    </div>
                                    <div class="mt-1 text-[10px] font-bold text-slate-300">
                                        Preview unavailable
                                    </div>
                                </div>
                            </div>
                        @endif

                        <span class="absolute left-4 top-4 rounded-full bg-white/95 px-3 py-1 text-[9px] font-black uppercase tracking-wide text-slate-700 shadow-sm">
                            {{ $product['type'] === 'bundle' ? 'Bundle' : 'Add-on' }}
                        </span>
                    </div>

                    <div class="flex flex-1 flex-col p-3 sm:p-5">
                        <h2 class="line-clamp-1 text-sm font-black text-slate-950 sm:text-lg">
                            {{ $product['name'] }}
                        </h2>

                        @if($product['description'])
                            <p class="mt-1 line-clamp-1 text-xs leading-5 text-slate-500 sm:mt-2 sm:line-clamp-3 sm:text-sm sm:leading-6">
                                {{ $product['description'] }}
                            </p>
                        @endif

                        <div class="mt-2 rounded-lg border border-slate-200 bg-slate-50 px-2.5 py-1.5 sm:mt-5 sm:rounded-xl sm:p-3">
                            <div class="text-[9px] font-black uppercase tracking-[.14em] text-slate-400">
                                Price
                            </div>
                            <div class="mt-1 text-sm font-black text-slate-950 sm:text-lg">
                                @if($product['price'] === null || (float) $product['price'] <= 0)
                                    Free
                                @else
                                    {{ strtoupper($product['currency']) }}
                                    {{ number_format((float) $product['price'], 2) }}
                                @endif
                            </div>

                            @if($product['billing_period'] && $product['billing_interval'])
                                <div class="mt-0.5 text-[11px] font-bold text-slate-500">
                                    {{ $product['billing_period'] }}
                                    {{ ucfirst($product['billing_interval']) }}{{ (int) $product['billing_period'] === 1 ? '' : 's' }}
                                </div>
                            @endif
                        </div>


<div class="mt-auto grid grid-cols-2 gap-2 pt-2 sm:pt-5">
                            <button
                                type="button"
                                class="addon-features-trigger rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-black text-slate-700 transition hover:bg-slate-50"
                                data-product='@json($product)'
                            >
                                View Features
                            </button>

                            <button
                                type="button"
                                class="addon-buy-trigger rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-black text-white transition hover:bg-blue-700"
                                data-product-type="{{ $product['type'] }}"
                                data-product-id="{{ $product['id'] }}"
                                data-product-name="{{ $product['name'] }}"
                            >
                                {{ $product['price'] === null || (float) $product['price'] <= 0 ? 'Get' : 'Purchase' }}
                            </button>
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

        <div id="addonMarketplaceEmpty" class="mt-5 hidden rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center">
            <div class="text-lg font-black text-slate-900">
                No products match your search
            </div>
        </div>
    </div>
</div>

{{-- FEATURES MODAL --}}
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
                <div id="addonFeaturesType" class="text-[10px] font-black uppercase tracking-[.16em] text-blue-600">
                    Add-on
                </div>
                <h2 id="addonFeaturesTitle" class="mt-1 text-xl font-black text-slate-950">
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

            <p id="addonFeaturesDescription" class="hidden text-sm leading-6 text-slate-600"></p>

            <div id="addonFeaturesSection" class="mt-6">
                <div class="mb-3 text-xs font-black uppercase tracking-[.14em] text-slate-400">
                    Features
                </div>
                <div id="addonFeaturesList" class="grid gap-3 sm:grid-cols-2"></div>
            </div>
        </div>
    </div>
</div>

{{-- PURCHASE MODAL --}}
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
                <h2 id="addonPurchaseTitle" class="mt-1 text-xl font-black text-slate-950">
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
            id="developerOffServerPurchaseForm"
            method="GET"
            action=""
            class="p-6"
        >
            <input id="addonPurchaseType" type="hidden" name="product_type">
            <input id="addonPurchaseId" type="hidden" name="product_id">

            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                <div class="text-[10px] font-black uppercase tracking-[.16em] text-slate-400">
                    Deployment
                </div>
                <div class="mt-1 text-sm font-black text-slate-900">
                    Off-server Esubiz Core
                </div>
                <p class="mt-1 text-xs leading-5 text-slate-500">
                    This product will be purchased for an off-server Core installation.
                </p>
            </div>

            <label class="mt-5 block text-xs font-black uppercase tracking-wider text-slate-500">
                Quantity
            </label>

            <input
                type="number"
                name="quantity"
                value="1"
                min="1"
                step="1"
                required
                class="mt-2 w-full rounded-xl border-slate-200 bg-white text-sm focus:border-blue-500 focus:ring-blue-500"
            >

            <button
                type="submit"
                class="mt-5 w-full rounded-xl bg-blue-600 px-4 py-3 text-sm font-black text-white hover:bg-blue-700"
            >
                Continue to Checkout
            </button>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const featureModal = document.getElementById('addonFeaturesModal');
    const purchaseModal = document.getElementById('addonPurchaseModal');

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

    document.querySelectorAll('.addon-features-trigger').forEach(button => {
        button.addEventListener('click', function () {
            const product = JSON.parse(this.dataset.product || '{}');

            document.getElementById('addonFeaturesTitle').textContent =
                product.name || 'Product';

            document.getElementById('addonFeaturesType').textContent =
                product.type === 'bundle' ? 'Bundle' : 'Add-on';

            const description = document.getElementById('addonFeaturesDescription');
            description.textContent = product.description || '';
            description.classList.toggle('hidden', !product.description);

            const preview = document.getElementById('addonFeaturesPreview');

            if (product.preview) {
                preview.src = product.preview;
                preview.alt = (product.name || 'Product') + ' preview';
                preview.classList.remove('hidden');
            } else {
                preview.removeAttribute('src');
                preview.classList.add('hidden');
            }

            const list = document.getElementById('addonFeaturesList');
            list.innerHTML = '';

            const rows = product.type === 'bundle'
                ? (product.items || [])
                : (product.features || []);

            rows.forEach(row => {
                const item = document.createElement('div');
                item.className =
                    'flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-slate-50 p-3';

                const name = document.createElement('span');
                name.className = 'text-sm font-bold text-slate-800';
                name.textContent = row.name || 'Feature';

                const value = document.createElement('span');
                value.className =
                    'whitespace-nowrap rounded-full bg-white px-2.5 py-1 text-[10px] font-black text-blue-700 ring-1 ring-slate-200';
                value.textContent = row.value || 'Included';

                item.append(name, value);
                list.appendChild(item);
            });

            if (!rows.length) {
                const empty = document.createElement('div');
                empty.className =
                    'sm:col-span-2 rounded-xl border border-dashed border-slate-200 p-5 text-center text-sm text-slate-400';
                empty.textContent = 'No additional feature details available.';
                list.appendChild(empty);
            }

            featureModal.classList.remove('hidden');
            featureModal.setAttribute('aria-hidden', 'false');
            document.documentElement.classList.add('overflow-hidden');
        });
    });

    document.querySelectorAll('.addon-buy-trigger').forEach(button => {
        button.addEventListener('click', function () {
            document.getElementById('addonPurchaseType').value =
                this.dataset.productType || 'addon';

            document.getElementById('addonPurchaseId').value =
                this.dataset.productId || '';

            document.getElementById('addonPurchaseTitle').textContent =
                this.dataset.productName || 'Product';

            purchaseModal.classList.remove('hidden');
            purchaseModal.setAttribute('aria-hidden', 'false');
            document.documentElement.classList.add('overflow-hidden');
        });
    });

    const developerOffServerPurchaseForm =
        document.getElementById('developerOffServerPurchaseForm');

    developerOffServerPurchaseForm?.addEventListener('submit', function (event) {
        const type =
            document.getElementById('addonPurchaseType')?.value || '';

        const id =
            document.getElementById('addonPurchaseId')?.value || '';

        if (!type || !id) {
            event.preventDefault();
            return;
        }

        this.action =
            '/marketplace/developer/checkout/'
            + encodeURIComponent(type)
            + '/'
            + encodeURIComponent(id);

        /*
         * product_type/product_id are already represented by the route.
         * Prevent duplicate query parameters.
         */
        document.getElementById('addonPurchaseType')?.removeAttribute('name');
        document.getElementById('addonPurchaseId')?.removeAttribute('name');
    });

    document.querySelectorAll('[data-addon-features-close]').forEach(button =>
        button.addEventListener('click', closeFeature)
    );

    document.querySelectorAll('[data-addon-purchase-close]').forEach(button =>
        button.addEventListener('click', closePurchase)
    );

    featureModal?.addEventListener('click', event => {
        if (event.target === featureModal) closeFeature();
    });

    purchaseModal?.addEventListener('click', event => {
        if (event.target === purchaseModal) closePurchase();
    });

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') {
            closeFeature();
            closePurchase();
        }
    });

});
</script>

@endsection

{{-- ESUBIZ_ADDON_COMBINED_FILTER_V591 --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const grid = document.getElementById('addonMarketplaceGrid');
    const search = document.getElementById('addonMarketplaceSearch');
    const empty = document.getElementById('addonMarketplaceEmpty');

    const categoryRoot =
        document.querySelector('[data-addon-category-tabs]');

    const rankingRoot =
        document.querySelector('[data-addon-ranking-tabs]');

    if (!grid) return;

    const cards = Array.from(
        grid.querySelectorAll('.addon-marketplace-card')
    );

    const categoryButtons = categoryRoot
        ? Array.from(
            categoryRoot.querySelectorAll(
                '[data-addon-category-tab]'
            )
        )
        : [];

    const rankingButtons = rankingRoot
        ? Array.from(
            rankingRoot.querySelectorAll(
                '[data-addon-ranking-tab]'
            )
        )
        : [];

    let activeCategory = 'all';
    let activeRanking = 'all';
    let searchQuery = '';

    function setActive(buttons, activeButton) {
        buttons.forEach(function (button) {
            const active = button === activeButton;

            button.classList.toggle('bg-blue-600', active);
            button.classList.toggle('text-white', active);
            button.classList.toggle('shadow-sm', active);

            button.classList.toggle('bg-white', !active);
            button.classList.toggle('text-slate-600', !active);
            button.classList.toggle('border', !active);
            button.classList.toggle('border-slate-200', !active);
        });
    }

    function applyMarketplaceState() {
        let visible = 0;

        cards.forEach(function (card) {
            const categories = String(
                card.dataset.categoryIds || ''
            )
                .split(',')
                .map(value => value.trim())
                .filter(Boolean);

            const categoryMatch =
                activeCategory === 'all'
                || categories.includes(activeCategory);

            const searchMatch =
                !searchQuery
                || String(card.dataset.search || '')
                    .includes(searchQuery);

            const rankingMatch =
                activeRanking !== 'featured'
                || card.dataset.featured === '1';

            const show =
                categoryMatch
                && searchMatch
                && rankingMatch;

            card.classList.toggle('hidden', !show);

            if (show) visible++;
        });

        const sorted = [...cards].sort(function (a, b) {
            if (activeRanking === 'new') {
                return (
                    Number(b.dataset.createdAt || 0)
                    - Number(a.dataset.createdAt || 0)
                );
            }

            if (activeRanking === 'purchased') {
                const purchases =
                    Number(b.dataset.purchaseCount || 0)
                    - Number(a.dataset.purchaseCount || 0);

                if (purchases !== 0) {
                    return purchases;
                }

                return (
                    Number(b.dataset.createdAt || 0)
                    - Number(a.dataset.createdAt || 0)
                );
            }

            return (
                Number(a.dataset.originalOrder || 0)
                - Number(b.dataset.originalOrder || 0)
            );
        });

        sorted.forEach(card => grid.appendChild(card));

        empty?.classList.toggle('hidden', visible > 0);
    }

    search?.addEventListener('input', function () {
        searchQuery = this.value.trim().toLowerCase();
        applyMarketplaceState();
    });

    categoryButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            activeCategory = String(
                button.dataset.addonCategoryTab || 'all'
            );

            setActive(categoryButtons, button);
            applyMarketplaceState();
        });
    });

    rankingButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            activeRanking = String(
                button.dataset.addonRankingTab || 'all'
            );

            setActive(rankingButtons, button);
            applyMarketplaceState();
        });
    });

    applyMarketplaceState();
});
</script>
