@extends('tenant.admin.layouts.app')

@section('title', 'Settings - Site Settings')

@section('content')

{{-- ESUBIZ_CORE_SITE_SETTINGS_PAGE_V1 --}}

<div class="mx-auto max-w-7xl">

    <div class="mb-6">

        <div class="mb-5">
            <h1 class="text-2xl font-bold text-slate-900">
                Settings
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                Manage your website settings and configuration.
            </p>
        </div>

        <div class="flex flex-wrap gap-2 border-b border-slate-200 pb-3">

            <a
                href="{{ route('tenant.cms.settings.site', ['subdomain' => $website->subdomain]) }}"
                class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white"
            >
                Site Settings
            </a>

<a
                href="{{ route('tenant.cms.settings.authentication', ['subdomain' => $website->subdomain]) }}"
                class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100 hover:text-slate-900"
            >
                Authentication
            </a>

        </div>

    </div>


    @if(session('success'))
        <div
            class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700"
        >
            {{ session('success') }}
        </div>
    @endif


    @if($errors->any())
        <div
            class="mb-5 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700"
        >
            <div class="font-semibold">
                Please correct the following:
            </div>

            <ul class="mt-2 list-disc space-y-1 pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    <form enctype="multipart/form-data"
        method="POST"
        action="{{ route('tenant.cms.settings.site.update', ['subdomain' => $website->subdomain]) }}"
        class="rounded-2xl border border-slate-200 bg-white shadow-sm"
    >
        @csrf

        <div class="border-b border-slate-200 px-6 py-5">

            <h2 class="text-lg font-bold text-slate-900">
                Site Settings
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Configure your website identity and regional preferences.
            </p>

        </div>


        <div class="grid gap-6 p-6 md:grid-cols-2">

            <div>

                {{-- ESUBIZ_CORE_SITE_BRANDING_UI_V1 --}}
                <div class="mb-8 grid grid-cols-1 gap-6 lg:grid-cols-2">

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                        <label class="block text-sm font-semibold text-slate-800">
                            Website Logo
                        </label>

                        <p class="mt-1 text-xs leading-5 text-slate-500">
                            Recommended size: 180 × 60 px
                        </p>

                        <div class="mt-4 flex min-h-24 items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white p-4">
                            <img
                                id="coreWebsiteLogoPreview"
                                src="{{ !empty($siteConfig['logo_path']) ? request()->getSchemeAndHttpHost() . '/media/' . implode('/', array_map('rawurlencode', explode('/', ltrim($siteConfig['logo_path'], '/')))) : '' }}"
                                alt="Website logo"
                                class="{{ empty($siteConfig['logo_path']) ? 'hidden ' : '' }}max-h-[60px] max-w-[180px] object-contain"
                            >

                            <span
                                id="coreWebsiteLogoPlaceholder"
                                class="{{ !empty($siteConfig['logo_path']) ? 'hidden ' : '' }}text-sm text-slate-400"
                            >
                                No logo uploaded
                            </span>
                        </div>

                        <input
                            type="file"
                            id="coreWebsiteLogoInput"
                            name="website_logo"
                            accept=".jpg,.jpeg,.png,.webp,.svg"
                            class="mt-4 block w-full rounded-xl border border-slate-300 bg-white p-3 text-sm"
                        >

                        <label class="mt-3 flex items-center gap-2 text-sm text-slate-600">
                            <input
                                type="checkbox"
                                name="remove_website_logo"
                                value="1"
                                class="rounded border-slate-300"
                            >
                            Remove current logo
                        </label>

                        @error('website_logo')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                        <label class="block text-sm font-semibold text-slate-800">
                            Browser Icon
                        </label>

                        <p class="mt-1 text-xs leading-5 text-slate-500">
                            Recommended size: 64 × 64 px or 128 × 128 px
                        </p>

                        <div class="mt-4 flex min-h-24 items-center justify-center rounded-xl border border-dashed border-slate-300 bg-white p-4">
                            <img
                                id="coreWebsiteFaviconPreview"
                                src="{{ !empty($siteConfig['favicon_path']) ? request()->getSchemeAndHttpHost() . '/media/' . implode('/', array_map('rawurlencode', explode('/', ltrim($siteConfig['favicon_path'], '/')))) : '' }}"
                                alt="Browser icon"
                                class="{{ empty($siteConfig['favicon_path']) ? 'hidden ' : '' }}h-16 w-16 object-contain"
                            >

                            <span
                                id="coreWebsiteFaviconPlaceholder"
                                class="{{ !empty($siteConfig['favicon_path']) ? 'hidden ' : '' }}text-sm text-slate-400"
                            >
                                No browser icon uploaded
                            </span>
                        </div>

                        <input
                            type="file"
                            id="coreWebsiteFaviconInput"
                            name="website_favicon"
                            accept=".png,.ico,.jpg,.jpeg,.webp"
                            class="mt-4 block w-full rounded-xl border border-slate-300 bg-white p-3 text-sm"
                        >

                        <label class="mt-3 flex items-center gap-2 text-sm text-slate-600">
                            <input
                                type="checkbox"
                                name="remove_website_favicon"
                                value="1"
                                class="rounded border-slate-300"
                            >
                            Remove current browser icon
                        </label>

                        @error('website_favicon')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                </div>

                <label
                    for="website_name"
                    class="mb-2 block text-sm font-semibold text-slate-700"
                >
                    Website Name
                </label>

                <input
                    id="website_name"
                    name="website_name"
                    type="text"
                    maxlength="150"
                    required
                    value="{{ old('website_name', $siteConfig['website_name']) }}"
                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-slate-500 focus:ring-2 focus:ring-slate-200"
                >
            </div>


            <div>
                <label
                    for="timezone"
                    class="mb-2 block text-sm font-semibold text-slate-700"
                >
                    Timezone
                </label>

                <select
                    id="timezone"
                    name="timezone"
                    required
                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-slate-500 focus:ring-2 focus:ring-slate-200"
                >
                    @foreach($timezones as $timezone)
                        <option
                            value="{{ $timezone }}"
                            @selected(
                                old(
                                    'timezone',
                                    $siteConfig['timezone']
                                ) === $timezone
                            )
                        >
                            {{ $timezone }}
                        </option>
                    @endforeach
                </select>
            </div>


            <div>
                <label
                    for="language"
                    class="mb-2 block text-sm font-semibold text-slate-700"
                >
                    Language
                </label>

                <select
                    id="language"
                    name="language"
                    required
                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-slate-500 focus:ring-2 focus:ring-slate-200"
                >
                    @foreach($languages as $code => $label)
                        <option
                            value="{{ $code }}"
                            @selected(
                                old(
                                    'language',
                                    $siteConfig['language']
                                ) === $code
                            )
                        >
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>


            <div>
                <label
                    for="currency"
                    class="mb-2 block text-sm font-semibold text-slate-700"
                >
                    Currency
                </label>

                <select
                    id="currency"
                    name="currency"
                    required
                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-slate-500 focus:ring-2 focus:ring-slate-200"
                >
                    @foreach($currencies as $currency)
                        @php
                            $code =
                                $currency['code']
                                ?? '';

                            $name =
                                $currency['currency']
                                ?? $code;

                            $symbol =
                                $currency['symbol']
                                ?? '';
                        @endphp

                        @if($code)
                            <option
                                value="{{ $code }}"
                                @selected(
                                    old(
                                        'currency',
                                        $siteConfig['currency']
                                    ) === $code
                                )
                            >
                                {{ $code }}
                                @if($symbol)
                                    ({{ $symbol }})
                                @endif
                                — {{ $name }}
                            </option>
                        @endif
                    @endforeach
                </select>
            </div>


            <div>
                <label
                    for="date_format"
                    class="mb-2 block text-sm font-semibold text-slate-700"
                >
                    Date Format
                </label>

                <select
                    id="date_format"
                    name="date_format"
                    required
                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-slate-500 focus:ring-2 focus:ring-slate-200"
                >
                    @foreach($dateFormats as $format => $label)
                        <option
                            value="{{ $format }}"
                            @selected(
                                old(
                                    'date_format',
                                    $siteConfig['date_format']
                                ) === $format
                            )
                        >
                            {{ $label }}
                            — {{ now()->format($format) }}
                        </option>
                    @endforeach
                </select>
            </div>

        </div>


        <div
            class="flex justify-end border-t border-slate-200 bg-slate-50 px-6 py-4"
        >
            <button
                type="submit"
                class="rounded-xl bg-slate-900 px-5 py-2.5 text-sm font-bold text-white transition hover:bg-slate-700"
            >
                Save Settings
            </button>
        </div>

    </form>

</div>


<script>
document.addEventListener('DOMContentLoaded', function () {
    function bindCoreBrandPreview(
        inputId,
        previewId,
        placeholderId
    ) {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        const placeholder =
            document.getElementById(placeholderId);

        if (!input || !preview) {
            return;
        }

        input.addEventListener('change', function () {
            const file = input.files && input.files[0];

            if (!file) {
                return;
            }

            const reader = new FileReader();

            reader.onload = function (event) {
                preview.src = event.target.result;
                preview.classList.remove('hidden');

                if (placeholder) {
                    placeholder.classList.add('hidden');
                }
            };

            reader.readAsDataURL(file);
        });
    }

    bindCoreBrandPreview(
        'coreWebsiteLogoInput',
        'coreWebsiteLogoPreview',
        'coreWebsiteLogoPlaceholder'
    );

    bindCoreBrandPreview(
        'coreWebsiteFaviconInput',
        'coreWebsiteFaviconPreview',
        'coreWebsiteFaviconPlaceholder'
    );
});
</script>

@endsection
