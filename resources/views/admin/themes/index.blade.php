
@extends('admin.layouts.app')

@section('content')

<style>
    .esubiz-theme-page {
        padding: 28px 30px 40px;
    }

    .esubiz-theme-page-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
        margin-bottom: 24px;
    }

    .esubiz-theme-page-head h1 {
        margin: 0;
        color: #101828;
        font-size: 26px;
        font-weight: 800;
    }

    .esubiz-theme-page-head p {
        margin: 6px 0 0;
        color: #667085;
        font-size: 13px;
    }

    .esubiz-theme-add-btn {
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
        box-shadow: 0 8px 18px rgba(37, 99, 235, .17);
    }

    .esubiz-theme-table-shell {
        overflow: visible;
        border: 1px solid #e5eaf1;
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 10px 30px rgba(15, 23, 42, .04);
    }

    .esubiz-theme-table-scroll {
        overflow-x: auto;
    }

    .esubiz-theme-table {
        width: 100%;
        margin: 0;
        border-collapse: collapse;
        min-width: 1080px;
    }

    .esubiz-theme-table thead th {
        padding: 13px 16px;
        border-bottom: 1px solid #e7ebf1;
        background: #f8fafc;
        color: #667085;
        font-size: 10.5px;
        font-weight: 800;
        letter-spacing: .045em;
        text-align: left;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .esubiz-theme-table tbody td {
        padding: 16px;
        border-bottom: 1px solid #eef1f5;
        color: #344054;
        font-size: 12px;
        vertical-align: middle;
    }

    .esubiz-theme-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .esubiz-theme-name {
        color: #101828;
        font-size: 13px;
        font-weight: 800;
    }

    .esubiz-theme-slug {
        margin-top: 3px;
        color: #98a2b3;
        font-size: 10.5px;
    }

    .esubiz-theme-badge {
        display: inline-flex;
        align-items: center;
        padding: 5px 9px;
        border-radius: 999px;
        font-size: 10px;
        font-weight: 800;
        white-space: nowrap;
    }

    .esubiz-theme-badge-success {
        background: #ecfdf3;
        color: #027a48;
    }

    .esubiz-theme-badge-muted {
        background: #f2f4f7;
        color: #667085;
    }

    .esubiz-theme-badge-blue {
        background: #eff6ff;
        color: #1d4ed8;
    }

    .esubiz-theme-badge-warning {
        background: #fff7ed;
        color: #c2410c;
    }

    .esubiz-theme-stack {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
    }

    .esubiz-theme-actions {
        position: relative;
        text-align: right;
    }

    .esubiz-theme-menu {
        position: relative;
        display: inline-block;
    }

    .esubiz-theme-menu > summary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border: 1px solid #e1e6ee;
        border-radius: 9px;
        background: #fff;
        color: #475467;
        font-size: 18px;
        font-weight: 800;
        cursor: pointer;
        list-style: none;
        line-height: 1;
    }

    .esubiz-theme-menu > summary::-webkit-details-marker {
        display: none;
    }

    .esubiz-theme-menu-list {
        position: absolute;
        top: 40px;
        right: 0;
        z-index: 100;
        width: 180px;
        padding: 6px;
        border: 1px solid #e4e7ec;
        border-radius: 11px;
        background: #fff;
        box-shadow: 0 16px 35px rgba(15, 23, 42, .13);
        text-align: left;
    }

    .esubiz-theme-menu-list a,
    .esubiz-theme-menu-list button {
        display: block;
        width: 100%;
        padding: 9px 10px;
        border: 0;
        border-radius: 7px;
        background: transparent;
        color: #344054;
        text-align: left;
        text-decoration: none;
        font-size: 11.5px;
        cursor: pointer;
    }

    .esubiz-theme-menu-list a:hover,
    .esubiz-theme-menu-list button:hover {
        background: #f5f7fa;
    }

    .esubiz-theme-menu-list .danger {
        color: #b42318;
    }

    .esubiz-theme-empty {
        padding: 64px 20px !important;
        color: #667085 !important;
        text-align: center;
    }

    .esubiz-theme-pagination {
        padding: 16px;
        border-top: 1px solid #eef1f5;
    }

    @media (max-width: 767px) {
        .esubiz-theme-page {
            padding: 20px 15px 30px;
        }

        .esubiz-theme-page-head {
            align-items: stretch;
            flex-direction: column;
        }

        .esubiz-theme-add-btn {
            width: 100%;
        }
    }
</style>


