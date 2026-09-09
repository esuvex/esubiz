@extends('admin.layouts.app')

@section('title', 'Choose Marketplace Theme')

@section('content')

@php
/*
 * ESUBIZ_DYNAMIC_THEME_PROGRESS_V27
 *
 * This view is reachable only when Theme selection is visible.
 */
$step = 3;
$steps = 6;
@endphp

<form
    method="POST"
    action="{{ route('websites.theme.save', $website) }}"
    class="rounded-3xl bg-slate-100 p-8"
    id="website-theme-selection-form">

    @csrf

        <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">

            <div>

                <h1 class="text-4xl font-bold text-slate-900">
                    Choose Marketplace Theme
                </h1>

                <p class="mt-3 text-slate-500">
                    Select a design theme from the Esubiz Marketplace for your website. New themes added by Esubiz will automatically become available here.
                </p>

            </div>

            <div class="rounded-2xl bg-white px-6 py-5 shadow-sm">

                <div class="text-sm text-slate-500">
                    Step
                </div>

                <div class="text-3xl font-bold text-blue-600">
                    {{ $step }} / {{ $steps }}
                </div>

            </div>

        </div>

        <div class="mt-10 grid grid-cols-4 gap-4 md:flex md:items-center">

            @for($i = 1; $i <= $steps; $i++)

                <div class="flex justify-center md:flex-1">

                    <div class="flex h-12 w-12 items-center justify-center rounded-full border-2 text-sm font-bold {{ $i == $step ? 'border-blue-600 bg-blue-600 text-white' : 'border-slate-300 bg-white text-slate-500' }}">

                        {{ $i }}

                    </div>

                </div>

            @endfor

        </div>

        {{-- ESUBIZ_WIZARD_THEME_CARDS_V40 --}}
        <div class="mt-10 grid grid-cols-1 gap-6 lg:grid-cols-3">

            @foreach($themes as $theme)

                @php
                    $themeSlug = (string) $theme->slug;
                    $themeName = (string) $theme->name;
                    $themeDescription =
                        (string) ($theme->description ?? '');

                    $themePreviewUrl =
                        $theme->wizard_preview_url ?? null;

                    $isDefaultTheme =
                        $defaultTheme !== ''
                        && $themeSlug === $defaultTheme;

                    $savedTheme =
                        trim(
                            (string) data_get(
                                $wizard,
                                'theme',
                                ''
                            )
                        );

                    $isSelectedTheme =
                        $savedTheme !== ''
                        ? $savedTheme === $themeSlug
                        : $isDefaultTheme;
                @endphp

                <article
                    data-theme-card
                    data-theme-slug="{{ $themeSlug }}"
                    class="overflow-hidden rounded-3xl border-2 bg-white transition duration-300 hover:-translate-y-1 hover:shadow-xl {{ $isSelectedTheme ? 'border-blue-600 ring-4 ring-blue-100' : 'border-slate-200' }}">

                    <input
                        type="radio"
                        name="theme"
                        value="{{ $themeSlug }}"
                        data-theme-radio
                        class="hidden"
                        {{ $isSelectedTheme ? 'checked' : '' }}
                        form="website-theme-selection-form">

                    @if($themePreviewUrl)

                        <button
                            type="button"
                            data-theme-preview
                            data-preview-url="{{ $themePreviewUrl }}"
                            data-preview-name="{{ $themeName }}"
                            class="group relative block aspect-[16/10] w-full overflow-hidden bg-slate-100 text-left">

                            <img
                                src="{{ $themePreviewUrl }}"
                                alt="{{ $themeName }} Theme preview"
                                loading="lazy"
                                class="h-full w-full object-cover object-top transition duration-500 group-hover:scale-[1.02]">

                            <span class="absolute inset-0 flex items-center justify-center bg-slate-950/0 transition group-hover:bg-slate-950/25">

                                <span class="translate-y-2 rounded-xl bg-white/95 px-4 py-2 text-sm font-bold text-slate-900 opacity-0 shadow-lg transition group-hover:translate-y-0 group-hover:opacity-100">
                                    View Preview
                                </span>

                            </span>

                        </button>

                    @else

                        <div class="flex aspect-[16/10] items-center justify-center bg-slate-100">

                            <div class="text-center">

                                <div class="text-5xl">
                                    🖥️
                                </div>

                                <p class="mt-3 text-sm font-semibold text-slate-500">
                                    Preview unavailable
                                </p>

                            </div>

                        </div>

                    @endif

                    <div class="p-6">

                        <div class="flex items-start justify-between gap-4">

                            <h3 class="text-xl font-bold text-slate-900">
                                {{ $themeName }}
                            </h3>

                            @if($isDefaultTheme)

                                <span class="shrink-0 rounded-full bg-blue-100 px-3 py-1 text-xs font-bold text-blue-700">
                                    Default
                                </span>

                            @endif

                        </div>

                        @if($themeDescription !== '')

                            <p class="mt-3 line-clamp-3 text-sm leading-6 text-slate-500">
                                {{ $themeDescription }}
                            </p>

                        @endif

                        <div class="mt-6 grid grid-cols-2 gap-3">

                            <button
                                type="button"
                                data-theme-preview
                                data-preview-url="{{ $themePreviewUrl }}"
                                data-preview-name="{{ $themeName }}"
                                {{ !$themePreviewUrl ? 'disabled' : '' }}
                                class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40">

                                Preview

                            </button>

                            <button
                                type="button"
                                data-theme-select
                                data-theme-slug="{{ $themeSlug }}"
                                class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-blue-700">

                                {{ $isSelectedTheme ? 'Selected' : 'Select' }}

                            </button>

                        </div>

                    </div>

                </article>

            @endforeach

        </div>

        <div class="mt-10 flex flex-col gap-4 border-t border-slate-200 pt-8 sm:flex-row sm:items-center sm:justify-between">

            <a
                href="{{ route('websites.information', $website) }}"
                class="inline-flex items-center justify-center rounded-xl border border-slate-300 bg-white px-6 py-3 font-medium text-slate-700 hover:bg-slate-100">

                ← Back

            </a>

            <button
                type="submit"
                class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-8 py-3 font-semibold text-white hover:bg-blue-700">

                Continue →

            </button>

        </div>

    </div>

