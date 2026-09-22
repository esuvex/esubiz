@extends('admin.layouts.app')

@section('title', 'Theme Marketplace')

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


{{-- ESUBIZ_THEME_COMPACT_CARD_V1 --}}
<style>
    #themeMarketplaceGrid {
        align-items: start !important;
    }

    #themeMarketplaceGrid .theme-marketplace-card {
        height: auto !important;
        min-height: 0 !important;
        align-self: start !important;
    }

    #themeMarketplaceGrid .theme-marketplace-card > div:last-child {
        height: auto !important;
        min-height: 0 !important;
        flex: 0 0 auto !important;
    }

    @media (max-width: 639px) {
        #themeMarketplaceGrid .theme-marketplace-card > div:first-child {
            height: 100px !important;
            min-height: 100px !important;
            max-height: 100px !important;
        }

        #themeMarketplaceGrid .theme-marketplace-card > div:first-child img {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
        }
    }
</style>
<div
    class="mx-auto w-full max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8"
    data-esubiz-theme-marketplace
>
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <div class="text-xs font-black uppercase tracking-[.16em] text-blue-600">
                Esubiz Marketplace
            </div>

            <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-950">
                Theme Marketplace
            </h1>

            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                Browse SaaS Themes available for Esubiz websites.
            </p>
        </div>

        @if(session('account_mode') === 'developer')
            <a
                href="{{ route('developer.marketplace.themes') }}"
                class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-black text-slate-700 shadow-sm transition hover:bg-slate-50"
            >
                ← Back to Themes
            </a>
        @endif
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="grid gap-3 lg:grid-cols-[minmax(260px,1fr)_auto]">
            <input
                type="search"
                id="themeMarketplaceSearch"
                placeholder="Search Themes..."
                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white"
            >

            <div class="flex flex-wrap gap-2">
                <button type="button"
                    class="theme-marketplace-filter rounded-xl bg-slate-950 px-3 py-2 text-xs font-black text-white"
                    data-filter="all">
                    All Themes
                </button>

                <button type="button"
                    class="theme-marketplace-filter rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-600 hover:bg-slate-50"
                    data-filter="featured">
                    Featured
                </button>

                <button type="button"
                    class="theme-marketplace-filter rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-600 hover:bg-slate-50"
                    data-filter="newest">
                    Newest
                </button>

                <button type="button"
                    class="theme-marketplace-filter rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-600 hover:bg-slate-50"
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

            <button
                type="button"
                class="theme-marketplace-category rounded-full border border-blue-600 bg-blue-600 px-3 py-1.5 text-xs font-bold text-white"
                data-category="">
                All
            </button>

            @foreach($categories as $category)
                <button
                    type="button"
                    class="theme-marketplace-category rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-600 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700"
                    data-category="{{ strtolower((string) $category) }}">
                    {{ $category }}
                </button>
            @endforeach
        </div>
    @endif

    <div id="themeMarketplaceGrid" class="esubiz-marketplace-grid grid items-start gap-5 sm:grid-cols-2 xl:grid-cols-3">
        @forelse($themes as $theme)
            @php
                $featured = (bool) data_get($theme, 'marketplace.featured', false);
                $category = data_get($theme, 'marketplace.category');
                $publisher = $theme->publisher_name ?: 'Esubiz Marketplace';
                $price = $theme->saas_price;
                $currency = strtoupper((string) ($theme->saas_currency ?: ''));

                $billingPeriod = max(
                    1,
                    (int) ($theme->saas_billing_period ?: 1)
                );

                $billingUnit = strtolower(
                    trim(
                        (string) (
                            $theme->saas_billing_interval
                            ?: 'month'
                        )
                    )
                );

                $billingUnit = match ($billingUnit) {
                    'daily', 'day', 'days' => 'day',
                    'weekly', 'week', 'weeks' => 'week',
                    'monthly', 'month', 'months' => 'month',
                    'yearly', 'annual', 'annually', 'year', 'years' => 'year',
                    default => $billingUnit,
                };

                $billingLabel = $billingPeriod === 1
                    ? $billingUnit
                    : $billingPeriod . ' ' . $billingUnit . 's';

                $previewUrl = !empty($theme->preview_path)
                    ? route('marketplace.themes.preview', ['themePackageId' => $theme->id])
                    : null;

                $purchaseCount = (int) data_get($theme, 'marketplace.purchase_count', 0);
            @endphp

            <article
                class="theme-marketplace-card esubiz-marketplace-card self-start overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-md"
                data-name="{{ strtolower(($theme->name ?? '') . ' ' . ($theme->slug ?? '') . ' ' . $publisher) }}"
                data-category="{{ strtolower((string) $category) }}"
                data-featured="{{ $featured ? '1' : '0' }}"
                data-newest="1"
                data-most-purchased="{{ $purchaseCount }}"
            >
                <div class="esubiz-marketplace-preview relative overflow-hidden border-b border-slate-200 bg-slate-100">
                    @if($previewUrl)
                        <button
                            type="button"
                            class="theme-marketplace-preview-trigger group block h-full w-full text-left"
                            data-preview-url="{{ $previewUrl }}"
                            data-preview-name="{{ $theme->name }}"
                        >
                            <img
                                src="{{ $previewUrl }}"
                                alt="{{ $theme->name }} Theme preview"
                                class="h-full w-full object-cover object-top transition duration-300 group-hover:scale-[1.02]"
                                loading="lazy"
                            >

                            <div class="absolute inset-0 flex items-center justify-center bg-slate-950/0 opacity-0 transition group-hover:bg-slate-950/35 group-hover:opacity-100">
                                <span class="rounded-xl bg-white px-4 py-2 text-xs font-black text-slate-900 shadow-lg">
                                    View Preview
                                </span>
                            </div>
                        </button>
                    @else
                        <div class="flex h-full items-center justify-center bg-gradient-to-br from-slate-950 via-slate-900 to-blue-950 text-white">
                            <div class="text-center">
                                <div class="mx-auto inline-flex h-11 w-11 items-center justify-center rounded-xl bg-white/10 text-xl">
                                    ◈
                                </div>
                                <div class="mt-3 text-sm font-black">
                                    {{ $theme->name }}
                                </div>
                                <div class="mt-1 text-[10px] font-bold text-slate-300">
                                    Preview unavailable
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($featured)
                        <span class="absolute left-4 top-4 rounded-full bg-amber-400 px-2.5 py-1 text-[9px] font-black uppercase tracking-wide text-slate-950 shadow-sm">
                            Featured
                        </span>
                    @endif
                </div>

                <div class="esubiz-marketplace-card-body p-4">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-black text-slate-950">
                                {{ $theme->name }}
                            </h3>
                            <p class="mt-1 text-xs font-bold text-slate-500">
                                By {{ $publisher }}
                            </p>
                        </div>

                        @if($category)
                            <span class="rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-[10px] font-bold text-slate-600">
                                {{ $category }}
                            </span>
                        @endif
                    </div>

                    <div class="mt-3 flex flex-wrap gap-2">
                        <span class="rounded-full border border-slate-200 bg-white px-2.5 py-1 text-[10px] font-bold text-slate-500">
                            SaaS
                        </span>
                        <span class="rounded-full border border-slate-200 bg-white px-2.5 py-1 text-[10px] font-bold text-slate-500">
                            v{{ $theme->version }}
                        </span>
                    </div>

                    <div class="mt-3 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5">
                        <div class="text-[9px] font-black uppercase tracking-[.14em] text-slate-400">
                            Price
                        </div>

                        <div class="mt-1 text-base font-black text-slate-950">
                            @if($price === null || (float) $price <= 0)
                                Free
                            @else
                                {{ $currency }} {{ number_format((float) $price, 2) }}@if($billingLabel !== '') / {{ $billingLabel }}@endif
                            @endif
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 pt-3">
                        @if($previewUrl)
                            <button
                                type="button"
                                class="theme-marketplace-preview-trigger rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-700 transition hover:bg-slate-50"
                                data-preview-url="{{ $previewUrl }}"
                                data-preview-name="{{ $theme->name }}">
                                Preview
                            </button>
                        @else
                            <button type="button" disabled
                                class="cursor-not-allowed rounded-xl border border-slate-200 bg-slate-100 px-3 py-2 text-xs font-black text-slate-400">
                                Preview
                            </button>
                        @endif

                        <button
                            type="button"
                            class="rounded-xl bg-blue-600 px-3 py-2 text-xs font-black text-white transition hover:bg-blue-700">
                            Get Theme
                        </button>
                    </div>
                </div>
            </article>
        @empty
            <div class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-16 text-center">
                <div class="text-lg font-black text-slate-900">
                    No SaaS Themes available
                </div>
                <p class="mt-2 text-sm text-slate-500">
                    Published SaaS Themes will automatically appear here.
                </p>
            </div>
        @endforelse
    </div>

    <div
        id="themeMarketplaceEmptyFilter"
        class="hidden rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-14 text-center">
        <div class="text-lg font-black text-slate-900">
            No Themes match this selection
        </div>
        <p class="mt-2 text-sm text-slate-500">
            Try another search, category or ranking.
        </p>
    </div>
