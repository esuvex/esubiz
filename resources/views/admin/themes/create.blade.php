@extends('admin.layouts.app')

@section('content')

<style>
    .esubiz-theme-edit-page {
        max-width: 1180px;
        margin: 0 auto;
        padding: 30px 28px 46px;
    }

    .esubiz-theme-edit-back {
        display: inline-block;
        margin-bottom: 18px;
        color: #667085;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
    }

    .esubiz-theme-edit-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 24px;
    }

    .esubiz-theme-edit-head h1 {
        margin: 0;
        color: #101828;
        font-size: 27px;
        font-weight: 800;
    }

    .esubiz-theme-edit-head p {
        margin: 7px 0 0;
        color: #667085;
        font-size: 13px;
        line-height: 1.6;
    }

    .esubiz-theme-version {
        display: inline-flex;
        align-items: center;
        padding: 7px 11px;
        border-radius: 999px;
        background: #eff6ff;
        color: #1d4ed8;
        font-size: 11px;
        font-weight: 800;
        white-space: nowrap;
    }

    .esubiz-theme-edit-grid {
        display: grid;
        gap: 18px;
    }

    .esubiz-theme-section {
        border: 1px solid #e4e9f0;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 8px 26px rgba(15,23,42,.035);
    }

    .esubiz-theme-section-head {
        padding: 20px 22px;
        border-bottom: 1px solid #eef1f5;
    }

    .esubiz-theme-section-head h3 {
        margin: 0;
        color: #101828;
        font-size: 15px;
        font-weight: 800;
    }

    .esubiz-theme-section-head p {
        margin: 5px 0 0;
        color: #667085;
        font-size: 11.5px;
        line-height: 1.5;
    }

    .esubiz-theme-section-body {
        padding: 22px;
    }

    .esubiz-theme-fields {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .esubiz-theme-field-full {
        grid-column: 1 / -1;
    }

    .esubiz-theme-field label {
        display: block;
        margin-bottom: 8px;
        color: #344054;
        font-size: 12px;
        font-weight: 800;
    }

    .esubiz-theme-field input,
    .esubiz-theme-field select,
    .esubiz-theme-field textarea {
        width: 100%;
        border: 1px solid #d7dde7;
        border-radius: 10px;
        background: #fff;
        color: #1d2939;
        font-size: 12px;
        outline: none;
    }

    .esubiz-theme-field input,
    .esubiz-theme-field select {
        min-height: 44px;
        padding: 9px 12px;
    }

    .esubiz-theme-field textarea {
        min-height: 110px;
        padding: 11px 12px;
        resize: vertical;
    }

    .esubiz-theme-field input:focus,
    .esubiz-theme-field select:focus,
    .esubiz-theme-field textarea:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37,99,235,.08);
    }

    .esubiz-theme-help {
        margin-top: 7px;
        color: #7c8798;
        font-size: 11px;
        line-height: 1.5;
    }

    .esubiz-theme-check-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .esubiz-theme-check {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 14px;
        border: 1px solid #e3e8ef;
        border-radius: 11px;
        background: #fbfcfe;
    }

    .esubiz-theme-check input {
        margin-top: 2px;
    }

    .esubiz-theme-check strong {
        display: block;
        color: #344054;
        font-size: 12px;
        font-weight: 800;
    }

    .esubiz-theme-check span {
        display: block;
        margin-top: 3px;
        color: #667085;
        font-size: 10.5px;
        line-height: 1.45;
    }

    .esubiz-theme-type-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
    }

    .esubiz-theme-type {
        display: flex;
        align-items: center;
        gap: 9px;
        padding: 12px;
        border: 1px solid #e3e8ef;
        border-radius: 10px;
        background: #fff;
        color: #344054;
        font-size: 11.5px;
        font-weight: 700;
    }

    .esubiz-theme-edit-actions {
        position: sticky;
        bottom: 0;
        z-index: 20;
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 20px;
        padding: 14px 0 4px;
        background:
            linear-gradient(
                to top,
                #f8fafc 75%,
                rgba(248,250,252,0)
            );
    }

    .esubiz-theme-save-btn {
        min-height: 44px;
        padding: 0 20px;
        border: 0;
        border-radius: 10px;
        background: #2563eb;
        color: #fff;
        font-size: 12px;
        font-weight: 800;
        cursor: pointer;
        box-shadow: 0 8px 18px rgba(37,99,235,.18);
    }

    @media(max-width: 800px) {
        .esubiz-theme-fields,
        .esubiz-theme-check-grid,
        .esubiz-theme-type-grid {
            grid-template-columns: 1fr;
        }

        .esubiz-theme-edit-head {
            flex-direction: column;
        }
    }

