{{-- ESUBIZ_INTERNAL_MEDIA_ON_DEMAND_V1 --}}

{{-- ============================================================
     ESUBIZ_THEME_HOMEPAGE_AI_CONTRIBUTION
============================================================ --}}
@php
    /*
     * Theme Config contributes functions only.
     *
     * The global AI Assistant itself already exists in the
     * Tenant Admin base layout.
     *
     * Failure here must NEVER break Theme Config.
     */
    try {

        $siteAiCapability =
            app(
                \App\Services\SiteAi\Capabilities\ThemeHomepageCapability::class
            )->definition();


        app(
            \App\Services\SiteAi\Support\SiteAiContext::class
        )->contribute([
            'source' =>
                $siteAiCapability['key']
                ?? 'theme.homepage',

            'label' =>
                $siteAiCapability['label']
                ?? 'Theme Homepage',

            'description' =>
                'Select the homepage areas you want AI to build or customize.',

            'functions' =>
                $siteAiCapability['functions']
                ?? [],

            'context' => [
                /*
                 * Generated assets belong to this website,
                 * never Esubiz Central asset storage.
                 */
                'workspace_type' =>
                    'tenant',

                'output_owner' =>
                    'requesting_workspace',

                'central_asset_storage' =>
                    false,

                'theme' =>
                    $themeSlug
                    ?? (
                        is_object($theme ?? null)
                            ? (
                                $theme->slug
                                ?? $theme->key
                                ?? null
                            )
                            : (
                                is_string($theme ?? null)
                                    ? $theme
                                    : null
                            )
                    ),

                'website_id' =>
                    $website->id
                    ?? null,
            ],
        ]);

    } catch (\Throwable $siteAiException) {

        /*
         * AI is optional assistance.
         * Never allow it to damage the existing editor.
         */
        report(
            $siteAiException
        );
    }
@endphp

@extends('tenant.admin.layouts.app')

@section('title', 'Configure Business Theme')

@section('content')

<style data-theme-visibility-toggle-css>
    .theme-visibility-toggle {
        display: inline-grid;
        grid-template-columns: 1fr 1fr;
        width: 132px;
        height: 38px;
        border-radius: 999px;
        overflow: hidden;
        position: relative;
        border: 1px solid rgb(226 232 240);
        background: rgb(241 245 249);
        cursor: pointer;
        user-select: none;
    }

    .theme-visibility-toggle input {
        position: absolute;
        opacity: 0;
        pointer-events: none;
    }

    .theme-visibility-toggle .toggle-half {
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        z-index: 2;
        font-size: 11px;
        font-weight: 900;
        transition: color .18s ease;
    }

    .theme-visibility-toggle .toggle-hide {
        color: rgb(185 28 28);
    }

    .theme-visibility-toggle .toggle-show {
        color: rgb(21 128 61);
    }

    .theme-visibility-toggle::before {
        content: '';
        position: absolute;
        left: 3px;
        top: 3px;
        width: calc(50% - 3px);
        height: 30px;
        border-radius: 999px;
        background: rgb(220 38 38);
        transition:
            transform .2s ease,
            background .2s ease;
        z-index: 1;
    }

    .theme-visibility-toggle:has(input:checked)::before {
        transform: translateX(100%);
        background: rgb(22 163 74);
    }

    .theme-visibility-toggle:not(:has(input:checked))
        .toggle-hide,
    .theme-visibility-toggle:has(input:checked)
        .toggle-show {
        color: white;
    }
</style>


@php
    $assetUrl = static function (?string $path): ?string {
        if (empty($path)) {
            return null;
        }

        return request()->getSchemeAndHttpHost()
            . '/media/'
            . implode(
                '/',
                array_map(
                    'rawurlencode',
                    explode(
                        '/',
                        ltrim($path, '/')
                    )
                )
            );
    };
@endphp

<div class="mx-auto max-w-7xl space-y-8">

    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">

        <div>
            <div class="text-xs font-black uppercase tracking-[.16em] text-blue-600">
                Business · v1.0
            </div>

            <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900">
                Theme Configuration
            </h1>

            <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                Every visible homepage section can be customized here.
            </p>
        </div>

        <div class="flex gap-3">

            <a
                href="{{ route(
                    'tenant.cms.themes.index',
                    ['subdomain' => $website->subdomain]
                ) }}"
                class="rounded-xl border border-slate-200 bg-white px-5 py-3 text-sm font-black text-slate-700"
            >
                ← Themes
            </a>

            <a
                href="https://{{ $website->subdomain }}.esubiz.com"
                target="_blank"
                class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-black text-white"
            >
                View Website ↗
            </a>

        </div>

    </div>


    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-700">
            {{ session('success') }}
        </div>
    @endif


    @if($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
            <div class="font-black">
                Please correct the following:
            </div>

            <ul class="mt-2 list-disc space-y-1 pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    <form
        method="POST"
        enctype="multipart/form-data"
        action="{{ route(
            'tenant.cms.themes.business.update',
            ['subdomain' => $website->subdomain]
        ) }}"
        class="space-y-8"
        id="businessThemeForm"
    >
        @csrf


        {{-- ==================================================
             BRAND + HEADER
        =================================================== --}}

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">

            <div>
                <div class="text-xs font-black uppercase tracking-[.14em] text-blue-600">
                    Header
                </div>

                <h2 class="mt-2 text-xl font-black text-slate-900">
                    Brand & Navigation
                </h2>
            </div>


            <div class="mt-6 grid gap-6 lg:grid-cols-2">

                @foreach([
                    ['primary_color', 'Primary Color'],
                    ['secondary_color', 'Secondary Color'],
                ] as [$key, $label])

                    <div>
                        <label class="text-xs font-black uppercase tracking-wide text-slate-500">
                            {{ $label }}
                        </label>

                        <div class="mt-2 flex gap-3">

                            <input
                                type="color"
                                value="{{ old($key, $theme[$key]) }}"
                                class="h-12 w-16 cursor-pointer rounded-xl border border-slate-200 bg-white p-1"
                                oninput="this.nextElementSibling.value=this.value"
                            >

                            <input
                                type="text"
                                name="{{ $key }}"
                                value="{{ old($key, $theme[$key]) }}"
                                class="w-full rounded-xl border border-slate-200 px-4 py-3 text-sm font-bold"
                                oninput="
                                    if(/^#[0-9A-Fa-f]{6}$/.test(this.value)){
                                        this.previousElementSibling.value=this.value;
                                    }
                                "
                            >

                        </div>
                    </div>

                @endforeach

            </div>


            <div class="mt-7 grid gap-6 lg:grid-cols-2">

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">

                    <div class="font-black text-slate-900">
                        Header Logo
                    </div>

                    <div class="mt-1 text-xs text-slate-500">
                        Recommended: 180 × 60 px · PNG, JPG or WebP · Max 2 MB.
                    </div>

                    @if(!empty($theme['logo_path']))
                        <div class="mt-4 rounded-xl bg-white p-4">
                            <x-media.image
    src="{{ $assetUrl($theme['logo_path']) }}"
    alt="Current logo"
    class="max-h-16 max-w-[220px] object-contain"
/>
                        </div>
                    @endif

                    <input
                        type="file"
                        name="logo"
                        accept="image/*"
                        class="mt-4 block w-full text-sm"
                    >

                        <input
                            type="hidden"
                            name="remove_logo"
                            value="0"
                            data-theme-remove-input="remove_logo"
                        >

                        @if(!empty($theme['logo_path']))
                            <button
                                type="button"
                                class="mt-3 rounded-xl border border-red-200 bg-red-50 px-4 py-2 text-xs font-black text-red-600"
                                data-theme-remove-photo="remove_logo"
                            >
                                Delete Photo
                            </button>
                        @endif


                </div>


                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">

                    <div class="font-black text-slate-900">
                        Favicon
                    </div>

                    <div class="mt-1 text-xs text-slate-500">
                        Recommended: 64 × 64 px or 128 × 128 px square · Max 1 MB.
                    </div>

                    @if(!empty($theme['favicon_path']))
                        <div class="mt-4">
                            <x-media.image
    src="{{ $assetUrl($theme['favicon_path']) }}"
    alt="Current favicon"
    class="h-14 w-14 rounded-xl object-cover"
/>
                        </div>
                    @endif

                    <input
                        type="file"
                        name="favicon"
                        accept="image/*"
                        class="mt-4 block w-full text-sm"
                    >

                        <input
                            type="hidden"
                            name="remove_favicon"
                            value="0"
                            data-theme-remove-input="remove_favicon"
                        >

                        @if(!empty($theme['favicon_path']))
                            <button
                                type="button"
                                class="mt-3 rounded-xl border border-red-200 bg-red-50 px-4 py-2 text-xs font-black text-red-600"
                                data-theme-remove-photo="remove_favicon"
                            >
                                Delete Photo
                            </button>
                        @endif


                </div>

            </div>



            <div
                class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"
                data-theme-menu-position="header"
            >

                <div
                    class="flex flex-wrap items-start justify-between gap-4"
                >

                    <div>
                        <div
                            class="text-xs font-black uppercase tracking-[.14em] text-blue-600"
                        >
                            Core Menu Position
                        </div>

                        <h3
                            class="mt-2 text-xl font-black text-slate-900"
                        >
                            Header Menu
                        </h3>

                        <p
                            class="mt-2 max-w-3xl text-sm leading-6 text-slate-500"
                        >
                            This theme inherits the Header Menu
                            configured in Esubiz Core.
                        </p>
                    </div>


                    <div
                        class="rounded-xl bg-blue-50 px-4 py-2 text-xs font-black uppercase tracking-wide text-blue-600"
                    >
                        Inherited
                    </div>

                </div>


                <div
                    class="mt-6 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-5"
                >
                    <div
                        class="text-xs font-black uppercase tracking-wide text-slate-400"
                    >
                        Menu Position
                    </div>

                    <div
                        class="mt-2 text-base font-black text-slate-900"
                    >
                        Header Menu
                    </div>

                    <p
                        class="mt-2 text-sm leading-6 text-slate-500"
                    >
                        Menu items will be managed from the Core
                        Menu Manager. Any menu assigned to this
                        position will automatically appear in the
                        theme header.
                    </p>
                </div>

            </div>


        </section>


        {{--
        |--------------------------------------------------------------------------
        | BUSINESS HOME CONTENT
        |--------------------------------------------------------------------------
        |
        | Hero, Features, Statistics, About, Testimonials and CTA are edited
        | from Pages > Business Home.
        |
        --}}

        {{-- ==================================================
             FOOTER
        =================================================== --}}

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">







        {{-- =========================================================
             BUSINESS V1.0 NATIVE HOMEPAGE
             ========================================================= --}}

        <section
            class="space-y-8"
            data-business-native-homepage-config
        >
            <div
                class="rounded-3xl border border-blue-200 bg-blue-50 p-6"
            >
                <div
                    class="text-xs font-black uppercase tracking-[.16em] text-blue-600"
                >
                    Homepage
                </div>

                <h2
                    class="mt-2 text-2xl font-black text-slate-900"
                >
                    Business Homepage
                </h2>

                <p
                    class="mt-2 max-w-3xl text-sm leading-6 text-slate-600"
                >
                    Configure the native Business theme landing page.
                    Hero, Features, Statistics, About, Testimonials
                    and CTA can each be shown or hidden.
                </p>
            </div>

            @include(
                'tenant.admin.pages.partials.business-home'
            )
        </section>

        {{-- Native footer configuration follows homepage --}}