</div>


<style>
/* ESUBIZ_THEME_PREVIEW_VIEWPORT_CSS_V1 */

#themeMarketplacePreviewModal {
    position: fixed !important;
    top: 0 !important;
    right: 0 !important;
    bottom: 0 !important;
    left: 0 !important;
    width: 100vw !important;
    height: 100vh !important;
    height: 100dvh !important;
    z-index: 2147483000 !important;
    margin: 0 !important;
    padding: 12px !important;
    overflow: hidden !important;
    box-sizing: border-box !important;
}

#themeMarketplacePreviewModal.hidden {
    display: none !important;
}

#themeMarketplacePreviewModal:not(.hidden) {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}

#themeMarketplacePreviewModal > div {
    width: 100% !important;
    height: 100% !important;
    min-width: 0 !important;
    min-height: 0 !important;
    padding: 0 !important;
    margin: 0 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}

#themeMarketplacePreviewPanel {
    position: relative !important;
    width: min(100%, 1152px) !important;
    max-width: 1152px !important;
    height: 100% !important;
    max-height: 100% !important;
    min-width: 0 !important;
    min-height: 0 !important;
    margin: 0 auto !important;
    display: flex !important;
    flex-direction: column !important;
    overflow: hidden !important;
    background: #fff !important;
}