.esu-category-field{position:relative}
.esu-category-trigger{
    width:100%;min-height:46px;padding:10px 13px;
    border:1px solid #d8dee8;border-radius:10px;background:#fff;
    display:flex;align-items:center;justify-content:space-between;
    gap:12px;text-align:left;cursor:pointer
}
.esu-category-trigger:focus{
    outline:none;border-color:#2563eb;
    box-shadow:0 0 0 3px rgba(37,99,235,.10)
}
.esu-category-dropdown{
    position:absolute;z-index:50;left:0;right:0;top:calc(100% - 18px);
    background:#fff;border:1px solid #d8dee8;border-radius:12px;
    box-shadow:0 16px 35px rgba(15,23,42,.14);
    overflow:hidden
}
.esu-category-search-wrap{padding:10px;border-bottom:1px solid #edf0f5}
.esu-category-search{
    width:100%;min-height:40px;padding:8px 11px;
    border:1px solid #d8dee8;border-radius:8px
}
.esu-category-options{max-height:240px;overflow:auto;padding:6px}
.esu-category-option{
    display:flex;align-items:center;gap:9px;padding:9px 10px;
    border-radius:8px;cursor:pointer
}
.esu-category-option:hover{background:#f6f8fb}
.esu-category-option input{width:16px;height:16px}
.esu-category-empty{padding:14px;color:#64748b;font-size:13px}


/* ESUBIZ_STANDARD_BINARY_TOGGLE_V273 */
.esu-toggle-control{
    display:flex;
    align-items:center;
    gap:12px;
    cursor:pointer;
}
.esu-toggle-control > input[type="checkbox"]{
    position:absolute;
    opacity:0;
    pointer-events:none;
}
.esu-toggle-track{
    position:relative;
    flex:0 0 42px;
    width:42px;
    height:24px;
    border-radius:999px;
    background:#cbd5e1;
    transition:background .18s ease;
}
.esu-toggle-knob{
    position:absolute;
    width:18px;
    height:18px;
    left:3px;
    top:3px;
    border-radius:50%;
    background:#fff;
    box-shadow:0 1px 3px rgba(15,23,42,.25);
    transition:transform .18s ease;
}
.esu-toggle-control > input[type="checkbox"]:checked + .esu-toggle-track{
    background:#2563eb;
}
.esu-toggle-control > input[type="checkbox"]:checked + .esu-toggle-track .esu-toggle-knob{
    transform:translateX(18px);
}
.esu-toggle-control > input[type="checkbox"]:focus-visible + .esu-toggle-track{
    box-shadow:0 0 0 3px rgba(37,99,235,.18);
}

</style>
<style>
    .esubiz-theme-create-page {
        max-width: 1080px;
        margin: 0 auto;
        padding: 30px 28px 45px;
    }

    .esubiz-theme-create-back {
        display: inline-block;
        margin-bottom: 18px;
        color: #667085;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
    }

    .esubiz-theme-create-head {
        margin-bottom: 24px;
    }

    .esubiz-theme-create-head h1 {
        margin: 0;
        color: #101828;
        font-size: 27px;
        font-weight: 800;
    }

    .esubiz-theme-create-head p {
        margin: 7px 0 0;
        color: #667085;
        font-size: 13px;
        line-height: 1.6;
    }

    .esubiz-theme-create-card {
        overflow: hidden;
        border: 1px solid #e4e9f0;
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 14px 38px rgba(15,23,42,.055);
    }

    .esubiz-theme-create-card-head {
        padding: 24px 27px;
        border-bottom: 1px solid #eef1f5;
    }

    .esubiz-theme-create-card-head strong {
        display: block;
        color: #101828;
        font-size: 17px;
        font-weight: 800;
    }

    .esubiz-theme-create-card-head span {
        display: block;
        margin-top: 5px;
        color: #667085;
        font-size: 12px;
    }

    .esubiz-theme-create-body {
        padding: 27px;
    }

    .esubiz-theme-source-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 24px;
    }

    .esubiz-theme-source {
        position: relative;
        margin: 0;
        cursor: pointer;
    }

    .esubiz-theme-source input {
        position: absolute;
        opacity: 0;
    }

    .esubiz-theme-source-box {
        display: block;
        padding: 17px;
        border: 1px solid #dfe5ee;
        border-radius: 12px;
        background: #fff;
    }

    .esubiz-theme-source input:checked + .esubiz-theme-source-box {
        border-color: #2563eb;
        background: #f7faff;
        box-shadow: 0 0 0 3px rgba(37,99,235,.08);
    }

    .esubiz-theme-source-box strong {
        display: block;
        color: #1d2939;
        font-size: 13px;
        font-weight: 800;
    }

    .esubiz-theme-source-box span {
        display: block;
        margin-top: 4px;
        color: #667085;
        font-size: 11.5px;
        line-height: 1.5;
    }

    .esubiz-theme-field {
        margin-bottom: 20px;
    }

    .esubiz-theme-field label {
        display: block;
        margin-bottom: 8px;
        color: #344054;
        font-size: 12px;
        font-weight: 800;
    }

    .esubiz-theme-field select,
    .esubiz-theme-field input[type="file"] {
        width: 100%;
        min-height: 45px;
        padding: 10px 12px;
        border: 1px solid #d5dce6;
        border-radius: 10px;
        background: #fff;
        color: #344054;
        font-size: 12px;
    }

    .esubiz-theme-help {
        margin-top: 7px;
        color: #7c8798;
        font-size: 11px;
    }

    .esubiz-theme-create-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding-top: 6px;
    }

    .esubiz-theme-btn-primary {
        min-height: 42px;
        padding: 0 18px;
        border: 0;
        border-radius: 9px;
        background: #2563eb;
        color: #fff;
        font-size: 12px;
        font-weight: 800;
        cursor: pointer;
    }

    @media(max-width: 700px) {
        .esubiz-theme-source-grid {
            grid-template-columns: 1fr;
        }
    }

.esubiz-theme-source-toggle-wrap{
    margin-bottom:24px;
}

.esubiz-theme-toggle-row{
    display:flex;
    align-items:center;
    justify-content:flex-start;
    gap:10px;
    margin-bottom:22px;
}

.esubiz-theme-toggle-label{
    padding:0;
    border:0;
    background:transparent;
    color:#64748b;
    font-size:12px;
    font-weight:700;
    cursor:pointer;
}

.esubiz-theme-toggle{
    position:relative;
    width:46px;
    height:25px;
    flex:none;
    padding:0;
    border:0;
    border-radius:999px;
    background:#94a3b8;
    cursor:pointer;
    transition:background .2s ease;
}

.esubiz-theme-toggle span{
    position:absolute;
    top:3px;
    left:3px;
    width:19px;
    height:19px;
    border-radius:50%;
    background:#fff;
    box-shadow:0 1px 4px rgba(15,23,42,.25);
    transition:transform .2s ease;
}

.esubiz-theme-source-toggle-wrap.is-upload .esubiz-theme-toggle{
    background:#2563eb;
}

.esubiz-theme-source-toggle-wrap.is-upload .esubiz-theme-toggle span{
    transform:translateX(21px);
}

.esubiz-theme-source-toggle-wrap:not(.is-upload) #theme-folder-label{
    color:#334155;
}