<div
            class="w-full"
            style="
                width:100%;
                max-width:none;
                grid-column:1 / -1;
            "
            data-footer-full-width-wrapper
        >

<section
            class="space-y-8 w-full"
            data-footer-four-section-editor
        >

            <div>
                <div
                    class="text-xs font-black uppercase tracking-[.16em] text-blue-600"
                >
                    Footer
                </div>

                <h2
                    class="mt-2 text-2xl font-black text-slate-900"
                >
                    Footer Layout
                </h2>

                <p
                    class="mt-2 max-w-3xl text-sm leading-6 text-slate-500"
                >
                    Each footer section can be shown or hidden independently.
                    Hidden sections are automatically removed from the layout.
                </p>
            </div>


            <div class="grid w-full grid-cols-1 gap-6">


                {{-- =================================================
                     SECTION 1 — BRAND
                ================================================== --}}

                <div
                    class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"
                    data-footer-config-section="brand"
                 style="width:100%; grid-column:1 / -1;">

                    <div
                        class="flex flex-wrap items-start justify-between gap-4"
                    >

                        <div>
                            <div
                                class="text-xs font-black uppercase tracking-[.14em] text-blue-600"
                            >
                                Footer Section 1
                            </div>

                            <h3
                                class="mt-2 text-xl font-black text-slate-900"
                            >
                                Brand
                            </h3>

                            <p
                                class="mt-1 text-sm text-slate-500"
                            >
                                Logo and short company description.
                            </p>
                        </div>


                        <div
                            class="inline-flex overflow-hidden rounded-xl border border-slate-200 text-[10px] font-black uppercase"
                            data-inline-toggle="footer_brand_enabled"
                        >

                            <label class="cursor-pointer">
                                <input
                                    type="radio"
                                    name="footer_brand_enabled"
                                    value="0"
                                    class="peer sr-only"
                                    {{
                                        old(
                                            'footer_brand_enabled',
                                            $theme[
                                                'footer_brand_enabled'
                                            ] ?? '1'
                                        ) === '0'
                                            ? 'checked'
                                            : ''
                                    }}
                                >

                                <span
                                    class="block px-4 py-2 text-slate-400 peer-checked:bg-red-600 peer-checked:text-white"
                                >
                                    Hide
                                </span>
                            </label>


                            <label
                                class="cursor-pointer border-l border-slate-200"
                            >
                                <input
                                    type="radio"
                                    name="footer_brand_enabled"
                                    value="1"
                                    class="peer sr-only"
                                    {{
                                        old(
                                            'footer_brand_enabled',
                                            $theme[
                                                'footer_brand_enabled'
                                            ] ?? '1'
                                        ) === '1'
                                            ? 'checked'
                                            : ''
                                    }}
                                >

                                <span
                                    class="block px-4 py-2 text-slate-400 peer-checked:bg-emerald-600 peer-checked:text-white"
                                >
                                    Show
                                </span>
                            </label>

                        </div>

                    </div>


                    <div class="mt-6 grid gap-6 lg:grid-cols-2">

                        <div
                            class="rounded-2xl bg-slate-50 p-5"
                        >
                            <div class="flex items-center justify-between gap-3">

                                <div class="font-black text-slate-900">
                                    Footer Logo
                                </div>


<div
    class="inline-flex overflow-hidden rounded-xl border border-slate-200 text-[10px] font-black uppercase"
    data-inline-toggle="footer_logo_enabled"
>
    <label class="cursor-pointer">
        <input
            type="radio"
            name="footer_logo_enabled"
            value="0"
            class="peer sr-only"
            {{
                old(
                    'footer_logo_enabled',
                    $theme[
                        'footer_logo_enabled'
                    ] ?? '1'
                ) === '0'
                    ? 'checked'
                    : ''
            }}
        >
        <span
            class="block px-3 py-2 text-slate-400 peer-checked:bg-red-600 peer-checked:text-white"
        >
            Hide
        </span>
    </label>

    <label
        class="cursor-pointer border-l border-slate-200"
    >
        <input
            type="radio"
            name="footer_logo_enabled"
            value="1"
            class="peer sr-only"
            {{
                old(
                    'footer_logo_enabled',
                    $theme[
                        'footer_logo_enabled'
                    ] ?? '1'
                ) === '1'
                    ? 'checked'
                    : ''
            }}
        >
        <span
            class="block px-3 py-2 text-slate-400 peer-checked:bg-emerald-600 peer-checked:text-white"
        >
            Show
        </span>
    </label>
</div>


                            </div>

                            <div
                                class="mt-1 text-xs text-slate-500"
                            >
                                Recommended: 180 × 60 px · PNG / JPG / WEBP.
                            </div>

                            @if(
                                !empty(
                                    $theme[
                                        'footer_logo_path'
                                    ]
                                )
                            )

                                <x-media.image
    src="{{
                                        $assetUrl(
                                            $theme[
                                                'footer_logo_path'
                                            ]
                                        )
                                    }}"
    alt=""
    class="mt-4 max-h-16 max-w-[220px] object-contain"
/>

                            @endif


                            <input
                                type="file"
                                name="footer_logo"
                                accept="image/*"
                                class="mt-4 block w-full text-sm"
                            >


                            <input
                                type="hidden"
                                name="remove_footer_logo"
                                value="0"
                                data-theme-remove-input="remove_footer_logo"
                            >


                            <div
                                class="mt-4 flex flex-wrap gap-2"
                            >

                                <button
                                    type="button"
                                    class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-2 text-xs font-black text-blue-600"
                                    data-theme-media-picker="footer_logo_path"
                                >
                                    Choose from Media
                                </button>


                                @if(
                                    !empty(
                                        $theme[
                                            'footer_logo_path'
                                        ]
                                    )
                                )

                                    <button
                                        type="button"
                                        class="rounded-xl border border-red-200 bg-red-50 px-4 py-2 text-xs font-black text-red-600"
                                        data-theme-remove-photo="remove_footer_logo"
                                    >
                                        Delete Photo
                                    </button>

                                @endif

                            </div>

                        </div>


                        <div class="grid gap-5">

                            <div>
                                <div class="flex items-center justify-between gap-3">

                                <label
                                    class="text-xs font-black uppercase tracking-wide text-slate-500"
                                >
                                    Short Text
                                </label>


<div
    class="inline-flex overflow-hidden rounded-xl border border-slate-200 text-[10px] font-black uppercase"
    data-inline-toggle="footer_text_enabled"
>
    <label class="cursor-pointer">
        <input
            type="radio"
            name="footer_text_enabled"
            value="0"
            class="peer sr-only"
            {{
                old(
                    'footer_text_enabled',
                    $theme[
                        'footer_text_enabled'
                    ] ?? '1'
                ) === '0'
                    ? 'checked'
                    : ''
            }}
        >
        <span
            class="block px-3 py-2 text-slate-400 peer-checked:bg-red-600 peer-checked:text-white"
        >
            Hide
        </span>
    </label>

    <label
        class="cursor-pointer border-l border-slate-200"
    >
        <input
            type="radio"
            name="footer_text_enabled"
            value="1"
            class="peer sr-only"
            {{
                old(
                    'footer_text_enabled',
                    $theme[
                        'footer_text_enabled'
                    ] ?? '1'
                ) === '1'
                    ? 'checked'
                    : ''
            }}
        >
        <span
            class="block px-3 py-2 text-slate-400 peer-checked:bg-emerald-600 peer-checked:text-white"
        >
            Show
        </span>
    </label>
