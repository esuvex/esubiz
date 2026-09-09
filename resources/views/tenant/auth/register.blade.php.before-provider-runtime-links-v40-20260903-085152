<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>
<title>Register - {{ $website->name ?? 'Website' }}</title>

<style>
* {
    box-sizing: border-box;
}

html,
body {
    margin: 0;
    padding: 0;
    min-height: 100%;
}

body {
    min-height: 100vh;
    font-family:
        Inter,
        ui-sans-serif,
        system-ui,
        -apple-system,
        BlinkMacSystemFont,
        "Segoe UI",
        sans-serif;
    color: #0f172a;
    background: #f8fafc;
}

.auth-shell {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
    background:
        radial-gradient(
            circle at top left,
            rgba(37, 99, 235, .09),
            transparent 34%
        ),
        #f8fafc;
}

.auth-card {
    width: 100%;
    max-width: 440px;
    padding: 34px;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 24px;
    box-shadow:
        0 24px 60px rgba(15, 23, 42, .08);
}

.brand-mark {
    width: 58px;
    height: 58px;
    margin: 0 auto 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 18px;
    background: #2563eb;
    color: #fff;
    font-size: 21px;
    font-weight: 800;
}

.auth-heading {
    margin-bottom: 28px;
    text-align: center;
}

.auth-heading h1 {
    margin: 0;
    font-size: 28px;
    line-height: 1.2;
    font-weight: 800;
    letter-spacing: -.02em;
}

.auth-heading p {
    margin: 9px 0 0;
    color: #64748b;
    font-size: 14px;
    line-height: 1.6;
}

.auth-heading strong {
    color: #334155;
}

.field {
    margin-bottom: 18px;
}

.label-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    margin-bottom: 8px;
}

label {
    display: block;
    margin-bottom: 8px;
    color: #334155;
    font-size: 13px;
    font-weight: 700;
}

.label-row label {
    margin: 0;
}

input {
    width: 100%;
    height: 50px;
    padding: 0 15px;
    border: 1px solid #cbd5e1;
    border-radius: 12px;
    background: #fff;
    color: #0f172a;
    font-size: 14px;
    outline: none;
}

input:focus {
    border-color: #2563eb;
    box-shadow:
        0 0 0 4px rgba(37, 99, 235, .10);
}

.input-wrap {
    position: relative;
}

.password-input {
    padding-right: 76px;
}

.password-toggle {
    position: absolute;
    top: 50%;
    right: 12px;
    transform: translateY(-50%);
    padding: 5px;
    border: 0;
    background: transparent;
    color: #475569;
    font-size: 12px;
    font-weight: 700;
    cursor: pointer;
}

.btn {
    width: 100%;
    min-height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 0;
    text-decoration: none;
    text-align: center;
    font-size: 14px;
    font-weight: 800;
    cursor: pointer;
}

.btn-primary {
    background: #2563eb;
    color: #fff;
}

.btn-primary:hover {
    background: #1d4ed8;
}

.btn-secondary {
    border: 1px solid #cbd5e1;
    background: #fff;
    color: #334155;
}

.btn-secondary:hover {
    background: #f8fafc;
}

.text-link,
.forgot-link {
    color: #2563eb;
    font-weight: 700;
    text-decoration: none;
}

.text-link:hover,
.forgot-link:hover {
    text-decoration: underline;
}

.forgot-link {
    font-size: 12px;
}

.divider {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 24px 0;
}

.divider::before,
.divider::after {
    content: "";
    height: 1px;
    flex: 1;
    background: #e2e8f0;
}

.divider span {
    color: #94a3b8;
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .08em;
    text-transform: uppercase;
}

.bottom-copy {
    margin: 24px 0 0;
    text-align: center;
    color: #64748b;
    font-size: 13px;
    line-height: 1.6;
}

.site-note {
    margin-top: 22px;
    text-align: center;
    color: #94a3b8;
    font-size: 11px;
}

.alert {
    margin-bottom: 18px;
    padding: 12px 14px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 600;
    line-height: 1.5;
}

.alert-success {
    border: 1px solid #a7f3d0;
    background: #ecfdf5;
    color: #047857;
}

.alert-error {
    border: 1px solid #fecaca;
    background: #fef2f2;
    color: #b91c1c;
}

.helper {
    margin: -7px 0 18px;
    color: #64748b;
    font-size: 12px;
    line-height: 1.6;
}

@media (max-width: 520px) {
    .auth-shell {
        padding: 20px 14px;
    }

    .auth-card {
        padding: 26px 20px;
        border-radius: 20px;
    }

    .auth-heading h1 {
        font-size: 25px;
    }
}

/* ESUBIZ_SHARED_AUTH_LOGO_CSS_V3 */

.brand-mark.has-logo {
    width: auto;
    height: auto;
    min-height: 58px;
    max-width: 220px;
    background: transparent;
    border-radius: 0;
}

.brand-mark.has-logo img {
    display: block;
    width: auto;
    height: auto;
    max-width: 200px;
    max-height: 64px;
    object-fit: contain;
}

.auth-logo-dark {
    display: none !important;
}


/*
 * Explicit Light mode.
 */
html[data-theme="light"] .auth-logo-light,
body[data-theme="light"] .auth-logo-light {
    display: block !important;
}

html[data-theme="light"] .auth-logo-dark,
body[data-theme="light"] .auth-logo-dark {
    display: none !important;
}


/*
 * Explicit Dark mode.
 */
html[data-theme="dark"] .auth-logo-light,
body[data-theme="dark"] .auth-logo-light {
    display: none !important;
}

html[data-theme="dark"] .auth-logo-dark,
body[data-theme="dark"] .auth-logo-dark {
    display: block !important;
}


/*
 * Auto-ready behaviour.
 *
 * Until the site's explicit Light/Dark/Auto setting
 * is connected, system preference can choose Dark.
 */
@media (prefers-color-scheme: dark) {

    html:not([data-theme="light"])
        .auth-logo-light {
        display: none !important;
    }

    html:not([data-theme="light"])
        .auth-logo-dark {
        display: block !important;
    }
}




<style>
/* ESUBIZ_PROVIDER_CIRCULAR_UI_CSS_V5 */

.auth-provider-section {
    width: 100%;
    margin: 0 0 22px;
}

.auth-provider-grid {
    display: flex;
    align-items: flex-start;
    justify-content: center;
    flex-wrap: wrap;

    gap: 18px;

    width: 100%;
}

