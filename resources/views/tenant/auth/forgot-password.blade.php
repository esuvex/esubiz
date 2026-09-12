<!DOCTYPE html>
<html lang="en">
<head>


    @php
    /*
     * ==========================================================
     * CANONICAL CORE SHELL SETTINGS
     * ==========================================================
     *
     * resources/views/tenant/admin/layouts/app.blade.php
     * is the ONE internal Core layout/header source.
     *
     * resources/views/tenant/admin/partials/sidebar.blade.php
     * is the ONE Core sidebar source.
     *
     * Role changes affect menu visibility only.
     * Branding is shared by every Core role.
     */

    $coreExistingSettings =
        isset($settings) && is_array($settings)
            ? $settings
            : [];

    $coreFallbackSettings = [];

    /*
     * Preserve Website model settings exactly as stored.
     * Do NOT transform dotted keys into nested arrays.
     */
    if (isset($website) && $website) {

        $rawWebsiteSettings =
            $website->settings ?? null;

        if (is_string($rawWebsiteSettings)) {

            $decodedWebsiteSettings =
                json_decode(
                    $rawWebsiteSettings,
                    true
                );

            if (is_array($decodedWebsiteSettings)) {
                $coreFallbackSettings =
                    array_replace_recursive(
                        $coreFallbackSettings,
                        $decodedWebsiteSettings
                    );
            }

        } elseif (is_array($rawWebsiteSettings)) {

            $coreFallbackSettings =
                array_replace_recursive(
                    $coreFallbackSettings,
                    $rawWebsiteSettings
                );

        } elseif (
            $rawWebsiteSettings instanceof
                \Illuminate\Contracts\Support\Arrayable
        ) {

            $coreFallbackSettings =
                array_replace_recursive(
                    $coreFallbackSettings,
                    $rawWebsiteSettings->toArray()
                );
        }
    }

    /*
     * Read tenant settings when the current route did not already
     * receive them from its controller.
     *
     * IMPORTANT:
     * Keep the DB key literally as stored.
     *
     * Example:
     * theme.corporate.footer_logo_path
     *
     * Do NOT use data_set() here because that would change the
     * established Core settings structure.
     */
    if (empty($coreExistingSettings)) {

        try {

            $tenantSchema =
                \Illuminate\Support\Facades\Schema::connection(
                    'tenant'
                );

            $settingsTable = null;

            if ($tenantSchema->hasTable('site_settings')) {
                $settingsTable = 'site_settings';
            } elseif ($tenantSchema->hasTable('settings')) {
                $settingsTable = 'settings';
            }

            if ($settingsTable) {

                $rows =
                    \Illuminate\Support\Facades\DB::connection(
                        'tenant'
                    )
                    ->table($settingsTable)
                    ->get();

                foreach ($rows as $row) {

                    $key =
                        $row->key
                        ?? $row->name
                        ?? $row->setting_key
                        ?? null;

                    if (!$key) {
                        continue;
                    }

                    $value =
                        $row->value
                        ?? $row->setting_value
                        ?? null;

                    if (is_string($value)) {

                        $decoded =
                            json_decode(
                                $value,
                                true
                            );

                        if (
                            json_last_error()
                                === JSON_ERROR_NONE
                            && (
                                is_array($decoded)
                                || is_bool($decoded)
                                || is_numeric($decoded)
                            )
                        ) {
                            $value = $decoded;
                        }
                    }

                    /*
                     * FLAT KEY IS INTENTIONAL.
                     */
                    $coreFallbackSettings[
                        (string) $key
                    ] = $value;
                }
            }

        } catch (\Throwable $e) {
            /*
             * Shell must remain usable even if optional fallback
             * storage cannot be read.
             */
        }
    }

    /*
     * Existing controller-provided settings remain authoritative.
     */
    $settings = array_replace_recursive(
        $coreFallbackSettings,
        $coreExistingSettings
    );

    /*
     * Compatibility aliases from page/theme variables.
     * These do not create a second branding source.
     */
    if (
        empty(
            $settings[
                'theme.corporate.footer_logo_path'
            ] ?? null
        )
        && isset($theme)
        && is_array($theme)
        && !empty($theme['footer_logo_path'] ?? null)
    ) {
        $settings[
            'theme.corporate.footer_logo_path'
        ] = $theme['footer_logo_path'];
    }

    if (
        empty(
            $settings[
                'theme.corporate.footer_logo_path'
            ] ?? null
        )
        && isset($siteConfig)
        && is_array($siteConfig)
        && !empty(
            $siteConfig['footer_logo_path'] ?? null
        )
    ) {
        $settings[
            'theme.corporate.footer_logo_path'
        ] = $siteConfig['footer_logo_path'];
    }
