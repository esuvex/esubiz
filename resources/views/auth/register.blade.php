<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    @php
        /*
         * ESUBIZ_CENTRAL_PREMIUM_REGISTER_V10
         */

        $auth = $centralAuthUi ?? [];
        $brand = $auth['brand'] ?? [];
        $appearance = $auth['appearance'] ?? [];
        $copy = $auth['copy'] ?? [];

        $siteName =
            $brand['site_name']
            ?? 'Esubiz';

        $logo =
            $brand['logo']
            ?? null;

        $favicon =
            $brand['favicon']
            ?? null;

        $primary =
            $appearance['primary_color']
            ?? '#0b1f3a';

        $accent =
            $appearance['accent_color']
            ?? '#c89b3c';

        $background =
            $appearance['background_color']
            ?? '#f5f7fb';

        $card =
            $appearance['card_color']
            ?? '#ffffff';

        $text =
            $appearance['text_color']
            ?? '#172033';

        $muted =
            $appearance['muted_text_color']
            ?? '#667085';

        $backgroundImage =
            $appearance['background_image']
            ?? null;

        $registerTitle =
            $copy['register_title']
            ?? 'Create your account';

        $registerSubtitle =
            $copy['register_subtitle']
            ?? 'Join Esubiz and start building your digital business.';

        $registerButton =
            $copy['register_button']
            ?? 'Create Account';

        $roleSelectionEnabled =
            isset($centralRegistrationAccess)
                ? $centralRegistrationAccess
                    ->roleSelectionEnabled()
                : true;

        $roles =
            $centralPublicRegistrationRoles
            ?? [];

        $defaultRole =
            old(
                'account_role',
                old(
                    'role',
                    $centralDefaultRegistrationRole
                        ?? 'user'
                )
            );

        $resolvedCountry =
            strtoupper(
                (string) old(
                    'country_code',
                    old(
                        'country',
                        $esubizVisitorCountry
                            ?? 'NG'
                    )
                )
            );

        /*
         * Central registration uses its own compact country
         * catalogue here so it remains independent from
         * tenant/Core website configuration.
         */
        $countries = [
            'NG' => ['Nigeria', '+234'],
            'GH' => ['Ghana', '+233'],
            'US' => ['United States', '+1'],
            'GB' => ['United Kingdom', '+44'],
            'CA' => ['Canada', '+1'],
            'ZA' => ['South Africa', '+27'],
            'KE' => ['Kenya', '+254'],
            'UG' => ['Uganda', '+256'],
            'TZ' => ['Tanzania', '+255'],
            'RW' => ['Rwanda', '+250'],
            'CM' => ['Cameroon', '+237'],
            'CI' => ["Côte d'Ivoire", '+225'],
            'SN' => ['Senegal', '+221'],
            'AE' => ['United Arab Emirates', '+971'],
            'SA' => ['Saudi Arabia', '+966'],
            'IN' => ['India', '+91'],
            'CN' => ['China', '+86'],
            'DE' => ['Germany', '+49'],
            'FR' => ['France', '+33'],
            'IT' => ['Italy', '+39'],
            'ES' => ['Spain', '+34'],
            'NL' => ['Netherlands', '+31'],
            'BE' => ['Belgium', '+32'],
            'AU' => ['Australia', '+61'],
        ];

        if (
            !array_key_exists(
                $resolvedCountry,
                $countries
            )
        ) {
            $resolvedCountry = 'NG';
        }

        $phoneDial =
            old(
                'phone_country_code',
                $countries[$resolvedCountry][1]
                    ?? '+234'
            );
    @endphp

    {{-- ESUBIZ_CENTRAL_SHARED_REGISTER_V12 --}}
    @php
        /*
         * Central auth consumes the shared Esubiz auth
         * presentation source.
         *
         * Central settings remain authoritative.
         */
        /*
         * ESUBIZ_CENTRAL_AUTH_BRAND_CONTEXT_V14
         *
         * Auth branding override -> Central Esubiz branding.
         */
        $authLogo =
            $brand['auth_logo']
            ?? null;

        $defaultLogo =
            $brand['logo']
            ?? null;

        $authFavicon =
            $brand['auth_favicon']
            ?? null;

        $defaultFavicon =
            $brand['favicon']
            ?? null;

        $authSiteName =
            $siteName;

        $authPrimary =
            $primary;

        $authAccent =
            $accent;

        $authBackground =
            $background;

        $authCard =
            $card;

        $authText =
            $text;

        $authMuted =
            $muted;

        $authBackgroundImage =
            $backgroundImage;

        $authProviders =
            $centralAuthEnabledProviders
            ?? [];

        $authProviderUrlResolver =
            static function (
                string $provider
            ) {
                return url(
                    '/auth/'
                    . $provider
                    . '/redirect'
                );
            };

        $authProviderDividerText =
            'or register with email';
    @endphp


    <title>
        {{ $registerTitle }} · {{ $siteName }}
    </title>
    @include('shared.auth.favicon')


    @include('shared.auth.styles')

    <style>
        /*
         * ESUBIZ_CENTRAL_REGISTER_MOBILE_SELECT_HEIGHT_V19
         *
         * Prevent native select controls from collapsing below
         * the normal 52px Esubiz field height on small devices.
         */
        @media (max-width: 640px) {
            select[name="country_code"],
            select[name="country"],
            select[name="account_role"],
            select[name="account_type"] {
                display: block !important;
                width: 100% !important;
                height: 52px !important;
                min-height: 52px !important;
                max-height: 52px !important;
                padding-top: 0 !important;
                padding-bottom: 0 !important;
                font-size: 16px !important;
                line-height: normal !important;
                appearance: auto;
                -webkit-appearance: menulist;
            }
        }

        @media (max-width: 360px) {
            select[name="country_code"],
            select[name="country"],
            select[name="account_role"],
            select[name="account_type"] {
                height: 52px !important;
                min-height: 52px !important;
            }
        }
    </style>