.auth-provider-item {
    display: flex !important;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;

    width: 92px;

    color: inherit !important;
    text-decoration: none !important;
}

.auth-provider-circle {
    display: flex;
    align-items: center;
    justify-content: center;

    width: 52px;
    height: 52px;

    border-radius: 50%;

    border:
        1px solid
        rgba(127, 127, 127, .20);

    background: #ffffff;

    box-shadow:
        0 4px 14px
        rgba(15, 23, 42, .10);

    font-size: 18px;
    font-weight: 800;
    line-height: 1;

    transition:
        transform .15s ease,
        box-shadow .15s ease;
}

.auth-provider-item:hover
.auth-provider-circle {
    transform:
        translateY(-2px);

    box-shadow:
        0 7px 18px
        rgba(15, 23, 42, .15);
}

.auth-provider-esubiz {
    background: #0b1f3a;
    color: #ffffff;
}

.auth-provider-caption {
    display: block;

    width: 100%;
    margin-top: 7px;

    text-align: center;

    font-size: 10px;
    font-weight: 600;
    line-height: 1.25;

    color: inherit;
    opacity: .78;
}

</style>


</head>

<body>

{{{-- ESUBIZ_AUTH_REAL_CARD_STRUCTURE_V18_1 --}}}
<div class="auth-shell">
<div class="auth-card">

    {{-- ESUBIZ_SHARED_AUTH_LOGO_RENDER_V18_1 --}}
    @php
        $resolvedLightLogo =
            $authPageConfig['logo_light_url']
            ?? '';

        $resolvedDarkLogo =
            $authPageConfig['logo_dark_url']
            ?? '';

        $hasAuthLogo =
            $resolvedLightLogo !== ''
            || $resolvedDarkLogo !== '';

        $authLogoUrl = function ($value) {
            $value = trim((string) $value);

            if ($value === '') {
                return '';
            }

            if (
                str_starts_with($value, 'http://')
                || str_starts_with($value, 'https://')
            ) {
                return $value;
            }

            if (str_starts_with($value, '/')) {
                return asset(
                    ltrim($value, '/')
                );
            }

            return asset(
                'storage/' . ltrim($value, '/')
            );
        };

        $resolvedLightLogoUrl =
            $authLogoUrl(
                $resolvedLightLogo
            );

        $resolvedDarkLogoUrl =
            $authLogoUrl(
                $resolvedDarkLogo
            );
    @endphp

    <div class="brand-mark{{ $hasAuthLogo ? ' has-logo' : '' }}">
        @if($hasAuthLogo)

            @if($resolvedLightLogoUrl !== '')
                <img
                    class="auth-logo-light"
                    src="{{ $resolvedLightLogoUrl }}"
                    alt="{{ $website->name ?? 'Website' }}"
                >
            @endif

            @if($resolvedDarkLogoUrl !== '')
                <img
                    class="auth-logo-dark"
                    src="{{ $resolvedDarkLogoUrl }}"
                    alt="{{ $website->name ?? 'Website' }}"
                >
            @endif

        @else
            {{
                strtoupper(
                    substr(
                        $website->name
                        ?? $website->subdomain
                        ?? 'W',
                        0,
                        1
                    )
                )
            }}
        @endif
    </div>

    <div class="auth-heading">
        <h1>
            {{
                $authPageConfig['heading']
                ?? 'Create Your Account'
            }}
        </h1>

        <p>
            {{
                $authPageConfig['subheading']
                ?? 'Join us today'
            }}
        </p>
    </div>

{{-- ESUBIZ_CORE_AUTH_PROVIDER_STATIC_FINAL_V29 --}}
@php
    /*
     * Enabled state alone controls public visibility.
     * Credentials do not control whether a provider is displayed.
     */
    $coreAuthProviderLabels = [
        'esubiz' => 'Esubiz',
        'google' => 'Google',
        'facebook' => 'Facebook',
        'instagram' => 'Instagram',
        'tiktok' => 'TikTok',
        'x' => 'X',
    ];

    $coreAuthProviderOrder =
        $authPageConfig['provider_order']
        ?? array_keys($coreAuthProviderLabels);

    $coreEnabledAuthProviders = [];

    foreach ($coreAuthProviderOrder as $providerKey) {
        if (
            isset($coreAuthProviderLabels[$providerKey])
            && !empty(
                $authPageConfig['providers'][$providerKey]['enabled']
                ?? false
            )
        ) {
            $coreEnabledAuthProviders[$providerKey] =
                $authPageConfig['providers'][$providerKey] ?? [];
        }
    }

    /*
     * Include enabled providers omitted from a stale/custom order.
     */
    foreach ($coreAuthProviderLabels as $providerKey => $providerLabel) {
        if (
            !isset($coreEnabledAuthProviders[$providerKey])
            && !empty(
                $authPageConfig['providers'][$providerKey]['enabled']
                ?? false
            )
        ) {
            $coreEnabledAuthProviders[$providerKey] =
                $authPageConfig['providers'][$providerKey] ?? [];
        }
    }

    $coreAuthProviderAction =
        request()->routeIs('tenant.auth.register')
            ? 'Register with'
            : 'Login with';
@endphp

