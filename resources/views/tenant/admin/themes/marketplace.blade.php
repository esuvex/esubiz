{{-- ESUBIZ_CORE_THEME_MARKETPLACE_LAYOUT_V2 --}}
@extends('tenant.admin.layouts.app')

@section('title', 'Theme Marketplace')

@section('content')

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

    {{-- =====================================================
         ESUBIZ_CORE_THEME_MARKETPLACE_PAGE_V1
         Core Theme Marketplace
         ===================================================== --}}

    <div
        class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between"
    >

        <div>

            <div
                class="text-xs font-black uppercase tracking-[.16em] text-blue-600"
            >
                Esubiz Marketplace
            </div>

            <h1
                class="mt-2 text-3xl font-black tracking-tight text-slate-950"
            >
                Theme Marketplace
            </h1>

            <p
                class="mt-2 max-w-3xl text-sm leading-6 text-slate-500"
            >
                Browse Themes compatible with this website, discover featured
                designs and manage Theme purchases or installations.
            </p>

        </div>


        <a
            href="{{ route(
                'tenant.cms.themes.index',
                [
                    'subdomain' =>
                        $website->subdomain
                ]
            ) }}"
            class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-black text-slate-700 shadow-sm transition hover:bg-slate-50"
        >
            ← Back to Theme Hub
        </a>

    </div>


    {{-- Search + primary rankings --}}
    <div
        class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"
    >

        <div
            class="grid gap-3 lg:grid-cols-[minmax(260px,1fr)_auto]"
        >

            <input
                type="search"
                id="themeMarketplaceSearch"
                placeholder="Search Themes..."
                class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm font-semibold text-slate-900 outline-none transition focus:border-blue-500 focus:bg-white"
            >


            <div class="flex flex-wrap gap-2">

                <button
                    type="button"
                    class="theme-marketplace-filter rounded-xl bg-slate-950 px-3 py-2 text-xs font-black text-white"
                    data-filter="all"
                >
                    All Themes
                </button>

                <button
                    type="button"
                    class="theme-marketplace-filter rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-600 hover:bg-slate-50"
                    data-filter="featured"
                >
                    Featured
                </button>

                <button
                    type="button"
                    class="theme-marketplace-filter rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-600 hover:bg-slate-50"
                    data-filter="newest"
                >
                    Newest
                </button>

                <button
                    type="button"
                    class="theme-marketplace-filter rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-600 hover:bg-slate-50"
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

            <span
                class="mr-1 text-[10px] font-black uppercase tracking-[.14em] text-slate-400"
            >
                Categories
            </span>

            @foreach($categories as $category)

                <button
                    type="button"
                    class="theme-marketplace-category rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs font-bold text-slate-600 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700"
                    data-category="{{ strtolower(
                        (string) $category
                    ) }}"
                >
                    {{ $category }}
                </button>

            @endforeach

        </div>

    @endif


    {{-- Theme catalog --}}
    <div
        id="themeMarketplaceGrid"
        class="grid items-start gap-5 sm:grid-cols-2 xl:grid-cols-3"
    >

        @forelse($themes as $theme)

            @php
                $installed =
                    (bool) (
                        $theme['installed']
                        ?? false
                    );

                $canPurchase =
                    (bool) (
                        $theme['can_purchase']
                        ?? false
                    );

                $featured =
                    (bool) data_get(
                        $theme,
                        'marketplace.featured',
                        false
                    );

                $category =
                    data_get(
                        $theme,
                        'marketplace.category'
                    );

                $price =
                    $theme['price']
                    ?? null;

                $currency =
                    strtoupper(
                        (string) (
                            $theme['currency']
                            ?? ''
                        )
                    );

                $deployment =
                    $theme['deployment']
                    ?? 'saas';

                $billing =
                    $theme['billing']
                    ?? null;

                $publisher =
                    data_get(
                        $theme,
                        'publisher.name'
                    )
                    ?: 'Esubiz Marketplace';

                $previewUrl =
                    $theme['preview_url']
                    ?? null;
            @endphp


            <article
                class="theme-marketplace-card self-start flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition duration-200 hover:-translate-y-0.5 hover:shadow-md"
                data-name="{{ strtolower(
                    (
                        $theme['name']
                        ?? ''
                    )
                    . ' '
                    . (
                        $theme['slug']
                        ?? ''
                    )
                    . ' '
                    . $publisher
                ) }}"
                data-category="{{ strtolower(
                    (string) $category
                ) }}"
                data-featured="{{ $featured ? '1' : '0' }}"
                data-newest="1"
                data-most-purchased="0"
            >

                {{-- ESUBIZ_CORE_THEME_MARKETPLACE_PREVIEW_UI_V2 --}}
                <div
                    class="relative h-36 overflow-hidden border-b border-slate-200 bg-slate-100"
                >

                    @if($previewUrl)

                        <button
                            type="button"
                            class="theme-marketplace-preview-trigger group block h-full w-full text-left"
                            data-preview-url="{{ $previewUrl }}"
                            data-preview-name="{{ $theme['name'] }}"
                        >
                            <img
                                src="{{ $previewUrl }}"
                                alt="{{ $theme['name'] }} Theme preview"
                                class="h-full w-full object-cover object-top transition duration-300 group-hover:scale-[1.02]"
                                loading="lazy"
                            >

                            <div
                                class="absolute inset-0 flex items-center justify-center bg-slate-950/0 opacity-0 transition group-hover:bg-slate-950/35 group-hover:opacity-100"
                            >
                                <span
                                    class="rounded-xl bg-white px-4 py-2 text-xs font-black text-slate-900 shadow-lg"
                                >
                                    View Preview
                                </span>
                            </div>
                        </button>

                    @else

                        <div
                            class="flex h-full items-center justify-center bg-gradient-to-br from-slate-950 via-slate-900 to-blue-950 text-white"
                        >
                            <div class="text-center">

                                <div
                                    class="mx-auto inline-flex h-11 w-11 items-center justify-center rounded-xl bg-white/10 text-xl"
                                >
                                    ◈
                                </div>

                                <div
                                    class="mt-3 text-sm font-black"
                                >
                                    {{ $theme['name'] }}
                                </div>

                                <div
                                    class="mt-1 text-[10px] font-bold text-slate-300"
                                >
                                    Preview unavailable
                                </div>

                            </div>
                        </div>

                    @endif


                    @if($featured)

                        <span
                            class="absolute left-4 top-4 rounded-full bg-amber-400 px-2.5 py-1 text-[9px] font-black uppercase tracking-wide text-slate-950 shadow-sm"
                        >
                            Featured
                        </span>

                    @endif


                    @if($installed)

                        <span
                            class="absolute right-4 top-4 rounded-full border border-emerald-300/30 bg-emerald-950/80 px-2.5 py-1 text-[9px] font-black uppercase tracking-wide text-emerald-200 shadow-sm backdrop-blur-sm"
                        >
                            Installed
                        </span>

                    @endif

                </div>


                <div class="flex flex-col p-4">

                    <div
                        class="flex items-start justify-between gap-4"
                    >

                        <div>

                            <h3
                                class="text-lg font-black text-slate-950"
                            >
                                {{ $theme['name'] }}
                            </h3>

                            <p
                                class="mt-1 text-xs font-bold text-slate-500"
                            >
                                By {{ $publisher }}
                            </p>

                        </div>


                        @if($category)

                            <span
                                class="rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-[10px] font-bold text-slate-600"
                            >
                                {{ $category }}
                            </span>

                        @endif

                    </div>


                    <div
                        class="mt-3 flex flex-wrap gap-2"
                    >

                        <span
                            class="rounded-full border border-slate-200 bg-white px-2.5 py-1 text-[10px] font-bold text-slate-500"
                        >
                            {{ $deployment === 'off_server'
                                ? 'Off-server'
                                : 'SaaS'
                            }}
                        </span>

                        <span
                            class="rounded-full border border-slate-200 bg-white px-2.5 py-1 text-[10px] font-bold text-slate-500"
                        >
                            v{{ $theme['version'] }}
                        </span>

                    </div>


                    <div
                        class="mt-3 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2.5"
                    >

                        <div
                            class="text-[9px] font-black uppercase tracking-[.14em] text-slate-400"
                        >
                            Price
                        </div>

                        <div
                            class="mt-1 text-base font-black text-slate-950"
                        >
                            @if(
                                $price === null
                                || (float) $price <= 0
                            )

                                Free

                            @else

                                {{ $currency }}
                                {{ number_format(
                                    (float) $price,
                                    2
                                ) }}@if(
                                    $deployment === 'saas'
                                    && is_array($billing)
                                    && !empty($billing['period'])
                                    && !empty($billing['interval'])
                                ) / {{ (int) $billing['period'] === 1
                                    ? strtolower((string) $billing['interval'])
                                    : (int) $billing['period'] . ' ' . strtolower((string) $billing['interval']) . 's'
                                }}@endif

                            @endif
                        </div>


                        @if(
                            $deployment === 'off_server'
                        )

                            <div
                                class="mt-0.5 text-[11px] font-bold text-slate-500"
                            >
                                Off-server Theme license
                            </div>

                        @endif

                    </div>


                    <div
                        class="grid grid-cols-2 gap-2 pt-3"
                    >

                        @if($previewUrl)

                            <button
                                type="button"
                                class="theme-marketplace-preview-trigger rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-black text-slate-700 transition hover:bg-slate-50"
                                data-preview-url="{{ $previewUrl }}"
                                data-preview-name="{{ $theme['name'] }}"
                            >
                                Preview
                            </button>

                        @else

                            <button
                                type="button"
                                disabled
                                class="cursor-not-allowed rounded-xl border border-slate-200 bg-slate-100 px-3 py-2 text-xs font-black text-slate-400"
                            >
                                Preview
                            </button>

                        @endif


                        @if($installed)

                            <button
                                type="button"
                                disabled
                                class="cursor-not-allowed rounded-xl border border-emerald-200 bg-emerald-50 px-3 py-2 text-xs font-black text-emerald-700"
                            >
                                ✓ Installed
                            </button>

                        @elseif($canPurchase)

                            {{--
                                Secure checkout route is deliberately not
                                guessed. Server-side eligibility and duplicate
                                installation protection will be wired next.
                            --}}
                            <button
                                type="button"
                                disabled
                                title="Secure Theme checkout is being connected."
                                class="cursor-not-allowed rounded-xl bg-blue-600 px-3 py-2 text-xs font-black text-white opacity-70"
                            >
                                Purchase
                            </button>

                        @else

                            <button
                                type="button"
                                disabled
                                class="cursor-not-allowed rounded-xl bg-slate-100 px-3 py-2 text-xs font-black text-slate-500"
                            >
                                Unavailable
                            </button>

                        @endif

                    </div>

                </div>

            </article>

        @empty

            <div
                class="col-span-full rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-16 text-center"
            >

                <div
                    class="text-lg font-black text-slate-900"
                >
                    No compatible Themes available
                </div>

                <p
                    class="mt-2 text-sm text-slate-500"
                >
                    Eligible Themes for this website deployment will appear here.
                </p>

            </div>

        @endforelse

    </div>


    <div
        id="themeMarketplaceEmptyFilter"
        class="hidden rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-14 text-center"
    >
        <div
            class="text-lg font-black text-slate-900"
        >
            No Themes match this selection
        </div>

        <p
            class="mt-2 text-sm text-slate-500"
        >
            Try another search, category or ranking.
        </p>
    </div>

