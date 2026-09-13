@props([
    'countryField' => 'country_code',
    'phoneField' => 'phone',
    'selectedCountry' => '',
    'phoneValue' => '',
    'allowedCountries' => ['ALL'],
    'layout' => 'stacked',
])


{{-- ESUBIZ_IP_DETECTED_COUNTRY_DEFAULT_V6 --}}
@php
    /*
     * Selection priority:
     *
     * 1. old('country') after validation failure
     * 2. explicitly supplied component country value
     * 3. globally resolved visitor country
     * 4. NG fallback
     *
     * For anonymous visitors, the global visitor country is
     * supplied by IP detection. If the visitor manually changes
     * the select field, the browser-submitted country naturally
     * overrides this default.
     */
    $resolvedCountryCode = strtoupper(
        trim(
            (string) (
                old('country')
                ?: (
                    isset($country)
                    && is_scalar($country)
                        ? $country
                        : null
                )
                ?: (
                    isset($countryCode)
                    && is_scalar($countryCode)
                        ? $countryCode
                        : null
                )
                ?: (
                    isset($value)
                    && is_scalar($value)
                        ? $value
                        : null
                )
                ?: (
                    isset($esubizVisitorCountry)
                        ? $esubizVisitorCountry
                        : null
                )
                ?: 'NG'
            )
        )
    );
@endphp

@php
    /*
     * ESUBIZ_GLOBAL_COUNTRY_PHONE_FIELD_V1
     *
     * Shared country + phone identity fields.
     *
     * Country is the authority.
     * Phone remains digits-only.
     * Dial code is derived from the selected country.
     *
     * Nigeria is the platform default, but never a restriction.
     */

    /*
     * ESUBIZ_OFFSERVER_INSTALLER_COUNTRY_DETECTION_V1
     *
     * If the caller leaves the country empty, use the globally
     * resolved visitor country. Explicit/profile/browser-submitted
     * values remain authoritative.
     */
    $explicitSelectedCountry = strtoupper(
        trim(
            (string) old(
                $countryField,
                $selectedCountry
            )
        )
    );

    $selectedCountry =
        $explicitSelectedCountry !== ''
            ? $explicitSelectedCountry
            : $resolvedCountryCode;

    /*
     * ESUBIZ_CORE_COUNTRY_FINAL_FALLBACK_V2
     */
    if ($selectedCountry === '') {
        $selectedCountry = 'NG';
    }

    $phoneValue = (string) old(
        $phoneField,
        $phoneValue
    );

    if (!is_array($allowedCountries)) {
        $allowedCountries = ['ALL'];
    }

    $allowAllCountries = in_array(
        'ALL',
        $allowedCountries,
        true
    );

    try {
        $countryPhoneOptions = app(
            \App\Services\Core\CorePhoneCountryCatalog::class
        )->all();
    } catch (\Throwable $e) {
        $countryPhoneOptions = [
            [
                'country_code' => 'NG',
                'country' => 'Nigeria',
                'dial_code' => '+234',
            ],
        ];
    }

    if (!$allowAllCountries) {
        $countryPhoneOptions = array_values(
            array_filter(
                $countryPhoneOptions,
                fn (array $country): bool =>
                    in_array(
                        $country['country_code'],
                        $allowedCountries,
                        true
                    )
            )
        );
    }

    $selectedDialCode = '+234';

    foreach ($countryPhoneOptions as $country) {
        if ($country['country_code'] === $selectedCountry) {
            $selectedDialCode = $country['dial_code'];
            break;
        }
    }

    $fieldId = 'country-phone-'
        . preg_replace(
            '/[^a-z0-9]+/i',
            '-',
            $countryField . '-' . $phoneField
        )
        . '-'
        . substr(md5($countryField . $phoneField), 0, 8);
@endphp

<div
    data-esubiz-country-phone
    id="{{ $fieldId }}"
    @class([
        'grid gap-4',
        'md:grid-cols-2' => $layout === 'two-column',
        'grid-cols-1' => $layout !== 'two-column',
    ])