</div>


                            </div>

                                <textarea
                                    name="footer_text"
                                    rows="3"
                                    maxlength="180"
                                    class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"
                                    placeholder="A short description of the business."
                                >{{ old(
                                    'footer_text',
                                    $theme[
                                        'footer_text'
                                    ] ?? ''
                                ) }}</textarea>

                                <div
                                    class="mt-2 text-xs text-slate-400"
                                >
                                    Keep this brief — maximum 180 characters.
                                </div>
                            </div>

                        </div>

                    </div>

                </div>



                {{-- =================================================
                     SECTION 2 — CONTACT + SOCIAL
                ================================================== --}}


                    @php
                        /*
                         * Reload footer social links exactly from
                         * the stored theme setting.
                         *
                         * Public footer already proves this JSON
                         * is being saved correctly.
                         */
                        $storedFooterSocialsRaw =
                            (string) (
                                $theme[
                                    'footer_socials_json'
                                ] ?? '[]'
                            );

                        $storedFooterSocials =
                            json_decode(
                                $storedFooterSocialsRaw,
                                true
                            );

                        if (
                            !is_array(
                                $storedFooterSocials
                            )
                        ) {
                            $storedFooterSocials = [];
                        }


                        /*
                         * Normalize all historical formats to:
                         *
                         * [
                         *   platform => instagram,
                         *   url => https://...
                         * ]
                         */
                        $savedFooterSocials = [];

                        foreach (
                            $storedFooterSocials
                            as $storedSocial
                        ) {

                            if (
                                !is_array(
                                    $storedSocial
                                )
                            ) {
                                continue;
                            }

                            $url =
                                trim(
                                    (string) (
                                        $storedSocial[
                                            'url'
                                        ] ?? ''
                                    )
                                );


                            $platform =
                                trim(
                                    strtolower(
                                        (string) (
                                            $storedSocial[
                                                'platform'
                                            ]
                                            ?? $storedSocial[
                                                'icon'
                                            ]
                                            ?? $storedSocial[
                                                'label'
                                            ]
                                            ?? ''
                                        )
                                    )
                                );


                            /*
                             * If the old format stored a human
                             * label, normalize it.
                             */
                            $platform =
                                match ($platform) {

                                    'twitter',
                                    'twitter / x',
                                    'x / twitter' =>
                                        'x',

                                    'linked in' =>
                                        'linkedin',

                                    'facebook icon' =>
                                        'facebook',

                                    'instagram icon' =>
                                        'instagram',

                                    'linkedin icon' =>
                                        'linkedin',

                                    'youtube icon' =>
                                        'youtube',

                                    'tiktok icon' =>
                                        'tiktok',

                                    'whatsapp icon' =>
                                        'whatsapp',

                                    default =>
                                        $platform,
                                };


                            /*
                             * Last fallback:
                             * infer platform from the URL itself.
                             */
                            if (
                                $platform === ''
                                && $url !== ''
                            ) {

                                $host =
                                    strtolower(
                                        (string) parse_url(
                                            $url,
                                            PHP_URL_HOST
                                        )
                                    );

                                if (
                                    str_contains(
                                        $host,
                                        'instagram.'
                                    )
                                ) {
                                    $platform =
                                        'instagram';

                                } elseif (
                                    str_contains(
                                        $host,
                                        'facebook.'
                                    )
                                ) {
                                    $platform =
                                        'facebook';

                                } elseif (
                                    str_contains(
                                        $host,
                                        'linkedin.'
                                    )
                                ) {
                                    $platform =
                                        'linkedin';

                                } elseif (
                                    str_contains(
                                        $host,
                                        'youtube.'
                                    )
                                    || str_contains(
                                        $host,
                                        'youtu.be'
                                    )
                                ) {
                                    $platform =
                                        'youtube';

                                } elseif (
                                    str_contains(
                                        $host,
                                        'tiktok.'
                                    )
                                ) {
                                    $platform =
                                        'tiktok';

                                } elseif (
                                    str_contains(
                                        $host,
                                        'whatsapp.'
                                    )
                                    || str_contains(
                                        $host,
                                        'wa.me'
                                    )
                                ) {
                                    $platform =
                                        'whatsapp';

                                } elseif (
                                    $host === 'x.com'
                                    || str_contains(
                                        $host,
                                        'twitter.'
                                    )
                                ) {
                                    $platform =
                                        'x';
                                }
                            }


                            if (
                                $url === ''
                            ) {
                                continue;
                            }


                            $savedFooterSocials[] = [
                                'platform' =>
                                    $platform,

                                'url' =>
                                    $url,
                            ];
                        }


                        /*
                         * Laravel validation errors should still
                         * repopulate the submitted form.
                         */
                        $footerSocials =
                            old(
                                'footer_socials',
                                $savedFooterSocials
                            );

                        if (
                            !is_array(
                                $footerSocials
                            )
                        ) {
                            $footerSocials = [];
                        }
                    @endphp


                                    {{-- ESUBIZ_FOOTER_INTERMEDIATE_SOCIAL_CARD_REMOVED_V1 --}}


