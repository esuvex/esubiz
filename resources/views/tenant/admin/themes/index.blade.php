@extends('tenant.admin.layouts.app')

@section('title', 'Themes')

@section('content')

<div class="mx-auto max-w-7xl space-y-8">

    <div>
        <div class="text-xs font-black uppercase tracking-[.16em] text-blue-600">
            Installed Website Products
        </div>

        <h1 class="mt-2 text-3xl font-black tracking-tight text-slate-900">
            Themes
        </h1>

        <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
            View, activate and configure themes installed on this website.
            Only one theme can be active at a time.
        </p>
    </div>


    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-bold text-emerald-700">
            {{ session('success') }}
        </div>
    @endif


    {{-- =====================================================
         Theme Discovery / Creation
         ===================================================== --}}

    <section>

        <div
            class="mb-5 flex flex-wrap items-end justify-between gap-4"
        >
            <div>

                <div
                    class="text-xs font-black uppercase tracking-[.16em] text-blue-600"
                >
                    Get More Themes
                </div>

                <h2
                    class="mt-2 text-2xl font-black tracking-tight text-slate-900"
                >
                    Expand your website design
                </h2>

                <p
                    class="mt-2 max-w-3xl text-sm leading-6 text-slate-500"
                >
                    Browse professionally built Esubiz themes or create
                    a custom theme using Esubiz AI.
                </p>

            </div>

        </div>


        <div
            class="grid gap-6 lg:grid-cols-2"
        >

            {{-- Theme Marketplace --}}
            <article
                class="relative overflow-hidden rounded-3xl border border-slate-800 bg-slate-950 p-7 text-white shadow-sm"
            >

                <div
                    class="pointer-events-none absolute -right-16 -top-16 h-56 w-56 rounded-full bg-blue-500/20 blur-3xl"
                ></div>


                <div
                    class="relative"
                >

                    <div
                        class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-white/10 text-2xl"
                    >
                        ◈
                    </div>


                    <div
                        class="mt-6 text-xs font-black uppercase tracking-[.18em] text-blue-300"
                    >
                        Esubiz Marketplace
                    </div>


                    <h3
                        class="mt-2 text-2xl font-black"
                    >
                        Theme Marketplace
                    </h3>


                    <p
                        class="mt-3 max-w-xl text-sm leading-6 text-slate-300"
                    >
                        Discover free and paid themes designed for the
                        Esubiz website structure. Purchased themes can
                        be installed and managed from this Themes area.
                    </p>


                    <div
                        class="mt-5 flex flex-wrap gap-2"
                    >

                        <span
                            class="rounded-full border border-white/10 bg-white/5 px-3 py-1 text-[11px] font-bold text-slate-300"
                        >
                            Business
                        </span>

                        <span
                            class="rounded-full border border-white/10 bg-white/5 px-3 py-1 text-[11px] font-bold text-slate-300"
                        >
                            Ecommerce
                        </span>

                        <span
                            class="rounded-full border border-white/10 bg-white/5 px-3 py-1 text-[11px] font-bold text-slate-300"
                        >
                            Hotel
                        </span>

                        <span
                            class="rounded-full border border-white/10 bg-white/5 px-3 py-1 text-[11px] font-bold text-slate-300"
                        >
                            More
                        </span>

                    </div>


                    <div
                        class="mt-7"
                    >

                        <a
                            href="https://marketplace.esubiz.com/themes"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-black text-white shadow-lg shadow-blue-950/20 transition hover:bg-blue-500"
                        >
                            Browse Themes
                            <span aria-hidden="true">↗</span>
                        </a>

                    </div>


                    <p
                        class="mt-4 text-[11px] leading-5 text-slate-400"
                    >
                        Availability, pricing and installation will respect
                        the website's SaaS or off-server deployment settings.
                    </p>

                </div>

            </article>



            {{-- Build Theme with AI --}}
            <article
                class="relative overflow-hidden rounded-3xl border border-violet-200 bg-gradient-to-br from-violet-50 via-white to-blue-50 p-7 shadow-sm"
            >

                <div
                    class="pointer-events-none absolute -bottom-20 -right-14 h-56 w-56 rounded-full bg-violet-300/30 blur-3xl"
                ></div>


                <div
                    class="relative"
                >

                    <div
                        class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-violet-600 text-xl font-black text-white shadow-sm"
                    >
                        ✦
                    </div>


                    <div
                        class="mt-6 text-xs font-black uppercase tracking-[.18em] text-violet-600"
                    >
                        Esubiz AI
                    </div>


                    <h3
                        class="mt-2 text-2xl font-black text-slate-950"
                    >
                        Build Theme with AI
                    </h3>


                    <p
                        class="mt-3 max-w-xl text-sm leading-6 text-slate-600"
                    >
                        Create a customizable website theme from your
                        business description, preferred style and website
                        type using the approved Esubiz theme structure.
                    </p>


                    <div
                        class="mt-5 grid gap-3 sm:grid-cols-3"
                    >

                        <div
                            class="rounded-2xl border border-violet-100 bg-white/80 p-3"
                        >
                            <div
                                class="text-xs font-black text-slate-900"
                            >
                                1. Describe
                            </div>

                            <div
                                class="mt-1 text-[11px] leading-5 text-slate-500"
                            >
                                Tell AI about the brand.
                            </div>
                        </div>


                        <div
                            class="rounded-2xl border border-violet-100 bg-white/80 p-3"
                        >
                            <div
                                class="text-xs font-black text-slate-900"
                            >
                                2. Generate
                            </div>

                            <div
                                class="mt-1 text-[11px] leading-5 text-slate-500"
                            >
                                Esubiz builds the theme.
                            </div>
                        </div>


                        <div
                            class="rounded-2xl border border-violet-100 bg-white/80 p-3"
                        >
                            <div
                                class="text-xs font-black text-slate-900"
                            >
                                3. Customize
                            </div>

                            <div
                                class="mt-1 text-[11px] leading-5 text-slate-500"
                            >
                                Fine-tune before activation.
                            </div>
                        </div>

                    </div>


                    <div
                        class="mt-7"
                    >

                        <button
                            type="button"
                            id="buildThemeWithAi"
                            class="inline-flex items-center gap-2 rounded-xl bg-violet-600 px-5 py-3 text-sm font-black text-white shadow-sm transition hover:bg-violet-700"
                        >
                            ✦ Build with AI
                        </button>

                    </div>


                    <p
                        class="mt-4 text-[11px] leading-5 text-slate-500"
                    >
                        Uses Esubiz AI Credits. Generated themes will follow
                        the same installable theme package standard used by
                        Marketplace themes.
                    </p>

                </div>

            </article>

        </div>

    </section>



    {{-- =====================================================
         Installed Themes
         ===================================================== --}}

    <section>

        <div class="mb-5">

            <div
                class="text-xs font-black uppercase tracking-[.16em] text-blue-600"
            >
                Installed Themes
            </div>

            <h2
                class="mt-2 text-2xl font-black tracking-tight text-slate-900"
            >
                Your website themes
            </h2>

            <p
                class="mt-2 max-w-3xl text-sm leading-6 text-slate-500"
            >
                Enable, disable, preview and configure themes already
                installed on this website.
            </p>

        </div>


        <div class="grid gap-7 lg:grid-cols-2 xl:grid-cols-3">

        @foreach($themes as $installedTheme)

            @php
                $previewUrl = route(
                    'tenant.cms.themes.business.preview',
                    [
                        'subdomain' =>
                            $website->subdomain
                    ]
                );
            @endphp

            <article
                class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm"
            >

                <button
                    type="button"
                    class="theme-preview-trigger group block w-full overflow-hidden bg-slate-100 text-left"
                    data-preview="{{ $previewUrl }}"
                    data-name="{{ $installedTheme['name'] }}"
                >
                    <div class="relative h-64 overflow-hidden">

                        <img
                            src="{{ $previewUrl }}"
                            alt="Business theme preview"
                            class="h-full w-full object-cover object-top transition duration-300 group-hover:scale-[1.02]"
                        >

                        <div
                            class="absolute inset-0 flex items-center justify-center bg-slate-950/0 opacity-0 transition group-hover:bg-slate-950/35 group-hover:opacity-100"
                        >
                            <span
                                class="rounded-xl bg-white px-4 py-2 text-xs font-black text-slate-900 shadow-lg"
                            >
                                View Full Preview
                            </span>
                        </div>

                    </div>
                </button>


                <div class="p-6">

                    <div class="flex items-start justify-between gap-4">

                        <div>
                            <h2 class="text-xl font-black text-slate-900">
                                Business
                            </h2>

                            <p class="mt-1 text-xs font-bold text-slate-400">
                                v1.0
                            </p>
                        </div>


                        <div
                            class="inline-flex overflow-hidden rounded-xl border border-slate-200 text-[10px] font-black uppercase"
                        >

                            <form
                                method="POST"
                                action="{{ route(
                                    'tenant.cms.themes.enable',
                                    [
                                        'subdomain' =>
                                            $website->subdomain,

                                        'theme' =>
                                            'business',
                                    ]
                                ) }}"
                            >
                                @csrf

                                <button
                                    type="submit"
                                    class="px-3 py-2 {{
                                        $installedTheme['active']
                                            ? 'bg-emerald-600 text-white'
                                            : 'bg-white text-slate-400 hover:bg-emerald-50 hover:text-emerald-700'
                                    }}"
                                >
                                    Enabled
                                </button>
                            </form>


                            <form
                                method="POST"
                                action="{{ route(
                                    'tenant.cms.themes.disable',
                                    [
                                        'subdomain' =>
                                            $website->subdomain,

                                        'theme' =>
                                            'business',
                                    ]
                                ) }}"
                            >
                                @csrf

                                <button
                                    type="submit"
                                    class="border-l border-slate-200 px-3 py-2 {{
                                        !$installedTheme['active']
                                            ? 'bg-red-600 text-white'
                                            : 'bg-white text-slate-400 hover:bg-red-50 hover:text-red-700'
                                    }}"
                                >
                                    Disabled
                                </button>

                            </form>

                        </div>

                    </div>


                    <p class="mt-4 text-sm leading-6 text-slate-500">
                        A clean and responsive business website theme with
                        animations, homepage sections, testimonials, FAQs,
                        contact and legal pages.
                    </p>


                    <div class="mt-6 flex flex-wrap gap-3">

                        <a
                            href="{{ route(
                                'tenant.cms.themes.business.configure',
                                [
                                    'subdomain' =>
                                        $website->subdomain
                                ]
                            ) }}"
                            class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-black text-white hover:bg-blue-700"
                        >
                            Configure
                        </a>


                        <button
                            type="button"
                            class="theme-preview-trigger rounded-xl border border-slate-200 bg-white px-5 py-2.5 text-sm font-black text-slate-700 hover:bg-slate-50"
                            data-preview="{{ $previewUrl }}"
                            data-name="Business"
                        >
                            Preview
                        </button>

                    </div>

                </div>

            </article>

        @endforeach

        </div>

    </section>


    <style>
        #businessThemePreviewModal {
            display: none;
        }

        #businessThemePreviewModal.is-open {
            display: block;
        }

        body.business-preview-open {
            overflow: hidden;
        }
    </style>


    <div
        id="businessThemePreviewModal"
        class="fixed inset-0 z-[3000] overflow-y-auto bg-slate-950/80 p-4 backdrop-blur-sm sm:p-8"
        aria-hidden="true"
    >
        <div
            id="businessThemePreviewPanel"
            class="mx-auto max-w-6xl"
        >

            <div
                class="sticky top-3 z-20 mb-4 flex items-center justify-between rounded-2xl bg-white px-5 py-4 shadow-xl"
            >

                <div>
                    <div class="text-xs font-black uppercase tracking-wide text-blue-600">
                        Theme Preview
                    </div>

                    <div
                        id="businessThemePreviewName"
                        class="text-lg font-black text-slate-900"
                    >
                        Business
                    </div>
                </div>


                <button
                    type="button"
                    id="businessThemePreviewClose"
                    class="grid h-10 w-10 place-items-center rounded-xl bg-slate-100 text-xl font-black text-slate-700 hover:bg-slate-200"
                >
                    ×
                </button>

            </div>


            <div
                class="overflow-hidden rounded-2xl bg-white shadow-2xl"
            >
                <img
                    id="businessThemePreviewImage"
                    src=""
                    alt="Business full theme preview"
                    class="block h-auto w-full"
                >
            </div>

        </div>
    </div>


    <script>
        document.addEventListener(
            'DOMContentLoaded',
            function () {

                const buildThemeWithAi =
                    document.getElementById(
                        'buildThemeWithAi'
                    );


                buildThemeWithAi?.addEventListener(
                    'click',
                    function () {

                        alert(
                            'Build Theme with AI is ready for the next integration step. It will use Esubiz AI Credits and the approved Esubiz theme package structure.'
                        );
                    }
                );



                const modal =
                    document.getElementById(
                        'businessThemePreviewModal'
                    );

                const panel =
                    document.getElementById(
                        'businessThemePreviewPanel'
                    );

                const image =
                    document.getElementById(
                        'businessThemePreviewImage'
                    );

                const name =
                    document.getElementById(
                        'businessThemePreviewName'
                    );

                const close =
                    document.getElementById(
                        'businessThemePreviewClose'
                    );


                function openPreview(
                    src,
                    themeName
                ) {
                    image.src = src;

                    name.textContent =
                        themeName || 'Business';

                    modal.classList.add(
                        'is-open'
                    );

                    modal.setAttribute(
                        'aria-hidden',
                        'false'
                    );

                    document.body.classList.add(
                        'business-preview-open'
                    );
                }


                function closePreview() {

                    modal.classList.remove(
                        'is-open'
                    );

                    modal.setAttribute(
                        'aria-hidden',
                        'true'
                    );

                    document.body.classList.remove(
                        'business-preview-open'
                    );
                }


                document
                    .querySelectorAll(
                        '.theme-preview-trigger'
                    )
                    .forEach(
                        function (button) {

                            button.addEventListener(
                                'click',
                                function () {

                                    openPreview(
                                        button.dataset.preview,
                                        button.dataset.name
                                    );
                                }
                            );
                        }
                    );


                close.addEventListener(
                    'click',
                    closePreview
                );


                modal.addEventListener(
                    'click',
                    function (event) {

                        if (
                            event.target === modal
                        ) {
                            closePreview();
                        }
                    }
                );


                document.addEventListener(
                    'keydown',
                    function (event) {

                        if (
                            event.key === 'Escape'
                        ) {
                            closePreview();
                        }
                    }
                );

            }
        );
    </script>

</div>

@endsection