<style>
/*
 * ESUBIZ_CENTRAL_CORE_AUTH_VISUAL_V20
 *
 * Central follows Core's auth presentation:
 * - branding inside card
 * - provider icons inside card
 * - email divider
 * - consistent mobile controls
 * - Back to Homepage
 */

.esubiz-core-parity-brand {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    margin: 0 auto 28px;
}

.esubiz-core-parity-brand .esubiz-auth-brand,
.esubiz-core-parity-brand > a,
.esubiz-core-parity-brand > div {
    margin: 0 auto !important;
}

.esubiz-core-parity-brand img,
.esubiz-core-parity-brand .esubiz-auth-brand-logo {
    display: block;
    width: auto;
    height: auto;
    max-width: 180px;
    max-height: 60px;
    margin: 0 auto;
    object-fit: contain;
}

.esubiz-core-provider-section {
    width: 100%;
    margin: 30px 0 27px;
    text-align: center;
}

.esubiz-core-provider-title {
    margin-bottom: 15px;
    color: #142033;
    font-size: 14px;
    line-height: 1.35;
    font-weight: 700;
}

.esubiz-core-provider-section .auth-providers,
.esubiz-core-provider-section .esubiz-auth-providers,
.esubiz-core-provider-section .provider-list,
.esubiz-core-provider-section .providers {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start;
    justify-content: center;
    gap: 13px;
}

.esubiz-core-provider-section a,
.esubiz-core-provider-section button {
    transition:
        transform .16s ease,
        box-shadow .16s ease,
        border-color .16s ease;
}

.esubiz-core-provider-section a:hover,
.esubiz-core-provider-section button:hover {
    transform: translateY(-1px);
}

.esubiz-core-auth-divider {
    display: flex;
    align-items: center;
    gap: 13px;
    width: 100%;
    margin: 25px 0 0;
    color: #9aa4b5;
    font-size: 11px;
    line-height: 1;
    font-weight: 750;
    letter-spacing: .045em;
    white-space: nowrap;
}

.esubiz-core-auth-divider::before,
.esubiz-core-auth-divider::after {
    content: "";
    flex: 1 1 auto;
    height: 1px;
    background: #e2e7ee;
}

.esubiz-core-back-home {
    width: 100%;
    margin: 23px 0 0;
    text-align: center;
}

.esubiz-core-back-home a {
    color: #667085;
    font-size: 13px;
    line-height: 1.4;
    font-weight: 650;
    text-decoration: none;
}

.esubiz-core-back-home a:hover {
    color: #0b1f3a;
    text-decoration: underline;
}

