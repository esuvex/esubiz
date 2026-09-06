
@extends('admin.layouts.app')

@section('content')

<style>
    .esubiz-theme-show-page {
        max-width: 1180px;
        margin: 0 auto;
        padding: 30px 28px 46px;
    }

    .esubiz-theme-show-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 24px;
    }

    .esubiz-theme-show-back {
        display: inline-block;
        margin-bottom: 16px;
        color: #667085;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
    }

    .esubiz-theme-show-top h1 {
        margin: 0;
        color: #101828;
        font-size: 27px;
        font-weight: 800;
    }

    .esubiz-theme-show-top p {
        margin: 7px 0 0;
        color: #667085;
        font-size: 13px;
    }

    .esubiz-theme-show-actions {
        display: flex;
        gap: 10px;
    }

    .esubiz-theme-show-edit {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 42px;
        padding: 0 17px;
        border-radius: 10px;
        background: #2563eb;
        color: #fff !important;
        text-decoration: none !important;
        font-size: 12px;
        font-weight: 800;
    }

    .esubiz-theme-show-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .esubiz-theme-show-card {
        border: 1px solid #e4e9f0;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 8px 26px rgba(15,23,42,.035);
    }

    .esubiz-theme-show-card.full {
        grid-column: 1 / -1;
    }

    .esubiz-theme-show-card-head {
        padding: 18px 20px;
        border-bottom: 1px solid #eef1f5;
    }

    .esubiz-theme-show-card-head h3 {
        margin: 0;
        color: #101828;
        font-size: 14px;
        font-weight: 800;
    }

    .esubiz-theme-show-card-body {
        padding: 20px;
    }

    .esubiz-theme-show-list {
        display: grid;
        gap: 13px;
    }

    .esubiz-theme-show-item {
        display: grid;
        grid-template-columns: 165px minmax(0, 1fr);
        gap: 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid #f0f2f5;
    }

    .esubiz-theme-show-item:last-child {
        padding-bottom: 0;
        border-bottom: 0;
    }

    .esubiz-theme-show-label {
        color: #667085;
        font-size: 11px;
        font-weight: 700;
    }

    .esubiz-theme-show-value {
        color: #1d2939;
        font-size: 12px;
        font-weight: 700;
        word-break: break-word;
    }

    .esubiz-theme-show-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 7px;
    }

    .esubiz-theme-show-badge {
        display: inline-flex;
        align-items: center;
        padding: 5px 9px;
        border-radius: 999px;
        background: #f2f4f7;
        color: #475467;
        font-size: 10px;
        font-weight: 800;
    }

    .esubiz-theme-show-badge.on {
        background: #ecfdf3;
        color: #027a48;
    }

    .esubiz-theme-show-badge.blue {
        background: #eff6ff;
        color: #1d4ed8;
    }

    @media(max-width: 800px) {
        .esubiz-theme-show-grid {
            grid-template-columns: 1fr;
        }

        .esubiz-theme-show-card.full {
            grid-column: auto;
        }

        .esubiz-theme-show-top {
            flex-direction: column;
        }

        .esubiz-theme-show-item {
            grid-template-columns: 1fr;
            gap: 5px;
        }
    }
</style>