@if(!empty($coreEnabledAuthProviders))

    <style id="esubiz-core-auth-provider-static-final-v29-style">

        .auth-provider-section{
            display:block !important;
            width:100% !important;
            margin:22px auto 24px !important;
        }

        .auth-provider-heading-v29{
            margin:0 0 16px !important;
            text-align:center !important;
            font-size:14px !important;
            line-height:1.4 !important;
            font-weight:700 !important;
        }

        .auth-provider-grid{
            display:flex !important;
            align-items:flex-start !important;
            justify-content:center !important;
            flex-wrap:nowrap !important;
            gap:18px !important;
            width:100% !important;
        }

        .auth-provider-item{
            display:flex !important;
            flex-direction:column !important;
            align-items:center !important;
            justify-content:flex-start !important;
            flex:0 0 auto !important;
            gap:7px !important;
            color:inherit !important;
            text-decoration:none !important;
            text-align:center !important;
        }

        .auth-provider-circle{
            display:flex !important;
            align-items:center !important;
            justify-content:center !important;
            width:52px !important;
            height:52px !important;
            min-width:52px !important;
            padding:0 !important;
            overflow:hidden !important;
            border:1px solid rgba(148,163,184,.28) !important;
            border-radius:14px !important;
            background:#fff !important;
            box-shadow:0 6px 16px rgba(15,23,42,.07) !important;
        }

        .auth-provider-circle svg{
            display:block !important;
            width:27px !important;
            height:27px !important;
            background:transparent !important;
        }

        .auth-provider-caption{
            display:block !important;
            margin:0 !important;
            font-size:11px !important;
            line-height:1.25 !important;
            font-weight:500 !important;
            white-space:nowrap !important;
        }

        /*
         * Old generated provider presentations are permanently
         * suppressed from first paint. No JavaScript required.
         */
        .esubiz-provider-cards,
        .esubiz-auth-divider-v17{
            display:none !important;
        }

        @media(max-width:560px){
            .auth-provider-grid{
                flex-wrap:wrap !important;
                gap:14px 16px !important;
            }
        }

    </style>

    <div class="auth-provider-section">

        <div class="auth-provider-heading-v29">
            {{ $coreAuthProviderAction }}
        </div>

        <div class="auth-provider-grid">

            @foreach(
                $coreEnabledAuthProviders
                as $providerKey => $providerConfig
            )
                @php
                    $providerLabel =
                        $coreAuthProviderLabels[$providerKey]
                        ?? ucfirst($providerKey);

                    /*
                     * Only Esubiz currently has a proven Core route.
                     * Other enabled providers remain visible regardless
                     * of credential state without inventing OAuth routes.
                     */
                    $providerHref = null;

                    if ($providerKey === 'esubiz') {
                        $providerHref = route(
                            'tenant.sso.start',
                            [
                                'subdomain' =>
                                    request()->route('subdomain'),
                            ]
                        );
                    }
                @endphp

                <a
                    href="{{ $providerHref ?: '#' }}"
                    class="auth-provider-item"
                    data-auth-provider="{{ $providerKey }}"
                    aria-label="{{ $coreAuthProviderAction }} {{ $providerLabel }}"
                >
                    <span
                        class="auth-provider-circle auth-provider-{{ $providerKey }}"
                        aria-hidden="true"
                    >

                        @if($providerKey === 'esubiz')

                            <svg viewBox="0 0 64 64">
                                <defs>
                                    <linearGradient id="ezGold29" x1="0" y1="0" x2="1" y2="1">
                                        <stop offset="0%" stop-color="#d59d25"/>
                                        <stop offset="45%" stop-color="#ffca45"/>
                                        <stop offset="100%" stop-color="#b77a12"/>
                                    </linearGradient>

                                    <linearGradient id="ezNavy29" x1="0" y1="0" x2="1" y2="1">
                                        <stop offset="0%" stop-color="#0b2b59"/>
                                        <stop offset="100%" stop-color="#071a38"/>
                                    </linearGradient>
                                </defs>

                                <path
                                    fill="url(#ezNavy29)"
                                    d="M4 17H28L33 24H4ZM4 30H26L21 37H4ZM4 43H20L26 50H4Z"
                                />

                                <path
                                    fill="url(#ezGold29)"
                                    d="M30 18H58L59 55H38L45 42L35 55H25L40 32Z"
                                />

                                <path
                                    d="M39 20C39 9 43 4 49 4C55 4 58 9 58 20"
                                    fill="none"
                                    stroke="url(#ezGold29)"
                                    stroke-width="5"
                                    stroke-linecap="round"
                                />

                                <ellipse cx="39" cy="21" rx="3" ry="5" fill="#171717"/>
                                <ellipse cx="58" cy="21" rx="3" ry="5" fill="#171717"/>
                            </svg>

                        @elseif($providerKey === 'google')

                            <svg viewBox="0 0 24 24">
                                <path fill="#4285F4" d="M21.35 12.25c0-.74-.07-1.45-.19-2.13H12v4.03h5.24a4.48 4.48 0 0 1-1.94 2.94v2.62h3.14c1.84-1.69 2.91-4.18 2.91-7.46z"/>
                                <path fill="#34A853" d="M12 21.75c2.62 0 4.82-.87 6.43-2.35l-3.14-2.62c-.87.58-1.98.93-3.29.93-2.53 0-4.67-1.71-5.44-4.01H3.32v2.7A9.72 9.72 0 0 0 12 21.75z"/>
                                <path fill="#FBBC05" d="M6.56 13.7A5.85 5.85 0 0 1 6.25 12c0-.59.1-1.16.31-1.7V7.6H3.32A9.75 9.75 0 0 0 2.25 12c0 1.57.38 3.06 1.07 4.4l3.24-2.7z"/>
                                <path fill="#EA4335" d="M12 6.29c1.43 0 2.71.49 3.72 1.45l2.78-2.78C16.82 3.4 14.62 2.25 12 2.25A9.72 9.72 0 0 0 3.32 7.6l3.24 2.7C7.33 8 9.47 6.29 12 6.29z"/>
                            </svg>

                        @elseif($providerKey === 'facebook')

                            <svg viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" fill="#1877F2"/>
                                <path fill="#fff" d="M13.5 21v-8h2.7l.4-3h-3.1V8.1c0-.87.24-1.46 1.53-1.46H16.7V4a22.5 22.5 0 0 0-2.37-.12c-2.35 0-3.96 1.43-3.96 4.06V10H7.7v3h2.67v8h3.13z"/>
                            </svg>

                        @elseif($providerKey === 'instagram')

                            <svg viewBox="0 0 24 24">
                                <defs>
                                    <radialGradient id="ig29" cx="30%" cy="107%" r="120%">
                                        <stop offset="0%" stop-color="#FFD600"/>
                                        <stop offset="35%" stop-color="#FF7A00"/>
                                        <stop offset="65%" stop-color="#FF0169"/>
                                        <stop offset="100%" stop-color="#D300C5"/>
                                    </radialGradient>
                                </defs>

                                <rect x="2" y="2" width="20" height="20" rx="6" fill="url(#ig29)"/>
                                <circle cx="12" cy="12" r="4.2" fill="none" stroke="#fff" stroke-width="1.8"/>
                                <circle cx="17.4" cy="6.7" r="1.15" fill="#fff"/>
                            </svg>

                        @elseif($providerKey === 'tiktok')

                            <svg viewBox="0 0 24 24">
                                <path fill="#25F4EE" d="M13.8 3h2.8c.2 1.7 1.1 3 2.8 3.8v2.7c-1.1 0-2.2-.3-3.1-.9v6.1a5.4 5.4 0 1 1-4.7-5.4v2.9a2.6 2.6 0 1 0 1.8 2.5V3h.4z"/>
                                <path fill="#FE2C55" d="M14.8 2h2.7c.2 1.7 1.2 3 2.8 3.8v2.7c-1.2 0-2.2-.3-3.1-.9v6.1a5.4 5.4 0 0 1-5.4 5.4c-1.4 0-2.7-.5-3.7-1.4a5.4 5.4 0 0 0 9.1-4V7.6c.9.6 1.9.9 3.1.9V7.2c-2.7-.8-4.5-2.4-5.5-5.2z"/>
                                <path fill="#111" d="M14 3h2.7c.2 1.7 1.2 3 2.8 3.8v1.4c-1.2 0-2.2-.3-3.1-.9v6.1a5.4 5.4 0 1 1-5.4-5.4c.3 0 .6 0 .9.1V11a2.6 2.6 0 1 0 1.8 2.5V3h.3z"/>
                            </svg>

                        @elseif($providerKey === 'x')

                            <svg viewBox="0 0 24 24">
                                <path fill="#000" d="M18.9 2H22l-6.8 7.8L23.2 22H17l-4.9-6.4L6.5 22H3.4l7.2-8.3L2.9 2h6.4l4.4 5.8L18.9 2zm-1.1 17.9h1.7L8.4 4H6.6l11.2 15.9z"/>
                            </svg>

                        @endif

                    </span>

                    <span class="auth-provider-caption">
                        {{ $providerLabel }}
                    </span>
                </a>

            @endforeach

        </div>

        {{-- ESUBIZ_CORE_AUTH_EMAIL_DIVIDER_FINAL_V34 --}}
        <div class="esubiz-auth-email-divider-v32">
            <span>OR CONTINUE WITH EMAIL</span>
        </div>
    </div>
