
{{-- ESUBIZ_RESOURCE_SALES_TRIGGER_DASHBOARD_STYLE_V1 --}}
<style>
    /*
     * Dashboard-only compact Premium sales area.
     * Shared sales-trigger component is intentionally untouched.
     */

    .esubiz-resource-sales-trigger {
        margin-top: 12px;
        width: 100%;
    }

    .esubiz-resource-sales-trigger > * {
        width: 100% !important;
        max-width: 100% !important;
    }

    .esubiz-resource-sales-trigger [class*="rounded"] {
        border-radius: 10px !important;
    }

    .esubiz-resource-sales-trigger [class*="p-"] {
        padding: 10px !important;
    }

    .esubiz-resource-sales-trigger h1,
    .esubiz-resource-sales-trigger h2,
    .esubiz-resource-sales-trigger h3,
    .esubiz-resource-sales-trigger h4,
    .esubiz-resource-sales-trigger h5,
    .esubiz-resource-sales-trigger h6 {
        margin: 0 0 3px !important;
        font-size: 13px !important;
        line-height: 18px !important;
    }

    .esubiz-resource-sales-trigger p {
        margin: 0 0 7px !important;
        font-size: 12px !important;
        line-height: 17px !important;
    }

    .esubiz-resource-sales-trigger button,
    .esubiz-resource-sales-trigger a {
        min-height: 0 !important;
        padding: 6px 10px !important;
        font-size: 11px !important;
        line-height: 15px !important;
        border-radius: 7px !important;
    }

    /*
     * ESUBIZ_RESOURCE_SALES_TRIGGER_ICON_HIDE_V1
     *
     * Dashboard resource cards do not need the generic
     * Premium Feature globe/icon.
     */
    .esubiz-resource-sales-trigger svg {
        display: none !important;
    }

    .esubiz-resource-sales-trigger [class*="w-"][class*="h-"]:has(svg) {
        display: none !important;
    }

    .esubiz-resource-sales-trigger [class*="gap-"] {
        gap: 7px !important;
    }

    /*
     * ESUBIZ_RESOURCE_SALES_TRIGGER_STACK_FIX_V1
     *
     * Keep Premium content and CTA fully inside narrow
     * Dashboard resource cards on desktop and mobile.
     */
    .esubiz-resource-sales-trigger [class*="flex"] {
        flex-direction: column !important;
        align-items: stretch !important;
    }

    .esubiz-resource-sales-trigger [class*="justify-"] {
        justify-content: flex-start !important;
    }

    .esubiz-resource-sales-trigger [class*="items-"] {
        align-items: stretch !important;
    }

    .esubiz-resource-sales-trigger button,
    .esubiz-resource-sales-trigger a {
        display: inline-flex !important;
        width: auto !important;
        max-width: 100% !important;
        align-self: flex-start !important;
        justify-content: center !important;
        white-space: normal !important;
        overflow-wrap: anywhere !important;
        text-align: left !important;
    }

    .esubiz-resource-sales-trigger * {
        min-width: 0 !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
    }

    /*
     * ESUBIZ_RESOURCE_SALES_BADGE_POSITION_FIX_V1
     *
     * Keep the PRO FEATURE badge in normal document flow so
     * it cannot cover the Admin-configured Premium title.
     */
    .esubiz-resource-sales-trigger [class*="absolute"] {
        position: static !important;
        inset: auto !important;
        top: auto !important;
        right: auto !important;
        bottom: auto !important;
        left: auto !important;
        transform: none !important;
    }

    .esubiz-resource-sales-trigger [class*="uppercase"] {
        align-self: flex-start !important;
        width: auto !important;
        margin: 0 0 6px 0 !important;
        white-space: nowrap !important;
    }
</style>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Dashboard - {{ $website->name }}
    </title>


    {{-- ESUBIZ_TENANT_ADMIN_FAVICON_V1 --}}
    @php
        $tenantAdminFavicon =
            $settings['theme.corporate.favicon_path']
                ?? null;
    @endphp

    @if(!empty($tenantAdminFavicon))
        <link
            rel="icon"
            href="{{ request()->getSchemeAndHttpHost()
                . '/media/'
                . implode(
                    '/',
                    array_map(
                        'rawurlencode',
                        explode(
                            '/',
                            ltrim(
                                $tenantAdminFavicon,
                                '/'
                            )
                        )
                    )
                ) }}"
        >
    @endif

    <script src="https://cdn.tailwindcss.com"></script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            min-height: 100%;
        }

        body {
            background: #f1f5f9;
        }

        [x-cloak] {
            display: none !important;
        }

        #tenantCmsSidebar::-webkit-scrollbar {
            width: 5px;
        }

        #tenantCmsSidebar::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,.18);
            border-radius: 999px;
        }

        details > summary {
            list-style: none;
        }

        details > summary::-webkit-details-marker {
            display: none;
        }

        details[open] .menu-chevron {
            transform: rotate(180deg);
        }

    </style>

</head>


<body class="text-slate-900">


{{-- =========================================================
     MOBILE OVERLAY
========================================================= --}}

<div
    id="tenantCmsOverlay"
    class="fixed inset-0 z-40 hidden bg-slate-950/50 lg:hidden"
></div>


{{-- =========================================================
     SIDEBAR
========================================================= --}}

@include(
    'tenant.admin.partials.sidebar',
    ['website' => $website]
)


{{-- =========================================================
     HEADER
========================================================= --}}

{{-- ESUBIZ_CORE_CANONICAL_HEADER_INCLUDE_V58 --}}
@include('tenant.admin.partials.header')


{{-- =========================================================
     MAIN APPLICATION AREA
========================================================= --}}

<div
    class="min-h-screen pt-[72px] lg:pl-[280px]"