</div>


{{-- =====================================================
     ESUBIZ_CORE_THEME_MARKETPLACE_PREVIEW_MODAL_V1
     ===================================================== --}}
<div
    id="themeMarketplacePreviewModal"
    class="fixed inset-0 z-[3500] hidden overflow-y-auto bg-slate-950/75 p-3 backdrop-blur-sm sm:p-6"
    aria-hidden="true"
>

    <div
        id="themeMarketplacePreviewPanel"
        class="mx-auto flex min-h-[calc(100dvh-1.5rem)] w-full max-w-6xl flex-col overflow-hidden rounded-3xl bg-white shadow-2xl sm:min-h-[calc(100dvh-3rem)]"
    >

        <div
            class="flex shrink-0 items-center justify-between gap-4 border-b border-slate-200 bg-white px-5 py-4 sm:px-6"
        >

            <div>

                <div
                    class="text-[10px] font-black uppercase tracking-[.16em] text-blue-600"
                >
                    Theme Preview
                </div>

                <h2
                    id="themeMarketplacePreviewTitle"
                    class="mt-1 text-xl font-black text-slate-950"
                >
                    Theme Preview
                </h2>

            </div>


            <button
                type="button"
                id="themeMarketplacePreviewClose"
                class="grid h-10 w-10 place-items-center rounded-xl bg-slate-100 text-xl font-black text-slate-700 transition hover:bg-slate-200"
                aria-label="Close Theme preview"
            >
                ×
            </button>

        </div>


        <div
            class="min-h-0 flex-1 overflow-auto bg-slate-100 p-3 sm:p-5"
        >

            <div
                class="mx-auto min-h-full overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm"
            >
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