@endif


{{-- ESUBIZ_PUBLIC_AUTH_CSS_ONLY_V15 --}}
<style>

/* Public authentication form presentation */

form{
    width:min(100%,460px);
    box-sizing:border-box;
    margin:28px auto 0;
    padding:30px;
    border:1px solid rgba(148,163,184,.22);
    border-radius:18px;
    background:rgba(255,255,255,.98);
    box-shadow:
        0 18px 46px rgba(15,23,42,.08),
        0 2px 8px rgba(15,23,42,.04);
}

form label{
    display:block;
    margin-bottom:7px;
    font-size:13px;
    font-weight:600;
    line-height:1.4;
}

form input:not([type="hidden"]):not([type="checkbox"]):not([type="radio"]),
form select,
form textarea{
    width:100%;
    min-height:48px;
    box-sizing:border-box;
    border-radius:10px;
    transition:
        border-color .16s ease,
        box-shadow .16s ease,
        background .16s ease;
}

form textarea{
    min-height:105px;
    resize:vertical;
}

form input:not([type="hidden"]):not([type="checkbox"]):not([type="radio"]):focus,
form select:focus,
form textarea:focus{
    outline:none;
    border-color:#60a5fa;
    box-shadow:0 0 0 3px rgba(37,99,235,.10);
}

form input[type="checkbox"]{
    width:16px;
    height:16px;
    accent-color:#2563eb;
}

form button[type="submit"],
form input[type="submit"]{
    min-height:48px;
    border-radius:10px;
    font-weight:700;
    cursor:pointer;
    transition:
        transform .15s ease,
        box-shadow .15s ease,
        opacity .15s ease;
}

form button[type="submit"]:hover,
form input[type="submit"]:hover{
    transform:translateY(-1px);
    box-shadow:0 8px 20px rgba(15,23,42,.10);
}

form a{
    text-underline-offset:3px;
}

form .invalid-feedback,
form .text-danger,
form .error{
    font-size:12px;
    line-height:1.45;
}

@media(max-width:560px){

    form{
        width:100%;
        margin-top:20px;
        padding:22px 18px;
        border-radius:14px;
        box-shadow:
            0 10px 28px rgba(15,23,42,.06);
    }

    form input:not([type="hidden"]):not([type="checkbox"]):not([type="radio"]),
    form select,
    form button[type="submit"]{
        min-height:46px;
    }
}

</style>

<form
    method="POST"
    action="{{ route(
        'tenant.auth.register.submit',
        ['subdomain' => $website->subdomain]
    ) }}"
