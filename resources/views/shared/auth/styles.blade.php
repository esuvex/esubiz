{{-- ESUBIZ_SHARED_PREMIUM_AUTH_STYLES_V1 --}}
{{-- ESUBIZ_CORE_AUTH_VISUAL_SOURCE_V14 --}}

<style>
/*
 * ==========================================================
 * CORE AUTH VISUAL SOURCE
 * ==========================================================
 *
 * The existing Esubiz Core auth implementation is the visual
 * reference for Central and Core.
 *
 * Core itself is not modified by this file.
 */

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

/*
 * ==========================================================
 * SHARED CENTRAL COMPATIBILITY LAYER
 * ==========================================================
 *
 * Maps Central's semantic shared classes onto the same premium
 * single-card language used by Esubiz Core.
 */

:root {
    --es-auth-primary:
        {{ $authPrimary ?? '#0b1f3a' }};

    --es-auth-accent:
        {{ $authAccent ?? '#c89b3c' }};

    --es-auth-bg:
        {{ $authBackground ?? '#f5f7fb' }};

    --es-auth-card:
        {{ $authCard ?? '#ffffff' }};

    --es-auth-text:
        {{ $authText ?? '#172033' }};

    --es-auth-muted:
        {{ $authMuted ?? '#667085' }};
}

body.esubiz-auth-body {
    min-height: 100vh;
    margin: 0;
}

.esubiz-auth-page {
    min-height: 100vh;
    width: 100%;

    display: flex;
    align-items: center;
    justify-content: center;

    padding: 28px 16px;
}

.esubiz-auth-shell {
    width: 100%;
    max-width: 720px;
}

.esubiz-auth-brand {
    display: flex;
    align-items: center;
    justify-content: center;

    min-height: 60px;
    margin-bottom: 18px;
}

.esubiz-auth-brand-logo {
    display: block;

    width: auto;
    max-width: 180px;
    max-height: 60px;

    object-fit: contain;
}

.esubiz-auth-brand-text {
    color: var(--es-auth-primary);

    font-size: 26px;
    font-weight: 800;
}

.esubiz-auth-card {
    overflow: hidden;

    background:
        var(--es-auth-card);

    border:
        1px solid rgba(15, 23, 42, .09);

    border-radius: 20px;

    box-shadow:
        0 24px 65px
        rgba(15, 23, 42, .12);
}

.esubiz-auth-card-accent {
    height: 4px;

    background:
        linear-gradient(
            90deg,
            var(--es-auth-primary),
            var(--es-auth-accent)
        );
}

.esubiz-auth-card-body {
    padding: 34px;
}

.esubiz-auth-heading {
    margin-bottom: 27px;
    text-align: center;
}

.esubiz-auth-heading h1 {
    margin: 0 0 8px;

    color:
        var(--es-auth-primary);

    font-size:
        clamp(27px, 4vw, 34px);

    line-height: 1.15;
}

.esubiz-auth-heading p {
    max-width: 520px;
    margin: auto;

    color:
        var(--es-auth-muted);

    font-size: 14px;
    line-height: 1.6;
}

.esubiz-auth-grid {
    display: grid;

    grid-template-columns:
        repeat(
            2,
            minmax(0, 1fr)
        );

    gap: 16px;
}

.esubiz-auth-field {
    min-width: 0;
}

.esubiz-auth-field-full {
    grid-column: 1 / -1;
}

.esubiz-auth-label {
    display: block;

    margin-bottom: 7px;

    font-size: 13px;
    font-weight: 700;
}

.esubiz-auth-control,
.esubiz-auth-select {
    display: block;

    width: 100%;
    min-height: 52px;

    padding:
        0 14px;

    border:
        1px solid
        rgba(15,23,42,.12);

    border-radius: 11px;

    background: #fff;

    color:
        var(--es-auth-text);

    outline: none;

    font: inherit;
}

.esubiz-auth-control:focus,
.esubiz-auth-select:focus {
    border-color:
        var(--es-auth-primary);

    box-shadow:
        0 0 0 4px
        rgba(11,31,58,.07);
}

.esubiz-auth-phone {
    display: grid;

    grid-template-columns:
        108px
        minmax(0,1fr);

    gap: 9px;
}

.esubiz-auth-button {
    display: flex;
    align-items: center;
    justify-content: center;

    width: 100%;
    min-height: 52px;

    margin-top: 24px;

    border: 0;
    border-radius: 11px;

    background:
        var(--es-auth-primary);

    color: #fff;

    font: inherit;
    font-weight: 800;

    cursor: pointer;
}

.esubiz-auth-provider-grid {
    display: grid;
    gap: 10px;

    margin-bottom: 20px;
}

.esubiz-auth-provider {
    display: flex;
    align-items: center;
    justify-content: center;

    min-height: 48px;

    border:
        1px solid
        rgba(15,23,42,.11);

    border-radius: 11px;

    background: #fff;

    color:
        var(--es-auth-text);

    font-size: 14px;
    font-weight: 700;

    text-decoration: none;
}

.esubiz-auth-divider {
    display: flex;
    align-items: center;
    gap: 12px;

    margin: 19px 0 22px;

    color:
        var(--es-auth-muted);

    font-size: 11px;
    font-weight: 700;

    text-transform: uppercase;
}

.esubiz-auth-divider::before,
.esubiz-auth-divider::after {
    content: "";

    flex: 1;

    height: 1px;

    background:
        rgba(15,23,42,.10);
}

.esubiz-auth-footer {
    margin-top: 20px;

    text-align: center;

    color:
        var(--es-auth-muted);

    font-size: 13px;
}

.esubiz-auth-footer a {
    color:
        var(--es-auth-primary);

    font-weight: 800;

    text-decoration: none;
}

.esubiz-auth-help {
    margin-top: 7px;

    color:
        var(--es-auth-muted);

    font-size: 12px;
    line-height: 1.5;
}

.esubiz-auth-error {
    margin-top: 6px;

    color: #b42318;

    font-size: 12px;
}

.esubiz-auth-error-box {
    margin-bottom: 20px;
    padding: 13px 15px;

    border:
        1px solid
        rgba(180,35,24,.16);

    border-radius: 11px;

    background:
        rgba(180,35,24,.05);

    color: #b42318;

    font-size: 13px;
}

@media (max-width: 620px) {
    .esubiz-auth-page {
        align-items: flex-start;

        padding:
            20px 12px;
    }

    .esubiz-auth-card-body {
        padding:
            26px 18px;
    }

    .esubiz-auth-grid {
        grid-template-columns:
            1fr;

        gap: 15px;
    }

    .esubiz-auth-field-full {
        grid-column: auto;
    }

    .esubiz-auth-phone {
        grid-template-columns:
            102px
            minmax(0,1fr);
    }
}
</style>
