@extends('tenant.admin.layouts.app')

@section('title', 'Authentication Settings')

@section('content')

{{-- ESUBIZ_TENANT_AUTH_SETTINGS_UI_V1 --}}

@php
    $providers = [
        'esubiz' => [
            'name' => 'Esubiz',
            'letter' => 'E',
            'description' => 'Allow users to continue with their Esubiz account.',
            'credentials' => false,
        ],

        'google' => [
            'name' => 'Google',
            'letter' => 'G',
            'description' => 'Allow users to sign in or register with Google.',
            'credentials' => true,
        ],

        'facebook' => [
            'name' => 'Facebook',
            'letter' => 'f',
            'description' => 'Allow users to sign in or register with Facebook.',
            'credentials' => true,
        ],

        'instagram' => [
            'name' => 'Instagram',
            'letter' => '◎',
            'description' => 'Allow users to authenticate with Instagram.',
            'credentials' => true,
        ],

        'tiktok' => [
            'name' => 'TikTok',
            'letter' => '♪',
            'description' => 'Allow users to authenticate with TikTok.',
            'credentials' => true,
        ],

        'x' => [
            'name' => 'X',
            'letter' => 'X',
            'description' => 'Allow users to authenticate with X.',
            'credentials' => true,
        ],
    ];
@endphp

<style>
    .auth-settings-page {
        max-width: 1180px;
        margin: 0 auto;
        padding-bottom: 60px;
    }

    .auth-settings-head {
        margin-bottom: 24px;
    }

    .auth-settings-head h1 {
        margin: 0 0 7px;
        font-size: 28px;
        line-height: 1.2;
        color: #111827;
    }

    .auth-settings-head p {
        margin: 0;
        color: #6b7280;
        font-size: 14px;
    }

    .auth-alert {
        padding: 13px 16px;
        border-radius: 10px;
        margin-bottom: 20px;
        font-size: 14px;
    }

    .auth-alert-success {
        background: #ecfdf5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }

    .auth-alert-error {
        background: #fef2f2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }

    .auth-tabs {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 22px;
        border-bottom: 1px solid #e5e7eb;
        padding-bottom: 10px;
    }

    .auth-tab {
        appearance: none;
        border: 0;
        background: transparent;
        color: #6b7280;
        font-weight: 600;
        padding: 10px 14px;
        border-radius: 8px;
        cursor: pointer;
        font-size: 14px;
    }

    .auth-tab.active {
        background: #111827;
        color: #fff;
    }

    .auth-panel {
        display: none;
    }

    .auth-panel.active {
        display: block;
    }

    .auth-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        padding: 22px;
        margin-bottom: 18px;
    }

    .auth-card-title {
        margin: 0 0 5px;
        font-size: 17px;
        color: #111827;
    }

    .auth-card-subtitle {
        margin: 0 0 20px;
        color: #6b7280;
        font-size: 13px;
        line-height: 1.55;
    }

    .provider-grid {
        display: grid;
        grid-template-columns:
            repeat(auto-fit, minmax(300px, 1fr));
        gap: 15px;
    }

    .provider-card {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 17px;
    }

    .provider-head {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .provider-logo {
        width: 42px;
        height: 42px;
        flex: 0 0 42px;
        border-radius: 10px;
        border: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
        color: #111827;
        font-size: 19px;
        font-weight: 800;
    }

    .provider-info {
        flex: 1;
        min-width: 0;
    }

    .provider-info strong {
        display: block;
        color: #111827;
        font-size: 14px;
    }

    .provider-info span {
        display: block;
        color: #6b7280;
        font-size: 12px;
        margin-top: 3px;
        line-height: 1.4;
    }

    .switch {
        position: relative;
        width: 44px;
        height: 24px;
        flex: 0 0 44px;
    }

    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }

    .switch-slider {
        position: absolute;
        inset: 0;
        cursor: pointer;
        background: #d1d5db;
        border-radius: 999px;
        transition: .2s;
    }

    .switch-slider:before {
        content: "";
        position: absolute;
        width: 18px;
        height: 18px;
        left: 3px;
        top: 3px;
        background: #fff;
        border-radius: 50%;
        transition: .2s;
    }

    .switch input:checked + .switch-slider {
        background: #111827;
    }

    .switch input:checked + .switch-slider:before {
        transform: translateX(20px);
    }

    .provider-credentials {
        margin-top: 16px;
        padding-top: 16px;
        border-top: 1px solid #f0f1f3;
    }

    .field-grid {
        display: grid;
        grid-template-columns:
            repeat(2, minmax(0, 1fr));
        gap: 15px;
    }

    .field {
        margin-bottom: 15px;
    }

    .field:last-child {
        margin-bottom: 0;
    }

    .field label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 7px;
    }

    .field input,
    .field textarea,
    .field select {
        box-sizing: border-box;
        width: 100%;
        min-height: 43px;
        border: 1px solid #d1d5db;
        border-radius: 9px;
        padding: 10px 12px;
        background: #fff;
        color: #111827;
        outline: none;
    }

    .field input:focus,
    .field textarea:focus,
    .field select:focus {
        border-color: #111827;
    }

    .field-help {
        margin-top: 6px;
        color: #9ca3af;
        font-size: 11px;
        line-height: 1.4;
    }

    .color-row {
        display: grid;
        grid-template-columns:
            repeat(4, minmax(0, 1fr));
        gap: 12px;
    }

    .color-field {
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 11px;
    }

    .color-field label {
        display: block;
        font-size: 11px;
        font-weight: 600;
        color: #6b7280;
        margin-bottom: 8px;
    }

    .color-field input {
        width: 100%;
        height: 38px;
        padding: 2px;
        border: 0;
        background: transparent;
        cursor: pointer;
    }

    .option-row {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
        padding: 15px 0;
        border-bottom: 1px solid #f0f1f3;
    }

    .option-row:last-child {
        border-bottom: 0;
    }

    .option-copy strong {
        display: block;
        font-size: 14px;
        color: #111827;
    }

    .option-copy span {
        display: block;
        color: #6b7280;
        font-size: 12px;
        margin-top: 4px;
    }

    .coming-soon {
        border: 1px dashed #d1d5db;
        border-radius: 12px;
        padding: 24px;
        text-align: center;
        color: #6b7280;
    }

    .coming-soon strong {
        display: block;
        color: #111827;
        margin-bottom: 6px;
    }

    .auth-save-bar {
        position: sticky;
        bottom: 15px;
        display: flex;
        justify-content: flex-end;
        margin-top: 20px;
        pointer-events: none;
    }

    .auth-save {
        pointer-events: auto;
        border: 0;
        border-radius: 10px;
        background: #111827;
        color: #fff;
        font-weight: 700;
        padding: 12px 22px;
        cursor: pointer;
        box-shadow: 0 7px 20px rgba(0,0,0,.14);
    }

    @media (max-width: 760px) {
        .field-grid,
        .color-row {
            grid-template-columns: 1fr;
        }

        .provider-grid {
            grid-template-columns: 1fr;
        }
    }

    /* ESUBIZ_AUTH_COLOR_PICKER_HEX_CSS_V1 */
    .auth-color-control {
        display:flex;
        align-items:center;
        gap:10px;
    }

    .auth-color-control input[type="color"] {
        width:52px;
        height:44px;
        flex:0 0 52px;
        padding:3px;
        border:1px solid #d1d5db;
        border-radius:9px;
        background:#ffffff;
        cursor:pointer;
    }

    .auth-color-control input[type="text"] {
        width:130px;
        height:44px;
        padding:0 12px;
        border:1px solid #d1d5db;
        border-radius:9px;
        background:#ffffff;
        color:#111827;
        font-family:monospace;
        font-size:14px;
        text-transform:lowercase;
    }