@endphp

    {{-- ESUBIZ_AUTH_CANONICAL_FAVICON_V73 --}}
    @php
        /*
         * Same Core favicon setting used by Site Settings.
         * No separate auth favicon configuration.
         */
        $authFaviconPath =
            $settings['website_favicon_path']
                ?? $settings['favicon_path']
                ?? $settings['theme.corporate.favicon_path']
                ?? data_get(
                    $settings ?? [],
                    'theme.corporate.favicon_path'
                )
                ?? null;

        $authFaviconUrl = null;

        if (
            is_string($authFaviconPath)
            && trim($authFaviconPath) !== ''
        ) {
            $authFaviconPath = trim($authFaviconPath);

            if (
                str_starts_with($authFaviconPath, 'http://')
                || str_starts_with($authFaviconPath, 'https://')
            ) {
                $authFaviconUrl = $authFaviconPath;
            } else {
                $authFaviconPath = ltrim(
                    $authFaviconPath,
                    '/'
                );

                if (
                    str_starts_with(
                        $authFaviconPath,
                        'media/'
                    )
                ) {
                    $authFaviconPath = substr(
                        $authFaviconPath,
                        strlen('media/')
                    );
                }

                $authFaviconUrl =
                    request()->getSchemeAndHttpHost()
                    . '/media/'
                    . $authFaviconPath;
            }
        }
    @endphp

    @if($authFaviconUrl)
        <link
            rel="icon"
            href="{{ $authFaviconUrl }}?v={{ md5($authFaviconUrl) }}"
        >
        <link
            rel="shortcut icon"
            href="{{ $authFaviconUrl }}?v={{ md5($authFaviconUrl) }}"
        >
    @endif

<meta charset="UTF-8">
<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>
<title>Forgot Password - {{ $website->name ?? 'Website' }}</title>

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

</style>


{{-- ESUBIZ_PUBLIC_AUTH_ADMIN_CONFIG_RENDER_V1 --}}

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

<style id="esubiz-auth-public-style">
/* ESUBIZ_SHARED_AUTH_BACKGROUND_FIX_V1 */
html,
body {
    background-color:
        {{ $authPageConfig['background_color'] ?? '#f5f7fb' }}
        !important;
}

.auth-shell,
.auth-page,
.auth-wrapper,
.auth-container {
    background-color:
        {{ $authPageConfig['background_color'] ?? '#f5f7fb' }}
        !important;
}

.auth-card {
    background-color:
        {{ $authPageConfig['card_color'] ?? '#ffffff' }}
        !important;

    color:
        {{ $authPageConfig['text_color'] ?? '#111827' }}
        !important;
}

.auth-card h1,
.auth-card h2,
.auth-card h3,
.auth-card p,
.auth-card label {
    color:
        {{ $authPageConfig['text_color'] ?? '#111827' }};
}

.auth-card .btn-primary {
    background-color:
        {{ $authPageConfig['button_color'] ?? '#111827' }}
        !important;

    border-color:
        {{ $authPageConfig['button_color'] ?? '#111827' }}
        !important;
}


    body {
        background:
            {{ $authBackground }}
            !important;
    }

    .auth-card {
        background:
            {{ $authCard }}
            !important;
        color:
            {{ $authText }}
            !important;
    }

    .auth-card h1,
    .auth-card h2,
    .auth-card h3,
    .auth-card p,
    .auth-card label,
    .auth-card .site-note {
        color:
            {{ $authText }};
    }

    .btn-primary {
        background:
            {{ $authButton }}
            !important;
        border-color:
            {{ $authButton }}
            !important;
    }

    .auth-home-link {
        margin-top:16px;
        text-align:center;
    }

    .auth-home-link a {
        color:
            {{ $authText }};
        font-size:13px;
        font-weight:700;
        text-decoration:none;
        opacity:.72;
    }

    .auth-home-link a:hover {
        opacity:1;
        text-decoration:underline;
    }

    .site-note a {
        color:inherit;
        text-decoration:none;
    }

    .site-note a:hover {
        text-decoration:underline;
    }


