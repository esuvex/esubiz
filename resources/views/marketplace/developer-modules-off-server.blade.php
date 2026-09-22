@extends('admin.layouts.app')

@section('title', 'Off-server Module Marketplace')

@section('content')


{{-- ESUBIZ_MARKETPLACE_CONTENT_HEIGHT_CARD_V2 --}}
<style>
    .esubiz-marketplace-grid {
        align-items: start !important;
    }

    .esubiz-marketplace-card {
        height: auto !important;
        min-height: 0 !important;
        align-self: start !important;
        display: block !important;
    }

    .esubiz-marketplace-card-body {
        height: auto !important;
        min-height: 0 !important;
        flex: none !important;
    }

    .esubiz-marketplace-preview {
        height: 176px !important;
        min-height: 176px !important;
        max-height: 176px !important;
    }

    .esubiz-marketplace-preview img {
        width: 100% !important;
        height: 100% !important;
        object-fit: cover !important;
        object-position: top !important;
    }

    @media (max-width: 639px) {
        .esubiz-marketplace-preview {
            height: 100px !important;
            min-height: 100px !important;
            max-height: 100px !important;
        }
    }
</style>

<div class="mx-auto w-full max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">

    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <div class="text-xs font-black uppercase tracking-[.16em] text-blue-600">
                Esubiz Marketplace
            </div>

            <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950">
                Off-server Module Marketplace
            </h1>

            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                Browse Modules available for off-server Esubiz Core installations.
            </p>
        </div>

        <a
            href="{{ route('developer.marketplace.modules') }}"
            class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-black text-slate-700 shadow-sm transition hover:bg-slate-50"
        >
            ← Back to Modules
        </a>

    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="grid gap-3 lg:grid-cols-[minmax(260px,1fr)_auto]">
            <input
                type="search"
                id="moduleMarketplaceSearch"
                placeholder="Search Modules..."
                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white"
            >

            <div class="flex flex-wrap gap-2">
                <button type="button"
                    class="module-marketplace-filter rounded-xl bg-slate-950 px-4 py-2.5 text-xs font-black text-white"
                    data-filter="all">
                    All Modules
                </button>

                <button type="button"
                    class="module-marketplace-filter rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-black text-slate-600"
                    data-filter="featured">
                    Featured
                </button>

                <button type="button"
                    class="module-marketplace-filter rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-black text-slate-600"
                    data-filter="newest">
                    Newest
                </button>

                <button type="button"
                    class="module-marketplace-filter rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-black text-slate-600"
                    data-filter="most-purchased">
                    Most Purchased
                </button>
            </div>
        </div>
    </div>

    @if($categories->isNotEmpty())
        <div class="flex flex-wrap items-center gap-2">
            <span class="mr-1 text-[10px] font-black uppercase tracking-[.14em] text-slate-400">
                Categories
            </span>

            <button type="button"
                class="module-marketplace-category rounded-full border border-blue-600 bg-blue-600 px-3 py-1.5 text-xs font-bold text-white"
                data-category="">
                All
            </button>

            @foreach($categories as $category)
                <button type="button"
                    class="module-marketplace-category rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-600"
                    data-category="{{ strtolower((string) $category) }}">
                    {{ $category }}
                </button>
            @endforeach
        </div>
    @endif

    <div class="esubiz-marketplace-grid grid items-start gap-5 sm:grid-cols-2 xl:grid-cols-3">
        @forelse($modules as $module)
            @php
                $featured = (bool) ($module->marketplace_featured ?? false);
                $category = $module->marketplace_category ?? null;
                $price = $resolver->price($module, 'off_server');
                $currency = strtoupper((string) $resolver->currency($module, 'off_server'));

                $metadata = is_array($module->metadata ?? null)
                    ? $module->metadata
                    : [];

                $previewUrl = data_get($metadata, 'preview_url')
                    ?: data_get($metadata, 'preview');

                $purchaseCount = (int) data_get(
                    $metadata,
                    'marketplace.purchase_count',
                    0
                );
            @endphp

            <article
                class="module-marketplace-card esubiz-marketplace-card self-start overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md"
                data-name="{{ strtolower(($module->name ?? '') . ' ' . ($module->slug ?? '') . ' ' . ($module->description ?? '')) }}"
                data-category="{{ strtolower((string) $category) }}"
                data-featured="{{ $featured ? '1' : '0' }}"
                data-most-purchased="{{ $purchaseCount }}"
            >
                <div class="esubiz-marketplace-preview relative overflow-hidden border-b border-slate-200 bg-slate-100">
                    @if($previewUrl)
                        <img
                            src="{{ $previewUrl }}"
                            alt="{{ $module->name }} preview"
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
                                    {{ $module->name }}
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($featured)
                        <span class="absolute left-4 top-4 rounded-full bg-amber-400 px-2.5 py-1 text-[9px] font-black uppercase tracking-wide text-slate-950">
                            Featured
                        </span>
                    @endif
                </div>

                <div class="esubiz-marketplace-card-body p-4">
                    <div class="flex items-start justify-between gap-4">
                        <h3 class="text-lg font-black text-slate-950">
                            {{ $module->name }}
                        </h3>

                        @if($category)
                            <span class="rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-[10px] font-bold text-slate-600">
                                {{ $category }}
                            </span>
                        @endif
                    </div>

                    @if(!empty($module->description))
                        <p class="mt-2 line-clamp-2 text-sm leading-5 text-slate-500">
                            {{ $module->description }}
                        </p>
                    @endif

                    <div class="mt-3 flex flex-wrap gap-2">
                        <span class="rounded-full border border-slate-200 bg-white px-2.5 py-1 text-[10px] font-bold text-slate-500">
                            Off-server
                        </span>

                        <span class="rounded-full border border-slate-200 bg-white px-2.5 py-1 text-[10px] font-bold text-slate-500">
                            v{{ $module->version }}
                        </span>
                    </div>

                    <div class="mt-3 rounded-xl border border-slate-200 bg-slate-50 p-3">
                        <div class="text-[9px] font-black uppercase tracking-[.14em] text-slate-400">
                            Price
                        </div>

                        <div class="mt-1 text-lg font-black text-slate-950">
                            @if($price === null || (float) $price <= 0)
                                Free
                            @else
                                {{ $currency }} {{ number_format((float) $price, 2) }}
                            @endif
                        </div>
                    </div>

                    <div class="pt-3">
                        <button
                            type="button"
                            class="w-full rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-black text-white transition hover:bg-blue-700">
                            Get Module
                        </button>
                    </div>
                </div>
            </article>
        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-16 text-center">
                <div class="text-lg font-black text-slate-900">
                    No Off-server Modules available
                </div>

                <p class="mt-2 text-sm text-slate-500">
                    Published off-server Modules will automatically appear here.
                </p>
            </div>
        @endforelse
    </div>

    <div id="moduleMarketplaceEmpty"
        class="hidden rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-14 text-center">
        <div class="text-lg font-black text-slate-900">
            No Modules match this selection
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const search = document.getElementById('moduleMarketplaceSearch');
    const cards = Array.from(document.querySelectorAll('.module-marketplace-card'));
    const filters = Array.from(document.querySelectorAll('.module-marketplace-filter'));
    const categories = Array.from(document.querySelectorAll('.module-marketplace-category'));
    const empty = document.getElementById('moduleMarketplaceEmpty');

    let activeFilter = 'all';
    let activeCategory = '';

    function applyFilters() {
        const query = (search?.value || '').trim().toLowerCase();
        let visible = 0;

        cards.forEach(function (card) {
            const searchMatch = !query || (card.dataset.name || '').includes(query);
            const categoryMatch = !activeCategory || card.dataset.category === activeCategory;

            let rankingMatch = true;

            if (activeFilter === 'featured') {
                rankingMatch = card.dataset.featured === '1';
            } else if (activeFilter === 'most-purchased') {
                rankingMatch = Number(card.dataset.mostPurchased || 0) > 0;
            }

            const show = searchMatch && categoryMatch && rankingMatch;
            card.classList.toggle('hidden', !show);

            if (show) visible++;
        });

        empty?.classList.toggle('hidden', visible !== 0 || cards.length === 0);
    }

    search?.addEventListener('input', applyFilters);

    filters.forEach(function (button) {
        button.addEventListener('click', function () {
            activeFilter = button.dataset.filter || 'all';

            filters.forEach(function (item) {
                item.className = item === button
                    ? 'module-marketplace-filter rounded-xl bg-slate-950 px-4 py-2.5 text-xs font-black text-white'
                    : 'module-marketplace-filter rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-xs font-black text-slate-600';
            });

            applyFilters();
        });
    });

    categories.forEach(function (button) {
        button.addEventListener('click', function () {
            activeCategory = button.dataset.category || '';

            categories.forEach(function (item) {
                item.className = item === button
                    ? 'module-marketplace-category rounded-full border border-blue-600 bg-blue-600 px-3 py-1.5 text-xs font-bold text-white'
                    : 'module-marketplace-category rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-600';
            });

            applyFilters();
        });
    });

    applyFilters();
});
</script>
@endsection
