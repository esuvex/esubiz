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



/* ESUBIZ_AUTH_SETTINGS_LAYOUT_REPAIR_V1 */

/*
 * Authentication Settings uses one predictable
 * responsive layout across every configuration panel.
 */
.auth-panel-fixed-layout {
    width: 100%;
    min-width: 0;
    box-sizing: border-box;
}

.auth-panel-fixed-layout > *,
.auth-panel-fixed-layout .auth-card {
    box-sizing: border-box;
    max-width: 100%;
}

.auth-panel-fixed-layout > .auth-card {
    width: 100%;
    margin-left: 0;
    margin-right: 0;
    margin-bottom: 20px;
}

.auth-panel-fixed-layout .auth-card:last-child {
    margin-bottom: 0;
}

.auth-panel-fixed-layout .form-grid,
.auth-panel-fixed-layout .color-grid,
.auth-panel-fixed-layout .settings-grid,
.auth-panel-fixed-layout .field-grid,
.auth-panel-fixed-layout .provider-grid {
    display: grid;
    grid-template-columns:
        repeat(
            auto-fit,
            minmax(
                min(100%, 240px),
                1fr
            )
        );
    gap: 16px;
    width: 100%;
    min-width: 0;
}

.auth-panel-fixed-layout input,
.auth-panel-fixed-layout select,
.auth-panel-fixed-layout textarea {
    max-width: 100%;
    box-sizing: border-box;
}

.auth-panel-fixed-layout textarea {
    width: 100%;
}

.auth-panel-fixed-layout .auth-color-control {
    width: 100%;
    min-width: 0;
}

.auth-panel-fixed-layout .auth-color-control input[type="text"] {
    min-width: 0;
    width: 100%;
}

@media (max-width: 720px) {
    .auth-panel-fixed-layout .form-grid,
    .auth-panel-fixed-layout .color-grid,
    .auth-panel-fixed-layout .settings-grid,
    .auth-panel-fixed-layout .field-grid,
    .auth-panel-fixed-layout .provider-grid {
        grid-template-columns: 1fr;
    }
}



/* ESUBIZ_AUTH_SETTINGS_SIDEBAR_CONTAINMENT_V3 */

/*
 * Keep every Authentication Settings panel inside the
 * existing Site Admin main-content column.
 */
[data-auth-panel] {
    position: relative !important;

    width: 100% !important;
    max-width: 100% !important;
    min-width: 0 !important;

    margin-left: 0 !important;
    margin-right: 0 !important;

    left: auto !important;
    right: auto !important;

    transform: none !important;

    box-sizing: border-box !important;
}

[data-auth-panel] *,
[data-auth-panel] *::before,
[data-auth-panel] *::after {
    box-sizing: border-box !important;
}

[data-auth-panel] > * {
    max-width: 100% !important;
    min-width: 0 !important;
}

[data-auth-panel] .auth-card {
    position: relative !important;

    width: 100% !important;
    max-width: 100% !important;
    min-width: 0 !important;

    margin-left: 0 !important;
    margin-right: 0 !important;

    left: auto !important;
    right: auto !important;

    transform: none !important;
}

[data-auth-panel] .form-grid,
[data-auth-panel] .settings-grid,
[data-auth-panel] .field-grid,
[data-auth-panel] .provider-grid,
[data-auth-panel] .color-grid {
    display: grid !important;

    grid-template-columns:
        repeat(
            2,
            minmax(0, 1fr)
        ) !important;

    gap: 16px !important;

    width: 100% !important;
    max-width: 100% !important;
    min-width: 0 !important;

    margin-left: 0 !important;
    margin-right: 0 !important;
}

[data-auth-panel] .form-group,
[data-auth-panel] .field,
[data-auth-panel] .setting-item,
[data-auth-panel] .provider-item,
[data-auth-panel] .registration-field-row,
[data-auth-panel] [data-registration-field-row] {
    width: 100% !important;
    max-width: 100% !important;
    min-width: 0 !important;
}

[data-auth-panel] input:not([type="checkbox"]):not([type="radio"]):not([type="color"]),
[data-auth-panel] select,
[data-auth-panel] textarea {
    display: block;

    width: 100% !important;
    max-width: 100% !important;
    min-width: 0 !important;
}

[data-auth-panel] textarea {
    resize: vertical;
}

[data-auth-panel] pre,
[data-auth-panel] code,
[data-auth-panel] .help-text,
[data-auth-panel] small {
    max-width: 100% !important;

    overflow-wrap: anywhere;
    word-break: break-word;
}

/*
 * Security, Registration and Identity Provider get their
 * own clean vertical content flow.
 */
[data-auth-panel="security"],
[data-auth-panel="registration"],
[data-auth-panel="identity-provider"],
[data-auth-panel="identity_provider"] {
    display: block;
    overflow: hidden;
}

[data-auth-panel="security"] > *,
[data-auth-panel="registration"] > *,
[data-auth-panel="identity-provider"] > *,
[data-auth-panel="identity_provider"] > * {
    float: none !important;
    clear: both;

    width: 100% !important;
    max-width: 100% !important;
}

@media (max-width: 900px) {
    [data-auth-panel] .form-grid,
    [data-auth-panel] .settings-grid,
    [data-auth-panel] .field-grid,
    [data-auth-panel] .provider-grid,
    [data-auth-panel] .color-grid {
        grid-template-columns: 1fr !important;
    }
}



/* ESUBIZ_AUTH_BROKEN_TAB_LAYOUT_FINAL_V5 */

/*
 * Authentication Settings belongs to the existing Site Admin
 * content column. Do not reposition any panel relative to the
 * viewport/sidebar.
 */

.auth-settings,
.auth-settings-wrap,
.auth-settings-wrapper,
.authentication-settings,
.authentication-settings-wrap,
.authentication-settings-wrapper {
    position: relative !important;

    display: block !important;

    width: 100% !important;
    max-width: 100% !important;
    min-width: 0 !important;

    margin-left: 0 !important;
    margin-right: 0 !important;

    left: auto !important;
    right: auto !important;

    transform: none !important;

    box-sizing: border-box !important;
}


/*
 * Normalize every actual tab content element regardless of the
 * specific Identity Provider data-auth-panel name.
 */
[role="tabpanel"],
.tab-pane,
.auth-tab-panel,
.settings-tab-panel,
[data-tab-panel],
[data-panel] {
    position: relative !important;

    width: 100% !important;
    max-width: 100% !important;
    min-width: 0 !important;

    margin-left: 0 !important;
    margin-right: 0 !important;

    left: auto !important;
    right: auto !important;

    transform: none !important;

    box-sizing: border-box !important;
}


/*
 * Existing data-auth-panel sections get the same treatment.
 */
[data-auth-panel] {
    position: relative !important;

    width: 100% !important;
    max-width: 100% !important;
    min-width: 0 !important;

    margin-left: 0 !important;
    margin-right: 0 !important;

    left: auto !important;
    right: auto !important;

    transform: none !important;

    box-sizing: border-box !important;
}


/*
 * Critical repair:
 * old layout rules were allowing cards/rows to extend back
 * beneath the fixed Site Admin sidebar.
 */
[role="tabpanel"] > *,
.tab-pane > *,
.auth-tab-panel > *,
.settings-tab-panel > *,
[data-tab-panel] > *,
[data-panel] > *,
[data-auth-panel] > * {
    position: relative;

    max-width: 100% !important;
    min-width: 0 !important;

    margin-left: 0;
    margin-right: 0;

    box-sizing: border-box;
}


/*
 * Cards remain inside their parent panel.
 */
[role="tabpanel"] .auth-card,
.tab-pane .auth-card,
.auth-tab-panel .auth-card,
.settings-tab-panel .auth-card,
[data-tab-panel] .auth-card,
[data-panel] .auth-card,
[data-auth-panel] .auth-card {
    position: relative !important;

    display: block !important;

    width: 100% !important;
    max-width: 100% !important;
    min-width: 0 !important;

    margin:
        0
        0
        18px
        0 !important;

    left: auto !important;
    right: auto !important;

    transform: none !important;

    box-sizing: border-box !important;
}


/*
 * Standard two-column settings layout.
 */
[role="tabpanel"] .form-grid,
[role="tabpanel"] .settings-grid,
[role="tabpanel"] .field-grid,
[role="tabpanel"] .provider-grid,
[role="tabpanel"] .color-grid,

.tab-pane .form-grid,
.tab-pane .settings-grid,
.tab-pane .field-grid,
.tab-pane .provider-grid,
.tab-pane .color-grid,