<script>
document.addEventListener(
    'DOMContentLoaded',
    function () {
        const search =
            document.getElementById(
                'themeMarketplaceSearch'
            );

        const cards =
            Array.from(
                document.querySelectorAll(
                    '.theme-marketplace-card'
                )
            );

        const filters =
            Array.from(
                document.querySelectorAll(
                    '.theme-marketplace-filter'
                )
            );

        const categories =
            Array.from(
                document.querySelectorAll(
                    '.theme-marketplace-category'
                )
            );

        const empty =
            document.getElementById(
                'themeMarketplaceEmptyFilter'
            );

        let activeFilter = 'all';
        let activeCategory = '';

        const previewModal =
            document.getElementById(
                'themeMarketplacePreviewModal'
            );

        const previewPanel =
            document.getElementById(
                'themeMarketplacePreviewPanel'
            );

        const previewImage =
            document.getElementById(
                'themeMarketplacePreviewImage'
            );

        const previewTitle =
            document.getElementById(
                'themeMarketplacePreviewTitle'
            );

        const previewClose =
            document.getElementById(
                'themeMarketplacePreviewClose'
            );

        const previewTriggers =
            Array.from(
                document.querySelectorAll(
                    '.theme-marketplace-preview-trigger'
                )
            );

        function openPreview(
            url,
            name
        ) {
            if (
                !url
                || !previewModal
                || !previewImage
            ) {
                return;
            }

            previewImage.src = url;
            previewImage.alt =
                (name || 'Theme')
                + ' Theme preview';

            if (previewTitle) {
                previewTitle.textContent =
                    (name || 'Theme')
                    + ' Preview';
            }

            previewModal.classList.remove(
                'hidden'
            );

            previewModal.setAttribute(
                'aria-hidden',
                'false'
            );

            document.documentElement.classList.add(
                'overflow-hidden'
            );
        }

        function closePreview() {
            if (!previewModal) {
                return;
            }

            previewModal.classList.add(
                'hidden'
            );

            previewModal.setAttribute(
                'aria-hidden',
                'true'
            );

            document.documentElement.classList.remove(
                'overflow-hidden'
            );

            if (previewImage) {
                previewImage.src = '';
            }
        }

        previewTriggers.forEach(
            function (trigger) {
                trigger.addEventListener(
                    'click',
                    function () {
                        openPreview(
                            trigger.dataset.previewUrl
                            || '',
                            trigger.dataset.previewName
                            || 'Theme'
                        );
                    }
                );
            }
        );

        previewClose?.addEventListener(
            'click',
            closePreview
        );

        previewModal?.addEventListener(
            'click',
            function (event) {
                if (
                    event.target
                    === previewModal
                ) {
                    closePreview();
                }
            }
        );

        previewPanel?.addEventListener(
            'click',
            function (event) {
                event.stopPropagation();
            }
        );

        document.addEventListener(
            'keydown',
            function (event) {
                if (
                    event.key === 'Escape'
                    && previewModal
                    && !previewModal.classList.contains(
                        'hidden'
                    )
                ) {
                    closePreview();
                }
            }
        );

        function refreshThemes() {
            const query =
                (
                    search?.value
                    || ''
                )
                    .trim()
                    .toLowerCase();

            let visible = 0;

            cards.forEach(
                function (card) {
                    const name =
                        card.dataset.name
                        || '';

                    const category =
                        card.dataset.category
                        || '';

                    let rankingMatch = true;

                    if (
                        activeFilter
                        === 'featured'
                    ) {
                        rankingMatch =
                            card.dataset.featured
                            === '1';
                    }

                    if (
                        activeFilter
                        === 'newest'
                    ) {
                        rankingMatch =
                            card.dataset.newest
                            === '1';
                    }

                    if (
                        activeFilter
                        === 'most-purchased'
                    ) {
                        rankingMatch =
                            card.dataset.mostPurchased
                            === '1';
                    }

                    const categoryMatch =
                        activeCategory === ''
                        || category
                            === activeCategory;

                    const searchMatch =
                        query === ''
                        || name.includes(
                            query
                        );

                    const show =
                        rankingMatch
                        && categoryMatch
                        && searchMatch;

                    card.classList.toggle(
                        'hidden',
                        !show
                    );

                    if (show) {
                        visible++;
                    }
                }
            );

            empty?.classList.toggle(
                'hidden',
                visible > 0
            );
        }


        search?.addEventListener(
            'input',
            refreshThemes
        );


        filters.forEach(
            function (button) {
                button.addEventListener(
                    'click',
                    function () {
                        activeFilter =
                            button.dataset.filter
                            || 'all';

                        filters.forEach(
                            function (item) {
                                const active =
                                    item === button;

                                item.classList.toggle(
                                    'bg-slate-950',
                                    active
                                );

                                item.classList.toggle(
                                    'text-white',
                                    active
                                );

                                item.classList.toggle(
                                    'border',
                                    !active
                                );

                                item.classList.toggle(
                                    'border-slate-200',
                                    !active
                                );

                                item.classList.toggle(
                                    'bg-white',
                                    !active
                                );

                                item.classList.toggle(
                                    'text-slate-600',
                                    !active
                                );
                            }
                        );

                        refreshThemes();
                    }
                );
            }
        );


        categories.forEach(
            function (button) {
                button.addEventListener(
                    'click',
                    function () {
                        const category =
                            button.dataset.category
                            || '';

                        activeCategory =
                            activeCategory
                            === category
                                ? ''
                                : category;

                        categories.forEach(
                            function (item) {
                                const active =
                                    item.dataset.category
                                    === activeCategory;

                                item.classList.toggle(
                                    'border-blue-200',
                                    active
                                );

                                item.classList.toggle(
                                    'bg-blue-50',
                                    active
                                );

                                item.classList.toggle(
                                    'text-blue-700',
                                    active
                                );
                            }
                        );

                        refreshThemes();
                    }
                );
            }
        );
    }
);
</script>

@endsection