</style>

<div class="auth-settings-page">

    <div class="auth-settings-head">
        <h1>Authentication</h1>

        <p>
            Configure how people sign in and create accounts
            on {{ $website->name ?? 'this website' }}.
        </p>
    </div>

    @if(session('success'))
        <div class="auth-alert auth-alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="auth-alert auth-alert-error">
            <strong>Please check the form.</strong>

            <div style="margin-top:6px;">
                {{ $errors->first() }}
            </div>
        </div>
    @endif

    <form
        method="POST"
        {{-- ESUBIZ_CORE_PORTABLE_AUTH_SETTINGS_ACTION_V1 --}}
        action="/admin/settings/authentication"
        {{-- ESUBIZ_SHARED_AUTH_LOGO_MULTIPART_V1 --}}
        enctype="multipart/form-data"
    >
        @csrf

        <div class="auth-tabs">

            <button
                type="button"
                class="auth-tab active"
                data-auth-tab="providers"
            >
                Login Providers
            </button>

            <button
                type="button"
                class="auth-tab"
                data-auth-tab="branding"
            >
                Branding
            </button>

                        <button
                type="button"
                class="auth-tab"
                data-auth-tab="security"
            >
                Security
            </button>

            <button
                type="button"
                class="auth-tab"
                data-auth-tab="appearance"
            >
                Appearance
            </button>