/* ESUBIZ_AUTH_PROVIDER_TOP_CARDS_V1 */

.esubiz-provider-cards {
    display: grid;
    gap: 9px;
    width: 100%;
    margin: 0 0 20px;
}

.esubiz-provider-card {
    display: flex !important;
    align-items: center;
    justify-content: flex-start;
    gap: 11px;

    width: 100%;
    min-height: 46px;
    padding: 8px 12px;

    border: 1px solid rgba(127, 127, 127, .24);
    border-radius: 10px;

    background: rgba(255, 255, 255, .04);

    color: inherit !important;
    text-decoration: none !important;

    box-sizing: border-box;
    transition:
        transform .15s ease,
        border-color .15s ease,
        background .15s ease;
}

.esubiz-provider-card:hover {
    transform: translateY(-1px);
    border-color: rgba(127, 127, 127, .42);
    background: rgba(127, 127, 127, .07);
}

.esubiz-provider-logo {
    width: 29px;
    height: 29px;
    flex: 0 0 29px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    border-radius: 8px;
    overflow: hidden;

    background: #fff;
    color: #111827;

    font-size: 15px;
    font-weight: 700;
    line-height: 1;
}

.esubiz-provider-logo img,
.esubiz-provider-logo svg {
    display: block;
    width: 19px;
    height: 19px;
    object-fit: contain;
}

.esubiz-provider-logo--esubiz {
    background: #111827;
    color: #fff;
}

.esubiz-provider-copy {
    display: block;
    min-width: 0;

    color: inherit;
    font-size: 13px;
    font-weight: 600;
    line-height: 1.3;
}

</style>

</head>
<body>
<div class="auth-shell">
<div class="auth-card">

{{-- ESUBIZ_SHARED_AUTH_LOGO_RENDER_V3 --}}

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

        $value = trim(
            (string) $value
        );

        if ($value === '') {
            return '';
        }

        if (
            str_starts_with(
                $value,
                'http://'
            )
            || str_starts_with(
                $value,
                'https://'
            )
        ) {
            return $value;
        }

        if (
            str_starts_with(
                $value,
                '/'
            )
        ) {
            return asset(
                ltrim(
                    $value,
                    '/'
                )
            );
        }

        return asset(
            'storage/' .
            ltrim(
                $value,
                '/'
            )
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


<div
    class="brand-mark{{ $hasAuthLogo ? ' has-logo' : '' }}"
>

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
    <h1>Forgot password?</h1>

    <p>
        Enter your email address and we'll send
        you a secure password reset link.
    </p>
</div>

@if(session('status'))
    <div class="alert alert-success">
        {{ session('status') }}
    </div>
@endif

@if($errors->any())
    <div class="alert alert-error">
        {{ $errors->first() }}
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
        'tenant.auth.password.email',
        ['subdomain' => $website->subdomain]
    ) }}"
>
    @csrf

    {{-- ESUBIZ_CORE_AUTH_FORM_BLADE_BINDING_V1 --}}
    @php
        $coreResetFields = collect(
            $authFormFields ?? []
        )->keyBy('name');

        $coreResetEmail =
            $coreResetFields->get(
                'email',
                [
                    'label' => 'Email address',
                    'type' => 'email',
                    'required' => true,
                ]
            );

        $coreResetEmailType =
            in_array(
                $coreResetEmail['type'] ?? '',
                ['email', 'text'],
                true
            )
                ? $coreResetEmail['type']
                : 'email';
    @endphp

    <div class="field">
        <label for="email">
            {{ $coreResetEmail['label'] ?? 'Email address' }}
        </label>

        <input
            id="email"
            name="email"
            type="{{ $coreResetEmailType }}"
            value="{{ old('email') }}"
            required
            autofocus
            autocomplete="email"
            placeholder="you@example.com"
        >
    </div>

    <button
        type="submit"
        class="btn btn-primary"
    >
        Send reset link
    </button>