>
    @csrf

    {{-- ESUBIZ_AUTH_BOT_FORM_FIELDS_V1 --}}
    @if(
        !empty(
            $authPageConfig[
                'security'
            ][
                'register_enabled'
            ]
        )
    )
        @php
            $authStartedAt = time();

            session()->put(
                'tenant_auth_form_started.'
                . (int) $website->id
                . '.register',
                $authStartedAt
            );
        @endphp

        <div
            aria-hidden="true"
            style="
                position:absolute;
                left:-10000px;
                width:1px;
                height:1px;
                overflow:hidden;
            "
        >
            <label>
                Website
                <input
                    type="text"
                    name="website_url"
                    value=""
                    tabindex="-1"
                    autocomplete="off"
                >
            </label>
        </div>

        <input
            type="hidden"
            name="auth_started_at"
            value="{{ $authStartedAt }}"
        >
    @endif

    <div class="field">
        <label for="name">
            Full name
        </label>

        <input
            id="name"
            name="name"
            type="text"
            value="{{ old('name') }}"
            required
            autocomplete="name"
            placeholder="Your full name"
        >
    </div>

    <div class="field">
        <label for="email">
            Email address
        </label>

        <input
            id="email"
            name="email"
            type="email"
            value="{{ old('email') }}"
            required
            autocomplete="email"
            placeholder="you@example.com"
        >
    </div>

    <div class="field">
        <label for="password">
            Password
        </label>

        <div class="input-wrap">
            <input
                id="password"
                name="password"
                type="password"
                required
                minlength="8"
                autocomplete="new-password"
                class="password-input"
                placeholder="Minimum 8 characters"
            >

            <button
                type="button"
                class="password-toggle"
                data-password-toggle="password"
            >
                Show
            </button>
        </div>
    </div>

    <div class="field">
        <label for="password_confirmation">
            Confirm password
        </label>

        <div class="input-wrap">
            <input
                id="password_confirmation"
                name="password_confirmation"
                type="password"
                required
                minlength="8"
                autocomplete="new-password"
                class="password-input"
                placeholder="Repeat your password"
            >

            <button
                type="button"
                class="password-toggle"
                data-password-toggle="password_confirmation"
            >
                Show
            </button>
        </div>

        {{-- ESUBIZ_PUBLIC_DYNAMIC_REGISTRATION_FIELDS_V1 --}}
        @foreach(($registrationFields ?? []) as $registrationField)

            @php
                $fieldSystem =
                    filter_var(
                        $registrationField['system']
                            ?? false,
                        FILTER_VALIDATE_BOOLEAN
                    );

                $fieldEnabled =
                    filter_var(
                        $registrationField['enabled']
                            ?? true,
                        FILTER_VALIDATE_BOOLEAN
                    );

                $fieldRequired =
                    filter_var(
                        $registrationField['required']
                            ?? false,
                        FILTER_VALIDATE_BOOLEAN
                    );

                $fieldKey =
                    trim(
                        (string) (
                            $registrationField['key']
                            ?? ''
                        )
                    );

                $fieldLabel =
                    trim(
                        (string) (
                            $registrationField['label']
                            ?? ''
                        )
                    );

                $fieldType =
                    strtolower(
                        trim(
                            (string) (
                                $registrationField['type']
                                ?? 'text'
                            )
                        )
                    );

                $fieldPlaceholder =
                    trim(
                        (string) (
                            $registrationField['placeholder']
                            ?? ''
                        )
                    );

                $fieldOptions =
                    $registrationField['options']
                    ?? [];

                if (is_string($fieldOptions)) {
                    $fieldOptions =
                        array_values(
                            array_filter(
                                array_map(
                                    'trim',
                                    explode(
                                        ',',
                                        $fieldOptions
                                    )
                                ),
                                static fn ($value) =>
                                    $value !== ''
                            )
                        );
                }

                if (!is_array($fieldOptions)) {
                    $fieldOptions = [];
                }
            @endphp


            @if(
                !$fieldSystem
                && $fieldEnabled
                && $fieldKey !== ''
                && $fieldLabel !== ''
            )

                <div class="field">

                    @if($fieldType !== 'checkbox')
                        <label
                            for="registration_field_{{ $fieldKey }}"
                        >
                            {{ $fieldLabel }}

                            @if($fieldRequired)
                                <span aria-hidden="true">*</span>
                            @endif
                        </label>
                    @endif


                    @if($fieldType === 'textarea')

                        <textarea
                            id="registration_field_{{ $fieldKey }}"
                            name="registration_data[{{ $fieldKey }}]"
                            placeholder="{{ $fieldPlaceholder }}"
                            @required($fieldRequired)
                        >{{ old('registration_data.' . $fieldKey) }}</textarea>


                    @elseif($fieldType === 'select')

                        <select
                            id="registration_field_{{ $fieldKey }}"
                            name="registration_data[{{ $fieldKey }}]"
                            @required($fieldRequired)
                        >
                            <option value="">
                                {{ $fieldPlaceholder !== ''
                                    ? $fieldPlaceholder
                                    : 'Select ' . $fieldLabel }}
                            </option>

                            @foreach($fieldOptions as $option)
                                <option
                                    value="{{ $option }}"
                                    @selected(
                                        old(
                                            'registration_data.'
                                            . $fieldKey
                                        ) == $option
                                    )
                                >
                                    {{ $option }}
                                </option>
                            @endforeach
                        </select>


                    @elseif($fieldType === 'checkbox')

                        <label
                            style="
                                display:flex;
                                align-items:center;
                                gap:8px;
                            "
                        >
                            <input
                                type="checkbox"
                                name="registration_data[{{ $fieldKey }}]"
                                value="1"
                                @checked(
                                    old(
                                        'registration_data.'
                                        . $fieldKey
                                    )
                                )
                                @required($fieldRequired)
                            >

                            <span>
                                {{ $fieldLabel }}

                                @if($fieldRequired)
                                    <span aria-hidden="true">*</span>
                                @endif
                            </span>
                        </label>


                    @else

                        <input
                            id="registration_field_{{ $fieldKey }}"
                            type="{{
                                in_array(
                                    $fieldType,
                                    [
                                        'text',
                                        'email',
                                        'tel',
                                        'number',
                                        'date',
                                    ],
                                    true
                                )
                                    ? $fieldType
                                    : 'text'
                            }}"
                            name="registration_data[{{ $fieldKey }}]"
                            value="{{
                                old(
                                    'registration_data.'
                                    . $fieldKey
                                )
                            }}"
                            placeholder="{{ $fieldPlaceholder }}"
                            @required($fieldRequired)
                        >

                    @endif


                    @error(
                        'registration_data.'
                        . $fieldKey
                    )
                        <div
                            style="
                                margin-top:6px;
                                color:#dc2626;
                                font-size:12px;
                            "
                        >
                            {{ $message }}
                        </div>
                    @enderror

                </div>

            @endif

        @endforeach

    </div>

    <button
        type="submit"
        class="btn btn-primary"
    >
        Create account
    </button>
</form>



<p class="bottom-copy">
    Already have an account?

    <a
        href="{{ route(
            'tenant.auth.login',
            ['subdomain' => $website->subdomain]
        ) }}"
        class="text-link"
    >
        Sign in
    </a>
</p>

{{-- ESUBIZ_AUTH_HOMEPAGE_RETURN_V1 --}}
<div class="auth-home-link">
    <a href="/">
        ← Back to homepage
    </a>
</div>

{{-- ESUBIZ_SAAS_ONLY_POWERED_BY_LINK_V1 --}}
@if(
    $authPageConfig['is_saas']
    ?? false
)
    <div class="site-note">
        <a
            href="https://esubiz.com/"
            target="_blank"
            rel="noopener noreferrer"
        >
            Powered by Esubiz
        </a>
    </div>
@endif
</div>
</div>

<script>
document.addEventListener(
    'DOMContentLoaded',
    function () {
        document
            .querySelectorAll(
                '[data-password-toggle]'
            )
            .forEach(function (button) {
                const target =
                    document.getElementById(
                        button.getAttribute(
                            'data-password-toggle'
                        )
                    );

                if (!target) {
                    return;
                }

                button.addEventListener(
                    'click',
                    function () {
                        const visible =
                            target.type === 'text';

                        target.type =
                            visible
                                ? 'password'
                                : 'text';

                        button.textContent =
                            visible
                                ? 'Show'
                                : 'Hide';
                    }
                );
            });
    }
);
</script>