<button
                type="button"
                class="auth-tab"
                data-auth-tab="registration"
            >
                Registration
            </button>

            <button
                type="button"
                class="auth-tab"
                data-auth-tab="identity"
            >
                Identity Provider
            </button>

        </div>


        {{-- LOGIN PROVIDERS --}}
        <div
            class="auth-panel active"
            data-auth-panel="providers"
        >

            <div class="auth-card">

                <h2 class="auth-card-title">
                    Login Providers
                </h2>

                <p class="auth-card-subtitle">
                    Enabled providers will appear above the normal
                    login and registration forms.
                </p>

                <div class="provider-grid">

                    @foreach($providers as $key => $provider)

                        @php
                            $config =
                                $authConfig['providers'][$key]
                                ?? [];
                        @endphp

                        <div class="provider-card">

                            <div class="provider-head">

                                <div class="provider-logo">
                                    {{ $provider['letter'] }}
                                </div>

                                <div class="provider-info">
                                    <strong>
                                        {{ $provider['name'] }}
                                    </strong>

                                    <span>
                                        {{ $provider['description'] }}
                                    </span>
                                </div>

                                <label class="switch">

                                    <input
                                        type="hidden"
                                        name="providers[{{ $key }}][enabled]"
                                        value="0"
                                    >

                                    <input
                                        type="checkbox"
                                        name="providers[{{ $key }}][enabled]"
                                        value="1"
                                        @checked(
                                            !empty(
                                                $config['enabled']
                                            )
                                        )
                                    >

                                    <span class="switch-slider"></span>

                                </label>

                            </div>

                            @if($provider['credentials'])

                                <div class="provider-credentials">

                                    <div class="field">

                                        <label>
                                            {{ $key === 'tiktok'
                                                ? 'Client Key'
                                                : 'Client ID' }}
                                        </label>

                                        <input
                                            type="text"
                                            name="providers[{{ $key }}][client_id]"
                                            value="{{ old(
                                                'providers.'
                                                . $key
                                                . '.client_id',
                                                $config['client_id']
                                                ?? ''
                                            ) }}"
                                            autocomplete="off"
                                        >

                                    </div>

                                    <div class="field">

                                        <label>Client Secret</label>

                                        <input
                                            type="password"
                                            name="providers[{{ $key }}][client_secret]"
                                            value=""
                                            autocomplete="new-password"
                                            placeholder="{{ !empty(
                                                $config['has_secret']
                                            )
                                                ? 'Saved — leave blank to keep'
                                                : 'Enter client secret' }}"
                                        >

                                        <div class="field-help">
                                            Secrets are never displayed
                                            after saving.
                                        </div>

                                    </div>

                                </div>

                            @endif

                        </div>

                    @endforeach

                </div>

            </div>

        </div>


        {{-- BRANDING --}}
        <div
            class="auth-panel"
            data-auth-panel="branding"
        >

                            {{-- ESUBIZ_SHARED_AUTH_LOGO_UPLOAD_UI_V1 --}}

                <div
                    style="
                        border:1px solid #e5e7eb;
                        border-radius:14px;
                        padding:18px;
                        margin-bottom:20px;
                        background:#fff;
                    "
                >

                    <div style="margin-bottom:16px;">

                        <strong
                            style="
                                display:block;
                                font-size:15px;
                                color:#111827;
                                margin-bottom:4px;
                            "
                        >
                            Authentication Logos
                        </strong>

                        <div
                            style="
                                font-size:12px;
                                color:#6b7280;
                                line-height:1.6;
                            "
                        >
                            One shared logo pair for Login,
                            Register, Forgot Password and
                            Reset Password.
                        </div>

                    </div>


                    <div class="field-grid">

                        <div class="field">

                            <label>Light Logo</label>

                            <input
                                type="file"
                                name="auth_logo_light"
                                accept="image/png,image/jpeg,image/webp,image/gif"
                            >

                            <div class="field-help">
                                Used when the authentication
                                interface is in Light mode.
                            </div>

                            @if(!empty(
                                $authConfig['brand']['logo_light']
                            ))

                                <div
                                    style="
                                        margin-top:10px;
                                        display:flex;
                                        align-items:center;
                                        gap:10px;
                                    "
                                >

                                    <div
                                        style="
                                            width:160px;
                                            min-height:70px;
                                            display:flex;
                                            align-items:center;
                                            justify-content:center;
                                            border:1px solid #e5e7eb;
                                            border-radius:9px;
                                            padding:8px;
                                            background:#ffffff;
                                        "
                                    >

                                        <img
                                            src="{{ asset(
                                                'storage/' .
                                                ltrim(
                                                    $authConfig[
                                                        'brand'
                                                    ][
                                                        'logo_light'
                                                    ],
                                                    '/'
                                                )
                                            ) }}"
                                            alt="Light Logo"
                                            style="
                                                max-width:140px;
                                                max-height:54px;
                                                object-fit:contain;
                                            "
                                        >

                                    </div>

                                    <label
                                        style="
                                            display:inline-flex;
                                            align-items:center;
                                            gap:6px;
                                            font-size:12px;
                                            color:#b91c1c;
                                        "
                                    >

                                        <input
                                            type="checkbox"
                                            name="remove_auth_logo_light"
                                            value="1"
                                        >

                                        Remove

                                    </label>

                                </div>

                            @endif

                        </div>


                        <div class="field">

                            <label>Dark Logo</label>

                            <input
                                type="file"
                                name="auth_logo_dark"
                                accept="image/png,image/jpeg,image/webp,image/gif"
                            >

                            <div class="field-help">
                                Used when the authentication
                                interface is in Dark mode.
                            </div>

                            @if(!empty(
                                $authConfig['brand']['logo_dark']
                            ))

                                <div
                                    style="
                                        margin-top:10px;
                                        display:flex;
                                        align-items:center;
                                        gap:10px;
                                    "
                                >

                                    <div
                                        style="
                                            width:160px;
                                            min-height:70px;
                                            display:flex;
                                            align-items:center;
                                            justify-content:center;
                                            border:1px solid #374151;
                                            border-radius:9px;
                                            padding:8px;
                                            background:#111827;
                                        "
                                    >

                                        <img
                                            src="{{ asset(
                                                'storage/' .
                                                ltrim(
                                                    $authConfig[
                                                        'brand'
                                                    ][
                                                        'logo_dark'
                                                    ],
                                                    '/'
                                                )
                                            ) }}"
                                            alt="Dark Logo"
                                            style="
                                                max-width:140px;
                                                max-height:54px;
                                                object-fit:contain;
                                            "
                                        >

                                    </div>

                                    <label
                                        style="
                                            display:inline-flex;
                                            align-items:center;
                                            gap:6px;
                                            font-size:12px;
                                            color:#b91c1c;
                                        "
                                    >

                                        <input
                                            type="checkbox"
                                            name="remove_auth_logo_dark"
                                            value="1"
                                        >

                                        Remove

                                    </label>

                                </div>

                            @endif

                        </div>

                    </div>


                    <div
                        style="
                            margin-top:12px;
                            padding:10px 12px;
                            border-radius:9px;
                            background:#f9fafb;
                            color:#6b7280;
                            font-size:12px;
                            line-height:1.6;
                        "
                    >
                        The future Light / Dark / Auto
                        appearance setting will select between
                        these two logos automatically.
                    </div>

                </div>