<div class="esubiz-theme-show-page">

    <a
        href="{{ route('admin.themes.index') }}"
        class="esubiz-theme-show-back"
    >
        ← Back to Themes
    </a>


    <div class="esubiz-theme-show-top">

        <div>

            <h1>
                {{ $theme->name }}
            </h1>

            <p>
                Theme package details and Marketplace configuration.
            </p>

        </div>


        <div class="esubiz-theme-show-actions">

            <a
                href="{{ route('admin.themes.edit', $theme->id) }}"
                class="esubiz-theme-show-edit"
            >
                Edit Theme
            </a>

        </div>

    </div>


    <div class="esubiz-theme-show-grid">


        <section class="esubiz-theme-show-card">

            <div class="esubiz-theme-show-card-head">
                <h3>Theme Details</h3>
            </div>

            <div class="esubiz-theme-show-card-body">

                <div class="esubiz-theme-show-list">

                    <div class="esubiz-theme-show-item">
                        <div class="esubiz-theme-show-label">
                            Name
                        </div>

                        <div class="esubiz-theme-show-value">
                            {{ $theme->name }}
                        </div>
                    </div>


                    <div class="esubiz-theme-show-item">
                        <div class="esubiz-theme-show-label">
                            Slug
                        </div>

                        <div class="esubiz-theme-show-value">
                            {{ $theme->slug }}
                        </div>
                    </div>


                    <div class="esubiz-theme-show-item">
                        <div class="esubiz-theme-show-label">
                            Version
                        </div>

                        <div class="esubiz-theme-show-value">
                            v{{ $theme->version }}
                        </div>
                    </div>


                    <div class="esubiz-theme-show-item">
                        <div class="esubiz-theme-show-label">
                            Publisher
                        </div>

                        <div class="esubiz-theme-show-value">
                            {{ $theme->publisher_name }}
                        </div>
                    </div>


                    <div class="esubiz-theme-show-item">
                        <div class="esubiz-theme-show-label">
                            Publisher Type
                        </div>

                        <div class="esubiz-theme-show-value">
                            {{ ucfirst($theme->publisher_type ?? '—') }}
                        </div>
                    </div>

                </div>

            </div>

        </section>


        <section class="esubiz-theme-show-card">

            <div class="esubiz-theme-show-card-head">
                <h3>Package</h3>
            </div>

            <div class="esubiz-theme-show-card-body">

                <div class="esubiz-theme-show-list">

                    <div class="esubiz-theme-show-item">
                        <div class="esubiz-theme-show-label">
                            Package Path
                        </div>

                        <div class="esubiz-theme-show-value">
                            {{ $theme->package_path }}
                        </div>
                    </div>


                    <div class="esubiz-theme-show-item">
                        <div class="esubiz-theme-show-label">
                            Preview
                        </div>

                        <div class="esubiz-theme-show-value">
                            {{ $theme->preview_path ?? '—' }}
                        </div>
                    </div>


                    <div class="esubiz-theme-show-item">
                        <div class="esubiz-theme-show-label">
                            Package Size
                        </div>

                        <div class="esubiz-theme-show-value">
                            {{ number_format(($theme->package_bytes ?? 0) / 1024, 1) }} KB
                        </div>
                    </div>


                    <div class="esubiz-theme-show-item">
                        <div class="esubiz-theme-show-label">
                            SHA256
                        </div>

                        <div class="esubiz-theme-show-value">
                            {{ $theme->checksum_sha256 }}
                        </div>
                    </div>

                </div>

            </div>

        </section>


        <section class="esubiz-theme-show-card">

            <div class="esubiz-theme-show-card-head">
                <h3>Commercial Availability</h3>
            </div>

            <div class="esubiz-theme-show-card-body">

                <div class="esubiz-theme-show-list">

                    <div class="esubiz-theme-show-item">
                        <div class="esubiz-theme-show-label">
                            SaaS
                        </div>

                        <div class="esubiz-theme-show-value">
                            @if($theme->saas_available)
                                {{ $theme->saas_currency ?? 'NGN' }}
                                {{ number_format((float) $theme->saas_price, 2) }}

                                @if($theme->saas_billing_period && $theme->saas_billing_interval)
                                    /
                                    {{ $theme->saas_billing_period }}
                                    {{ ucfirst($theme->saas_billing_interval) }}
                                @endif
                            @else
                                Not available
                            @endif
                        </div>
                    </div>


                    <div class="esubiz-theme-show-item">
                        <div class="esubiz-theme-show-label">
                            Off-server
                        </div>

                        <div class="esubiz-theme-show-value">
                            @if($theme->off_server_available)
                                {{ $theme->off_server_currency ?? 'NGN' }}
                                {{ number_format((float) $theme->off_server_price, 2) }}
                            @else
                                Not available
                            @endif
                        </div>
                    </div>

                </div>

            </div>

        </section>


        <section class="esubiz-theme-show-card">

            <div class="esubiz-theme-show-card-head">
                <h3>Status</h3>
            </div>

            <div class="esubiz-theme-show-card-body">

                <div class="esubiz-theme-show-badges">

                    <span class="esubiz-theme-show-badge {{ $theme->is_active ? 'on' : '' }}">
                        {{ $theme->is_active ? 'Active' : 'Disabled' }}
                    </span>

                    <span class="esubiz-theme-show-badge {{ $theme->marketplace_enabled ? 'on' : '' }}">
                        {{ $theme->marketplace_enabled ? 'Marketplace Enabled' : 'Marketplace Hidden' }}
                    </span>

                    @if($theme->marketplace_featured)
                        <span class="esubiz-theme-show-badge blue">
                            Featured
                        </span>
                    @endif

                    <span class="esubiz-theme-show-badge">
                        {{ ucfirst($theme->release_status ?? 'Draft') }}
                    </span>

                </div>

            </div>

        </section>


        <section class="esubiz-theme-show-card full">

            <div class="esubiz-theme-show-card-head">
                <h3>Wizard & Website Types</h3>
            </div>

            <div class="esubiz-theme-show-card-body">

                <div class="esubiz-theme-show-list">

                    <div class="esubiz-theme-show-item">

                        <div class="esubiz-theme-show-label">
                            Wizard Visibility
                        </div>

                        <div class="esubiz-theme-show-badges">

                            @if($theme->show_in_user_wizard)
                                <span class="esubiz-theme-show-badge blue">
                                    User Wizard
                                </span>
                            @endif

                            @if($theme->show_in_developer_wizard)
                                <span class="esubiz-theme-show-badge blue">
                                    Developer Wizard
                                </span>
                            @endif

                            @if(
                                !$theme->show_in_user_wizard
                                && !$theme->show_in_developer_wizard
                            )
                                <span class="esubiz-theme-show-badge">
                                    Hidden from Wizards
                                </span>
                            @endif

                        </div>

                    </div>


                    <div class="esubiz-theme-show-item">

                        <div class="esubiz-theme-show-label">
                            Website Types
                        </div>

                        <div class="esubiz-theme-show-badges">

                            @forelse($websiteTypes as $websiteType)

                                <span class="esubiz-theme-show-badge blue">
                                    {{ $websiteType->name }}
                                </span>

                            @empty

                                <span class="esubiz-theme-show-badge">
                                    None assigned
                                </span>

                            @endforelse

                        </div>

                    </div>

                </div>

            </div>

        </section>


    </div>

</div>

@endsection