>

    <main
        class="w-full p-4 sm:p-6 lg:p-8"
    >


        {{-- Heading --}}

        <div
            class="mb-7 flex flex-col gap-2"
        >

            <h1
                class="text-3xl font-black tracking-tight lg:text-4xl"
            >
                Dashboard
            </h1>

            <p
                class="text-sm text-slate-500 sm:text-base"
            >
                Overview of your website activity and business performance.
            </p>

        </div>


        {{-- =================================================
             QUICK OVERVIEW
        ================================================== --}}

        <section
            class="mb-6 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6"
        >

            <div
                class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between"
            >

                <div>

                    <div
                        class="text-xs font-black uppercase tracking-[.14em] text-blue-600"
                    >
                        Quick Overview
                    </div>

                    <h2
                        class="mt-2 text-xl font-black"
                    >
                        {{ $website->name }}
                    </h2>

                    <p
                        class="mt-1 text-sm text-slate-500"
                    >
                        Your website and Core CMS at a glance.
                    </p>

                </div>


                <span
                    class="w-fit rounded-full bg-emerald-100 px-3 py-1 text-xs font-black text-emerald-700"
                >
                    Active
                </span>

            </div>




    {{-- ESUBIZ_CORE_DASHBOARD_NOTICE_IMAGE_STANDARD_V7 --}}
    <style>
        /*
         * Central Dashboard Notice image standard.
         *
         * Recommended source:
         * 1200 x 600 px (2:1).
         *
         * The container controls the visual footprint,
         * so oversized or unusually-shaped uploads cannot
         * make the Core dashboard notice excessively large.
         */
        .es-core-dashboard-notice-image-v7 {
            position:relative;
            width:100%;
            aspect-ratio:2 / 1;
            max-height:360px;
            margin-top:16px;
            overflow:hidden;
            border-radius:14px;
            background:#f3f5f7;
        }

        .es-core-dashboard-notice-image-v7 img {
            display:block;
            width:100%;
            height:100%;
            object-fit:cover;
            object-position:center;
        }

        @media (max-width:767px) {
            .es-core-dashboard-notice-image-v7 {
                max-height:260px;
                border-radius:12px;
            }
        }
    </style>

            {{-- ESUBIZ_CORE_DASHBOARD_CENTRAL_NOTICES_V1 --}}
{{-- ESUBIZ_CORE_DASHBOARD_NOTICE_RUNTIME_V8 --}}

@php
    /*
     * ============================================================
     * ESUBIZ_CORE_DASHBOARD_NOTICE_SOURCE_V9
     * ============================================================
     *
     * One shared Core dashboard renderer, two authoritative sources:
     *
     * SaaS:
     *   Central database through TenantDashboardNoticeService.
     *
     * Off-server:
     *   authenticated Central API through the encrypted
     *   CoreCentralConnection.
     *
     * Presence of an active local Core Central connection is the
     * authoritative off-server signal. Hosted SaaS tenants do not
     * create this local connection.
     */
    $coreCentralConnection =
        app(
            \App\Services\Core\CoreCentralConnectionService::class
        )->current();

    $coreDashboardNoticeIsOffServer =
        $coreCentralConnection !== null
        && (bool) $coreCentralConnection->active;

    $coreDashboardNotices =
        $coreDashboardNoticeIsOffServer
            ? app(
                \App\Services\Core\OffServerDashboardNoticeService::class
            )->notices()
            : app(
                \App\Services\DashboardNotices\TenantDashboardNoticeService::class
            )->forWebsite(
                $website
            );

    /*
     * CentralMediaService is needed only for SaaS image_path values.
     *
     * Off-server notices already contain Central's generated
     * public image_url, and the existing V8 renderer falls back
     * to that URL automatically.
     */
    $coreDashboardNoticeMedia =
        app(
            \App\Services\Media\CentralMediaService::class
        );

    /*
     * Rotation disabled = permanently stacked.
     * Rotation enabled  = one shared rotating slot.
     */
    $coreStaticNotices =
        $coreDashboardNotices
            ->filter(
                fn ($notice) =>
                    ! (bool) ($notice->rotation_enabled ?? false)
            )
            ->values();

    $coreRotatingNotices =
        $coreDashboardNotices
            ->filter(
                fn ($notice) =>
                    (bool) ($notice->rotation_enabled ?? false)
            )
            ->values();

    $coreNoticeVariantMap = [
        'info' => [
            'class' => 'es-core-notice-info-v8',
            'label' => 'Information',
        ],
        'success' => [
            'class' => 'es-core-notice-success-v8',
            'label' => 'Success',
        ],
        'warning' => [
            'class' => 'es-core-notice-warning-v8',
            'label' => 'Notice',
        ],
        'danger' => [
            'class' => 'es-core-notice-danger-v8',
            'label' => 'Important',
        ],
        'primary' => [
            'class' => 'es-core-notice-primary-v8',
            'label' => 'Update',
        ],
        'secondary' => [
            'class' => 'es-core-notice-secondary-v8',
            'label' => 'Notice',
        ],
    ];
@endphp

@if($coreDashboardNotices->isNotEmpty())

