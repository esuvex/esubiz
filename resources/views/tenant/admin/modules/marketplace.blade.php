@extends('tenant.admin.layouts.app')

@section('title', 'Module Marketplace')

@section('content')
<div class="mx-auto max-w-7xl space-y-6">

    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <div class="text-xs font-black uppercase tracking-[.16em] text-blue-600">
                Esubiz Marketplace
            </div>

            <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900">
                Module Marketplace
            </h1>

            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                Browse Modules compatible with this website, discover featured products
                and manage Module purchases or installations.
            </p>
        </div>

        <a
            href="{{ route('tenant.cms.modules.index', [
                'subdomain' => $website->subdomain
            ]) }}"
            class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-black text-slate-700 shadow-sm transition hover:bg-slate-50"
        >
            ← Back to Module Hub
        </a>
    </div>


    {{-- Search + primary rankings --}}
    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="grid gap-3 lg:grid-cols-[minmax(260px,1fr)_auto]">

            <input
                type="search"
                id="moduleMarketplaceSearch"
                placeholder="Search Modules..."
                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white"
            >

            <div class="flex flex-wrap gap-2">

                <button
                    type="button"
                    class="module-marketplace-filter rounded-xl bg-slate-950 px-4 py-2.5 text-xs font-black text-white"
                    data-filter="all"
                >
                    All Modules
                </button>

                <button
                    type="button"
                    class="module-marketplace-filter rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-black text-slate-600 hover:bg-slate-50"
                    data-filter="featured"
                >
                    Featured
                </button>

                <button
                    type="button"
                    class="module-marketplace-filter rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-black text-slate-600 hover:bg-slate-50"
                    data-filter="newest"
                >
                    Newest
                </button>

                <button
                    type="button"
                    class="module-marketplace-filter rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-black text-slate-600 hover:bg-slate-50"
                    data-filter="most-purchased"
                >
                    Most Purchased
                </button>

            </div>
        </div>
    </div>


    {{-- Dynamic Marketplace categories --}}
    @if($categories->isNotEmpty())
        <div class="flex flex-wrap items-center gap-2">

            <span class="mr-1 text-[10px] font-black uppercase tracking-[.14em] text-slate-400">
                Categories
            </span>

            @foreach($categories as $category)
                <button
                    type="button"
                    class="module-marketplace-category rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-600 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700"
                    data-category="{{ strtolower((string) $category) }}"
                >
                    {{ $category }}
                </button>
            @endforeach

        </div>
    @endif


    {{-- Module catalog --}}
    <div
        id="moduleMarketplaceGrid"
        class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3"
    >

        @forelse($modules as $module)

            @php
                $installed = (bool) ($module['installed'] ?? false);

                $canPurchase = (bool) ($module['can_purchase'] ?? false);

                $featured = (bool) data_get(
                    $module,
                    'marketplace.featured',
                    false
                );

                $category = data_get(
                    $module,
                    'marketplace.category'
                );

                $price = $module['price'] ?? null;

                $currency = strtoupper(
                    (string) ($module['currency'] ?? '')
                );

                $billing = is_array($module['billing'] ?? null)
                    ? $module['billing']
                    : null;

                $publisher = data_get(
                    $module,
                    'publisher.name'
                ) ?: 'Esubiz Marketplace';

                $previewUrl = $module['preview_url'] ?? null;

                $isNewest = $newestModules
                    ->contains(
                        fn ($item) =>
                            ($item['slug'] ?? null) ===
                            ($module['slug'] ?? null)
                    );

                $isMostPurchased = $mostPurchasedModules
                    ->contains(
                        fn ($item) =>
                            ($item['slug'] ?? null) ===
                            ($module['slug'] ?? null)
                    );
            @endphp

            <article
                class="module-marketplace-card flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-md"
                data-name="{{ strtolower(
                    ($module['name'] ?? '')
                    . ' '
                    . ($module['slug'] ?? '')
                    . ' '
                    . $publisher
                ) }}"
                data-category="{{ strtolower((string) $category) }}"
                data-featured="{{ $featured ? '1' : '0' }}"
                data-newest="{{ $isNewest ? '1' : '0' }}"
                data-most-purchased="{{ $isMostPurchased ? '1' : '0' }}"
            >

                <div class="relative h-44 overflow-hidden border-b border-slate-200 bg-slate-100">

                    @if($previewUrl)
                        <img
                            src="{{ $previewUrl }}"
                            alt="{{ $module['name'] }} Module preview"
                            class="h-full w-full object-cover object-top"
                            loading="lazy"
                        >
                    @else
                        <div class="flex h-full items-center justify-center bg-gradient-to-br from-slate-950 via-slate-900 to-blue-950 text-white">
                            <div class="text-center">
                                <div class="mx-auto inline-flex h-11 w-11 items-center justify-center rounded-xl bg-white/10 text-xl">
                                    ◫
                                </div>

                                <div class="mt-3 text-sm font-black">
                                    {{ $module['name'] ?? 'Module' }}
                                </div>

                                <div class="mt-1 text-[10px] font-bold text-slate-300">
                                    Preview unavailable
                                </div>
                            </div>
                        </div>
                    @endif

                </div>


                <div class="flex flex-1 flex-col p-5">

                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="text-xs font-black uppercase tracking-wide text-blue-600">
                                {{ $category ?: 'Module' }}
                            </div>

                            <h2 class="mt-1.5 text-lg font-black text-slate-900">
                                {{ $module['name'] ?? 'Module' }}
                            </h2>

                            <p class="mt-1 text-xs font-bold text-slate-400">
                                {{ $publisher }}
                            </p>
                        </div>

                        @if($featured)
                            <span class="rounded-full bg-blue-50 px-2.5 py-1 text-[10px] font-black uppercase text-blue-700">
                                Featured
                            </span>
                        @endif
                    </div>


                    <p class="mt-4 text-sm leading-6 text-slate-500">
                        {{ $module['description'] ?? 'Extend your website with additional Esubiz functionality.' }}
                    </p>


                    <div class="mt-auto pt-6">

                        <div class="mb-4 flex items-center justify-between gap-4">

                            <div>
                                @if($price !== null)
                                    <div class="text-lg font-black text-slate-950">
                                        @if((float) $price <= 0)
                                            Free
                                        @else
                                            {{ $currency }} {{ number_format((float) $price, 2) }}@if(!empty($billing['label'])) / {{ $billing['label'] }}@endif
                                        @endif
                                    </div>
                                @endif
                            </div>

                            @if($installed)
                                <span class="rounded-full bg-emerald-50 px-3 py-1.5 text-[10px] font-black uppercase text-emerald-700">
                                    Installed
                                </span>
                            @endif

                        </div>


                        @if($installed)
                            <a
                                href="{{ route('tenant.cms.modules.index', [
                                    'subdomain' => $website->subdomain
                                ]) }}"
                                class="inline-flex w-full items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-black text-slate-700 transition hover:bg-slate-50"
                            >
                                Manage Module
                            </a>

                        @elseif($canPurchase)
                            <button
                                type="button"
                                data-core-catalog-purchase
                                data-product-type="module"
                                data-product-id="{{ $module['id'] }}"
                                class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-black text-white transition hover:bg-blue-700 disabled:opacity-50"
                            >
                                Get Module
                            </button>

                        @else
                            <button
                                type="button"
                                disabled
                                class="inline-flex w-full cursor-not-allowed items-center justify-center rounded-xl bg-slate-100 px-4 py-2.5 text-xs font-black text-slate-400"
                            >
                                Unavailable
                            </button>
                        @endif

                    </div>
                </div>

            </article>

        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center">
                <div class="text-base font-black text-slate-900">
                    No Modules Available
                </div>

                <p class="mt-2 text-sm text-slate-500">
                    No compatible Modules are currently available for this website.
                </p>
            </div>
        @endforelse

    </div>


    <div
        id="moduleMarketplaceEmpty"
        class="hidden rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-14 text-center"
    >
        <div class="text-base font-black text-slate-900">
            No Matching Modules
        </div>

        <p class="mt-2 text-sm text-slate-500">
            Try another search term or Marketplace filter.
        </p>
    </div>