{{-- Login/Register colors are now shared. --}}

                    </div>

                </div>

            {{-- ESUBIZ_OLD_AUTH_COLOR_LOOP_ORPHAN_REMOVED_V1 --}}

        </div>


        {{-- ESUBIZ_AUTH_BOT_PROTECTION_UI_V1 --}}
        <div
            class="auth-panel"
            data-auth-panel="security"
        >

            <div class="auth-card">

                <h2 class="auth-card-title">
                    Authentication Security
                </h2>

                <p class="auth-card-subtitle">
                    Control the first layer of automated
                    bot and malicious authentication protection.
                </p>

                <div class="option-row">

                    <div class="option-copy">
                        <strong>
                            Protect Login
                        </strong>

                        <span>
                            Apply Core bot and abuse protection
                            to the Login form.
                        </span>
                    </div>

                    <label class="switch">

                        <input
                            type="hidden"
                            name="security[login_enabled]"
                            value="0"
                        >

                        <input
                            type="checkbox"
                            name="security[login_enabled]"
                            value="1"
                            @checked(
                                !empty(
                                    $authConfig[
                                        'security'
                                    ][
                                        'login_enabled'
                                    ]
                                )
                            )
                        >

                        <span class="switch-slider"></span>

                    </label>

                </div>


                <div class="option-row">

                    <div class="option-copy">
                        <strong>
                            Protect Registration
                        </strong>

                        <span>
                            Apply Core bot and abuse protection
                            to the Registration form.
                        </span>
                    </div>

                    <label class="switch">

                        <input
                            type="hidden"
                            name="security[register_enabled]"
                            value="0"
                        >

                        <input
                            type="checkbox"
                            name="security[register_enabled]"
                            value="1"
                            @checked(
                                !empty(
                                    $authConfig[
                                        'security'
                                    ][
                                        'register_enabled'
                                    ]
                                )
                            )
                        >

                        <span class="switch-slider"></span>

                    </label>

                </div>

            </div>

        </div>


        
        {{-- ESUBIZ_SHARED_AUTH_APPEARANCE_UI_V1 --}}
        <div
            class="auth-panel"
            data-auth-panel="appearance"
        >
            <div class="auth-card">

                <h2 class="auth-card-title">
                    Auth Appearance
                </h2>

                <p class="auth-card-subtitle">
                    One appearance for all authentication pages.
                </p>

                <div class="color-grid">

                    @foreach([
                        'background_color' => 'Background Color',
                        'card_color' => 'Card Color',
                        'text_color' => 'Text Color',
                        'button_color' => 'Button Color',
                    ] as $field => $label)

                        @php
                            $authColorValue = old(
                                'appearance.' . $field,
                                $authConfig['appearance'][$field]
                                ?? '#ffffff'
                            );
                        @endphp

                        <div class="color-field">

                            <label>
                                {{ $label }}
                            </label>

                            <div
                                class="auth-color-control"
                                data-auth-color-control
                            >
                                <input
                                    type="color"
                                    value="{{ $authColorValue }}"
                                    data-auth-color-picker
                                    aria-label="{{ $label }} picker"
                                >

                                <input
                                    type="text"
                                    name="appearance[{{ $field }}]"
                                    value="{{ $authColorValue }}"
                                    maxlength="7"
                                    pattern="^#[0-9A-Fa-f]{6}$"
                                    placeholder="#0b1f3a"
                                    spellcheck="false"
                                    autocomplete="off"
                                    data-auth-color-code
                                >
                            </div>

                        </div>

                    @endforeach

                </div>

            </div>
        </div>


