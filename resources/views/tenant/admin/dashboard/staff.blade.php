@extends('tenant.admin.layouts.app')

@section('title', 'Staff Dashboard')

@section('content')
{{-- ESUBIZ_CORE_PREMIUM_STAFF_DASHBOARD_V2 --}}

@php
    $staffName = trim(
        (string) ($coreUser->name ?? '')
    );

    $staffDisplayName =
        $staffName !== ''
            ? $staffName
            : 'Team Member';

    $staffRoles = collect(
        $coreRoles ?? []
    )->map(function ($role) {
        return ucwords(
            str_replace('_', ' ', $role)
        );
    });

    $workspaceItems = collect(
        $staffWorkspaceItems
            ?? $internalNavigation
            ?? []
    )->filter(function ($item) {
        $url = $item['url'] ?? null;

        return is_string($url)
            && $url !== ''
            && $url !== '#'
            && $url !== '/admin/dashboard';
    })->values();

    $workspaceCount = (int) (
        $staffWorkspaceCount
            ?? $workspaceItems->count()
    );

    $permissionCount = (int) (
        $staffPermissionCount
            ?? count($staffPermissions ?? [])
    );

    $roleCount = (int) (
        $staffRoleCount
            ?? count($coreRoles ?? [])
    );

    $accountActive = (bool) (
        $staffAccountActive
            ?? ($coreUser->is_active ?? false)
    );
@endphp

<style>
.staff-dashboard {
    --sd-text: #101828;
    --sd-muted: #667085;
    --sd-border: #eaecf0;
}

.staff-dashboard .sd-hero {
    position: relative;
    overflow: hidden;
    border-radius: 24px;
    padding: 30px;
    color: #fff;
    background:
        radial-gradient(
            circle at 90% 10%,
            rgba(255,255,255,.17),
            transparent 27%
        ),
        linear-gradient(
            135deg,
            #101828 0%,
            #1d2939 52%,
            #344054 100%
        );
    box-shadow:
        0 20px 45px rgba(16,24,40,.14);
}

.staff-dashboard .sd-hero::after {
    content: "";
    position: absolute;
    width: 240px;
    height: 240px;
    right: -85px;
    bottom: -145px;
    border-radius: 50%;
    background: rgba(255,255,255,.06);
}

.staff-dashboard .sd-hero-inner {
    position: relative;
    z-index: 1;
}

.staff-dashboard .sd-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 7px 11px;
    border-radius: 999px;
    border: 1px solid rgba(255,255,255,.16);
    background: rgba(255,255,255,.08);
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .06em;
    text-transform: uppercase;
}

.staff-dashboard .sd-title {
    margin: 15px 0 8px;
    font-size: clamp(27px,4vw,39px);
    font-weight: 800;
    letter-spacing: -.04em;
}

.staff-dashboard .sd-intro {
    max-width: 730px;
    margin: 0;
    color: rgba(255,255,255,.72);
    font-size: 14px;
    line-height: 1.7;
}

.staff-dashboard .sd-role {
    display: inline-flex;
    margin: 16px 5px 0 0;
    padding: 7px 11px;
    border-radius: 999px;
    background: rgba(255,255,255,.1);
    color: rgba(255,255,255,.9);
    font-size: 11px;
    font-weight: 700;
}

.staff-dashboard .sd-stat {
    height: 100%;
    min-height: 170px;
    padding: 22px;
    border: 1px solid transparent;
    border-radius: 20px;
    transition:
        transform .2s ease,
        box-shadow .2s ease;
}

.staff-dashboard .sd-stat:hover {
    transform: translateY(-3px);
    box-shadow:
        0 15px 32px rgba(16,24,40,.08);
}

