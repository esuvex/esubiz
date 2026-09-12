<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1, viewport-fit=cover">

    <title>Choose New Password | {{ config('app.name', 'Esubiz') }}</title>

    @php
        /*
         * ESUBIZ_CENTRAL_AUTH_PAGES_V19
         *
         * Central has its own branding authority.
         * Core auth branding remains separate.
         */
        $esubizCentralAuthBrand =
            app(\App\Services\Platform\CentralAuthUiService::class)
                ->brand();

        $esubizCentralAuthLogo =
            $esubizCentralAuthBrand['auth_logo']
            ?? $esubizCentralAuthBrand['logo']
            ?? null;

        $esubizCentralAuthFavicon =
            $esubizCentralAuthBrand['auth_favicon']
            ?? $esubizCentralAuthBrand['favicon']
            ?? null;
    @endphp

    @if($esubizCentralAuthFavicon)
        <link rel="icon"
              type="image/webp"
              href="{{ $esubizCentralAuthFavicon }}">
        <link rel="shortcut icon"
              href="{{ $esubizCentralAuthFavicon }}">
        <link rel="apple-touch-icon"
              href="{{ $esubizCentralAuthFavicon }}">
    @endif

    <style>
        /*
         * ESUBIZ_CENTRAL_PREMIUM_AUTH_V19
         */

        :root {
            --esubiz-navy: #0b1f3a;
            --esubiz-blue: #1769ff;
            --esubiz-bg: #f4f7fb;
            --esubiz-card: #ffffff;
            --esubiz-text: #142033;
            --esubiz-muted: #6b7483;
            --esubiz-border: #dfe5ec;
            --esubiz-danger: #c62828;
            --esubiz-shadow: 0 24px 70px rgba(11, 31, 58, .12);
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            min-height: 100%;
        }

        body {
            margin: 0;
            background:
                radial-gradient(circle at top left,
                    rgba(23, 105, 255, .09),
                    transparent 34rem),
                var(--esubiz-bg);
            color: var(--esubiz-text);
            font-family:
                Inter,
                ui-sans-serif,
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        button,
        input,
        select {
            font: inherit;
        }

        .esubiz-auth-page {
            min-height: 100vh;
            min-height: 100dvh;
            display: grid;
            place-items: center;
            padding: 34px 18px;
        }

        .esubiz-auth-wrap {
            width: min(100%, 480px);
        }

        .esubiz-auth-brand {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 62px;
            margin: 0 auto 20px;
        }

        .esubiz-auth-brand img {
            display: block;
            width: auto;
            height: auto;
            max-width: 180px;
            max-height: 60px;
            object-fit: contain;
        }

        .esubiz-auth-wordmark {
            color: var(--esubiz-navy);
            font-size: 29px;
            line-height: 1;
            font-weight: 800;
            letter-spacing: -.04em;
        }

        .esubiz-auth-card {
            background: var(--esubiz-card);
            border: 1px solid rgba(223, 229, 236, .9);
            border-radius: 22px;
            padding: 30px;
            box-shadow: var(--esubiz-shadow);
        }

        .esubiz-auth-eyebrow {
            display: inline-flex;
            align-items: center;
            min-height: 28px;
            padding: 5px 10px;
            margin-bottom: 13px;
            border-radius: 999px;
            background: rgba(23, 105, 255, .08);
            color: var(--esubiz-blue);
            font-size: 12px;
            line-height: 1;
            font-weight: 750;
            letter-spacing: .04em;
            text-transform: uppercase;
        }

        .esubiz-auth-title {
            margin: 0;
            color: var(--esubiz-navy);
            font-size: clamp(26px, 6vw, 34px);
            line-height: 1.12;
            font-weight: 800;
            letter-spacing: -.035em;
        }

        .esubiz-auth-description {
            margin: 10px 0 0;
            color: var(--esubiz-muted);
            font-size: 14px;
            line-height: 1.65;
        }

        .esubiz-auth-heading {
            margin-bottom: 25px;
        }

        .esubiz-auth-form {
            margin: 0;
        }

        .esubiz-field {
            margin-bottom: 18px;
        }

        .esubiz-field-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            margin-bottom: 8px;
        }

        .esubiz-label {
            display: block;
            margin: 0 0 8px;
            color: var(--esubiz-text);
            font-size: 13px;
            line-height: 1.3;
            font-weight: 700;
        }

        .esubiz-field-row .esubiz-label {
            margin: 0;
        }

        .esubiz-input-wrap {
            position: relative;
        }

        .esubiz-input {
            display: block;
            width: 100%;
            height: 52px;
            min-height: 52px;
            padding: 0 15px;
            border: 1px solid var(--esubiz-border);
            border-radius: 12px;
            outline: 0;
            background: #fff;
            color: var(--esubiz-text);
            font-size: 15px;
            transition:
                border-color .18s ease,
                box-shadow .18s ease;
        }

        .esubiz-input:focus {
            border-color: rgba(23, 105, 255, .7);
            box-shadow: 0 0 0 4px rgba(23, 105, 255, .10);
        }

        .esubiz-input::placeholder {
            color: #a1a8b3;
        }

        .esubiz-password-input {
            padding-right: 58px;
        }

        .esubiz-password-toggle {
            position: absolute;
            top: 50%;
            right: 7px;
            transform: translateY(-50%);
            width: 42px;
            height: 38px;
            padding: 0;
            border: 0;
            border-radius: 9px;
            background: transparent;
            color: var(--esubiz-muted);
            font-size: 11px;
            font-weight: 750;
            cursor: pointer;
        }

        .esubiz-password-toggle:hover {
            background: #f1f4f8;
            color: var(--esubiz-navy);
        }

        .esubiz-error {
            margin: 7px 0 0;
            color: var(--esubiz-danger);
            font-size: 12px;
            line-height: 1.45;
        }

        .esubiz-status {
            margin-bottom: 19px;
            padding: 12px 14px;
            border: 1px solid #bde3cd;
            border-radius: 11px;
            background: #effaf3;
            color: #17713c;
            font-size: 13px;
            line-height: 1.5;
        }

        .esubiz-auth-link {
            color: var(--esubiz-blue);
            font-size: 13px;
            font-weight: 700;
            text-decoration: none;
        }

        .esubiz-auth-link:hover {
            text-decoration: underline;
        }

        .esubiz-remember {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--esubiz-muted);
            font-size: 13px;
            cursor: pointer;
        }

        .esubiz-remember input {
            width: 17px;
            height: 17px;
            margin: 0;
            accent-color: var(--esubiz-blue);
        }

        .esubiz-form-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            margin: -2px 0 20px;
        }

        .esubiz-auth-submit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            min-height: 52px;
            padding: 12px 20px;
            border: 0;
            border-radius: 12px;
            background: var(--esubiz-navy);
            color: #fff;
            font-weight: 750;
            font-size: 14px;
            line-height: 1.2;
            cursor: pointer;
            transition:
                transform .15s ease,
                opacity .15s ease;
        }

        .esubiz-auth-submit:hover {
            opacity: .94;
        }

        .esubiz-auth-submit:active {
            transform: translateY(1px);
        }

        .esubiz-auth-bottom {
            margin: 21px 0 0;
            text-align: center;
            color: var(--esubiz-muted);
            font-size: 13px;
            line-height: 1.6;
        }

        .esubiz-auth-footer {
            margin-top: 18px;
            text-align: center;
            color: #8a93a0;
            font-size: 11px;
            line-height: 1.55;
        }

        @media (max-width: 520px) {
            .esubiz-auth-page {
                padding: 22px 13px;
                align-items: start;
            }

            .esubiz-auth-wrap {
                margin-top: 8px;
            }

            .esubiz-auth-card {
                padding: 23px 18px;
                border-radius: 18px;
            }

            .esubiz-auth-brand {
                margin-bottom: 14px;
            }

            .esubiz-form-meta {
                align-items: flex-start;
            }
        }

        @media (max-width: 360px) {
            .esubiz-auth-page {
                padding-left: 10px;
                padding-right: 10px;
            }

            .esubiz-auth-card {
                padding-left: 15px;
                padding-right: 15px;
            }

            .esubiz-form-meta {
                gap: 9px;
            }
        }
    </style>

    <style>
        /*
         * ESUBIZ_CENTRAL_CORE_AUTH_VISUAL_V20
         */

        .esubiz-auth-wrap {
            width: min(100%, 655px);
        }

        .esubiz-auth-card {
            padding: 48px 48px 38px;
        }

        .esubiz-core-parity-brand {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            margin: 0 auto 30px;
        }

        .esubiz-core-parity-brand .esubiz-auth-brand {
            min-height: 0;
            margin: 0 !important;
        }

        .esubiz-core-parity-brand .esubiz-auth-brand img {
            max-width: 180px;
            max-height: 60px;
        }

        .esubiz-auth-heading {
            margin-bottom: 29px;
            text-align: center;
        }

        .esubiz-auth-eyebrow {
            display: none;
        }

        .esubiz-auth-title {
            text-align: center;
        }

        .esubiz-auth-description {
            max-width: 470px;
            margin-left: auto;
            margin-right: auto;
            text-align: center;
        }

        .esubiz-core-provider-section {
            width: 100%;
            margin: 29px 0 30px;
            text-align: center;
        }

        .esubiz-core-provider-title {
            margin-bottom: 15px;
            color: var(--esubiz-text);
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
                box-shadow .16s ease;
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
            margin: 24px 0 0;
            text-align: center;
        }

        .esubiz-core-back-home a {
            color: var(--esubiz-muted);
            font-size: 13px;
            line-height: 1.4;
            font-weight: 650;
            text-decoration: none;
        }

        .esubiz-core-back-home a:hover {
            color: var(--esubiz-navy);
            text-decoration: underline;
        }

        @media (max-width: 640px) {
            .esubiz-auth-wrap {
                width: 100%;
            }

            .esubiz-auth-card {
                padding: 30px 20px 27px;
            }

            .esubiz-core-parity-brand {
                margin-bottom: 25px;
            }

            .esubiz-core-auth-divider {
                gap: 8px;
                font-size: 10px;
            }
        }

        @media (max-width: 360px) {
            .esubiz-auth-card {
                padding-left: 16px;
                padding-right: 16px;
            }
        }
    </style>