[data-auth-panel] .form-grid,
[data-auth-panel] .settings-grid,
[data-auth-panel] .field-grid,
[data-auth-panel] .provider-grid,
[data-auth-panel] .color-grid {
    display: grid !important;

    grid-template-columns:
        repeat(
            2,
            minmax(0, 1fr)
        ) !important;

    gap: 16px !important;

    width: 100% !important;
    max-width: 100% !important;
    min-width: 0 !important;

    margin-left: 0 !important;
    margin-right: 0 !important;

    box-sizing: border-box !important;
}


/*
 * Individual setting rows must never inherit viewport width.
 */
[role="tabpanel"] .form-group,
[role="tabpanel"] .field,
[role="tabpanel"] .setting-item,

.tab-pane .form-group,
.tab-pane .field,
.tab-pane .setting-item,

[data-auth-panel] .form-group,
[data-auth-panel] .field,
[data-auth-panel] .setting-item,

.registration-field-row,
[data-registration-field-row] {
    width: 100% !important;
    max-width: 100% !important;
    min-width: 0 !important;

    box-sizing: border-box !important;
}


/*
 * Inputs stay inside the repaired content column.
 */
[role="tabpanel"] input:not([type="checkbox"]):not([type="radio"]):not([type="color"]),
[role="tabpanel"] select,
[role="tabpanel"] textarea,

.tab-pane input:not([type="checkbox"]):not([type="radio"]):not([type="color"]),
.tab-pane select,
.tab-pane textarea,

[data-auth-panel] input:not([type="checkbox"]):not([type="radio"]):not([type="color"]),
[data-auth-panel] select,
[data-auth-panel] textarea {
    display: block;

    width: 100% !important;
    max-width: 100% !important;
    min-width: 0 !important;

    box-sizing: border-box !important;
}


/*
 * Long OAuth/API values must wrap instead of increasing
 * panel width.
 */
[role="tabpanel"] pre,
[role="tabpanel"] code,
.tab-pane pre,
.tab-pane code,
[data-auth-panel] pre,
[data-auth-panel] code {
    max-width: 100% !important;

    overflow-wrap: anywhere;
    word-break: break-word;
    white-space: pre-wrap;
}


/*
 * Remove accidental horizontal overflow from the settings
 * content itself without affecting the Site Admin sidebar.
 */
main,
.main-content,
.content,
.content-wrapper {
    min-width: 0;
}


/* Responsive */
@media (max-width: 900px) {

    [role="tabpanel"] .form-grid,
    [role="tabpanel"] .settings-grid,
    [role="tabpanel"] .field-grid,
    [role="tabpanel"] .provider-grid,
    [role="tabpanel"] .color-grid,

    .tab-pane .form-grid,
    .tab-pane .settings-grid,
    .tab-pane .field-grid,
    .tab-pane .provider-grid,
    .tab-pane .color-grid,

    [data-auth-panel] .form-grid,
    [data-auth-panel] .settings-grid,
    [data-auth-panel] .field-grid,
    [data-auth-panel] .provider-grid,
    [data-auth-panel] .color-grid {
        grid-template-columns:
            1fr !important;
    }
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

{{-- ESUBIZ_AUTH_REFERENCE_UI_V1 --}}


{{-- ESUBIZ_AUTH_REFERENCE_UI_CORRECTED_V3 --}}

<div class="esubiz-auth-corrected">

    <div class="esubiz-auth-tabs">

        <button
            type="button"
            class="esubiz-auth-tab"
            data-auth-ui-tab="providers"
        >
            Login Providers
        </button>


        <button
            type="button"
            class="esubiz-auth-tab active"
            data-auth-ui-tab="branding"
        >
            Branding & Appearance
        </button>


        <button
            type="button"
            class="esubiz-auth-tab"
            data-auth-ui-tab="security"
        >
            Security
        </button>


        <button
            type="button"
            class="esubiz-auth-tab"
            data-auth-ui-tab="registration"
        >
            Registration
        </button>


        <button
            type="button"
            class="esubiz-auth-tab"
            data-auth-ui-tab="identity"
        >
            Identity Provider
        </button>

    </div>


    <div class="esubiz-auth-ui-panel" data-auth-ui-panel="providers">
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
</div>

    <div class="esubiz-auth-ui-panel active" data-auth-ui-panel="branding">

{{-- ESUBIZ_AUTH_BRANDING_RESTORED_V5 --}}

<div class="auth-card">

    <h2 class="auth-card-title">
        Authentication Branding
    </h2>

    <p class="auth-card-subtitle">
        Shared logo and branding used across Login,
        Registration, Forgot Password and Reset Password.
    </p>


    <div class="esubiz-branding-upload-grid">

        <div class="field">

            <label>
                Light Logo
            </label>

            @if(
                !empty(
                    $authConfig[
                        'brand'
                    ][
                        'logo_light'
                    ]
                    ?? null
                )
            )
                <div class="esubiz-auth-current-file">
                    Light authentication logo configured
                </div>
            @endif

            <input
                type="file"
                name="brand_logo_light"
                accept="image/png,image/jpeg,image/webp,image/svg+xml"
            >

            <div class="field-help">
                Overrides the website logo in Light mode.
                If empty, the website logo is used.
            </div>

        </div>


        <div class="field">

            <label>
                Dark Logo
            </label>

            @if(
                !empty(
                    $authConfig[
                        'brand'
                    ][
                        'logo_dark'
                    ]
                    ?? null
                )
            )
                <div class="esubiz-auth-current-file">
                    Dark authentication logo configured
                </div>
            @endif

            <input
                type="file"
                name="brand_logo_dark"
                accept="image/png,image/jpeg,image/webp,image/svg+xml"
            >

            <div class="field-help">
                Optional logo specifically for Dark mode.
            </div>

        </div>

    </div>

</div>


<div class="auth-card">

    <h2 class="auth-card-title">
        Login Page
    </h2>

    <p class="auth-card-subtitle">
        Customize the heading and supporting text
        shown on the default Login page.
    </p>


    <div class="esubiz-branding-field-grid">

        <div class="field">

            <label>
                Login Heading
            </label>

            <input
                type="text"
                name="login[heading]"
                value="{{ old(
                    'login.heading',
                    $authConfig[
                        'login'
                    ][
                        'heading'
                    ]
                    ?? 'Welcome Back'
                ) }}"
            >

        </div>


        <div class="field">

            <label>
                Login Subheading
            </label>

            <input
                type="text"
                name="login[subheading]"
                value="{{ old(
                    'login.subheading',
                    $authConfig[
                        'login'
                    ][
                        'subheading'
                    ]
                    ?? ''
                ) }}"
            >

        </div>

    </div>

</div>


<div class="auth-card">

    <h2 class="auth-card-title">
        Registration Page
    </h2>

    <p class="auth-card-subtitle">
        Customize the heading and supporting text
        shown on the default Registration page.
    </p>


    <div class="esubiz-branding-field-grid">

        <div class="field">

            <label>
                Registration Heading
            </label>

            <input
                type="text"
                name="register[heading]"
                value="{{ old(
                    'register.heading',
                    $authConfig[
                        'register'
                    ][
                        'heading'
                    ]
                    ?? 'Create Your Account'
                ) }}"
            >

        </div>


        <div class="field">

            <label>
                Registration Subheading
            </label>

            <input
                type="text"
                name="register[subheading]"
                value="{{ old(
                    'register.subheading',
                    $authConfig[
                        'register'
                    ][
                        'subheading'
                    ]
                    ?? ''
                ) }}"
            >

        </div>

    </div>

</div>


<div class="auth-card">

    <h2 class="auth-card-title">
        Auth Appearance
    </h2>

    <p class="auth-card-subtitle">
        Shared appearance used across all authentication pages.
    </p>


    <div class="esubiz-auth-color-grid">

        @php
            $authAppearanceFields = [

                'background_color' => [
                    'label' => 'Background Color',
                    'default' => '#f5f7fb',
                ],

                'card_color' => [
                    'label' => 'Card Color',
                    'default' => '#ffffff',
                ],

                'text_color' => [
                    'label' => 'Text Color',
                    'default' => '#111827',
                ],

                'button_color' => [
                    'label' => 'Button Color',
                    'default' => '#111827',
                ],

            ];
        @endphp


        @foreach(
            $authAppearanceFields
            as $appearanceKey => $appearanceDefinition
        )

            @php
                $appearanceValue =
                    $authConfig[
                        'appearance'
                    ][
                        $appearanceKey
                    ]
                    ?? $appearanceDefinition[
                        'default'
                    ];
            @endphp


            <div class="field">

                <label>
                    {{ $appearanceDefinition['label'] }}
                </label>


                <div class="esubiz-auth-color-control">

                    <input
                        type="color"
                        value="{{ $appearanceValue }}"
                        data-auth-color-picker
                    >

                    <input
                        type="text"
                        name="appearance[{{ $appearanceKey }}]"
                        value="{{ $appearanceValue }}"
                        maxlength="7"
                        pattern="^#[0-9A-Fa-f]{6}$"
                        spellcheck="false"
                        autocomplete="off"
                        data-auth-color-hex
                    >

                </div>

            </div>

        @endforeach

    </div>