{{-- ESUBIZ_PUBLIC_AUTH_V17_VARIABLE_BRIDGE --}}
@php
    $authPublic =
        $authPageConfig
        ?? [];

    $authBackground =
        $authPublic[
            'background_color'
        ]
        ?? '#f5f7fb';

    $authCard =
        $authPublic[
            'card_color'
        ]
        ?? '#ffffff';

    $authText =
        $authPublic[
            'text_color'
        ]
        ?? '#111827';

    $authButton =
        $authPublic[
            'button_color'
        ]
        ?? '#111827';
@endphp

{{-- ESUBIZ_PUBLIC_AUTH_PREMIUM_CARD_V17 --}}




<style>

/* =========================================================
   Shared premium authentication card
   ========================================================= */

.auth-shell{
    width:100%;
    min-height:100vh;
    min-height:100dvh;
    box-sizing:border-box;
    padding:42px 20px;
    background:{{ $authBackground }} !important;
}

.auth-card{
    width:min(100%,520px);
    margin:0 auto;
    box-sizing:border-box;
    padding:38px 38px 32px;
    border:1px solid rgba(148,163,184,.20);
    border-radius:22px;
    background:{{ $authCard }} !important;
    color:{{ $authText }} !important;
    box-shadow:
        0 24px 64px rgba(15,23,42,.10),
        0 3px 12px rgba(15,23,42,.04);
}


/* Existing form must not become a second card */

.auth-card form{
    width:100% !important;
    max-width:none !important;
    margin:0 !important;
    padding:0 !important;
    border:0 !important;
    border-radius:0 !important;
    background:transparent !important;
    box-shadow:none !important;
}


/* =========================================================
   Logo
   ========================================================= */

.auth-card .brand-mark{
    margin:0 auto 20px;
    text-align:center;
}

.auth-card .brand-mark.has-logo{
    width:auto;
    height:auto;
    background:transparent;
    border-radius:0;
    box-shadow:none;
}

.auth-card .brand-mark img{
    display:block;
    width:auto;
    max-width:190px;
    max-height:72px;
    margin:0 auto;
    object-fit:contain;
}

.auth-card .auth-logo-light{
    display:block;
}

.auth-card .auth-logo-dark{
    display:none;
}

@media (prefers-color-scheme:dark){

    .auth-card .auth-logo-light{
        display:none;
    }

    .auth-card .auth-logo-dark{
        display:block;
    }
}


/* =========================================================
   Provider icons
   ========================================================= */

.esubiz-provider-cards{
    display:flex;
    flex-wrap:wrap;
    justify-content:center;
    align-items:center;
    gap:10px;
    width:100%;
    margin:5px 0 24px;
}

.esubiz-provider-card{
    position:relative;
    display:inline-flex !important;
    align-items:center;
    justify-content:center;
    width:50px !important;
    height:50px;
    min-width:50px;
    padding:0 !important;
    border:1px solid rgba(148,163,184,.28) !important;
    border-radius:14px !important;
    background:{{ $authCard }} !important;
    color:{{ $authText }} !important;
    text-decoration:none !important;
    box-shadow:
        0 5px 14px rgba(15,23,42,.055);
    transition:
        transform .15s ease,
        border-color .15s ease,
        box-shadow .15s ease;
}

.esubiz-provider-card:hover{
    transform:translateY(-2px);
    border-color:{{ $authButton }} !important;
    box-shadow:
        0 9px 20px rgba(15,23,42,.09);
}

.esubiz-provider-logo{
    display:inline-flex;
    align-items:center;
    justify-content:center;
    width:28px;
    height:28px;
    flex:0 0 28px;
    font-size:15px;
    font-weight:800;
}

.esubiz-provider-logo svg{
    display:block;
    width:22px;
    height:22px;
}

.esubiz-provider-logo--esubiz{
    border-radius:8px;
    background:#0b1f3a;
    color:#fff;
}

.esubiz-provider-copy{
    position:absolute !important;
    width:1px !important;
    height:1px !important;
    padding:0 !important;
    margin:-1px !important;
    overflow:hidden !important;
    clip:rect(0,0,0,0) !important;
    white-space:nowrap !important;
    border:0 !important;
}


/* Divider */

.esubiz-auth-divider-v17{
    display:flex;
    align-items:center;
    gap:12px;
    margin:0 0 22px;
    color:{{ $authText }};
    opacity:.58;
    font-size:11px;
    font-weight:700;
    text-transform:uppercase;
    letter-spacing:.07em;
}

.esubiz-auth-divider-v17::before,
.esubiz-auth-divider-v17::after{
    content:"";
    height:1px;
    flex:1;
    background:currentColor;
    opacity:.20;
}


/* =========================================================
   Shared appearance
   ========================================================= */

.auth-card,
.auth-card h1,
.auth-card h2,
.auth-card h3,
.auth-card p,
.auth-card label{
    color:{{ $authText }} !important;
}

.auth-card input:not([type="hidden"]):not([type="checkbox"]):not([type="radio"]),
.auth-card select,
.auth-card textarea{
    width:100%;
    min-height:48px;
    box-sizing:border-box;
    border-radius:10px;
}

.auth-card .btn-primary,
.auth-card button[type="submit"],
.auth-card input[type="submit"]{
    background:{{ $authButton }} !important;
    border-color:{{ $authButton }} !important;
}

.auth-card a{
    color:{{ $authButton }};
}


/* Homepage / Esubiz footer */

.auth-card .auth-home-link{
    margin-top:24px;
    text-align:center;
}

.auth-card .auth-home-link a{
    color:{{ $authText }};
    font-size:13px;
    font-weight:700;
    text-decoration:none;
    opacity:.72;
}

.auth-card .site-note{
    margin-top:14px;
    text-align:center;
}

.auth-card .site-note a{
    color:{{ $authText }};
    font-size:12px;
    opacity:.65;
}


/* Mobile */

@media(max-width:560px){

    .auth-shell{
        padding:20px 14px;
    }

    .auth-card{
        padding:28px 20px 25px;
        border-radius:18px;
    }

    .auth-card .brand-mark img{
        max-width:160px;
        max-height:60px;
    }

    .esubiz-provider-card{
        width:46px !important;
        height:46px;
        min-width:46px;
    }
}