/*
 * Mobile native-select correction.
 *
 * Match Core's usable control height even on browsers that
 * aggressively shrink native selects.
 */
@media (max-width: 640px) {
    select[name="country"],
    select[name="country_code"],
    select[name="account_type"],
    select[name="account_role"] {
        box-sizing: border-box !important;
        display: block !important;
        width: 100% !important;
        height: 52px !important;
        min-height: 52px !important;
        max-height: none !important;
        padding: 0 42px 0 14px !important;
        border-width: 1px !important;
        font-size: 16px !important;
        line-height: 52px !important;
        vertical-align: middle !important;
    }
}
</style>


<style>
/*
 * ESUBIZ_CENTRAL_REGISTER_NO_LOGO_DIVIDER_V22
 *
 * Registration card now follows Login / Forgot / Reset:
 * no decorative divider beneath the Central Esubiz logo.
 */

.esubiz-auth-card::before,
.esubiz-auth-card:before,
.esubiz-register-card::before,
.esubiz-register-card:before,
.auth-card::before,
.auth-card:before {
    display: none !important;
    content: none !important;
    height: 0 !important;
    border: 0 !important;
    background: none !important;
}

.esubiz-auth-card {
    border-top-color: rgba(223, 229, 236, .9) !important;
}

.esubiz-core-parity-brand {
    border-bottom: 0 !important;
}

.esubiz-core-parity-brand::after,
.esubiz-core-parity-brand:after {
    display: none !important;
    content: none !important;
}
</style>


<style>
/*
 * ESUBIZ_CENTRAL_REGISTER_DIVIDER_REMOVAL_V23
 */

.esubiz-core-parity-brand {
    margin-bottom: 28px !important;
    padding-bottom: 0 !important;
    border: 0 !important;
    background: transparent !important;
}

.esubiz-core-parity-brand + .brand-divider,
.esubiz-core-parity-brand + .auth-divider-line,
.esubiz-core-parity-brand + .accent-line,
.esubiz-core-parity-brand + .top-line,
.esubiz-core-parity-brand + .brand-line,
.esubiz-core-parity-brand + .logo-divider,
.esubiz-core-parity-brand + .card-accent {
    display: none !important;
}

/*
 * Do NOT hide the provider email divider.
 */
.esubiz-core-auth-divider {
    display: flex !important;
}
</style>


<style>
/*
 * ESUBIZ_CENTRAL_REGISTER_LOGIN_PARITY_V24
 *
 * Registration uses the same card geometry as Central Login,
 * Forgot Password and Reset Password.
 */

.esubiz-auth-page {
    display: grid !important;
    place-items: center !important;
    padding: 34px 18px !important;
}

.esubiz-auth-wrap {
    width: min(100%, 655px) !important;
    margin: 0 auto !important;
}

.esubiz-auth-card {
    width: 100% !important;
    padding: 48px 48px 38px !important;
    overflow: hidden;
}

.esubiz-core-parity-brand {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;

    width: 100% !important;

    margin:
        0
        auto
        30px
        !important;

    padding: 0 !important;

    border: 0 !important;
}

.esubiz-core-parity-brand .esubiz-auth-brand,
.esubiz-core-parity-brand > a {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;

    min-height: 0 !important;

    margin: 0 auto !important;
    padding: 0 !important;
}

.esubiz-core-parity-brand img,
.esubiz-core-parity-brand .esubiz-auth-brand-logo {
    display: block !important;

    width: auto !important;
    height: auto !important;

    max-width: 180px !important;
    max-height: 60px !important;

    margin: 0 auto !important;

    object-fit: contain !important;
}


/*
 * Same heading placement as Login.
 */
.esubiz-auth-heading {
    margin:
        0
        0
        29px
        !important;

    text-align: center !important;
}

.esubiz-auth-title {
    text-align: center !important;
}

.esubiz-auth-description {
    max-width: 470px !important;

    margin-left: auto !important;
    margin-right: auto !important;

    text-align: center !important;
}


/*
 * The registration form must remain inside the card's
 * horizontal padding.
 */
.esubiz-auth-form {
    width: 100% !important;
    max-width: 100% !important;

    margin:
        0
        auto
        !important;
}


/*
 * Do not allow Create Account to span the physical card edge.
 * Its 100% width now means 100% of the padded form area,
 * exactly like Sign In.
 */
