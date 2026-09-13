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


        {{-- ESUBIZ_CORE_SITE_SETTINGS_GROUPED_LAYOUT_V5 --}}
        <div class="p-6">

            {{-- ESUBIZ_CORE_SITE_SECONDARY_CURRENCY_UI_V1 --}}
            @php
                $coreSecondaryEnabled =
                    (bool) old(
                        'secondary_currency_enabled',
                        $siteCurrencySettings['secondary']['enabled']
                            ?? false
                    );

                $coreSecondaryCurrency =
                    strtoupper(
                        (string) old(
                            'secondary_currency',
                            $siteCurrencySettings['secondary']['currency']
                                ?? ''
                        )
                    );

                $coreAutomaticConversion =
                    (bool) old(
                        'currency_automatic_conversion',
                        $siteCurrencySettings['conversion']['automatic']
                            ?? true
                    );

                $coreManualRate =
                    old(
                        'currency_manual_rate',
                        $siteCurrencySettings['conversion']['manual_rate']
                            ?? ''
                    );

                $coreMarginType =
                    old(
                        'currency_margin_type',
                        $siteCurrencySettings['margin']['type']
                            ?? 'none'
                    );

                $coreMarginValue =
                    old(
                        'currency_margin_value',
                        $siteCurrencySettings['margin']['value']
                            ?? 0
                    );
            @endphp

            {{-- ROW 1: WEBSITE IDENTITY / DISPLAY --}}
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

                <div>
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

            </div>


            {{-- ROW 2: REGIONAL SETTINGS --}}
            <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">

                {{-- ESUBIZ_CORE_SITE_REGIONAL_FIELDS_V2 --}}
                <div>
                    <label
                        for="default_country_code"
                        class="mb-2 block text-sm font-semibold text-slate-700"
                    >
                        Country
                    </label>

                    <select
                    id="default_country_code"
                    name="default_country_code"
                    required
                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-slate-500 focus:ring-2 focus:ring-slate-200"
                >
                    @foreach($phoneCountries as $country)
                        <option
                            value="{{ $country['country_code'] }}"
                            @selected(
                                old(
                                    'default_country_code',
                                    $siteConfig['default_country_code']
                                ) === $country['country_code']
                            )
                        >
                            {{ $country['country'] }}
                        </option>
                    @endforeach
                </select>

                    <p class="mt-2 text-xs text-slate-500">
                        Automatically detected when no country has been saved.
                    </p>
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

                    <p class="mt-2 text-xs text-slate-500">
                        Suggested from Country. You can select another timezone.
                    </p>
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

            </div>


            {{-- ROW 3: CONTACT / SECONDARY CURRENCY --}}
            <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">

                <div>
                    <label
                        for="site_email"
                        class="mb-2 block text-sm font-semibold text-slate-700"
                    >
                        Site Email
                    </label>

                    <input
                        id="site_email"
                        name="site_email"
                        type="email"
                        maxlength="254"
                        autocomplete="email"
                        value="{{ old('site_email', $siteConfig['site_email'] ?? '') }}"
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-slate-500 focus:ring-2 focus:ring-slate-200"
                        placeholder="Enter site email"
                    >

                    <p class="mt-2 text-xs text-slate-500">
                        Public contact email for this website.
                    </p>

                    @error('site_email')
                        <p class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>


                {{-- ESUBIZ_CORE_SITE_PHONE_CONTROL_V3 --}}
                <div>
                    <label
                        for="phone_number"
                        class="mb-2 block text-sm font-semibold text-slate-700"
                    >
                        Phone Number
                    </label>

                    @php
                        $selectedSiteCountryCode =
                            old(
                                'default_country_code',
                                $siteConfig['default_country_code']
                            );

                        $selectedSiteDialCode = '+234';

                        foreach ($phoneCountries as $country) {
                            if (
                                $country['country_code']
                                === $selectedSiteCountryCode
                            ) {
                                $selectedSiteDialCode =
                                    $country['dial_code'];

                                break;
                            }
                        }
                    @endphp

                    <div class="flex">
                        <span
                            id="core_site_phone_code"
                            data-site-dial-code
                            class="inline-flex min-w-[96px] items-center justify-center rounded-l-xl border border-r-0 border-slate-300 bg-slate-50 px-4 text-sm font-semibold text-slate-700"
                        >
                            {{ $selectedSiteDialCode }}
                        </span>

                        <input
                            id="phone_number"
                            name="phone_number"
                            type="text"
                            inputmode="numeric"
                            pattern="[0-9]*"
                            autocomplete="tel-national"
                            maxlength="30"
                            value="{{ old('phone_number', $siteConfig['phone_number'] ?? '') }}"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                            class="min-w-0 w-full rounded-r-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-slate-500 focus:ring-2 focus:ring-slate-200"
                            placeholder="Enter phone number"
                        >
                    </div>

                    <p class="mt-2 text-xs text-slate-500">
                        Phone code is derived automatically from Country.
                    </p>
                </div>


                {{-- ESUBIZ_CORE_SITE_SECONDARY_MASTER_TOGGLE_V4 --}}
                {{-- ESUBIZ_CORE_SITE_PREMIUM_TOGGLE_V5 --}}
                <div>
                    <label
                        for="secondary_currency_enabled"
                        class="mb-2 block text-sm font-semibold text-slate-700"
                    >
                        Secondary Currency
                    </label>

                    <div
                        class="flex min-h-[48px] items-center justify-between rounded-xl border border-slate-300 bg-white px-4 py-3"
                    >
                        <span class="text-sm text-slate-600">
                            Enable secondary currency
                        </span>

                        <label
                            class="relative inline-flex shrink-0 cursor-pointer items-center"
                        >
                            <input
                                id="secondary_currency_enabled"
                                type="checkbox"
                                name="secondary_currency_enabled"
                                value="1"
                                @checked($coreSecondaryEnabled)
                                class="peer sr-only"
                            >

                            <span
                                class="h-6 w-11 rounded-full bg-slate-300 transition-colors duration-200 peer-checked:bg-blue-600 peer-focus:ring-2 peer-focus:ring-blue-200"
                            ></span>

                            <span
                                class="pointer-events-none absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow-sm transition-transform duration-200 peer-checked:translate-x-5"
                            ></span>
                        </label>
                    </div>

                    <p class="mt-2 text-xs text-slate-500">
                        Optional second currency for customer pricing.
                    </p>
                </div>

            </div>


            {{-- SECONDARY CURRENCY EXPANDED SETTINGS --}}
            <div
                id="coreSecondaryCurrencySettings"
                class="{{ $coreSecondaryEnabled ? 'mt-6' : 'hidden mt-6' }}"
            >
                <div
                    class="rounded-2xl border border-slate-200 bg-slate-50 p-5"
                >
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

                        <div>
                            <label
                                for="secondary_currency"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                            >
                                Secondary Currency
                            </label>

                            <select
                        id="secondary_currency"
                        name="secondary_currency"
                        class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-slate-500 focus:ring-2 focus:ring-slate-200"
                    >
                        <option value="">
                            Select secondary currency
                        </option>

                        @foreach($currencies as $secondaryCurrencyOption)
                            @php
                                $secondaryCode =
                                    strtoupper(
                                        (string) (
                                            $secondaryCurrencyOption['code']
                                            ?? ''
                                        )
                                    );

                                $secondaryName =
                                    $secondaryCurrencyOption['currency']
                                    ?? $secondaryCode;

                                $secondarySymbol =
                                    $secondaryCurrencyOption['symbol']
                                    ?? '';
                            @endphp

                            @if($secondaryCode)
                                <option
                                    value="{{ $secondaryCode }}"
                                    @selected(
                                        $coreSecondaryCurrency
                                        === $secondaryCode
                                    )
                                >
                                    {{ $secondaryCode }}
                                    @if($secondarySymbol)
                                        ({{ $secondarySymbol }})
                                    @endif
                                    — {{ $secondaryName }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                        </div>


                        <div>
                            <label
                                class="mb-2 block text-sm font-semibold text-slate-700"
                            >
                                Conversion
                            </label>

                            <div
                                class="flex min-h-[48px] items-center justify-between rounded-xl border border-slate-300 bg-white px-4 py-3"
                            >
                                <span class="text-sm text-slate-600">
                                    Automatic conversion
                                </span>

                                <label
                                    class="relative inline-flex shrink-0 cursor-pointer items-center"
                                >
                                    <input
                                        id="currency_automatic_conversion"
                                        type="checkbox"
                                        name="currency_automatic_conversion"
                                        value="1"
                                        @checked($coreAutomaticConversion)
                                        class="peer sr-only"
                                    >

                                    <span
                                        class="h-6 w-11 rounded-full bg-slate-300 transition-colors duration-200 peer-checked:bg-blue-600 peer-focus:ring-2 peer-focus:ring-blue-200"
                                    ></span>

                                    <span
                                        class="pointer-events-none absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow-sm transition-transform duration-200 peer-checked:translate-x-5"
                                    ></span>
                                </label>
                            </div>
                        </div>


                        <div
                            id="coreManualRateField"
                            class="{{ $coreAutomaticConversion ? 'hidden' : '' }}"
                        >
                            <label
                                for="currency_manual_rate"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                            >
                                Manual Exchange Rate
                            </label>

                            <input
                                id="currency_manual_rate"
                                name="currency_manual_rate"
                                type="number"
                                min="0.00000001"
                                step="0.00000001"
                                value="{{ $coreManualRate }}"
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-slate-500 focus:ring-2 focus:ring-slate-200"
                                placeholder="Enter exchange rate"
                            >
                        </div>


                        <div
                            id="coreCurrencyMarkupTypeField"
                        >
                            <label
                                for="currency_margin_type"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                            >
                                Markup Type
                            </label>

                            <select
                                id="currency_margin_type"
                                name="currency_margin_type"
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-slate-500 focus:ring-2 focus:ring-slate-200"
                            >
                                <option
                                    value="none"
                                    @selected($coreMarginType === 'none')
                                >
                                    No markup
                                </option>

                                <option
                                    value="percentage"
                                    @selected($coreMarginType === 'percentage')
                                >
                                    Percentage
                                </option>

                                <option
                                    value="fixed"
                                    @selected($coreMarginType === 'fixed')
                                >
                                    Fixed amount
                                </option>
                            </select>
                        </div>


                        <div
                            id="coreCurrencyMarkupValueField"
                        >
                            <label
                                for="currency_margin_value"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                            >
                                Markup Value
                            </label>

                            <input
                                id="currency_margin_value"
                                name="currency_margin_value"
                                type="number"
                                min="0"
                                step="0.01"
                                value="{{ $coreMarginValue }}"
                                class="w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm text-slate-900 outline-none focus:border-slate-500 focus:ring-2 focus:ring-slate-200"
                            >
                        </div>

                    </div>
                </div>
            </div>


            {{-- BRANDING AT BOTTOM --}}
            <div class="mt-8 border-t border-slate-200 pt-8">
                <div class="mb-5">
                    <h3 class="text-sm font-bold text-slate-900">
                        Website Branding
                    </h3>

                    <p class="mt-1 text-xs text-slate-500">
                        Manage the website logo and browser icon.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    {{-- ESUBIZ_CORE_SITE_BRANDING_UI_V1 --}}
                <div class="contents">

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
                </div>
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


    /*
     * ESUBIZ_CORE_SITE_REGIONAL_SYNC_V2
     *
     * Country supplies smart defaults only.
     *
     * Automatic/manual Country selection updates:
     * - non-editable Phone Code
     * - Timezone
     * - Currency
     *
     * Phone Code always follows Country.
     * Timezone and Currency remain independently selectable
     * after their country defaults are applied.
     */
    const siteCountry =
        document.getElementById('default_country_code');

    const sitePhoneCode =
        document.getElementById('core_site_phone_code');

    const siteTimezone =
        document.getElementById('timezone');

    const siteCurrency =
        document.getElementById('currency');

    const siteRegionalMap =
        @json($siteCountryRegionalMap ?? []);

    function selectExistingOption(select, value) {
        if (!select || !value) {
            return;
        }

        const exists =
            Array.from(select.options).some(
                option => option.value === value
            );

        if (exists) {
            select.value = value;
        }
    }

    function applyCountryRegionalDefaults() {
        if (!siteCountry) {
            return;
        }

        const countryCode =
            String(siteCountry.value || '')
                .trim()
                .toUpperCase();

        const regional =
            siteRegionalMap[countryCode] || {};

        if (sitePhoneCode) {
            sitePhoneCode.textContent =
                regional.dial_code || '';
        }

        const countryTimezones =
            Array.isArray(regional.timezones)
                ? regional.timezones
                : [];

        if (countryTimezones.length) {
            selectExistingOption(
                siteTimezone,
                countryTimezones[0]
            );
        }

        selectExistingOption(
            siteCurrency,
            regional.currency || ''
        );
    }

    if (siteCountry) {
        /*
         * Server-side rendering already preserves saved/manual
         * values on initial load. Only a Country CHANGE reapplies
         * the country's smart defaults.
         */
        siteCountry.addEventListener(
            'change',
            applyCountryRegionalDefaults
        );
    }


    /*
     * ESUBIZ_CORE_SITE_SECONDARY_CURRENCY_JS_V1
     */
    const primaryCurrency =
        document.getElementById('currency');

    const secondaryCurrency =
        document.getElementById('secondary_currency');

    const secondaryEnabled =
        document.getElementById(
            'secondary_currency_enabled'
        );

    const secondarySettings =
        document.getElementById(
            'coreSecondaryCurrencySettings'
        );

    const automaticConversion =
        document.getElementById(
            'currency_automatic_conversion'
        );

    const manualRateField =
        document.getElementById(
            'coreManualRateField'
        );

    /*
     * ESUBIZ_CORE_SITE_MANUAL_RATE_NO_MARKUP_UI_V3
     */
    const markupTypeField =
        document.getElementById(
            'coreCurrencyMarkupTypeField'
        );

    const markupValueField =
        document.getElementById(
            'coreCurrencyMarkupValueField'
        );

    const markupType =
        document.getElementById(
            'currency_margin_type'
        );

    const markupValue =
        document.getElementById(
            'currency_margin_value'
        );

    function syncSecondaryCurrencyOptions() {
        if (
            !primaryCurrency
            || !secondaryCurrency
        ) {
            return;
        }

        const primary =
            String(primaryCurrency.value || '')
                .toUpperCase();

        Array.from(
            secondaryCurrency.options
        ).forEach(option => {
            if (!option.value) {
                option.disabled = false;
                return;
            }

            option.disabled =
                String(option.value).toUpperCase()
                === primary;
        });

        if (
            secondaryCurrency.value
            && String(
                secondaryCurrency.value
            ).toUpperCase() === primary
        ) {
            secondaryCurrency.value = '';
        }
    }

    function syncSecondaryCurrencySettings() {
        if (
            !secondaryEnabled
            || !secondarySettings
        ) {
            return;
        }

        const isEnabled =
            secondaryEnabled.checked;

        secondarySettings.classList.toggle(
            'hidden',
            !isEnabled
        );

        /*
         * Keep every secondary-currency control inactive while the
         * master switch is disabled. This prevents hidden stale values
         * from being submitted accidentally.
         */
        secondarySettings
            .querySelectorAll(
                'select, input, textarea'
            )
            .forEach(control => {
                control.disabled = !isEnabled;
            });

        if (isEnabled) {
            syncSecondaryCurrencyOptions();
            syncManualRateField();
        }
    }

    function syncManualRateField() {
        if (
            !automaticConversion
            || !manualRateField
        ) {
            return;
        }

        if (
            secondaryEnabled
            && !secondaryEnabled.checked
        ) {
            return;
        }

        const isAutomatic =
            automaticConversion.checked;

        /*
         * Automatic conversion:
         * - manual rate hidden
         * - markup available
         *
         * Manual conversion:
         * - manual rate visible
         * - markup unavailable
         */
        manualRateField.classList.toggle(
            'hidden',
            isAutomatic
        );

        if (markupTypeField) {
            markupTypeField.classList.toggle(
                'hidden',
                !isAutomatic
            );
        }

        if (markupValueField) {
            markupValueField.classList.toggle(
                'hidden',
                !isAutomatic
            );
        }

        if (markupType) {
            markupType.disabled =
                !isAutomatic;
        }

        if (markupValue) {
            markupValue.disabled =
                !isAutomatic;
        }
    }

    if (primaryCurrency) {
        primaryCurrency.addEventListener(
            'change',
            syncSecondaryCurrencyOptions
        );
    }

    if (secondaryEnabled) {
        secondaryEnabled.addEventListener(
            'change',
            syncSecondaryCurrencySettings
        );
    }

    if (automaticConversion) {
        automaticConversion.addEventListener(
            'change',
            syncManualRateField
        );
    }

    syncSecondaryCurrencyOptions();
    syncSecondaryCurrencySettings();
    syncManualRateField();
});
</script>

@endsection