</div>

</div>

    
<div
    class="esubiz-auth-ui-panel"
    data-auth-ui-panel="security"
>

    <div class="esubiz-reference-card">

        <div class="esubiz-reference-heading">

            <h3>
                Login Security
            </h3>

            <p>
                Manage login protection and security behaviour.
            </p>

        </div>


        <div class="esubiz-reference-row">

            <div>

                <strong>
                    Enable login protection
                </strong>

                <small>
                    Protect login form from bots and malicious attempts.
                </small>

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


        <div class="esubiz-reference-row">

            <div>

                <strong>
                    Enable rate limiting
                </strong>

                <small>
                    Limit repeated failed login attempts.
                </small>

            </div>


            <label class="switch">

                <input
                    type="hidden"
                    name="security[rate_limit_enabled]"
                    value="0"
                >

                <input
                    type="checkbox"
                    name="security[rate_limit_enabled]"
                    value="1"
                    @checked(
                        $authConfig[
                            'security'
                        ][
                            'rate_limit_enabled'
                        ]
                        ?? true
                    )
                >

                <span class="switch-slider"></span>

            </label>

        </div>


        <div class="esubiz-reference-grid">

            <div class="field">

                <label>
                    Maximum login attempts
                </label>

                <input
                    type="number"
                    min="1"
                    max="50"
                    name="security[max_login_attempts]"
                    value="{{ old(
                        'security.max_login_attempts',
                        $authConfig[
                            'security'
                        ][
                            'max_login_attempts'
                        ]
                        ?? 5
                    ) }}"
                >

                <div class="field-help">
                    Number of failed attempts before temporary lockout.
                </div>

            </div>


            <div class="field">

                <label>
                    Lockout duration (minutes)
                </label>

                <input
                    type="number"
                    min="1"
                    max="1440"
                    name="security[lockout_minutes]"
                    value="{{ old(
                        'security.lockout_minutes',
                        $authConfig[
                            'security'
                        ][
                            'lockout_minutes'
                        ]
                        ?? 15
                    ) }}"
                >

                <div class="field-help">
                    Lockout time after the maximum attempts are reached.
                </div>

            </div>

        </div>

    </div>

</div>


    
<div class="esubiz-auth-ui-panel" data-auth-ui-panel="registration">


{{-- ESUBIZ_REFERENCE_REGISTRATION_SETTINGS_V8 --}}

<div class="auth-card esubiz-registration-settings-card">

    <div class="esubiz-registration-card-heading">

        <div>

            <h2 class="auth-card-title">
                Registration Settings
            </h2>

            <p class="auth-card-subtitle">
                Control how new users can create accounts
                on this website.
            </p>

        </div>

    </div>


    <div class="esubiz-registration-option-list">


        {{-- ALLOW REGISTRATION --}}

        <div class="esubiz-registration-option">

            <div class="esubiz-registration-option-copy">

                <div class="esubiz-registration-option-title">
                    Allow user registration
                </div>

                <div class="esubiz-registration-option-description">
                    Allow visitors to create an account
                    from the public registration page.
                </div>

            </div>


            <label class="esubiz-switch">

                <input
                    type="hidden"
                    name="registration_enabled"
                    value="0"
                >

                <input
                    type="checkbox"
                    name="registration_enabled"
                    value="1"
                    {{
                        old(
                            'registration_enabled',
                            $authConfig[
                                'registration_enabled'
                            ]
                            ?? true
                        )
                        ? 'checked'
                        : ''
                    }}
                >

                <span class="esubiz-switch-slider"></span>

            </label>

        </div>


        {{-- AUTO LOGIN --}}

        <div class="esubiz-registration-option">

            <div class="esubiz-registration-option-copy">

                <div class="esubiz-registration-option-title">
                    Automatically log in new users
                </div>

                <div class="esubiz-registration-option-description">
                    Sign the user in automatically after
                    successful registration.
                </div>

            </div>


            <label class="esubiz-switch">

                <input
                    type="hidden"
                    name="auto_login"
                    value="0"
                >

                <input
                    type="checkbox"
                    name="auto_login"
                    value="1"
                    {{
                        old(
                            'auto_login',
                            $authConfig[
                                'auto_login'
                            ]
                            ?? true
                        )
                        ? 'checked'
                        : ''
                    }}
                >

                <span class="esubiz-switch-slider"></span>

            </label>

        </div>


        {{-- EMAIL VERIFICATION REFERENCE ROW --}}

        <div class="esubiz-registration-option">

            <div class="esubiz-registration-option-copy">

                <div class="esubiz-registration-option-title">
                    Require email verification
                </div>

                <div class="esubiz-registration-option-description">
                    Require new users to verify their email
                    address before account access.
                </div>

            </div>


            <div
                class="esubiz-planned-setting"
                title="Email verification workflow will be connected separately."
            >
                Planned
            </div>

        </div>

    </div>


    <div class="esubiz-registration-form-section">

        <div class="esubiz-registration-form-group">

            <label class="esubiz-registration-label">
                Default Role
            </label>

            <div class="esubiz-registration-readonly-setting">

                <div>
                    <strong>
                        Website User
                    </strong>

                    <span>
                        New public registrations receive
                        the safe default website user role.
                    </span>
                </div>

                <span class="esubiz-planned-setting">
                    Planned
                </span>

            </div>

        </div>


        <div class="esubiz-registration-form-group">

            <label
                for="esubiz-registration-redirect"
                class="esubiz-registration-label"
            >
                Registration Redirect
            </label>

            <input
                id="esubiz-registration-redirect"
                class="esubiz-registration-input"
                type="text"
                name="registration_redirect"
                value="{{ old(
                    'registration_redirect',
                    $authConfig[
                        'registration_redirect'
                    ]
                    ?? '/admin/dashboard'
                ) }}"
                placeholder="/admin/dashboard"
            >

            <div class="esubiz-registration-help">
                Relative website path users should be sent to
                after successful registration.
            </div>

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

                
{{-- ESUBIZ_DYNAMIC_REGISTRATION_FIELD_BUILDER_V1 --}}
{{-- ESUBIZ_REFERENCE_REGISTRATION_FIELD_MANAGER_V10 --}}

@php

    $registrationFields =
        $authConfig['registration_fields']
        ?? [];

    if (!is_array($registrationFields)) {
        $registrationFields = [];
    }

@endphp