</style>





{{-- ESUBIZ_AUTH_AJAX_SWITCHING_ALL_FORMS_V2 --}}




{{-- ESUBIZ_PUBLIC_AUTH_SINGLE_CARD_V18 --}}
<style id="esubiz-public-auth-single-card-v18">

/* =========================================================
   V18 — Login/Register authoritative single-card composition
   ========================================================= */

.auth-shell{
    width:100% !important;
    min-height:100vh !important;
    min-height:100dvh !important;
    display:flex !important;
    align-items:center !important;
    justify-content:center !important;
    box-sizing:border-box !important;
    padding:36px 20px !important;
    background:{{ $authBackground }} !important;
}

.auth-card{
    width:min(100%,520px) !important;
    max-width:520px !important;
    margin:0 auto !important;
    padding:38px 38px 32px !important;
    box-sizing:border-box !important;

    display:flex !important;
    flex-direction:column !important;
    align-items:stretch !important;

    border:1px solid rgba(148,163,184,.20) !important;
    border-radius:22px !important;

    background:{{ $authCard }} !important;
    color:{{ $authText }} !important;

    box-shadow:
        0 24px 64px rgba(15,23,42,.10),
        0 3px 12px rgba(15,23,42,.04) !important;
}


/* Logo belongs to the card */

.auth-card .brand-mark{
    order:1;
    margin:0 auto 18px !important;
    text-align:center !important;
}

.auth-card .brand-mark.has-logo{
    width:auto !important;
    height:auto !important;
    min-height:0 !important;
    background:transparent !important;
    border-radius:0 !important;
}

.auth-card .brand-mark img{
    display:block;
    width:auto !important;
    height:auto !important;
    max-width:190px !important;
    max-height:68px !important;
    margin:0 auto !important;
    object-fit:contain !important;
}


/* Heading */

.auth-card .auth-heading{
    order:2;
    margin:0 0 22px !important;
    text-align:center !important;
}

.auth-card .auth-heading h1{
    margin:0 !important;
    font-size:28px !important;
    line-height:1.2 !important;
    font-weight:800 !important;
    letter-spacing:-.02em !important;
}

.auth-card .auth-heading p{
    margin:8px 0 0 !important;
    font-size:14px !important;
    line-height:1.55 !important;
    opacity:.68;
}


/* Validation/status messages */

.auth-card .alert{
    order:3;
}


/* Provider area */

.auth-card .auth-provider-section,
.auth-card .esubiz-provider-cards{
    order:4;
    width:100% !important;
    margin:0 0 24px !important;
}

.auth-card .auth-provider-grid,
.auth-card .esubiz-provider-cards{
    display:flex !important;
    align-items:flex-start !important;
    justify-content:center !important;
    flex-wrap:wrap !important;
    gap:16px !important;
}


/* Existing form becomes content, NOT another card */

.auth-card form{
    order:5;

    width:100% !important;
    max-width:none !important;

    margin:0 !important;
    padding:0 !important;

    border:0 !important;
    border-radius:0 !important;

    background:transparent !important;
    box-shadow:none !important;
}


/* Fields */

.auth-card .field{
    margin-bottom:18px !important;
}

.auth-card form label{
    margin-bottom:7px !important;
    font-size:13px !important;
    font-weight:600 !important;
}

.auth-card form input:not([type="hidden"]):not([type="checkbox"]):not([type="radio"]),
.auth-card form select,
.auth-card form textarea{
    width:100% !important;
    min-height:48px !important;
    box-sizing:border-box !important;
    border-radius:10px !important;
}


/* Primary action */

.auth-card .btn-primary,
.auth-card form button[type="submit"],
.auth-card form input[type="submit"]{
    width:100% !important;
    min-height:48px !important;
    border-radius:10px !important;
    font-weight:700 !important;
}


/* Register / Sign-in copy stays inside card */

.auth-card .bottom-copy{
    order:6;
    margin:22px 0 0 !important;
    text-align:center !important;
    font-size:13px !important;
    line-height:1.55 !important;
}


/*
 * Reference design ends at the Login/Register switch.
 * Keep existing elements in markup for compatibility,
 * but don't render them on Login/Register.
 */

.auth-card .auth-home-link,
.auth-card .site-note{
    display:none !important;
}


/* Prevent legacy provider script from creating a second row */

.auth-card .esubiz-provider-cards + .auth-provider-section,
.auth-card .auth-provider-section + .esubiz-provider-cards{
    display:none !important;
}


/* Mobile */

@media(max-width:560px){

    .auth-shell{
        align-items:flex-start !important;
        padding:20px 14px !important;
    }

    .auth-card{
        padding:28px 20px 24px !important;
        border-radius:18px !important;
    }

    .auth-card .auth-heading h1{
        font-size:25px !important;
    }

    .auth-card .brand-mark img{
        max-width:170px !important;
        max-height:60px !important;
    }
}

</style>


{{-- ESUBIZ_PUBLIC_AUTH_CLEANUP_V18_2 --}}
<style id="esubiz-public-auth-cleanup-v18-2">

/*
 * V18.2
 * Keep the server-rendered provider row authoritative.
 * AJAX may still enhance navigation, but its duplicate visual
 * provider row/divider must not appear.
 */

.auth-card > .esubiz-provider-cards,
.auth-card .esubiz-provider-cards,
.auth-card > .esubiz-auth-divider-v17,
.auth-card .esubiz-auth-divider-v17{
    display:none !important;
}


/*
 * Clean single divider immediately before the real provider row.
 */

.auth-card .auth-provider-section{
    position:relative !important;
    padding-top:24px !important;
    margin-top:0 !important;
}

.auth-card .auth-provider-section::before{
    content:"or";
    position:absolute;
    top:0;
    left:50%;
    transform:translateX(-50%);

    padding:0 10px;

    background:{{ $authCard }} !important;
    color:{{ $authText }} !important;

    font-size:11px;
    font-weight:600;
    line-height:1.2;

    opacity:.50;
    text-transform:lowercase;
}


/*
 * Prevent provider divider pseudo-element from creating
 * unwanted horizontal overflow.
 */

.auth-card .auth-provider-section::after{
    content:"";
    position:absolute;
    top:6px;
    left:0;
    right:0;
    height:1px;

    background:rgba(148,163,184,.22);
    z-index:-1;
}