<style>
    /* ESUBIZ_CORE_DASHBOARD_NOTICE_RUNTIME_V8 */

    .es-core-notices-v8 {
        display:grid;
        gap:14px;
        margin-bottom:24px;
    }

    .es-core-notice-v8 {
        position:relative;
        overflow:hidden;
        border:1px solid;
        border-radius:16px;
        background:#fff;
        box-shadow:0 8px 24px rgba(15,23,42,.055);
    }

    .es-core-notice-inner-v8 {
        padding:18px 20px;
    }

    .es-core-notice-top-v8 {
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        gap:18px;
    }

    .es-core-notice-heading-v8 {
        min-width:0;
    }

    .es-core-notice-label-v8 {
        display:inline-flex;
        min-height:23px;
        margin-bottom:7px;
        padding:4px 8px;
        align-items:center;
        border-radius:999px;
        font-size:10px;
        font-weight:800;
        letter-spacing:.035em;
        text-transform:uppercase;
    }

    .es-core-notice-title-v8 {
        margin:0;
        color:#172033;
        font-size:16px;
        font-weight:800;
        line-height:1.35;
    }

    .es-core-notice-message-v8 {
        margin-top:9px;
        color:#475467;
        font-size:13px;
        line-height:1.75;
        overflow-wrap:anywhere;
    }

    .es-core-notice-message-v8 p:last-child {
        margin-bottom:0;
    }

    .es-core-notice-message-v8 a {
        font-weight:700;
        text-decoration:underline;
        text-underline-offset:2px;
    }

    .es-core-notice-message-v8 img {
        max-width:100%;
        height:auto;
    }

    .es-core-notice-dismiss-v8 {
        display:flex;
        width:34px;
        height:34px;
        padding:0;
        flex:0 0 34px;
        align-items:center;
        justify-content:center;
        border:1px solid rgba(15,23,42,.09);
        border-radius:9px;
        background:rgba(255,255,255,.88);
        color:#667085;
        font-size:19px;
        line-height:1;
        cursor:pointer;
    }

    /*
     * Every rotating notice remains present in the DOM,
     * but only one is displayed at a time.
     */
    .es-core-notice-rotating-item-v8 {
        display:none;
    }

    .es-core-notice-rotating-item-v8.is-active {
        display:block;
        animation:esCoreNoticeFadeV8 .24s ease;
    }

    @keyframes esCoreNoticeFadeV8 {
        from {
            opacity:0;
            transform:translateY(3px);
        }

        to {
            opacity:1;
            transform:translateY(0);
        }
    }

    .es-core-notice-info-v8 {
        border-color:#c9def5;
        background:#f8fbff;
    }

    .es-core-notice-info-v8 .es-core-notice-label-v8 {
        background:#e7f1fb;
        color:#245f94;
    }

    .es-core-notice-success-v8 {
        border-color:#bfe5ce;
        background:#f7fcf9;
    }

    .es-core-notice-success-v8 .es-core-notice-label-v8 {
        background:#e5f6ec;
        color:#147447;
    }

    .es-core-notice-warning-v8 {
        border-color:#eed69c;
        background:#fffaf0;
    }

    .es-core-notice-warning-v8 .es-core-notice-label-v8 {
        background:#fff0c6;
        color:#8b6100;
    }

    .es-core-notice-danger-v8 {
        border-color:#efc6c2;
        background:#fff8f7;
    }

    .es-core-notice-danger-v8 .es-core-notice-label-v8 {
        background:#fde9e7;
        color:#a9382e;
    }

    .es-core-notice-primary-v8 {
        border-color:#c6d0dd;
        background:#f8fafc;
    }

    .es-core-notice-primary-v8 .es-core-notice-label-v8 {
        background:#e7ecf2;
        color:#0b1f3a;
    }

    .es-core-notice-secondary-v8 {
        border-color:#d9dde4;
        background:#fafbfc;
    }

    .es-core-notice-secondary-v8 .es-core-notice-label-v8 {
        background:#eceff3;
        color:#536071;
    }

    /*
     * Existing V7 image rule stays authoritative:
     * recommended source 1200 x 600, rendered at 2:1.
     */
    .es-core-notice-v8
    .es-core-dashboard-notice-image-v7 {
        margin-top:16px;
    }

    @media(max-width:767px) {
        .es-core-notices-v8 {
            gap:12px;
            margin-bottom:18px;
        }

        .es-core-notice-inner-v8 {
            padding:16px;
        }

        .es-core-notice-title-v8 {
            font-size:15px;
        }

        .es-core-notice-message-v8 {
            font-size:12px;
        }
    }
</style>

<section
    id="esCoreDashboardNoticesV8"
    class="es-core-notices-v8"
    aria-label="Dashboard notices"