</form>


{{-- ESUBIZ_WIZARD_THEME_PREVIEW_DESKTOP_OFFSET_V46 --}}
<style>
@media (min-width: 1024px) {
    #wizardThemePreviewModal {
        left: 318px !important;
        right: auto !important;
        width: calc(100vw - 318px) !important;
    }

    #wizardThemePreviewModal > div {
        width: 100% !important;
        max-width: 1152px !important;
    }
}
</style>

{{-- ESUBIZ_WIZARD_THEME_PREVIEW_MODAL_V40 --}}
<div
    id="wizardThemePreviewModal"
    class="fixed inset-0 z-[99999] hidden overflow-y-auto bg-slate-950/80 p-4 backdrop-blur-sm sm:p-8 lg:left-[318px] lg:w-[calc(100vw-318px)]"
    aria-hidden="true">

    <div class="mx-auto flex min-h-full max-w-6xl items-center justify-center">

        <div
            id="wizardThemePreviewPanel"
            class="w-full overflow-hidden rounded-3xl bg-white shadow-2xl">

            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-5">

                <div>

                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-blue-600">
                        Theme Preview
                    </p>

                    <h3
                        id="wizardThemePreviewTitle"
                        class="mt-1 text-xl font-bold text-slate-900">
                        Theme Preview
                    </h3>

                </div>

                <button
                    type="button"
                    id="wizardThemePreviewClose"
                    class="flex h-11 w-11 items-center justify-center rounded-full bg-slate-100 text-2xl text-slate-600 transition hover:bg-slate-200"
                    aria-label="Close Theme preview">
                    ×
                </button>

            </div>

            <div class="max-h-[78vh] overflow-y-auto bg-slate-100 p-3 sm:p-6">

                <img
                    id="wizardThemePreviewImage"
                    src=""
                    alt="Theme preview"
                    class="mx-auto h-auto w-full rounded-2xl bg-white shadow-sm">

            </div>

        </div>

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const modal =
        document.getElementById(
            'wizardThemePreviewModal'
        );

    const panel =
        document.getElementById(
            'wizardThemePreviewPanel'
        );

    const image =
        document.getElementById(
            'wizardThemePreviewImage'
        );

    const title =
        document.getElementById(
            'wizardThemePreviewTitle'
        );

    const close =
        document.getElementById(
            'wizardThemePreviewClose'
        );

    if (!modal || !image || !title || !close) {
        return;
    }

    function openPreview(trigger) {

        const url =
            trigger.dataset.previewUrl || '';

        const name =
            trigger.dataset.previewName
            || 'Theme';

        if (!url) {
            return;
        }

        image.src = url;
        image.alt = name + ' Theme preview';
        title.textContent = name;

        modal.classList.remove('hidden');
        modal.setAttribute(
            'aria-hidden',
            'false'
        );

        document.body.classList.add(
            'overflow-hidden'
        );
    }

    function closePreview() {

        modal.classList.add('hidden');
        modal.setAttribute(
            'aria-hidden',
            'true'
        );

        image.src = '';

        document.body.classList.remove(
            'overflow-hidden'
        );
    }

    document
        .querySelectorAll(
            '[data-theme-preview]'
        )
        .forEach(function (trigger) {

            trigger.addEventListener(
                'click',
                function (event) {

                    event.preventDefault();
                    event.stopPropagation();

                    openPreview(trigger);
                }
            );
        });

    close.addEventListener(
        'click',
        closePreview
    );

    modal.addEventListener(
        'click',
        function (event) {

            if (
                event.target === modal
                || (
                    panel
                    && !panel.contains(
                        event.target
                    )
                )
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
                && !modal.classList.contains(
                    'hidden'
                )
            ) {
                closePreview();
            }
        }
    );
});
</script>