.esubiz-theme-source-toggle-wrap.is-upload #theme-upload-label{
    color:#2563eb;
}

</style>

<div class="esubiz-theme-edit-page">

    <a href="{{ route('admin.themes.index') }}"
       class="esubiz-theme-edit-back">
        ← Back to Themes
    </a>

    <div class="esubiz-theme-edit-head">
        <div>
            <h1>Add Theme</h1>
            <p>
                Add the Theme package and configure deployment,
                compatibility, pricing and Marketplace settings in one place.
            </p>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger mb-4">
            <strong>Please correct the following:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form
        method="POST"
        action="{{ route('admin.themes.packages.store') }}"
        enctype="multipart/form-data"
    >
        @csrf

        <div class="esubiz-theme-edit-grid">

            <section class="esubiz-theme-section">
                <div class="esubiz-theme-section-head">
                    <h3>Theme Package</h3>
                    <p>
                        Search an existing protected Theme package
                        or upload a new Esubiz Theme ZIP.
                    </p>
                </div>

                <div class="esubiz-theme-section-body">
<div class="esubiz-theme-source-toggle-wrap">

                    <input
                        type="hidden"
                        name="package_source"
                        id="theme-package-source"
                        value="{{ old('package_source', 'storage') }}"
                    >

                    <div class="esubiz-theme-toggle-row">

                        <button
                            type="button"
                            class="esubiz-theme-toggle-label"
                            id="theme-folder-label"
                        >
                            Search Folder
                        </button>

                        <button
                            type="button"
                            class="esubiz-theme-toggle"
                            id="theme-source-toggle"
                            aria-label="Switch Theme package source"
                        >
                            <span></span>
                        </button>

                        <button
                            type="button"
                            class="esubiz-theme-toggle-label"
                            id="theme-upload-label"
                        >
                            Upload ZIP
                        </button>

                    </div>


                    <div
                        class="esubiz-theme-field"
                        id="theme-storage-field"
                    >

                        <label>
                            Theme Package in Storage
                        </label>

                        <select
                            name="storage_package"
                            id="theme-storage-package"
                        >

                            <option value="">
                                Search/select Theme Package
                            </option>

                            @foreach(
                                ($storageThemePackages ?? collect())
                                as $package
                            )

                                <option
                                    value="{{ $package['filename'] }}"
                                    {{ old('storage_package') === $package['filename'] ? 'selected' : '' }}
                                >

                                    {{ $package['name'] }}

                                    @if(!empty($package['version']))
                                        v{{ $package['version'] }}
                                    @endif

                                    — {{ $package['filename'] }}

                                </option>

                            @endforeach

                        </select>

                    </div>


                    <div
                        class="esubiz-theme-field"
                        id="theme-upload-field"
                        style="display:none;"
                    >

                        <label>
                            Upload Theme ZIP
                        </label>

                        <input
                            type="file"
                            name="theme_package"
                            id="theme-upload-package"
                            accept=".zip,application/zip"
                            disabled
                        >

                        <div class="esubiz-theme-help">
                            theme.json must exist at the ZIP root.
                        </div>

                    </div>

                </div>
                </div>
            </section>




            <section class="esubiz-theme-section">

                <div class="esubiz-theme-section-head">
                    <h3>Preview Photo</h3>
                    <p>
                        Upload the Marketplace preview independently from the Theme package.
                        Images are automatically optimized to 1600 × 1000 (8:5).
                    </p>
                </div>

                <div class="esubiz-theme-section-body">

                    <div class="esubiz-theme-field esubiz-theme-field-full">
                        <label>Theme Preview Photo</label>

                        <input
                            type="file"
                            name="preview_image"
                            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                            data-theme-preview-input
                        >

                        @error('preview_image')
                            <div class="mt-2 text-sm text-red-600">{{ $message }}</div>
                        @enderror

                        <div
                            data-theme-preview-box
                            style="display:none;margin-top:16px;max-width:560px;"
                        >
                            <img
                                data-theme-preview-image
                                alt="Theme preview"
                                style="display:block;width:100%;aspect-ratio:8/5;object-fit:cover;border-radius:16px;border:1px solid #e2e8f0;"
                            >
                        </div>
                    </div>

                </div>

            </section>


            <section class="esubiz-theme-section">

                <div class="esubiz-theme-section-head">
                    <h3>SaaS</h3>
                    <p>
                        Configure hosted Esubiz availability,
                        price and sales duration.
                    </p>
                </div>

                <div class="esubiz-theme-section-body">

                    <div class="esubiz-theme-check-grid mb-4">

                        <label class="esu-toggle-control esubiz-theme-check">

                            <input
                                type="checkbox"
                                name="saas_available"
                                value="1"
                                @checked((bool) old('saas_available', false))
                            >
                            <span class="esu-toggle-track" aria-hidden="true">
                                <span class="esu-toggle-knob"></span>
                            </span>

                            <span>
                                <strong>
                                    Available for SaaS websites
                                </strong>

                                <span>
                                    Allow this Theme to be sold and used
                                    on Esubiz-hosted websites.
                                </span>
                            </span>

                        </label>

                    </div>


                    <div class="esubiz-theme-fields">

                        <div class="esubiz-theme-field">
                            <label>SaaS Price</label>

                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                name="saas_price"
                                value="{{ old('saas_price', '') }}"
                            >
                        </div>


                        <div class="esubiz-theme-field">
                            <label>Currency</label>

                            <input type="hidden" name="saas_currency" value="{{ strtoupper($centralCurrencyCode) }}">
                            <div class="esu-field-static">
                                {{ strtoupper($centralCurrencyCode) }}
                            </div>
                        </div>


                        <div class="esubiz-theme-field">
                            <label>SaaS Billing Period</label>

                            <input
                                type="number"
                                min="1"
                                step="1"
                                name="saas_billing_period"
                                value="{{ old('saas_billing_period', 1) }}"
                            >
                        </div>


                        <div class="esubiz-theme-field">
                            <label>SaaS Billing Interval</label>

                            @php
                                $saasInterval =
                                    old(
                                        'saas_billing_interval',
                                        'year'
                                    );
                            @endphp

                            <select name="saas_billing_interval">

                                <option
                                    value="day"
                                    @selected($saasInterval === 'day')
                                >
                                    Day
                                </option>

                                <option
                                    value="week"
                                    @selected($saasInterval === 'week')
                                >
                                    Week
                                </option>

                                <option
                                    value="month"
                                    @selected($saasInterval === 'month')
                                >
                                    Month
                                </option>

                                <option
                                    value="year"
                                    @selected($saasInterval === 'year')
                                >
                                    Year
                                </option>

                            </select>
                        </div>

                    </div>

                </div>

            </section>


            <section class="esubiz-theme-section">

                <div class="esubiz-theme-section-head">
                    <h3>Off-server</h3>
                    <p>
                        Configure downloadable/off-server Theme sales.
                    </p>
                </div>

                <div class="esubiz-theme-section-body">

                    <div class="esubiz-theme-check-grid mb-4">

                        <label class="esu-toggle-control esubiz-theme-check">

                            <input
                                type="checkbox"
                                name="off_server_available"
                                value="1"
                                @checked((bool) old('off_server_available', false))
                            >
                            <span class="esu-toggle-track" aria-hidden="true">
                                <span class="esu-toggle-knob"></span>
                            </span>

                            <span>
                                <strong>
                                    Available off-server
                                </strong>

                                <span>
                                    Allow this Theme to be sold for
                                    external Core installations.
                                </span>
                            </span>

                        </label>

                    </div>


                    <div class="esubiz-theme-fields">

                        <div class="esubiz-theme-field">
                            <label>Off-server Price</label>

                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                name="off_server_price"
                                value="{{ old('off_server_price', '') }}"
                            >
                        </div>


                        <div class="esubiz-theme-field">
                            <label>Currency</label>

                            <input type="hidden" name="off_server_currency" value="{{ strtoupper($centralCurrencyCode) }}">
                            <div class="esu-field-static">
                                {{ strtoupper($centralCurrencyCode) }}
                            </div>
                        </div>

                    </div>

                </div>

            </section>


            <section class="esubiz-theme-section">

                <div class="esubiz-theme-section-head">
                    <h3>Marketplace</h3>
                    <p>
                        Control Marketplace visibility, release state
                        and commercial presentation.
                    </p>
                </div>

                <div class="esubiz-theme-section-body">

                    <div class="esubiz-theme-check-grid">

                        {{-- ESUBIZ_THEME_STATUS_CONTROL_V1 --}}
                        <label class="esu-toggle-control esubiz-theme-check">

                            <input
                                type="hidden"
                                name="is_active"
                                value="0"
                            >

                            <input
                                type="checkbox"
                                name="is_active"
                                value="1"
                                @checked((bool) old('is_active', true))
                            >
                            <span class="esu-toggle-track" aria-hidden="true">
                                <span class="esu-toggle-knob"></span>
                            </span>

                            <span>
                                <strong>Theme Active</strong>

                                <span>
                                    Keep this Theme active and available
                                    for its configured deployment channels.
                                </span>
                            </span>

                        </label>


                        <label class="esu-toggle-control esubiz-theme-check">

                            <input
                                type="checkbox"
                                name="marketplace_enabled"
                                value="1"
                                @checked((bool) old('marketplace_enabled', false))
                            >
                            <span class="esu-toggle-track" aria-hidden="true">
                                <span class="esu-toggle-knob"></span>
                            </span>

                            <span>
                                <strong>Marketplace Enabled</strong>

                                <span>
                                    Allow this Theme to appear as
                                    a Marketplace product.
                                </span>
                            </span>

                        </label>


                        <label class="esu-toggle-control esubiz-theme-check">

                            <input
                                type="checkbox"
                                name="marketplace_featured"
                                value="1"
                                @checked((bool) old('marketplace_featured', false))
                            >
                            <span class="esu-toggle-track" aria-hidden="true">
                                <span class="esu-toggle-knob"></span>
                            </span>

                            <span>
                                <strong>Featured Theme</strong>

                                <span>
                                    Mark this Theme as featured
                                    in Marketplace presentation.
                                </span>
                            </span>

                        </label>


                        

                    </div>


                    <div class="esubiz-theme-fields mt-4">

                        <div class="esu-category-field" data-esu-category-select>
                            <label>Marketplace Categories</label>

                            @php
                                $esuSelectedCategories = collect(old('marketplace_category_ids', []))
                                    ->map(fn ($id) => (int) $id)
                                    ->all();
                            @endphp

                            <button
                                type="button"
                                class="esu-category-trigger"
                                data-esu-category-trigger
                                aria-expanded="false"
                            >
                                <span data-esu-category-summary>
                                    Select categories
                                </span>
                                <span aria-hidden="true">⌄</span>
                            </button>

                            <div
                                class="esu-category-dropdown"
                                data-esu-category-dropdown
                                hidden
                            >
                                <div class="esu-category-search-wrap">
                                    <input
                                        type="search"
                                        class="esu-category-search"
                                        placeholder="Search categories..."
                                        autocomplete="off"
                                        data-esu-category-search
                                    >
                                </div>

                                <div class="esu-category-options">
                                    @forelse($marketplaceCategories as $category)
                                        <label
                                            class="esu-category-option"
                                            data-esu-category-option
                                            data-category-name="{{ strtolower($category->name) }}"
                                        >
                                            <input
                                                type="checkbox"
                                                name="marketplace_category_ids[]"
                                                value="{{ $category->id }}"
                                                @checked(
                                                    in_array(
                                                        (int) $category->id,
                                                        $esuSelectedCategories,
                                                        true
                                                    )
                                                )
                                            >
                                            <span>{{ $category->name }}</span>
                                        </label>
                                    @empty
                                        <div class="esu-category-empty">
                                            No active Marketplace Categories are configured for Themes.
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                            <small>
                                Choose one or more categories configured in Marketplace Settings.
                            </small>
                        </div>


                        <div class="esubiz-theme-field esubiz-theme-field-full">
                            <label>Release Notes</label>

                            <textarea name="release_notes">{{ old('release_notes', '') }}</textarea>
                        </div>

                    </div>

                </div>

            </section>


            <section class="esubiz-theme-section">

                <div class="esubiz-theme-section-head">
                    <h3>Wizard Visibility</h3>

                    <p>
                        Theme must also be assigned to the selected
                        Website Type before it appears in a wizard.
                    </p>
                </div>

                <div class="esubiz-theme-section-body">

                    <div class="esubiz-theme-check-grid">

                        <label class="esu-toggle-control esubiz-theme-check">

                            <input
                                type="hidden"
                                name="show_in_user_wizard"
                                value="0"
                            >

                            <input
                                type="checkbox"
                                name="show_in_user_wizard"
                                value="1"
                                @checked((bool) old('show_in_user_wizard', true))
                            >
                            <span class="esu-toggle-track" aria-hidden="true">
                                <span class="esu-toggle-knob"></span>
                            </span>

                            <span>
                                <strong>
                                    Show in User Wizard
                                </strong>

                                <span>
                                    Available to normal users when
                                    assigned to their Website Type.
                                </span>
                            </span>

                        </label>


                        <label class="esu-toggle-control esubiz-theme-check">

                            <input
                                type="hidden"
                                name="show_in_developer_wizard"
                                value="0"
                            >

                            <input
                                type="checkbox"
                                name="show_in_developer_wizard"
                                value="1"
                                @checked((bool) old('show_in_developer_wizard', true))
                            >
                            <span class="esu-toggle-track" aria-hidden="true">
                                <span class="esu-toggle-knob"></span>
                            </span>

                            <span>
                                <strong>
                                    Show in Developer Wizard
                                </strong>

                                <span>
                                    Available to developers when
                                    assigned to their Website Type.
                                </span>
                            </span>

                        </label>

                    </div>

                </div>

            </section>


            <section class="esubiz-theme-section">

                <div class="esubiz-theme-section-head">
                    <h3>Website Types</h3>

                    <p>
                        Assign this Theme to compatible Website Types.
                        Add-ons are not assigned here.
                    </p>
                </div>

                <div class="esubiz-theme-section-body">

                    <div class="esu-category-field" data-esu-multi-select>
                            <label>Website Types</label>

                            @php
                                $esuSelectedWebsiteTypes = collect(
                                    old('website_type_ids', [])
                                )
                                    ->map(fn ($id) => (int) $id)
                                    ->all();
                            @endphp

                            <button
                                type="button"
                                class="esu-category-trigger"
                                data-esu-multi-trigger
                                aria-expanded="false"
                            >
                                <span data-esu-multi-summary>
                                    Select website types
                                </span>
                                <span aria-hidden="true">⌄</span>
                            </button>

                            <div
                                class="esu-category-dropdown"
                                data-esu-multi-dropdown
                                hidden
                            >
                                <div class="esu-category-search-wrap">
                                    <input
                                        type="search"
                                        class="esu-category-search"
                                        placeholder="Search website types..."
                                        autocomplete="off"
                                        data-esu-multi-search
                                    >
                                </div>

                                <div class="esu-category-options">
                                    @forelse($websiteTypes as $websiteType)
                                        <label
                                            class="esu-category-option"
                                            data-esu-multi-option
                                            data-option-name="{{ strtolower($websiteType->name) }}"
                                        >
                                            <input
                                                type="checkbox"
                                                name="website_type_ids[]"
                                                value="{{ $websiteType->id }}"
                                                @checked(
                                                    in_array(
                                                        (int) $websiteType->id,
                                                        $esuSelectedWebsiteTypes,
                                                        true
                                                    )
                                                )
                                            >
                                            <span>{{ $websiteType->name }}</span>
                                        </label>
                                    @empty
                                        <div class="esu-category-empty">
                                            No active Website Types are available.
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                            <small>
                                Choose one or more compatible Website Types.
                            </small>
                        </div>

                </div>

            </section>


        </div>

        <div class="esubiz-theme-edit-actions">
            <a
                href="{{ route('admin.themes.index') }}"
                class="btn btn-light"
            >
                Cancel
            </a>

            <button
                type="submit"
                class="esubiz-theme-save-btn"
            >
                Add Theme
            </button>
        </div>

    </form>