</div>


<script>
document.addEventListener('DOMContentLoaded', function () {
    const search = document.getElementById('moduleMarketplaceSearch');
    const cards = Array.from(
        document.querySelectorAll('.module-marketplace-card')
    );
    const filters = Array.from(
        document.querySelectorAll('.module-marketplace-filter')
    );
    const categories = Array.from(
        document.querySelectorAll('.module-marketplace-category')
    );
    const empty = document.getElementById('moduleMarketplaceEmpty');

    let activeFilter = 'all';
    let activeCategory = '';

    function render() {
        const query = (search?.value || '').trim().toLowerCase();
        let visible = 0;

        cards.forEach(function (card) {
            const matchesSearch =
                !query ||
                (card.dataset.name || '').includes(query);

            const matchesCategory =
                !activeCategory ||
                (card.dataset.category || '') === activeCategory;

            let matchesFilter = true;

            if (activeFilter !== 'all') {
                matchesFilter =
                    card.dataset[activeFilter.replace('-', '')] === '1';
            }

            const show =
                matchesSearch &&
                matchesCategory &&
                matchesFilter;

            card.classList.toggle('hidden', !show);

            if (show) {
                visible++;
            }
        });

        empty?.classList.toggle('hidden', visible !== 0);
    }

    search?.addEventListener('input', render);

    filters.forEach(function (button) {
        button.addEventListener('click', function () {
            activeFilter = button.dataset.filter || 'all';

            filters.forEach(function (item) {
                const active = item === button;

                item.classList.toggle('bg-slate-950', active);
                item.classList.toggle('text-white', active);
                item.classList.toggle('bg-white', !active);
                item.classList.toggle('text-slate-600', !active);
                item.classList.toggle('border', !active);
                item.classList.toggle('border-slate-200', !active);
            });

            render();
        });
    });

    categories.forEach(function (button) {
        button.addEventListener('click', function () {
            const selected = button.dataset.category || '';

            activeCategory =
                activeCategory === selected
                    ? ''
                    : selected;

            categories.forEach(function (item) {
                const active =
                    activeCategory !== '' &&
                    item.dataset.category === activeCategory;

                item.classList.toggle('bg-blue-600', active);
                item.classList.toggle('text-white', active);
                item.classList.toggle('border-blue-600', active);
                item.classList.toggle('bg-white', !active);
                item.classList.toggle('text-slate-600', !active);
            });

            render();
        });
    });

    render();
});
</script>

