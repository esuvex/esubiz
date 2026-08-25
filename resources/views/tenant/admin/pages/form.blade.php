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
            name="builder_json"
            id="builderJson"
            value="{{ $builderJson }}"
        >

        <div class="grid gap-5 xl:grid-cols-[280px_minmax(0,1fr)_310px]">

            {{-- Widget Library --}}
            <aside class="self-start rounded-3xl border border-slate-200 bg-white p-4 shadow-sm xl:sticky xl:top-4">

                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="font-black text-slate-950">Widgets</h2>
                        <p class="text-xs text-slate-500">Click to add</p>
                    </div>

                    <button
                        type="button"
                        id="aiAssistButton"
                        class="rounded-xl bg-gradient-to-r from-blue-600 to-violet-600 px-3 py-2 text-xs font-black text-white"
                    >
                        ✦ AI Assist
                    </button>
                </div>

                <input
                    id="widgetSearch"
                    type="search"
                    placeholder="Search widgets..."
                    class="mt-4 w-full rounded-xl border border-slate-200 px-3 py-2 text-sm outline-none focus:border-blue-500"
                >

                <div id="widgetLibrary" class="mt-4 grid grid-cols-2 gap-2"></div>

                <div class="mt-5 rounded-2xl bg-blue-50 p-3">
                    <div class="text-xs font-black text-blue-900">
                        Module Widgets
                    </div>
                    <p class="mt-1 text-xs leading-5 text-blue-700">
                        Installed modules can register additional widgets here.
                    </p>
                </div>

            </aside>

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
                                value="{{ old('slug', $page->slug ?? '') }}"
                                placeholder="about-us"
                                class="mt-2 w-full rounded-xl border border-slate-300 px-4 py-3 outline-none focus:border-blue-500"
                            >
                        </div>

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
                                Select a widget from the library or drag widgets here.
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

{{-- AI Assist Modal --}}
<div
    id="aiModal"
    data-builder-hidden
    class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-950/60 p-4"
>
    <div class="w-full max-w-xl rounded-3xl bg-white p-6 shadow-2xl">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-xl font-black">✦ Esubiz AI Assist</h2>
                <p class="mt-1 text-sm text-slate-500">
                    Generate and improve content using Esubiz AI Credits.
                </p>
            </div>

            <button
                type="button"
                id="closeAiModal"
                class="rounded-lg px-3 py-1 text-xl"
            >
                ×
            </button>
        </div>

        <div class="mt-5 grid grid-cols-2 gap-2">
            <button type="button" class="ai-action rounded-xl border p-3 text-sm font-bold" data-action="generate">Generate</button>
            <button type="button" class="ai-action rounded-xl border p-3 text-sm font-bold" data-action="rewrite">Rewrite</button>
            <button type="button" class="ai-action rounded-xl border p-3 text-sm font-bold" data-action="shorten">Shorten</button>
            <button type="button" class="ai-action rounded-xl border p-3 text-sm font-bold" data-action="expand">Expand</button>
        </div>

        <textarea
            id="aiPrompt"
            rows="5"
            placeholder="Describe what you want Esubiz AI to write..."
            class="mt-4 w-full rounded-xl border border-slate-300 p-3 text-sm"
        ></textarea>

        <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-3 text-xs leading-5 text-amber-800">
            AI Assist is prepared in the builder UI. The central Esubiz AI Credit endpoint will be connected separately before AI generation is enabled.
        </div>

        <button
            type="button"
            id="runAiAssist"
            disabled
            class="mt-4 w-full cursor-not-allowed rounded-xl bg-slate-300 px-4 py-3 text-sm font-black text-slate-500"
        >
            AI Connection Pending
        </button>
    </div>
</div>