</div>

<script>
document.addEventListener(
    'DOMContentLoaded',
    function () {

        const storage =
            document.getElementById(
                'theme-source-storage'
            );

        const upload =
            document.getElementById(
                'theme-source-upload'
            );

        const storageField =
            document.getElementById(
                'theme-storage-field'
            );

        const uploadField =
            document.getElementById(
                'theme-upload-field'
            );

        const storageInput =
            document.getElementById(
                'theme-storage-package'
            );

        const uploadInput =
            document.getElementById(
                'theme-upload-package'
            );


        function sync() {

            const isUpload =
                upload.checked;

            storageField.style.display =
                isUpload
                    ? 'none'
                    : 'block';

            uploadField.style.display =
                isUpload
                    ? 'block'
                    : 'none';

            storageInput.disabled =
                isUpload;

            storageInput.required =
                !isUpload;

            uploadInput.disabled =
                !isUpload;

            uploadInput.required =
                isUpload;
        }


        storage.addEventListener(
            'change',
            sync
        );

        upload.addEventListener(
            'change',
            sync
        );

        sync();
    }
);

(function () {
    const root = document.querySelector('.esubiz-theme-source-toggle-wrap');
    if (!root) return;

    const source = document.getElementById('theme-package-source');
    const toggle = document.getElementById('theme-source-toggle');
    const folderLabel = document.getElementById('theme-folder-label');
    const uploadLabel = document.getElementById('theme-upload-label');
    const storageField = document.getElementById('theme-storage-field');
    const uploadField = document.getElementById('theme-upload-field');
    const storageInput = document.getElementById('theme-storage-package');
    const uploadInput = document.getElementById('theme-upload-package');

    function setSource(mode) {
        mode = mode === 'upload' ? 'upload' : 'storage';

        source.value = mode;
        root.classList.toggle('is-upload', mode === 'upload');

        storageField.style.display = mode === 'storage' ? '' : 'none';
        uploadField.style.display = mode === 'upload' ? '' : 'none';

        storageInput.disabled = mode !== 'storage';
        uploadInput.disabled = mode !== 'upload';

        storageInput.required = mode === 'storage';
        uploadInput.required = mode === 'upload';
    }

    toggle.addEventListener('click', function () {
        setSource(source.value === 'upload' ? 'storage' : 'upload');
    });

    folderLabel.addEventListener('click', function () {
        setSource('storage');
    });

    uploadLabel.addEventListener('click', function () {
        setSource('upload');
    });

    setSource(source.value);
})();

