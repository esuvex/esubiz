
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
</style>


<div class="esubiz-theme-edit-page">

    <a
        href="{{ route('admin.themes.index') }}"
        class="esubiz-theme-edit-back"
    >
        ← Back to Themes
    </a>


    <div class="esubiz-theme-edit-head">

        <div>
            <h1>
                Edit {{ $theme->name }}
            </h1>

            <p>
                Configure pricing, deployment availability,
                Marketplace publishing and wizard visibility.
            </p>
        </div>

        <span class="esubiz-theme-version">
            v{{ $theme->version }}
        </span>

    </div>


    @if(session('success'))
        <div class="alert alert-success mb-4">
            {{ session('success') }}
        </div>
    @endif


    <form
        method="POST"
        action="{{ route('admin.themes.update', $theme->id) }}"
    >
        @csrf
        @method('PUT')


        <div class="esubiz-theme-edit-grid">


            <section class="esubiz-theme-section">

                <div class="esubiz-theme-section-head">
                    <h3>Theme Details</h3>
                    <p>
                        Core package identity and publisher information.
                    </p>
                </div>

                <div class="esubiz-theme-section-body">

                    <div class="esubiz-theme-fields">

                        <div class="esubiz-theme-field esubiz-theme-field-full">
                            <label>Theme Name</label>

                            <input
                                type="text"
                                name="name"
                                value="{{ old('name', $theme->name) }}"
                                required
                            >
                        </div>


                        <div class="esubiz-theme-field">
                            <label>Publisher</label>

                            <input
                                type="text"
                                name="publisher_name"
                                value="{{ old('publisher_name', $theme->publisher_name) }}"
                                required
                            >
                        </div>


                        <div class="esubiz-theme-field">
                            <label>Publisher Type</label>

                            @php
                                $publisherType =
                                    old(
                                        'publisher_type',
                                        $theme->publisher_type
                                    );
                            @endphp

                            <select name="publisher_type">

                                <option
                                    value="platform"
                                    @selected($publisherType === 'platform')
                                >
                                    Platform
                                </option>

                                <option
                                    value="company"
                                    @selected($publisherType === 'company')
                                >
                                    Company
                                </option>

                                <option
                                    value="developer"
                                    @selected($publisherType === 'developer')
                                >
                                    Developer
                                </option>

                            </select>
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

                        <label class="esubiz-theme-check">

                            <input
                                type="checkbox"
                                name="saas_available"
                                value="1"
                                @checked($theme->saas_available)
                            >

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
                                value="{{ old('saas_price', $theme->saas_price) }}"
                            >
                        </div>


                        <div class="esubiz-theme-field">
                            <label>Currency</label>

                            <input
                                type="text"
                                name="saas_currency"
                                value="{{ old('saas_currency', $theme->saas_currency ?? 'NGN') }}"
                            >
                        </div>


                        <div class="esubiz-theme-field">
                            <label>SaaS Billing Period</label>

                            <input
                                type="number"
                                min="1"
                                step="1"
                                name="saas_billing_period"
                                value="{{ old('saas_billing_period', $theme->saas_billing_period ?? 1) }}"
                            >
                        </div>


                        <div class="esubiz-theme-field">
                            <label>SaaS Billing Interval</label>

                            @php
                                $saasInterval =
                                    old(
                                        'saas_billing_interval',
                                        $theme->saas_billing_interval
                                        ?? 'year'
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

                        <label class="esubiz-theme-check">

                            <input
                                type="checkbox"
                                name="off_server_available"
                                value="1"
                                @checked($theme->off_server_available)
                            >

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
                                value="{{ old('off_server_price', $theme->off_server_price) }}"
                            >
                        </div>


                        <div class="esubiz-theme-field">
                            <label>Currency</label>

                            <input
                                type="text"
                                name="off_server_currency"
                                value="{{ old('off_server_currency', $theme->off_server_currency ?? 'NGN') }}"
                            >
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

                        <label class="esubiz-theme-check">

                            <input
                                type="checkbox"
                                name="marketplace_enabled"
                                value="1"
                                @checked($theme->marketplace_enabled)
                            >

                            <span>
                                <strong>Marketplace Enabled</strong>

                                <span>
                                    Allow this Theme to appear as
                                    a Marketplace product.
                                </span>
                            </span>

                        </label>


                        <label class="esubiz-theme-check">

                            <input
                                type="checkbox"
                                name="marketplace_featured"
                                value="1"
                                @checked($theme->marketplace_featured)
                            >

                            <span>
                                <strong>Featured Theme</strong>

                                <span>
                                    Mark this Theme as featured
                                    in Marketplace presentation.
                                </span>
                            </span>

                        </label>


                        <label class="esubiz-theme-check">

                            <input
                                type="checkbox"
                                name="is_active"
                                value="1"
                                @checked($theme->is_active)
                            >

                            <span>
                                <strong>Active</strong>

                                <span>
                                    Master commerce switch for
                                    this Theme package.
                                </span>
                            </span>

                        </label>

                    </div>


                    <div class="esubiz-theme-fields mt-4">

                        <div class="esubiz-theme-field">
                            <label>Marketplace Category</label>

                            <input
                                type="text"
                                name="marketplace_category"
                                value="{{ old('marketplace_category', $theme->marketplace_category) }}"
                            >
                        </div>


                        <div class="esubiz-theme-field">
                            <label>Commission Rate (%)</label>

                            <input
                                type="number"
                                step="0.01"
                                min="0"
                                max="100"
                                name="commission_rate"
                                value="{{ old('commission_rate', $theme->commission_rate) }}"
                            >
                        </div>


                        <div class="esubiz-theme-field esubiz-theme-field-full">
                            <label>Release Notes</label>

                            <textarea name="release_notes">{{ old('release_notes', $theme->release_notes) }}</textarea>
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

                        <label class="esubiz-theme-check">

                            <input
                                type="hidden"
                                name="show_in_user_wizard"
                                value="0"
                            >

                            <input
                                type="checkbox"
                                name="show_in_user_wizard"
                                value="1"
                                @checked($theme->show_in_user_wizard)
                            >

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


                        <label class="esubiz-theme-check">

                            <input
                                type="hidden"
                                name="show_in_developer_wizard"
                                value="0"
                            >

                            <input
                                type="checkbox"
                                name="show_in_developer_wizard"
                                value="1"
                                @checked($theme->show_in_developer_wizard)
                            >

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

                    <div class="esubiz-theme-type-grid">

                        @forelse($websiteTypes as $websiteType)

                            <label class="esubiz-theme-type">

                                <input
                                    type="checkbox"
                                    name="website_type_ids[]"
                                    value="{{ $websiteType->id }}"
                                    @checked(
                                        in_array(
                                            (int) $websiteType->id,
                                            $selectedWebsiteTypes,
                                            true
                                        )
                                    )
                                >

                                <span>
                                    {{ $websiteType->name }}
                                </span>

                            </label>

                        @empty

                            <div class="esubiz-theme-help">
                                No active Website Types are configured.
                            </div>

                        @endforelse

                    </div>

                </div>

            </section>


        </div>


        <div class="esubiz-theme-edit-actions">

            <button
                type="submit"
                class="esubiz-theme-save-btn"
            >
                Save Theme Settings
            </button>

        </div>

    </form>

</div>

@endsection