<div class="auth-card esubiz-registration-fields-card">

    <div class="esubiz-fields-header">

        <div>
            <h2 class="auth-card-title">
                Registration Fields
            </h2>

            <p class="auth-card-subtitle">
                Configure the information users provide
                when creating an account.
            </p>
        </div>

        <button
            type="button"
            class="esubiz-add-registration-field"
            data-v10-add-registration-field
        >
            + Add Field
        </button>

    </div>


    <div class="esubiz-registration-fields-table">

        <div class="esubiz-fields-table-head">

            <div>Field</div>

            <div>Type</div>

            <div class="esubiz-fields-center">
                Required
            </div>

            <div class="esubiz-fields-center">
                Enabled
            </div>

            <div class="esubiz-fields-action">
                Action
            </div>

        </div>


        <div data-v10-registration-field-rows>

            @foreach($registrationFields as $fieldIndex => $field)

                @php

                    $field = is_array($field) ? $field : [];

                    $fieldKey =
                        (string) ($field['key'] ?? '');

                    $fieldLabel =
                        (string) ($field['label'] ?? '');

                    $fieldType =
                        (string) ($field['type'] ?? 'text');

                    $fieldPlaceholder =
                        (string) ($field['placeholder'] ?? '');

                    $isSystem =
                        !empty($field['system'] ?? false);

                    $isRequired =
                        !empty($field['required'] ?? false);

                    $isEnabled =
                        array_key_exists('enabled', $field)
                        ? !empty($field['enabled'])
                        : true;

                    $protectedSystemField =
                        $isSystem
                        &&
                        in_array(
                            $fieldKey,
                            [
                                'name',
                                'email',
                                'password',
                                'password_confirmation'
                            ],
                            true
                        );

                @endphp


                <div
                    class="esubiz-registration-field-row"
                    data-v10-registration-field-row
                    data-system-field="{{ $isSystem ? '1' : '0' }}"
                >

                    <input
                        type="hidden"
                        name="registration_fields[{{ $fieldIndex }}][key]"
                        value="{{ $fieldKey }}"
                    >

                    <input
                        type="hidden"
                        name="registration_fields[{{ $fieldIndex }}][system]"
                        value="{{ $isSystem ? '1' : '0' }}"
                    >


                    {{-- FIELD --}}

                    <div class="esubiz-field-main">

                        <input
                            type="text"
                            class="esubiz-field-label-input"
                            name="registration_fields[{{ $fieldIndex }}][label]"
                            value="{{ $fieldLabel }}"
                            placeholder="Field label"
                            {{ $isSystem ? 'readonly' : '' }}
                            required
                        >

                        <input
                            type="text"
                            class="esubiz-field-placeholder-input"
                            name="registration_fields[{{ $fieldIndex }}][placeholder]"
                            value="{{ $fieldPlaceholder }}"
                            placeholder="Placeholder"
                        >

                        @if($isSystem)

                            <span class="esubiz-system-field-badge">
                                System
                            </span>

                        @endif

                    </div>


                    {{-- TYPE --}}

                    <div>

                        <select
                            class="esubiz-field-type-select"
                            name="registration_fields[{{ $fieldIndex }}][type]"
                            {{ $isSystem ? 'disabled' : '' }}
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
                                    {{ $fieldType === $typeValue ? 'selected' : '' }}
                                >
                                    {{ $typeLabel }}
                                </option>

                            @endforeach

                        </select>

                        @if($isSystem)

                            <input
                                type="hidden"
                                name="registration_fields[{{ $fieldIndex }}][type]"
                                value="{{ $fieldType }}"
                            >

                        @endif

                    </div>


                    {{-- REQUIRED --}}

                    <div class="esubiz-fields-center">

                        <input
                            type="hidden"
                            name="registration_fields[{{ $fieldIndex }}][required]"
                            value="0"
                        >

                        <label class="esubiz-mini-switch">

                            <input
                                type="checkbox"
                                name="registration_fields[{{ $fieldIndex }}][required]"
                                value="1"
                                {{ $isRequired ? 'checked' : '' }}
                                {{ $protectedSystemField ? 'disabled' : '' }}
                            >

                            <span></span>

                        </label>

                        @if($protectedSystemField)

                            <input
                                type="hidden"
                                name="registration_fields[{{ $fieldIndex }}][required]"
                                value="{{ $isRequired ? '1' : '0' }}"
                            >

                        @endif

                    </div>


                    {{-- ENABLED --}}

                    <div class="esubiz-fields-center">

                        <input
                            type="hidden"
                            name="registration_fields[{{ $fieldIndex }}][enabled]"
                            value="0"
                        >

                        <label class="esubiz-mini-switch">

                            <input
                                type="checkbox"
                                name="registration_fields[{{ $fieldIndex }}][enabled]"
                                value="1"
                                {{ $isEnabled ? 'checked' : '' }}
                                {{ $isSystem ? 'disabled' : '' }}
                            >

                            <span></span>

                        </label>

                        @if($isSystem)

                            <input
                                type="hidden"
                                name="registration_fields[{{ $fieldIndex }}][enabled]"
                                value="{{ $isEnabled ? '1' : '0' }}"
                            >

                        @endif

                    </div>


                    {{-- ACTION --}}

                    <div class="esubiz-fields-action">

                        @if($isSystem)

                            <span class="esubiz-protected-field">
                                Protected
                            </span>

                        @else

                            <button
                                type="button"
                                class="esubiz-remove-field"
                                data-v10-remove-registration-field
                            >
                                Remove
                            </button>

                        @endif

                    </div>

                </div>

            @endforeach

        </div>

    </div>

</div>


<template data-v10-registration-field-template>

    <div
        class="esubiz-registration-field-row"
        data-v10-registration-field-row
        data-system-field="0"
    >

        <input
            type="hidden"
            name="registration_fields[__INDEX__][key]"
            value=""
        >

        <input
            type="hidden"
            name="registration_fields[__INDEX__][system]"
            value="0"
        >


        <div class="esubiz-field-main">

            <input
                type="text"
                class="esubiz-field-label-input"
                name="registration_fields[__INDEX__][label]"
                placeholder="Field label"
                required
            >

            <input
                type="text"
                class="esubiz-field-placeholder-input"
                name="registration_fields[__INDEX__][placeholder]"
                placeholder="Placeholder"
            >

        </div>


        <div>

            <select
                class="esubiz-field-type-select"
                name="registration_fields[__INDEX__][type]"
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


        <div class="esubiz-fields-center">

            <input
                type="hidden"
                name="registration_fields[__INDEX__][required]"
                value="0"
            >

            <label class="esubiz-mini-switch">

                <input
                    type="checkbox"
                    name="registration_fields[__INDEX__][required]"
                    value="1"
                >

                <span></span>

            </label>

        </div>


        <div class="esubiz-fields-center">

            <input
                type="hidden"
                name="registration_fields[__INDEX__][enabled]"
                value="0"
            >

            <label class="esubiz-mini-switch">

                <input
                    type="checkbox"
                    name="registration_fields[__INDEX__][enabled]"
                    value="1"
                    checked
                >

                <span></span>

            </label>

        </div>


        <div class="esubiz-fields-action">

            <button
                type="button"
                class="esubiz-remove-field"
                data-v10-remove-registration-field
            >
                Remove
            </button>

        </div>

    </div>

</template>


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
</div>


    <div class="esubiz-auth-ui-panel" data-auth-ui-panel="identity">

{{-- ESUBIZ_CORE_IDENTITY_PROVIDER_RECONNECTED_V5 --}}

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

</div>

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


<script>
/* ESUBIZ_AUTH_SETTINGS_TAB_VISIBILITY_V1 */

document.addEventListener('DOMContentLoaded', function () {

    const normalize = (value) =>
        String(value || '')
            .replace(/\s+/g, ' ')
            .trim()
            .toLowerCase();

    const tabNames = [
        'login providers',
        'branding & appearance',
        'security',
        'registration',
        'identity provider'
    ];

    /*
     * Locate the visible Authentication tab controls from
     * their existing text. No form markup is changed.
     */
    const candidates = Array.from(
        document.querySelectorAll(
            'button, a, [role="tab"], [data-auth-tab]'
        )
    );

    const tabs = {};

    candidates.forEach(function (element) {

        const name = normalize(
            element.textContent
        );

        if (
            tabNames.includes(name)
            && !tabs[name]
        ) {
            tabs[name] = element;
        }
    });


    /*
     * Existing definite panels.
     */
    const panels = {
        'branding & appearance':
            document.querySelector(
                '[data-auth-panel="branding"]'
            ),

        'security':
            document.querySelector(
                '[data-auth-panel="security"]'
            ),

        'registration':
            document.querySelector(
                '[data-auth-panel="registration"]'
            )
    };


    /*
     * Resolve Login Providers and Identity Provider using
     * their existing section headings/contents, because those
     * two sections do not currently share the same reliable
     * data-auth-panel naming.
     */
    const authPanels = Array.from(
        document.querySelectorAll(
            '.auth-panel, [data-auth-panel]'
        )
    );

    const panelText = (panel) =>
        normalize(panel.textContent);


    if (!panels['login providers']) {

        panels['login providers'] =
            authPanels.find(function (panel) {

                const content =
                    panelText(panel);

                return (
                    content.includes('google')
                    && content.includes('facebook')
                    && content.includes('tiktok')
                    && content.includes('client')
                );
            }) || null;
    }


    if (!panels['identity provider']) {

        panels['identity provider'] =
            authPanels.find(function (panel) {

                const content =
                    panelText(panel);

                return (
                    content.includes(
                        'identity provider'
                    )
                    && panel !==
                        panels['login providers']
                );
            }) || null;
    }


    /*
     * A few existing builds use an identity panel without
     * auth-panel class. Resolve that section by heading and
     * climb to the nearest settings card/panel.
     */
    if (!panels['identity provider']) {

        const headings = Array.from(
            document.querySelectorAll(
                'h1, h2, h3, h4, strong'
            )
        );

        const identityHeading =
            headings.find(function (heading) {

                const value =
                    normalize(
                        heading.textContent
                    );

                return (
                    value ===
                    'identity provider'
                    ||
                    value.includes(
                        'identity provider settings'
                    )
                );
            });

        if (identityHeading) {

            panels['identity provider'] =
                identityHeading.closest(
                    '.auth-panel, .auth-card, [data-auth-panel]'
                );
        }
    }


    /*
     * Ensure we have four functional sections before doing
     * anything. Login Providers or Identity may intentionally
     * use a different wrapper; if unresolved we stop quietly
     * rather than damaging the page.
     */
    const availablePanels =
        Object.values(panels)
            .filter(Boolean);

    if (
        !panels['branding & appearance']
        ||
        !panels['security']
        ||
        !panels['registration']
    ) {
        console.error(
            'Authentication tab repair: core panels unresolved.'
        );

        return;
    }


    /*
     * Include unique resolved panels only.
     */
    const uniquePanels =
        [...new Set(
            availablePanels
        )];


    function activate(name) {

        const target =
            panels[name];

        if (!target) {
            return;
        }

        uniquePanels.forEach(
            function (panel) {

                panel.style.display =
                    panel === target
                        ? ''
                        : 'none';
            }
        );


        Object.entries(tabs)
            .forEach(
                function ([tabName, tab]) {

                    const active =
                        tabName === name;

                    tab.classList.toggle(
                        'active',
                        active
                    );

                    tab.setAttribute(
                        'aria-selected',
                        active
                            ? 'true'
                            : 'false'
                    );
                }
            );
    }


    Object.entries(tabs)
        .forEach(
            function ([name, tab]) {

                tab.addEventListener(
                    'click',
                    function (event) {

                        /*
                         * These are settings tabs, not navigation
                         * away from this page.
                         */
                        event.preventDefault();

                        activate(name);
                    }
                );
            }
        );


    /*
     * Respect whichever tab the existing page already marks
     * active. Otherwise start at Login Providers.
     */
    let initial = null;

    Object.entries(tabs)
        .some(
            function ([name, tab]) {

                if (
                    tab.classList.contains(
                        'active'
                    )
                    ||
                    tab.getAttribute(
                        'aria-selected'
                    ) === 'true'
                ) {
                    initial = name;
                    return true;
                }

                return false;
            }
        );


    if (
        !initial
        ||
        !panels[initial]
    ) {
        initial =
            panels['login providers']
                ? 'login providers'
                : 'branding & appearance';
    }

    activate(initial);

});
</script>