</form>

<p class="bottom-copy">
    Remember your password?

    <a
        href="{{ route(
            'tenant.auth.login',
            ['subdomain' => $website->subdomain]
        ) }}"
        class="text-link"
    >
        Back to sign in
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

<script>
/* ESUBIZ_PUBLIC_AUTH_PREMIUM_RUNTIME_V17 */

(function () {
    'use strict';

    const providers = [
        'esubiz',
        'google',
        'facebook',
        'instagram',
        'tiktok',
        'x'
    ];

    function normalizePath(path) {

        return String(path || '')
            .replace(/\/+$/, '') || '/';
    }

    function supported(path) {

        path = normalizePath(path);

        return (
            path === '/login'
            || path === '/register'
            || path === '/forgot-password'
            || path.indexOf('/reset-password/') === 0
        );
    }

    function providerFrom(link) {

        const text = String(
            link.textContent || ''
        ).toLowerCase();

        const href = String(
            link.getAttribute('href') || ''
        ).toLowerCase();

        if (
            /\bwith\s+x\b/i.test(text)
            || href.includes('provider=x')
            || href.includes('provider%3dx')
            || href.includes('/x/')
        ) {
            return 'x';
        }

        for (const provider of providers) {

            if (provider === 'x') {
                continue;
            }

            if (
                text.includes(provider)
                || href.includes(
                    'provider=' + provider
                )
                || href.includes(
                    '/' + provider + '/'
                )
                || (
                    provider === 'esubiz'
                    && href.includes('/admin?sso=1')
                )
            ) {
                return provider;
            }
        }

        return null;
    }

    function icon(provider) {

        if (provider === 'esubiz') {
            return 'E';
        }

        if (provider === 'google') {
            return `
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <text
                        x="12"
                        y="17"
                        text-anchor="middle"
                        font-size="17"
                        font-family="Arial,sans-serif"
                        font-weight="700"
                        fill="currentColor"
                    >G</text>
                </svg>
            `;
        }

        if (provider === 'facebook') {
            return `
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path
                        fill="currentColor"
                        d="M13.7 22v-8h2.7l.4-3h-3.1V9.1c0-.9.3-1.5 1.6-1.5H17V4.9c-.3 0-1.3-.1-2.4-.1-2.4 0-4 1.4-4 4.1V11H8v3h2.6v8h3.1Z"
                    />
                </svg>
            `;
        }

        if (provider === 'instagram') {
            return `
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <rect
                        x="3.5"
                        y="3.5"
                        width="17"
                        height="17"
                        rx="5"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    />
                    <circle
                        cx="12"
                        cy="12"
                        r="4"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    />
                    <circle
                        cx="17.4"
                        cy="6.8"
                        r="1.1"
                        fill="currentColor"
                    />
                </svg>
            `;
        }

        if (provider === 'tiktok') {
            return `
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path
                        fill="currentColor"
                        d="M15.3 3c.4 2.3 1.7 3.7 3.7 3.9v3a8.2 8.2 0 0 1-3.7-1.1v6.1a6 6 0 1 1-5.2-5.9v3.1a2.9 2.9 0 1 0 2.1 2.8V3h3.1Z"
                    />
                </svg>
            `;
        }

        return `
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path
                    fill="currentColor"
                    d="M5 4h3.8l3.7 5 4.2-5H19l-5.4 6.4L19.5 20h-3.8l-4-5.4L7.1 20H4.8l5.8-6.8L5 4Z"
                />
            </svg>
        `;
    }

    function collect(source) {

        if (!source) {
            return [];
        }

        return Array.from(
            source.querySelectorAll('a[href]')
        ).filter(function (link) {
            return providerFrom(link) !== null;
        });
    }

    function prepare(card, source, path) {

        if (!card) {
            return;
        }

        path = normalizePath(
            path || window.location.pathname
        );

        card.querySelectorAll(
            '.esubiz-provider-cards, .esubiz-auth-divider-v17'
        ).forEach(function (node) {
            node.remove();
        });

        if (
            path !== '/login'
            && path !== '/register'
        ) {
            return;
        }

        const links = collect(
            source || document
        );

        if (!links.length) {
            return;
        }

        const container =
            document.createElement('div');

        container.className =
            'esubiz-provider-cards';

        const seen = new Set();

        links.forEach(function (original) {

            const provider =
                providerFrom(original);

            if (
                !provider
                || seen.has(provider)
            ) {
                return;
            }

            const href =
                original.getAttribute('href');

            if (!href) {
                return;
            }

            seen.add(provider);

            const link =
                document.createElement('a');

            link.href = href;

            link.className =
                'esubiz-provider-card';

            const label =
                provider === 'x'
                    ? 'X'
                    : provider.charAt(0).toUpperCase()
                        + provider.slice(1);

            link.setAttribute(
                'aria-label',
                (
                    path === '/register'
                        ? 'Register with '
                        : 'Login with '
                ) + label
            );

            link.innerHTML = `
                <span
                    class="esubiz-provider-logo esubiz-provider-logo--${provider}"
                    aria-hidden="true"
                >
                    ${icon(provider)}
                </span>

                <span class="esubiz-provider-copy">
                    ${
                        path === '/register'
                            ? 'Register'
                            : 'Login'
                    } with ${label}
                </span>
            `;

            container.appendChild(
                link
            );
        });

        if (!container.children.length) {
            return;
        }

        const form =
            card.querySelector('form');

        if (!form) {
            return;
        }

        card.insertBefore(
            container,
            form
        );

        const divider =
            document.createElement('div');

        divider.className =
            'esubiz-auth-divider-v17';

        divider.textContent =
            'or continue with email';

        card.insertBefore(
            divider,
            form
        );
    }

    prepare(
        document.querySelector('.auth-card'),
        document,
        window.location.pathname
    );

    document.addEventListener(
        'click',
        async function (event) {

            const link =
                event.target.closest('a[href]');

            if (!link) {
                return;
            }

            if (
                event.button !== 0
                || event.metaKey
                || event.ctrlKey
                || event.shiftKey
                || event.altKey
                || link.target === '_blank'
                || link.hasAttribute('download')
            ) {
                return;
            }

            let target;

            try {

                target = new URL(
                    link.href,
                    window.location.href
                );

            } catch (error) {

                return;
            }

            if (
                target.origin
                !== window.location.origin
                || !supported(
                    target.pathname
                )
            ) {
                return;
            }

            event.preventDefault();
            event.stopImmediatePropagation();

            try {

                const response =
                    await fetch(
                        target.href,
                        {
                            method:'GET',
                            credentials:'same-origin',
                            headers:{
                                'X-Requested-With':
                                    'XMLHttpRequest'
                            }
                        }
                    );

                if (!response.ok) {
                    throw new Error(
                        'Authentication page could not be loaded.'
                    );
                }

                const html =
                    await response.text();

                const parsed =
                    new DOMParser()
                        .parseFromString(
                            html,
                            'text/html'
                        );

                const fetched =
                    parsed.querySelector(
                        '.auth-card'
                    );

                const current =
                    document.querySelector(
                        '.auth-card'
                    );

                if (
                    !fetched
                    || !current
                ) {
                    window.location.href =
                        target.href;
                    return;
                }

                const imported =
                    document.importNode(
                        fetched,
                        true
                    );

                prepare(
                    imported,
                    parsed,
                    target.pathname
                );

                current.replaceWith(
                    imported
                );

                if (parsed.title) {
                    document.title =
                        parsed.title;
                }

                window.history.pushState(
                    {
                        esubizAuthAjaxV17:true
                    },
                    '',
                    target.pathname
                    + target.search
                    + target.hash
                );

                window.scrollTo({
                    top:0,
                    behavior:'smooth'
                });

            } catch (error) {

                window.location.href =
                    target.href;
            }
        },
        true
    );

    window.addEventListener(
        'popstate',
        function () {

            if (
                supported(
                    window.location.pathname
                )
            ) {
                window.location.reload();
            }
        }
    );

})();
</script>