>

    {{-- STATIC / NON-ROTATING NOTICES --}}
    @foreach($coreStaticNotices as $coreNotice)

        @php
            $coreNoticeVariant =
                strtolower(
                    (string) ($coreNotice->variant ?? 'info')
                );

            $coreNoticeVisual =
                $coreNoticeVariantMap[$coreNoticeVariant]
                ?? $coreNoticeVariantMap['info'];

            $coreNoticeImageUrl =
                !empty($coreNotice->image_path)
                    ? $coreDashboardNoticeMedia->url(
                        $coreNotice->image_path
                    )
                    : ($coreNotice->image_url ?? null);
        @endphp

        <article
            class="
                es-core-notice-v8
                es-core-notice-static-item-v8
                {{ $coreNoticeVisual['class'] }}
            "
            data-notice-id="{{ $coreNotice->id }}"
            data-rotating="0"
        >
            <div class="es-core-notice-inner-v8">

                <div class="es-core-notice-top-v8">

                    <div class="es-core-notice-heading-v8">

                        <h3 class="es-core-notice-title-v8">
                            {{ $coreNotice->title }}
                        </h3>

                    </div>

                    @if($coreNotice->dismissible)
                        <button
                            type="button"
                            class="es-core-notice-dismiss-v8"
                            aria-label="Dismiss notice"
                            title="Dismiss"
                        >
                            &times;
                        </button>
                    @endif

                </div>

                <div class="es-core-notice-message-v8">
                    {!! $coreNotice->message !!}
                </div>

                @if($coreNoticeImageUrl)
                    <div class="es-core-dashboard-notice-image-v7">
                        <img
                            src="{{ $coreNoticeImageUrl }}"
                            alt="{{ $coreNotice->title }}"
                            loading="lazy"
                        >
                    </div>
                @endif

            </div>
        </article>

    @endforeach


    {{-- ROTATING NOTICES --}}
    @if($coreRotatingNotices->isNotEmpty())

        <div
            id="esCoreDashboardNoticeRotationV8"
            aria-live="polite"
        >

            @foreach($coreRotatingNotices as $coreNotice)

                @php
                    $coreNoticeVariant =
                        strtolower(
                            (string) ($coreNotice->variant ?? 'info')
                        );

                    $coreNoticeVisual =
                        $coreNoticeVariantMap[$coreNoticeVariant]
                        ?? $coreNoticeVariantMap['info'];

                    $coreNoticeImageUrl =
                        !empty($coreNotice->image_path)
                            ? $coreDashboardNoticeMedia->url(
                                $coreNotice->image_path
                            )
                            : ($coreNotice->image_url ?? null);

                    $coreNoticeRotationSeconds =
                        max(
                            3,
                            min(
                                120,
                                (int) (
                                    $coreNotice->rotation_seconds
                                    ?: 8
                                )
                            )
                        );
                @endphp

                <article
                    class="
                        es-core-notice-v8
                        es-core-notice-rotating-item-v8
                        {{ $loop->first ? 'is-active' : '' }}
                        {{ $coreNoticeVisual['class'] }}
                    "
                    data-notice-id="{{ $coreNotice->id }}"
                    data-rotating="1"
                    data-rotation-seconds="{{ $coreNoticeRotationSeconds }}"
                >
                    <div class="es-core-notice-inner-v8">

                        <div class="es-core-notice-top-v8">

                            <div class="es-core-notice-heading-v8">

                                <h3 class="es-core-notice-title-v8">
                                    {{ $coreNotice->title }}
                                </h3>

                            </div>

                            @if($coreNotice->dismissible)
                                <button
                                    type="button"
                                    class="es-core-notice-dismiss-v8"
                                    aria-label="Dismiss notice"
                                    title="Dismiss"
                                >
                                    &times;
                                </button>
                            @endif

                        </div>

                        <div class="es-core-notice-message-v8">
                            {!! $coreNotice->message !!}
                        </div>

                        @if($coreNoticeImageUrl)
                            <div class="es-core-dashboard-notice-image-v7">
                                <img
                                    src="{{ $coreNoticeImageUrl }}"
                                    alt="{{ $coreNotice->title }}"
                                    loading="lazy"
                                >
                            </div>
                        @endif

                    </div>
                </article>

            @endforeach

        </div>

    @endif

</section>


{{-- ESUBIZ_CORE_NOTICE_IMAGE_PREVIEW_V14 --}}
<style>
    .es-core-dashboard-notice-image-v7 img {
        cursor:zoom-in;
    }

    .es-core-notice-image-preview-v14 {
        position:fixed;
        inset:0;
        z-index:99999;
        display:none;
        align-items:center;
        justify-content:center;
        padding:28px;
        background:rgba(15,23,42,.72);
        backdrop-filter:blur(4px);
    }

    .es-core-notice-image-preview-v14.is-open {
        display:flex;
    }

    .es-core-notice-image-preview-dialog-v14 {
        position:relative;
        width:min(1100px, 92vw);
        max-height:88vh;
        overflow:hidden;
        border:1px solid rgba(255,255,255,.16);
        border-radius:18px;
        background:#fff;
        box-shadow:
            0 28px 70px rgba(15,23,42,.32),
            0 8px 24px rgba(15,23,42,.16);
    }

    .es-core-notice-image-preview-head-v14 {
        display:flex;
        align-items:center;
        justify-content:space-between;
        min-height:54px;
        padding:10px 12px 10px 18px;
        border-bottom:1px solid #eaecf0;
        background:#fff;
    }

    .es-core-notice-image-preview-title-v14 {
        margin:0;
        overflow:hidden;
        color:#172033;
        font-size:14px;
        font-weight:800;
        line-height:1.4;
        text-overflow:ellipsis;
        white-space:nowrap;
    }

    .es-core-notice-image-preview-close-v14 {
        display:flex;
        width:36px;
        height:36px;
        flex:0 0 36px;
        align-items:center;
        justify-content:center;
        border:1px solid #e4e7ec;
        border-radius:10px;
        background:#fff;
        color:#475467;
        font-size:22px;
        line-height:1;
        cursor:pointer;
    }

    .es-core-notice-image-preview-close-v14:hover {
        background:#f8fafc;
        color:#101828;
    }

    .es-core-notice-image-preview-body-v14 {
        max-height:calc(88vh - 55px);
        overflow:auto;
        padding:16px;
        background:#f8fafc;
        text-align:center;
        overscroll-behavior:contain;
    }

    .es-core-notice-image-preview-body-v14 img {
        display:block;
        width:auto;
        max-width:100%;
        height:auto;
        margin:0 auto;
        border-radius:12px;
        object-fit:contain;
    }

    @media (max-width:767px) {
        .es-core-notice-image-preview-v14 {
            padding:14px;
        }

        .es-core-notice-image-preview-dialog-v14 {
            width:94vw;
            max-height:84vh;
            border-radius:15px;
        }

        .es-core-notice-image-preview-body-v14 {
            max-height:calc(84vh - 55px);
            padding:10px;
        }

        .es-core-notice-image-preview-title-v14 {
            font-size:13px;
        }
    }
</style>