{{-- =================================================
                     SECTION 2 — CONTACT & SOCIAL LINKS
                ================================================== --}}

                <div
                    class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"
                    data-footer-config-section="contact"
                    style="width:100%; grid-column:1 / -1;"
                >

                    <div
                        class="flex flex-wrap items-start justify-between gap-4"
                    >

                        <div>
                            <div
                                class="text-xs font-black uppercase tracking-[.14em] text-blue-600"
                            >
                                Footer Section 2
                            </div>

                            <h3
                                class="mt-2 text-xl font-black text-slate-900"
                            >
                                Contact & Social Links
                            </h3>

                            <p
                                class="mt-1 text-sm text-slate-500"
                            >
                                Contact information and social media links.
                            </p>
                        </div>


                        <div
                            class="inline-flex overflow-hidden rounded-xl border border-slate-200 text-[10px] font-black uppercase"
                            data-inline-toggle="footer_contact_enabled"
                        >

                            <label class="cursor-pointer">

                                <input
                                    type="radio"
                                    name="footer_contact_enabled"
                                    value="0"
                                    class="peer sr-only"
                                    {{
                                        old(
                                            'footer_contact_enabled',
                                            $theme[
                                                'footer_contact_enabled'
                                            ] ?? '1'
                                        ) === '0'
                                            ? 'checked'
                                            : ''
                                    }}
                                >

                                <span
                                    class="block px-4 py-2 text-slate-400 peer-checked:bg-red-600 peer-checked:text-white"
                                >
                                    Hide
                                </span>

                            </label>


                            <label
                                class="cursor-pointer border-l border-slate-200"
                            >

                                <input
                                    type="radio"
                                    name="footer_contact_enabled"
                                    value="1"
                                    class="peer sr-only"
                                    {{
                                        old(
                                            'footer_contact_enabled',
                                            $theme[
                                                'footer_contact_enabled'
                                            ] ?? '1'
                                        ) === '1'
                                            ? 'checked'
                                            : ''
                                    }}
                                >

                                <span
                                    class="block px-4 py-2 text-slate-400 peer-checked:bg-emerald-600 peer-checked:text-white"
                                >
                                    Show
                                </span>

                            </label>

                        </div>

                    </div>


                    @php
                        $footerContacts = [
                            [
                                'label' => 'Phone',
                                'enabled' => 'footer_phone_enabled',
                                'field' => 'footer_phone',
                                'icon_field' => 'footer_phone_icon',
                                'default_icon' => 'phone',
                            ],
                            [
                                'label' => 'Email',
                                'enabled' => 'footer_email_enabled',
                                'field' => 'footer_email',
                                'icon_field' => 'footer_email_icon',
                                'default_icon' => 'mail',
                            ],
                            [
                                'label' => 'Address',
                                'enabled' => 'footer_address_enabled',
                                'field' => 'footer_address',
                                'icon_field' => 'footer_address_icon',
                                'default_icon' => 'location',
                            ],
                        ];
                    @endphp


                    <div
                        class="mt-6 grid gap-5 lg:grid-cols-3"
                    >

                        @foreach(
                            $footerContacts
                            as $contact
                        )

                            <div
                                class="rounded-2xl bg-slate-50 p-5"
                            >

                                <div
                                    class="flex items-center justify-between gap-3"
                                >

                                    <div
                                        class="font-black text-slate-900"
                                    >
                                        {{
                                            $contact[
                                                'label'
                                            ]
                                        }}
                                    </div>


                                    <div
                                        class="inline-flex overflow-hidden rounded-lg border border-slate-200 text-[10px] font-black uppercase"
                                    >

                                        <label class="cursor-pointer">

                                            <input
                                                type="radio"
                                                name="{{
                                                    $contact[
                                                        'enabled'
                                                    ]
                                                }}"
                                                value="0"
                                                class="peer sr-only"
                                                {{
                                                    old(
                                                        $contact[
                                                            'enabled'
                                                        ],
                                                        $theme[
                                                            $contact[
                                                                'enabled'
                                                            ]
                                                        ] ?? '1'
                                                    ) === '0'
                                                        ? 'checked'
                                                        : ''
                                                }}
                                            >

                                            <span
                                                class="block px-2.5 py-1.5 text-slate-400 peer-checked:bg-red-600 peer-checked:text-white"
                                            >
                                                Hide
                                            </span>

                                        </label>


                                        <label
                                            class="cursor-pointer border-l border-slate-200"
                                        >

                                            <input
                                                type="radio"
                                                name="{{
                                                    $contact[
                                                        'enabled'
                                                    ]
                                                }}"
                                                value="1"
                                                class="peer sr-only"
                                                {{
                                                    old(
                                                        $contact[
                                                            'enabled'
                                                        ],
                                                        $theme[
                                                            $contact[
                                                                'enabled'
                                                            ]
                                                        ] ?? '1'
                                                    ) === '1'
                                                        ? 'checked'
                                                        : ''
                                                }}
                                            >

                                            <span
                                                class="block px-2.5 py-1.5 text-slate-400 peer-checked:bg-emerald-600 peer-checked:text-white"
                                            >
                                                Show
                                            </span>

                                        </label>

                                    </div>

                                </div>


                                <input
                                    type="text"
                                    name="{{
                                        $contact[
                                            'field'
                                        ]
                                    }}"
                                    value="{{
                                        old(
                                            $contact[
                                                'field'
                                            ],
                                            $theme[
                                                $contact[
                                                    'field'
                                                ]
                                            ] ?? ''
                                        )
                                    }}"
                                    class="mt-4 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                                >


                                <select
                                    name="{{
                                        $contact[
                                            'icon_field'
                                        ]
                                    }}"
                                    class="mt-3 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                                >

                                    @foreach(
                                        [
                                            'phone' => 'Phone',
                                            'mail' => 'Email',
                                            'location' => 'Location',
                                            'mobile' => 'Mobile',
                                            'chat' => 'Chat',
                                        ]
                                        as $value => $label
                                    )

                                        <option
                                            value="{{ $value }}"
                                            {{
                                                old(
                                                    $contact[
                                                        'icon_field'
                                                    ],
                                                    $theme[
                                                        $contact[
                                                            'icon_field'
                                                        ]
                                                    ]
                                                    ?? $contact[
                                                        'default_icon'
                                                    ]
                                                ) === $value
                                                    ? 'selected'
                                                    : ''
                                            }}
                                        >
                                            {{ $label }}
                                        </option>

                                    @endforeach

                                </select>

                            </div>

                        @endforeach

                    </div>


                    @php
                        $savedFooterSocials =
                            json_decode(
                                $theme[
                                    'footer_socials_json'
                                ] ?? '[]',
                                true
                            );

                        if (
                            !is_array(
                                $savedFooterSocials
                            )
                        ) {
                            $savedFooterSocials = [];
                        }

                        $savedFooterSocials =
                            array_values(
                                array_filter(
                                    $savedFooterSocials,
                                    static function ($social) {
                                        return
                                            is_array($social)
                                            && !empty(
                                                $social[
                                                    'platform'
                                                ]
                                            )
                                            && !empty(
                                                $social[
                                                    'url'
                                                ]
                                            );
                                    }
                                )
                            );

                        $footerSocials =
                            old(
                                'footer_socials',
                                $savedFooterSocials
                            );

                        if (
                            !is_array(
                                $footerSocials
                            )
                        ) {
                            $footerSocials = [];
                        }
                    @endphp


                    <div
                        class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-5"
                        data-footer-social-editor
                    >

                        <div
                            class="flex flex-wrap items-center justify-between gap-4"
                        >

                            <div>
                                <div
                                    class="font-black text-slate-900"
                                >
                                    Social Media Links
                                </div>

                                <p
                                    class="mt-1 text-xs text-slate-500"
                                >
                                    Add only the social platforms you use.
                                </p>
                            </div>


                            <div
                                class="flex flex-wrap items-center gap-3"
                            >

                                <div
                                    class="inline-flex overflow-hidden rounded-xl border border-slate-200 text-[10px] font-black uppercase"
                                >

                                    <label class="cursor-pointer">

                                        <input
                                            type="radio"
                                            name="footer_socials_enabled"
                                            value="0"
                                            class="peer sr-only"
                                            {{
                                                old(
                                                    'footer_socials_enabled',
                                                    $theme[
                                                        'footer_socials_enabled'
                                                    ] ?? '1'
                                                ) === '0'
                                                    ? 'checked'
                                                    : ''
                                            }}
                                        >

                                        <span
                                            class="block px-3 py-2 text-slate-400 peer-checked:bg-red-600 peer-checked:text-white"
                                        >
                                            Hide
                                        </span>

                                    </label>


                                    <label
                                        class="cursor-pointer border-l border-slate-200"
                                    >

                                        <input
                                            type="radio"
                                            name="footer_socials_enabled"
                                            value="1"
                                            class="peer sr-only"
                                            {{
                                                old(
                                                    'footer_socials_enabled',
                                                    $theme[
                                                        'footer_socials_enabled'
                                                    ] ?? '1'
                                                ) === '1'
                                                    ? 'checked'
                                                    : ''
                                            }}
                                        >

                                        <span
                                            class="block px-3 py-2 text-slate-400 peer-checked:bg-emerald-600 peer-checked:text-white"
                                        >
                                            Show
                                        </span>

                                    </label>

                                </div>


                                <button
                                    type="button"
                                    class="rounded-xl bg-blue-600 px-4 py-2.5 text-xs font-black text-white hover:bg-blue-700"
                                    data-add-footer-social
                                >
                                    + Add Link
                                </button>

                            </div>

                        </div>


                        <div
                            class="mt-5 grid gap-4"
                            data-footer-social-list
                        >

                            @foreach(
                                $footerSocials
                                as $index => $social
                            )

                                <div
                                    class="rounded-2xl border border-slate-200 bg-white p-4"
                                    data-footer-social-row
                                >

                                    <div
                                        class="grid items-center gap-3 md:grid-cols-[48px_220px_1fr_auto]"
                                    >

                                        <div
                                            class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-100 text-slate-700"
                                            data-social-icon
                                        ></div>


                                        <select
                                            name="footer_socials[{{ $index }}][platform]"
                                            class="rounded-xl border border-slate-200 px-4 py-3 text-sm"
                                            data-social-platform
                                        >

                                            <option value="">
                                                Select platform
                                            </option>

                                            @foreach(
                                                [
                                                    'facebook' => 'Facebook',
                                                    'instagram' => 'Instagram',
                                                    'x' => 'X / Twitter',
                                                    'linkedin' => 'LinkedIn',
                                                    'youtube' => 'YouTube',
                                                    'tiktok' => 'TikTok',
                                                    'whatsapp' => 'WhatsApp',
                                                ]
                                                as $value => $label
                                            )

                                                <option
                                                    value="{{ $value }}"
                                                    {{
                                                        (
                                                            $social[
                                                                'platform'
                                                            ] ?? ''
                                                        ) === $value
                                                            ? 'selected'
                                                            : ''
                                                    }}
                                                >
                                                    {{ $label }}
                                                </option>

                                            @endforeach

                                        </select>


                                        <input
                                            type="url"
                                            name="footer_socials[{{ $index }}][url]"
                                            value="{{
                                                $social[
                                                    'url'
                                                ] ?? ''
                                            }}"
                                            placeholder="https://..."
                                            class="rounded-xl border border-slate-200 px-4 py-3 text-sm"
                                            data-social-url
                                        >


                                        <button
                                            type="button"
                                            class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-xs font-black text-red-600"
                                            data-delete-footer-social
                                        >
                                            Delete
                                        </button>

                                    </div>

                                </div>

                            @endforeach

                        </div>


                        <template
                            data-footer-social-template
                        >
                            <div
                                class="rounded-2xl border border-slate-200 bg-white p-4"
                                data-footer-social-row
                            >

                                <div
                                    class="grid items-center gap-3 md:grid-cols-[48px_220px_1fr_auto]"
                                >

                                    <div
                                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-100 text-slate-700"
                                        data-social-icon
                                    ></div>

                                    <select
                                        class="rounded-xl border border-slate-200 px-4 py-3 text-sm"
                                        data-social-platform
                                    >
                                        <option value="">
                                            Select platform
                                        </option>
                                        <option value="facebook">
                                            Facebook
                                        </option>
                                        <option value="instagram">
                                            Instagram
                                        </option>
                                        <option value="x">
                                            X / Twitter
                                        </option>
                                        <option value="linkedin">
                                            LinkedIn
                                        </option>
                                        <option value="youtube">
                                            YouTube
                                        </option>
                                        <option value="tiktok">
                                            TikTok
                                        </option>
                                        <option value="whatsapp">
                                            WhatsApp
                                        </option>
                                    </select>

                                    <input
                                        type="url"
                                        placeholder="https://..."
                                        class="rounded-xl border border-slate-200 px-4 py-3 text-sm"
                                        data-social-url
                                    >

                                    <button
                                        type="button"
                                        class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-xs font-black text-red-600"
                                        data-delete-footer-social
                                    >
                                        Delete
                                    </button>

                                </div>
                            </div>
                        </template>

                    </div>

                </div>




                {{-- Footer Sections 3 + 4 --}}




                {{-- =============================================
                     FOOTER MENU ROW
                     Section 3 left / Section 4 right
                     ============================================= --}}

                <div
                    class="grid w-full grid-cols-1 gap-6 lg:grid-cols-2"
                    data-footer-menu-pair
                    style="grid-column:1 / -1;"
                >