#themeMarketplacePreviewPanel > div:first-child {
    position: relative !important;
    flex: 0 0 auto !important;
    z-index: 2 !important;
    width: 100% !important;
}

#themeMarketplacePreviewScroller {
    position: relative !important;
    flex: 1 1 auto !important;
    min-width: 0 !important;
    min-height: 0 !important;
    width: 100% !important;
    overflow-x: hidden !important;
    overflow-y: auto !important;
    -webkit-overflow-scrolling: touch !important;
    overscroll-behavior: contain !important;
}

#themeMarketplacePreviewImage {
    display: block !important;
    width: 100% !important;
    height: auto !important;
    max-width: 100% !important;
}

@media (min-width: 640px) {
    #themeMarketplacePreviewModal {
        padding: 16px !important;
    }
}

@media (min-width: 1024px) {
    #themeMarketplacePreviewModal {
        /*
         * Central Admin has a fixed 256px desktop sidebar.
         * Keep the Marketplace preview entirely inside the usable
         * content viewport instead of underneath the sidebar.
         */
        left: 256px !important;
        width: calc(100vw - 256px) !important;
        padding: 24px !important;
    }
}

@media (max-width: 639px) {
    #themeMarketplacePreviewModal {
        padding: 8px !important;
    }

    #themeMarketplacePreviewPanel {
        border-radius: 16px !important;
    }
}
</style>

<div
    id="themeMarketplacePreviewModal"
    class="fixed inset-0 z-[9999] hidden bg-slate-950/75 backdrop-blur-sm"
    aria-hidden="true"