<script>
document.addEventListener(
    'DOMContentLoaded',
    function () {
        const noticeRoot =
            document.getElementById(
                'esCoreDashboardNoticesV8'
            );

        if (!noticeRoot) {
            return;
        }

        const viewer =
            document.createElement('div');

        viewer.className =
            'es-core-notice-image-preview-v14';

        viewer.setAttribute(
            'aria-hidden',
            'true'
        );

        viewer.innerHTML = `
            <div
                class="es-core-notice-image-preview-dialog-v14"
                role="dialog"
                aria-modal="true"
                aria-label="Notice image preview"
            >
                <div class="es-core-notice-image-preview-head-v14">
                    <p class="es-core-notice-image-preview-title-v14">
                        Image preview
                    </p>

                    <button
                        type="button"
                        class="es-core-notice-image-preview-close-v14"
                        aria-label="Close image preview"
                        title="Close"
                    >&times;</button>
                </div>

                <div class="es-core-notice-image-preview-body-v14">
                    <img
                        src=""
                        alt=""
                    >
                </div>
            </div>
        `;

        document.body.appendChild(viewer);

        const previewImage =
            viewer.querySelector('img');

        const previewTitle =
            viewer.querySelector(
                '.es-core-notice-image-preview-title-v14'
            );

        const closeButton =
            viewer.querySelector(
                '.es-core-notice-image-preview-close-v14'
            );

        let previousBodyOverflow = '';

        function closePreview() {
            viewer.classList.remove(
                'is-open'
            );

            viewer.setAttribute(
                'aria-hidden',
                'true'
            );

            previewImage.src = '';
            previewImage.alt = '';

            document.body.style.overflow =
                previousBodyOverflow;
        }

        function openPreview(image) {
            const notice =
                image.closest(
                    '.es-core-notice-v8'
                );

            const heading =
                notice
                    ? notice.querySelector(
                        '.es-core-notice-title-v8'
                    )
                    : null;

            previewImage.src =
                image.currentSrc
                || image.src;

            previewImage.alt =
                image.alt
                || (
                    heading
                        ? heading.textContent.trim()
                        : 'Notice image'
                );

            previewTitle.textContent =
                heading
                    ? heading.textContent.trim()
                    : 'Image preview';

            previousBodyOverflow =
                document.body.style.overflow;

            document.body.style.overflow =
                'hidden';

            viewer.classList.add(
                'is-open'
            );

            viewer.setAttribute(
                'aria-hidden',
                'false'
            );

            closeButton.focus();
        }

        noticeRoot.addEventListener(
            'click',
            function (event) {
                const image =
                    event.target.closest(
                        '.es-core-dashboard-notice-image-v7 img'
                    );

                if (!image) {
                    return;
                }

                event.preventDefault();

                openPreview(image);
            }
        );

        closeButton.addEventListener(
            'click',
            closePreview
        );

        viewer.addEventListener(
            'click',
            function (event) {
                if (event.target === viewer) {
                    closePreview();
                }
            }
        );

        document.addEventListener(
            'keydown',
            function (event) {
                if (
                    event.key === 'Escape'
                    && viewer.classList.contains(
                        'is-open'
                    )
                ) {
                    closePreview();
                }
            }
        );
    }
);
</script>