</head>

<body>
<div class="esubiz-auth-page">
    <main class="esubiz-auth-wrap">



        <section class="esubiz-auth-card">
            {{-- ESUBIZ_CENTRAL_CORE_AUTH_PARITY_V20 --}}
            <div class="esubiz-core-parity-brand">
        <a href="{{ url('/') }}"
           class="esubiz-auth-brand"
           aria-label="Esubiz home">
            @if($esubizCentralAuthLogo)
                <img
                    class="esubiz-auth-brand-logo"
                    src="{{ $esubizCentralAuthLogo }}"
                    alt="Esubiz"
                >
            @else
                <span class="esubiz-auth-wordmark">
                    Esubiz
                </span>
            @endif
        </a>
            </div>

            <div class="esubiz-auth-heading">
                <span class="esubiz-auth-eyebrow">
                    Account recovery
                </span>

                <h1 class="esubiz-auth-title">
                    Create a new password
                </h1>

                <p class="esubiz-auth-description">
                    Choose a secure new password for your Esubiz account.
                </p>
            </div>


            {{-- ESUBIZ_CENTRAL_PROVIDER_AREA_V20 --}}
            @php
                $esubizCentralAuthProviders =
                    app(\App\Services\Platform\CentralAuthUiService::class)
                        ->enabledProviders();
            @endphp

            @if(!empty($esubizCentralAuthProviders))
                <div class="esubiz-core-provider-section">
                    <div class="esubiz-core-provider-title">
                        Continue with
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
<form method="POST" action="{{ route('password.store') }}" class="esubiz-auth-form">
                @csrf

            <input type="hidden" name="token" value="{{ $request->

                <div class="esubiz-field">
                    <label class="esubiz-label"
                           for="email">
                        Email address
                    </label>

                    <input
                        class="esubiz-input"
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email', request('email')) }}"
                        required
                        autocomplete="email"
                        placeholder="Enter your email address"
                    >

                    @error('email')
                        <p class="esubiz-error">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="esubiz-field">
                    <label class="esubiz-label"
                           for="password">
                        New password
                    </label>

                    <div class="esubiz-input-wrap">
                        <input
                            class="esubiz-input esubiz-password-input"
                            id="password"
                            type="password"
                            name="password"
                            required
                            autocomplete="new-password"
                            placeholder="Create a new password"
                        >

                        <button
                            type="button"
                            class="esubiz-password-toggle"
                            data-esubiz-password-toggle="password"
                            aria-label="Show password"
                        >
                            Show
                        </button>
                    </div>

                    @error('password')
                        <p class="esubiz-error">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="esubiz-field">
                    <label class="esubiz-label"
                           for="password_confirmation">
                        Confirm new password
                    </label>

                    <div class="esubiz-input-wrap">
                        <input
                            class="esubiz-input esubiz-password-input"
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            required
                            autocomplete="new-password"
                            placeholder="Repeat your new password"
                        >

                        <button
                            type="button"
                            class="esubiz-password-toggle"
                            data-esubiz-password-toggle="password_confirmation"
                            aria-label="Show password confirmation"
                        >
                            Show
                        </button>
                    </div>
                </div>

                <button
                    type="submit"
                    class="esubiz-auth-submit"
                >
                    Reset password
                </button>
            </form>

            <p class="esubiz-auth-bottom">
                Return to
                <a class="esubiz-auth-link"
                   href="{{ route('login') }}">
                    sign in
                </a>
            </p>


            {{-- ESUBIZ_CENTRAL_BACK_HOME_V20 --}}
            <div class="esubiz-core-back-home">
                <a href="{{ url('/') }}">
                    ← Back to Homepage
                </a>
            </div>

        </section>

        <div class="esubiz-auth-footer">
            &copy; {{ now()->year }} Esubiz.
            All rights reserved.
        </div>
    </main>
</div>

<script>
document.addEventListener('click', function (event) {
    const button = event.target.closest(
        '[data-esubiz-password-toggle]'
    );

    if (!button) {
        return;
    }

    const targetId = button.getAttribute(
        'data-esubiz-password-toggle'
    );

    const input = document.getElementById(
        targetId
    );

    if (!input) {
        return;
    }

    const revealing =
        input.type === 'password';

    input.type =
        revealing
            ? 'text'
            : 'password';

    button.textContent =
        revealing
            ? 'Hide'
            : 'Show';

    button.setAttribute(
        'aria-label',
        revealing
            ? 'Hide password'
            : 'Show password'
    );
});
</script>
</body>
</html>
