{{--
|--------------------------------------------------------------------------
| ESUBIZ-PAGE-BUILDER-BOUNDARY
|--------------------------------------------------------------------------
|
| Core Builder stores neutral widget JSON only.
| Themes control presentation.
| Modules control functionality.
|
| Never save theme Blade/CSS or module business logic into builder_json.
|
--}}

@extends('tenant.admin.layouts.app')

@section('title', $page ? 'Page Builder' : 'Create Page')

@section('content')
@php
    $existingBuilder = [];

    if ($page && !empty($page->settings)) {
        $decodedSettings = json_decode($page->settings, true);

        if (
            is_array($decodedSettings) &&
            isset($decodedSettings['basic_builder']) &&
            is_array($decodedSettings['basic_builder'])
        ) {
            $existingBuilder = $decodedSettings['basic_builder'];
        }
    }

    $builderJson = old(
        'builder_json',
        json_encode($existingBuilder, JSON_UNESCAPED_SLASHES)
    );
@endphp

<style>
    [data-builder-hidden] { display:none !important; }

    .eb-widget {
        transition:
            transform .18s ease,
            box-shadow .18s ease,
            border-color .18s ease;
    }

    .eb-widget:hover {
        border-color:#93c5fd;
        box-shadow:0 12px 35px rgba(15,23,42,.08);
    }

    .eb-widget.dragging {
        opacity:.45;
        transform:scale(.985);
    }

    .eb-canvas[data-device="tablet"] {
        max-width:768px;
        margin-left:auto;
        margin-right:auto;
    }

    .eb-canvas[data-device="mobile"] {
        max-width:390px;
        margin-left:auto;
        margin-right:auto;
    }

    .eb-drop-active {
        outline:2px dashed #2563eb;
        outline-offset:4px;
    }

    .eb-scroll-overlay {
        overflow-y:auto;
        overscroll-behavior:contain;
        -webkit-overflow-scrolling:touch;
    }

    .eb-scroll-panel {
        max-height:calc(100dvh - 2rem);
        overflow-y:auto;
        overscroll-behavior:contain;
        -webkit-overflow-scrolling:touch;
        scrollbar-gutter:stable;
    }

    .eb-scroll-panel-inner {
        min-height:min-content;
    }

    @media (max-width:640px) {
        .eb-scroll-overlay {
            padding:.75rem;
            align-items:flex-end;
        }

        .eb-scroll-panel {
            width:100%;
            max-height:calc(100dvh - 1.5rem);
            border-radius:1.5rem 1.5rem 0 0;
        }
    }

</style>

<div class="mx-auto max-w-[1600px] p-4 sm:p-6">

    <div class="mb-5 flex flex-wrap items-center justify-between gap-3">

        <div>
            <a
                href="{{ route('tenant.cms.pages.index', [
                    'subdomain' => $website->subdomain
                ]) }}"
                class="text-sm font-bold text-blue-600"
            >
                ← Pages
            </a>

            <h1 class="mt-2 text-2xl font-black text-slate-950">
                {{ $page ? 'Page Builder' : 'Create Page' }}
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                Build responsive pages using Esubiz Core widgets.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <button
                type="button"
                id="ebDesktop"
                class="rounded-xl bg-slate-950 px-3 py-2 text-xs font-black text-white"
            >
                Desktop
            </button>

            <button
                type="button"
                id="ebTablet"
                class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-black"
            >
                Tablet
            </button>

            <button
                type="button"
                id="ebMobile"
                class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-black"
            >
                Mobile
            </button>
        </div>

    </div>

    @if($errors->any())
        <div class="mb-5 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        id="pageBuilderForm"
        method="POST"
        action="{{ $page
            ? route('tenant.cms.pages.update', [
                'subdomain' => $website->subdomain,
                'page' => $page->id
            ])
            : route('tenant.cms.pages.store', [
                'subdomain' => $website->subdomain
            ]) }}"
    >
        @csrf

        @if($page)
            @method('PUT')
        @endif

        
        <input
            type="hidden"
            id="builderMediaUploadUrl"
            value="{{ route(
                'tenant.cms.media.image.upload',
                ['subdomain' => $website->subdomain]
            ) }}"
        >

<input
            type="hidden"
            name="builder_json"
            id="builderJson"
            value="{{ $builderJson }}"
        >

        <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_310px]">

            {{-- Canvas --}}
            <main class="min-w-0">
<div class="mb-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                    <div class="grid gap-4 md:grid-cols-2">

                        <div>
                            <label class="text-xs font-black uppercase tracking-wide text-slate-500">
                                Page Title
                            </label>
                            <input
                                type="text"
                                name="title"
                                id="pageTitle"
                                required
                                value="{{ old('title', $page->title ?? '') }}"
                                class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 font-bold outline-none focus:border-blue-500"
                            >
                        </div>

                        <div>
                            <label class="text-xs font-black uppercase tracking-wide text-slate-500">
                                URL Slug
                            </label>
                            <input
                                type="text"
                                name="slug"
                                id="pageSlug"
                                value="{{ old('slug', $page->slug ?? '') }}"
                                placeholder="about-us"
                                class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-blue-500"
                            >
                        </div>

                    </div>
                </div>

<div
                    class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm"
                >
                    <div class="flex flex-wrap gap-2">

                        <button
                            type="button"
                            id="addSectionButton"
                            class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-black text-white hover:bg-blue-700"
                        >
                            + Add Section
                        </button>

                        <button
                            type="button"
                            id="aiAssistButton"
                            class="rounded-xl bg-gradient-to-r from-blue-600 to-violet-600 px-4 py-2.5 text-sm font-black text-white"
                        >
                            ✦ Create with AI
                        </button>

                    </div>

                    <div class="text-xs font-bold text-slate-400">
                        Basic Page Builder
                    </div>

                </div>


                <div
                    id="builderCanvas"
                    data-device="desktop"
                    class="eb-canvas min-h-[620px] rounded-3xl border border-slate-200 bg-slate-100 p-4 shadow-inner transition-all"
                >
                    <div
                        id="emptyBuilder"
                        class="flex min-h-[560px] items-center justify-center rounded-2xl border-2 border-dashed border-slate-300 bg-white/60 p-8 text-center"
                    >
                        <div>
                            <div class="text-4xl">＋</div>
                            <h3 class="mt-3 text-lg font-black">Start building your page</h3>
                            <p class="mt-2 max-w-sm text-sm text-slate-500">
                                Add a section, choose its column layout, then add widgets inside the section.
                            </p>
                        </div>
                    </div>
                </div>

            </main>

            {{-- Page / Selected Widget Settings --}}
            <aside class="self-start rounded-3xl border border-slate-200 bg-white p-5 shadow-sm xl:sticky xl:top-4">

                <div id="pageSettingsPanel">
                    <h2 class="font-black">Page Settings</h2>

                    <div class="mt-5">
                        <label class="text-sm font-bold">Status</label>

                        <select
                            name="status"
                            class="mt-2 w-full rounded-xl border border-slate-300 px-3 py-3"
                        >
                            <option
                                value="published"
                                @selected(old('status', $page->status ?? 'published') === 'published')
                            >
                                Published
                            </option>

                            <option
                                value="draft"
                                @selected(old('status', $page->status ?? '') === 'draft')
                            >
                                Draft
                            </option>
                        </select>
                    </div>

                    <label class="mt-5 flex items-center gap-3 rounded-xl border border-slate-200 p-4">
                        <input
                            type="checkbox"
                            name="is_homepage"
                            value="1"
                            @checked(old('is_homepage', $page->is_homepage ?? false))
                        >

                        <span class="text-sm font-bold">
                            Set as website homepage
                        </span>
                    </label>

                    <div class="mt-5">
                        <label class="text-sm font-bold">
                            Fallback Content
                        </label>

                        <textarea
                            name="content"
                            rows="6"
                            class="mt-2 w-full rounded-xl border border-slate-300 px-3 py-3 text-sm"
                        >{{ old('content', $page->content ?? '') }}</textarea>

                        <p class="mt-2 text-xs leading-5 text-slate-400">
                            Used when a theme does not render builder widgets.
                        </p>
                    </div>
                </div>

                <div id="widgetSettingsPanel" data-builder-hidden>
                    <button
                        type="button"
                        id="closeWidgetSettings"
                        class="mb-4 text-xs font-black text-blue-600"
                    >
                        ← Page Settings
                    </button>

                    <h2 id="settingsTitle" class="font-black">
                        Widget Settings
                    </h2>

                    <div id="settingsFields" class="mt-5 space-y-4"></div>
                </div>

                <div class="mt-6 border-t border-slate-100 pt-5">
                    <button
                        type="submit"
                        class="w-full rounded-xl bg-blue-600 px-5 py-3 text-sm font-black text-white shadow-sm"
                    >
                        {{ $page ? 'Save Page' : 'Create Page' }}
                    </button>
                </div>

            </aside>

        </div>

    </form>

</div>



{{-- Add Section Layout --}}
<div
    id="sectionLayoutModal"
    data-builder-hidden
    class="eb-scroll-overlay fixed inset-0 z-[105] flex items-center justify-center bg-slate-950/60 p-4"