<script>
document.addEventListener(
    'DOMContentLoaded',
    function () {
        const root =
            document.getElementById(
                'esCoreDashboardNoticesV8'
            );

        if (!root) {
            return;
        }

        const rotation =
            document.getElementById(
                'esCoreDashboardNoticeRotationV8'
            );

        const dismissalPrefix =
            'esubiz_core_dashboard_notice_dismissed_v8_';

        let rotationTimer = null;
        let activeIndex = 0;

        function dismissalKey(id) {
            return dismissalPrefix + String(id);
        }

        function isDismissed(notice) {
            try {
                return (
                    localStorage.getItem(
                        dismissalKey(
                            notice.dataset.noticeId
                        )
                    ) === '1'
                );
            } catch (error) {
                return false;
            }
        }

        function rememberDismissal(notice) {
            try {
                localStorage.setItem(
                    dismissalKey(
                        notice.dataset.noticeId
                    ),
                    '1'
                );
            } catch (error) {
                // Current page dismissal still works.
            }
        }

        function clearRotationTimer() {
            if (rotationTimer) {
                clearTimeout(rotationTimer);
                rotationTimer = null;
            }
        }

        function rotatingItems() {
            if (!rotation) {
                return [];
            }

            return Array.from(
                rotation.querySelectorAll(
                    '.es-core-notice-rotating-item-v8'
                )
            ).filter(
                function (notice) {
                    return (
                        notice.dataset.dismissed !== '1'
                        && !isDismissed(notice)
                    );
                }
            );
        }

        function hideRotating() {
            if (!rotation) {
                return;
            }

            rotation
                .querySelectorAll(
                    '.es-core-notice-rotating-item-v8'
                )
                .forEach(
                    function (notice) {
                        notice.classList.remove(
                            'is-active'
                        );
                    }
                );
        }

        function scheduleNext() {
            clearRotationTimer();

            const items = rotatingItems();

            if (
                items.length <= 1
                || document.visibilityState !== 'visible'
            ) {
                return;
            }

            const current =
                items[activeIndex] || items[0];

            const seconds =
                Math.max(
                    3,
                    Math.min(
                        120,
                        Number(
                            current.dataset.rotationSeconds
                            || 8
                        )
                    )
                );

            rotationTimer =
                setTimeout(
                    function () {
                        const latest =
                            rotatingItems();

                        if (latest.length <= 1) {
                            showRotating(0);
                            return;
                        }

                        showRotating(
                            (activeIndex + 1)
                            % latest.length
                        );
                    },
                    seconds * 1000
                );
        }

        function showRotating(index) {
            const items = rotatingItems();

            hideRotating();

            if (!items.length) {
                if (rotation) {
                    rotation.style.display = 'none';
                }

                activeIndex = 0;
                clearRotationTimer();
                return;
            }

            rotation.style.display = '';

            if (index >= items.length) {
                index = 0;
            }

            if (index < 0) {
                index = items.length - 1;
            }

            activeIndex = index;

            items[activeIndex]
                .classList
                .add('is-active');

            scheduleNext();
        }

        /*
         * Restore remembered dismissals.
         */
        root
            .querySelectorAll(
                '.es-core-notice-v8'
            )
            .forEach(
                function (notice) {
                    if (!isDismissed(notice)) {
                        return;
                    }

                    notice.dataset.dismissed = '1';
                    notice.style.display = 'none';
                    notice.classList.remove(
                        'is-active'
                    );
                }
            );

        /*
         * Dismiss both static and rotating notices.
         */
        root.addEventListener(
            'click',
            function (event) {
                const button =
                    event.target.closest(
                        '.es-core-notice-dismiss-v8'
                    );

                if (!button) {
                    return;
                }

                const notice =
                    button.closest(
                        '.es-core-notice-v8'
                    );

                if (!notice) {
                    return;
                }

                rememberDismissal(notice);

                notice.dataset.dismissed = '1';
                notice.style.display = 'none';
                notice.classList.remove(
                    'is-active'
                );

                if (
                    notice.dataset.rotating !== '1'
                ) {
                    return;
                }

                const remaining =
                    rotatingItems();

                if (!remaining.length) {
                    if (rotation) {
                        rotation.style.display =
                            'none';
                    }

                    clearRotationTimer();
                    return;
                }

                if (
                    activeIndex >= remaining.length
                ) {
                    activeIndex = 0;
                }

                /*
                 * Advance immediately after dismissal.
                 */
                showRotating(activeIndex);
            }
        );

        /*
         * Rotation exists only while the dashboard
         * browser tab is actually visible.
         */
        document.addEventListener(
            'visibilitychange',
            function () {
                if (
                    document.visibilityState
                    === 'visible'
                ) {
                    const items =
                        rotatingItems();

                    if (!items.length) {
                        return;
                    }

                    if (
                        activeIndex >= items.length
                    ) {
                        activeIndex = 0;
                    }

                    showRotating(activeIndex);
                    return;
                }

                clearRotationTimer();
            }
        );

        const initial =
            rotatingItems();

        if (initial.length) {
            showRotating(0);
        } else if (rotation) {
            rotation.style.display = 'none';
        }
    }
);
</script>

@endif