<style>

/* ESUBIZ_AUTH_REFERENCE_UI_V1 */

.esubiz-auth-reference {
    width: 100%;
    max-width: 1180px;
    margin: 0 auto;
    box-sizing: border-box;
}

.esubiz-auth-tabs {
    display: flex;
    justify-content: center;
    gap: 8px;
    width: 100%;
    margin-bottom: 18px;
    border-bottom: 1px solid #e5e7eb;
}

.esubiz-auth-tab {
    appearance: none;
    border: 0;
    border-bottom: 2px solid transparent;
    background: transparent;
    padding: 11px 16px;
    color: #64748b;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
}

.esubiz-auth-tab.active {
    color: #2563eb;
    border-bottom-color: #2563eb;
}

.esubiz-auth-ui-panel {
    display: none;
}

.esubiz-auth-ui-panel.active {
    display: block;
}

.esubiz-settings-card {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    padding: 20px;
    margin-bottom: 18px;
}

.esubiz-card-heading {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 12px;
}

.esubiz-card-heading h3 {
    margin: 0 0 4px;
    font-size: 16px;
    color: #0f172a;
}

.esubiz-card-heading p {
    margin: 0;
    color: #64748b;
    font-size: 12px;
}

.esubiz-setting-row,
.esubiz-provider-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 18px;
    padding: 13px 0;
    border-bottom: 1px solid #eef2f7;
}

.esubiz-setting-row:last-child,
.esubiz-provider-row:last-child {
    border-bottom: 0;
}

.esubiz-setting-row strong,
.esubiz-provider-row strong {
    display: block;
    color: #0f172a;
    font-size: 13px;
    margin-bottom: 3px;
}

.esubiz-setting-row small,
.esubiz-provider-row small {
    display: block;
    color: #64748b;
    font-size: 11px;
}

.esubiz-field-grid {
    display: grid;
    grid-template-columns:
        repeat(
            2,
            minmax(0, 1fr)
        );
    gap: 14px;
    margin-top: 16px;
}

.esubiz-provider-list {
    display: flex;
    flex-direction: column;
}

.esubiz-provider-info {
    display: flex;
    align-items: center;
    gap: 11px;
}

.esubiz-provider-icon {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 34px;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    font-weight: 800;
    color: #0f172a;
}

.provider-esubiz {
    background: #0b1f3a;
    color: #ffffff;
}

.provider-google {
    color: #4285f4;
}

.provider-facebook {
    background: #1877f2;
    color: #ffffff;
}

.provider-instagram {
    color: #e1306c;
}

.provider-tiktok {
    color: #000000;
}

.provider-x {
    background: #000000;
    color: #ffffff;
}

.esubiz-info-box {
    margin-top: 14px;
    padding: 12px 14px;
    border-radius: 9px;
    border: 1px solid #bfdbfe;
    background: #eff6ff;
    color: #475569;
    font-size: 11px;
}

.esubiz-auth-reference input,
.esubiz-auth-reference select,
.esubiz-auth-reference textarea {
    max-width: 100%;
    box-sizing: border-box;
}

@media (max-width: 760px) {

    .esubiz-auth-tabs {
        justify-content: flex-start;
        overflow-x: auto;
    }

    .esubiz-auth-tab {
        white-space: nowrap;
    }

    .esubiz-field-grid {
        grid-template-columns: 1fr;
    }
}

</style>


<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const tabs =
            document.querySelectorAll(
                '[data-auth-ui-tab]'
            );

        const panels =
            document.querySelectorAll(
                '[data-auth-ui-panel]'
            );

        tabs.forEach(
            function (tab) {

                tab.addEventListener(
                    'click',
                    function () {

                        const target =
                            tab.getAttribute(
                                'data-auth-ui-tab'
                            );

                        tabs.forEach(
                            function (item) {
                                item.classList.remove(
                                    'active'
                                );
                            }
                        );

                        panels.forEach(
                            function (panel) {

                                panel.classList.toggle(
                                    'active',
                                    panel.getAttribute(
                                        'data-auth-ui-panel'
                                    ) === target
                                );
                            }
                        );

                        tab.classList.add(
                            'active'
                        );
                    }
                );

            }
        );

    }
);

</script>



<style>

/* ESUBIZ_AUTH_REFERENCE_UI_CORRECTED_V3 */

.esubiz-auth-corrected {
    width: 100%;
    max-width: 1180px;
    margin: 0 auto;
    min-width: 0;
}

.esubiz-auth-corrected,
.esubiz-auth-corrected * {
    box-sizing: border-box;
}

.esubiz-auth-corrected .esubiz-auth-tabs {
    display: flex;
    justify-content: center;
    gap: 12px;
    width: 100%;
    margin-bottom: 18px;
    border-bottom: 1px solid #e5e7eb;
}

.esubiz-auth-corrected .esubiz-auth-tab {
    appearance: none;
    border: 0;
    border-bottom: 2px solid transparent;
    background: transparent;
    padding: 11px 14px;
    color: #64748b;
    font-weight: 600;
    cursor: pointer;
    white-space: nowrap;
}

.esubiz-auth-corrected .esubiz-auth-tab.active {
    color: #2563eb;
    border-bottom-color: #2563eb;
}

.esubiz-auth-corrected .esubiz-auth-ui-panel {
    display: none;
}

.esubiz-auth-corrected .esubiz-auth-ui-panel.active {
    display: block;
}


/*
 * Reference styling ONLY:
 * Security + Registration.
 */
.esubiz-reference-card {
    width: 100%;
    padding: 20px;
    margin-bottom: 16px;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
}

.esubiz-reference-heading {
    margin-bottom: 12px;
}

.esubiz-reference-heading h3 {
    margin: 0 0 4px;
    color: #0f172a;
    font-size: 16px;
}

.esubiz-reference-heading p {
    margin: 0;
    color: #64748b;
    font-size: 12px;
}

.esubiz-reference-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 18px;
    min-height: 62px;
    padding: 12px 0;
    border-bottom: 1px solid #eef2f7;
}

.esubiz-reference-row strong {
    display: block;
    margin-bottom: 3px;
    color: #0f172a;
    font-size: 13px;
}

.esubiz-reference-row small {
    display: block;
    color: #64748b;
    font-size: 11px;
}