>
    <div
        class="eb-scroll-panel w-full max-w-2xl rounded-3xl bg-white shadow-2xl"
    >

        <div
            class="sticky top-0 z-10 flex items-start justify-between gap-4 border-b border-slate-200 bg-white/95 p-6 backdrop-blur"
        >
            <div>
                <div class="text-xs font-black uppercase tracking-[.16em] text-blue-600">
                    Basic Page Builder
                </div>

                <h2 class="mt-1 text-xl font-black text-slate-950">
                    Add Section
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Choose how this section should be divided.
                </p>
            </div>

            <button
                type="button"
                id="closeSectionLayout"
                class="grid h-10 w-10 place-items-center rounded-xl bg-slate-100 text-xl font-black"
            >
                ×
            </button>
        </div>


        <div class="eb-scroll-panel-inner p-6">

            <div
                id="sectionLayoutChoices"
                class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3"
            >

                <button type="button" data-layout="1" data-ratios="1"
                    class="section-layout-choice rounded-2xl border border-slate-200 p-4 text-left hover:border-blue-400 hover:bg-blue-50">
                    <div class="font-black">Full Width</div>
                    <div class="mt-1 text-xs text-slate-500">1 column</div>
                </button>

                <button type="button" data-layout="2" data-ratios="1,1"
                    class="section-layout-choice rounded-2xl border border-slate-200 p-4 text-left hover:border-blue-400 hover:bg-blue-50">
                    <div class="font-black">1 : 1</div>
                    <div class="mt-1 flex gap-1">
                        <span class="h-7 flex-1 rounded bg-blue-100"></span>
                        <span class="h-7 flex-1 rounded bg-blue-100"></span>
                    </div>
                </button>

                <button type="button" data-layout="2" data-ratios="1,2"
                    class="section-layout-choice rounded-2xl border border-slate-200 p-4 text-left hover:border-blue-400 hover:bg-blue-50">
                    <div class="font-black">1 : 2</div>
                    <div class="mt-1 flex gap-1">
                        <span class="h-7 basis-1/3 rounded bg-blue-100"></span>
                        <span class="h-7 basis-2/3 rounded bg-blue-100"></span>
                    </div>
                </button>

                <button type="button" data-layout="2" data-ratios="2,1"
                    class="section-layout-choice rounded-2xl border border-slate-200 p-4 text-left hover:border-blue-400 hover:bg-blue-50">
                    <div class="font-black">2 : 1</div>
                    <div class="mt-1 flex gap-1">
                        <span class="h-7 basis-2/3 rounded bg-blue-100"></span>
                        <span class="h-7 basis-1/3 rounded bg-blue-100"></span>
                    </div>
                </button>

                <button type="button" data-layout="3" data-ratios="1,1,1"
                    class="section-layout-choice rounded-2xl border border-slate-200 p-4 text-left hover:border-blue-400 hover:bg-blue-50">
                    <div class="font-black">1 : 1 : 1</div>
                    <div class="mt-1 flex gap-1">
                        <span class="h-7 flex-1 rounded bg-blue-100"></span>
                        <span class="h-7 flex-1 rounded bg-blue-100"></span>
                        <span class="h-7 flex-1 rounded bg-blue-100"></span>
                    </div>
                </button>

                <button type="button" data-layout="3" data-ratios="1,2,1"
                    class="section-layout-choice rounded-2xl border border-slate-200 p-4 text-left hover:border-blue-400 hover:bg-blue-50">
                    <div class="font-black">1 : 2 : 1</div>
                    <div class="mt-1 flex gap-1">
                        <span class="h-7 basis-1/4 rounded bg-blue-100"></span>
                        <span class="h-7 basis-1/2 rounded bg-blue-100"></span>
                        <span class="h-7 basis-1/4 rounded bg-blue-100"></span>
                    </div>
                </button>

            </div>


            <div class="mt-6 rounded-2xl border border-slate-200 bg-slate-50 p-5">

                <div class="font-black text-slate-950">
                    Custom Ratio
                </div>

                <p class="mt-1 text-xs leading-5 text-slate-500">
                    Enter ratios separated by colons. Examples:
                    1:3, 2:3:1, 1:1:2:1.
                </p>

                <div class="mt-4 flex gap-3">

                    <input
                        id="customSectionRatio"
                        type="text"
                        placeholder="1:3"
                        class="min-w-0 flex-1 rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm"
                    >

                    <button
                        type="button"
                        id="addCustomSection"
                        class="rounded-xl bg-slate-950 px-5 py-3 text-sm font-black text-white"
                    >
                        Add
                    </button>

                </div>

            </div>

        </div>

    </div>
</div>


{{-- Add Widget Drawer --}}
<div
    id="widgetDrawer"
    data-builder-hidden
    class="eb-scroll-overlay fixed inset-0 z-[110] flex justify-end bg-slate-950/50"
>
    <div
        id="widgetDrawerPanel"
        class="eb-scroll-panel relative h-full w-full max-w-md bg-white shadow-2xl max-sm:mt-auto max-sm:h-auto max-sm:max-h-[92dvh] max-sm:max-w-none max-sm:rounded-t-3xl"
    >

        <div
            class="sticky top-0 z-10 border-b border-slate-200 bg-white/95 p-5 backdrop-blur"
        >
            <div class="flex items-start justify-between gap-4">

                <div>
                    <div class="text-xs font-black uppercase tracking-[.16em] text-blue-600">
                        Basic Page Builder
                    </div>

                    <h2 class="mt-1 text-xl font-black text-slate-950">
                        Add Widget
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Choose a content widget to add to this page.
                    </p>
                </div>

                <button
                    type="button"
                    id="closeWidgetDrawer"
                    class="grid h-10 w-10 place-items-center rounded-xl bg-slate-100 text-xl font-black text-slate-700"
                    aria-label="Close widget library"
                >
                    ×
                </button>

            </div>

            <input
                id="widgetSearch"
                type="search"
                placeholder="Search widgets..."
                class="mt-4 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-blue-500"
            >
        </div>


        <div class="eb-scroll-panel-inner p-5">

            <div
                id="widgetLibrary"
                class="grid grid-cols-2 gap-3"
            ></div>


            <div class="mt-6 rounded-2xl border border-blue-100 bg-blue-50 p-4">

                <div class="text-xs font-black uppercase tracking-wide text-blue-900">
                    Module Widgets
                </div>

                <p class="mt-2 text-xs leading-5 text-blue-700">
                    Installed Esubiz modules can register compatible widgets here.
                    Ecommerce, Hotel and other Website Type modules will extend the
                    same builder without changing Core.
                </p>

            </div>

        </div>

    </div>
</div>


{{-- AI Assist Modal --}}
<div
    id="aiModal"
    data-builder-hidden
    class="eb-scroll-overlay fixed inset-0 z-[120] flex items-center justify-center bg-slate-950/60 p-4"
>
    <div
        class="eb-scroll-panel w-full max-w-2xl rounded-3xl bg-white shadow-2xl"
    >

        <div
            class="sticky top-0 z-10 flex items-start justify-between gap-4 border-b border-slate-200 bg-white/95 p-6 backdrop-blur"
        >

            <div>
                <div class="text-xs font-black uppercase tracking-[.16em] text-violet-600">
                    Esubiz AI
                </div>

                <h2 class="mt-1 text-xl font-black text-slate-950">
                    ✦ Create with AI
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    AI generates content only inside Esubiz-defined page structures.
                </p>
            </div>

            <button
                type="button"
                id="closeAiModal"
                class="grid h-10 w-10 place-items-center rounded-xl bg-slate-100 text-xl font-black"
                aria-label="Close AI creator"
            >
                ×
            </button>

        </div>


        <div class="eb-scroll-panel-inner p-6">

            <div>
                <label class="text-xs font-black uppercase tracking-wide text-slate-500">
                    What should AI create?
                </label>

                <div class="mt-3 grid gap-3 sm:grid-cols-3">

                    <button
                        type="button"
                        class="ai-scope rounded-2xl border-2 border-blue-600 bg-blue-50 p-4 text-left"
                        data-scope="page"
                    >
                        <div class="font-black text-slate-950">
                            Full Page
                        </div>

                        <p class="mt-1 text-xs leading-5 text-slate-500">
                            Generate a complete page using approved Esubiz widgets and sections.
                        </p>
                    </button>


                    <button
                        type="button"
                        class="ai-scope rounded-2xl border border-slate-200 p-4 text-left hover:border-blue-300"
                        data-scope="seo"
                    >
                        <div class="font-black text-slate-950">
                            SEO
                        </div>

                        <p class="mt-1 text-xs leading-5 text-slate-500">
                            Generate SEO title, description and recommended page keywords.
                        </p>
                    </button>


                    <button
                        type="button"
                        class="ai-scope rounded-2xl border border-slate-200 p-4 text-left hover:border-blue-300"
                        data-scope="section"
                    >
                        <div class="font-black text-slate-950">
                            Selected Section
                        </div>

                        <p class="mt-1 text-xs leading-5 text-slate-500">
                            Create or improve the currently selected builder section.
                        </p>
                    </button>

                </div>
            </div>


            <input
                type="hidden"
                id="aiScope"
                value="page"
            >


            <div class="mt-6">

                <label
                    for="aiPrompt"
                    class="text-xs font-black uppercase tracking-wide text-slate-500"
                >
                    Describe the page or content
                </label>

                <textarea
                    id="aiPrompt"
                    rows="6"
                    placeholder="Example: Create a professional About page for an interior design company in Abuja..."
                    class="mt-2 w-full rounded-2xl border border-slate-300 p-4 text-sm outline-none focus:border-blue-500"
                ></textarea>

            </div>


            <div class="mt-5 rounded-2xl border border-blue-100 bg-blue-50 p-4">

                <div class="text-sm font-black text-blue-950">
                    Structured AI Generation
                </div>

                <p class="mt-1 text-xs leading-5 text-blue-800">
                    Esubiz AI will not generate arbitrary page code. It will select
                    approved Core widgets, populate their fields and preserve the
                    active theme's layout and styling.
                </p>

            </div>


            <div class="mt-4 rounded-2xl border border-amber-200 bg-amber-50 p-4">

                <div class="text-xs font-black uppercase tracking-wide text-amber-800">
                    AI Credits
                </div>

                <p class="mt-1 text-xs leading-5 text-amber-800">
                    Generation will use this website owner's centrally controlled
                    Esubiz AI Credits. The central AI endpoint will be connected
                    separately.
                </p>

            </div>

        </div>


        <div
            class="sticky bottom-0 border-t border-slate-200 bg-white/95 p-5 backdrop-blur"
        >
            <button
                type="button"
                id="runAiAssist"
                disabled
                class="w-full cursor-not-allowed rounded-xl bg-slate-300 px-5 py-3 text-sm font-black text-slate-500"
            >
                AI Connection Pending
            </button>
        </div>

    </div>