<script>
(() => {
    const widgetDefinitions = {
        hero: { label:'Hero', icon:'▣' },
        heading: { label:'Heading', icon:'H' },
        text: { label:'Rich Text', icon:'¶' },
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

    let documentState = [];
    let selectedId = null;
    let draggedId = null;

    const canvas = document.getElementById('builderCanvas');
    const empty = document.getElementById('emptyBuilder');
    const library = document.getElementById('widgetLibrary');
    const jsonInput = document.getElementById('builderJson');
    const search = document.getElementById('widgetSearch');

    function uid() {
        return 'w_' + Date.now().toString(36) + '_' +
            Math.random().toString(36).slice(2, 8);
    }

    function defaults(type) {
        const base = {
            id: uid(),
            type,
            settings: {
                background: '#ffffff',
                textColor: '#0f172a',
                align: 'left',
                paddingTop: 48,
                paddingBottom: 48
            },
            data: {}
        };

        const data = {
            hero: {
                eyebrow:'Welcome',
                heading:'Build something remarkable',
                text:'Tell visitors what makes your business different.',
                buttonText:'Get Started',
                buttonUrl:'#',
                image:''
            },
            heading:{ text:'Your heading', level:'h2' },
            text:{ text:'Add your content here.' },
            image:{ src:'', alt:'', caption:'' },
            gallery:{ images:[] },
            button:{ text:'Learn More', url:'#' },
            cta:{ heading:'Ready to get started?', text:'Let’s build something great together.', buttonText:'Contact Us', buttonUrl:'/contact' },
            features:{ heading:'Why choose us', items:[
                {title:'Quality', text:'Thoughtful service and attention to detail.'},
                {title:'Experience', text:'Built around the needs of our customers.'},
                {title:'Support', text:'Here when you need us.'}
            ]},
            cards:{ heading:'Explore', items:[] },
            testimonials:{ heading:'What our customers say', items:[] },
            faq:{ heading:'Frequently asked questions', items:[] },
            stats:{ items:[
                {value:'100+', label:'Customers'},
                {value:'5+', label:'Years Experience'},
                {value:'98%', label:'Satisfaction'}
            ]},
            columns:{ columns:2 },
            divider:{},
            spacer:{ height:48 },
            video:{ url:'' },
            map:{ embed:'' },
            form:{ formId:'' },
            social:{ links:[] },
            panorama:{
                panoramaId:'',
                heading:'',
                height:480,
                autoRotate:false
            },
            html:{ html:'' }
        };

        base.data = data[type] || {};
        return base;
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
        documentState.push(defaults(type));
        sync();
        renderCanvas();
        selectWidget(documentState.at(-1).id);
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
        canvas.querySelectorAll('.eb-widget').forEach(el => el.remove());

        empty.style.display = documentState.length ? 'none' : 'flex';

        documentState.forEach((widget, index) => {
            const el = document.createElement('section');
            el.className = 'eb-widget mb-3 rounded-2xl border border-slate-200 bg-white p-4';
            el.draggable = true;
            el.dataset.id = widget.id;

            el.innerHTML = `
                <div class="mb-3 flex items-center justify-between gap-3 border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2">
                        <span class="cursor-grab text-slate-400">⋮⋮</span>
                        <span class="text-xs font-black uppercase tracking-wide text-slate-500">
                            ${escapeHtml(widgetDefinitions[widget.type]?.label || widget.type)}
                        </span>
                    </div>

                    <div class="flex gap-1">
                        <button type="button" data-action="up" class="rounded-lg border px-2 py-1 text-xs">↑</button>
                        <button type="button" data-action="down" class="rounded-lg border px-2 py-1 text-xs">↓</button>
                        <button type="button" data-action="duplicate" class="rounded-lg border px-2 py-1 text-xs">Duplicate</button>
                        <button type="button" data-action="delete" class="rounded-lg border border-red-200 px-2 py-1 text-xs text-red-600">Delete</button>
                    </div>
                </div>

                ${preview(widget)}
            `;

            el.addEventListener('click', event => {
                const action = event.target.dataset.action;

                if (action) {
                    event.stopPropagation();
                    widgetAction(widget.id, action);
                    return;
                }

                selectWidget(widget.id);
            });

            el.addEventListener('dragstart', () => {
                draggedId = widget.id;
                el.classList.add('dragging');
            });

            el.addEventListener('dragend', () => {
                draggedId = null;
                el.classList.remove('dragging');
            });

            el.addEventListener('dragover', event => {
                event.preventDefault();
            });

            el.addEventListener('drop', event => {
                event.preventDefault();

                if (!draggedId || draggedId === widget.id) return;

                const from = documentState.findIndex(x => x.id === draggedId);
                const to = documentState.findIndex(x => x.id === widget.id);

                const [moving] = documentState.splice(from, 1);
                documentState.splice(to, 0, moving);

                sync();
                renderCanvas();
            });

            canvas.appendChild(el);
        });
    }

    function widgetAction(id, action) {
        const index = documentState.findIndex(x => x.id === id);
        if (index < 0) return;

        if (action === 'delete') {
            documentState.splice(index, 1);
            selectedId = null;
        }

        if (action === 'duplicate') {
            const clone = JSON.parse(JSON.stringify(documentState[index]));
            clone.id = uid();
            documentState.splice(index + 1, 0, clone);
        }

        if (action === 'up' && index > 0) {
            [documentState[index - 1], documentState[index]] =
                [documentState[index], documentState[index - 1]];
        }

        if (action === 'down' && index < documentState.length - 1) {
            [documentState[index + 1], documentState[index]] =
                [documentState[index], documentState[index + 1]];
        }

        sync();
        renderCanvas();
        showPageSettings();
    }

    function selectWidget(id) {
        selectedId = id;

        const widget = documentState.find(x => x.id === id);
        if (!widget) return;

        document.getElementById('pageSettingsPanel').setAttribute('data-builder-hidden','');
        document.getElementById('widgetSettingsPanel').removeAttribute('data-builder-hidden');

        document.getElementById('settingsTitle').textContent =
            (widgetDefinitions[widget.type]?.label || widget.type) + ' Settings';

        const fields = document.getElementById('settingsFields');

        fields.innerHTML = `
            <div>
                <label class="text-xs font-black uppercase text-slate-500">Background</label>
                <div class="mt-2 flex gap-2">
                    <input type="color" data-setting="background" value="${escapeHtml(widget.settings.background || '#ffffff')}" class="h-11 w-14 rounded border">
                    <input type="text" data-setting="background" value="${escapeHtml(widget.settings.background || '#ffffff')}" class="min-w-0 flex-1 rounded-xl border px-3 text-sm">
                </div>
            </div>

            <div>
                <label class="text-xs font-black uppercase text-slate-500">Text Color</label>
                <div class="mt-2 flex gap-2">
                    <input type="color" data-setting="textColor" value="${escapeHtml(widget.settings.textColor || '#0f172a')}" class="h-11 w-14 rounded border">
                    <input type="text" data-setting="textColor" value="${escapeHtml(widget.settings.textColor || '#0f172a')}" class="min-w-0 flex-1 rounded-xl border px-3 text-sm">
                </div>
            </div>

            <div>
                <label class="text-xs font-black uppercase text-slate-500">Alignment</label>
                <select data-setting="align" class="mt-2 w-full rounded-xl border px-3 py-2">
                    <option value="left" ${widget.settings.align === 'left' ? 'selected' : ''}>Left</option>
                    <option value="center" ${widget.settings.align === 'center' ? 'selected' : ''}>Center</option>
                    <option value="right" ${widget.settings.align === 'right' ? 'selected' : ''}>Right</option>
                </select>
            </div>

            <div class="rounded-xl bg-slate-50 p-3 text-xs leading-5 text-slate-500">
                Widget-specific content controls will be progressively registered here without changing the saved document format.
            </div>
        `;

        fields.querySelectorAll('[data-setting]').forEach(input => {
            input.addEventListener('input', event => {
                widget.settings[event.target.dataset.setting] = event.target.value;

                fields
                    .querySelectorAll(`[data-setting="${event.target.dataset.setting}"]`)
                    .forEach(other => {
                        if (other !== event.target) other.value = event.target.value;
                    });

                sync();
            });
        });
    }

    function showPageSettings() {
        document.getElementById('widgetSettingsPanel').setAttribute('data-builder-hidden','');
        document.getElementById('pageSettingsPanel').removeAttribute('data-builder-hidden');
    }

    function sync() {
        jsonInput.value = JSON.stringify(documentState);
    }

    function setDevice(device) {
        canvas.dataset.device = device;

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
        documentState = Array.isArray(parsed) ? parsed : [];
    } catch {
        documentState = [];
    }

    renderLibrary();
    renderCanvas();
    sync();

    search.addEventListener('input', e => renderLibrary(e.target.value));

    document.getElementById('closeWidgetSettings')
        .addEventListener('click', showPageSettings);

    document.getElementById('ebDesktop')
        .addEventListener('click', () => setDevice('desktop'));

    document.getElementById('ebTablet')
        .addEventListener('click', () => setDevice('tablet'));

    document.getElementById('ebMobile')
        .addEventListener('click', () => setDevice('mobile'));

    const aiModal = document.getElementById('aiModal');

    document.getElementById('aiAssistButton').addEventListener('click', () => {
        aiModal.removeAttribute('data-builder-hidden');
    });

    document.getElementById('closeAiModal').addEventListener('click', () => {
        aiModal.setAttribute('data-builder-hidden','');
    });

    aiModal.addEventListener('click', event => {
        if (event.target === aiModal) {
            aiModal.setAttribute('data-builder-hidden','');
        }
    });

    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') {
            aiModal.setAttribute('data-builder-hidden','');
        }
    });

    document.getElementById('pageBuilderForm')
        .addEventListener('submit', sync);
})();
</script>
@endsection