<script>
(() => {
    // CORE_CATALOG_PRODUCT_CHECKOUT_V1
    @php
        $coreCatalogCheckoutUrl = route('tenant.admin.addons.checkout', [
            'subdomain' => request()->route('subdomain'),
            'website' => (int) $website->id,
        ]);
    @endphp
    const checkoutUrl = @json($coreCatalogCheckoutUrl);

    document.querySelectorAll('[data-core-catalog-purchase]').forEach(button => {
        button.addEventListener('click', async () => {
            if (button.disabled) return;
            const originalText = button.textContent;
            button.disabled = true;
            button.textContent = 'Opening checkout…';

            try {
                if (!window.EsubizCoreCheckout) {
                    throw new Error('Please refresh this page to open checkout.');
                }

                const response = await fetch(checkoutUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector(
                            'meta[name="csrf-token"]'
                        )?.content || '',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        product_type: button.dataset.productType,
                        product_id: Number(button.dataset.productId),
                        return_url: window.location.href,
                        return_area: button.dataset.productType + 's'
                    })
                });

                const result = await response.json();
                if (!response.ok || !result.url) {
                    const errors = result.errors
                        ? Object.values(result.errors).flat().join(' ')
                        : '';
                    throw new Error(errors || result.message ||
                        'Checkout could not be opened. Please try again.');
                }

                window.EsubizCoreCheckout.open(result.url);
            } catch (error) {
                alert(error.message || 'Checkout could not be opened.');
            } finally {
                button.disabled = false;
                button.textContent = originalText;
            }
        });
    });
})();
</script>

@endsection