{{-- REGISTRATION --}}
        <div
            class="auth-panel"
            data-auth-panel="registration"
        >

            <div class="auth-card">

                <h2 class="auth-card-title">
                    Registration Behaviour
                </h2>

                <p class="auth-card-subtitle">
                    Control whether visitors can create accounts
                    and what happens after registration.
                </p>

                <div class="option-row">

                    <div class="option-copy">
                        <strong>Public Registration</strong>
                        <span>
                            Allow visitors to create accounts.
                        </span>
                    </div>

                    <label class="switch">

                        <input
                            type="hidden"
                            name="registration_enabled"
                            value="0"
                        >

                        <input
                            type="checkbox"
                            name="registration_enabled"
                            value="1"
                            @checked(
                                !empty(
                                    $authConfig[
                                        'registration_enabled'
                                    ]
                                )
                            )
                        >

                        <span class="switch-slider"></span>

                    </label>

                </div>

                <div class="option-row">

                    <div class="option-copy">
                        <strong>
                            Login after registration
                        </strong>

                        <span>
                            Automatically start the user's
                            website session after signup.
                        </span>
                    </div>

                    <label class="switch">

                        <input
                            type="hidden"
                            name="auto_login"
                            value="0"
                        >

                        <input
                            type="checkbox"
                            name="auto_login"
                            value="1"
                            @checked(
                                !empty(
                                    $authConfig['auto_login']
                                )
                            )
                        >

                        <span class="switch-slider"></span>

                    </label>

                </div>

                <div
                    class="field"
                    style="margin-top:18px;"
                >
                    <label>
                        Registration Redirect
                    </label>

                    <input
                        type="text"
                        name="registration_redirect"
                        value="{{ old(
                            'registration_redirect',
                            $authConfig[
                                'registration_redirect'
                            ] ?? '/admin/dashboard'
                        ) }}"
                    >

                    <div class="field-help">
                        Example: /account or /admin/dashboard
                    </div>

                </div>

            </div>


            <div class="auth-card">

                <h2 class="auth-card-title">
                    Registration Form Fields
                </h2>

                <p class="auth-card-subtitle">
                    Add, remove, reorder and configure fields
                    shown during registration.
                </p>

                {{-- ESUBIZ_DYNAMIC_REGISTRATION_FIELD_BUILDER_V1 --}}

                @php
                    $registrationFields =
                        old(
                            'registration_fields',
                            $authConfig[
                                'registration_fields'
                            ] ?? []
                        );

                    if (empty($registrationFields)) {
                        $registrationFields = [
                            [
                                'key' => 'name',
                                'label' => 'Name',
                                'type' => 'text',
                                'required' => true,
                                'system' => true,
                                'enabled' => true,
                            ],
                            [
                                'key' => 'email',
                                'label' => 'Email',
                                'type' => 'email',
                                'required' => true,
                                'system' => true,
                                'enabled' => true,
                            ],
                            [
                                'key' => 'password',
                                'label' => 'Password',
                                'type' => 'password',
                                'required' => true,
                                'system' => true,
                                'enabled' => true,
                            ],
                            [
                                'key' => 'password_confirmation',
                                'label' => 'Confirm Password',
                                'type' => 'password',
                                'required' => true,
                                'system' => true,
                                'enabled' => true,
                            ],
                        ];
                    }
                @endphp

                <div
                    id="registration-fields-builder"
                    style="display:flex;flex-direction:column;gap:12px;"
                >

                    @foreach(
                        $registrationFields
                        as $index => $field
                    )

                        @php
                            $system =
                                !empty($field['system']);

                            $options =
                                $field['options'] ?? '';

                            if (is_array($options)) {
                                $options =
                                    implode(', ', $options);
                            }
                        @endphp

                        <div
                            class="registration-field-row"
                            data-registration-field
                            style="
                                border:1px solid #e5e7eb;
                                border-radius:12px;
                                padding:15px;
                                background:#fff;
                            "
                        >

                            <div
                                style="
                                    display:flex;
                                    align-items:center;
                                    justify-content:space-between;
                                    gap:12px;
                                    margin-bottom:14px;
                                "
                            >
                                <strong
                                    style="
                                        color:#111827;
                                        font-size:13px;
                                    "
                                >
                                    {{ $system
                                        ? 'Core field'
                                        : 'Custom field' }}
                                </strong>

                                <div
                                    style="
                                        display:flex;
                                        gap:6px;
                                    "
                                >
                                    <button
                                        type="button"
                                        data-field-up
                                        style="
                                            border:1px solid #d1d5db;
                                            background:#fff;
                                            border-radius:7px;
                                            padding:6px 9px;
                                            cursor:pointer;
                                        "
                                    >↑</button>

                                    <button
                                        type="button"
                                        data-field-down
                                        style="
                                            border:1px solid #d1d5db;
                                            background:#fff;
                                            border-radius:7px;
                                            padding:6px 9px;
                                            cursor:pointer;
                                        "
                                    >↓</button>

                                    @if(!$system)
                                        <button
                                            type="button"
                                            data-field-remove
                                            style="
                                                border:1px solid #fecaca;
                                                color:#b91c1c;
                                                background:#fff;
                                                border-radius:7px;
                                                padding:6px 10px;
                                                cursor:pointer;
                                            "
                                        >
                                            Remove
                                        </button>
                                    @endif
                                </div>
                            </div>

                            <div class="field-grid">

                                {{-- ESUBIZ_INTERNAL_FIELD_KEY_UI_V3 --}}
                                @if($system)
                                    <input
                                        type="hidden"
                                        data-field-input="key"
                                        name="registration_fields[{{ $index }}][key]"
                                        value="{{ $field['key'] ?? '' }}"
                                    >
                                @endif

                                <div class="field">
                                    <label>Label</label>

                                    <input
                                        type="text"
                                        data-field-input="label"
                                        name="registration_fields[{{ $index }}][label]"
                                        value="{{ $field['label'] ?? '' }}"
                                        @readonly($system)
                                    >
                                </div>

                                <div class="field">
                                    <label>Field Type</label>

                                    @if($system)

                                        <input
                                            type="text"
                                            value="{{ ucfirst(
                                                $field['type']
                                                ?? 'text'
                                            ) }}"
                                            readonly
                                        >

                                        <input
                                            type="hidden"
                                            data-field-input="type"
                                            name="registration_fields[{{ $index }}][type]"
                                            value="{{ $field['type'] ?? 'text' }}"
                                        >

                                    @else

                                        <select
                                            data-field-input="type"
                                            data-field-type
                                            name="registration_fields[{{ $index }}][type]"
                                        >
                                            @foreach([
                                                'text' => 'Text',
                                                'email' => 'Email',
                                                'tel' => 'Phone',
                                                'number' => 'Number',
                                                'date' => 'Date',
                                                'textarea' => 'Textarea',
                                                'select' => 'Select',
                                                'checkbox' => 'Checkbox',
                                            ] as $typeValue => $typeLabel)

                                                <option
                                                    value="{{ $typeValue }}"
                                                    @selected(
                                                        ($field['type']
                                                            ?? 'text')
                                                        === $typeValue
                                                    )
                                                >
                                                    {{ $typeLabel }}
                                                </option>

                                            @endforeach
                                        </select>

                                    @endif
                                </div>

                                <div class="field">
                                    <label>Placeholder</label>

                                    <input
                                        type="text"
                                        data-field-input="placeholder"
                                        name="registration_fields[{{ $index }}][placeholder]"
                                        value="{{ $field['placeholder'] ?? '' }}"
                                        @readonly($system)
                                    >
                                </div>

                            </div>

                            @if(!$system)
                                <div
                                    class="field"
                                    data-field-options-wrap
                                    style="{{ ($field['type'] ?? '') === 'select'
                                        ? ''
                                        : 'display:none;' }}"
                                >
                                    <label>
                                        Select Options
                                    </label>

                                    <input
                                        type="text"
                                        data-field-input="options"
                                        name="registration_fields[{{ $index }}][options]"
                                        value="{{ $options }}"
                                        placeholder="Option 1, Option 2, Option 3"
                                    >

                                    <div class="field-help">
                                        Separate options with commas.
                                    </div>
                                </div>
                            @endif

                            <input
                                type="hidden"
                                data-field-input="system"
                                name="registration_fields[{{ $index }}][system]"
                                value="{{ $system ? '1' : '0' }}"
                            >

                            <input
                                type="hidden"
                                data-field-input="enabled"
                                name="registration_fields[{{ $index }}][enabled]"
                                value="1"
                            >

                            <label
                                style="
                                    display:inline-flex;
                                    align-items:center;
                                    gap:8px;
                                    font-size:13px;
                                    color:#374151;
                                "
                            >
                                <input
                                    type="hidden"
                                    data-field-required-hidden
                                    name="registration_fields[{{ $index }}][required]"
                                    value="0"
                                >

                                <input
                                    type="checkbox"
                                    data-field-input="required"
                                    name="registration_fields[{{ $index }}][required]"
                                    value="1"
                                    @checked(
                                        !empty(
                                            $field['required']
                                        )
                                    )
                                    @disabled($system)
                                >

                                Required

                                @if($system)
                                    <input
                                        type="hidden"
                                        data-field-required-system
                                        name="registration_fields[{{ $index }}][required]"
                                        value="1"
                                    >
                                @endif
                            </label>

                        </div>

                    @endforeach

                </div>

                <button
                    type="button"
                    id="add-registration-field"
                    style="
                        margin-top:14px;
                        border:1px solid #d1d5db;
                        background:#fff;
                        color:#111827;
                        border-radius:9px;
                        padding:10px 14px;
                        font-weight:600;
                        cursor:pointer;
                    "
                >
                    + Add Field
                </button>

                <template id="registration-field-template">

                    <div
                        class="registration-field-row"
                        data-registration-field
                        style="
                            border:1px solid #e5e7eb;
                            border-radius:12px;
                            padding:15px;
                            background:#fff;
                        "
                    >

                        <div
                            style="
                                display:flex;
                                align-items:center;
                                justify-content:space-between;
                                gap:12px;
                                margin-bottom:14px;
                            "
                        >
                            <strong
                                style="
                                    color:#111827;
                                    font-size:13px;
                                "
                            >
                                Custom field
                            </strong>

                            <div style="display:flex;gap:6px;">

                                <button
                                    type="button"
                                    data-field-up
                                    style="
                                        border:1px solid #d1d5db;
                                        background:#fff;
                                        border-radius:7px;
                                        padding:6px 9px;
                                        cursor:pointer;
                                    "
                                >↑</button>

                                <button
                                    type="button"
                                    data-field-down
                                    style="
                                        border:1px solid #d1d5db;
                                        background:#fff;
                                        border-radius:7px;
                                        padding:6px 9px;
                                        cursor:pointer;
                                    "
                                >↓</button>

                                <button
                                    type="button"
                                    data-field-remove
                                    style="
                                        border:1px solid #fecaca;
                                        color:#b91c1c;
                                        background:#fff;
                                        border-radius:7px;
                                        padding:6px 10px;
                                        cursor:pointer;
                                    "
                                >
                                    Remove
                                </button>

                            </div>
                        </div>

                        <div class="field-grid">


                            <div class="field">
                                <label>Label</label>
                                <input
                                    type="text"
                                    data-field-input="label"
                                    placeholder="Company Name"
                                >
                            </div>

                            <div class="field">
                                <label>Field Type</label>

                                <select
                                    data-field-input="type"
                                    data-field-type
                                >
                                    <option value="text">Text</option>
                                    <option value="email">Email</option>
                                    <option value="tel">Phone</option>
                                    <option value="number">Number</option>
                                    <option value="date">Date</option>
                                    <option value="textarea">Textarea</option>
                                    <option value="select">Select</option>
                                    <option value="checkbox">Checkbox</option>
                                </select>
                            </div>

                            <div class="field">
                                <label>Placeholder</label>
                                <input
                                    type="text"
                                    data-field-input="placeholder"
                                >
                            </div>

                        </div>

                        <div
                            class="field"
                            data-field-options-wrap
                            style="display:none;"
                        >
                            <label>Select Options</label>

                            <input
                                type="text"
                                data-field-input="options"
                                placeholder="Option 1, Option 2, Option 3"
                            >
                        </div>

                        <input
                            type="hidden"
                            data-field-input="system"
                            value="0"
                        >

                        <input
                            type="hidden"
                            data-field-input="enabled"
                            value="1"
                        >

                        <label
                            style="
                                display:inline-flex;
                                align-items:center;
                                gap:8px;
                                font-size:13px;
                                color:#374151;
                            "
                        >
                            <input
                                type="hidden"
                                data-field-required-hidden
                                value="0"
                            >

                            <input
                                type="checkbox"
                                data-field-input="required"
                                value="1"
                            >

                            Required
                        </label>

                    </div>

                </template>

            </div>


            <div class="auth-card">

                <h2 class="auth-card-title">
                    Products & Plans
                </h2>

                <p class="auth-card-subtitle">
                    Optionally offer website products or plans
                    during account registration.
                </p>

                <div class="coming-soon">
                    <strong>
                        Registration Products
                    </strong>

                    Product and plan selection will use the
                    website's available product sources.
                </div>

            </div>

        </div>


        {{-- IDENTITY PROVIDER --}}
        <div
            class="auth-panel"
            data-auth-panel="identity"
        >

            <div class="auth-card">

                <h2 class="auth-card-title">
                    Website Identity Provider
                </h2>

                <p class="auth-card-subtitle">
                    Allow trusted websites and applications
                    to authenticate users through this Core
                    website using registered OAuth/OIDC clients.
                </p>

                <div class="coming-soon">

                    <strong>
                        Identity Provider
                    </strong>

                    Client applications, redirect URIs,
                    scopes, secrets, consent and token
                    management will be configured here.

                </div>

            </div>

        </div>


        <div class="auth-save-bar">
            <button
                type="submit"
                class="auth-save"
            >
                Save Authentication Settings
            </button>
        </div>

    </form>