<div class="esubiz-theme-page">

    <div class="esubiz-theme-page-head">

        <div>
            <h1>Themes</h1>

            <p>
                Manage Theme packages, availability,
                Marketplace publishing and wizard visibility.
            </p>
        </div>

        <a
            href="{{ route('admin.themes.create') }}"
            class="esubiz-theme-add-btn"
        >
            + Add Theme
        </a>

    </div>


    @if(session('success'))
        <div class="alert alert-success mb-4">
            {{ session('success') }}
        </div>
    @endif


    <div class="esubiz-theme-table-shell">

        <div class="esubiz-theme-table-scroll">

            <table class="esubiz-theme-table">

                <thead>
                    <tr>
                        <th>Theme</th>
                        <th>Version</th>
                        <th>Publisher</th>
                        <th>Availability</th>
                        <th>Marketplace</th>
                        <th>Wizard</th>
                        <th>Status</th>
                        <th>Updated</th>
                        <th style="width: 60px;"></th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($themes as $theme)

                        <tr>

                            <td>
                                <div class="esubiz-theme-name">
                                    {{ $theme->name }}
                                </div>

                                <div class="esubiz-theme-slug">
                                    {{ $theme->slug }}
                                </div>
                            </td>


                            <td>
                                v{{ $theme->version }}
                            </td>


                            <td>
                                {{ $theme->publisher_name }}
                            </td>


                            <td>
                                <div class="esubiz-theme-stack">

                                    @if($theme->saas_available)
                                        <span class="esubiz-theme-badge esubiz-theme-badge-blue">
                                            SaaS
                                        </span>
                                    @endif

                                    @if($theme->off_server_available)
                                        <span class="esubiz-theme-badge esubiz-theme-badge-blue">
                                            Off-server
                                        </span>
                                    @endif

                                    @if(
                                        !$theme->saas_available
                                        && !$theme->off_server_available
                                    )
                                        <span class="esubiz-theme-badge esubiz-theme-badge-muted">
                                            Not for sale
                                        </span>
                                    @endif

                                </div>
                            </td>


                            <td>

                                @if($theme->marketplace_enabled)

                                    <span class="esubiz-theme-badge esubiz-theme-badge-success">
                                        Enabled
                                    </span>

                                @else

                                    <span class="esubiz-theme-badge esubiz-theme-badge-muted">
                                        Hidden
                                    </span>

                                @endif

                            </td>


                            <td>
                                <div class="esubiz-theme-stack">

                                    @if($theme->show_in_user_wizard)
                                        <span class="esubiz-theme-badge esubiz-theme-badge-blue">
                                            User
                                        </span>
                                    @endif

                                    @if($theme->show_in_developer_wizard)
                                        <span class="esubiz-theme-badge esubiz-theme-badge-blue">
                                            Developer
                                        </span>
                                    @endif

                                    @if(
                                        !$theme->show_in_user_wizard
                                        && !$theme->show_in_developer_wizard
                                    )
                                        <span class="esubiz-theme-badge esubiz-theme-badge-muted">
                                            Hidden
                                        </span>
                                    @endif

                                </div>
                            </td>


                            <td>

                                @if(!$theme->is_active)

                                    <span class="esubiz-theme-badge esubiz-theme-badge-warning">
                                        Disabled
                                    </span>

                                @elseif($theme->release_status === 'published')

                                    <span class="esubiz-theme-badge esubiz-theme-badge-success">
                                        Published
                                    </span>

                                @else

                                    <span class="esubiz-theme-badge esubiz-theme-badge-muted">
                                        {{ ucfirst($theme->release_status ?? 'Draft') }}
                                    </span>

                                @endif

                            </td>


                            <td>
                                {{ \Illuminate\Support\Carbon::parse($theme->updated_at)->format('d M Y') }}
                            </td>


                            <td class="esubiz-theme-actions">

                                <details class="esubiz-theme-menu">

                                    <summary title="Theme actions">
                                        ⋮
                                    </summary>

                                    <div class="esubiz-theme-menu-list">

                                        <a
                                            href="{{ route('admin.themes.show', $theme->id) }}"
                                        >
                                            View
                                        </a>

                                        <a
                                            href="{{ route('admin.themes.edit', $theme->id) }}"
                                        >
                                            Edit
                                        </a>

                                        <form
                                            method="POST"
                                            action="{{ route('admin.themes.destroy', $theme->id) }}"
                                            onsubmit="return confirm('Delete this Theme package?');"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="danger"
                                            >
                                                Delete
                                            </button>
                                        </form>

                                    </div>

                                </details>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td
                                colspan="9"
                                class="esubiz-theme-empty"
                            >
                                No Theme packages registered yet.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        @if(method_exists($themes, 'links'))

            <div class="esubiz-theme-pagination">
                {{ $themes->links() }}
            </div>

        @endif

    </div>

</div>

@endsection