<div
                class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4"
            >

                <div
                    class="rounded-2xl bg-slate-50 p-4"
                >
                    <div
                        class="text-[11px] font-black uppercase tracking-wide text-slate-400"
                    >
                        Website
                    </div>

                    <div
                        class="mt-2 font-black text-slate-900"
                    >
                        {{ $website->name }}
                    </div>
                </div>


                <div
                    class="rounded-2xl bg-slate-50 p-4"
                >
                    <div
                        class="text-[11px] font-black uppercase tracking-wide text-slate-400"
                    >
                        CMS
                    </div>

                    <div
                        class="mt-2 font-black text-slate-900"
                    >
                        Esubiz Core
                    </div>
                </div>


                <div
                    class="rounded-2xl bg-slate-50 p-4"
                >
                    <div
                        class="text-[11px] font-black uppercase tracking-wide text-slate-400"
                    >
                        Website Type
                    </div>

                    <div
                        class="mt-2 font-black text-slate-900"
                    >
                        {{ ucfirst(
                            (string) (
                                $settings['website_type']
                                ?? $website->type
                                ?? 'Core'
                            )
                        ) }}
                    </div>
                </div>


                <div
                    class="rounded-2xl bg-slate-50 p-4"
                >
                    <div
                        class="text-[11px] font-black uppercase tracking-wide text-slate-400"
                    >
                        Address
                    </div>

                    <div
                        class="mt-2 truncate font-black text-blue-600"
                    >
                        {{ $website->subdomain }}.esubiz.com
                    </div>
                </div>

            </div>

        </section>


        {{-- =================================================
             MODULE-AWARE STATS
        ================================================== --}}

        <section
            class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4"
        >

            @foreach($dashboardStats as $stat)

                @php
                    /*
                     * ESUBIZ_GENERIC_RESOURCE_CARD_DISPLAY_GATE_V1
                     *
                     * Normal dashboard statistics remain unchanged.
                     * Resource cards obey Central Admin Resource Settings.
                     */
                    $showDashboardStat = true;

                    if (!empty($stat['resource'])) {
                        $dashboardStatResourceKey =
                            $stat['resource_key']
                            ?? $stat['key']
                            ?? $stat['icon']
                            ?? null;

                        $dashboardStatResourceSetting =
                            $dashboardStatResourceKey
                                ? \Illuminate\Support\Facades\DB::table(
                                    'core_resource_settings'
                                )
                                    ->where(
                                        'resource_key',
                                        $dashboardStatResourceKey
                                    )
                                    ->first()
                                : null;

                        $dashboardStatDeploymentVisible =
                            $dashboardStatResourceSetting
                            && (bool) (
                                $dashboardStatResourceSetting
                                    ->is_active
                                ?? false
                            )
                            && (
                                !empty($website->is_off_server)
                                    ? (bool) (
                                        $dashboardStatResourceSetting
                                            ->off_server_visible
                                        ?? false
                                    )
                                    : (bool) (
                                        $dashboardStatResourceSetting
                                            ->saas_visible
                                        ?? false
                                    )
                            );

                        $dashboardStatPercentage = (float) (
                            $stat['percentage'] ?? 0
                        );

                        $dashboardStatDisplayThreshold = (float) (
                            $dashboardStatResourceSetting
                                ->dashboard_threshold_percentage
                            ?? 100
                        );

                        $showDashboardStat =
                            $dashboardStatDeploymentVisible
                            && $dashboardStatPercentage
                                >= $dashboardStatDisplayThreshold;
                    }
                @endphp

                @if($showDashboardStat)

                <div
                    class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
                >

                    <div
                        class="flex items-start justify-between gap-4"
                    >

                        <div>

                            <div
                                class="text-xs font-black uppercase tracking-wide text-slate-400"
                            >
                                {{ $stat['label'] }}
                            </div>

                            <div
                                class="mt-3 break-words text-2xl font-black text-slate-950"
                            >
                                {{ $stat['value'] }}
                            </div>

                            @if(!empty($stat['resource']))

                                <div class="mt-3">

                                    <div
                                        class="mb-2 flex items-center justify-between gap-3 text-[11px] font-bold text-slate-500"
                                    >
                                        <span>
                                            {{ $stat['resource_detail'] ?? '' }}
                                        </span>

                                        <span>
                                            {{ number_format(
                                                (float) ($stat['percentage'] ?? 0),
                                                1
                                            ) }}%
                                        </span>
                                    </div>

                                    @php
                                        /*
                                         * ESUBIZ_RESOURCE_METER_THRESHOLD_UI_V1
                                         */
                                        $resourceStatus =
                                            $stat['resource_status']
                                            ?? 'normal';

                                        $resourceBarClass =
                                            $resourceStatus === 'critical'
                                                ? 'bg-red-600'
                                                : (
                                                    $resourceStatus === 'warning'
                                                        ? 'bg-amber-500'
                                                        : 'bg-blue-600'
                                                );
                                    @endphp

                                    <div
                                        class="h-2 overflow-hidden rounded-full bg-slate-100"
                                    >
                                        <div
                                            class="h-full rounded-full {{ $resourceBarClass }} transition-all"
                                            style="width: {{ min(
                                                100,
                                                max(
                                                    0,
                                                    (float) ($stat['percentage'] ?? 0)
                                                )
                                            ) }}%"
                                        ></div>
                                    </div>






                                </div>

                            @endif

                        </div>


                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-lg font-black text-blue-600"
                        >
                            @switch($stat['icon'])

                                @case('storage')
                                    ▣
                                    @break

                                @case('bandwidth')
                                    ↕
                                    @break

                                @case('orders')
                                    ≡
                                    @break

                                @case('sales')
                                    ₦
                                    @break

                                @case('products')
                                    □
                                    @break

                                @case('clients')
                                    ◎
                                    @break

                                @case('staff')
                                    ♙
                                    @break

                                @case('tickets')
                                    ✉
                                    @break

                                @case('bookings')
                                    ◫
                                    @break

                                @case('users')
                                    ◉
                                    @break

                                @default
                                    ▦

                            @endswitch
                        </div>

                    </div>

                @if(
                    !empty($stat['resource'])
                    && !empty(
                        $stat['resource_key']
                        ?? $stat['key']
                        ?? $stat['icon']
                        ?? null
                    )
                )
                    @php
                        /*
                         * ESUBIZ_RESOURCE_CARD_SALES_TRIGGER_V2
                         *
                         * Sales Trigger belongs inside the resource card.
                         * No Add-on/resource names are hardcoded.
                         */
                        $resourceSalesKey =
                            $stat['resource_key']
                            ?? $stat['key']
                            ?? $stat['icon'];
                    @endphp

                    {{-- ESUBIZ_COMPACT_RESOURCE_SALES_TRIGGER_V1 --}}
                    <div class="esubiz-resource-sales-trigger">
                        <x-core-addon-sales-triggers
                            location="dashboard"
                            :website="$website"
                            :context="[
                                'resources' => [
                                    $resourceSalesKey => [
                                        'percentage' => (float) (
                                            $stat['percentage'] ?? 0
                                        ),
                                        'used_percentage' => (float) (
                                            $stat['percentage'] ?? 0
                                        ),
                                        'usage_percentage' => (float) (
                                            $stat['percentage'] ?? 0
                                        ),
                                        'status' =>
                                            $stat['resource_status']
                                            ?? 'normal',
                                    ],
                                ],
                            ]"
                        />
                    </div>
                @endif

                </div>

                @endif

            @endforeach

            {{-- ESUBIZ_UNIVERSAL_ADDON_PLACEMENT_DASHBOARD_V1 --}}
            @php
                /*
                 * Universal Dashboard Add-on context.
                 *
                 * ESUBIZ_UNIVERSAL_DASHBOARD_RESOURCE_DISPLAY_GATE_V1
                 *
                 * No resource/Add-on is hardcoded here.
                 *
                 * A resource is exposed to Dashboard sales triggers only when:
                 *  - Central Admin has an active resource setting;
                 *  - it is visible for this deployment type;
                 *  - its configured Dashboard display threshold is reached.
                 *
                 * The Add-on's own Dashboard sales threshold/limit is then
                 * evaluated independently by the generic trigger resolver.
                 */
                $addonDashboardResources = [];

                $dashboardDeploymentType =
                    !empty($website->is_off_server)
                        ? 'off_server'
                        : 'saas';

                $dashboardResourceSettings = \Illuminate\Support\Facades\DB::table(
                    'core_resource_settings'
                )
                    ->where('is_active', 1)
                    ->get()
                    ->keyBy('resource_key');

                foreach (($dashboardStats ?? []) as $dashboardResourceStat) {
                    $dashboardResourceKey =
                        $dashboardResourceStat['resource_key']
                        ?? $dashboardResourceStat['key']
                        ?? $dashboardResourceStat['icon']
                        ?? null;

                    if (empty($dashboardResourceKey)) {
                        continue;
                    }

                    $dashboardResourceSetting =
                        $dashboardResourceSettings->get(
                            $dashboardResourceKey
                        );

                    if (!$dashboardResourceSetting) {
                        continue;
                    }

                    $dashboardResourceVisible =
                        $dashboardDeploymentType === 'off_server'
                            ? (bool) (
                                $dashboardResourceSetting->off_server_visible
                                ?? false
                            )
                            : (bool) (
                                $dashboardResourceSetting->saas_visible
                                ?? false
                            );

                    if (!$dashboardResourceVisible) {
                        continue;
                    }

                    $dashboardResourcePercentage = (float) (
                        $dashboardResourceStat['percentage'] ?? 0
                    );

                    $dashboardDisplayThreshold = (float) (
                        $dashboardResourceSetting
                            ->dashboard_threshold_percentage
                        ?? 100
                    );

                    if (
                        $dashboardResourcePercentage
                        < $dashboardDisplayThreshold
                    ) {
                        continue;
                    }

                    $addonDashboardResources[$dashboardResourceKey] = [
                        'percentage' =>
                            $dashboardResourcePercentage,

                        'used_percentage' =>
                            $dashboardResourcePercentage,

                        'usage_percentage' =>
                            $dashboardResourcePercentage,

                        'status' =>
                            $dashboardResourceStat[
                                'resource_status'
                            ]
                            ?? 'normal',
                    ];
                }
            @endphp


        </section>


        {{-- =================================================
             INSTALLED MODULES
        ================================================== --}}

        <section
            class="mt-6 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6"
        >

            <div>

                <h2
                    class="text-xl font-black"
                >
                    Installed Modules
                </h2>

                <p
                    class="mt-1 text-sm text-slate-500"
                >
                    Enabled modules and their activity will appear here automatically.
                </p>

            </div>


            <div
                class="mt-5 rounded-2xl border border-dashed border-slate-200 bg-slate-50 p-5"
            >

                <p
                    class="text-sm leading-6 text-slate-500"
                >
                    Ecommerce, Hotel, Restaurant and other installed
                    module summaries will be loaded here from the
                    tenant module registry.
                </p>

            </div>

        </section>


        {{-- =================================================
             FINANCIAL CHART
        ================================================== --}}

        <section
            class="mt-6 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6"
        >

            <div
                class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between"
            >

                <div>

                    <h2
                        class="text-xl font-black"
                    >
                        Income vs Expenses
                    </h2>

                    <p
                        class="mt-1 text-sm text-slate-500"
                    >
                        Financial performance for the last 7 days.
                    </p>

                </div>


                <span
                    class="w-fit rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-500"
                >
                    Last 7 days
                </span>

            </div>


            <div
                class="mt-6 h-[300px] w-full sm:h-[360px]"
            >

                <canvas
                    id="incomeExpenseChart"
                ></canvas>

            </div>

        </section>