<div
                    class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"
                    data-footer-config-section="menu-1"
                 >

                    <div
                        class="flex flex-wrap items-start justify-between gap-4"
                    >

                        <div>
                            <div
                                class="text-xs font-black uppercase tracking-[.14em] text-blue-600"
                            >
                                Footer Section 3
                            </div>

                            <h3
                                class="mt-2 text-xl font-black text-slate-900"
                            >
                                Quick Links
                            </h3>

                            <p
                                class="mt-1 text-sm text-slate-500"
                            >
                                Inherits the Core menu position:
                            Footer Quick Links.
                            </p>
                        </div>


                        <div
                            class="inline-flex overflow-hidden rounded-xl border border-slate-200 text-[10px] font-black uppercase"
                            data-inline-toggle="footer_menu_1_enabled"
                        >

                            <label class="cursor-pointer">
                                <input
                                    type="radio"
                                    name="footer_menu_1_enabled"
                                    value="0"
                                    class="peer sr-only"
                                    {{
                                        old(
                                            'footer_menu_1_enabled',
                                            $theme[
                                                'footer_menu_1_enabled'
                                            ] ?? '1'
                                        ) === '0'
                                            ? 'checked'
                                            : ''
                                    }}
                                >

                                <span
                                    class="block px-4 py-2 text-slate-400 peer-checked:bg-red-600 peer-checked:text-white"
                                >
                                    Hide
                                </span>
                            </label>

                            <label
                                class="cursor-pointer border-l border-slate-200"
                            >
                                <input
                                    type="radio"
                                    name="footer_menu_1_enabled"
                                    value="1"
                                    class="peer sr-only"
                                    {{
                                        old(
                                            'footer_menu_1_enabled',
                                            $theme[
                                                'footer_menu_1_enabled'
                                            ] ?? '1'
                                        ) === '1'
                                            ? 'checked'
                                            : ''
                                    }}
                                >

                                <span
                                    class="block px-4 py-2 text-slate-400 peer-checked:bg-emerald-600 peer-checked:text-white"
                                >
                                    Show
                                </span>
                            </label>

                        </div>

                    </div>




                </div>



                {{-- =================================================
                     SECTION 4 — RESOURCES
                ================================================== --}}

                <div
                    class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"
                    data-footer-config-section="menu-2"
                 >

                    <div
                        class="flex flex-wrap items-start justify-between gap-4"
                    >

                        <div>
                            <div
                                class="text-xs font-black uppercase tracking-[.14em] text-blue-600"
                            >
                                Footer Section 4
                            </div>

                            <h3
                                class="mt-2 text-xl font-black text-slate-900"
                            >
                                Resources
                            </h3>

                            <p
                                class="mt-1 text-sm text-slate-500"
                            >
                                Inherits the Core menu position:
                            Footer Resources.
                            </p>
                        </div>


                        <div
                            class="inline-flex overflow-hidden rounded-xl border border-slate-200 text-[10px] font-black uppercase"
                            data-inline-toggle="footer_menu_2_enabled"
                        >

                            <label class="cursor-pointer">
                                <input
                                    type="radio"
                                    name="footer_menu_2_enabled"
                                    value="0"
                                    class="peer sr-only"
                                    {{
                                        old(
                                            'footer_menu_2_enabled',
                                            $theme[
                                                'footer_menu_2_enabled'
                                            ] ?? '1'
                                        ) === '0'
                                            ? 'checked'
                                            : ''
                                    }}
                                >

                                <span
                                    class="block px-4 py-2 text-slate-400 peer-checked:bg-red-600 peer-checked:text-white"
                                >
                                    Hide
                                </span>
                            </label>

                            <label
                                class="cursor-pointer border-l border-slate-200"
                            >
                                <input
                                    type="radio"
                                    name="footer_menu_2_enabled"
                                    value="1"
                                    class="peer sr-only"
                                    {{
                                        old(
                                            'footer_menu_2_enabled',
                                            $theme[
                                                'footer_menu_2_enabled'
                                            ] ?? '1'
                                        ) === '1'
                                            ? 'checked'
                                            : ''
                                    }}
                                >

                                <span
                                    class="block px-4 py-2 text-slate-400 peer-checked:bg-emerald-600 peer-checked:text-white"
                                >
                                    Show
                                </span>
                            </label>

                        </div>

                    </div>




                </div>

                </div>







            </div>


            {{-- FOOTER BOTTOM --}}
            <div
                class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"
            >
                <div
                    class="text-xs font-black uppercase tracking-[.14em] text-blue-600"
                >
                    Footer Bottom
                </div>

        {{-- =====================================================
             FLOATING WEBSITE TOOLS
             ===================================================== --}}

        <div class="mt-6 grid gap-6" data-footer-floating-tools>

            {{-- WhatsApp --}}
            <div
                class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"
                data-floating-config="whatsapp"
            >
                <div
                    class="flex flex-wrap items-start justify-between gap-4"
                >
                    <div>
                        <div
                            class="text-xs font-black uppercase tracking-[.14em] text-blue-600"
                        >
                            Floating Tool
                        </div>

                        <h3
                            class="mt-2 text-xl font-black text-slate-900"
                        >
                            WhatsApp
                        </h3>
                    </div>

                    <div
                        class="inline-flex overflow-hidden rounded-xl border border-slate-200 text-[10px] font-black uppercase"
                    >
                        <label class="cursor-pointer">
                            <input
                                type="radio"
                                name="whatsapp_enabled"
                                value="0"
                                class="peer sr-only"
                                {{
                                    old(
                                        'whatsapp_enabled',
                                        $theme[
                                            'whatsapp_enabled'
                                        ] ?? '0'
                                    ) === '0'
                                        ? 'checked'
                                        : ''
                                }}
                            >
                            <span
                                class="block px-4 py-2 text-slate-400 peer-checked:bg-red-600 peer-checked:text-white"
                            >
                                Hide
                            </span>
                        </label>

                        <label
                            class="cursor-pointer border-l border-slate-200"
                        >
                            <input
                                type="radio"
                                name="whatsapp_enabled"
                                value="1"
                                class="peer sr-only"
                                {{
                                    old(
                                        'whatsapp_enabled',
                                        $theme[
                                            'whatsapp_enabled'
                                        ] ?? '0'
                                    ) === '1'
                                        ? 'checked'
                                        : ''
                                }}
                            >
                            <span
                                class="block px-4 py-2 text-slate-400 peer-checked:bg-emerald-600 peer-checked:text-white"
                            >
                                Show
                            </span>
                        </label>
                    </div>
                </div>

                <div class="mt-6 grid gap-5 md:grid-cols-3">
                    <div>
                        <label
                            class="text-xs font-black uppercase tracking-wide text-slate-500"
                        >
                            WhatsApp Number
                        </label>

                        <input
                            type="text"
                            name="whatsapp_number"
                            value="{{
                                old(
                                    'whatsapp_number',
                                    $theme[
                                        'whatsapp_number'
                                    ] ?? ''
                                )
                            }}"
                            class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"
                        >
                    </div>

                    <div>
                        <label
                            class="text-xs font-black uppercase tracking-wide text-slate-500"
                        >
                            Position
                        </label>

                        <select
                            name="whatsapp_position"
                            class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"
                        >
                            <option
                                value="left"
                                {{
                                    old(
                                        'whatsapp_position',
                                        $theme[
                                            'whatsapp_position'
                                        ] ?? 'right'
                                    ) === 'left'
                                        ? 'selected'
                                        : ''
                                }}
                            >
                                Bottom Left
                            </option>

                            <option
                                value="right"
                                {{
                                    old(
                                        'whatsapp_position',
                                        $theme[
                                            'whatsapp_position'
                                        ] ?? 'right'
                                    ) === 'right'
                                        ? 'selected'
                                        : ''
                                }}
                            >
                                Bottom Right
                            </option>
                        </select>
                    </div>

                    <div>
                        <label
                            class="text-xs font-black uppercase tracking-wide text-slate-500"
                        >
                            Devices
                        </label>


                        @php
                            $whatsapp_devicesCurrent =
                                old(
                                    'whatsapp_devices',
                                    $theme[
                                        'whatsapp_devices'
                                    ] ?? 'desktop,tablet,mobile'
                                );

                            $whatsapp_devicesDevices =
                                array_values(
                                    array_filter(
                                        array_map(
                                            'trim',
                                            explode(
                                                ',',
                                                $whatsapp_devicesCurrent
                                            )
                                        )
                                    )
                                );
                        @endphp

                        <div
                            class="flex flex-wrap gap-3"
                            data-device-checkbox-group="whatsapp_devices"
                        >

                            @foreach(
                                [
                                    'desktop' => 'Desktop',
                                    'tablet' => 'Tablet',
                                    'mobile' => 'Mobile',
                                ]
                                as $deviceValue => $deviceLabel
                            )

                                <label
                                    class="inline-flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700"
                                >

                                    <input
                                        type="checkbox"
                                        value="{{ $deviceValue }}"
                                        data-device-checkbox="whatsapp_devices"
                                        class="h-4 w-4 rounded border-slate-300"
                                        {{
                                            in_array(
                                                $deviceValue,
                                                $whatsapp_devicesDevices,
                                                true
                                            )
                                                ? 'checked'
                                                : ''
                                        }}
                                    >

                                    <span>
                                        {{ $deviceLabel }}
                                    </span>

                                </label>

                            @endforeach

                        </div>

                        <input
                            type="hidden"
                            name="whatsapp_devices"
                            value="{{ $whatsapp_devicesCurrent }}"
                            data-device-hidden="whatsapp_devices"
                        >

                    </div>
                </div>
            </div>


            {{-- Live Chat --}}
            <div
                class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"
                data-floating-config="live-chat"
            >
                <div
                    class="flex flex-wrap items-start justify-between gap-4"
                >
                    <div>
                        <div
                            class="text-xs font-black uppercase tracking-[.14em] text-blue-600"
                        >
                            Floating Tool
                        </div>

                        <h3
                            class="mt-2 text-xl font-black text-slate-900"
                        >
                            Live Chat
                        </h3>
                    </div>

                    <div
                        class="inline-flex overflow-hidden rounded-xl border border-slate-200 text-[10px] font-black uppercase"
                    >
                        <label class="cursor-pointer">
                            <input
                                type="radio"
                                name="live_chat_enabled"
                                value="0"
                                class="peer sr-only"
                                {{
                                    old(
                                        'live_chat_enabled',
                                        $theme[
                                            'live_chat_enabled'
                                        ] ?? '0'
                                    ) === '0'
                                        ? 'checked'
                                        : ''
                                }}
                            >
                            <span
                                class="block px-4 py-2 text-slate-400 peer-checked:bg-red-600 peer-checked:text-white"
                            >
                                Hide
                            </span>
                        </label>

                        <label
                            class="cursor-pointer border-l border-slate-200"
                        >
                            <input
                                type="radio"
                                name="live_chat_enabled"
                                value="1"
                                class="peer sr-only"
                                {{
                                    old(
                                        'live_chat_enabled',
                                        $theme[
                                            'live_chat_enabled'
                                        ] ?? '0'
                                    ) === '1'
                                        ? 'checked'
                                        : ''
                                }}
                            >
                            <span
                                class="block px-4 py-2 text-slate-400 peer-checked:bg-emerald-600 peer-checked:text-white"
                            >
                                Show
                            </span>
                        </label>
                    </div>
                </div>

                <div class="mt-6 grid gap-5 md:grid-cols-2">
                    <div>
                        <label
                            class="text-xs font-black uppercase tracking-wide text-slate-500"
                        >
                            Position
                        </label>

                        <select
                            name="live_chat_position"
                            class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"
                        >
                            <option value="left"
                                {{
                                    old(
                                        'live_chat_position',
                                        $theme[
                                            'live_chat_position'
                                        ] ?? 'right'
                                    ) === 'left'
                                        ? 'selected'
                                        : ''
                                }}
                            >
                                Bottom Left
                            </option>

                            <option value="right"
                                {{
                                    old(
                                        'live_chat_position',
                                        $theme[
                                            'live_chat_position'
                                        ] ?? 'right'
                                    ) === 'right'
                                        ? 'selected'
                                        : ''
                                }}
                            >
                                Bottom Right
                            </option>
                        </select>
                    </div>

                    <div>
                        <label
                            class="text-xs font-black uppercase tracking-wide text-slate-500"
                        >
                            Devices
                        </label>


                        @php
                            $live_chat_devicesCurrent =
                                old(
                                    'live_chat_devices',
                                    $theme[
                                        'live_chat_devices'
                                    ] ?? 'desktop,tablet,mobile'
                                );

                            $live_chat_devicesDevices =
                                array_values(
                                    array_filter(
                                        array_map(
                                            'trim',
                                            explode(
                                                ',',
                                                $live_chat_devicesCurrent
                                            )
                                        )
                                    )
                                );
                        @endphp

                        <div
                            class="flex flex-wrap gap-3"
                            data-device-checkbox-group="live_chat_devices"
                        >

                            @foreach(
                                [
                                    'desktop' => 'Desktop',
                                    'tablet' => 'Tablet',
                                    'mobile' => 'Mobile',
                                ]
                                as $deviceValue => $deviceLabel
                            )

                                <label
                                    class="inline-flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700"
                                >

                                    <input
                                        type="checkbox"
                                        value="{{ $deviceValue }}"
                                        data-device-checkbox="live_chat_devices"
                                        class="h-4 w-4 rounded border-slate-300"
                                        {{
                                            in_array(
                                                $deviceValue,
                                                $live_chat_devicesDevices,
                                                true
                                            )
                                                ? 'checked'
                                                : ''
                                        }}
                                    >

                                    <span>
                                        {{ $deviceLabel }}
                                    </span>

                                </label>

                            @endforeach

                        </div>

                        <input
                            type="hidden"
                            name="live_chat_devices"
                            value="{{ $live_chat_devicesCurrent }}"
                            data-device-hidden="live_chat_devices"
                        >

                    </div>
                </div>
            </div>


            {{-- Back to Top --}}
            <div
                class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm"
                data-floating-config="back-to-top"
            >
                <div
                    class="flex flex-wrap items-start justify-between gap-4"
                >
                    <div>
                        <div
                            class="text-xs font-black uppercase tracking-[.14em] text-blue-600"
                        >
                            Floating Tool
                        </div>

                        <h3
                            class="mt-2 text-xl font-black text-slate-900"
                        >
                            Back to Top
                        </h3>
                    </div>

                    <div
                        class="inline-flex overflow-hidden rounded-xl border border-slate-200 text-[10px] font-black uppercase"
                    >
                        <label class="cursor-pointer">
                            <input
                                type="radio"
                                name="back_to_top_enabled"
                                value="0"
                                class="peer sr-only"
                                {{
                                    old(
                                        'back_to_top_enabled',
                                        $theme[
                                            'back_to_top_enabled'
                                        ] ?? '1'
                                    ) === '0'
                                        ? 'checked'
                                        : ''
                                }}
                            >
                            <span
                                class="block px-4 py-2 text-slate-400 peer-checked:bg-red-600 peer-checked:text-white"
                            >
                                Hide
                            </span>
                        </label>

                        <label
                            class="cursor-pointer border-l border-slate-200"
                        >
                            <input
                                type="radio"
                                name="back_to_top_enabled"
                                value="1"
                                class="peer sr-only"
                                {{
                                    old(
                                        'back_to_top_enabled',
                                        $theme[
                                            'back_to_top_enabled'
                                        ] ?? '1'
                                    ) === '1'
                                        ? 'checked'
                                        : ''
                                }}
                            >
                            <span
                                class="block px-4 py-2 text-slate-400 peer-checked:bg-emerald-600 peer-checked:text-white"
                            >
                                Show
                            </span>
                        </label>
                    </div>
                </div>

                <div class="mt-6 grid gap-5 md:grid-cols-2">
                    <div>
                        <label
                            class="text-xs font-black uppercase tracking-wide text-slate-500"
                        >
                            Position
                        </label>

                        <select
                            name="back_to_top_position"
                            class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"
                        >
                            <option value="left"
                                {{
                                    old(
                                        'back_to_top_position',
                                        $theme[
                                            'back_to_top_position'
                                        ] ?? 'right'
                                    ) === 'left'
                                        ? 'selected'
                                        : ''
                                }}
                            >
                                Bottom Left
                            </option>

                            <option value="right"
                                {{
                                    old(
                                        'back_to_top_position',
                                        $theme[
                                            'back_to_top_position'
                                        ] ?? 'right'
                                    ) === 'right'
                                        ? 'selected'
                                        : ''
                                }}
                            >
                                Bottom Right
                            </option>
                        </select>
                    </div>

                    <div>
                        <label
                            class="text-xs font-black uppercase tracking-wide text-slate-500"
                        >
                            Devices
                        </label>


                        @php
                            $back_to_top_devicesCurrent =
                                old(
                                    'back_to_top_devices',
                                    $theme[
                                        'back_to_top_devices'
                                    ] ?? 'desktop,tablet,mobile'
                                );

                            $back_to_top_devicesDevices =
                                array_values(
                                    array_filter(
                                        array_map(
                                            'trim',
                                            explode(
                                                ',',
                                                $back_to_top_devicesCurrent
                                            )
                                        )
                                    )
                                );
                        @endphp

                        <div
                            class="flex flex-wrap gap-3"
                            data-device-checkbox-group="back_to_top_devices"
                        >

                            @foreach(
                                [
                                    'desktop' => 'Desktop',
                                    'tablet' => 'Tablet',
                                    'mobile' => 'Mobile',
                                ]
                                as $deviceValue => $deviceLabel
                            )

                                <label
                                    class="inline-flex cursor-pointer items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-bold text-slate-700"
                                >

                                    <input
                                        type="checkbox"
                                        value="{{ $deviceValue }}"
                                        data-device-checkbox="back_to_top_devices"
                                        class="h-4 w-4 rounded border-slate-300"
                                        {{
                                            in_array(
                                                $deviceValue,
                                                $back_to_top_devicesDevices,
                                                true
                                            )
                                                ? 'checked'
                                                : ''
                                        }}
                                    >

                                    <span>
                                        {{ $deviceLabel }}
                                    </span>

                                </label>

                            @endforeach

                        </div>

                        <input
                            type="hidden"
                            name="back_to_top_devices"
                            value="{{ $back_to_top_devicesCurrent }}"
                            data-device-hidden="back_to_top_devices"
                        >

                    </div>
                </div>
            </div>

        </div>


                <h3
                    class="mt-2 text-xl font-black text-slate-900"
                >
                    Copyright
                </h3>

                <input
                    type="text"
                    name="footer_copyright"
                    value="{{
                        old(
                            'footer_copyright',
                            $theme[
                                'footer_copyright'
                            ]
                            ?? '© {year} {website}. All rights reserved.'
                        )
                    }}"
                    class="mt-6 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm"
                >

                <p
                    class="mt-3 text-xs text-slate-400"
                >
                    Powered by Esubiz remains fixed on this theme.
                </p>
            </div>

        </section>

        </div>