.esubiz-auth-form button[type="submit"],
.esubiz-auth-form .esubiz-auth-submit {
    display: flex !important;

    width: 100% !important;
    max-width: 100% !important;

    margin:
        22px
        0
        0
        !important;
}


/*
 * Footer links follow the same order/spacing as Login:
 *
 * Already have an account?
 * Back to Homepage
 */
.esubiz-auth-bottom {
    margin:
        21px
        0
        0
        !important;

    text-align: center !important;
}

.esubiz-core-back-home {
    width: 100% !important;

    margin:
        24px
        0
        0
        !important;

    text-align: center !important;
}


/*
 * Agreement rows are part of the form content and must not
 * alter the card geometry.
 */
.esubiz-registration-agreements {
    width: 100% !important;
    max-width: 100% !important;

    margin:
        20px
        0
        0
        !important;
}


/*
 * Keep the removed logo divider removed.
 */
.esubiz-core-parity-brand::before,
.esubiz-core-parity-brand::after,
.esubiz-auth-card::before {
    display: none !important;

    content: none !important;
}


@media (max-width: 640px) {

    .esubiz-auth-page {
        padding:
            22px
            13px
            !important;
    }

    .esubiz-auth-wrap {
        width: 100% !important;
    }

    .esubiz-auth-card {
        padding:
            30px
            20px
            27px
            !important;
    }

    .esubiz-core-parity-brand {
        margin-bottom: 25px !important;
    }
}


@media (max-width: 360px) {

    .esubiz-auth-card {
        padding-left: 16px !important;
        padding-right: 16px !important;
    }
}
</style>


<style>
/*
 * ESUBIZ_CENTRAL_BACK_HOME_STRUCTURAL_V25
 */

.esubiz-auth-card > .esubiz-core-back-home {
    width: 100% !important;
    margin: 24px 0 0 !important;
    padding: 0 !important;
    text-align: center !important;
}

.esubiz-auth-card > .esubiz-core-back-home a {
    display: inline-block;
}
</style>

</head>