</div>


<script>
/* ESUBIZ_TENANT_AUTH_SETTINGS_UI_JS_V1 */
document.addEventListener('DOMContentLoaded', function () {

    const tabs =
        document.querySelectorAll('[data-auth-tab]');

    const panels =
        document.querySelectorAll('[data-auth-panel]');

    tabs.forEach(function (tab) {

        tab.addEventListener('click', function () {

            const target =
                tab.getAttribute('data-auth-tab');

            tabs.forEach(function (item) {
                item.classList.remove('active');
            });

            panels.forEach(function (panel) {
                panel.classList.remove('active');
            });

            tab.classList.add('active');

            const panel =
                document.querySelector(
                    '[data-auth-panel="' +
                    target +
                    '"]'
                );

            if (panel) {
                panel.classList.add('active');
            }
        });
    });


    /*
     * ESUBIZ_DYNAMIC_REGISTRATION_FIELD_BUILDER_JS_V1
     */
    const fieldBuilder =
        document.getElementById(
            'registration-fields-builder'
        );

    const addFieldButton =
        document.getElementById(
            'add-registration-field'
        );

    const fieldTemplate =
        document.getElementById(
            'registration-field-template'
        );

    function renumberRegistrationFields() {

        if (!fieldBuilder) {
            return;
        }

        const rows =
            fieldBuilder.querySelectorAll(
                '[data-registration-field]'
            );

        rows.forEach(function (row, index) {

            row.querySelectorAll(
                '[data-field-input]'
            ).forEach(function (input) {

                const key =
                    input.getAttribute(
                        'data-field-input'
                    );

                input.name =
                    'registration_fields['
                    + index
                    + ']['
                    + key
                    + ']';
            });

            row.querySelectorAll(
                '[data-field-required-hidden]'
            ).forEach(function (input) {
                input.name =
                    'registration_fields['
                    + index
                    + '][required]';
            });

            row.querySelectorAll(
                '[data-field-required-system]'
            ).forEach(function (input) {
                input.name =
                    'registration_fields['
                    + index
                    + '][required]';
            });
        });
    }

    function updateFieldOptions(row) {

        const type =
            row.querySelector(
                '[data-field-type]'
            );

        const options =
            row.querySelector(
                '[data-field-options-wrap]'
            );

        if (!type || !options) {
            return;
        }

        options.style.display =
            type.value === 'select'
                ? ''
                : 'none';
    }

    if (fieldBuilder) {

        fieldBuilder.addEventListener(
            'click',
            function (event) {

                const row =
                    event.target.closest(
                        '[data-registration-field]'
                    );

                if (!row) {
                    return;
                }

                if (
                    event.target.closest(
                        '[data-field-remove]'
                    )
                ) {
                    row.remove();
                    renumberRegistrationFields();
                    return;
                }

                if (
                    event.target.closest(
                        '[data-field-up]'
                    )
                ) {
                    const previous =
                        row.previousElementSibling;

                    if (previous) {
                        fieldBuilder.insertBefore(
                            row,
                            previous
                        );

                        renumberRegistrationFields();
                    }

                    return;
                }

                if (
                    event.target.closest(
                        '[data-field-down]'
                    )
                ) {
                    const next =
                        row.nextElementSibling;

                    if (next) {
                        fieldBuilder.insertBefore(
                            next,
                            row
                        );

                        renumberRegistrationFields();
                    }
                }
            }
        );

        fieldBuilder.addEventListener(
            'change',
            function (event) {

                if (
                    event.target.matches(
                        '[data-field-type]'
                    )
                ) {
                    const row =
                        event.target.closest(
                            '[data-registration-field]'
                        );

                    if (row) {
                        updateFieldOptions(row);
                    }
                }
            }
        );

        fieldBuilder.querySelectorAll(
            '[data-registration-field]'
        ).forEach(updateFieldOptions);
    }

    if (
        addFieldButton
        && fieldTemplate
        && fieldBuilder
    ) {
        addFieldButton.addEventListener(
            'click',
            function () {

                const fragment =
                    fieldTemplate.content.cloneNode(
                        true
                    );

                fieldBuilder.appendChild(fragment);

                const rows =
                    fieldBuilder.querySelectorAll(
                        '[data-registration-field]'
                    );

                const row =
                    rows[rows.length - 1];

                if (row) {
                    updateFieldOptions(row);
                }

                renumberRegistrationFields();
            }
        );
    }

    renumberRegistrationFields();

});
</script>