{{-- ESUBIZ_AUTH_AJAX_SWITCHING_ALL_FORMS_V2 --}}
<script>
(function () {
    'use strict';

    function normalizePath(path) {
        return path.replace(
            /\/+$/,
            ''
        ) || '/';
    }

    function isSupportedAuthPath(path) {
        path = normalizePath(path);

        if (
            path === '/login'
            || path === '/register'
            || path === '/forgot-password'
        ) {
            return true;
        }

        /*
         * Supports reset URLs such as:
         * /reset-password/{token}
         * /password/reset/{token}
         *
         * without hardcoding a specific token.
         */
        return (
            path.indexOf(
                '/reset-password/'
            ) === 0
            ||
            path.indexOf(
                '/password/reset/'
            ) === 0
        );
    }

    function isAuthAjaxLink(link) {
        if (!link || !link.href) {
            return false;
        }

        let target;

        try {
            target = new URL(
                link.href,
                window.location.href
            );
        } catch (error) {
            return false;
        }

        /*
         * Never intercept another host.
         * This keeps Core portable for SaaS/off-server.
         */
        if (
            target.origin
            !== window.location.origin
        ) {
            return false;
        }

        return isSupportedAuthPath(
            target.pathname
        );
    }

    function bindPasswordToggles(root) {
        root
            .querySelectorAll(
                '[data-password-toggle]'
            )
            .forEach(function (button) {

                if (
                    button.dataset
                        .ajaxPasswordBound
                    === '1'
                ) {
                    return;
                }

                button.dataset
                    .ajaxPasswordBound = '1';

                button.addEventListener(
                    'click',
                    function () {
                        const target =
                            document.getElementById(
                                button.getAttribute(
                                    'data-password-toggle'
                                )
                            );

                        if (!target) {
                            return;
                        }

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


    /* ESUBIZ_AUTH_PROVIDER_TOP_CARDS_V1 */
    function enhanceProviderCards(root, authPath) {
        if (!root) {
            return;
        }

        /*
         * Providers are useful for Login/Register.
         * Forgot/Reset remain focused password flows.
         */
        const path = (
            authPath
            || window.location.pathname
            || ''
        ).replace(/\/+$/, '');

        if (
            path !== '/login'
            && path !== '/register'
        ) {
            return;
        }

        if (
            root.querySelector(
                '.esubiz-provider-cards'
            )
        ) {
            return;
        }

        const providerNames = [
            'esubiz',
            'google',
            'facebook',
            'instagram',
            'tiktok',
            'x'
        ];

        const links = Array.from(
            root.querySelectorAll('a[href]')
        ).filter(function (link) {
            const text = (
                link.textContent
                || ''
            ).trim().toLowerCase();

            const href = (
                link.getAttribute('href')
                || ''
            ).toLowerCase();

            return providerNames.some(
                function (provider) {

                    if (provider === 'x') {
                        return (
                            /\bwith\s+x\b/i.test(text)
                            || href.includes('/x/')
                            || href.includes('provider=x')
                            || href.includes('provider%3dx')
                        );
                    }

                    return (
                        text.includes(provider)
                        || href.includes(
                            'provider=' + provider
                        )
                        || href.includes(
                            '/' + provider + '/'
                        )
                    );
                }
            );
        });

        if (!links.length) {
            return;
        }

        const container =
            document.createElement('div');

        container.className =
            'esubiz-provider-cards';

        const action =
            path === '/register'
                ? 'Register'
                : 'Login';

        function providerFrom(link) {
            const haystack = (
                (
                    link.textContent
                    || ''
                )
                + ' '
                + (
                    link.getAttribute('href')
                    || ''
                )
            ).toLowerCase();

            if (haystack.includes('esubiz')) {
                return 'esubiz';
            }

            if (haystack.includes('facebook')) {
                return 'facebook';
            }

            if (haystack.includes('instagram')) {
                return 'instagram';
            }

            if (haystack.includes('google')) {
                return 'google';
            }

            if (haystack.includes('tiktok')) {
                return 'tiktok';
            }

            return 'x';
        }

        function iconMarkup(provider) {
            if (provider === 'esubiz') {
                return 'E';
            }

            if (provider === 'facebook') {
                return `
                    <svg
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <path
                            fill="#1877F2"
                            d="M24 12.073C24 5.405 18.627 0 12 0S0 5.405 0 12.073C0 18.1 4.388 23.094 10.125 24v-8.438H7.078v-3.49h3.047V9.413c0-3.024 1.792-4.695 4.533-4.695 1.313 0 2.686.236 2.686.236v2.973h-1.513c-1.49 0-1.956.931-1.956 1.887v2.26h3.328l-.532 3.49h-2.796V24C19.612 23.094 24 18.1 24 12.073Z"
                        />
                    </svg>
                `;
            }

            if (provider === 'instagram') {
                return `
                    <svg
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <rect
                            x="3"
                            y="3"
                            width="18"
                            height="18"
                            rx="5"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        />
                        <circle
                            cx="12"
                            cy="12"
                            r="4"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                        />
                        <circle
                            cx="17.4"
                            cy="6.7"
                            r="1"
                            fill="currentColor"
                        />
                    </svg>
                `;
            }

            if (provider === 'google') {
                return `
                    <svg
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <text
                            x="4"
                            y="18"
                            font-size="18"
                            font-family="Arial,sans-serif"
                            font-weight="700"
                            fill="#4285F4"
                        >G</text>
                    </svg>
                `;
            }

            if (provider === 'tiktok') {
                return `
                    <svg
                        viewBox="0 0 24 24"
                        aria-hidden="true"
                    >
                        <path
                            fill="currentColor"
                            d="M15.6 3c.4 2.2 1.7 3.6 4 4v3.1c-1.5 0-2.8-.4-4-1.2v6.2c0 3.5-2.5 5.9-5.8 5.9-3.2 0-5.8-2.5-5.8-5.7 0-3.6 3.1-6.2 6.7-5.6v3.2c-1.8-.5-3.5.5-3.5 2.4 0 1.4 1.1 2.5 2.5 2.5 1.7 0 2.7-1.3 2.7-3.1V3h3.2Z"
                        />
                    </svg>
                `;
            }

            return `
                <svg
                    viewBox="0 0 24 24"
                    aria-hidden="true"
                >
                    <path
                        fill="currentColor"
                        d="M18.9 2H22l-6.8 7.8L23.2 22H17l-4.9-6.4L6.5 22H3.4l7.2-8.2L2.8 2H9.2l4.4 5.8L18.9 2Zm-1.1 17.8h1.7L8.3 4.1H6.5l11.3 15.7Z"
                    />
                </svg>
            `;
        }

        links.forEach(function (link) {
            const provider =
                providerFrom(link);

            const providerLabel =
                provider === 'x'
                    ? 'X'
                    : provider.charAt(0).toUpperCase()
                        + provider.slice(1);

            link.classList.add(
                'esubiz-provider-card'
            );

            /*
             * Keep the original href/provider route.
             * Only presentation is changed.
             */
            link.innerHTML = `
                <span
                    class="esubiz-provider-logo esubiz-provider-logo--${provider}"
                    aria-hidden="true"
                >
                    ${iconMarkup(provider)}
                </span>

                <span class="esubiz-provider-copy">
                    ${action} with ${providerLabel}
                </span>
            `;

            container.appendChild(link);
        });

        /*
         * Providers should be the first usable block
         * inside the authentication card.
         */
        root.insertBefore(
            container,
            root.firstElementChild
        );

        /*
         * Remove leftover standalone "OR" divider text
         * from the previous provider location where possible.
         */
        Array.from(
            root.querySelectorAll(
                'div, p, span'
            )
        ).forEach(function (element) {
            if (
                element === container
                || container.contains(element)
            ) {
                return;
            }

            if (
                element.children.length === 0
                && /^or$/i.test(
                    (
                        element.textContent
                        || ''
                    ).trim()
                )
            ) {
                element.style.display = 'none';
            }
        });
    }

async function swapAuthPage(
        url,
        options
    ) {
        const settings = Object.assign(
            {
                pushState: true
            },
            options || {}
        );

        let targetUrl;

        try {
            targetUrl = new URL(
                url,
                window.location.href
            );
        } catch (error) {
            window.location.href = url;
            return;
        }

        if (
            targetUrl.origin
            !== window.location.origin
            ||
            !isSupportedAuthPath(
                targetUrl.pathname
            )
        ) {
            window.location.href =
                targetUrl.href;

            return;
        }

        const currentCard =
            document.querySelector(
                '.auth-card'
            );

        if (!currentCard) {
            window.location.href =
                targetUrl.href;

            return;
        }

        currentCard.style.opacity =
            '0.55';

        currentCard.style.pointerEvents =
            'none';

        try {
            const response = await fetch(
                targetUrl.href,
                {
                    method: 'GET',
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With':
                            'XMLHttpRequest',
                        'Accept':
                            'text/html'
                    }
                }
            );

            if (!response.ok) {
                throw new Error(
                    'Auth page request failed.'
                );
            }

            const html =
                await response.text();

            const parser =
                new DOMParser();

            const fetchedDocument =
                parser.parseFromString(
                    html,
                    'text/html'
                );

            const fetchedCard =
                fetchedDocument
                    .querySelector(
                        '.auth-card'
                    );

            if (!fetchedCard) {
                throw new Error(
                    'Fetched auth card missing.'
                );
            }

            /*
             * Swap the complete server-rendered auth card.
             *
             * This preserves Laravel-generated:
             * - CSRF tokens
             * - reset tokens
             * - security fields
             * - configured branding
             * - providers
             * - dynamic registration fields
             * - validation-compatible form actions
             */
            const importedCard =
                document.importNode(
                    fetchedCard,
                    true
                );

            currentCard.replaceWith(
                importedCard
            );

            /*
             * Login/Register may use different Admin colors.
             * Forgot/Reset inherit Login configuration.
             */
            const fetchedStyle =
                fetchedDocument
                    .getElementById(
                        'esubiz-auth-public-style'
                    );

            const currentStyle =
                document
                    .getElementById(
                        'esubiz-auth-public-style'
                    );

            if (
                fetchedStyle
                && currentStyle
            ) {
                currentStyle.textContent =
                    fetchedStyle.textContent;
            }

            if (fetchedDocument.title) {
                document.title =
                    fetchedDocument.title;
            }

            bindPasswordToggles(
                importedCard
            );

            enhanceProviderCards(
                importedCard,
                targetUrl.pathname
            );

            if (settings.pushState) {
                window.history.pushState(
                    {
                        esubizAuthAjax: true
                    },
                    '',
                    targetUrl.pathname
                    + targetUrl.search
                    + targetUrl.hash
                );
            }

            const focusTarget =
                importedCard.querySelector(
                    'input:not([type="hidden"]):not([tabindex="-1"])'
                );

            if (focusTarget) {
                window.setTimeout(
                    function () {
                        focusTarget.focus();
                    },
                    50
                );
            }

        } catch (error) {

            /*
             * AJAX is progressive enhancement.
             * Never strand the visitor if it fails.
             */
            window.location.href =
                targetUrl.href;
        }
    }

    document.addEventListener(
        'click',
        function (event) {
            const link =
                event.target.closest('a');

            if (
                !isAuthAjaxLink(link)
            ) {
                return;
            }

            /*
             * Preserve new-tab and modifier behavior.
             */
            if (
                event.button !== 0
                || event.metaKey
                || event.ctrlKey
                || event.shiftKey
                || event.altKey
            ) {
                return;
            }

            event.preventDefault();

            swapAuthPage(
                link.href,
                {
                    pushState: true
                }
            );
        }
    );

    window.addEventListener(
        'popstate',
        function () {
            if (
                isSupportedAuthPath(
                    window.location.pathname
                )
            ) {
                swapAuthPage(
                    window.location.href,
                    {
                        pushState: false
                    }
                );
            }
        }
    );


    enhanceProviderCards(
        document.querySelector('.auth-card'),
        window.location.pathname
    );

})();
</script>


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

</body>
</html>