<body class="esubiz-auth-body">
<div class="esubiz-auth-page">
    <main class="esubiz-auth-shell">

        {{-- ESUBIZ_CENTRAL_REGISTER_PARSE_FIX_V13 --}}


        <section class="esubiz-auth-card">
        {{-- ESUBIZ_CENTRAL_CORE_AUTH_PARITY_V20 --}}
        <div class="esubiz-core-parity-brand">
            @include('shared.auth.logo')
        </div>



            <div class="esubiz-auth-card-body">

                <header class="esubiz-auth-heading">
                    <h1>{{ $registerTitle }}</h1>
                    <p>{{ $registerSubtitle }}</p>
            {{-- ESUBIZ_CENTRAL_PROVIDER_AREA_V20 --}}
            @php
                $esubizCentralAuthProviders =
                    app(\App\Services\Platform\CentralAuthUiService::class)
                        ->enabledProviders();
            @endphp

            @if(!empty($esubizCentralAuthProviders))
                <div class="esubiz-core-provider-section">
                    <div class="esubiz-core-provider-title">
                        Register with
                    </div>

                    @include('shared.auth.providers', [
                        'providers' => $esubizCentralAuthProviders,
                    ])

                    <div class="esubiz-core-auth-divider">
                        <span>
                            OR CONTINUE WITH EMAIL
                        </span>
                    </div>
                </div>
            @endif

                </header>

                @include('shared.auth.providers')

                <form
                    method="POST"
                    action="{{ route('register') }}"
                    autocomplete="on"
                >
                    @csrf

                    <div class="esubiz-auth-grid">

                        <div class="esubiz-auth-field esubiz-auth-field-full">
                            <label class="esubiz-auth-label" for="name">
                                Full Name
                                <span class="esubiz-auth-required">*</span>
                            </label>

                            <input
                                id="name"
                                class="esubiz-auth-control"
                                type="text"
                                name="name"
                                value="{{ old('name') }}"
                                required
                                autofocus
                                autocomplete="name"
                            >

                            @error('name')
                                <div class="esubiz-auth-error">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="esubiz-auth-field esubiz-auth-field-full">
                            <label class="esubiz-auth-label" for="email">
                                Email Address
                                <span class="esubiz-auth-required">*</span>
                            </label>

                            <input
                                id="email"
                                class="esubiz-auth-control"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                autocomplete="email"
                            >

                            @error('email')
                                <div class="esubiz-auth-error">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="esubiz-auth-field">
                            <label class="esubiz-auth-label" for="country_code">
                                Country
                                <span class="esubiz-auth-required">*</span>
                            </label>

                            <select
                                id="country_code"
                                class="esubiz-auth-select"
                                name="country_code"
                                required
                            >
                                @foreach(
                                    $countries
                                    as $code => $country
                                )
                                    <option
                                        value="{{ $code }}"
                                        data-dial="{{ $country[1] }}"
                                        @selected(
                                            $resolvedCountry
                                                === $code
                                        )
                                    >
                                        {{ $country[0] }}
                                    </option>
                                @endforeach
                            </select>

                            @error('country_code')
                                <div class="esubiz-auth-error">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="esubiz-auth-field">
                            <label class="esubiz-auth-label" for="phone_number">
                                Phone Number
                                <span class="esubiz-auth-required">*</span>
                            </label>

                            <div class="esubiz-auth-phone">
                                <input
                                    id="phone_country_code"
                                    class="esubiz-auth-control"
                                    type="text"
                                    name="phone_country_code"
                                    value="{{ $phoneDial }}"
                                    readonly
                                    aria-label="Phone country code"
                                >

                                <input
                                    id="phone_number"
                                    class="esubiz-auth-control"
                                    type="tel"
                                    name="phone_number"
                                    value="{{ old('phone_number') }}"
                                    required
                                    autocomplete="tel"
                                >
                            </div>

                            @error('phone_number')
                                <div class="esubiz-auth-error">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        @if($roleSelectionEnabled)
                            <div class="esubiz-auth-field esubiz-auth-field-full">
                                {{-- ESUBIZ_CENTRAL_ACCOUNT_TYPE_PRODUCTION_V15 --}}
                                <label class="esubiz-auth-label" for="account_role">
                                    Account Type
                                    <span class="esubiz-auth-required">*</span>
                                </label>

                                <select
                                    id="account_role"
                                    class="esubiz-auth-select"
                                    name="account_role"
                                    required
                                >
                                    @foreach(
                                        $roles
                                        as $role
                                    )
                                        <option
                                            value="{{ $role['value'] }}"
                                            @selected(
                                                $defaultRole
                                                    === $role['value']
                                            )
                                        >
                                            {{ $role['label'] }}
                                        </option>
                                    @endforeach
                                </select>

                                <div
                                    id="role-note"
                                    class="esubiz-auth-help"
                                ></div>

                                @error('account_role')
                                    <div class="esubiz-auth-error">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        @else
                            <input
                                type="hidden"
                                name="account_role"
                                value="user"
                            >
                        @endif

                        <div class="esubiz-auth-field">
                            <label class="esubiz-auth-label" for="password">
                                Password
                                <span class="esubiz-auth-required">*</span>
                            </label>

                            <input
                                id="password"
                                class="esubiz-auth-control"
                                type="password"
                                name="password"
                                required
                                autocomplete="new-password"
                            >

                            @error('password')
                                <div class="esubiz-auth-error">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="esubiz-auth-field">
                            <label class="esubiz-auth-label" for="password_confirmation">
                                Confirm Password
                                <span class="esubiz-auth-required">*</span>
                            </label>

                            <input
                                id="password_confirmation"
                                class="esubiz-auth-control"
                                type="password"
                                name="password_confirmation"
                                required
                                autocomplete="new-password"
                            >
                        </div>

                    </div>


                {{-- ESUBIZ_CENTRAL_REGISTRATION_AGREEMENTS_V21 --}}
                @php
                    /*
                     * V21 bootstrap defaults.
                     *
                     * V22 will replace this local array with
                     * Central Admin-managed settings.
                     */
                    $registrationAgreements = [
                        [
                            'id' => 'terms',
                            'label' => 'Terms of Service',
                            'url' => url('/terms'),
                            'required' => true,
                            'enabled' => true,
                            'account_types' => [
                                'user',
                                'developer',
                                'investor_partner',
                                'investor-partner',
                                'partner',
                            ],
                        ],
                        [
                            'id' => 'privacy',
                            'label' => 'Privacy Policy',
                            'url' => url('/privacy'),
                            'required' => true,
                            'enabled' => true,
                            'account_types' => [
                                '*',
                            ],
                        ],
                    ];

                    $registrationAgreementAccountField =
                        'account_role';

                    $registrationAgreementDefaultAccountType =
                        'user';

                    $registrationAgreementContext =
                        'central';
                @endphp

                @include(
                    'shared.auth.registration-agreements'
                )