<div class="sticky bottom-4 z-30 flex justify-end">

            <button
                type="submit"
                class="rounded-xl bg-blue-600 px-7 py-3.5 text-sm font-black text-white shadow-xl hover:bg-blue-700"
            >
                Save Theme Configuration
            </button>

        </div>

    </form>


    {{-- Templates for repeatable sections --}}

    <template id="featureTemplate">
        <div class="repeatable-feature rounded-2xl border border-slate-200 bg-slate-50 p-5">

            <div class="flex items-center justify-between">
                <div class="font-black text-slate-900">
                    Feature
                </div>

                <button
                    type="button"
                    class="remove-repeatable text-xs font-black text-red-600"
                >
                    Delete
                </button>
            </div>

            <div class="mt-4 grid gap-5 lg:grid-cols-[1fr_260px]">

                <div class="space-y-4">

                    <input
                        type="text"
                        data-field="title"
                        placeholder="Feature title"
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                    >

                    <textarea
                        data-field="text"
                        rows="4"
                        placeholder="Feature description"
                        class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                    ></textarea>

                </div>

                <div>
                    <div class="text-xs font-bold text-slate-500">
                        Feature Image
                    </div>

                    <div class="mt-1 text-[11px] text-slate-400">
                        Recommended: 800 × 520 px.
                    </div>

                    <input
                        type="file"
                        data-field="image"
                        accept="image/*"
                        class="mt-3 block w-full text-xs"
                    >
                </div>

            </div>

        </div>
    </template>


    <template id="testimonialTemplate">
        <div class="repeatable-testimonial rounded-2xl border border-slate-200 bg-slate-50 p-5">

            <div class="flex justify-end">
                <button
                    type="button"
                    class="remove-repeatable text-xs font-black text-red-600"
                >
                    Delete
                </button>
            </div>

            <div class="mt-3 grid gap-4">

                <div class="grid gap-4 md:grid-cols-2">

                    <input
                        type="text"
                        data-field="name"
                        placeholder="Customer name"
                        class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                    >

                    <input
                        type="text"
                        data-field="role"
                        placeholder="Role / company"
                        class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                    >

                </div>

                <textarea
                    data-field="text"
                    rows="4"
                    placeholder="Testimonial"
                    class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
                ></textarea>

                <div>
                    <div class="text-xs font-bold text-slate-500">
                        Customer Photo
                    </div>

                    <div class="mt-1 text-[11px] text-slate-400">
                        Recommended: 160 × 160 px square.
                    </div>

                    <input
                        type="file"
                        data-field="photo"
                        accept="image/*"
                        class="mt-3 block w-full text-xs"
                    >
                </div>

            </div>

        </div>
    </template>



    <template id="footerSocialTemplate">

        <div
            class="repeatable-footer-social grid gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 lg:grid-cols-[170px_1fr_1.4fr_130px_auto]"
        >

            <select
                data-field="icon"
                class="rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm"
            >
                <option value="facebook">Facebook</option>
                <option value="instagram">Instagram</option>
                <option value="x">X / Twitter</option>
                <option value="linkedin">LinkedIn</option>
                <option value="youtube">YouTube</option>
                <option value="tiktok">TikTok</option>
                <option value="whatsapp">WhatsApp</option>
                <option value="telegram">Telegram</option>
            </select>

            <input
                type="text"
                data-field="label"
                placeholder="Label"
                class="rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm"
            >

            <input
                type="text"
                data-field="url"
                placeholder="https://..."
                class="rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm"
            >

            <select
                data-field="enabled"
                class="rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm font-bold"
            >
                <option value="1">Show</option>
                <option value="0">Hide</option>
            </select>

            <button
                type="button"
                class="remove-repeatable rounded-xl px-3 py-2 text-xs font-black text-red-600"
            >
                Delete
            </button>

        </div>

    </template>


    <template id="footerLinkTemplate">
        <div class="repeatable-footer-link grid gap-3 rounded-2xl bg-slate-50 p-4 md:grid-cols-[1fr_1.4fr_auto]">

            <input
                type="text"
                data-field="label"
                placeholder="Link label"
                class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
            >

            <input
                type="text"
                data-field="url"
                placeholder="/page"
                class="rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm"
            >

            <button
                type="button"
                class="remove-repeatable rounded-xl px-4 py-2 text-xs font-black text-red-600"
            >
                Delete
            </button>

        </div>
    </template>


    <script>
        document.addEventListener(
            'DOMContentLoaded',
            function () {

                function attachDeleteButtons(root) {
                    root
                        .querySelectorAll(
                            '.remove-repeatable'
                        )
                        .forEach(function (button) {
                            button.onclick =
                                function () {
                                    button
                                        .closest(
                                            '.repeatable-feature, .repeatable-testimonial, .repeatable-footer-link, .repeatable-footer-social'
                                        )
                                        ?.remove();
                                };
                        });
                }


                function addRepeatable(
                    listId,
                    templateId,
                    prefix
                ) {
                    const list =
                        document.getElementById(
                            listId
                        );

                    const template =
                        document.getElementById(
                            templateId
                        );

                    const index =
                        Date.now().toString()
                        + Math.floor(
                            Math.random() * 1000
                        );

                    const fragment =
                        template.content.cloneNode(
                            true
                        );

                    fragment
                        .querySelectorAll(
                            '[data-field]'
                        )
                        .forEach(function (field) {

                            const key =
                                field.dataset.field;

                            field.name =
                                prefix
                                + '['
                                + index
                                + ']['
                                + key
                                + ']';
                        });

                    list.appendChild(fragment);

                    attachDeleteButtons(list);
                }


                document
                    .getElementById('addFeature')
                    ?.addEventListener(
                        'click',
                        function () {
                            addRepeatable(
                                'featureList',
                                'featureTemplate',
                                'features'
                            );
                        }
                    );


                document
                    .getElementById(
                        'addTestimonial'
                    )
                    ?.addEventListener(
                        'click',
                        function () {
                            addRepeatable(
                                'testimonialList',
                                'testimonialTemplate',
                                'testimonials'
                            );
                        }
                    );


                document
                    .getElementById(
                        'addFooterSocial'
                    )
                    ?.addEventListener(
                        'click',
                        function () {
                            addRepeatable(
                                'footerSocialList',
                                'footerSocialTemplate',
                                'footer_socials'
                            );
                        }
                    );


                document
                    .getElementById(
                        'addFooterLink'
                    )
                    ?.addEventListener(
                        'click',
                        function () {
                            addRepeatable(
                                'footerLinkList',
                                'footerLinkTemplate',
                                'footer_links'
                            );
                        }
                    );


                /*
                 * Instant Theme image previews.
                 *
                 * Works for:
                 * logo
                 * favicon
                 * hero
                 * about
                 * footer logo/background
                 * feature images
                 * testimonial photos
                 *
                 * The preview is local only until Save Theme is clicked.
                 */
                function installThemeImagePreviews(root) {
                    (root || document)
                        .querySelectorAll(
                            'input[type="file"][accept*="image"]'
                        )
                        .forEach(function (input) {

                            if (
                                input.dataset.themePreviewReady
                                === '1'
                            ) {
                                return;
                            }

                            input.dataset.themePreviewReady = '1';

                            input.addEventListener(
                                'change',
                                function () {

                                    const file =
                                        input.files
                                        && input.files[0];

                                    if (!file) {
                                        return;
                                    }

                                    if (
                                        !file.type
                                            .startsWith('image/')
                                    ) {
                                        return;
                                    }

                                    let container =
                                        input.parentElement;

                                    if (!container) {
                                        return;
                                    }

                                    let preview =
                                        container.querySelector(
                                            '[data-theme-upload-preview]'
                                        );

                                    if (!preview) {
                                        preview =
                                            document.createElement(
                                                'img'
                                            );

                                        preview.setAttribute(
                                            'data-theme-upload-preview',
                                            '1'
                                        );

                                        preview.alt =
                                            'Selected image preview';

                                        preview.className =
                                            input.name === 'favicon'
                                                ? 'mt-3 h-14 w-14 rounded-xl object-cover'
                                                : (
                                                    input.name
                                                        && input.name.includes(
                                                            '[photo]'
                                                        )
                                                        ? 'mt-3 h-16 w-16 rounded-full object-cover'
                                                        : 'mt-3 max-h-56 w-full rounded-xl object-contain bg-white'
                                                );

                                        input.insertAdjacentElement(
                                            'beforebegin',
                                            preview
                                        );
                                    }

                                    if (
                                        preview.dataset.objectUrl
                                    ) {
                                        URL.revokeObjectURL(
                                            preview.dataset.objectUrl
                                        );
                                    }

                                    const objectUrl =
                                        URL.createObjectURL(file);

                                    preview.dataset.objectUrl =
                                        objectUrl;

                                    preview.src =
                                        objectUrl;
                                }
                            );
                        });
                }

                installThemeImagePreviews(
                    document
                );

                /*
                 * New repeatable Feature/Testimonial rows are inserted
                 * dynamically, so observe the form and attach preview
                 * behaviour to newly-created image inputs too.
                 */
                const themePreviewObserver =
                    new MutationObserver(
                        function (mutations) {
                            mutations.forEach(
                                function (mutation) {
                                    mutation.addedNodes
                                        .forEach(
                                            function (node) {
                                                if (
                                                    node.nodeType
                                                    !== 1
                                                ) {
                                                    return;
                                                }

                                                installThemeImagePreviews(
                                                    node
                                                );
                                            }
                                        );
                                }
                            );
                        }
                    );

                themePreviewObserver.observe(
                    document,
                    {
                        childList: true,
                        subtree: true
                    }
                );

                function syncDeviceGroup(
                    groupName,
                    hiddenId
                ) {
                    const group =
                        document.querySelector(
                            `[data-device-group="${groupName}"]`
                        );

                    const hidden =
                        document.getElementById(
                            hiddenId
                        );

                    if (!group || !hidden) {
                        return;
                    }

                    const sync =
                        function () {
                            hidden.value =
                                Array.from(
                                    group.querySelectorAll(
                                        'input[type="checkbox"]:checked'
                                    )
                                )
                                .map(
                                    input =>
                                        input.value
                                )
                                .join(',');
                        };

                    group
                        .querySelectorAll(
                            'input[type="checkbox"]'
                        )
                        .forEach(
                            input =>
                                input.addEventListener(
                                    'change',
                                    sync
                                )
                        );

                    sync();
                }

                syncDeviceGroup(
                    'whatsapp',
                    'whatsappDevicesValue'
                );

                syncDeviceGroup(
                    'liveChat',
                    'liveChatDevicesValue'
                );

                syncDeviceGroup(
                    'backToTop',
                    'backToTopDevicesValue'
                );

                /*
                 * Theme photo detach controls.
                 *
                 * The Media Library file is NOT physically deleted.
                 * Only this theme reference is removed.
                 */
                document
                    .querySelectorAll(
                        '[data-theme-remove-photo]'
                    )
                    .forEach(
                        function (button) {

                            button.addEventListener(
                                'click',
                                function () {

                                    const inputName =
                                        button.getAttribute(
                                            'data-theme-remove-photo'
                                        );

                                    const hidden =
                                        document.querySelector(
                                            `[data-theme-remove-input="${inputName}"]`
                                        );

                                    if (hidden) {
                                        hidden.value = '1';
                                    }

                                    const container =
                                        button.parentElement;

                                    container
                                        ?.querySelectorAll(
                                            'img'
                                        )
                                        .forEach(
                                            function (image) {
                                                image.style.display =
                                                    'none';
                                            }
                                        );

                                    button.textContent =
                                        'Photo will be removed';

                                    button.disabled =
                                        true;
                                }
                            );
                        }
                    );


                document
                    .querySelectorAll(
                        '[data-repeatable-image-remove]'
                    )
                    .forEach(
                        function (button) {

                            button.addEventListener(
                                'click',
                                function () {

                                    const card =
                                        button.closest(
                                            '.repeatable-feature'
                                        );

                                    const hidden =
                                        card?.querySelector(
                                            '[data-feature-remove-input]'
                                        );

                                    if (hidden) {
                                        hidden.value = '1';
                                    }

                                    card
                                        ?.querySelectorAll(
                                            'img'
                                        )
                                        .forEach(
                                            image =>
                                                image.style.display =
                                                    'none'
                                        );

                                    button.textContent =
                                        'Photo will be removed';

                                    button.disabled =
                                        true;
                                }
                            );
                        }
                    );


                document
                    .querySelectorAll(
                        '[data-repeatable-photo-remove]'
                    )
                    .forEach(
                        function (button) {

                            button.addEventListener(
                                'click',
                                function () {

                                    const card =
                                        button.closest(
                                            '.repeatable-testimonial'
                                        );

                                    const hidden =
                                        card?.querySelector(
                                            '[data-testimonial-remove-input]'
                                        );

                                    if (hidden) {
                                        hidden.value = '1';
                                    }

                                    card
                                        ?.querySelectorAll(
                                            'img'
                                        )
                                        .forEach(
                                            image =>
                                                image.style.display =
                                                    'none'
                                        );

                                    button.textContent =
                                        'Photo will be removed';

                                    button.disabled =
                                        true;
                                }
                            );
                        }
                    );


                document.documentElement
                    .setAttribute(
                        'data-theme-delete-contract-installed',
                        '1'
                    );


                attachDeleteButtons(
                    document
                );

            }
        );
    </script>