.esubiz-reference-grid {
    display: grid;
    grid-template-columns:
        repeat(
            2,
            minmax(0, 1fr)
        );
    gap: 14px;
    padding-top: 16px;
}

.esubiz-reference-field {
    margin-top: 16px;
}

[data-auth-ui-panel="registration"]
.auth-card,
[data-auth-ui-panel="registration"]
.esubiz-settings-card {
    width: 100% !important;
    max-width: 100% !important;
}


@media (max-width: 800px) {

    .esubiz-auth-corrected .esubiz-auth-tabs {
        justify-content: flex-start;
        overflow-x: auto;
    }

    .esubiz-reference-grid {
        grid-template-columns: 1fr;
    }
}

</style>


<script>

/* ESUBIZ_AUTH_REFERENCE_TABS_CORRECTED_V3 */

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const root =
            document.querySelector(
                '.esubiz-auth-corrected'
            );

        if (!root) {
            return;
        }

        const tabs =
            root.querySelectorAll(
                ':scope > .esubiz-auth-tabs [data-auth-ui-tab]'
            );

        const panels =
            root.querySelectorAll(
                ':scope > [data-auth-ui-panel]'
            );


        function activate(name) {

            tabs.forEach(
                function (tab) {

                    tab.classList.toggle(
                        'active',
                        tab.dataset.authUiTab === name
                    );

                }
            );

            panels.forEach(
                function (panel) {

                    panel.classList.toggle(
                        'active',
                        panel.dataset.authUiPanel === name
                    );

                }
            );

        }


        tabs.forEach(
            function (tab) {

                tab.addEventListener(
                    'click',
                    function () {

                        activate(
                            tab.dataset.authUiTab
                        );

                    }
                );

            }
        );

        activate(
            'branding'
        );

    }
);

</script>


<style>

/* ESUBIZ_AUTH_BRANDING_IDENTITY_FINAL_REPAIR_V5 */

.esubiz-branding-upload-grid,
.esubiz-branding-field-grid,
.esubiz-auth-color-grid {
    display: grid;
    grid-template-columns:
        repeat(
            2,
            minmax(0, 1fr)
        );
    gap: 14px;
}

.esubiz-auth-color-control {
    display: grid;
    grid-template-columns:
        46px minmax(0, 1fr);
    gap: 8px;
    align-items: center;
}

.esubiz-auth-color-control
input[type="color"] {
    width: 46px !important;
    height: 40px;
    padding: 3px;
    border: 1px solid #dbe2ea;
    border-radius: 8px;
    background: #ffffff;
    cursor: pointer;
}

.esubiz-auth-current-file {
    margin-bottom: 7px;
    padding: 7px 9px;
    border-radius: 7px;
    background: #f8fafc;
    color: #64748b;
    font-size: 11px;
}

[data-auth-ui-panel="branding"]
.auth-card {
    width: 100%;
    max-width: 100%;
    margin-bottom: 16px;
}

[data-auth-ui-panel="identity"]
.auth-card {
    width: 100%;
    max-width: 100%;
}


@media (max-width: 760px) {

    .esubiz-branding-upload-grid,
    .esubiz-branding-field-grid,
    .esubiz-auth-color-grid {
        grid-template-columns: 1fr;
    }

}

</style>


<script>

/* ESUBIZ_AUTH_BRANDING_COLOR_SYNC_V5 */

document.addEventListener(
    'DOMContentLoaded',
    function () {

        document.querySelectorAll(
            '[data-auth-color-picker]'
        ).forEach(
            function (picker) {

                const container =
                    picker.closest(
                        '.esubiz-auth-color-control'
                    );

                if (!container) {
                    return;
                }

                const hex =
                    container.querySelector(
                        '[data-auth-color-hex]'
                    );

                if (!hex) {
                    return;
                }


                picker.addEventListener(
                    'input',
                    function () {

                        hex.value =
                            picker.value;

                    }
                );


                hex.addEventListener(
                    'input',
                    function () {

                        if (
                            /^#[0-9A-Fa-f]{6}$/.test(
                                hex.value
                            )
                        ) {
                            picker.value =
                                hex.value;
                        }

                    }
                );

            }
        );

    }
);

</script>


<style>

/* ESUBIZ_AUTH_PREMIUM_TABS_IDENTITY_FIX_V6 */


/* ==========================================================
   PREMIUM AUTHENTICATION NAVIGATION
   ========================================================== */

.esubiz-auth-corrected
.esubiz-auth-tabs {
    display: flex !important;
    align-items: center;
    justify-content: center;

    width: fit-content;
    max-width: 100%;

    margin:
        0 auto
        26px !important;

    padding: 6px !important;

    gap: 4px !important;

    border: 1px solid #e6eaf0 !important;
    border-radius: 14px !important;

    background:
        linear-gradient(
            180deg,
            #ffffff 0%,
            #f8fafc 100%
        ) !important;

    box-shadow:
        0 1px 2px rgba(15, 23, 42, 0.04),
        0 8px 24px rgba(15, 23, 42, 0.05);

    overflow-x: auto;
    scrollbar-width: none;
}

.esubiz-auth-corrected
.esubiz-auth-tabs::-webkit-scrollbar {
    display: none;
}


.esubiz-auth-corrected
.esubiz-auth-tab {
    position: relative;

    display: inline-flex !important;
    align-items: center;
    justify-content: center;

    min-height: 42px;

    padding:
        10px
        16px !important;

    border: 0 !important;
    border-radius: 10px !important;

    background: transparent !important;

    color: #64748b !important;

    font-size: 13px;
    font-weight: 600 !important;
    line-height: 1;

    letter-spacing: -0.01em;

    white-space: nowrap;

    cursor: pointer;

    transition:
        background-color .18s ease,
        color .18s ease,
        box-shadow .18s ease,
        transform .18s ease !important;
}


.esubiz-auth-corrected
.esubiz-auth-tab:hover {
    color: #0f172a !important;
    background: #f1f5f9 !important;
}


.esubiz-auth-corrected
.esubiz-auth-tab.active {
    color: #0f172a !important;

    background: #ffffff !important;

    border-bottom: 0 !important;

    box-shadow:
        0 1px 2px rgba(15, 23, 42, 0.06),
        0 4px 12px rgba(15, 23, 42, 0.07) !important;
}


.esubiz-auth-corrected
.esubiz-auth-tab.active::after {
    content: "";

    position: absolute;

    left: 50%;
    bottom: 5px;

    width: 18px;
    height: 2px;

    border-radius: 999px;

    background: #2563eb;

    transform:
        translateX(-50%);
}


/* ==========================================================
   PREMIUM CONTENT TRANSITION
   ========================================================== */

.esubiz-auth-corrected
> .esubiz-auth-ui-panel.active {
    animation:
        esubizAuthPanelIn
        .18s ease-out;
}


@keyframes esubizAuthPanelIn {

    from {
        opacity: 0;
        transform:
            translateY(4px);
    }

    to {
        opacity: 1;
        transform:
            translateY(0);
    }

}


/* ==========================================================
   IDENTITY PROVIDER VISIBILITY FIX

   The new esubiz-auth-ui-panel controls tab visibility.

   Therefore the OLD nested auth-panel must NOT independently
   hide the Identity Provider after the outer tab is activated.
   ========================================================== */

.esubiz-auth-corrected
[data-auth-ui-panel="identity"].active
> .auth-panel,

.esubiz-auth-corrected
[data-auth-ui-panel="identity"].active
[data-auth-panel="identity"] {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;

    position: relative !important;

    width: 100% !important;
    max-width: 100% !important;

    height: auto !important;
    min-height: 0 !important;

    margin: 0 !important;

    left: auto !important;
    right: auto !important;

    transform: none !important;

    overflow: visible !important;

    pointer-events: auto !important;
}


.esubiz-auth-corrected
[data-auth-ui-panel="identity"].active
.auth-card {
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;

    width: 100% !important;
    max-width: 100% !important;

    min-height: 120px;

    position: relative !important;

    transform: none !important;
}


/* ==========================================================
   IDENTITY PROVIDER CARD POLISH
   ========================================================== */

.esubiz-auth-corrected
[data-auth-ui-panel="identity"]
.auth-card {
    padding: 22px !important;

    border:
        1px solid
        #e5eaf0 !important;

    border-radius:
        14px !important;

    background:
        #ffffff !important;

    box-shadow:
        0 1px 2px rgba(15, 23, 42, 0.03);
}