<button
                        class="esubiz-auth-button"
                        type="submit"
                    >
                        {{ $registerButton }}
                    </button>
                </form>

<div class="esubiz-auth-footer">
                    Already have an account?
                    <a href="{{ route('login') }}">
                        Sign in
                    </a>
                </div>

            </div>



            {{-- ESUBIZ_CENTRAL_BACK_HOME_V20 --}}
                    <div class="esubiz-core-back-home">
                        <a href="{{ url('/') }}">
                            ← Back to Homepage
                        </a>
                    </div>
        </section>

    </main>
</div>

<script>
(function () {
    const country =
        document.getElementById(
            'country_code'
        );

    const dial =
        document.getElementById(
            'phone_country_code'
        );

    if (country && dial) {
        const syncDialCode = function () {
            const selected =
                country.options[
                    country.selectedIndex
                ];

            if (!selected) {
                return;
            }

            const value =
                selected.dataset.dial;

            if (value) {
                dial.value = value;
            }
        };

        country.addEventListener(
            'change',
            syncDialCode
        );

        syncDialCode();
    }
})();
</script>

<script>
/*
 * ESUBIZ_CENTRAL_BACK_HOME_DOM_FIX_V28
 *
 * Keep exactly one Back to Homepage link and place it
 * directly after the existing account/sign-in footer inside
 * the Central registration auth card.
 */
document.addEventListener('DOMContentLoaded', function () {
    const card =
        document.querySelector('.esubiz-auth-card');

    if (!card) {
        return;
    }

    const accountFooter =
        Array.from(
            card.querySelectorAll(
                '.esubiz-auth-bottom, p, div'
            )
        ).find(function (element) {
            return (
                element.textContent || ''
            )
                .toLowerCase()
                .includes(
                    'already have an account'
                );
        });

    if (!accountFooter) {
        return;
    }

    let backHomeElements =
        Array.from(
            document.querySelectorAll(
                '.esubiz-core-back-home'
            )
        );

    let backHome =
        backHomeElements.shift();

    /*
     * Remove duplicates from earlier layout attempts.
     */
    backHomeElements.forEach(
        function (element) {
            element.remove();
        }
    );

    /*
     * If no reusable wrapper exists, create the single
     * authoritative registration footer link.
     */
    if (!backHome) {
        backHome =
            document.createElement('div');

        backHome.className =
            'esubiz-core-back-home';

        const link =
            document.createElement('a');

        link.href = '/';
        link.textContent =
            '← Back to Homepage';

        backHome.appendChild(link);
    }

    /*
     * Normalize its contents in case an older block survives.
     */
    let link =
        backHome.querySelector('a');

    if (!link) {
        link =
            document.createElement('a');

        backHome.innerHTML = '';
        backHome.appendChild(link);
    }

    link.href = '/';
    link.textContent =
        '← Back to Homepage';

    /*
     * This physically moves the node inside the card,
     * immediately after the account/sign-in footer.
     */
    accountFooter.insertAdjacentElement(
        'afterend',
        backHome
    );
});
</script>

<style>
/*
 * ESUBIZ_CENTRAL_BACK_HOME_DOM_STYLE_V28
 */
.esubiz-auth-card .esubiz-core-back-home {
    position: static !important;
    display: block !important;

    width: 100% !important;
    max-width: 100% !important;

    margin: 24px 0 0 !important;
    padding: 0 !important;

    float: none !important;
    clear: both !important;

    text-align: center !important;
}

.esubiz-auth-card .esubiz-core-back-home a {
    display: inline-block !important;

    color: #667085;
    font-size: 13px;
    line-height: 1.4;
    font-weight: 650;

    text-decoration: none;
}

.esubiz-auth-card .esubiz-core-back-home a:hover {
    color: #0b1f3a;
    text-decoration: underline;
}
</style>

</body>
</html>