</script>


<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-esu-category-select]').forEach(function (root) {
        const trigger = root.querySelector('[data-esu-category-trigger]');
        const dropdown = root.querySelector('[data-esu-category-dropdown]');
        const search = root.querySelector('[data-esu-category-search]');
        const summary = root.querySelector('[data-esu-category-summary]');
        const checks = Array.from(
            root.querySelectorAll('input[name="marketplace_category_ids[]"]')
        );

        function refreshSummary() {
            const selected = checks
                .filter(input => input.checked)
                .map(input => input.closest('label').innerText.trim());

            summary.textContent = selected.length
                ? selected.join(', ')
                : 'Select categories';
        }

        trigger.addEventListener('click', function () {
            const opening = dropdown.hidden;
            dropdown.hidden = !opening;
            trigger.setAttribute('aria-expanded', opening ? 'true' : 'false');

            if (opening && search) {
                setTimeout(() => search.focus(), 0);
            }
        });

        checks.forEach(input => {
            input.addEventListener('change', refreshSummary);
        });

        if (search) {
            search.addEventListener('input', function () {
                const term = this.value.trim().toLowerCase();

                root.querySelectorAll('[data-esu-category-option]').forEach(function (option) {
                    option.hidden = !option.dataset.categoryName.includes(term);
                });
            });
        }

        document.addEventListener('click', function (event) {
            if (!root.contains(event.target)) {
                dropdown.hidden = true;
                trigger.setAttribute('aria-expanded', 'false');
            }
        });

        refreshSummary();
    });
});
</script>


