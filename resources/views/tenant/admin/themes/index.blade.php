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
