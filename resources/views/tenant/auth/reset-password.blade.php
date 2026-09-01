<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>
<title>Reset Password - {{ $website->name ?? 'Website' }}</title>

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
    <h1>Reset password</h1>

    <p>
        Create a new password for your account.
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

<form
    method="POST"
    action="{{ route(
        'tenant.auth.password.update',
        ['subdomain' => $website->subdomain]
    ) }}"
>
    @csrf

    <input
        type="hidden"
        name="token"
        value="{{ $token }}"
    >

    <div class="field">
        <label for="email">
            Email address
        </label>

        <input
            id="email"
            name="email"
            type="email"
            value="{{ old('email', $email) }}"
            required
            autocomplete="email"
        >
    </div>

    <div class="field">
        <label for="password">
            New password
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
            Confirm new password
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
                placeholder="Repeat your new password"
            >

            <button
                type="button"
                class="password-toggle"
                data-password-toggle="password_confirmation"
            >
                Show
            </button>
        </div>
    </div>

    <button
        type="submit"
        class="btn btn-primary"
    >
        Reset password
    </button>
</form>

<p class="bottom-copy">
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

})();
</script>

</body>
</html>