<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        /*
         * Mobile navigation
         */
        const sidebar =
            document.getElementById(
                'tenantCmsSidebar'
            );

        const overlay =
            document.getElementById(
                'tenantCmsOverlay'
            );

        const menuButton =
            document.getElementById(
                'tenantCmsMenuButton'
            );

        const closeButton =
            document.getElementById(
                'tenantCmsCloseButton'
            );


        const openMenu = function () {

            sidebar.classList.remove(
                '-translate-x-full'
            );

            overlay.classList.remove(
                'hidden'
            );

            document.body.classList.add(
                'overflow-hidden'
            );

        };


        const closeMenu = function () {

            sidebar.classList.add(
                '-translate-x-full'
            );

            overlay.classList.add(
                'hidden'
            );

            document.body.classList.remove(
                'overflow-hidden'
            );

        };


        if (menuButton) {
            menuButton.addEventListener(
                'click',
                openMenu
            );
        }


        if (closeButton) {
            closeButton.addEventListener(
                'click',
                closeMenu
            );
        }


        if (overlay) {
            overlay.addEventListener(
                'click',
                closeMenu
            );
        }


        document.addEventListener(
            'keydown',
            function (event) {

                if (event.key === 'Escape') {
                    closeMenu();
                }

            }
        );


        /*
         * Income / expense chart
         */
        const chartElement =
            document.getElementById(
                'incomeExpenseChart'
            );

        if (
            chartElement
            && typeof Chart !== 'undefined'
        ) {

            new Chart(
                chartElement,
                {
                    type: 'line',

                    data: {
                        labels:
                            @json($chartLabels),

                        datasets: [
                            {
                                label: 'Income',
                                data:
                                    @json($chartIncome),
                                borderColor:
                                    '#2563eb',
                                backgroundColor:
                                    'rgba(37,99,235,.08)',
                                tension: .35,
                                fill: true
                            },
                            {
                                label: 'Expenses',
                                data:
                                    @json($chartExpenses),
                                borderColor:
                                    '#ef4444',
                                backgroundColor:
                                    'rgba(239,68,68,.05)',
                                tension: .35,
                                fill: true
                            }
                        ]
                    },

                    options: {
                        responsive: true,
                        maintainAspectRatio: false,

                        interaction: {
                            mode: 'index',
                            intersect: false
                        },

                        plugins: {
                            legend: {
                                position: 'top',
                                align: 'end'
                            }
                        },

                        scales: {
                            y: {
                                beginAtZero: true,

                                grid: {
                                    color:
                                        'rgba(148,163,184,.15)'
                                }
                            },

                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                }
            );

        }

    }
);

</script>

</body>
</html>