{{-- ESUBIZ_AUTH_COLOR_PICKER_HEX_JS_V1 --}}
<script>
document.addEventListener(
    'DOMContentLoaded',
    function () {
        document
            .querySelectorAll(
                '[data-auth-color-control]'
            )
            .forEach(function (control) {
                const picker =
                    control.querySelector(
                        '[data-auth-color-picker]'
                    );

                const code =
                    control.querySelector(
                        '[data-auth-color-code]'
                    );

                if (!picker || !code) {
                    return;
                }

                picker.addEventListener(
                    'input',
                    function () {
                        code.value =
                            picker.value.toLowerCase();
                    }
                );

                code.addEventListener(
                    'input',
                    function () {
                        let value =
                            code.value.trim();

                        if (
                            /^[0-9a-fA-F]{6}$/.test(
                                value
                            )
                        ) {
                            value = '#' + value;
                            code.value = value;
                        }

                        if (
                            /^#[0-9a-fA-F]{6}$/.test(
                                value
                            )
                        ) {
                            picker.value =
                                value.toLowerCase();
                        }
                    }
                );

                code.addEventListener(
                    'blur',
                    function () {
                        const value =
                            code.value.trim();

                        if (
                            /^#[0-9a-fA-F]{6}$/.test(
                                value
                            )
                        ) {
                            code.value =
                                value.toLowerCase();

                            picker.value =
                                value.toLowerCase();
                        } else {
                            code.value =
                                picker.value.toLowerCase();
                        }
                    }
                );
            });
    }
);
</script>

@endsection