<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-esu-multi-select]').forEach(function (root) {
        const trigger = root.querySelector('[data-esu-multi-trigger]');
        const dropdown = root.querySelector('[data-esu-multi-dropdown]');
        const search = root.querySelector('[data-esu-multi-search]');
        const summary = root.querySelector('[data-esu-multi-summary]');
        const checks = Array.from(
            root.querySelectorAll('input[name="website_type_ids[]"]')
        );

        function refreshSummary() {
            const selected = checks
                .filter(input => input.checked)
                .map(input => input.closest('label').innerText.trim());

            summary.textContent = selected.length
                ? selected.join(', ')
                : 'Select website types';
        }

        trigger.addEventListener('click', function () {
            const opening = dropdown.hidden;
            dropdown.hidden = !opening;
            trigger.setAttribute(
                'aria-expanded',
                opening ? 'true' : 'false'
            );

            if (opening && search) {
                setTimeout(() => search.focus(), 0);
            }
        });

        checks.forEach(function (input) {
            input.addEventListener('change', refreshSummary);
        });

        if (search) {
            search.addEventListener('input', function () {
                const term = this.value.trim().toLowerCase();

                root.querySelectorAll(
                    '[data-esu-multi-option]'
                ).forEach(function (option) {
                    option.hidden =
                        !option.dataset.optionName.includes(term);
                });
            });
        }

        document.addEventListener('click', function (event) {
            if (!root.contains(event.target)) {
                dropdown.hidden = true;
                trigger.setAttribute('aria-expanded', 'false');
            }
        });

        refreshSummary();
    });
});
</script>

@endsection

<script>
document.addEventListener('change', function (event) {
    const input = event.target.closest('[data-theme-preview-input]');
    if (!input || !input.files || !input.files[0]) return;

    const box = document.querySelector('[data-theme-preview-box]');
    const image = document.querySelector('[data-theme-preview-image]');
    if (!box || !image) return;

    const reader = new FileReader();
    reader.onload = function (e) {
        image.src = e.target.result;
        box.style.display = 'block';
    };
    reader.readAsDataURL(input.files[0]);
});
</script>