>
    <div class="flex h-[100dvh] w-full items-center justify-center p-2 sm:p-4 lg:p-6">
        <div
            id="themeMarketplacePreviewPanel"
            class="flex h-[calc(100dvh-1rem)] w-full max-w-6xl flex-col overflow-hidden rounded-2xl bg-white shadow-2xl sm:h-[calc(100dvh-2rem)] lg:h-[calc(100dvh-3rem)] lg:rounded-3xl"
        >
            <div class="flex shrink-0 items-center justify-between gap-4 border-b border-slate-200 bg-white px-4 py-3 sm:px-6 sm:py-4">
                <div class="min-w-0">
                    <div class="text-[10px] font-black uppercase tracking-[.16em] text-blue-600">
                        Theme Preview
                    </div>

                    <h2
                        id="themeMarketplacePreviewTitle"
                        class="mt-1 truncate text-base font-black text-slate-950 sm:text-xl"
                    >
                        Theme Preview
                    </h2>
                </div>

                <button
                    type="button"
                    id="themeMarketplacePreviewClose"
                    class="grid h-10 w-10 shrink-0 place-items-center rounded-xl bg-slate-100 text-xl font-black text-slate-700 transition hover:bg-slate-200"
                    aria-label="Close Theme preview"
                >
                    ×
                </button>
            </div>

            <div
                id="themeMarketplacePreviewScroller"
                class="min-h-0 flex-1 overflow-x-hidden overflow-y-auto overscroll-contain bg-slate-100 p-2 sm:p-4"
            >
                <div class="mx-auto w-full overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm sm:rounded-2xl">
                    <img
                        id="themeMarketplacePreviewImage"
                        src=""
                        alt=""
                        class="block h-auto w-full object-contain object-top"
                    >
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const search = document.getElementById('themeMarketplaceSearch');
    const cards = Array.from(document.querySelectorAll('.theme-marketplace-card'));
    const filters = Array.from(document.querySelectorAll('.theme-marketplace-filter'));
    const categories = Array.from(document.querySelectorAll('.theme-marketplace-category'));
    const empty = document.getElementById('themeMarketplaceEmptyFilter');

    let activeFilter = 'all';
    let activeCategory = '';

    function applyFilters() {
        const query = (search?.value || '').trim().toLowerCase();
        let visible = 0;

        cards.forEach(function (card) {
            const matchesSearch = !query || (card.dataset.name || '').includes(query);
            const matchesCategory = !activeCategory || (card.dataset.category || '') === activeCategory;

            let matchesRanking = true;

            if (activeFilter === 'featured') {
                matchesRanking = card.dataset.featured === '1';
            } else if (activeFilter === 'most-purchased') {
                matchesRanking = Number(card.dataset.mostPurchased || 0) > 0;
            }

            const show = matchesSearch && matchesCategory && matchesRanking;
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
                const active = item === button;
                item.className = active
                    ? 'theme-marketplace-filter rounded-xl bg-slate-950 px-3 py-2 text-xs font-black text-white'
                    : 'theme-marketplace-filter rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-600 hover:bg-slate-50';
            });

            applyFilters();
        });
    });

    categories.forEach(function (button) {
        button.addEventListener('click', function () {
            activeCategory = button.dataset.category || '';

            categories.forEach(function (item) {
                const active = item === button;
                item.className = active
                    ? 'theme-marketplace-category rounded-full border border-blue-600 bg-blue-600 px-3 py-1.5 text-xs font-bold text-white'
                    : 'theme-marketplace-category rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-600 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700';
            });

            applyFilters();
        });
    });

    const modal = document.getElementById('themeMarketplacePreviewModal');

    /*
     * ESUBIZ_THEME_PREVIEW_BODY_PORTAL_V1
     *
     * Keep the Marketplace preview outside dashboard/layout containing
     * blocks so position:fixed is always relative to the browser viewport.
     */
    if (modal && modal.parentElement !== document.body) {
        document.body.appendChild(modal);
    }

    const image = document.getElementById('themeMarketplacePreviewImage');
    const title = document.getElementById('themeMarketplacePreviewTitle');
    const close = document.getElementById('themeMarketplacePreviewClose');

    function closePreview() {
        modal?.classList.add('hidden');
        modal?.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');

        if (image) image.src = '';
    }

    document.querySelectorAll('.theme-marketplace-preview-trigger').forEach(function (button) {
        button.addEventListener('click', function () {
            if (!modal || !image) return;

            image.src = button.dataset.previewUrl || '';
            image.alt = (button.dataset.previewName || 'Theme') + ' preview';

            if (title) {
                title.textContent = button.dataset.previewName || 'Theme Preview';
            }

            const previewScroller = document.getElementById('themeMarketplacePreviewScroller');

            if (previewScroller) {
                previewScroller.scrollTop = 0;
            }

            modal.classList.remove('hidden');
            modal.setAttribute('aria-hidden', 'false');
            document.body.classList.add('overflow-hidden');
        });
    });

    close?.addEventListener('click', closePreview);

    modal?.addEventListener('click', function (event) {
        if (event.target === modal) closePreview();
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') closePreview();
    });

    applyFilters();
});
</script>
@endsection