>
    <div>
        <label
            for="{{ $fieldId }}-country"
            class="mb-2 block font-medium text-slate-700"
        >
            Country
        </label>

        <select
            id="{{ $fieldId }}-country"
            name="{{ $countryField }}"
            data-country-select
            class="w-full rounded-xl border border-slate-300 px-5 py-4"
        >
            @foreach($countryPhoneOptions as $country)
                <option
                    value="{{ $country['country_code'] }}"
                    data-dial-code="{{ $country['dial_code'] }}"
                    @selected(
                        $selectedCountry
                        === $country['country_code']
                    )
                >
                    {{ $country['country'] }}
                </option>
            @endforeach
        </select>
    </div>

    <div @class([
        'mt-4' => $layout !== 'two-column',
    ])>
        <label
            for="{{ $fieldId }}-phone"
            class="mb-2 block font-medium text-slate-700"
        >
            Phone Number
        </label>

        <div class="esubiz-core-phone-control-v82 flex">
            <span
                data-dial-code-display
                class="esubiz-core-phone-dial-v82 inline-flex items-center rounded-l-xl border border-r-0 border-slate-300 bg-slate-50 px-4 text-slate-600"
            >
                {{ $selectedDialCode }}
            </span>

            <input
                id="{{ $fieldId }}-phone"
                type="text"
                name="{{ $phoneField }}"
                value="{{ $phoneValue }}"
                inputmode="numeric"
                pattern="[0-9]*"
                autocomplete="tel-national"
                oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                class="esubiz-core-phone-input-v82 min-w-0 w-full rounded-r-xl border border-slate-300 px-5 py-4"
            >
        </div>
    </div>
</div>

@once
<style>
/*
 * ESUBIZ_CORE_COUNTRY_PHONE_LAYOUT_V82
 *
 * Authoritative phone control layout.
 * Do not rely solely on utility CSS because public themes/auth
 * pages may define their own generic input/span/flex rules.
 */

/*
 * ESUBIZ_CORE_COUNTRY_SELECT_MOBILE_V84
 *
 * Keep Country at normal full-field size on narrow/mobile layouts.
 */
[data-esubiz-country-phone]
[data-country-select]{
    display:block !important;
    width:100% !important;
    max-width:100% !important;
    min-width:0 !important;
    height:52px !important;
    min-height:52px !important;
    padding-top:0 !important;
    padding-bottom:0 !important;
    padding-left:16px !important;
    padding-right:44px !important;
    margin:0 !important;
    box-sizing:border-box !important;
    font-size:16px !important;
    line-height:normal !important;
}
[data-esubiz-country-phone]
.esubiz-core-phone-control-v82{
    display:flex !important;
    align-items:stretch !important;
    width:100% !important;
    flex-direction:row !important;
}

[data-esubiz-country-phone]
.esubiz-core-phone-dial-v82{
    display:inline-flex !important;
    align-items:center !important;
    justify-content:center !important;
    flex:0 0 auto !important;
    width:auto !important;
    min-width:72px !important;
    margin:0 !important;
    white-space:nowrap !important;
    border-right:0 !important;
    border-top-right-radius:0 !important;
    border-bottom-right-radius:0 !important;
    box-sizing:border-box !important;
}

[data-esubiz-country-phone]
.esubiz-core-phone-input-v82{
    display:block !important;
    flex:1 1 auto !important;
    min-width:0 !important;
    width:1% !important;
    margin:0 !important;
    border-top-left-radius:0 !important;
    border-bottom-left-radius:0 !important;
    box-sizing:border-box !important;
}

[data-esubiz-country-phone]
.esubiz-core-phone-control-v82
> .esubiz-core-phone-dial-v82
+ .esubiz-core-phone-input-v82{
    margin-left:0 !important;
}
</style>

<script>
document.addEventListener('change', function (event) {
    const select = event.target.closest(
        '[data-esubiz-country-phone] [data-country-select]'
    );

    if (!select) {
        return;
    }

    const wrapper = select.closest(
        '[data-esubiz-country-phone]'
    );

    const dialDisplay = wrapper?.querySelector(
        '[data-dial-code-display]'
    );

    const option = select.options[
        select.selectedIndex
    ];

    if (dialDisplay && option) {
        dialDisplay.textContent =
            option.dataset.dialCode || '';
    }
});
</script>
@endonce