{{-- ESUBIZ_USER_THEME_FAST_AJAX_V29 --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('website-theme-selection-form');

    if (!form) {
        return;
    }

    const buttons = Array.from(
        form.querySelectorAll('[data-theme-select]')
    );

    let submitting = false;

    async function submitTheme(button) {
        if (submitting) {
            return;
        }

        const slug = button.dataset.themeSlug;

        const radio = Array.from(
            form.querySelectorAll('[data-theme-radio]')
        ).find(function (item) {
            return item.value === slug;
        });

        if (!radio) {
            return;
        }

        radio.checked = true;

        form.querySelectorAll('[data-theme-card]').forEach(
            function (card) {
                const selected =
                    card.dataset.themeSlug === slug;

                card.classList.toggle(
                    'border-blue-600',
                    selected
                );

                card.classList.toggle(
                    'ring-4',
                    selected
                );

                card.classList.toggle(
                    'ring-blue-100',
                    selected
                );

                if (!selected) {
                    card.classList.add(
                        'border-slate-200'
                    );
                } else {
                    card.classList.remove(
                        'border-slate-200'
                    );
                }
            }
        );

        submitting = true;

        const oldLabel = button.textContent;

        buttons.forEach(function (item) {
            item.disabled = true;
            item.classList.add(
                'opacity-60',
                'cursor-not-allowed'
            );
        });

        button.textContent = 'Loading...';

        try {
            const response = await fetch(
                form.action,
                {
                    method: 'POST',
                    body: new FormData(form),
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin',
                    cache: 'no-store'
                }
            );

            if (!response.ok) {
                throw new Error(
                    'Theme selection request failed.'
                );
            }

            const data = await response.json();

            if (
                data.success
                && data.next_url
            ) {
                window.location.assign(
                    data.next_url
                );
                return;
            }

            throw new Error(
                'Plan URL was not returned.'
            );

        } catch (error) {
            submitting = false;

            buttons.forEach(function (item) {
                item.disabled = false;
                item.classList.remove(
                    'opacity-60',
                    'cursor-not-allowed'
                );
            });

            button.textContent = oldLabel;

            console.error(
                'Esubiz Theme selection:',
                error
            );
        }
    }

    buttons.forEach(function (button) {
        button.addEventListener(
            'click',
            function (event) {
                event.preventDefault();
                event.stopPropagation();

                submitTheme(button);
            }
        );
    });
});
</script>

@endsection