/*
 * Keep heading spacing aligned with Forgot Password.
 */

.auth-card .auth-heading{
    margin-bottom:18px !important;
}

.auth-card .auth-provider-section{
    margin-bottom:24px !important;
}


/*
 * Provider is one clean circular option.
 */

.auth-card .auth-provider-grid{
    gap:16px !important;
}

.auth-card .auth-provider-item{
    width:100px !important;
}

.auth-card .auth-provider-circle{
    width:48px !important;
    height:48px !important;
}

.auth-card .auth-provider-caption{
    margin-top:7px !important;
    font-size:10px !important;
}


/*
 * Keep the Login/Register switch neatly inside the card.
 */

.auth-card .bottom-copy{
    margin-top:20px !important;
}

</style>








{{-- ESUBIZ_PUBLIC_AUTH_FINAL_ORDER_V18_4 --}}
<style id="esubiz-public-auth-final-order-v18-4">

/*
 * Keep footer navigation after the form/account switch.
 */

.auth-card .auth-home-link{
    order:7 !important;
    display:block !important;
    width:100% !important;
    margin:18px 0 0 !important;
    text-align:center !important;
}

.auth-card .site-note{
    order:8 !important;
    display:block !important;
    width:100% !important;
    margin:8px 0 0 !important;
    text-align:center !important;
}


/*
 * Canonical provider row belongs between heading and form.
 */

.auth-card .esubiz-auth-provider-final{
    order:4 !important;
}

.auth-card form{
    order:5 !important;
}

.auth-card .bottom-copy{
    order:6 !important;
}

</style>

<script>
(function () {

    /*
     * Remove accidental literal "{}" text emitted outside the
     * authentication markup. This does not touch form data,
     * Blade expressions or JavaScript objects.
     */
    function removeAuthStrayText() {

        Array.from(document.body.childNodes)
            .filter(function (node) {
                return node.nodeType === Node.TEXT_NODE;
            })
            .forEach(function (node) {

                const value =
                    (node.textContent || '').trim();

                if (
                    value === '{}'
                    || value === '{ }'
                    || value === '{'
                    || value === '}'
                ) {
                    node.remove();
                }
            });
    }

    removeAuthStrayText();

    document.addEventListener(
        'DOMContentLoaded',
        removeAuthStrayText
    );

    window.addEventListener(
        'load',
        removeAuthStrayText
    );

})();
</script>







































<style id="esubiz-core-auth-provider-clean-final-v30">
/* ESUBIZ_CORE_AUTH_PROVIDER_CLEAN_FINAL_V30 */

/*
 * V29 server-rendered provider row is the ONLY provider UI.
 */

/* Never display generated/legacy provider rows. */
.esubiz-provider-cards,
.esubiz-auth-divider-v17{
    display:none !important;
    visibility:hidden !important;
    opacity:0 !important;
}

/* V29 section */
.auth-card .auth-provider-section,
.auth-provider-section{
    display:block !important;
    visibility:visible !important;
    opacity:1 !important;
    width:100% !important;
}

/* Force one horizontal row on desktop. */
.auth-card .auth-provider-section .auth-provider-grid,
.auth-provider-section .auth-provider-grid{
    display:flex !important;
    flex-direction:row !important;
    flex-wrap:nowrap !important;
    align-items:flex-start !important;
    justify-content:center !important;
    gap:18px !important;
    width:100% !important;
}

/* Each provider stays compact, never full-width/vertical. */
.auth-card .auth-provider-section .auth-provider-item,
.auth-provider-section .auth-provider-item{
    display:flex !important;
    flex-direction:column !important;
    flex:0 0 auto !important;
    width:auto !important;
    min-width:0 !important;
    max-width:none !important;
    align-items:center !important;
    justify-content:flex-start !important;
    gap:7px !important;
    margin:0 !important;
    padding:0 !important;
    text-align:center !important;
}

/* Shared provider button. */
.auth-card .auth-provider-section .auth-provider-circle,
.auth-provider-section .auth-provider-circle{
    display:flex !important;
    width:52px !important;
    height:52px !important;
    min-width:52px !important;
    min-height:52px !important;
    align-items:center !important;
    justify-content:center !important;
    margin:0 !important;
    border-radius:14px !important;
    background:#fff !important;
}

/* Provider names below logos. */
.auth-card .auth-provider-section .auth-provider-caption,
.auth-provider-section .auth-provider-caption{
    display:block !important;
    visibility:visible !important;
    opacity:1 !important;
    width:auto !important;
    margin:0 !important;
    white-space:nowrap !important;
    text-align:center !important;
}

/* Mobile can wrap cleanly. */
@media(max-width:560px){
    .auth-card .auth-provider-section .auth-provider-grid,
    .auth-provider-section .auth-provider-grid{
        flex-wrap:wrap !important;
        gap:14px 16px !important;
    }
}
</style>


{{-- ESUBIZ_CORE_AUTH_PROVIDER_LEGACY_JS_REMOVED_V31 --}}
<style id="esubiz-core-auth-provider-legacy-js-removed-v31">
/*
 * V29 is the only provider renderer.
 * Legacy generated provider rows/dividers remain suppressed.
 */
.esubiz-provider-cards,
.esubiz-auth-divider-v17{
    display:none !important;
}
</style>


<style id="esubiz-core-auth-email-divider-v32-style">
.esubiz-auth-email-divider-v32{
    display:flex;
    align-items:center;
    gap:12px;
    width:100%;
    margin:18px 0 20px;
    color:#94a3b8;
    font-size:10px;
    line-height:1;
    font-weight:700;
    letter-spacing:.04em;
    text-align:center;
    white-space:nowrap;
}

.esubiz-auth-email-divider-v32::before,
.esubiz-auth-email-divider-v32::after{
    content:"";
    flex:1 1 auto;
    height:1px;
    background:rgba(148,163,184,.22);
}

.esubiz-auth-email-divider-v32 span{
    flex:0 0 auto;
}
</style>



{{-- ESUBIZ_CORE_AUTH_REMOVE_PROVIDER_OR_V36 --}}
<style id="esubiz-core-auth-remove-provider-or-v36">

/*
 * Remove only the old standalone "or" generated above
 * Login with / Register with.
 *
 * The OR CONTINUE WITH EMAIL divider is untouched.
 */
.auth-card .auth-provider-section::before,
.auth-provider-section::before{
    content:none !important;
    display:none !important;
}

</style>