.esubiz-auth-corrected
[data-auth-ui-panel="identity"]
.auth-card-title {
    margin:
        0
        0
        5px !important;

    color:
        #0f172a !important;

    font-size:
        16px !important;

    font-weight:
        700 !important;

    letter-spacing:
        -0.015em;
}


.esubiz-auth-corrected
[data-auth-ui-panel="identity"]
.auth-card-subtitle {
    max-width:
        720px;

    margin:
        0
        0
        18px !important;

    color:
        #64748b !important;

    font-size:
        12px !important;

    line-height:
        1.65 !important;
}


.esubiz-auth-corrected
[data-auth-ui-panel="identity"]
.coming-soon {
    display: block !important;

    width: 100%;

    padding:
        16px
        18px !important;

    border:
        1px solid
        #e8edf3 !important;

    border-radius:
        11px !important;

    background:
        #f8fafc !important;

    color:
        #64748b !important;

    font-size:
        12px;

    line-height:
        1.65;
}


.esubiz-auth-corrected
[data-auth-ui-panel="identity"]
.coming-soon strong {
    display:
        block;

    margin-bottom:
        4px;

    color:
        #0f172a !important;

    font-size:
        13px;
}


/* ==========================================================
   RESPONSIVE
   ========================================================== */

@media (max-width: 900px) {

    .esubiz-auth-corrected
    .esubiz-auth-tabs {
        justify-content:
            flex-start;

        width:
            100%;

        margin-bottom:
            20px !important;
    }

}


@media (max-width: 600px) {

    .esubiz-auth-corrected
    .esubiz-auth-tab {
        min-height:
            40px;

        padding:
            9px
            13px !important;

        font-size:
            12px;
    }

}

</style>

{{-- ESUBIZ_AUTH_PREMIUM_TABS_IDENTITY_FIX_V6 --}}


<style>

/* ESUBIZ_AUTH_PREMIUM_BLUE_TABS_V7 */

/*
 * Authentication navigation:
 * inactive = light blue
 * active   = deep blue
 */

.esubiz-auth-corrected
.esubiz-auth-tabs {
    gap: 6px !important;

    padding: 6px !important;

    border: 1px solid #dbeafe !important;
    border-radius: 14px !important;

    background: #eff6ff !important;

    box-shadow:
        0 1px 2px rgba(15, 23, 42, 0.03),
        0 8px 24px rgba(37, 99, 235, 0.06) !important;
}


/* INACTIVE */

.esubiz-auth-corrected
.esubiz-auth-tab {
    min-height: 42px;

    padding:
        10px
        16px !important;

    border:
        1px solid
        #bfdbfe !important;

    border-radius:
        10px !important;

    background:
        #60a5fa !important;

    color:
        #ffffff !important;

    font-weight:
        600 !important;

    box-shadow:
        0 1px 2px rgba(37, 99, 235, 0.08) !important;

    transition:
        background-color .18s ease,
        border-color .18s ease,
        box-shadow .18s ease,
        transform .18s ease !important;
}


/* INACTIVE HOVER */

.esubiz-auth-corrected
.esubiz-auth-tab:hover {
    background:
        #3b82f6 !important;

    border-color:
        #3b82f6 !important;

    color:
        #ffffff !important;

    transform:
        translateY(-1px);

    box-shadow:
        0 4px 10px rgba(37, 99, 235, 0.16) !important;
}


/* ACTIVE */

.esubiz-auth-corrected
.esubiz-auth-tab.active {
    background:
        #1d4ed8 !important;

    border-color:
        #1d4ed8 !important;

    color:
        #ffffff !important;

    box-shadow:
        0 4px 12px rgba(29, 78, 216, 0.24) !important;

    transform:
        none;
}


/* Active indicator */

.esubiz-auth-corrected
.esubiz-auth-tab.active::after {
    content: "";

    position: absolute;

    left: 50%;
    bottom: 5px;

    width: 20px;
    height: 2px;

    border-radius: 999px;

    background:
        rgba(255, 255, 255, 0.92) !important;

    transform:
        translateX(-50%);
}


/* Keyboard accessibility */

.esubiz-auth-corrected
.esubiz-auth-tab:focus-visible {
    outline:
        3px solid
        rgba(59, 130, 246, 0.25) !important;

    outline-offset:
        2px;
}


/* Mobile keeps the same blue treatment */

@media (max-width: 600px) {

    .esubiz-auth-corrected
    .esubiz-auth-tab {
        padding:
            9px
            13px !important;
    }

}

</style>



<style>

/* ESUBIZ_REFERENCE_REGISTRATION_UI_V8 */

[data-auth-ui-panel="registration"] {
    width: 100%;
}


[data-auth-ui-panel="registration"]
.auth-card {
    width: 100%;
    max-width: 100%;

    border: 1px solid #e5eaf0;
    border-radius: 14px;

    background: #ffffff;

    box-shadow:
        0 1px 2px rgba(15, 23, 42, 0.03);

    overflow: hidden;
}


.esubiz-registration-settings-card {
    padding: 0 !important;
    margin-bottom: 18px !important;
}


.esubiz-registration-card-heading {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;

    padding:
        20px
        22px
        17px;

    border-bottom:
        1px solid
        #edf0f4;
}


.esubiz-registration-card-heading
.auth-card-title {
    margin:
        0
        0
        4px !important;

    color:
        #0f172a;

    font-size:
        16px !important;

    font-weight:
        700 !important;

    letter-spacing:
        -0.015em;
}


.esubiz-registration-card-heading
.auth-card-subtitle {
    margin: 0 !important;

    color:
        #64748b;

    font-size:
        12px !important;

    line-height:
        1.55;
}


.esubiz-registration-option-list {
    width: 100%;
}


.esubiz-registration-option {
    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 24px;

    min-height: 72px;

    padding:
        15px
        22px;

    border-bottom:
        1px solid
        #edf0f4;
}


.esubiz-registration-option-copy {
    min-width: 0;
}


.esubiz-registration-option-title {
    margin-bottom: 3px;

    color:
        #172033;

    font-size:
        13px;

    font-weight:
        600;

    line-height:
        1.4;
}


.esubiz-registration-option-description {
    max-width: 680px;

    color:
        #7b8798;

    font-size:
        11px;

    line-height:
        1.55;
}


/* Toggle */

.esubiz-switch {
    position: relative;

    flex:
        0 0 auto;

    display:
        inline-flex;

    width:
        42px;

    height:
        24px;

    cursor:
        pointer;
}


.esubiz-switch input[type="checkbox"] {
    position: absolute;

    width: 1px;
    height: 1px;

    opacity: 0;

    pointer-events: none;
}


.esubiz-switch-slider {
    position: absolute;

    inset: 0;

    border-radius:
        999px;

    background:
        #cbd5e1;

    transition:
        .18s ease;
}


.esubiz-switch-slider::before {
    content: "";

    position: absolute;

    top: 3px;
    left: 3px;

    width: 18px;
    height: 18px;

    border-radius:
        50%;

    background:
        #ffffff;

    box-shadow:
        0 1px 3px rgba(15, 23, 42, 0.20);

    transition:
        .18s ease;
}


.esubiz-switch
input[type="checkbox"]:checked
+ .esubiz-switch-slider {
    background:
        #2563eb;
}


.esubiz-switch
input[type="checkbox"]:checked
+ .esubiz-switch-slider::before {
    transform:
        translateX(18px);
}


.esubiz-switch
input[type="checkbox"]:focus-visible
+ .esubiz-switch-slider {
    outline:
        3px solid
        rgba(37, 99, 235, 0.18);

    outline-offset:
        2px;
}


/* Lower settings */

.esubiz-registration-form-section {
    padding:
        20px
        22px
        22px;

    background:
        #fbfcfe;
}


.esubiz-registration-form-group
+ .esubiz-registration-form-group {
    margin-top:
        18px;
}


.esubiz-registration-label {
    display:
        block;

    margin-bottom:
        7px;

    color:
        #334155;

    font-size:
        12px;

    font-weight:
        600;
}


.esubiz-registration-input {
    display:
        block;

    width:
        100%;

    min-height:
        42px;

    padding:
        9px
        12px;

    border:
        1px solid
        #dbe2ea;

    border-radius:
        9px;

    background:
        #ffffff;

    color:
        #0f172a;

    font-size:
        12px;

    outline:
        none;

    transition:
        border-color .18s ease,
        box-shadow .18s ease;
}


.esubiz-registration-input:focus {
    border-color:
        #60a5fa;

    box-shadow:
        0 0 0 3px
        rgba(59, 130, 246, 0.10);
}