</div>





<script data-device-checkbox-sync-installed>
document.addEventListener(
    'DOMContentLoaded',
    function () {

        document
            .querySelectorAll(
                '[data-device-checkbox-group]'
            )
            .forEach(
                function (group) {

                    const field =
                        group.getAttribute(
                            'data-device-checkbox-group'
                        );

                    const hidden =
                        document.querySelector(
                            '[data-device-hidden="'
                            + field
                            + '"]'
                        );

                    if (!hidden) {
                        return;
                    }


                    function sync() {

                        hidden.value =
                            Array.from(
                                group.querySelectorAll(
                                    '[data-device-checkbox="'
                                    + field
                                    + '"]:checked'
                                )
                            )
                            .map(
                                checkbox =>
                                    checkbox.value
                            )
                            .join(',');
                    }


                    group.addEventListener(
                        'change',
                        sync
                    );

                    sync();
                }
            );
    }
);
</script>





<script data-footer-social-repeatable-installed>
(function () {

    const icons = {
        facebook:
            '<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><path fill="currentColor" d="M13.5 22v-9h3l.45-3h-3.45V8.1c0-.87.24-1.46 1.5-1.46H17V3.96c-.35-.05-1.55-.15-2.94-.15-2.9 0-4.89 1.77-4.89 5.02V10H6v3h3.17v9h4.33z"/></svg>',

        instagram:
            '<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><path fill="currentColor" d="M7.5 2h9A5.5 5.5 0 0 1 22 7.5v9a5.5 5.5 0 0 1-5.5 5.5h-9A5.5 5.5 0 0 1 2 16.5v-9A5.5 5.5 0 0 1 7.5 2zm0 2A3.5 3.5 0 0 0 4 7.5v9A3.5 3.5 0 0 0 7.5 20h9a3.5 3.5 0 0 0 3.5-3.5v-9A3.5 3.5 0 0 0 16.5 4h-9zM12 7a5 5 0 1 1 0 10 5 5 0 0 1 0-10zm0 2a3 3 0 1 0 0 6 3 3 0 0 0 0-6zm5.25-3.25a1.25 1.25 0 1 1 0 2.5 1.25 1.25 0 0 1 0-2.5z"/></svg>',

        x:
            '<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><path fill="currentColor" d="M18.244 2H21.552l-7.227 8.26L22.827 22h-6.657l-5.214-6.817L4.99 22H1.68l7.73-8.835L1.254 2h6.826l4.713 6.231L18.244 2zm-1.161 17.93h1.833L7.084 3.966H5.117L17.083 19.93z"/></svg>',

        linkedin:
            '<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><path fill="currentColor" d="M4.98 3.5A2.5 2.5 0 1 1 4.98 8a2.5 2.5 0 0 1 0-4.5zM3 9h4v12H3V9zm6.5 0h3.84v1.64h.05c.54-1.02 1.84-2.1 3.79-2.1 4.05 0 4.8 2.67 4.8 6.14V21h-4v-5.6c0-1.34-.02-3.06-1.87-3.06-1.87 0-2.16 1.46-2.16 2.96V21h-4V9z"/></svg>',

        youtube:
            '<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><path fill="currentColor" d="M23.5 6.2a3.05 3.05 0 0 0-2.15-2.16C19.45 3.5 12 3.5 12 3.5s-7.45 0-9.35.54A3.05 3.05 0 0 0 .5 6.2 31.7 31.7 0 0 0 0 12a31.7 31.7 0 0 0 .5 5.8 3.05 3.05 0 0 0 2.15 2.16c1.9.54 9.35.54 9.35.54s7.45 0 9.35-.54a3.05 3.05 0 0 0 2.15-2.16A31.7 31.7 0 0 0 24 12a31.7 31.7 0 0 0-.5-5.8zM9.6 15.7V8.3L16 12l-6.4 3.7z"/></svg>',

        tiktok:
            '<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><path fill="currentColor" d="M16.6 2c.18 1.52.82 2.77 1.92 3.72A6.1 6.1 0 0 0 22 7.14v3.3a9.18 9.18 0 0 1-5.37-1.72v7.05A6.23 6.23 0 1 1 11.22 9.6v3.36a2.92 2.92 0 1 0 2.12 2.8V2h3.26z"/></svg>',

        whatsapp:
            '<svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><path fill="currentColor" d="M12.04 2a9.84 9.84 0 0 0-8.4 14.95L2 22l5.2-1.62A9.92 9.92 0 1 0 12.04 2zm0 17.9a8 8 0 0 1-4.08-1.12l-.3-.18-3.08.96 1-3-.2-.31A7.92 7.92 0 1 1 12.04 19.9zm4.6-5.94c-.25-.12-1.48-.73-1.71-.81-.23-.08-.4-.12-.57.12-.17.25-.65.81-.8.98-.15.17-.3.19-.55.07a6.6 6.6 0 0 1-1.95-1.2 7.3 7.3 0 0 1-1.35-1.68c-.14-.25-.01-.38.11-.5.11-.11.25-.29.37-.43.13-.14.17-.25.25-.42.08-.17.04-.31-.02-.43-.06-.13-.57-1.38-.78-1.89-.21-.5-.42-.43-.57-.44h-.49c-.17 0-.44.06-.67.31-.23.25-.88.86-.88 2.1s.9 2.44 1.03 2.61c.12.17 1.78 2.72 4.31 3.81.6.26 1.07.41 1.44.53.61.19 1.16.16 1.6.1.49-.07 1.48-.61 1.69-1.2.21-.59.21-1.1.15-1.2-.06-.11-.23-.17-.48-.29z"/></svg>'
    };


    function rowHtml(index) {
        return `
            <div
                class="rounded-2xl border border-slate-200 bg-white p-4"
                data-footer-social-row
            >
                <div
                    class="grid items-center gap-3 md:grid-cols-[48px_220px_1fr_auto]"
                >
                    <div
                        class="flex h-11 w-11 items-center justify-center rounded-xl bg-slate-100 text-slate-700"
                        data-social-icon
                    >
                        <span class="text-xs font-black text-slate-400">+</span>
                    </div>

                    <select
                        name="footer_socials[${index}][platform]"
                        class="rounded-xl border border-slate-200 px-4 py-3 text-sm"
                        data-social-platform
                    >
                        <option value="">Select platform</option>
                        <option value="facebook">Facebook</option>
                        <option value="instagram">Instagram</option>
                        <option value="x">X / Twitter</option>
                        <option value="linkedin">LinkedIn</option>
                        <option value="youtube">YouTube</option>
                        <option value="tiktok">TikTok</option>
                        <option value="whatsapp">WhatsApp</option>
                    </select>

                    <input
                        type="url"
                        name="footer_socials[${index}][url]"
                        placeholder="https://..."
                        class="rounded-xl border border-slate-200 px-4 py-3 text-sm"
                        data-social-url
                    >

                    <button
                        type="button"
                        class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-xs font-black text-red-600"
                        data-delete-footer-social
                    >
                        Delete
                    </button>
                </div>
            </div>
        `;
    }


    function boot() {

        const addButtons =
            document.querySelectorAll(
                '[data-add-footer-social]'
            );

        if (!addButtons.length) {
            return;
        }

        addButtons.forEach(
            function (button) {

                button.addEventListener(
                    'click',
                    function (event) {

                        event.preventDefault();
                        event.stopPropagation();

                        const section =
                            button.closest(
                                '[data-footer-config-section="contact"]'
                            )
                            || document;

                        const list =
                            section.querySelector(
                                '[data-footer-social-list]'
                            );

                        if (!list) {
                            console.error(
                                'Footer social list not found.'
                            );
                            return;
                        }

                        const index =
                            list.querySelectorAll(
                                '[data-footer-social-row]'
                            ).length;

                        list.insertAdjacentHTML(
                            'beforeend',
                            rowHtml(index)
                        );
                    }
                );
            }
        );


        document.addEventListener(
            'click',
            function (event) {

                const deleteButton =
                    event.target.closest(
                        '[data-delete-footer-social]'
                    );

                if (!deleteButton) {
                    return;
                }

                event.preventDefault();

                deleteButton
                    .closest(
                        '[data-footer-social-row]'
                    )
                    ?.remove();

                const section =
                    deleteButton.closest(
                        '[data-footer-config-section="contact"]'
                    );

                const list =
                    section?.querySelector(
                        '[data-footer-social-list]'
                    );

                if (!list) {
                    return;
                }

                list
                    .querySelectorAll(
                        '[data-footer-social-row]'
                    )
                    .forEach(
                        function (row, index) {

                            const platform =
                                row.querySelector(
                                    '[data-social-platform]'
                                );

                            const url =
                                row.querySelector(
                                    '[data-social-url]'
                                );

                            if (platform) {
                                platform.name =
                                    `footer_socials[${index}][platform]`;
                            }

                            if (url) {
                                url.name =
                                    `footer_socials[${index}][url]`;
                            }
                        }
                    );
            }
        );


        document.addEventListener(
            'change',
            function (event) {

                if (
                    !event.target.matches(
                        '[data-social-platform]'
                    )
                ) {
                    return;
                }

                const row =
                    event.target.closest(
                        '[data-footer-social-row]'
                    );

                const icon =
                    row?.querySelector(
                        '[data-social-icon]'
                    );

                if (!icon) {
                    return;
                }

                icon.innerHTML =
                    icons[
                        event.target.value
                    ]
                    || '<span class="text-xs font-black text-slate-400">+</span>';
            }
        );

    }


    if (document.readyState === 'loading') {
        document.addEventListener(
            'DOMContentLoaded',
            boot
        );
    } else {
        boot();
    }

})();
</script>

@endsection