</div>

<script>
(() => {
    const widgetDefinitions = {
        hero: { label:'Hero', icon:'▣' },
        heading: { label:'Heading', icon:'H' },
        text: { label:'Text', icon:'¶' },
        image: { label:'Image', icon:'▧' },
        gallery: { label:'Gallery', icon:'▦' },
        button: { label:'Button', icon:'●' },
        cta: { label:'CTA', icon:'→' },
        features: { label:'Features', icon:'✦' },
        cards: { label:'Cards', icon:'▤' },
        testimonials: { label:'Testimonials', icon:'❝' },
        faq: { label:'FAQ', icon:'?' },
        stats: { label:'Statistics', icon:'#' },
        columns: { label:'Columns', icon:'Ⅱ' },
        divider: { label:'Divider', icon:'―' },
        spacer: { label:'Spacer', icon:'↕' },
        video: { label:'Video', icon:'▶' },
        map: { label:'Map', icon:'⌖' },
        form: { label:'Form', icon:'□' },
        social: { label:'Social Links', icon:'◎' },
        panorama: { label:'360° Panorama', icon:'360°' },
        html: { label:'HTML', icon:'</>' }
    };

    /*
     * Builder document format:
     *
     * [
     *   {
     *     id: "...",
     *     type: "section",
     *     ratios: [1, 2],
     *     settings: {...},
     *     columns: [
     *       { id:"...", widgets:[...] },
     *       { id:"...", widgets:[...] }
     *     ]
     *   }
     * ]
     *
     * Themes render this structure.
     * Widgets never own theme layout.
     */

    let documentState = [];

    let selectedId = null;
    let selectedSectionId = null;
    let selectedColumnId = null;

    let draggedId = null;

    const canvas =
        document.getElementById(
            'builderCanvas'
        );

    const empty =
        document.getElementById(
            'emptyBuilder'
        );

    const library =
        document.getElementById(
            'widgetLibrary'
        );

    const jsonInput =
        document.getElementById(
            'builderJson'
        );

    const search =
        document.getElementById(
            'widgetSearch'
        );


    function uid() {
        return 'b_'
            + Date.now().toString(36)
            + '_'
            + Math.random()
                .toString(36)
                .slice(2, 8);
    }


    function createSection(ratios) {

        ratios =
            ratios
                .map(Number)
                .filter(
                    value =>
                        Number.isFinite(value)
                        && value > 0
                );

        if (!ratios.length) {
            ratios = [1];
        }

        return {
            id: uid(),
            collapsed: false,
            type: 'section',
            ratios: ratios,

            settings: {
                background: '#ffffff',
                paddingTop: 48,
                paddingBottom: 48,
                gap: 24
            },

            columns:
                ratios.map(
                    () => ({
                        id: uid(),
                        widgets: []
                    })
                )
        };
    }


    function normalizeDocument(input) {

        if (!Array.isArray(input)) {
            return [];
        }

        /*
         * Backward compatibility with Phase-1 flat widgets:
         * automatically place them inside a full-width section.
         */
        const isLegacy =
            input.some(
                item =>
                    item
                    && item.type !== 'section'
            );

        if (isLegacy) {

            const section =
                createSection([1]);

            section.columns[0].widgets =
                input.filter(Boolean);

            return [section];
        }


        return input.map(section => {

            if (
                !Array.isArray(
                    section.ratios
                )
                || !section.ratios.length
            ) {
                section.ratios = [1];
            }

            if (
                !Array.isArray(
                    section.columns
                )
            ) {
                section.columns = [];
            }

            while (
                section.columns.length
                < section.ratios.length
            ) {
                section.columns.push({
                    id: uid(),
                    widgets: []
                });
            }

            section.columns =
                section.columns
                    .slice(
                        0,
                        section.ratios.length
                    )
                    .map(column => ({
                        id:
                            column.id
                            || uid(),

                        widgets:
                            Array.isArray(
                                column.widgets
                            )
                                ? column.widgets
                                : []
                    }));

            return section;
        });
    }


    function findSection(sectionId) {
        return documentState.find(
            section =>
                section.id === sectionId
        );
    }


    function findColumn(
        sectionId,
        columnId
    ) {
        const section =
            findSection(sectionId);

        return section
            ?.columns
            ?.find(
                column =>
                    column.id === columnId
            )
            || null;
    }


    function findWidget(widgetId) {

        for (
            const section
            of documentState
        ) {
            for (
                const column
                of section.columns || []
            ) {
                const widget =
                    (column.widgets || [])
                        .find(
                            item =>
                                item.id === widgetId
                        );

                if (widget) {
                    return {
                        widget,
                        section,
                        column
                    };
                }
            }
        }

        return null;
    }

    /*
     * Create a clean, neutral Core widget document.
     *
     * IMPORTANT:
     * These are CONTENT defaults only.
     * Themes remain responsible for presentation.
     */
    function defaults(type) {

        const widget = {
            id: uid(),

            type: type,

            settings: {
                background: '#ffffff',
                textColor: '#0f172a',
                align: 'left',

                paddingTop: 24,
                paddingBottom: 24,

                hideDesktop: false,
                hideTablet: false,
                hideMobile: false
            },

            data: {}
        };


        const widgetData = {

            hero: {
                eyebrow: 'Welcome',
                heading: 'Build something remarkable',
                text:
                    'Tell visitors what makes your business different.',

                buttonText: 'Get Started',
                buttonUrl: '/contact',

                image: ''
            },


            heading: {
                text: 'Your heading',
                level: 'h2'
            },


            text: {
                text:
                    'Add your content here.'
            },


            image: {
                src: '',
                alt: '',
                caption: ''
            },


            gallery: {
                images: []
            },


            button: {
                text: 'Learn More',
                url: '#'
            },


            cta: {
                heading:
                    'Ready to get started?',

                text:
                    'Let’s build something great together.',

                buttonText:
                    'Contact Us',

                buttonUrl:
                    '/contact'
            },


            features: {
                heading:
                    'Why choose us',

                items: [
                    {
                        title: 'Quality',
                        text:
                            'Thoughtful service and attention to detail.',
                        image: ''
                    },

                    {
                        title: 'Experience',
                        text:
                            'Built around the needs of our customers.',
                        image: ''
                    },

                    {
                        title: 'Support',
                        text:
                            'Here when you need us.',
                        image: ''
                    }
                ]
            },


            cards: {
                heading: 'Explore',

                items: [
                    {
                        title: 'Card One',
                        text:
                            'Add information here.',
                        image: '',
                        buttonText: '',
                        buttonUrl: ''
                    }
                ]
            },


            testimonials: {
                heading:
                    'What our customers say',

                items: [
                    {
                        name: 'Customer Name',
                        role: 'Customer',
                        text:
                            'Add the customer testimonial here.',
                        image: ''
                    }
                ]
            },


            faq: {
                heading:
                    'Frequently Asked Questions',

                items: [
                    {
                        question:
                            'Add a question',
                        answer:
                            'Add the answer here.'
                    }
                ]
            },


            stats: {
                items: [
                    {
                        value: '100+',
                        label: 'Customers'
                    },

                    {
                        value: '5+',
                        label: 'Years Experience'
                    },

                    {
                        value: '98%',
                        label: 'Satisfaction'
                    }
                ]
            },


            columns: {
                columns: 2
            },


            divider: {
                thickness: 1
            },


            spacer: {
                height: 48
            },


            video: {
                url: ''
            },


            map: {
                embed: ''
            },


            form: {
                formId: ''
            },


            social: {
                links: []
            },


            panorama: {
                panoramaId: '',
                heading: '',
                height: 480,
                autoRotate: false
            },


            html: {
                html: ''
            }

        };


        widget.data =
            JSON.parse(
                JSON.stringify(
                    widgetData[type]
                    || {}
                )
            );


        return widget;
    }


    function escapeHtml(value) {
        return String(value ?? '')
            .replaceAll('&','&amp;')
            .replaceAll('<','&lt;')
            .replaceAll('>','&gt;')
            .replaceAll('"','&quot;');
    }

    function renderLibrary(filter='') {
        library.innerHTML = '';

        Object.entries(widgetDefinitions).forEach(([type, def]) => {
            if (
                filter &&
                !def.label.toLowerCase().includes(filter.toLowerCase())
            ) return;

            const button = document.createElement('button');
            button.type = 'button';
            button.className =
                'rounded-2xl border border-slate-200 bg-slate-50 p-3 text-left hover:border-blue-300 hover:bg-blue-50';

            button.innerHTML = `
                <div class="text-lg font-black">${escapeHtml(def.icon)}</div>
                <div class="mt-1 text-xs font-black">${escapeHtml(def.label)}</div>
            `;

            button.addEventListener('click', () => addWidget(type));
            library.appendChild(button);
        });
    }

    function addWidget(type) {

        const drawer =
            document.getElementById(
                'widgetDrawer'
            );

        /*
         * The drawer itself is the authoritative insertion target.
         * This prevents selected state from being lost while the
         * widget panel is open.
         */
        const sectionId =
            drawer?.dataset.sectionId
            || selectedSectionId;

        const columnId =
            drawer?.dataset.columnId
            || selectedColumnId;


        if (!sectionId || !columnId) {

            alert(
                'Click + Add Widget inside the section column where you want this widget.'
            );

            return;
        }


        const section =
            findSection(
                sectionId
            );

        if (!section) {

            alert(
                'Selected section could not be found.'
            );

            return;
        }


        const column =
            section.columns.find(
                item =>
                    item.id === columnId
            );


        if (!column) {

            alert(
                'Selected section column could not be found.'
            );

            return;
        }


        const widget =
            defaults(type);


        column.widgets.push(
            widget
        );


        selectedSectionId =
            section.id;

        selectedColumnId =
            column.id;

        selectedId =
            widget.id;


        sync();
        renderCanvas();


        drawer.setAttribute(
            'data-builder-hidden',
            ''
        );


        /*
         * Canvas was rebuilt above. Select after DOM replacement.
         */
        requestAnimationFrame(
            () => {

                selectWidget(
                    widget.id
                );

                const element =
                    document.querySelector(
                        `[data-widget-id="${widget.id}"].eb-widget`
                    );

                element?.scrollIntoView({
                    behavior:'smooth',
                    block:'center'
                });
            }
        );
    }


    function preview(widget) {
        const d = widget.data || {};

        switch(widget.type) {
            case 'hero':
                return `
                    <div class="rounded-2xl bg-slate-950 p-8 text-white">
                        <div class="text-xs font-black uppercase tracking-[.2em] text-blue-300">${escapeHtml(d.eyebrow)}</div>
                        <div class="mt-3 text-3xl font-black">${escapeHtml(d.heading)}</div>
                        <div class="mt-3 max-w-xl text-sm text-slate-300">${escapeHtml(d.text)}</div>
                    </div>`;

            case 'image':

                if (d.src) {
                    return `
                        <figure>
                            <img
                                src="${escapeHtml(d.src)}"
                                alt="${escapeHtml(d.alt || '')}"
                                class="max-h-[420px] w-full rounded-2xl object-cover"
                            >

                            ${
                                d.caption
                                    ? `
                                        <figcaption
                                            class="mt-2 text-xs text-slate-500"
                                        >
                                            ${escapeHtml(d.caption)}
                                        </figcaption>
                                    `
                                    : ''
                            }
                        </figure>
                    `;
                }

                return `
                    <div
                        class="flex min-h-[180px] items-center justify-center rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 text-center"
                    >
                        <div>
                            <div class="text-3xl">▧</div>

                            <div class="mt-2 text-sm font-black text-slate-700">
                                Image
                            </div>

                            <div class="mt-1 text-xs text-slate-400">
                                Click this widget to upload an image
                            </div>
                        </div>
                    </div>
                `;


            case 'heading':
                return `<div class="text-2xl font-black">${escapeHtml(d.text)}</div>`;

            case 'text':
                return `<p class="leading-7 text-slate-600">${escapeHtml(d.text)}</p>`;

            case 'features':
                return `<div><div class="text-xl font-black">${escapeHtml(d.heading)}</div><div class="mt-3 text-sm text-slate-500">Feature grid · ${(d.items || []).length} items</div></div>`;

            case 'testimonials':
                return `<div><div class="text-xl font-black">${escapeHtml(d.heading)}</div><div class="mt-3 text-sm text-slate-500">Testimonials slider · ${(d.items || []).length} testimonials</div></div>`;

            case 'faq':
                return `<div><div class="text-xl font-black">${escapeHtml(d.heading)}</div><div class="mt-3 text-sm text-slate-500">FAQ accordion · ${(d.items || []).length} questions</div></div>`;

            case 'panorama':
                return `
                    <div class="flex min-h-[180px] items-center justify-center rounded-2xl bg-slate-900 text-center text-white">
                        <div>
                            <div class="text-3xl font-black">360°</div>
                            <div class="mt-2 text-sm">Panorama Viewer</div>
                        </div>
                    </div>`;

            default:
                return `
                    <div class="rounded-xl bg-slate-50 p-5">
                        <div class="font-black">${escapeHtml(widgetDefinitions[widget.type]?.label || widget.type)}</div>
                        <div class="mt-1 text-xs text-slate-500">Select to configure this widget.</div>
                    </div>`;
        }
    }

    function renderCanvas() {

        canvas
            .querySelectorAll(
                '.eb-section'
            )
            .forEach(
                element =>
                    element.remove()
            );

        empty.style.display =
            documentState.length
                ? 'none'
                : 'flex';


        documentState.forEach(
            (
                section,
                sectionIndex
            ) => {

                const sectionEl =
                    document.createElement(
                        'section'
                    );

                sectionEl.className =
                    'eb-section mb-4 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm';

                sectionEl.dataset.id =
                    section.id;


                const ratios =
                    section.ratios
                    || [1];

                const template =
                    ratios
                        .map(
                            ratio =>
                                `${ratio}fr`
                        )
                        .join(' ');


                sectionEl.innerHTML = `
                    <div
                        class="mb-4 flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 pb-3"
                    >

                        <div>
                            <span
                                class="text-xs font-black uppercase tracking-wide text-blue-600"
                            >
                                Section ${sectionIndex + 1}
                            </span>

                            <span
                                class="ml-2 text-xs text-slate-400"
                            >
                                ${ratios.join(' : ')}
                            </span>
                        </div>


                        <div class="flex flex-wrap gap-1">

                            <button
                                type="button"
                                data-section-action="up"
                                class="rounded-lg border px-2 py-1 text-xs"
                            >
                                ↑
                            </button>

                            <button
                                type="button"
                                data-section-action="down"
                                class="rounded-lg border px-2 py-1 text-xs"
                            >
                                ↓
                            </button>

                            <button
                                type="button"
                                data-section-action="duplicate"
                                class="rounded-lg border px-2 py-1 text-xs"
                            >
                                Duplicate
                            </button>

                            <button
                                type="button"
                                class="eb-toggle-section rounded-lg border border-slate-200 bg-white px-3 py-1 text-xs font-black text-slate-600 hover:bg-slate-50"
                                aria-expanded="${section.collapsed ? 'false' : 'true'}"
                                title="${section.collapsed ? 'Expand section' : 'Collapse section'}"
                            >
                                ${section.collapsed ? 'Open' : 'Collapse'}
                            </button>

                            <button
                                type="button"
                                data-section-action="delete"
                                class="rounded-lg border border-red-200 px-2 py-1 text-xs text-red-600"
                            >
                                Delete
                            </button>

                        </div>

                    </div>


                    <div
                        class="eb-columns grid gap-4"
                    ></div>
                `;


                const columnsEl =
                    sectionEl
                        .querySelector(
                            '.eb-columns'
                        );

                columnsEl.style.gridTemplateColumns =
                    template;


                /*
                 * Stack columns on narrow editor widths.
                 */
                columnsEl.style.minWidth = '0';


                                    const sectionToggle =
                        sectionEl.querySelector(
                            '.eb-toggle-section'
                        );

                    if (sectionToggle) {
                        sectionToggle.addEventListener(
                            'click',
                            event => {
                                event.preventDefault();
                                event.stopPropagation();

                                section.collapsed =
                                    !section.collapsed;

                                sync();
                                renderCanvas();
                            }
                        );
                    }

section.columns.forEach(
                    (
                        column,
                        columnIndex
                    ) => {

                        const columnEl =
                            document.createElement(
                                'div'
                            );

                        columnEl.className =
                            'eb-column min-w-0 rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-3';

                        if (section.collapsed) {
                            columnEl.style.display =
                                'none';
                        }

                        columnEl.dataset.sectionId =
                            section.id;

                        columnEl.dataset.columnId =
                            column.id;


                        const widgetHtml =
                            (column.widgets || [])
                                .map(
                                    widget => `
                                        <div
                                            class="eb-widget mb-3 rounded-xl border border-slate-200 bg-white p-3"
                                            data-widget-id="${widget.id}"
                                        >

                                            <div
                                                class="mb-3 flex items-center justify-between gap-2 border-b border-slate-100 pb-2"
                                            >

                                                <div>
                                                    <span
                                                        class="text-[10px] font-black uppercase tracking-wide text-slate-400"
                                                    >
                                                        ${escapeHtml(
                                                            widgetDefinitions[
                                                                widget.type
                                                            ]?.label
                                                            || widget.type
                                                        )}
                                                    </span>

                                                    <div
                                                        class="mt-0.5 text-[10px] font-bold text-blue-500"
                                                    >
                                                        Click to edit
                                                    </div>
                                                </div>


                                                <div class="flex gap-1">

                                                    <button
                                                        type="button"
                                                        data-widget-action="duplicate"
                                                        data-widget-id="${widget.id}"
                                                        class="rounded border px-2 py-1 text-[10px]"
                                                    >
                                                        Copy
                                                    </button>

                                                    <button
                                                        type="button"
                                                        data-widget-action="delete"
                                                        data-widget-id="${widget.id}"
                                                        class="rounded border border-red-200 px-2 py-1 text-[10px] text-red-600"
                                                    >
                                                        Delete
                                                    </button>

                                                </div>

                                            </div>

                                            ${preview(widget)}

                                        </div>
                                    `
                                )
                                .join('');


                        columnEl.innerHTML = `
                            <div
                                class="mb-3 flex items-center justify-between"
                            >
                                <span
                                    class="text-[10px] font-black uppercase tracking-wide text-slate-400"
                                >
                                    Column ${columnIndex + 1}
                                </span>
                            </div>

                            <div
                                class="eb-column-widgets min-h-[70px]"
                            >
                                ${widgetHtml}
                            </div>

                            <button
                                type="button"
                                class="eb-add-widget mt-2 w-full rounded-xl border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-black text-blue-600 hover:bg-blue-100"
                            >
                                + Add Widget
                            </button>
                        `;


                        columnEl
                            .querySelector(
                                '.eb-add-widget'
                            )
                            .addEventListener(
                                'click',
                                event => {

                                    event.preventDefault();
                                    event.stopPropagation();

                                    selectedSectionId =
                                        section.id;

                                    selectedColumnId =
                                        column.id;

                                    selectedId =
                                        null;


                                    const drawer =
                                        document.getElementById(
                                            'widgetDrawer'
                                        );


                                    drawer.dataset.sectionId =
                                        section.id;

                                    drawer.dataset.columnId =
                                        column.id;


                                    /*
                                     * Make the target visually obvious.
                                     */
                                    document
                                        .querySelectorAll(
                                            '.eb-column'
                                        )
                                        .forEach(
                                            target => {
                                                target.classList.remove(
                                                    'ring-2',
                                                    'ring-blue-500',
                                                    'ring-offset-2'
                                                );
                                            }
                                        );


                                    columnEl.classList.add(
                                        'ring-2',
                                        'ring-blue-500',
                                        'ring-offset-2'
                                    );


                                    drawer.removeAttribute(
                                        'data-builder-hidden'
                                    );
                                }
                            );


                        columnEl
                            .querySelectorAll(
                                '[data-widget-id]'
                            )
                            .forEach(
                                element => {

                                    if (
                                        !element.classList
                                            .contains(
                                                'eb-widget'
                                            )
                                    ) {
                                        return;
                                    }

                                    element.addEventListener(
                                        'click',
                                        event => {

                                            const actionButton =
                                                event.target.closest(
                                                    '[data-widget-action]'
                                                );


                                            if (actionButton) {
                                                return;
                                            }


                                            event.preventDefault();
                                            event.stopPropagation();


                                            selectedSectionId =
                                                section.id;

                                            selectedColumnId =
                                                column.id;


                                            selectWidget(
                                                element.dataset
                                                    .widgetId
                                            );
                                        }
                                    );
                                }
                            );


                        columnEl
                            .querySelectorAll(
                                '[data-widget-action]'
                            )
                            .forEach(
                                button => {

                                    button.addEventListener(
                                        'click',
                                        event => {

                                            event.stopPropagation();

                                            widgetAction(
                                                button.dataset
                                                    .widgetId,

                                                button.dataset
                                                    .widgetAction
                                            );
                                        }
                                    );
                                }
                            );


                        columnsEl.appendChild(
                            columnEl
                        );
                    }
                );


                sectionEl
                    .querySelectorAll(
                        '[data-section-action]'
                    )
                    .forEach(
                        button => {

                            button.addEventListener(
                                'click',
                                () => {

                                    sectionAction(
                                        section.id,
                                        button.dataset
                                            .sectionAction
                                    );
                                }
                            );
                        }
                    );


                canvas.appendChild(
                    sectionEl
                );
            }
        );


        /*
         * Editor responsiveness.
         */
        canvas
            .querySelectorAll(
                '.eb-columns'
            )
            .forEach(
                grid => {

                    if (
                        canvas.dataset.device
                        !== 'desktop'
                    ) {
                        grid.style
                            .gridTemplateColumns =
                                '1fr';
                    }
                }
            );
    }


    function sectionAction(
        sectionId,
        action
    ) {
        const index =
            documentState.findIndex(
                section =>
                    section.id === sectionId
            );

        if (index < 0) {
            return;
        }


        if (action === 'delete') {
            documentState.splice(
                index,
                1
            );
        }


        if (action === 'duplicate') {

            const clone =
                JSON.parse(
                    JSON.stringify(
                        documentState[index]
                    )
                );

            clone.id = uid();

            clone.columns =
                clone.columns.map(
                    column => ({
                        ...column,
                        id: uid(),
                        widgets:
                            column.widgets.map(
                                widget => ({
                                    ...widget,
                                    id: uid()
                                })
                            )
                    })
                );

            documentState.splice(
                index + 1,
                0,
                clone
            );
        }


        if (
            action === 'up'
            && index > 0
        ) {
            [
                documentState[index - 1],
                documentState[index]
            ] = [
                documentState[index],
                documentState[index - 1]
            ];
        }


        if (
            action === 'down'
            && index
                < documentState.length - 1
        ) {
            [
                documentState[index + 1],
                documentState[index]
            ] = [
                documentState[index],
                documentState[index + 1]
            ];
        }


        sync();
        renderCanvas();
    }


    function widgetAction(
        id,
        action
    ) {

        const result =
            findWidget(id);

        if (!result) {
            return;
        }

        const {
            widget,
            column
        } = result;

        const index =
            column.widgets
                .findIndex(
                    item =>
                        item.id === id
                );


        if (action === 'delete') {

            column.widgets.splice(
                index,
                1
            );

            selectedId = null;
        }


        if (action === 'duplicate') {

            const clone =
                JSON.parse(
                    JSON.stringify(
                        widget
                    )
                );

            clone.id = uid();

            column.widgets.splice(
                index + 1,
                0,
                clone
            );
        }


        sync();
        renderCanvas();
        showPageSettings();
    }


    function selectWidget(id) {

        selectedId = id;

        const result =
            findWidget(id);

        const widget =
            result?.widget;

        if (!widget) {
            return;
        }


        selectedSectionId =
            result.section.id;

        selectedColumnId =
            result.column.id;


        document
            .getElementById(
                'pageSettingsPanel'
            )
            .setAttribute(
                'data-builder-hidden',
                ''
            );


        document
            .getElementById(
                'widgetSettingsPanel'
            )
            .removeAttribute(
                'data-builder-hidden'
            );


        document
            .getElementById(
                'settingsTitle'
            )
            .textContent =
                (
                    widgetDefinitions[
                        widget.type
                    ]?.label
                    || widget.type
                )
                + ' Settings';


        const fields =
            document.getElementById(
                'settingsFields'
            );


        function textField(
            label,
            key,
            value = ''
        ) {
            return `
                <div>
                    <label
                        class="text-xs font-black uppercase tracking-wide text-slate-500"
                    >
                        ${escapeHtml(label)}
                    </label>

                    <input
                        type="text"
                        data-data-key="${escapeHtml(key)}"
                        value="${escapeHtml(value)}"
                        class="mt-2 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm"
                    >
                </div>
            `;
        }


        function textArea(
            label,
            key,
            value = ''
        ) {
            return `
                <div>
                    <label
                        class="text-xs font-black uppercase tracking-wide text-slate-500"
                    >
                        ${escapeHtml(label)}
                    </label>

                    <textarea
                        data-data-key="${escapeHtml(key)}"
                        rows="5"
                        class="mt-2 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm"
                    >${escapeHtml(value)}</textarea>
                </div>
            `;
        }


        function numberField(
            label,
            key,
            value = 0
        ) {
            return `
                <div>
                    <label
                        class="text-xs font-black uppercase tracking-wide text-slate-500"
                    >
                        ${escapeHtml(label)}
                    </label>

                    <input
                        type="number"
                        data-data-key="${escapeHtml(key)}"
                        value="${escapeHtml(value)}"
                        class="mt-2 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm"
                    >
                </div>
            `;
        }


        let content = '';


        switch (widget.type) {

            case 'hero':
                content +=
                    textField(
                        'Eyebrow',
                        'eyebrow',
                        widget.data.eyebrow
                    );

                content +=
                    textField(
                        'Heading',
                        'heading',
                        widget.data.heading
                    );

                content +=
                    textArea(
                        'Text',
                        'text',
                        widget.data.text
                    );

                content +=
                    textField(
                        'Button Text',
                        'buttonText',
                        widget.data.buttonText
                    );

                content +=
                    textField(
                        'Button Link',
                        'buttonUrl',
                        widget.data.buttonUrl
                    );

                content +=
                    textField(
                        'Image',
                        'image',
                        widget.data.image
                    );
                break;


            case 'heading':
                content +=
                    textField(
                        'Heading',
                        'text',
                        widget.data.text
                    );

                content += `
                    <div>
                        <label
                            class="text-xs font-black uppercase tracking-wide text-slate-500"
                        >
                            Heading Level
                        </label>

                        <select
                            data-data-key="level"
                            class="mt-2 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm"
                        >
                            ${[
                                'h1',
                                'h2',
                                'h3',
                                'h4'
                            ].map(
                                level => `
                                    <option
                                        value="${level}"
                                        ${
                                            widget.data.level === level
                                                ? 'selected'
                                                : ''
                                        }
                                    >
                                        ${level.toUpperCase()}
                                    </option>
                                `
                            ).join('')}
                        </select>
                    </div>
                `;
                break;


            case 'text':
                content +=
                    textArea(
                        'Text',
                        'text',
                        widget.data.text
                    );
                break;


            case 'image':

                content += `
                    <div>

                        <label
                            class="text-xs font-black uppercase tracking-wide text-slate-500"
                        >
                            Image
                        </label>


                        <div
                            id="builderImagePreview"
                            class="mt-2 overflow-hidden rounded-2xl border border-slate-200 bg-slate-50"
                        >

                            ${
                                widget.data.src
                                    ? `
                                        <img
                                            src="${escapeHtml(widget.data.src)}"
                                            alt=""
                                            class="max-h-[260px] w-full object-cover"
                                        >
                                    `
                                    : `
                                        <div
                                            class="flex min-h-[160px] items-center justify-center p-6 text-center text-xs text-slate-400"
                                        >
                                            No image uploaded
                                        </div>
                                    `
                            }

                        </div>


                        <input
                            type="file"
                            id="builderImageUpload"
                            accept="image/jpeg,image/png,image/webp,image/gif"
                            class="mt-3 block w-full rounded-xl border border-slate-200 bg-white p-3 text-xs"
                        >


                        <div
                            id="builderImageUploadStatus"
                            class="mt-2 text-xs text-slate-400"
                        >
                            Upload an image from this device. It will be stored in this website's media storage.
                        </div>

                    </div>
                `;


                content +=
                    textField(
                        'Alternative Text',
                        'alt',
                        widget.data.alt
                    );


                content +=
                    textField(
                        'Caption',
                        'caption',
                        widget.data.caption
                    );

                break;


            case 'button':
                content +=
                    textField(
                        'Button Text',
                        'text',
                        widget.data.text
                    );

                content +=
                    textField(
                        'Button Link',
                        'url',
                        widget.data.url
                    );
                break;


            case 'cta':
                content +=
                    textField(
                        'Heading',
                        'heading',
                        widget.data.heading
                    );

                content +=
                    textArea(
                        'Text',
                        'text',
                        widget.data.text
                    );

                content +=
                    textField(
                        'Button Text',
                        'buttonText',
                        widget.data.buttonText
                    );

                content +=
                    textField(
                        'Button Link',
                        'buttonUrl',
                        widget.data.buttonUrl
                    );
                break;


            case 'faq':

                content +=
                    textField(
                        'Section Heading',
                        'heading',
                        widget.data.heading
                            || 'Frequently Asked Questions'
                    );

                content += `
                    <div>

                        <div
                            class="flex items-center justify-between gap-3"
                        >
                            <div>
                                <label
                                    class="text-xs font-black uppercase tracking-wide text-slate-500"
                                >
                                    FAQ Accordion
                                </label>

                                <p
                                    class="mt-1 text-xs leading-5 text-slate-400"
                                >
                                    Add, edit, remove and reorder questions.
                                </p>
                            </div>

                            <button
                                type="button"
                                id="addFaqItem"
                                class="rounded-xl bg-blue-600 px-3 py-2 text-xs font-black text-white"
                            >
                                + Add FAQ
                            </button>

                        </div>


                        <div
                            id="faqAccordionEditor"
                            class="mt-4 space-y-3"
                        ></div>

                    </div>
                `;

                break;


            case 'features':
            case 'cards':
            case 'testimonials':
            case 'stats':
            case 'gallery':
            case 'social':

                if (
                    Object.prototype
                        .hasOwnProperty
                        .call(
                            widget.data,
                            'heading'
                        )
                ) {
                    content +=
                        textField(
                            'Heading',
                            'heading',
                            widget.data.heading
                        );
                }

                content += `
                    <div>
                        <label
                            class="text-xs font-black uppercase tracking-wide text-slate-500"
                        >
                            Items
                        </label>

                        <p
                            class="mt-1 text-xs leading-5 text-slate-400"
                        >
                            This structured editor will also be upgraded to visual repeatable controls.
                        </p>

                        <textarea
                            data-json-key="items"
                            rows="10"
                            class="mt-2 w-full rounded-xl border border-slate-300 px-3 py-2 font-mono text-xs"
                        >${escapeHtml(
                            JSON.stringify(
                                widget.data.items || [],
                                null,
                                2
                            )
                        )}</textarea>
                    </div>
                `;

                break;


            case 'video':
                content +=
                    textField(
                        'Video URL',
                        'url',
                        widget.data.url
                    );
                break;


            case 'map':
                content +=
                    textArea(
                        'Map Embed / URL',
                        'embed',
                        widget.data.embed
                    );
                break;


            case 'form':
                content += `
                    <div>
                        <label
                            class="text-xs font-black uppercase tracking-wide text-slate-500"
                        >
                            Form
                        </label>

                        <select
                            data-data-key="formId"
                            class="mt-2 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm"
                        >
                            <option value="">
                                Select created form
                            </option>

                            @foreach($forms as $form)
                                <option
                                    value="{{ $form->id }}"
                                >
                                    {{ addslashes($form->name) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                `;
                break;


            case 'panorama':
                content +=
                    textField(
                        'Heading',
                        'heading',
                        widget.data.heading
                    );

                content +=
                    textField(
                        'Panorama ID',
                        'panoramaId',
                        widget.data.panoramaId
                    );

                content +=
                    numberField(
                        'Viewer Height',
                        'height',
                        widget.data.height || 480
                    );

                content += `
                    <label
                        class="flex items-center gap-3 rounded-xl border border-slate-200 p-3"
                    >
                        <input
                            type="checkbox"
                            data-data-key="autoRotate"
                            ${
                                widget.data.autoRotate
                                    ? 'checked'
                                    : ''
                            }
                        >

                        <span class="text-sm font-bold">
                            Auto rotate
                        </span>
                    </label>
                `;
                break;


            case 'spacer':
                content +=
                    numberField(
                        'Height',
                        'height',
                        widget.data.height || 48
                    );
                break;


            case 'columns':
                content +=
                    numberField(
                        'Columns',
                        'columns',
                        widget.data.columns || 2
                    );
                break;


            case 'html':
                content +=
                    textArea(
                        'HTML',
                        'html',
                        widget.data.html
                    );
                break;


            default:
                content += `
                    <div
                        class="rounded-xl border border-slate-200 bg-slate-50 p-3 text-xs leading-5 text-slate-500"
                    >
                        This widget has no additional content fields.
                    </div>
                `;
                break;
        }


        /*
         * Shared presentation settings.
         */
        content += `
            <div class="border-t border-slate-100 pt-4">

                <div
                    class="mb-3 text-xs font-black uppercase tracking-wide text-slate-400"
                >
                    Appearance
                </div>

                <div>
                    <label
                        class="text-xs font-black uppercase text-slate-500"
                    >
                        Background
                    </label>

                    <div class="mt-2 flex gap-2">

                        <input
                            type="color"
                            data-setting="background"
                            value="${escapeHtml(
                                widget.settings.background
                                || '#ffffff'
                            )}"
                            class="h-11 w-14 rounded border"
                        >

                        <input
                            type="text"
                            data-setting="background"
                            value="${escapeHtml(
                                widget.settings.background
                                || '#ffffff'
                            )}"
                            class="min-w-0 flex-1 rounded-xl border px-3 text-sm"
                        >

                    </div>
                </div>


                <div class="mt-4">

                    <label
                        class="text-xs font-black uppercase text-slate-500"
                    >
                        Text Color
                    </label>

                    <div class="mt-2 flex gap-2">

                        <input
                            type="color"
                            data-setting="textColor"
                            value="${escapeHtml(
                                widget.settings.textColor
                                || '#0f172a'
                            )}"
                            class="h-11 w-14 rounded border"
                        >

                        <input
                            type="text"
                            data-setting="textColor"
                            value="${escapeHtml(
                                widget.settings.textColor
                                || '#0f172a'
                            )}"
                            class="min-w-0 flex-1 rounded-xl border px-3 text-sm"
                        >

                    </div>

                </div>


                <div class="mt-4">

                    <label
                        class="text-xs font-black uppercase text-slate-500"
                    >
                        Alignment
                    </label>

                    <select
                        data-setting="align"
                        class="mt-2 w-full rounded-xl border px-3 py-2"
                    >
                        <option
                            value="left"
                            ${
                                widget.settings.align === 'left'
                                    ? 'selected'
                                    : ''
                            }
                        >
                            Left
                        </option>

                        <option
                            value="center"
                            ${
                                widget.settings.align === 'center'
                                    ? 'selected'
                                    : ''
                            }
                        >
                            Center
                        </option>

                        <option
                            value="right"
                            ${
                                widget.settings.align === 'right'
                                    ? 'selected'
                                    : ''
                            }
                        >
                            Right
                        </option>
                    </select>

                </div>

            </div>
        `;


        fields.innerHTML =
            content;


        /*
         * Image widget upload.
         *
         * Uses the existing tenant CMS media endpoint so images
         * remain owned by this website rather than the central
         * public theme directory.
         */
        if (widget.type === 'image') {

            const upload =
                document.getElementById(
                    'builderImageUpload'
                );

            const preview =
                document.getElementById(
                    'builderImagePreview'
                );

            const status =
                document.getElementById(
                    'builderImageUploadStatus'
                );


            upload?.addEventListener(
                'change',
                async () => {

                    const file =
                        upload.files?.[0];


                    if (!file) {
                        return;
                    }


                    if (
                        !file.type.startsWith(
                            'image/'
                        )
                    ) {
                        alert(
                            'Please choose a valid image file.'
                        );

                        upload.value = '';
                        return;
                    }


                    /*
                     * Immediate local preview.
                     */
                    const localUrl =
                        URL.createObjectURL(
                            file
                        );


                    preview.innerHTML = `
                        <img
                            src="${localUrl}"
                            alt=""
                            class="max-h-[260px] w-full object-cover"
                        >
                    `;


                    status.textContent =
                        'Uploading image...';

                    status.className =
                        'mt-2 text-xs font-bold text-blue-600';


                    const endpoint =
                        document
                            .getElementById(
                                'builderMediaUploadUrl'
                            )
                            ?.value;


                    if (!endpoint) {

                        status.textContent =
                            'Media upload endpoint is unavailable.';

                        status.className =
                            'mt-2 text-xs font-bold text-red-600';

                        return;
                    }


                    const body =
                        new FormData();


                    /*
                     * Support the common names used by the existing
                     * tenant media controller.
                     */
                    body.append(
                        'image',
                        file
                    );


                    const token =
                        document.querySelector(
                            '#pageBuilderForm input[name="_token"]'
                        )?.value;


                    try {

                        const response =
                            await fetch(
                                endpoint,
                                {
                                    method:'POST',

                                    headers:{
                                        'X-CSRF-TOKEN':
                                            token || '',

                                        'Accept':
                                            'application/json'
                                    },

                                    body
                                }
                            );


                        let payload = null;


                        try {
                            payload =
                                await response.json();
                        } catch {
                            payload = null;
                        }


                        if (!response.ok) {

                            const message =
                                payload?.message
                                || 'Image upload failed.';

                            throw new Error(
                                message
                            );
                        }


                        /*
                         * Accept the current endpoint as well as
                         * future normalized media responses.
                         */
                        const uploadedPath =
                            payload?.url
                            || payload?.path
                            || payload?.src
                            || payload?.location
                            || payload?.data?.url
                            || payload?.data?.path
                            || payload?.data?.src
                            || null;


                        if (!uploadedPath) {

                            console.log(
                                'Tenant media upload response:',
                                payload
                            );

                            throw new Error(
                                'Upload succeeded but no image path was returned.'
                            );
                        }


                        widget.data.src =
                            uploadedPath;


                        sync();
                        renderCanvas();


                        preview.innerHTML = `
                            <img
                                src="${escapeHtml(uploadedPath)}"
                                alt=""
                                class="max-h-[260px] w-full object-cover"
                            >
                        `;


                        status.textContent =
                            'Image uploaded successfully.';

                        status.className =
                            'mt-2 text-xs font-bold text-emerald-600';


                        URL.revokeObjectURL(
                            localUrl
                        );

                    } catch (error) {

                        console.error(
                            error
                        );


                        status.textContent =
                            error.message
                            || 'Image upload failed.';

                        status.className =
                            'mt-2 text-xs font-bold text-red-600';
                    }
                }
            );
        }


        /*
         * Visual FAQ accordion editor.
         */
        if (widget.type === 'faq') {

            if (
                !Array.isArray(
                    widget.data.items
                )
            ) {
                widget.data.items = [];
            }


            const faqEditor =
                document.getElementById(
                    'faqAccordionEditor'
                );


            function renderFaqEditor() {

                if (!faqEditor) {
                    return;
                }


                faqEditor.innerHTML = '';


                if (
                    !widget.data.items.length
                ) {
                    faqEditor.innerHTML = `
                        <div
                            class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-5 text-center text-xs text-slate-400"
                        >
                            No FAQs yet. Click Add FAQ.
                        </div>
                    `;

                    return;
                }


                widget.data.items.forEach(
                    (
                        item,
                        index
                    ) => {

                        const wrapper =
                            document.createElement(
                                'div'
                            );

                        wrapper.className =
                            'rounded-2xl border border-slate-200 bg-white';


                        wrapper.innerHTML = `
                            <button
                                type="button"
                                class="faq-editor-toggle flex w-full items-center justify-between gap-3 px-4 py-3 text-left"
                            >

                                <div class="min-w-0">

                                    <div
                                        class="text-[10px] font-black uppercase tracking-wide text-blue-600"
                                    >
                                        FAQ ${index + 1}
                                    </div>

                                    <div
                                        class="mt-1 truncate text-sm font-black text-slate-900"
                                    >
                                        ${escapeHtml(
                                            item.question
                                            || 'Untitled question'
                                        )}
                                    </div>

                                </div>

                                <span
                                    class="faq-editor-icon text-lg font-black text-slate-400"
                                >
                                    +
                                </span>

                            </button>


                            <div
                                class="faq-editor-body hidden border-t border-slate-100 p-4"
                            >

                                <div>

                                    <label
                                        class="text-xs font-black uppercase tracking-wide text-slate-500"
                                    >
                                        Question
                                    </label>

                                    <input
                                        type="text"
                                        class="faq-question-input mt-2 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm"
                                        value="${escapeHtml(
                                            item.question
                                            || ''
                                        )}"
                                    >

                                </div>


                                <div class="mt-4">

                                    <label
                                        class="text-xs font-black uppercase tracking-wide text-slate-500"
                                    >
                                        Answer
                                    </label>

                                    <textarea
                                        rows="5"
                                        class="faq-answer-input mt-2 w-full rounded-xl border border-slate-300 px-3 py-2.5 text-sm"
                                    >${escapeHtml(
                                        item.answer
                                        || ''
                                    )}</textarea>

                                </div>


                                <div
                                    class="mt-4 flex flex-wrap items-center justify-between gap-2"
                                >

                                    <div class="flex gap-2">

                                        <button
                                            type="button"
                                            class="faq-move-up rounded-lg border border-slate-200 px-3 py-2 text-xs font-black"
                                            ${index === 0 ? 'disabled' : ''}
                                        >
                                            ↑ Up
                                        </button>

                                        <button
                                            type="button"
                                            class="faq-move-down rounded-lg border border-slate-200 px-3 py-2 text-xs font-black"
                                            ${
                                                index
                                                === widget.data.items.length - 1
                                                    ? 'disabled'
                                                    : ''
                                            }
                                        >
                                            ↓ Down
                                        </button>

                                    </div>


                                    <button
                                        type="button"
                                        class="faq-delete rounded-lg border border-red-200 px-3 py-2 text-xs font-black text-red-600"
                                    >
                                        Delete FAQ
                                    </button>

                                </div>

                            </div>
                        `;


                        const toggle =
                            wrapper.querySelector(
                                '.faq-editor-toggle'
                            );

                        const body =
                            wrapper.querySelector(
                                '.faq-editor-body'
                            );

                        const icon =
                            wrapper.querySelector(
                                '.faq-editor-icon'
                            );


                        toggle.addEventListener(
                            'click',
                            () => {

                                const closed =
                                    body.classList
                                        .contains(
                                            'hidden'
                                        );


                                faqEditor
                                    .querySelectorAll(
                                        '.faq-editor-body'
                                    )
                                    .forEach(
                                        panel => {
                                            panel.classList
                                                .add(
                                                    'hidden'
                                                );
                                        }
                                    );


                                faqEditor
                                    .querySelectorAll(
                                        '.faq-editor-icon'
                                    )
                                    .forEach(
                                        target => {
                                            target.textContent =
                                                '+';
                                        }
                                    );


                                if (closed) {

                                    body.classList
                                        .remove(
                                            'hidden'
                                        );

                                    icon.textContent =
                                        '−';
                                }
                            }
                        );


                        wrapper
                            .querySelector(
                                '.faq-question-input'
                            )
                            .addEventListener(
                                'input',
                                event => {

                                    item.question =
                                        event.target.value;

                                    sync();


                                    wrapper
                                        .querySelector(
                                            '.faq-editor-toggle div div:last-child'
                                        )
                                        .textContent =
                                            item.question
                                            || 'Untitled question';
                                }
                            );


                        wrapper
                            .querySelector(
                                '.faq-answer-input'
                            )
                            .addEventListener(
                                'input',
                                event => {

                                    item.answer =
                                        event.target.value;

                                    sync();
                                }
                            );


                        wrapper
                            .querySelector(
                                '.faq-delete'
                            )
                            .addEventListener(
                                'click',
                                () => {

                                    if (
                                        !confirm(
                                            'Delete this FAQ?'
                                        )
                                    ) {
                                        return;
                                    }

                                    widget.data.items
                                        .splice(
                                            index,
                                            1
                                        );

                                    sync();
                                    renderFaqEditor();
                                    renderCanvas();
                                }
                            );


                        wrapper
                            .querySelector(
                                '.faq-move-up'
                            )
                            .addEventListener(
                                'click',
                                () => {

                                    if (
                                        index <= 0
                                    ) {
                                        return;
                                    }


                                    [
                                        widget.data.items[
                                            index - 1
                                        ],
                                        widget.data.items[
                                            index
                                        ]
                                    ] = [
                                        widget.data.items[
                                            index
                                        ],
                                        widget.data.items[
                                            index - 1
                                        ]
                                    ];


                                    sync();
                                    renderFaqEditor();
                                    renderCanvas();
                                }
                            );


                        wrapper
                            .querySelector(
                                '.faq-move-down'
                            )
                            .addEventListener(
                                'click',
                                () => {

                                    if (
                                        index
                                        >= widget.data.items.length - 1
                                    ) {
                                        return;
                                    }


                                    [
                                        widget.data.items[
                                            index + 1
                                        ],
                                        widget.data.items[
                                            index
                                        ]
                                    ] = [
                                        widget.data.items[
                                            index
                                        ],
                                        widget.data.items[
                                            index + 1
                                        ]
                                    ];


                                    sync();
                                    renderFaqEditor();
                                    renderCanvas();
                                }
                            );


                        faqEditor.appendChild(
                            wrapper
                        );
                    }
                );
            }


            document
                .getElementById(
                    'addFaqItem'
                )
                ?.addEventListener(
                    'click',
                    () => {

                        widget.data.items.push({
                            question:
                                'New question',

                            answer:
                                'Add the answer here.'
                        });


                        sync();
                        renderFaqEditor();
                        renderCanvas();


                        requestAnimationFrame(
                            () => {

                                const panels =
                                    faqEditor
                                        ?.querySelectorAll(
                                            '.faq-editor-body'
                                        );

                                const icons =
                                    faqEditor
                                        ?.querySelectorAll(
                                            '.faq-editor-icon'
                                        );


                                const lastPanel =
                                    panels?.[
                                        panels.length - 1
                                    ];

                                const lastIcon =
                                    icons?.[
                                        icons.length - 1
                                    ];


                                lastPanel
                                    ?.classList
                                    .remove(
                                        'hidden'
                                    );

                                if (lastIcon) {
                                    lastIcon.textContent =
                                        '−';
                                }
                            }
                        );
                    }
                );


            renderFaqEditor();
        }


        /*
         * Bind normal widget data fields.
         */
        fields
            .querySelectorAll(
                '[data-data-key]'
            )
            .forEach(
                input => {

                    const update =
                        () => {

                            const key =
                                input.dataset
                                    .dataKey;

                            let value;

                            if (
                                input.type
                                === 'checkbox'
                            ) {
                                value =
                                    input.checked;
                            } else if (
                                input.type
                                === 'number'
                            ) {
                                value =
                                    Number(
                                        input.value
                                    );
                            } else {
                                value =
                                    input.value;
                            }

                            widget.data[key] =
                                value;

                            sync();
                            renderCanvas();
                        };


                    input.addEventListener(
                        'input',
                        update
                    );

                    input.addEventListener(
                        'change',
                        update
                    );
                }
            );


        /*
         * Bind structured JSON arrays.
         */
        fields
            .querySelectorAll(
                '[data-json-key]'
            )
            .forEach(
                input => {

                    input.addEventListener(
                        'change',
                        () => {

                            try {

                                const parsed =
                                    JSON.parse(
                                        input.value
                                    );

                                if (
                                    !Array.isArray(
                                        parsed
                                    )
                                ) {
                                    throw new Error();
                                }

                                widget.data[
                                    input.dataset.jsonKey
                                ] = parsed;

                                input.classList
                                    .remove(
                                        'border-red-500'
                                    );

                                sync();
                                renderCanvas();

                            } catch {

                                input.classList
                                    .add(
                                        'border-red-500'
                                    );

                                alert(
                                    'The items field must contain a valid JSON array.'
                                );
                            }
                        }
                    );
                }
            );


        /*
         * Bind presentation settings.
         */
        fields
            .querySelectorAll(
                '[data-setting]'
            )
            .forEach(
                input => {

                    input.addEventListener(
                        'input',
                        event => {

                            const key =
                                event.target
                                    .dataset
                                    .setting;

                            widget.settings[key] =
                                event.target.value;


                            fields
                                .querySelectorAll(
                                    `[data-setting="${key}"]`
                                )
                                .forEach(
                                    other => {

                                        if (
                                            other
                                            !== event.target
                                        ) {
                                            other.value =
                                                event.target.value;
                                        }
                                    }
                                );

                            sync();
                        }
                    );
                }
            );
    }


    function showPageSettings() {
        document.getElementById('widgetSettingsPanel').setAttribute('data-builder-hidden','');
        document.getElementById('pageSettingsPanel').removeAttribute('data-builder-hidden');
    }

    function sync() {
        jsonInput.value = JSON.stringify(documentState);
    }

    function setDevice(device) {
        canvas.dataset.device =
            device;

        renderCanvas();

        ['Desktop','Tablet','Mobile'].forEach(name => {
            const button = document.getElementById('eb' + name);
            const active = name.toLowerCase() === device;

            button.className = active
                ? 'rounded-xl bg-slate-950 px-3 py-2 text-xs font-black text-white'
                : 'rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-black';
        });
    }

    try {
        const parsed = JSON.parse(jsonInput.value || '[]');
        documentState =
            normalizeDocument(
                parsed
            );
    } catch {
        documentState = [];
    }

    /*
     * Dynamic slug generation.
     *
     * Slug follows the page title until the administrator
     * manually edits the slug field.
     */
    const pageTitle =
        document.getElementById(
            'pageTitle'
        );

    const pageSlug =
        document.getElementById(
            'pageSlug'
        );

    let slugManuallyEdited =
        false;


    function makeSlug(value) {

        return String(value || '')
            .normalize('NFKD')
            .replace(
                /[\u0300-\u036f]/g,
                ''
            )
            .toLowerCase()
            .trim()
            .replace(
                /[^a-z0-9]+/g,
                '-'
            )
            .replace(
                /^-+|-+$/g,
                ''
            );
    }


    pageTitle?.addEventListener(
        'input',
        () => {

            if (
                !slugManuallyEdited
            ) {
                pageSlug.value =
                    makeSlug(
                        pageTitle.value
                    );
            }
        }
    );


    pageSlug?.addEventListener(
        'input',
        () => {

            slugManuallyEdited =
                true;

            pageSlug.value =
                makeSlug(
                    pageSlug.value
                );
        }
    );


    renderLibrary();
    renderCanvas();
    sync();

    search.addEventListener(
        'input',
        e => renderLibrary(e.target.value)
    );

    const sectionModal =
        document.getElementById(
            'sectionLayoutModal'
        );


    document
        .getElementById(
            'addSectionButton'
        )
        .addEventListener(
            'click',
            () => {

                sectionModal
                    .removeAttribute(
                        'data-builder-hidden'
                    );
            }
        );


    document
        .getElementById(
            'closeSectionLayout'
        )
        .addEventListener(
            'click',
            () => {

                sectionModal
                    .setAttribute(
                        'data-builder-hidden',
                        ''
                    );
            }
        );


    document
        .querySelectorAll(
            '.section-layout-choice'
        )
        .forEach(
            button => {

                button.addEventListener(
                    'click',
                    () => {

                        const ratios =
                            button.dataset
                                .ratios
                                .split(',')
                                .map(Number);

                        documentState.push(
                            createSection(
                                ratios
                            )
                        );

                        sync();
                        renderCanvas();

                        sectionModal
                            .setAttribute(
                                'data-builder-hidden',
                                ''
                            );
                    }
                );
            }
        );


    document
        .getElementById(
            'addCustomSection'
        )
        .addEventListener(
            'click',
            () => {

                const value =
                    document
                        .getElementById(
                            'customSectionRatio'
                        )
                        .value
                        .trim();

                const ratios =
                    value
                        .split(':')
                        .map(
                            item =>
                                Number(
                                    item.trim()
                                )
                        )
                        .filter(
                            item =>
                                Number.isFinite(
                                    item
                                )
                                && item > 0
                        );

                if (
                    !ratios.length
                    || ratios.length > 6
                ) {
                    alert(
                        'Enter between 1 and 6 valid positive ratios.'
                    );

                    return;
                }

                documentState.push(
                    createSection(
                        ratios
                    )
                );

                sync();
                renderCanvas();

                sectionModal
                    .setAttribute(
                        'data-builder-hidden',
                        ''
                    );
            }
        );


    const widgetDrawer =
        document.getElementById(
            'widgetDrawer'
        );

    const widgetDrawerPanel =
        document.getElementById(
            'widgetDrawerPanel'
        );

    

    document
        .getElementById(
            'closeWidgetDrawer'
        )
        .addEventListener(
            'click',
            () => {
                widgetDrawer.setAttribute(
                    'data-builder-hidden',
                    ''
                );
            }
        );

    widgetDrawer.addEventListener(
        'click',
        event => {
            if (
                event.target === widgetDrawer
            ) {
                widgetDrawer.setAttribute(
                    'data-builder-hidden',
                    ''
                );
            }
        }
    );

    document.getElementById('closeWidgetSettings')
        .addEventListener('click', showPageSettings);

    document.getElementById('ebDesktop')
        .addEventListener('click', () => setDevice('desktop'));

    document.getElementById('ebTablet')
        .addEventListener('click', () => setDevice('tablet'));

    document.getElementById('ebMobile')
        .addEventListener('click', () => setDevice('mobile'));

    const aiModal =
        document.getElementById(
            'aiModal'
        );

    document
        .getElementById(
            'aiAssistButton'
        )
        .addEventListener(
            'click',
            () => {
                aiModal.removeAttribute(
                    'data-builder-hidden'
                );
            }
        );

    document
        .querySelectorAll(
            '.ai-scope'
        )
        .forEach(
            button => {

                button.addEventListener(
                    'click',
                    () => {

                        document
                            .querySelectorAll(
                                '.ai-scope'
                            )
                            .forEach(
                                item => {
                                    item.className =
                                        'ai-scope rounded-2xl border border-slate-200 p-4 text-left hover:border-blue-300';
                                }
                            );

                        button.className =
                            'ai-scope rounded-2xl border-2 border-blue-600 bg-blue-50 p-4 text-left';

                        document
                            .getElementById(
                                'aiScope'
                            )
                            .value =
                                button.dataset.scope;
                    }
                );

            }
        );

    document.getElementById('closeAiModal').addEventListener('click', () => {
        aiModal.setAttribute('data-builder-hidden','');
    });

    aiModal.addEventListener('click', event => {
        if (event.target === aiModal) {
            aiModal.setAttribute('data-builder-hidden','');
        }
    });

    document.addEventListener(
        'keydown',
        event => {

            if (
                event.key === 'Escape'
            ) {
                aiModal.setAttribute(
                    'data-builder-hidden',
                    ''
                );

                widgetDrawer.setAttribute(
                    'data-builder-hidden',
                    ''
                );

                sectionModal.setAttribute(
                    'data-builder-hidden',
                    ''
                );
            }
        }
    );

    document.getElementById('pageBuilderForm')
        .addEventListener('submit', sync);
})();
</script>
@endsection