.staff-dashboard .sd-blue {
    background:
        linear-gradient(145deg,#eff8ff,#fff);
    border-color: #b2ddff;
}

.staff-dashboard .sd-purple {
    background:
        linear-gradient(145deg,#f4f3ff,#fff);
    border-color: #d9d6fe;
}

.staff-dashboard .sd-gold {
    background:
        linear-gradient(145deg,#fffaeb,#fff);
    border-color: #fedf89;
}

.staff-dashboard .sd-green {
    background:
        linear-gradient(145deg,#ecfdf3,#fff);
    border-color: #abefc6;
}

.staff-dashboard .sd-icon {
    width: 42px;
    height: 42px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 19px;
    border-radius: 12px;
    background: rgba(255,255,255,.85);
    box-shadow:
        0 5px 14px rgba(16,24,40,.06);
    color: #344054;
    font-size: 17px;
}

.staff-dashboard .sd-label {
    color: var(--sd-muted);
    font-size: 11px;
    font-weight: 800;
    letter-spacing: .05em;
    text-transform: uppercase;
}

.staff-dashboard .sd-value {
    margin-top: 4px;
    color: var(--sd-text);
    font-size: 29px;
    line-height: 1.2;
    font-weight: 800;
    letter-spacing: -.04em;
}

.staff-dashboard .sd-note {
    margin-top: 6px;
    color: var(--sd-muted);
    font-size: 11px;
    line-height: 1.5;
}

.staff-dashboard .sd-section-title {
    color: var(--sd-text);
    font-size: 18px;
    font-weight: 800;
    letter-spacing: -.025em;
}

.staff-dashboard .sd-section-copy {
    color: var(--sd-muted);
    font-size: 12px;
    line-height: 1.6;
}

.staff-dashboard .sd-workspace {
    display: block;
    height: 100%;
    min-height: 160px;
    padding: 22px;
    border: 1px solid var(--sd-border);
    border-radius: 19px;
    background: #fff;
    color: inherit;
    text-decoration: none;
    transition:
        transform .2s ease,
        box-shadow .2s ease,
        border-color .2s ease;
}

.staff-dashboard .sd-workspace:hover {
    transform: translateY(-3px);
    border-color: #d0d5dd;
    box-shadow:
        0 15px 30px rgba(16,24,40,.08);
    color: inherit;
}

.staff-dashboard .sd-workspace-number {
    width: 39px;
    height: 39px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 11px;
    background: #f2f4f7;
    color: #344054;
    font-size: 11px;
    font-weight: 800;
}

.staff-dashboard .sd-workspace-arrow {
    color: #98a2b3;
    font-size: 19px;
}

.staff-dashboard .sd-workspace-title {
    margin-top: 24px;
    color: var(--sd-text);
    font-size: 15px;
    font-weight: 800;
}

.staff-dashboard .sd-workspace-copy {
    margin-top: 4px;
    color: var(--sd-muted);
    font-size: 11px;
    line-height: 1.6;
}

.staff-dashboard .sd-empty {
    padding: 35px 24px;
    border: 1px dashed #d0d5dd;
    border-radius: 19px;
    background: #fcfcfd;
    text-align: center;
}

@media (max-width: 767.98px) {
    .staff-dashboard .sd-hero {
        padding: 23px;
        border-radius: 20px;
    }

    .staff-dashboard .sd-stat {
        min-height: 150px;
    }
}
</style>


<div class="container-fluid py-4 staff-dashboard">

    <section class="sd-hero mb-4">
        <div class="sd-hero-inner">

            <div class="sd-eyebrow">
                <i class="bi bi-briefcase-fill"></i>
                Staff Workspace
            </div>

            <h1 class="sd-title">
                Welcome, {{ $staffDisplayName }}
            </h1>

            <p class="sd-intro">
                Your workspace brings together the Core
                tools and actions assigned to you. Access
                automatically reflects your current roles
                and permissions.
            </p>

            @foreach($staffRoles as $role)
                <span class="sd-role">
                    {{ $role }}
                </span>
            @endforeach

        </div>
    </section>


    <div class="row g-3 mb-5">

        <div class="col-12 col-lg-3">
            <div class="sd-stat sd-blue">
                <div class="sd-icon">
                    <i class="bi bi-grid-1x2-fill"></i>
                </div>

                <div class="sd-label">
                    Accessible Areas
                </div>

                <div class="sd-value">
                    {{ number_format($workspaceCount) }}
                </div>

                <div class="sd-note">
                    Core workspaces currently available
                    to your account
                </div>
            </div>
        </div>


        <div class="col-12 col-lg-3">
            <div class="sd-stat sd-purple">
                <div class="sd-icon">
                    <i class="bi bi-shield-check"></i>
                </div>

                <div class="sd-label">
                    Granted Permissions
                </div>

                <div class="sd-value">
                    {{ number_format($permissionCount) }}
                </div>

                <div class="sd-note">
                    Authorized actions from your
                    current Core roles
                </div>
            </div>
        </div>


        <div class="col-12 col-lg-3">
            <div class="sd-stat sd-gold">
                <div class="sd-icon">
                    <i class="bi bi-person-badge-fill"></i>
                </div>

                <div class="sd-label">
                    Assigned Roles
                </div>

                <div class="sd-value">
                    {{ number_format($roleCount) }}
                </div>

                <div class="sd-note">
                    Roles currently defining your
                    staff workspace
                </div>
            </div>
        </div>


        <div class="col-12 col-lg-3">
            <div class="sd-stat sd-green">
                <div class="sd-icon">
                    <i class="bi bi-check-circle-fill"></i>
                </div>

                <div class="sd-label">
                    Account Status
                </div>

                <div
                    class="sd-value"
                    style="font-size:22px;"
                >
                    {{
                        $accountActive
                            ? 'Active'
                            : 'Inactive'
                    }}
                </div>

                <div class="sd-note">
                    Current Core staff account status
                </div>
            </div>
        </div>

    </div>


    <div
        class="d-flex flex-column flex-md-row
               justify-content-between
               align-items-md-end gap-2 mb-3"
    >
        <div>
            <div class="sd-section-title">
                Your Workspace
            </div>

            <div class="sd-section-copy mt-1">
                Open an area below to continue your
                assigned work. Only Core features
                available to your account are shown.
            </div>
        </div>

        @if($workspaceCount > 0)
            <div class="sd-section-copy">
                {{ $workspaceCount }}
                {{
                    \Illuminate\Support\Str::plural(
                        'area',
                        $workspaceCount
                    )
                }}
                available
            </div>
        @endif
    </div>


    <div class="row g-3">

        @forelse(
            $workspaceItems
            as $index => $item
        )

            <div class="col-12 col-md-6 col-xl-4">
                <a
                    href="{{ $item['url'] }}"
                    class="sd-workspace"
                >
                    <div
                        class="d-flex
                               justify-content-between
                               align-items-start gap-3"
                    >
                        <span
                            class="sd-workspace-number"
                        >
                            {{
                                str_pad(
                                    (string) ($index + 1),
                                    2,
                                    '0',
                                    STR_PAD_LEFT
                                )
                            }}
                        </span>

                        <span
                            class="sd-workspace-arrow"
                        >
                            &rarr;
                        </span>
                    </div>

                    <div class="sd-workspace-title">
                        {{
                            $item['label']
                                ?? 'Workspace'
                        }}
                    </div>

                    <div class="sd-workspace-copy">
                        Open
                        {{
                            $item['label']
                                ?? 'this area'
                        }}
                        and continue with the actions
                        permitted for your role.
                    </div>
                </a>
            </div>

        @empty

            <div class="col-12">
                <div class="sd-empty">

                    <div class="fw-semibold mb-2">
                        No workspaces are currently
                        assigned
                    </div>

                    <div class="text-muted small">
                        Your account is active, but no
                        additional Core workspace
                        permissions are currently
                        available. Your Site Administrator
                        can update your role permissions
                        when required.
                    </div>

                </div>
            </div>

        @endforelse

    </div>

</div>
@endsection