.esubiz-registration-help {
    margin-top:
        6px;

    color:
        #94a3b8;

    font-size:
        10px;

    line-height:
        1.5;
}


/* Safe role presentation */

.esubiz-registration-readonly-setting {
    display:
        flex;

    align-items:
        center;

    justify-content:
        space-between;

    gap:
        18px;

    width:
        100%;

    padding:
        11px
        13px;

    border:
        1px solid
        #e2e8f0;

    border-radius:
        9px;

    background:
        #ffffff;
}


.esubiz-registration-readonly-setting
strong {
    display:
        block;

    margin-bottom:
        2px;

    color:
        #334155;

    font-size:
        12px;
}


.esubiz-registration-readonly-setting
span:not(.esubiz-planned-setting) {
    display:
        block;

    color:
        #94a3b8;

    font-size:
        10px;
}


.esubiz-planned-setting {
    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    flex:
        0 0 auto;

    min-height:
        24px;

    padding:
        4px
        9px;

    border:
        1px solid
        #dbeafe;

    border-radius:
        999px;

    background:
        #eff6ff;

    color:
        #2563eb;

    font-size:
        9px;

    font-weight:
        700;

    text-transform:
        uppercase;

    letter-spacing:
        .04em;
}


/*
 * Existing Registration Field Builder remains the next card.
 */

[data-auth-ui-panel="registration"]
.auth-card
+ .auth-card {
    margin-top:
        18px;
}


@media (max-width: 700px) {

    .esubiz-registration-option {
        gap:
            14px;

        padding:
            14px
            16px;
    }


    .esubiz-registration-card-heading,
    .esubiz-registration-form-section {
        padding-left:
            16px;

        padding-right:
            16px;
    }


    .esubiz-registration-readonly-setting {
        align-items:
            flex-start;
    }

}

</style>



<style>

/* ESUBIZ_REFERENCE_REGISTRATION_FIELD_MANAGER_V10 */

.esubiz-registration-fields-card {
    padding: 0 !important;
    margin-top: 18px !important;
    overflow: hidden !important;
}

.esubiz-fields-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;

    padding: 20px 22px;

    border-bottom: 1px solid #edf0f4;
}

.esubiz-fields-header .auth-card-title {
    margin: 0 0 4px !important;
}

.esubiz-fields-header .auth-card-subtitle {
    margin: 0 !important;
}

.esubiz-add-registration-field {
    flex: 0 0 auto;

    min-height: 38px;
    padding: 8px 14px;

    border: 1px solid #2563eb;
    border-radius: 9px;

    background: #2563eb;
    color: #fff;

    font-size: 11px;
    font-weight: 700;

    cursor: pointer;
}

.esubiz-add-registration-field:hover {
    background: #1d4ed8;
    border-color: #1d4ed8;
}

.esubiz-registration-fields-table {
    width: 100%;
    overflow-x: auto;
}

.esubiz-fields-table-head,
.esubiz-registration-field-row {
    display: grid;

    grid-template-columns:
        minmax(230px, 2fr)
        minmax(130px, 1fr)
        90px
        90px
        100px;

    align-items: center;
    column-gap: 14px;

    min-width: 760px;
}

.esubiz-fields-table-head {
    padding: 11px 18px;

    border-bottom: 1px solid #e8edf3;

    background: #f8fafc;
    color: #64748b;

    font-size: 10px;
    font-weight: 700;

    text-transform: uppercase;
    letter-spacing: .04em;
}

.esubiz-registration-field-row {
    padding: 14px 18px;

    border-bottom: 1px solid #eef2f6;

    background: #fff;
}

.esubiz-registration-field-row:last-child {
    border-bottom: 0;
}

.esubiz-registration-field-row:hover {
    background: #fbfdff;
}

.esubiz-field-main {
    position: relative;
}

.esubiz-field-label-input,
.esubiz-field-placeholder-input,
.esubiz-field-type-select {
    display: block;

    width: 100%;

    border: 1px solid #dbe2ea;
    border-radius: 8px;

    background: #fff;
    color: #172033;

    font-size: 11px;

    outline: none;
}

.esubiz-field-label-input {
    min-height: 38px;
    padding: 8px 10px;

    font-weight: 600;
}

.esubiz-field-placeholder-input {
    min-height: 32px;

    margin-top: 6px;
    padding: 6px 10px;

    color: #64748b;
    font-size: 10px;
}

.esubiz-field-type-select {
    min-height: 38px;
    padding: 7px 9px;
}

.esubiz-field-label-input:focus,
.esubiz-field-placeholder-input:focus,
.esubiz-field-type-select:focus {
    border-color: #60a5fa;

    box-shadow:
        0 0 0 3px rgba(59, 130, 246, .08);
}

.esubiz-field-label-input[readonly],
.esubiz-field-type-select:disabled {
    background: #f8fafc;
    color: #475569;
}

.esubiz-system-field-badge {
    display: inline-flex;

    margin-top: 6px;
    padding: 3px 7px;

    border-radius: 999px;

    background: #eff6ff;
    color: #2563eb;

    font-size: 8px;
    font-weight: 700;

    text-transform: uppercase;
    letter-spacing: .04em;
}

.esubiz-fields-center {
    display: flex;
    align-items: center;
    justify-content: center;
}

.esubiz-fields-action {
    display: flex;
    align-items: center;
    justify-content: flex-end;
}


/* compact switches */

.esubiz-mini-switch {
    position: relative;

    display: inline-flex;

    width: 36px;
    height: 20px;

    cursor: pointer;
}

.esubiz-mini-switch input[type="checkbox"] {
    position: absolute;

    width: 1px;
    height: 1px;

    opacity: 0;
}

.esubiz-mini-switch span {
    position: absolute;

    inset: 0;

    border-radius: 999px;

    background: #cbd5e1;

    transition: .18s ease;
}

.esubiz-mini-switch span::before {
    content: "";

    position: absolute;

    top: 3px;
    left: 3px;

    width: 14px;
    height: 14px;

    border-radius: 50%;

    background: #fff;

    box-shadow:
        0 1px 2px rgba(15, 23, 42, .18);

    transition: .18s ease;
}

.esubiz-mini-switch input:checked + span {
    background: #2563eb;
}

.esubiz-mini-switch input:checked + span::before {
    transform: translateX(16px);
}

.esubiz-mini-switch input:disabled + span {
    opacity: .65;
    cursor: default;
}

.esubiz-remove-field {
    padding: 6px 8px;

    border: 0;

    background: transparent;
    color: #dc2626;

    font-size: 10px;
    font-weight: 600;

    cursor: pointer;
}

.esubiz-remove-field:hover {
    text-decoration: underline;
}

.esubiz-protected-field {
    color: #94a3b8;

    font-size: 9px;
    font-weight: 600;
}

@media (max-width: 700px) {

    .esubiz-fields-header {
        align-items: flex-start;
        padding: 16px;
    }

}

</style>


<script>

/* ESUBIZ_REFERENCE_REGISTRATION_FIELD_MANAGER_JS_V10 */

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const panel =
            document.querySelector(
                '[data-auth-ui-panel="registration"]'
            );

        if (!panel) {
            return;
        }

        const rows =
            panel.querySelector(
                '[data-v10-registration-field-rows]'
            );

        const template =
            panel.querySelector(
                '[data-v10-registration-field-template]'
            );

        const addButton =
            panel.querySelector(
                '[data-v10-add-registration-field]'
            );

        if (!rows || !template || !addButton) {
            return;
        }

        let nextIndex =
            rows.querySelectorAll(
                '[data-v10-registration-field-row]'
            ).length;


        addButton.addEventListener(
            'click',
            function () {

                const html =
                    template.innerHTML.replace(
                        /__INDEX__/g,
                        String(nextIndex)
                    );

                nextIndex++;

                rows.insertAdjacentHTML(
                    'beforeend',
                    html
                );

                const allRows =
                    rows.querySelectorAll(
                        '[data-v10-registration-field-row]'
                    );

                const row =
                    allRows[allRows.length - 1];

                const input =
                    row
                    ? row.querySelector(
                        '.esubiz-field-label-input'
                    )
                    : null;

                if (input) {
                    input.focus();
                }

            }
        );


        rows.addEventListener(
            'click',
            function (event) {

                const button =
                    event.target.closest(
                        '[data-v10-remove-registration-field]'
                    );

                if (!button) {
                    return;
                }

                const row =
                    button.closest(
                        '[data-v10-registration-field-row]'
                    );

                if (
                    !row
                    ||
                    row.dataset.systemField === '1'
                ) {
                    return;
                }

                row.remove();

            }
        );

    }
);

</script>

