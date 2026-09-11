@php
    /*
     * ESUBIZ_CENTRAL_SSO_CONTROLS_UI_V1
     */
    $esubizSsoSettings = \Illuminate\Support\Facades\DB::table('site_settings')
        ->whereNull('workspace_id')
        ->whereIn('key', [
            'auth.esubiz_sso.enabled',
            'auth.esubiz_sso.core_enabled',
            'auth.esubiz_sso.external_enabled',
        ])
        ->pluck('value', 'key');

    $esubizSsoEnabled =
        ($esubizSsoSettings['auth.esubiz_sso.enabled'] ?? '1') === '1';

    $esubizSsoCoreEnabled =
        ($esubizSsoSettings['auth.esubiz_sso.core_enabled'] ?? '1') === '1';

    $esubizSsoExternalEnabled =
        ($esubizSsoSettings['auth.esubiz_sso.external_enabled'] ?? '1') === '1';


    /*
     * ESUBIZ_WEBSITE_WIZARD_GLOBAL_SETTINGS_V1
     */
    $wizardSettingDefaults = [
        /*
         * ESUBIZ_SEPARATE_WIZARD_PROGRESS_TIMERS_V1
         */
        'wizard.user.progress_minimum_seconds' => '8',
        'wizard.developer.progress_minimum_seconds' => '8',

        'wizard.user.progress_title' =>
            'Creating Your Website',
        /*
         * ESUBIZ_WIZARD_REAL_PROGRESS_CONFIG_V1
         */
        'wizard.user.progress_text.0_19' =>
            'Preparing your website...',
        'wizard.user.progress_text.20_39' =>
            'Preparing your website configuration...',
        'wizard.user.progress_text.40_59' =>
            'Installing your website...',
        'wizard.user.progress_text.60_79' =>
            'Configuring your website...',
        'wizard.user.progress_text.80_99' =>
            'Finalizing your website...',
        'wizard.user.progress_text.100' =>
            'Website deployment completed.',
        'wizard.user.success_title' =>
            'Website Created Successfully',
        'wizard.user.success_text' =>
            'Your website is ready.',
        'wizard.user.failure_title' =>
            'Website Creation Failed',
        'wizard.user.failure_text' =>
            'Your website could not be created. Please try again.',

        'wizard.developer.progress_title' =>
            'Compiling Your Website',
        'wizard.developer.progress_text.0_19' =>
            'Preparing your build...',
        'wizard.developer.progress_text.20_39' =>
            'Preparing the Website Type package...',
        'wizard.developer.progress_text.40_59' =>
            'Assembling selected products...',
        'wizard.developer.progress_text.60_79' =>
            'Preparing licensing and documentation...',
        'wizard.developer.progress_text.80_99' =>
            'Packaging your website...',
        'wizard.developer.progress_text.100' =>
            'Website compilation completed.',
        'wizard.developer.success_title' =>
            'Compilation Successful',
        'wizard.developer.success_text' =>
            'Your website package is ready.',
        'wizard.developer.failure_title' =>
            'Compilation Failed',
        'wizard.developer.failure_text' =>
            'The website package could not be compiled.',

        // ESUBIZ_USER_WIZARD_RESULT_BUTTON_LABELS_V1
        'wizard.user.manage_website_label' =>
            'Manage Website',
        'wizard.user.dashboard_label' =>
            'Esubiz Dashboard',
        'wizard.user.redeploy_label' =>
            'Redeploy',

        'wizard.developer.payment_label' =>
            'Proceed to Payment',
        'wizard.developer.recompile_label' =>
            'Recompile',
        'wizard.developer.dashboard_label' =>
            'Dashboard',
    ];

    $wizardSettings = \Illuminate\Support\Facades\DB::table(
        'site_settings'
    )
        ->whereNull('workspace_id')
        ->whereIn(
            'key',
            array_keys($wizardSettingDefaults)
        )
        ->pluck('value', 'key');

    foreach ($wizardSettingDefaults as $key => $default) {
        if (!isset($wizardSettings[$key])) {
            $wizardSettings[$key] = $default;
        }
    }

@endphp

@extends('admin.layouts.app')

@section('title', 'Site Settings')

@section('content')
<style>
    /* ESUBIZ_CENTRAL_SETTINGS_SUBTABS_V2 */

    /* ESUBIZ_CENTRAL_SSO_TOGGLE_UI_V3 */
    /* ESUBIZ_CENTRAL_SSO_GRID_V4 */
    .es-sso-toggle-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 18px;
    }

    .es-sso-toggle-grid .es-sso-toggle-card {
        margin-bottom: 0;
        height: 100%;
    }

    @media (max-width: 767.98px) {
        .es-sso-toggle-grid {
            grid-template-columns: 1fr;
        }
    }

    .es-sso-toggle-card {
        padding: 17px 18px;
        margin-bottom: 14px;
        background: #fff;
        border: 1px solid var(--es-border);
        border-radius: 12px;
    }

    .es-sso-toggle-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
    }

    .es-sso-toggle-copy {
        min-width: 0;
    }

    .es-sso-toggle-title {
        margin: 0 0 4px;
        color: var(--es-text);
        font-size: 14px;
        font-weight: 700;
    }

    .es-sso-toggle-description {
        margin: 0;
        color: var(--es-muted);
        font-size: 12px;
        line-height: 1.5;
    }

    .es-sso-switch {
        position: relative;
        flex: 0 0 auto;
        width: 48px;
        height: 26px;
        margin: 0;
    }

    .es-sso-switch input {
        position: absolute;
        width: 1px;
        height: 1px;
        opacity: 0;
        pointer-events: none;
    }

    .es-sso-switch-track {
        position: absolute;
        inset: 0;
        cursor: pointer;
        background: #fff;
        border: 2px solid #344054;
        border-radius: 999px;
        transition: .18s ease;
    }

    .es-sso-switch-track::after {
        content: "";
        position: absolute;
        top: 3px;
        left: 3px;
        width: 16px;
        height: 16px;
        background: #344054;
        border-radius: 50%;
        transition: .18s ease;
    }

    .es-sso-switch input:checked + .es-sso-switch-track {
        background: var(--es-blue);
        border-color: var(--es-blue);
    }

    .es-sso-switch input:checked + .es-sso-switch-track::after {
        left: 25px;
        background: #fff;
    }

    .es-sso-switch input:focus-visible + .es-sso-switch-track {
        outline: 3px solid rgba(21, 94, 239, .18);
        outline-offset: 2px;
    }

    .es-settings {
        --es-blue: #155eef;
        --es-blue-dark: #0b3ea8;
        --es-blue-soft: #eff4ff;
        --es-blue-border: #b2ccff;
        --es-border: #e4e7ec;
        --es-text: #101828;
        --es-muted: #667085;
    }

    .es-settings-header {
        margin-bottom: 24px;
    }

    .es-settings-header h1 {
        margin: 0 0 6px;
        color: var(--es-text);
        font-size: 28px;
        font-weight: 700;
    }

    .es-settings-header p {
        margin: 0;
        color: var(--es-muted);
        font-size: 14px;
    }

    .es-settings-shell {
        overflow: hidden;
        background: #fff;
        border: 1px solid var(--es-border);
        border-radius: 16px;
        box-shadow: 0 8px 28px rgba(16, 24, 40, .06);
    }

    .es-main-tabs {
        display: flex;
        gap: 6px;
        padding: 12px;
        overflow-x: auto;
        background: linear-gradient(135deg, #0b3ea8 0%, #155eef 100%);
        scrollbar-width: thin;
    }

    .es-main-tab {
        flex: 0 0 auto;
        padding: 11px 15px;
        color: rgba(255,255,255,.84);
        text-decoration: none;
        font-size: 13px;
        font-weight: 600;
        border: 1px solid transparent;
        border-radius: 9px;
        white-space: nowrap;
        transition: .18s ease;
    }

    .es-main-tab:hover {
        color: #fff;
        background: rgba(255,255,255,.12);
        text-decoration: none;
    }

    .es-main-tab.active {
        color: var(--es-blue-dark);
        background: #fff;
        box-shadow: 0 4px 12px rgba(0,0,0,.13);
    }

    .es-subtabs-wrap {
        padding: 13px 18px;
        background: #f8faff;
        border-bottom: 1px solid var(--es-border);
    }

    .es-subtabs {
        display: flex;
        gap: 8px;
        overflow-x: auto;
        scrollbar-width: thin;
    }

    .es-subtab {
        flex: 0 0 auto;
        padding: 8px 13px;
        color: #344054;
        background: #fff;
        border: 1px solid #d0d5dd;
        border-radius: 8px;
        text-decoration: none;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
        transition: .18s ease;
    }

    .es-subtab:hover {
        color: var(--es-blue-dark);
        border-color: var(--es-blue-border);
        background: var(--es-blue-soft);
        text-decoration: none;
    }

    .es-subtab.active {
        color: #fff;
        background: var(--es-blue);
        border-color: var(--es-blue);
    }

    .es-settings-body {
        padding: 28px;
    }

    .es-settings-section-title {
        margin: 0 0 6px;
        color: var(--es-text);
        font-size: 21px;
        font-weight: 700;
    }

    .es-settings-section-copy {
        margin: 0 0 22px;
        color: var(--es-muted);
        font-size: 14px;
    }

    .es-settings-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 16px;
    }

    .es-setting-card {
        display: flex;
        flex-direction: column;
        min-height: 155px;
        padding: 20px;
        color: inherit;
        text-decoration: none;
        background: #fff;
        border: 1px solid var(--es-border);
        border-radius: 13px;
        transition: .18s ease;
    }

    a.es-setting-card:hover {
        border-color: var(--es-blue-border);
        box-shadow: 0 8px 20px rgba(21,94,239,.10);
        transform: translateY(-2px);
        text-decoration: none;
    }

    .es-setting-card h3 {
        margin: 0 0 8px;
        color: var(--es-text);
        font-size: 15px;
        font-weight: 700;
    }

    .es-setting-card p {
        margin: 0;
        color: var(--es-muted);
        font-size: 13px;
        line-height: 1.55;
    }

    .es-setting-card .es-open {
        margin-top: auto;
        padding-top: 18px;
        color: var(--es-blue);
        font-size: 13px;
        font-weight: 700;
    }

    .es-coming {
        background: #f9fafb;
    }

    .es-coming-badge {
        align-self: flex-start;
        margin-top: auto;
        padding: 5px 9px;
        color: #475467;
        background: #eaecf0;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
    }

    @media (max-width: 1100px) {
        .es-settings-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 680px) {
        .es-settings-body {
            padding: 18px;
        }

        .es-settings-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

@php
    $tab = request('tab', 'main');
    $sub = request('sub', 'general');

    $tabs = [
        'main'       => 'Main Settings',
        'email'      => 'Email Settings',
        'auth'       => 'Auth Settings',
        'payments'   => 'Payment Gateways',
        'themes'     => 'Theme Manager',
        'pages'      => 'Site Pages',
        'ai'         => 'Esubiz AI',
        'sms'        => 'Esubiz SMS',
        'esubizmail' => 'Esubiz Email',
        'whatsapp'   => 'Esubiz WhatsApp',
        'marketing'  => 'Marketing & Ads',
        'api'        => 'API',
        'website-wizard' => 'Website Wizard',
        'updates'    => 'Migration & Updates',
        'notices'    => 'Dashboard Notices',
    ];

    $subTabs = [
        'main' => [
            'general'   => 'General',
            'branding'  => 'Branding',
            'regional'  => 'Regional',
            'system'    => 'System',
        ],

        'email' => [
            'general'   => 'General',
            'smtp'      => 'SMTP',
            'sender'    => 'Sender Identity',
            'templates' => 'Templates',
        ],

        'auth' => [
            'general'   => 'General',
            'esubiz-sso'=> 'Esubiz SSO',
            'google'    => 'Google',
            'facebook'  => 'Facebook',
            'instagram' => 'Instagram',
            'tiktok'    => 'TikTok',
            'x'         => 'X',
        ],

        'payments' => [
            'overview'  => 'Overview',
            'online'    => 'Online',
            'offline'   => 'Offline',
            'reviews'   => 'Offline Payments',
            'wallet'    => 'Wallet',
            'giftcard'  => 'Gift Cards',
            'payout'    => 'Payout',
        ],

        'themes' => [
            'overview'  => 'Overview',
            'manager'   => 'Theme Manager',
        ],

        'pages' => [
            'general'   => 'Pages',
            'terms'     => 'Terms & Conditions',
            'privacy'   => 'Privacy Policy',
        ],

        'ai' => [
            'overview'  => 'Overview',
            'management'=> 'AI Management',
            'settings'  => 'AI Settings',
        ],

        'sms' => [
            'general'   => 'General',
            'providers' => 'Providers',
            'pricing'   => 'Pricing',
            'usage'     => 'Usage',
        ],

        'esubizmail' => [
            'general'   => 'General',
            'providers' => 'Providers',
            'pricing'   => 'Pricing',
            'usage'     => 'Usage',
        ],

        'whatsapp' => [
            'general'   => 'General',
            'providers' => 'Providers',
            'pricing'   => 'Pricing',
            'usage'     => 'Usage',
        ],

        'marketing' => [
            'general'   => 'General',
            'ads'       => 'Ads',
            'tracking'  => 'Tracking',
            'social'    => 'Social Platforms',
        ],

        'api' => [
            'general'   => 'General',
            'keys'      => 'API Keys',
            'webhooks'  => 'Webhooks',
            'logs'      => 'Logs',
        ],

        'updates' => [
            'overview' => 'Overview',
            'updates'  => 'Updates',
            'tenants'  => 'Tenants',
            'history'  => 'History',
        ],
    ];

    if (!isset($tabs[$tab])) {
        $tab = 'main';
    }

    $currentSubs = $subTabs[$tab] ?? [];

    /* ESUBIZ_PLATFORM_UPDATES_V2 */
    $platformUpdateStats = [
        'updates' => 0,
        'published' => 0,
        'pending_runs' => 0,
        'failed_runs' => 0,
        'websites' => 0,
    ];

    $platformUpdates = collect();

    /* ESUBIZ_DASHBOARD_NOTICES_ADMIN_UI_V1 */
    $dashboardNotices = collect();
    $dashboardNoticeTenants = collect();

    if ($tab === 'notices') {
        $dashboardNotices = \App\Models\DashboardNotice::query()
            ->latest()
            ->paginate(
            10,
            ['*'],
            'notice_page'
        );

        $dashboardNoticeTenants = \App\Models\Website::query()
            ->orderBy('id')
            ->get();

    /*
     * ESUBIZ_DASHBOARD_NOTICE_DYNAMIC_CENTRAL_ROLES_V17
     *
     * Central roles come directly from the Central roles table.
     * Core roles live separately in tenant site_roles, therefore
     * they cannot enter this collection.
     *
     * Any future active Central role automatically becomes
     * available to Dashboard Notice targeting without another
     * Dashboard Notice code change.
     */
    /*
     * ESUBIZ_DASHBOARD_NOTICE_REAL_ROLE_MEMBERSHIP_V43
     *
     * Central roles remain fully database-driven.
     * Membership comes exclusively from user_roles.
     *
     * No Admin/Developer/future role name is hard-coded.
     */
    $dashboardNoticeCentralRoles =
        \App\Models\Role::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'slug',
                'description',
            ]);

    $dashboardNoticeCentralMembershipsV43 =
        \Illuminate\Support\Facades\DB::table('user_roles')
            ->join(
                'roles',
                'roles.id',
                '=',
                'user_roles.role_id'
            )
            ->where('roles.is_active', true)
            ->select([
                'user_roles.user_id',
                'roles.id as role_id',
                'roles.name as role_name',
            ])
            ->get();

    $dashboardNoticeRoleMemberCountsV43 =
        $dashboardNoticeCentralMembershipsV43
            ->groupBy('role_id')
            ->map(
                fn ($members) =>
                    $members
                        ->pluck('user_id')
                        ->unique()
                        ->count()
            );

    $dashboardNoticeUserRolesV43 =
        $dashboardNoticeCentralMembershipsV43
            ->groupBy('user_id')
            ->map(
                fn ($members) =>
                    $members
                        ->map(
                            fn ($membership) => [
                                'id' =>
                                    (int) $membership->role_id,
                                'name' =>
                                    $membership->role_name,
                            ]
                        )
                        ->unique('id')
                        ->values()
            );

    /*
     * Central users remain dynamically sourced from users.
     * Their displayed roles are the actual user_roles
     * memberships calculated above.
     */
    $dashboardNoticeCentralUsers =
        \App\Models\User::query()
            ->orderBy('name')
            ->orderBy('email')
            ->get([
                'id',
                'name',
                'email',
            ]);

    }


    /* ESUBIZ_PLATFORM_UPDATE_SETTINGS_UI_V1 */
    $platformUpdateSettings = null;

    if ($tab === 'updates') {
        $platformUpdateSettings =
            \App\Models\PlatformUpdateSetting::current();
    }
    $platformUpdateRuns = collect();
    $platformUpdateTenants = collect();

    if ($tab === 'updates') {
        /*
         * ESUBIZ_PLATFORM_UPDATE_AUTO_DISCOVERY_V4
         *
         * Opening Migration & Updates synchronizes the Central registry
         * with canonical update sources only. Discovery never executes
         * tenant migrations or modifies tenant databases/files.
         */
        $platformDiscoveryStats = [
            'discovered' => 0,
            'existing' => 0,
        ];

        if (\Illuminate\Support\Facades\Schema::hasTable('platform_updates')) {
            try {
                $platformDiscoveryStats = app(
                    \App\Services\PlatformUpdates\PlatformUpdateDiscoveryService::class
                )->sync();
            } catch (\Throwable $e) {
                report($e);
            }

            $platformUpdateStats['updates'] =
                \Illuminate\Support\Facades\DB::table('platform_updates')->count();

            $platformUpdateStats['published'] =
                \Illuminate\Support\Facades\DB::table('platform_updates')
                    ->where('status', 'published')
                    ->count();

            $platformUpdates =
                \Illuminate\Support\Facades\DB::table('platform_updates')
                    ->orderByDesc('id')
                    ->limit(50)
                    ->get();

        } else {
            $platformDiscoveryStats = [
                'discovered' => 0,
                'existing' => 0,
            ];
        }

        /*
         * The remainder of the existing V2 block starts with this
         * condition. Keep its run/history handling intact.
         */
        if (\Illuminate\Support\Facades\Schema::hasTable('platform_update_runs')) {
            $platformUpdateStats['pending_runs'] =
                \Illuminate\Support\Facades\DB::table('platform_update_runs')
                    ->where('status', 'pending')
                    ->count();

            $platformUpdateStats['failed_runs'] =
                \Illuminate\Support\Facades\DB::table('platform_update_runs')
                    ->where('status', 'failed')
                    ->count();

            $platformUpdateRuns =
                \Illuminate\Support\Facades\DB::table('platform_update_runs')
                    ->orderByDesc('id')
                    ->limit(10)
                    ->get();
        }

        $platformUpdateTenants = \App\Models\Website::query()
            ->orderBy('id')
            ->get();

        $platformUpdateStats['websites'] = $platformUpdateTenants->count();
    }

    if (!isset($currentSubs[$sub])) {
        $sub = array_key_first($currentSubs) ?? 'general';
    }
@endphp

<div class="es-settings">

    <div class="es-settings-header">
        <h1>Site Settings</h1>
        <p>Manage Central Esubiz configuration, services and platform integrations from one place.</p>
    </div>

    <div class="es-settings-shell">

        <nav class="es-main-tabs">
            @foreach($tabs as $key => $label)
                <a
                    href="{{ route('admin.site-settings.index', [
                        'tab' => $key,
                        'sub' => array_key_first($subTabs[$key] ?? []) ?? 'general'
                    ]) }}"
                    class="es-main-tab {{ $tab === $key ? 'active' : '' }}"
                >
                    {{ $label }}
                </a>
            @endforeach
        </nav>

        <div class="es-subtabs-wrap">
            <nav class="es-subtabs">
                @foreach($currentSubs as $key => $label)

                    @php
                        $href = route('admin.site-settings.index', [
                            'tab' => $tab,
                            'sub' => $key,
                        ]);

                        if ($tab === 'payments') {
                            $href = match($key) {
                                'online'   => route('admin.payment-gateways.online.index'),
                                'offline'  => route('admin.payment-gateways.offline.index'),
                                'reviews'  => route('admin.payment-gateways.offline-payments.index'),
                                'wallet'   => route('admin.payment-gateways.wallet'),
                                'giftcard' => route('admin.payment-gateways.gift-card'),
                                'payout'   => route('admin.site-settings.payout.index'),
                                default    => $href,
                            };
                        }

                        if ($tab === 'ai') {
                            $href = match($key) {
                                'management' => route('admin.ai.index'),
                                'settings'   => route('admin.ai.settings'),
                                default      => $href,
                            };
                        }

                        if ($tab === 'themes' && $key === 'manager') {
                            $href = route('admin.themes.index');
                        }
                    @endphp

                    <a
                        href="{{ $href }}"
                        class="es-subtab {{ $sub === $key ? 'active' : '' }}"
                    >
                        {{ $label }}
                    </a>

                @endforeach
            </nav>
        </div>

        <div class="es-settings-body">

            <h2 class="es-settings-section-title">
                {{ $currentSubs[$sub] ?? $tabs[$tab] }}
            </h2>

            @if($tab === 'payments' && $sub === 'overview')

                <p class="es-settings-section-copy">
                    Manage Central payment collection, wallet, gift cards and payout infrastructure.
                </p>

                <div class="es-settings-grid">

                    <a class="es-setting-card" href="{{ route('admin.payment-gateways.online.index') }}">
                        <h3>Online Payment Gateways</h3>
                        <p>Manage Central online payment providers and their configuration.</p>
                        <span class="es-open">Open Online Gateways →</span>
                    </a>

                    <a class="es-setting-card" href="{{ route('admin.payment-gateways.offline.index') }}">
                        <h3>Offline Payment Gateways</h3>
                        <p>Manage Central offline payment methods.</p>
                        <span class="es-open">Open Offline Gateways →</span>
                    </a>

                    <a class="es-setting-card" href="{{ route('admin.payment-gateways.offline-payments.index') }}">
                        <h3>Offline Payments</h3>
                        <p>Review submitted offline payment attempts.</p>
                        <span class="es-open">Open Payments →</span>
                    </a>

                    <a class="es-setting-card" href="{{ route('admin.payment-gateways.wallet') }}">
                        <h3>Wallet</h3>
                        <p>Manage the Central wallet payment system.</p>
                        <span class="es-open">Open Wallet →</span>
                    </a>

                    <a class="es-setting-card" href="{{ route('admin.payment-gateways.gift-card') }}">
                        <h3>Gift Cards</h3>
                        <p>Manage gift cards and wallet funding codes.</p>
                        <span class="es-open">Open Gift Cards →</span>
                    </a>

                    <a class="es-setting-card" href="{{ route('admin.site-settings.payout.index') }}">
                        <h3>Payout</h3>
                        <p>Manage Central payout methods and requests.</p>
                        <span class="es-open">Open Payout →</span>
                    </a>

                </div>

            @elseif($tab === 'ai' && $sub === 'overview')

                <p class="es-settings-section-copy">
                    Manage the existing Central Esubiz AI infrastructure.
                </p>

                <div class="es-settings-grid">

                    <a class="es-setting-card" href="{{ route('admin.ai.index') }}">
                        <h3>AI Management</h3>
                        <p>Providers, models, credits, pricing, routing and commercial AI configuration.</p>
                        <span class="es-open">Open AI Management →</span>
                    </a>

                    <a class="es-setting-card" href="{{ route('admin.ai.settings') }}">
                        <h3>AI Settings</h3>
                        <p>Manage global Esubiz AI settings and chat configuration.</p>
                        <span class="es-open">Open AI Settings →</span>
                    </a>

                </div>

            @elseif($tab === 'auth' && $sub === 'esubiz-sso')

                {{-- ESUBIZ_CENTRAL_SSO_AUTH_TAB_V1 --}}
                <p class="es-settings-section-copy">
                    Control whether Esubiz SSO is available to websites connected to the Central Esubiz platform.
                </p>

                <div class="es-settings-grid">

                    
{{-- ESUBIZ_CENTRAL_SSO_UI_CLEANUP_V2 --}}
<div>
                <h5 class="mb-1">Esubiz SSO Control</h5>
                <p class="text-muted mb-0">
                    Control Esubiz account authentication across Core and external applications.
                </p>
            </div>
        </div>

        <form
            method="POST"
            action="{{ route('admin.site-settings.auth.esubiz-sso.update') }}"
        >
            @csrf

            <div class="es-sso-toggle-grid">
            <div class="es-sso-toggle-card">
                <div class="es-sso-toggle-row">
                    <div class="es-sso-toggle-copy">
                        <div class="es-sso-toggle-title">Enable Esubiz SSO</div>
                        <p class="es-sso-toggle-description">
                            Master authentication switch for Continue with Esubiz.
                        </p>
                    </div>

                    <label class="es-sso-switch" for="esubiz_sso_enabled">
                        <input
                            type="checkbox"
                            role="switch"
                            id="esubiz_sso_enabled"
                            name="enabled"
                            value="1"
                            {{ $esubizSsoEnabled ? 'checked' : '' }}
                        >
                        <span class="es-sso-switch-track"></span>
                    </label>
                </div>
            </div>

            <div class="es-sso-toggle-card">
                <div class="es-sso-toggle-row">
                    <div class="es-sso-toggle-copy">
                        <div class="es-sso-toggle-title">Core Applications</div>
                        <p class="es-sso-toggle-description">
                            Allow Esubiz SSO for Esubiz Core installations.
                        </p>
                    </div>

                    <label class="es-sso-switch" for="esubiz_sso_core_enabled">
                        <input
                            type="checkbox"
                            role="switch"
                            id="esubiz_sso_core_enabled"
                            name="core_enabled"
                            value="1"
                            {{ $esubizSsoCoreEnabled ? 'checked' : '' }}
                        >
                        <span class="es-sso-switch-track"></span>
                    </label>
                </div>
            </div>

            <div class="es-sso-toggle-card">
                <div class="es-sso-toggle-row">
                    <div class="es-sso-toggle-copy">
                        <div class="es-sso-toggle-title">External Applications</div>
                    </div>

                    <label class="es-sso-switch" for="esubiz_sso_external_enabled">
                        <input
                            type="checkbox"
                            role="switch"
                            id="esubiz_sso_external_enabled"
                            name="external_enabled"
                            value="1"
                            {{ $esubizSsoExternalEnabled ? 'checked' : '' }}
                        >
                        <span class="es-sso-switch-track"></span>
                    </label>
                </div>

                <div class="small text-muted mt-1">
                    Allow developer-owned applications to authenticate Esubiz users.
                </div>
            </div>

            </div>

            <button
                type="submit"
                class="btn px-4 fw-semibold"
                style="background:#1464f4;color:#ffffff;border-color:#1464f4;border-radius:10px;"
            >
                Save SSO Settings
            </button>
        </form>
    </div>
</div>

@elseif($tab === 'themes' && $sub === 'overview')

                <p class="es-settings-section-copy">
                    Manage Central marketplace theme administration.
                </p>

                <div class="es-settings-grid">
                    <a class="es-setting-card" href="{{ route('admin.themes.index') }}">
                        <h3>Theme Manager</h3>
                        <p>Open the existing Central marketplace theme management interface.</p>
                        <span class="es-open">Open Theme Manager →</span>
                    </a>
                </div>

            @elseif($tab === 'website-wizard')

                <p class="es-settings-section-copy">
                    Control the progress presentation and display text used
                    during User website deployment and Developer compilation.
                    These settings do not change actual backend processing.
                </p>

                @if(session('success'))
                    <div class="alert alert-success mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                <form
                    method="POST"
                    action="{{ route('admin.site-settings.website-wizard.update') }}"
                >
                    @csrf

                    <div class="es-settings-grid">

                        <div class="es-setting-card">
                            <h3>User Website Deployment</h3>

                            <p class="mt-2 text-muted">
                                Configure the SaaS website deployment
                                progress presentation. The percentage shown
                                to the user will come from the real backend
                                deployment progress.
                            </p>

                            <div class="mb-3 mt-3">
                                <label class="form-label fw-semibold">
                                    Minimum Progress Display Time
                                </label>

                                <div class="input-group">
                                    <input
                                        type="number"
                                        name="user_progress_minimum_seconds"
                                        min="0"
                                        max="60"
                                        required
                                        class="form-control"
                                        value="{{ old(
                                            'user_progress_minimum_seconds',
                                            $wizardSettings[
                                                'wizard.user.progress_minimum_seconds'
                                            ]
                                        ) }}"
                                    >
                                    <span class="input-group-text">
                                        seconds
                                    </span>
                                </div>

                                <small class="text-muted d-block mt-2">
                                    Presentation only. It never slows or
                                    controls the real deployment process.
                                </small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    Progress Title
                                </label>
                                <input
                                    class="form-control"
                                    name="user_progress_title"
                                    maxlength="120"
                                    required
                                    value="{{ old(
                                        'user_progress_title',
                                        $wizardSettings[
                                            'wizard.user.progress_title'
                                        ]
                                    ) }}"
                                >
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    Progress Texts by Real Percentage
                                </label>

                                <small class="text-muted d-block mb-3">
                                    Each text is displayed only when the real
                                    backend deployment percentage is inside
                                    its configured range.
                                </small>

                                <div class="mb-3">
                                    <label class="form-label">0–19%</label>
                                    <textarea
                                        class="form-control"
                                        name="user_progress_text_0_19"
                                        rows="2"
                                        maxlength="500"
                                        required
                                    >{{ old(
                                        'user_progress_text_0_19',
                                        $wizardSettings[
                                            'wizard.user.progress_text.0_19'
                                        ]
                                    ) }}</textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">20–39%</label>
                                    <textarea
                                        class="form-control"
                                        name="user_progress_text_20_39"
                                        rows="2"
                                        maxlength="500"
                                        required
                                    >{{ old(
                                        'user_progress_text_20_39',
                                        $wizardSettings[
                                            'wizard.user.progress_text.20_39'
                                        ]
                                    ) }}</textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">40–59%</label>
                                    <textarea
                                        class="form-control"
                                        name="user_progress_text_40_59"
                                        rows="2"
                                        maxlength="500"
                                        required
                                    >{{ old(
                                        'user_progress_text_40_59',
                                        $wizardSettings[
                                            'wizard.user.progress_text.40_59'
                                        ]
                                    ) }}</textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">60–79%</label>
                                    <textarea
                                        class="form-control"
                                        name="user_progress_text_60_79"
                                        rows="2"
                                        maxlength="500"
                                        required
                                    >{{ old(
                                        'user_progress_text_60_79',
                                        $wizardSettings[
                                            'wizard.user.progress_text.60_79'
                                        ]
                                    ) }}</textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">80–99%</label>
                                    <textarea
                                        class="form-control"
                                        name="user_progress_text_80_99"
                                        rows="2"
                                        maxlength="500"
                                        required
                                    >{{ old(
                                        'user_progress_text_80_99',
                                        $wizardSettings[
                                            'wizard.user.progress_text.80_99'
                                        ]
                                    ) }}</textarea>
                                </div>

                                <div>
                                    <label class="form-label">100%</label>
                                    <textarea
                                        class="form-control"
                                        name="user_progress_text_100"
                                        rows="2"
                                        maxlength="500"
                                        required
                                    >{{ old(
                                        'user_progress_text_100',
                                        $wizardSettings[
                                            'wizard.user.progress_text.100'
                                        ]
                                    ) }}</textarea>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    Success Title
                                </label>
                                <input
                                    class="form-control"
                                    name="user_success_title"
                                    maxlength="120"
                                    required
                                    value="{{ old(
                                        'user_success_title',
                                        $wizardSettings[
                                            'wizard.user.success_title'
                                        ]
                                    ) }}"
                                >
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    Success Text
                                </label>
                                <textarea
                                    class="form-control"
                                    name="user_success_text"
                                    rows="2"
                                    maxlength="500"
                                    required
                                >{{ old(
                                    'user_success_text',
                                    $wizardSettings[
                                        'wizard.user.success_text'
                                    ]
                                ) }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    Failure Title
                                </label>
                                <input
                                    class="form-control"
                                    name="user_failure_title"
                                    maxlength="120"
                                    required
                                    value="{{ old(
                                        'user_failure_title',
                                        $wizardSettings[
                                            'wizard.user.failure_title'
                                        ]
                                    ) }}"
                                >
                            </div>

                            <div>
                                <label class="form-label fw-semibold">
                                    Failure Text
                                </label>
                                <textarea
                                    class="form-control"
                                    name="user_failure_text"
                                    rows="2"
                                    maxlength="500"
                                    required
                                >{{ old(
                                    'user_failure_text',
                                    $wizardSettings[
                                        'wizard.user.failure_text'
                                    ]
                                ) }}</textarea>
                            </div>

                            {{-- ESUBIZ_USER_WIZARD_BUTTON_FIELDS_V1 --}}
                            <div class="mb-3 mt-3">
                                <label class="form-label fw-semibold">
                                    Manage Website Button
                                </label>
                                <input
                                    type="text"
                                    class="form-control"
                                    name="user_manage_website_label"
                                    maxlength="60"
                                    required
                                    value="{{ old(
                                        'user_manage_website_label',
                                        $wizardSettings[
                                            'wizard.user.manage_website_label'
                                        ] ?? 'Manage Website'
                                    ) }}"
                                >
                                <div class="form-text">
                                    Button shown after a successful User Mode website deployment.
                                </div>
                            </div>

                            <div>
                                <label class="form-label fw-semibold">
                                    Esubiz Dashboard Button
                                </label>
                                <input
                                    type="text"
                                    class="form-control"
                                    name="user_dashboard_label"
                                    maxlength="60"
                                    required
                                    value="{{ old(
                                        'user_dashboard_label',
                                        $wizardSettings[
                                            'wizard.user.dashboard_label'
                                        ] ?? 'Esubiz Dashboard'
                                    ) }}"
                                >
                                <div class="form-text">
                                    Dashboard button shown after User Mode website deployment.
                                </div>
                            </div>

                            {{-- ESUBIZ_USER_WIZARD_REDEPLOY_FIELD_V1 --}}
                            <div class="mt-3">
                                <label class="form-label fw-semibold">
                                    Redeploy Button
                                </label>
                                <input
                                    type="text"
                                    class="form-control"
                                    name="user_redeploy_label"
                                    maxlength="60"
                                    required
                                    value="{{ old(
                                        'user_redeploy_label',
                                        $wizardSettings[
                                            'wizard.user.redeploy_label'
                                        ] ?? 'Redeploy'
                                    ) }}"
                                >
                                <div class="form-text">
                                    Button shown when User Mode website creation fails.
                                </div>
                            </div>


                        </div>

                        <div class="es-setting-card">
                            <h3>Developer Compilation</h3>

                            <p class="mt-2 text-muted">
                                Configure Developer compilation progress.
                                The percentage displayed in the compilation
                                modal will come from the real backend compiler
                                progress.
                            </p>

                            <div class="mb-3 mt-3">
                                <label class="form-label fw-semibold">
                                    Minimum Progress Display Time
                                </label>

                                <div class="input-group">
                                    <input
                                        type="number"
                                        name="developer_progress_minimum_seconds"
                                        min="0"
                                        max="60"
                                        required
                                        class="form-control"
                                        value="{{ old(
                                            'developer_progress_minimum_seconds',
                                            $wizardSettings[
                                                'wizard.developer.progress_minimum_seconds'
                                            ]
                                        ) }}"
                                    >
                                    <span class="input-group-text">
                                        seconds
                                    </span>
                                </div>

                                <small class="text-muted d-block mt-2">
                                    Presentation only. It never slows or
                                    controls the real compilation process.
                                </small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    Progress Title
                                </label>
                                <input
                                    class="form-control"
                                    name="developer_progress_title"
                                    maxlength="120"
                                    required
                                    value="{{ old(
                                        'developer_progress_title',
                                        $wizardSettings[
                                            'wizard.developer.progress_title'
                                        ]
                                    ) }}"
                                >
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    Progress Texts by Real Percentage
                                </label>

                                <small class="text-muted d-block mb-3">
                                    Each text is displayed only when the real
                                    backend compiler percentage is inside its
                                    configured range.
                                </small>

                                <div class="mb-3">
                                    <label class="form-label">0–19%</label>
                                    <textarea
                                        class="form-control"
                                        name="developer_progress_text_0_19"
                                        rows="2"
                                        maxlength="500"
                                        required
                                    >{{ old(
                                        'developer_progress_text_0_19',
                                        $wizardSettings[
                                            'wizard.developer.progress_text.0_19'
                                        ]
                                    ) }}</textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">20–39%</label>
                                    <textarea
                                        class="form-control"
                                        name="developer_progress_text_20_39"
                                        rows="2"
                                        maxlength="500"
                                        required
                                    >{{ old(
                                        'developer_progress_text_20_39',
                                        $wizardSettings[
                                            'wizard.developer.progress_text.20_39'
                                        ]
                                    ) }}</textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">40–59%</label>
                                    <textarea
                                        class="form-control"
                                        name="developer_progress_text_40_59"
                                        rows="2"
                                        maxlength="500"
                                        required
                                    >{{ old(
                                        'developer_progress_text_40_59',
                                        $wizardSettings[
                                            'wizard.developer.progress_text.40_59'
                                        ]
                                    ) }}</textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">60–79%</label>
                                    <textarea
                                        class="form-control"
                                        name="developer_progress_text_60_79"
                                        rows="2"
                                        maxlength="500"
                                        required
                                    >{{ old(
                                        'developer_progress_text_60_79',
                                        $wizardSettings[
                                            'wizard.developer.progress_text.60_79'
                                        ]
                                    ) }}</textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">80–99%</label>
                                    <textarea
                                        class="form-control"
                                        name="developer_progress_text_80_99"
                                        rows="2"
                                        maxlength="500"
                                        required
                                    >{{ old(
                                        'developer_progress_text_80_99',
                                        $wizardSettings[
                                            'wizard.developer.progress_text.80_99'
                                        ]
                                    ) }}</textarea>
                                </div>

                                <div>
                                    <label class="form-label">100%</label>
                                    <textarea
                                        class="form-control"
                                        name="developer_progress_text_100"
                                        rows="2"
                                        maxlength="500"
                                        required
                                    >{{ old(
                                        'developer_progress_text_100',
                                        $wizardSettings[
                                            'wizard.developer.progress_text.100'
                                        ]
                                    ) }}</textarea>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    Success Title
                                </label>
                                <input
                                    class="form-control"
                                    name="developer_success_title"
                                    maxlength="120"
                                    required
                                    value="{{ old(
                                        'developer_success_title',
                                        $wizardSettings[
                                            'wizard.developer.success_title'
                                        ]
                                    ) }}"
                                >
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    Success Text
                                </label>
                                <textarea
                                    class="form-control"
                                    name="developer_success_text"
                                    rows="2"
                                    maxlength="500"
                                    required
                                >{{ old(
                                    'developer_success_text',
                                    $wizardSettings[
                                        'wizard.developer.success_text'
                                    ]
                                ) }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    Failure Title
                                </label>
                                <input
                                    class="form-control"
                                    name="developer_failure_title"
                                    maxlength="120"
                                    required
                                    value="{{ old(
                                        'developer_failure_title',
                                        $wizardSettings[
                                            'wizard.developer.failure_title'
                                        ]
                                    ) }}"
                                >
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    Failure Text
                                </label>
                                <textarea
                                    class="form-control"
                                    name="developer_failure_text"
                                    rows="2"
                                    maxlength="500"
                                    required
                                >{{ old(
                                    'developer_failure_text',
                                    $wizardSettings[
                                        'wizard.developer.failure_text'
                                    ]
                                ) }}</textarea>
                            </div>

                            
                    <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    Proceed to Payment Button
                                </label>
                                <input
                                    class="form-control"
                                    name="developer_payment_label"
                                    maxlength="60"
                                    required
                                    value="{{ old(
                                        'developer_payment_label',
                                        $wizardSettings[
                                            'wizard.developer.payment_label'
                                        ]
                                    ) }}"
                                >
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">
                                    Recompile Button
                                </label>
                                <input
                                    class="form-control"
                                    name="developer_recompile_label"
                                    maxlength="60"
                                    required
                                    value="{{ old(
                                        'developer_recompile_label',
                                        $wizardSettings[
                                            'wizard.developer.recompile_label'
                                        ]
                                    ) }}"
                                >
                            </div>

                            <div>
                                <label class="form-label fw-semibold">
                                    Dashboard Button
                                </label>
                                <input
                                    class="form-control"
                                    name="developer_dashboard_label"
                                    maxlength="60"
                                    required
                                    value="{{ old(
                                        'developer_dashboard_label',
                                        $wizardSettings[
                                            'wizard.developer.dashboard_label'
                                        ]
                                    ) }}"
                                >
                            </div>
                        </div>

                    </div>

                    <div class="mt-4">
                        <button
                            type="submit"
                            class="btn px-4 fw-semibold"
                            style="background:#1464f4;color:#ffffff;border-color:#1464f4;border-radius:10px;"
                        >
                            Save Website Wizard Settings
                        </button>
                    </div>
                </form>


            @elseif($tab === 'notices')

                {{-- ESUBIZ_DASHBOARD_NOTICES_ADMIN_UI_V6 --}}
                <style>
                    .es-notice-shell-v6 {
                        display:grid;
                        gap:22px;
                    }

                    .es-notice-card-v6 {
                        background:#fff;
                        border:1px solid #e7ebf0;
                        border-radius:18px;
                        box-shadow:0 8px 30px rgba(15,23,42,.055);
                        overflow:visible;
                    }

                    .es-notice-head-v6 {
                        display:flex;
                        align-items:flex-start;
                        justify-content:space-between;
                        gap:20px;
                        padding:22px 24px;
                        border-bottom:1px solid #edf0f4;
                    }

                    .es-notice-head-v6 h3 {
                        margin:0;
                        color:#0b1f3a;
                        font-size:18px;
                        font-weight:800;
                    }

                    .es-notice-head-v6 p {
                        margin:6px 0 0;
                        max-width:760px;
                        color:#6b7280;
                        font-size:13px;
                        line-height:1.65;
                    }

                    .es-notice-body-v6 {
                        padding:24px;
                    }

                    .es-notice-grid-v6 {
                        display:grid;
                        grid-template-columns:repeat(3, minmax(0, 1fr));
                        gap:18px;
                    }

                    .es-notice-span-2-v6 {
                        grid-column:span 2;
                    }

                    .es-notice-span-3-v6 {
                        grid-column:1 / -1;
                    }

                    .es-notice-field-v6 {
                        min-width:0;
                    }

                    .es-notice-field-v6 > label {
                        display:block;
                        margin:0 0 8px;
                        color:#24364d;
                        font-size:12px;
                        font-weight:800;
                    }

                    .es-notice-field-v6 input,
                    .es-notice-field-v6 select,
                    .es-notice-field-v6 textarea {
                        width:100%;
                        min-height:44px;
                        padding:10px 12px;
                        border:1px solid #d8dee8;
                        border-radius:11px;
                        background:#fff;
                        color:#172033;
                        font-size:13px;
                        outline:0;
                        transition:
                            border-color .16s ease,
                            box-shadow .16s ease;
                    }

                    .es-notice-field-v6 textarea {
                        min-height:145px;
                        resize:vertical;
                        line-height:1.6;
                    }

                    .es-notice-field-v6 input:focus,
                    .es-notice-field-v6 select:focus,
                    .es-notice-field-v6 textarea:focus {
                        border-color:#0b1f3a;
                        box-shadow:0 0 0 3px rgba(11,31,58,.09);
                    }

                    .es-notice-help-v6 {
                        margin-top:6px;
                        color:#8892a0;
                        font-size:11px;
                        line-height:1.5;
                    }

                    .es-notice-targets-v6 {
                        display:grid;
                        grid-template-columns:repeat(3, minmax(0, 1fr));
                        gap:10px;
                        max-height:280px;
                        padding:12px;
                        overflow:auto;
                        border:1px solid #e1e6ec;
                        border-radius:12px;
                        background:#fafbfc;
                    }

                    .es-notice-target-v6 {
                        display:flex;
                        gap:9px;
                        align-items:flex-start;
                        padding:10px;
                        border:1px solid #e7ebef;
                        border-radius:10px;
                        background:#fff;
                        cursor:pointer;
                    }

                    .es-notice-target-v6 input {
                        width:auto;
                        min-height:auto;
                        margin-top:3px;
                    }

                    .es-notice-target-v6 strong,
                    .es-notice-target-v6 small {
                        display:block;
                    }

                    .es-notice-target-v6 strong {
                        color:#24364d;
                        font-size:12px;
                    }

                    .es-notice-target-v6 small {
                        margin-top:2px;
                        color:#8a94a2;
                        font-size:10px;
                    }

                    .es-notice-upload-v6 {
                        position:relative;
                        min-height:160px;
                        overflow:hidden;
                        border:1px dashed #cbd3df;
                        border-radius:14px;
                        background:#fafbfc;
                    }

                    .es-notice-upload-input-v6 {
                        position:absolute;
                        z-index:5;
                        inset:0;
                        width:100%;
                        height:100%;
                        opacity:0;
                        cursor:pointer;
                    }

                    .es-notice-upload-empty-v6 {
                        display:flex;
                        min-height:160px;
                        padding:24px;
                        flex-direction:column;
                        align-items:center;
                        justify-content:center;
                        text-align:center;
                        pointer-events:none;
                    }

                    .es-notice-upload-icon-v6 {
                        display:flex;
                        width:42px;
                        height:42px;
                        margin-bottom:10px;
                        align-items:center;
                        justify-content:center;
                        border-radius:11px;
                        background:#edf2f7;
                        color:#0b1f3a;
                        font-size:20px;
                        font-weight:700;
                    }

                    .es-notice-upload-empty-v6 strong {
                        color:#24364d;
                        font-size:13px;
                    }

                    .es-notice-upload-empty-v6 small {
                        margin-top:4px;
                        color:#8a94a2;
                        font-size:11px;
                    }

                    .es-notice-preview-v6 {
                        position:relative;
                        display:none;
                        min-height:190px;
                        background:#f4f6f8;
                    }

                    .es-notice-preview-v6.is-visible {
                        display:block;
                    }

                    .es-notice-preview-v6 img {
                        display:block;
                        width:100%;
                        aspect-ratio:2 / 1;
                        height:auto;
                        max-height:360px;
                        object-fit:cover;
                        object-position:center;
                    }

                    /*
                     * ESUBIZ_DASHBOARD_NOTICE_IMAGE_STANDARD_V7
                     *
                     * Dashboard Notice media standard:
                     * recommended source = 1200 x 600 px (2:1).
                     *
                     * Admin preview deliberately uses the same
                     * aspect ratio as the Core dashboard.
                     */
                    .es-notice-upload-v6,
                    .es-notice-preview-v6 {
                        width:100%;
                    }

                    .es-notice-remove-image-v6 {
                        position:absolute;
                        z-index:10;
                        top:12px;
                        right:12px;
                        padding:8px 12px;
                        border:1px solid #e3e7ec;
                        border-radius:9px;
                        background:rgba(255,255,255,.96);
                        color:#b42318;
                        font-size:11px;
                        font-weight:800;
                        cursor:pointer;
                        box-shadow:0 5px 15px rgba(15,23,42,.10);
                    }

                    .es-notice-form-actions-v6 {
                        display:flex;
                        align-items:center;
                        justify-content:space-between;
                        gap:14px;
                        margin-top:22px;
                        padding-top:18px;
                        border-top:1px solid #edf0f4;
                    }

                    .es-notice-form-message-v6 {
                        color:#667085;
                        font-size:12px;
                        font-weight:600;
                    }

                    .es-notice-action-buttons-v6 {
                        display:flex;
                        gap:9px;
                    }

                    .es-notice-save-v6,
                    .es-notice-cancel-v6 {
                        display:inline-flex;
                        min-height:42px;
                        padding:10px 17px;
                        align-items:center;
                        justify-content:center;
                        border-radius:10px;
                        font-size:12px;
                        font-weight:800;
                        cursor:pointer;
                    }

                    .es-notice-save-v6 {
                        border:1px solid #0b1f3a;
                        background:#0b1f3a;
                        color:#fff;
                        box-shadow:0 7px 18px rgba(11,31,58,.16);
                    }

                    .es-notice-save-v6:disabled {
                        opacity:.55;
                        cursor:not-allowed;
                    }

                    .es-notice-cancel-v6 {
                        display:none;
                        border:1px solid #dbe1e8;
                        background:#fff;
                        color:#344054;
                    }

                    .es-notice-cancel-v6.is-visible {
                        display:inline-flex;
                    }

                    .es-notice-table-wrap-v6 {
                        width:100%;
                        overflow:visible;
                    }

                    .es-notice-table-scroll-v6 {
                        width:100%;
                        overflow-x:auto;
                    }

                    .es-notice-table-v6 {
                        width:100%;
                        border-collapse:collapse;
                    }

                    .es-notice-table-v6 th {
                        padding:12px 14px;
                        border-bottom:1px solid #e9edf2;
                        color:#7b8491;
                        font-size:10px;
                        font-weight:800;
                        letter-spacing:.055em;
                        text-align:left;
                        text-transform:uppercase;
                        white-space:nowrap;
                    }

                    .es-notice-table-v6 td {
                        padding:14px;
                        border-bottom:1px solid #eff2f5;
                        color:#344054;
                        font-size:12px;
                        vertical-align:middle;
                    }

                    .es-notice-title-v6 {
                        color:#172033;
                        font-size:13px;
                        font-weight:800;
                    }

                    .es-notice-copy-v6 {
                        max-width:330px;
                        margin-top:3px;
                        overflow:hidden;
                        color:#7b8491;
                        font-size:11px;
                        text-overflow:ellipsis;
                        white-space:nowrap;
                    }

                    .es-notice-badge-v6 {
                        display:inline-flex;
                        padding:5px 8px;
                        align-items:center;
                        border-radius:999px;
                        font-size:10px;
                        font-weight:800;
                        text-transform:capitalize;
                    }

                    .es-notice-badge-v6.published {
                        background:#e8f7ef;
                        color:#107044;
                    }

                    .es-notice-badge-v6.disabled {
                        background:#f2f4f7;
                        color:#667085;
                    }

                    .es-notice-badge-v6.draft {
                        background:#fff4dd;
                        color:#9a6700;
                    }

                    .es-notice-menu-wrap-v6 {
                        position:relative;
                        display:inline-flex;
                    }

                    .es-notice-menu-trigger-v6 {
                        display:flex;
                        width:36px;
                        height:36px;
                        padding:0;
                        align-items:center;
                        justify-content:center;
                        border:1px solid #e1e6ec;
                        border-radius:10px;
                        background:#fff;
                        color:#344054;
                        font-size:20px;
                        font-weight:800;
                        line-height:1;
                        cursor:pointer;
                        transition:
                            background .15s ease,
                            border-color .15s ease;
                    }

                    .es-notice-menu-trigger-v6:hover {
                        border-color:#c7ced8;
                        background:#f8fafc;
                    }

                    .es-notice-menu-v6 {
                        position:absolute;
                        z-index:200;
                        top:42px;
                        right:0;
                        display:none;
                        width:178px;
                        padding:6px;
                        border:1px solid #e1e6ec;
                        border-radius:12px;
                        background:#fff;
                        box-shadow:0 16px 38px rgba(15,23,42,.16);
                    }

                    .es-notice-menu-v6.is-open {
                        display:block;
                    }

                    .es-notice-menu-item-v6 {
                        display:flex;
                        width:100%;
                        min-height:38px;
                        padding:9px 10px;
                        gap:9px;
                        align-items:center;
                        border:0;
                        border-radius:8px;
                        background:transparent;
                        color:#344054;
                        font-size:12px;
                        font-weight:700;
                        text-align:left;
                        cursor:pointer;
                    }

                    .es-notice-menu-item-v6:hover {
                        background:#f5f7fa;
                    }

                    .es-notice-menu-item-v6.danger {
                        color:#b42318;
                    }

                    .es-notice-menu-divider-v6 {
                        height:1px;
                        margin:5px 4px;
                        background:#edf0f4;
                    }

                    .es-notice-empty-v6 {
                        padding:44px 20px;
                        color:#7d8794;
                        font-size:13px;
                        text-align:center;
                    }

                    .es-notice-pagination-v6 {
                        display:flex;
                        padding:16px 20px;
                        align-items:center;
                        justify-content:space-between;
                        gap:14px;
                        border-top:1px solid #edf0f4;
                    }

                    .es-notice-page-meta-v6 {
                        color:#7b8491;
                        font-size:11px;
                        font-weight:600;
                    }

                    .es-notice-pages-v6 {
                        display:flex;
                        gap:8px;
                        align-items:center;
                    }

                    .es-notice-page-btn-v6 {
                        min-height:36px;
                        padding:8px 13px;
                        border:1px solid #dfe4ea;
                        border-radius:9px;
                        background:#fff;
                        color:#344054;
                        font-size:11px;
                        font-weight:800;
                        cursor:pointer;
                    }

                    .es-notice-page-btn-v6:disabled {
                        opacity:.38;
                        cursor:not-allowed;
                    }

                    .es-notice-modal-v6 {
                        position:fixed;
                        z-index:99990;
                        inset:0;
                        display:none;
                        padding:24px;
                        align-items:center;
                        justify-content:center;
                        background:rgba(10,20,35,.55);
                        backdrop-filter:blur(3px);
                    }

                    .es-notice-modal-v6.is-open {
                        display:flex;
                    }

                    .es-notice-modal-card-v6 {
                        width:min(720px, 100%);
                        max-height:88vh;
                        overflow:auto;
                        border-radius:18px;
                        background:#fff;
                        box-shadow:0 28px 70px rgba(0,0,0,.24);
                    }

                    .es-notice-modal-head-v6 {
                        display:flex;
                        padding:19px 21px;
                        align-items:flex-start;
                        justify-content:space-between;
                        gap:20px;
                        border-bottom:1px solid #edf0f4;
                    }

                    .es-notice-modal-head-v6 h4 {
                        margin:0;
                        color:#0b1f3a;
                        font-size:17px;
                        font-weight:800;
                    }

                    .es-notice-modal-close-v6 {
                        display:flex;
                        width:34px;
                        height:34px;
                        padding:0;
                        align-items:center;
                        justify-content:center;
                        border:1px solid #e1e6ec;
                        border-radius:9px;
                        background:#fff;
                        color:#667085;
                        font-size:19px;
                        cursor:pointer;
                    }

                    .es-notice-modal-body-v6 {
                        padding:21px;
                    }

                    .es-notice-view-image-v6 {
                        display:none;
                        width:100%;
                        aspect-ratio:2 / 1;
                        height:auto;
                        max-height:360px;
                        margin-bottom:18px;
                        border-radius:13px;
                        object-fit:cover;
                        object-position:center;
                    }

                    .es-notice-view-image-v6.is-visible {
                        display:block;
                    }

                    .es-notice-view-meta-v6 {
                        display:flex;
                        margin-bottom:16px;
                        gap:8px;
                        flex-wrap:wrap;
                    }

                    .es-notice-view-pill-v6 {
                        padding:5px 8px;
                        border-radius:999px;
                        background:#f1f4f7;
                        color:#536071;
                        font-size:10px;
                        font-weight:800;
                    }

                    .es-notice-view-message-v6 {
                        color:#344054;
                        font-size:13px;
                        line-height:1.75;
                        overflow-wrap:anywhere;
                    }

                    .es-notice-view-message-v6 img {
                        max-width:100%;
                    }

                    @media(max-width:991px) {
                        .es-notice-grid-v6 {
                            grid-template-columns:repeat(2, minmax(0, 1fr));
                        }

                        .es-notice-span-3-v6 {
                            grid-column:1 / -1;
                        }

                        .es-notice-targets-v6 {
                            grid-template-columns:repeat(2, minmax(0, 1fr));
                        }
                    }

                    @media(max-width:767px) {
                        .es-notice-grid-v6,
                        .es-notice-targets-v6 {
                            grid-template-columns:1fr;
                        }

                        .es-notice-span-2-v6,
                        .es-notice-span-3-v6 {
                            grid-column:auto;
                        }

                        .es-notice-head-v6,
                        .es-notice-form-actions-v6,
                        .es-notice-pagination-v6 {
                            align-items:stretch;
                            flex-direction:column;
                        }

                        .es-notice-action-buttons-v6 {
                            width:100%;
                        }

                        .es-notice-save-v6,
                        .es-notice-cancel-v6 {
                            flex:1;
                        }
                    }
                </style>

                <div class="es-notice-shell-v6">

                    <div class="es-notice-card-v6">
                        <div class="es-notice-head-v6">
                            <div>
                                <h3 id="esNoticeFormHeadingV6">
                                    Add Dashboard Notice
                                </h3>
                                <p>
                                    Create or edit a Central notice delivered to
                                    SaaS Core, off-server Core, or both.
                                    The same form and persistence rules are used
                                    for both Add and Edit.
                                </p>
                            </div>
                        </div>

                        <div class="es-notice-body-v6">
                            <form
                                id="esDashboardNoticeFormV6"
                                data-create-url="{{ route('admin.site-settings.dashboard-notices.store') }}"
                                data-update-base="{{ url('/admin/site-settings/dashboard-notices') }}"
                                enctype="multipart/form-data"
                            >
                                @csrf

                                <input
                                    type="hidden"
                                    id="esNoticeEditingIdV6"
                                    value=""
                                >

                                <input
                                    type="hidden"
                                    name="remove_image"
                                    id="esNoticeRemoveImageV6"
                                    value="0"
                                >

                                <div class="es-notice-grid-v6">

                                    <div class="es-notice-field-v6 es-notice-span-2-v6">
                                        <label for="esNoticeTitleV6">
                                            Notice Title
                                        </label>

                                        <input
                                            id="esNoticeTitleV6"
                                            type="text"
                                            name="title"
                                            maxlength="160"
                                            placeholder="Enter notice title"
                                            required
                                        >
                                    </div>

                                    <div class="es-notice-field-v6">
                                        <label for="esNoticeStatusV6">
                                            Status
                                        </label>

                                        <select
                                            id="esNoticeStatusV6"
                                            name="status"
                                            required
                                        >
                                            <option value="draft">Draft</option>
                                            <option value="published">Active</option>
                                            <option value="disabled">Inactive</option>
                                        </select>
                                    </div>

                                    <div class="es-notice-field-v6">
                                        <label>
                                            Delivery
                                        </label>

                                        {{-- ESUBIZ_DASHBOARD_NOTICE_DELIVERY_MULTISELECT_V18 --}}

                                        {{-- ESUBIZ_NOTICE_LEGACY_DELIVERY_UI_REMOVED_V34 --}}
{{-- ESUBIZ_NOTICE_DELIVERY_COMPAT_ID_FIX_V37 --}}
<input
    type="hidden"
    name="delivery_scope"
    id="esNoticeDeliveryScopeV6"
    value="all"
>

                                        <div
                                            class="es-notice-delivery-multi-v18"
                                            id="esNoticeDeliveryMultiV18"
                                        >
                                            <button
                                                type="button"
                                                class="es-notice-delivery-trigger-v18"
                                                id="esNoticeDeliveryTriggerV18"
                                                aria-expanded="false"
                                            >
                                                <span
                                                    id="esNoticeDeliverySummaryV18"
                                                    class="es-notice-delivery-summary-v18"
                                                >
                                                    SaaS Core + Off-server Core
                                                </span>

                                                <span
                                                    class="es-notice-delivery-chevron-v18"
                                                    aria-hidden="true"
                                                >
                                                    &#8964;
                                                </span>
                                            </button>

                                            <div
                                                class="es-notice-delivery-panel-v18"
                                                id="esNoticeDeliveryPanelV18"
                                                hidden
                                            >
                                                <label class="es-notice-delivery-option-v18">
                                                    <input
                                                        type="checkbox"
                                                        name="delivery_channels[]"
                                                        value="saas"
                                                        checked
                                                    >

                                                    <span>
                                                        <strong>SaaS Core</strong>
                                                        <small>
                                                            Hosted Esubiz Core websites
                                                        </small>
                                                    </span>
                                                </label>

                                                <label class="es-notice-delivery-option-v18">
                                                    <input
                                                        type="checkbox"
                                                        name="delivery_channels[]"
                                                        value="off_server"
                                                        checked
                                                    >

                                                    <span>
                                                        <strong>Off-server Core</strong>
                                                        <small>
                                                            Externally hosted Core websites
                                                        </small>
                                                    </span>
                                                </label>

                                                <label class="es-notice-delivery-option-v18">
                                                    <input
                                                        type="checkbox"
                                                        name="delivery_channels[]"
                                                        value="central"
                                                    >

                                                    <span>
                                                        <strong>Esubiz Central</strong>
                                                        <small>
                                                            Central Esubiz users by role
                                                        </small>
                                                    </span>
                                                </label>
                                            </div>
                                        </div>

                                        <style>
                                            .es-notice-delivery-multi-v18 {
                                                position:relative;
                                                width:100%;
                                            }

                                            .es-notice-delivery-trigger-v18 {
                                                width:100%;
                                                min-height:46px;
                                                border:1px solid #d9e0ea;
                                                border-radius:10px;
                                                background:#fff;
                                                padding:10px 13px;
                                                display:flex;
                                                align-items:center;
                                                justify-content:space-between;
                                                gap:12px;
                                                text-align:left;
                                                cursor:pointer;
                                                font:inherit;
                                                color:#1f2937;
                                                transition:
                                                    border-color .18s ease,
                                                    box-shadow .18s ease;
                                            }

                                            .es-notice-delivery-trigger-v18:hover,
                                            .es-notice-delivery-trigger-v18:focus {
                                                border-color:#9aa9bf;
                                                box-shadow:
                                                    0 0 0 3px rgba(11,31,58,.06);
                                                outline:none;
                                            }

                                            .es-notice-delivery-summary-v18 {
                                                min-width:0;
                                                overflow:hidden;
                                                text-overflow:ellipsis;
                                                white-space:nowrap;
                                                font-weight:600;
                                            }

                                            .es-notice-delivery-chevron-v18 {
                                                flex:0 0 auto;
                                                font-size:18px;
                                                line-height:1;
                                                transition:transform .18s ease;
                                            }

                                            .es-notice-delivery-trigger-v18[
                                                aria-expanded="true"
                                            ] .es-notice-delivery-chevron-v18 {
                                                transform:rotate(180deg);
                                            }

                                            .es-notice-delivery-panel-v18 {
                                                position:absolute;
                                                z-index:80;
                                                top:calc(100% + 7px);
                                                left:0;
                                                right:0;
                                                padding:7px;
                                                border:1px solid #dde4ed;
                                                border-radius:12px;
                                                background:#fff;
                                                box-shadow:
                                                    0 16px 40px rgba(15,23,42,.14);
                                                max-height:280px;
                                                overflow:auto;
                                            }

                                            .es-notice-delivery-panel-v18[hidden] {
                                                display:none !important;
                                            }

                                            .es-notice-delivery-option-v18 {
                                                display:flex;
                                                align-items:flex-start;
                                                gap:10px;
                                                width:100%;
                                                padding:10px;
                                                border-radius:9px;
                                                cursor:pointer;
                                                margin:0;
                                                transition:background .15s ease;
                                            }

                                            .es-notice-delivery-option-v18:hover {
                                                background:#f5f7fa;
                                            }

                                            /*
                                             * ESUBIZ_NOTICE_COMPACT_MULTI_CHECKBOX_V23
                                             *
                                             * These remain normal independent
                                             * checkboxes, so Admin can select
                                             * any number of destinations,
                                             * tenants, roles and users.
                                             */
                                            .es-notice-delivery-option-v18 input[type="checkbox"] {
                                                appearance:auto;
                                                -webkit-appearance:checkbox;
                                                flex:0 0 13px;
                                                width:13px !important;
                                                height:13px !important;
                                                min-width:13px;
                                                min-height:13px;
                                                max-width:13px;
                                                max-height:13px;
                                                margin:2px 0 0 0 !important;
                                                padding:0 !important;
                                                accent-color:#0b1f3a;
                                                cursor:pointer;
                                            }

                                            .es-notice-delivery-option-v18 {
                                                gap:8px;
                                                padding:8px 10px;
                                            }

                                            .es-notice-delivery-option-v18 span {
                                                display:flex;
                                                flex-direction:column;
                                                min-width:0;
                                            }

                                            .es-notice-delivery-option-v18 strong {
                                                color:#182235;
                                                font-size:13px;
                                                line-height:1.3;
                                            }

                                            .es-notice-delivery-option-v18 small {
                                                display:block;
                                                margin-top:2px;
                                                color:#7a8596;
                                                font-size:11px;
                                                line-height:1.35;
                                            }

                                            @media (max-width: 767px) {
                                                .es-notice-delivery-panel-v18 {
                                                    max-height:230px;
                                                }

                                                .es-notice-delivery-trigger-v18 {
                                                    min-height:44px;
                                                }
                                            }
                                        </style>
                                    </div>

                                    <div class="es-notice-field-v6">
                                        <label for="esNoticeVariantV6">
                                            Notice Style
                                        </label>

                                        <select
                                            id="esNoticeVariantV6"
                                            name="variant"
                                            required
                                        >
                                            <option value="info">Info</option>
                                            <option value="success">Success</option>
                                            <option value="warning">Warning</option>
                                            <option value="danger">Danger</option>
                                            <option value="primary">Primary</option>
                                            <option value="secondary">Secondary</option>
                                        </select>
                                    </div>

                                    <div class="es-notice-field-v6">
                                        <label for="esNoticeDismissibleV6">
                                            Recipients Can Close Notice
                                        </label>

                                        <select
                                            id="esNoticeDismissibleV6"
                                            name="dismissible"
                                            required
                                        >
                                            <option value="1">Yes</option>
                                            <option value="0">No</option>
                                        </select>
                                    </div>

                                    <div class="es-notice-field-v6 es-notice-span-3-v6">
                                        <label for="esNoticeMessageInputV6">
                                            Message
                                        </label>

                                        <textarea
                                            id="esNoticeMessageInputV6"
                                            name="message"
                                            maxlength="10000"
                                            placeholder="Write the notice content. Trusted Central Admin HTML is supported."
                                            required
                                        ></textarea>

                                        <div class="es-notice-help-v6">
                                            HTML in this field is treated as trusted
                                            Central Admin-authored notice content.
                                        </div>
                                    </div>

                                    <div class="es-notice-field-v6">
                                        <label for="esNoticeTargetTypeV6">
                                            Audience
                                        </label>

                                        <select
                                            id="esNoticeTargetTypeV6"
                                            name="target_type"
                                            required
                                        >
                                            <option value="all">
                                                All SaaS Tenants
                                            </option>
                                            <option value="selected">
                                                Selected SaaS Tenants
                                            </option>
                                        </select>
                                    </div>

                                    {{-- ESUBIZ_DASHBOARD_NOTICE_CENTRAL_AUDIENCE_V19 --}}
                                    <div
                                        class="es-notice-field-v6"
                                        id="esNoticeCentralAudienceWrapV19"
                                        style="display:none;"
                                    >
                                        <label>
                                            Central Audience
                                        </label>

                                        <select
                                            id="esNoticeCentralTargetTypeV19"
                                            name="central_target_type"
                                        >
                                            <option value="all">
                                                All Central Roles
                                            </option>
                                            <option value="selected">
                                                Selected Central Roles
                                            </option>
                                        </select>
                                    </div>

                                    <div
                                        class="es-notice-field-v6 es-notice-span-2-v6"
                                        id="esNoticeCentralRolesWrapV19"
                                        style="display:none;"
                                    >
                                        <label>
                                            Selected Central Roles
                                        </label>

                                        <div
                                            class="es-notice-central-role-multi-v19"
                                            id="esNoticeCentralRoleMultiV19"
                                        >
                                            <button
                                                type="button"
                                                class="es-notice-delivery-trigger-v18"
                                                id="esNoticeCentralRoleTriggerV19"
                                                aria-expanded="false"
                                            >
                                                <span
                                                    id="esNoticeCentralRoleSummaryV19"
                                                    class="es-notice-delivery-summary-v18"
                                                >
                                                    Select Central roles
                                                </span>

                                                <span
                                                    class="es-notice-delivery-chevron-v18"
                                                    aria-hidden="true"
                                                >
                                                    &#8964;
                                                </span>
                                            </button>

                                            <div
                                                class="es-notice-delivery-panel-v18"
                                                id="esNoticeCentralRolePanelV19"
                                                hidden
                                            >
                                                @forelse($dashboardNoticeCentralRoles as $centralRole)
                                                    <label class="es-notice-delivery-option-v18">
                                                        <input
                                                            type="checkbox"
                                                            name="central_role_ids[]"
                                                            value="{{ $centralRole->id }}"
                                                            data-role-name="{{ $centralRole->name }}"
                                                            data-account-count="{{ $dashboardNoticeRoleMemberCountsV43->get($centralRole->id, 0) }}"
                                                        >

                                                        <span>
                                                            <strong>
                                                                {{ $centralRole->name }}
                                                            </strong>

                                                            <small>
                                                                {{ $centralRole->description
                                                                    ?: $centralRole->slug }}
                                                            </small>
                                                        </span>
                                                    </label>
                                                @empty
                                                    <div
                                                        style="
                                                            padding:12px;
                                                            color:#7a8596;
                                                            font-size:12px;
                                                        "
                                                    >
                                                        No active Central roles are available.
                                                    </div>
                                                @endforelse
                                            </div>
                                        </div>
                                    </div>


                                    
                                    <div
                                        class="es-notice-field-v6 es-notice-span-2-v6"
                                        id="esNoticeCentralUsersWrapV21"
                                        style="display:none;"
                                    >
                                        <label>
                                            Specific Central Users
                                        </label>

                                        <div
                                            class="es-notice-central-user-multi-v21"
                                            id="esNoticeCentralUserMultiV21"
                                        >
                                            <button
                                                type="button"
                                                class="es-notice-delivery-trigger-v18"
                                                id="esNoticeCentralUserTriggerV21"
                                                aria-expanded="false"
                                            >
                                                <span
                                                    id="esNoticeCentralUserSummaryV21"
                                                    class="es-notice-delivery-summary-v18"
                                                >
                                                    Select Central users
                                                </span>

                                                <span
                                                    class="es-notice-delivery-chevron-v18"
                                                    aria-hidden="true"
                                                >
                                                    &#8964;
                                                </span>
                                            </button>

                                            <div
                                                class="es-notice-delivery-panel-v18"
                                                id="esNoticeCentralUserPanelV21"
                                                hidden
                                            >
                                                @forelse($dashboardNoticeCentralUsers as $centralUser)
                                                    <label class="es-notice-delivery-option-v18">
                                                        <input
                                                            type="checkbox"
                                                            name="central_user_ids[]"
                                                            value="{{ $centralUser->id }}"
                                                            data-user-name="{{ $centralUser->name ?: $centralUser->email }}"
                                                            data-role-ids="{{ $dashboardNoticeUserRolesV43->get($centralUser->id, collect())->pluck('id')->implode(',') }}"
                                                        >

                                                        <span>
                                                            <strong>
                                                                {{ $centralUser->name ?: 'Unnamed User' }}
                                                            </strong>

                                                            <small>
                                                                {{ $centralUser->email }}
                                                            </small>

                                                            @php
                                                                $centralUserRolesV43 =
                                                                    $dashboardNoticeUserRolesV43
                                                                        ->get(
                                                                            $centralUser->id,
                                                                            collect()
                                                                        );
                                                            @endphp

                                                            @if($centralUserRolesV43->isNotEmpty())
                                                                <span
                                                                    style="
                                                                        display:block;
                                                                        margin-top:3px;
                                                                    "
                                                                >
                                                                    @foreach($centralUserRolesV43 as $centralUserRoleV43)
                                                                        <span class="es-central-user-role-v28">
                                                                            {{ $centralUserRoleV43['name'] }}
                                                                        </span>
                                                                    @endforeach
                                                                </span>
                                                            @endif
                                                        </span>
                                                    </label>
                                                @empty
                                                    <div
                                                        style="
                                                            padding:12px;
                                                            color:#7a8596;
                                                            font-size:12px;
                                                        "
                                                    >
                                                        No Central users are available.
                                                    </div>
                                                @endforelse
                                            </div>
                                        </div>

                                        <small
                                            style="
                                                display:block;
                                                margin-top:6px;
                                                color:#7a8596;
                                            "
                                        >
                                            You can select roles, specific users,
                                            or both. Matching is role OR user.
                                        </small>
                                    </div>

<div class="es-notice-field-v6">
                                        <label for="esNoticePublishedAtV6">
                                            Publish Date & Time
                                        </label>

                                        <input
                                            id="esNoticePublishedAtV6"
                                            type="datetime-local"
                                            name="published_at"
                                        >
                                    </div>

                                    <div class="es-notice-field-v6">
                                        <label for="esNoticeExpiresAtV6">
                                            Expiry Date & Time
                                        </label>

                                        <input
                                            id="esNoticeExpiresAtV6"
                                            type="datetime-local"
                                            name="expires_at"
                                        >
                                    </div>

                                    <div
                                        class="es-notice-field-v6 es-notice-span-3-v6"
                                        id="esNoticeSelectedTenantsWrapV6"
                                        style="display:none;"
                                    >
                                        <label>
                                            Select SaaS Tenants
                                        </label>

                                        <div class="es-notice-targets-v6">
                                            @forelse($dashboardNoticeTenants as $tenant)
                                                @php
                                                    $noticeTenantLabel =
                                                        $tenant->name
                                                        ?? $tenant->business_name
                                                        ?? $tenant->domain
                                                        ?? $tenant->subdomain
                                                        ?? ('Tenant #' . $tenant->id);

                                                    $noticeTenantMeta =
                                                        $tenant->domain
                                                        ?? $tenant->subdomain
                                                        ?? ('Website ID: ' . $tenant->id);
                                                @endphp

                                                <label class="es-notice-target-v6">
                                                    <input
                                                        type="checkbox"
                                                        name="tenant_ids[]"
                                                        value="{{ $tenant->id }}"
                                                    >

                                                    <span>
                                                        <strong>
                                                            {{ $noticeTenantLabel }}
                                                        </strong>

                                                        <small>
                                                            {{ $noticeTenantMeta }}
                                                        </small>
                                                    </span>
                                                </label>
                                            @empty
                                                <div class="text-muted small">
                                                    No tenants are currently available.
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>

                                    <div class="es-notice-field-v6 es-notice-span-2-v6">
                                        <label>
                                            Notice Image
                                            <span style="font-weight:500;color:#8a94a2;">
                                                (optional)
                                            </span>
                                        </label>

                                        <div class="es-notice-upload-v6">
                                            <input
                                                id="esNoticeImageInputV6"
                                                class="es-notice-upload-input-v6"
                                                type="file"
                                                name="image"
                                                accept="image/jpeg,image/png,image/webp,image/gif"
                                            >

                                            <div
                                                id="esNoticeUploadEmptyV6"
                                                class="es-notice-upload-empty-v6"
                                            >
                                                <div class="es-notice-upload-icon-v6">
                                                    +
                                                </div>

                                                <strong>
                                                    Upload notice image
                                                </strong>

                                                <small>
                                                    Recommended: 1200 × 600 px (2:1) · JPG, PNG, WEBP or GIF · maximum 8 MB
                                                </small>
                                            </div>

                                            <div
                                                id="esNoticeImagePreviewWrapV6"
                                                class="es-notice-preview-v6"
                                            >
                                                <img
                                                    id="esNoticeImagePreviewV6"
                                                    src=""
                                                    alt="Notice image preview"
                                                >

                                                <button
                                                    type="button"
                                                    id="esNoticeImageRemoveButtonV6"
                                                    class="es-notice-remove-image-v6"
                                                >
                                                    Remove image
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="es-notice-field-v6">
                                        <label for="esNoticeRotationEnabledV6">
                                            Notice Rotation
                                        </label>

                                        <select
                                            id="esNoticeRotationEnabledV6"
                                            name="rotation_enabled"
                                            required
                                        >
                                            <option value="1" selected>
                                                Enabled
                                            </option>
                                            <option value="0">
                                                Disabled
                                            </option>
                                        </select>

                                        <div class="es-notice-help-v6">
                                            Runs only while the Core dashboard
                                            is being viewed.
                                        </div>
                                    </div>

                                    <div
                                        class="es-notice-field-v6"
                                        id="esNoticeRotationSecondsWrapV6"
                                    >
                                        <label for="esNoticeRotationSecondsV6">
                                            Rotation Interval
                                        </label>

                                        <input
                                            id="esNoticeRotationSecondsV6"
                                            type="number"
                                            name="rotation_seconds"
                                            min="3"
                                            max="120"
                                            value="8"
                                        >

                                        <div class="es-notice-help-v6">
                                            3–120 seconds.
                                        </div>
                                    </div>

                                </div>

                                <div class="es-notice-form-actions-v6">
                                    <div
                                        id="esDashboardNoticeMessageV6"
                                        class="es-notice-form-message-v6"
                                    ></div>

                                    <div class="es-notice-action-buttons-v6">
                                        <button
                                            type="button"
                                            id="esDashboardNoticeCancelV6"
                                            class="es-notice-cancel-v6"
                                        >
                                            Cancel Edit
                                        </button>

                                        <button
                                            type="submit"
                                            id="esDashboardNoticeSaveV6"
                                            class="es-notice-save-v6"
                                        >
                                            Add Notice
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="es-notice-card-v6">
                        <div class="es-notice-head-v6">
                            <div>
                                <h3>Saved Notices</h3>
                                <p>
                                    Use the Esubiz three-dot menu to View,
                                    Edit, set Active/Inactive, or Delete
                                    a saved dashboard notice.
                                </p>
                            </div>
                        </div>

                        <div id="esDashboardNoticeListV6">
                            @if($dashboardNotices->isEmpty())
                                <div class="es-notice-empty-v6">
                                    No dashboard notices have been created yet.
                                </div>
                            @else
                                <div class="es-notice-table-wrap-v6">
                                    <div class="es-notice-table-scroll-v6">
                                        <table class="es-notice-table-v6">
                                            <thead>
                                                <tr>
                                                    <th>Notice</th>
                                                    <th>Status</th>
                                                    <th>Delivery</th>
                                                    <th>Style</th>
                                                    <th>Audience</th>
                                                    <th>Publish</th>
                                                    <th>Expiry</th>
                                                    <th></th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                @foreach($dashboardNotices as $notice)
                                                    @php
                                                        $noticeImageUrl =
                                                            $notice->image_path
                                                                ? app(
                                                                    \App\Services\Media\CentralMediaService::class
                                                                )->url($notice->image_path)
                                                                : null;

                                                        $noticePayload = [
                                                            'id' => (int) $notice->id,
                                                            'title' => (string) $notice->title,
                                                            'message' => (string) $notice->message,
                                                            'status' => (string) $notice->status,
                                                            'delivery_scope' => (string) ($notice->delivery_scope ?? 'all'),
                                                            'delivery_channels' => $notice->deliveryChannels(),
                                                            'central_target_type' => (string) ($notice->central_target_type ?? 'all'),
                                                            'central_role_ids' => array_values($notice->central_role_ids ?? []),
                                                            'central_user_ids' => array_values($notice->central_user_ids ?? []),
                                                            'variant' => (string) ($notice->variant ?? 'info'),
                                                            'dismissible' => (bool) $notice->dismissible,
                                                            'target_type' => (string) ($notice->target_type ?? 'all'),
                                                            'tenant_ids' => array_values($notice->tenant_ids ?? []),
                                                            'published_at' => $notice->published_at
                                                                ? $notice->published_at->format('Y-m-d\TH:i')
                                                                : null,
                                                            'expires_at' => $notice->expires_at
                                                                ? $notice->expires_at->format('Y-m-d\TH:i')
                                                                : null,
                                                            'image_url' => $noticeImageUrl,
                                                            'rotation_enabled' => (bool) $notice->rotation_enabled,
                                                            'rotation_seconds' => (int) ($notice->rotation_seconds ?: 8),
                                                            'update_url' => route(
                                                                'admin.site-settings.dashboard-notices.update',
                                                                $notice->id
                                                            ),
                                                            'status_url' => route(
                                                                'admin.site-settings.dashboard-notices.status',
                                                                $notice->id
                                                            ),
                                                            'delete_url' => route(
                                                                'admin.site-settings.dashboard-notices.destroy',
                                                                $notice->id
                                                            ),
                                                        ];

                                                        $noticePayloadEncoded =
                                                            base64_encode(
                                                                json_encode(
                                                                    $noticePayload,
                                                                    JSON_UNESCAPED_UNICODE
                                                                    | JSON_UNESCAPED_SLASHES
                                                                )
                                                            );
                                                    @endphp

                                                    <tr
                                                        class="es-notice-row-v6"
                                                        data-notice="{{ $noticePayloadEncoded }}"
                                                    >
                                                        <td>
                                                            <div class="es-notice-title-v6">
                                                                {{ $notice->title }}
                                                            </div>

                                                            <div class="es-notice-copy-v6">
                                                                {{ \Illuminate\Support\Str::limit(
                                                                    strip_tags($notice->message),
                                                                    120
                                                                ) }}
                                                            </div>
                                                        </td>

                                                        <td>
                                                            <span
                                                                class="es-notice-badge-v6 {{ $notice->status }}"
                                                            >
                                                                {{ $notice->status === 'published'
                                                                    ? 'Active'
                                                                    : ($notice->status === 'disabled'
                                                                        ? 'Inactive'
                                                                        : 'Draft') }}
                                                            </span>
                                                        </td>

                                                        {{-- ESUBIZ_NOTICE_DELIVERY_DISPLAY_TIMEZONE_FIX_V41 --}}
                                                        @php
                                                            /*
                                                             * delivery_channels is authoritative.
                                                             * delivery_scope remains legacy Core
                                                             * compatibility data only.
                                                             */
                                                            $noticeChannelsV41 =
                                                                $notice->deliveryChannels();

                                                            $noticeDeliveryLabelsV41 = [];

                                                            if (
                                                                in_array(
                                                                    'saas',
                                                                    $noticeChannelsV41,
                                                                    true
                                                                )
                                                            ) {
                                                                $noticeDeliveryLabelsV41[] =
                                                                    'SaaS Core';
                                                            }

                                                            if (
                                                                in_array(
                                                                    'off_server',
                                                                    $noticeChannelsV41,
                                                                    true
                                                                )
                                                            ) {
                                                                $noticeDeliveryLabelsV41[] =
                                                                    'Off-server Core';
                                                            }

                                                            if (
                                                                in_array(
                                                                    'central',
                                                                    $noticeChannelsV41,
                                                                    true
                                                                )
                                                            ) {
                                                                $noticeDeliveryLabelsV41[] =
                                                                    'Esubiz Central';
                                                            }

                                                            $noticeAudienceLabelsV41 = [];

                                                            if (
                                                                in_array(
                                                                    'saas',
                                                                    $noticeChannelsV41,
                                                                    true
                                                                )
                                                            ) {
                                                                if (
                                                                    $notice->target_type
                                                                        === 'selected'
                                                                ) {
                                                                    $tenantCountV41 =
                                                                        count(
                                                                            $notice->tenant_ids
                                                                            ?? []
                                                                        );

                                                                    $noticeAudienceLabelsV41[] =
                                                                        $tenantCountV41
                                                                        . ' SaaS tenant'
                                                                        . (
                                                                            $tenantCountV41 === 1
                                                                                ? ''
                                                                                : 's'
                                                                        );
                                                                } else {
                                                                    $noticeAudienceLabelsV41[] =
                                                                        'All SaaS';
                                                                }
                                                            }

                                                            if (
                                                                in_array(
                                                                    'off_server',
                                                                    $noticeChannelsV41,
                                                                    true
                                                                )
                                                            ) {
                                                                $noticeAudienceLabelsV41[] =
                                                                    'All off-server Core';
                                                            }

                                                            if (
                                                                in_array(
                                                                    'central',
                                                                    $noticeChannelsV41,
                                                                    true
                                                                )
                                                            ) {
                                                                if (
                                                                    $notice->central_target_type
                                                                        === 'selected'
                                                                ) {
                                                                    $roleCountV41 =
                                                                        count(
                                                                            $notice->central_role_ids
                                                                            ?? []
                                                                        );

                                                                    $userCountV41 =
                                                                        count(
                                                                            $notice->central_user_ids
                                                                            ?? []
                                                                        );

                                                                    $centralPartsV41 = [];

                                                                    if ($roleCountV41 > 0) {
                                                                        $centralPartsV41[] =
                                                                            $roleCountV41
                                                                            . ' role'
                                                                            . (
                                                                                $roleCountV41 === 1
                                                                                    ? ''
                                                                                    : 's'
                                                                            );
                                                                    }

                                                                    if ($userCountV41 > 0) {
                                                                        $centralPartsV41[] =
                                                                            $userCountV41
                                                                            . ' user'
                                                                            . (
                                                                                $userCountV41 === 1
                                                                                    ? ''
                                                                                    : 's'
                                                                            );
                                                                    }

                                                                    $noticeAudienceLabelsV41[] =
                                                                        !empty($centralPartsV41)
                                                                            ? implode(
                                                                                ' + ',
                                                                                $centralPartsV41
                                                                            )
                                                                            : 'Selected Central';
                                                                } else {
                                                                    $noticeAudienceLabelsV41[] =
                                                                        'All Central';
                                                                }
                                                            }
                                                        @endphp

                                                        <td>
                                                            {{ !empty($noticeDeliveryLabelsV41)
                                                                ? implode(
                                                                    ' + ',
                                                                    $noticeDeliveryLabelsV41
                                                                )
                                                                : '—' }}
                                                        </td>

                                                        <td>
                                                            {{ ucfirst($notice->variant ?? 'info') }}
                                                        </td>

                                                        <td>
                                                            {{ !empty($noticeAudienceLabelsV41)
                                                                ? implode(
                                                                    ' + ',
                                                                    $noticeAudienceLabelsV41
                                                                )
                                                                : '—' }}
                                                        </td>

                                                        <td>
                                                            {{ $notice->published_at
                                                                ? $notice->published_at->format('d M Y, H:i')
                                                                : 'Immediate' }}
                                                        </td>

                                                        <td>
                                                            {{ $notice->expires_at
                                                                ? $notice->expires_at->format('d M Y, H:i')
                                                                : 'No expiry' }}
                                                        </td>

                                                        <td>
                                                            <div class="es-notice-menu-wrap-v6">
                                                                <button
                                                                    type="button"
                                                                    class="es-notice-menu-trigger-v6"
                                                                    aria-label="Notice actions"
                                                                    title="Actions"
                                                                >
                                                                    &#8942;
                                                                </button>

                                                                <div class="es-notice-menu-v6">
                                                                    <button
                                                                        type="button"
                                                                        class="es-notice-menu-item-v6 es-notice-view-v6"
                                                                    >
                                                                        View
                                                                    </button>

                                                                    <button
                                                                        type="button"
                                                                        class="es-notice-menu-item-v6 es-notice-edit-v6"
                                                                    >
                                                                        Edit
                                                                    </button>

                                                                    <button
                                                                        type="button"
                                                                        class="es-notice-menu-item-v6 es-notice-status-v6"
                                                                    >
                                                                        {{ $notice->status === 'published'
                                                                            ? 'Set Inactive'
                                                                            : 'Set Active' }}
                                                                    </button>

                                                                    <div class="es-notice-menu-divider-v6"></div>

                                                                    <button
                                                                        type="button"
                                                                        class="es-notice-menu-item-v6 danger es-notice-delete-v6"
                                                                    >
                                                                        Delete
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="es-notice-pagination-v6">
                                        <div class="es-notice-page-meta-v6">
                                            Showing
                                            {{ $dashboardNotices->firstItem() ?? 0 }}
                                            –
                                            {{ $dashboardNotices->lastItem() ?? 0 }}
                                            of
                                            {{ $dashboardNotices->total() }}
                                            notices
                                        </div>

                                        <div class="es-notice-pages-v6">
                                            <button
                                                type="button"
                                                class="es-notice-page-btn-v6"
                                                data-page="{{ max(
                                                    1,
                                                    $dashboardNotices->currentPage() - 1
                                                ) }}"
                                                @disabled($dashboardNotices->onFirstPage())
                                            >
                                                Previous
                                            </button>

                                            <span class="es-notice-page-meta-v6">
                                                Page
                                                {{ $dashboardNotices->currentPage() }}
                                                of
                                                {{ max(1, $dashboardNotices->lastPage()) }}
                                            </span>

                                            <button
                                                type="button"
                                                class="es-notice-page-btn-v6"
                                                data-page="{{ min(
                                                    $dashboardNotices->lastPage(),
                                                    $dashboardNotices->currentPage() + 1
                                                ) }}"
                                                @disabled(!$dashboardNotices->hasMorePages())
                                            >
                                                Next
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div
                    id="esNoticeViewModalV6"
                    class="es-notice-modal-v6"
                    aria-hidden="true"
                >
                    <div class="es-notice-modal-card-v6">
                        <div class="es-notice-modal-head-v6">
                            <div>
                                <h4 id="esNoticeViewTitleV6">
                                    Dashboard Notice
                                </h4>
                            </div>

                            <button
                                type="button"
                                id="esNoticeViewCloseV6"
                                class="es-notice-modal-close-v6"
                            >
                                &times;
                            </button>
                        </div>

                        <div class="es-notice-modal-body-v6">
                            <img
                                id="esNoticeViewImageV6"
                                class="es-notice-view-image-v6"
                                src=""
                                alt=""
                            >

                            <div
                                id="esNoticeViewMetaV6"
                                class="es-notice-view-meta-v6"
                            ></div>

                            <div
                                id="esNoticeViewMessageV6"
                                class="es-notice-view-message-v6"
                            ></div>
                        </div>
                    </div>
                </div>

                <script>
    /* ESUBIZ_DASHBOARD_NOTICE_PAGINATION_STATE_V10 */
    let currentNoticePageV10 = Number(
        new URL(window.location.href).searchParams.get('notice_page') || 1
    );

                    document.addEventListener(
                        'DOMContentLoaded',
                        function () {
                            const form =
                                document.getElementById(
                                    'esDashboardNoticeFormV6'
                                );

                            if (!form) {
                                return;
                            }

                            const csrf =
                                form.querySelector(
                                    'input[name="_token"]'
                                )?.value || '';

                            const editingId =
                                document.getElementById(
                                    'esNoticeEditingIdV6'
                                );

                            const heading =
                                document.getElementById(
                                    'esNoticeFormHeadingV6'
                                );

                            const saveButton =
                                document.getElementById(
                                    'esDashboardNoticeSaveV6'
                                );

                            const cancelButton =
                                document.getElementById(
                                    'esDashboardNoticeCancelV6'
                                );

                            const formMessage =
                                document.getElementById(
                                    'esDashboardNoticeMessageV6'
                                );

                            const targetType =
                                document.getElementById(
                                    'esNoticeTargetTypeV6'
                                );

                            /*
                             * ESUBIZ_NOTICE_REMOVE_LEGACY_DELIVERY_JS_V38
                             *
                             * Legacy single delivery select removed.
                             * delivery_channels[] is authoritative.
                             */

                            const deliveryMulti =
                                document.getElementById(
                                    'esNoticeDeliveryMultiV18'
                                );

                            const deliveryTrigger =
                                document.getElementById(
                                    'esNoticeDeliveryTriggerV18'
                                );

                            const deliveryPanel =
                                document.getElementById(
                                    'esNoticeDeliveryPanelV18'
                                );

                            const deliverySummary =
                                document.getElementById(
                                    'esNoticeDeliverySummaryV18'
                                );

                            const deliveryCheckboxes =
                                Array.from(
                                    form.querySelectorAll(
                                        'input[name="delivery_channels[]"]'
                                    )
                                );

                            const selectedTenants =
                                document.getElementById(
                                    'esNoticeSelectedTenantsWrapV6'
                                );

                            const centralAudienceWrap =
                                document.getElementById(
                                    'esNoticeCentralAudienceWrapV19'
                                );

                            const centralTargetType =
                                document.getElementById(
                                    'esNoticeCentralTargetTypeV19'
                                );

                            const centralRolesWrap =
                                document.getElementById(
                                    'esNoticeCentralRolesWrapV19'
                                );

                            const centralRoleMulti =
                                document.getElementById(
                                    'esNoticeCentralRoleMultiV19'
                                );

                            const centralRoleTrigger =
                                document.getElementById(
                                    'esNoticeCentralRoleTriggerV19'
                                );

                            const centralRolePanel =
                                document.getElementById(
                                    'esNoticeCentralRolePanelV19'
                                );

                            const centralRoleSummary =
                                document.getElementById(
                                    'esNoticeCentralRoleSummaryV19'
                                );

                            const centralRoleCheckboxes =
                                Array.from(
                                    form.querySelectorAll(
                                        'input[name="central_role_ids[]"]'
                                    )
                                );

                            const tenantCheckboxes =
                                Array.from(
                                    form.querySelectorAll(
                                        'input[name="tenant_ids[]"]'
                                    )
                                );

                            const centralUsersWrap =
                                document.getElementById(
                                    'esNoticeCentralUsersWrapV21'
                                );

                            const centralUserMulti =
                                document.getElementById(
                                    'esNoticeCentralUserMultiV21'
                                );

                            const centralUserTrigger =
                                document.getElementById(
                                    'esNoticeCentralUserTriggerV21'
                                );

                            const centralUserPanel =
                                document.getElementById(
                                    'esNoticeCentralUserPanelV21'
                                );

                            const centralUserSummary =
                                document.getElementById(
                                    'esNoticeCentralUserSummaryV21'
                                );

                            const centralUserCheckboxes =
                                Array.from(
                                    form.querySelectorAll(
                                        'input[name="central_user_ids[]"]'
                                    )
                                );

                            const rotationEnabled =
                                document.getElementById(
                                    'esNoticeRotationEnabledV6'
                                );

                            const rotationWrap =
                                document.getElementById(
                                    'esNoticeRotationSecondsWrapV6'
                                );

                            const rotationSeconds =
                                document.getElementById(
                                    'esNoticeRotationSecondsV6'
                                );

                            const imageInput =
                                document.getElementById(
                                    'esNoticeImageInputV6'
                                );

                            const imagePreviewWrap =
                                document.getElementById(
                                    'esNoticeImagePreviewWrapV6'
                                );

                            const imagePreview =
                                document.getElementById(
                                    'esNoticeImagePreviewV6'
                                );

                            const uploadEmpty =
                                document.getElementById(
                                    'esNoticeUploadEmptyV6'
                                );

                            const removeImage =
                                document.getElementById(
                                    'esNoticeRemoveImageV6'
                                );

                            const removeImageButton =
                                document.getElementById(
                                    'esNoticeImageRemoveButtonV6'
                                );

                            const viewModal =
                                document.getElementById(
                                    'esNoticeViewModalV6'
                                );

                            function decodeNotice(row) {
                                const encoded =
                                    row?.dataset?.notice;

                                if (!encoded) {
                                    return null;
                                }

                                try {
                                    const binary =
                                        atob(encoded);

                                    const bytes =
                                        Uint8Array.from(
                                            binary,
                                            function (char) {
                                                return char.charCodeAt(0);
                                            }
                                        );

                                    const json =
                                        new TextDecoder()
                                            .decode(bytes);

                                    return JSON.parse(json);
                                } catch (error) {
                                    console.error(
                                        'Invalid Dashboard Notice payload.',
                                        error
                                    );

                                    return null;
                                }
                            }

                            function closeMenus() {
                                document
                                    .querySelectorAll(
                                        '.es-notice-menu-v6.is-open'
                                    )
                                    .forEach(
                                        function (menu) {
                                            menu.classList.remove(
                                                'is-open'
                                            );
                                        }
                                    );
                            }

                            function syncAudience() {
                                if (
                                    !targetType
                                    || !selectedTenants
                                ) {
                                    return;
                                }

                                /*
                                 * V18 delivery_channels[] is now
                                 * authoritative.
                                 *
                                 * SaaS audience applies only when
                                 * SaaS Core is selected.
                                 */
                                const selectedChannels =
                                    deliveryCheckboxes
                                        .filter(
                                            function (checkbox) {
                                                return checkbox.checked;
                                            }
                                        )
                                        .map(
                                            function (checkbox) {
                                                return checkbox.value;
                                            }
                                        );

                                const hasSaas =
                                    selectedChannels.includes(
                                        'saas'
                                    );

                                if (!hasSaas) {
                                    targetType.value =
                                        'all';

                                    targetType.disabled =
                                        true;

                                    selectedTenants.style.display =
                                        'none';

                                    return;
                                }

                                targetType.disabled =
                                    false;

                                selectedTenants.style.display =
                                    targetType.value
                                        === 'selected'
                                        ? ''
                                        : 'none';
                            }

                            function syncRotation() {
                                const enabled =
                                    rotationEnabled?.value
                                    === '1';

                                if (rotationWrap) {
                                    rotationWrap.style.display =
                                        enabled
                                            ? ''
                                            : 'none';
                                }

                                if (rotationSeconds) {
                                    rotationSeconds.disabled =
                                        !enabled;
                                }
                            }

                            function setImagePreview(url) {
                                if (
                                    !imagePreview
                                    || !imagePreviewWrap
                                    || !uploadEmpty
                                ) {
                                    return;
                                }

                                if (url) {
                                    imagePreview.src =
                                        url;

                                    imagePreviewWrap
                                        .classList
                                        .add('is-visible');

                                    uploadEmpty.style.display =
                                        'none';
                                } else {
                                    imagePreview.src =
                                        '';

                                    imagePreviewWrap
                                        .classList
                                        .remove('is-visible');

                                    uploadEmpty.style.display =
                                        '';
                                }
                            }

                            /*
                             * ESUBIZ_DASHBOARD_NOTICE_FEEDBACK_V12
                             *
                             * Normal resets clear feedback.
                             * Successful persistence may preserve it.
                             */
                            function setNoticeFeedbackV12(
                                message,
                                type = 'success'
                            ) {
                                if (!formMessage) {
                                    return;
                                }

                                formMessage.textContent =
                                    message || '';

                                formMessage.classList.remove(
                                    'is-success',
                                    'is-error'
                                );

                                if (message) {
                                    formMessage.classList.add(
                                        type === 'error'
                                            ? 'is-error'
                                            : 'is-success'
                                    );
                                }
                            }

                            function installTenantMultiSelectV21() {
                                const wrapper =
                                    document.getElementById(
                                        'esNoticeSelectedTenantsWrapV6'
                                    );

                                if (
                                    !wrapper
                                    || tenantCheckboxes.length === 0
                                    || document.getElementById(
                                        'esNoticeTenantMultiV21'
                                    )
                                ) {
                                    return;
                                }

                                const labels =
                                    tenantCheckboxes
                                        .map(
                                            function (checkbox) {
                                                return checkbox.closest(
                                                    'label'
                                                );
                                            }
                                        )
                                        .filter(Boolean);

                                const multi =
                                    document.createElement('div');

                                multi.id =
                                    'esNoticeTenantMultiV21';

                                multi.className =
                                    'es-notice-tenant-multi-v21';

                                const trigger =
                                    document.createElement('button');

                                trigger.type = 'button';
                                trigger.id =
                                    'esNoticeTenantTriggerV21';

                                trigger.className =
                                    'es-notice-delivery-trigger-v18';

                                trigger.setAttribute(
                                    'aria-expanded',
                                    'false'
                                );

                                const summary =
                                    document.createElement('span');

                                summary.id =
                                    'esNoticeTenantSummaryV21';

                                summary.className =
                                    'es-notice-delivery-summary-v18';

                                summary.textContent =
                                    'Select SaaS tenants';

                                const chevron =
                                    document.createElement('span');

                                chevron.className =
                                    'es-notice-delivery-chevron-v18';

                                chevron.setAttribute(
                                    'aria-hidden',
                                    'true'
                                );

                                chevron.innerHTML =
                                    '&#8964;';

                                trigger.appendChild(summary);
                                trigger.appendChild(chevron);

                                const panel =
                                    document.createElement('div');

                                panel.id =
                                    'esNoticeTenantPanelV21';

                                panel.className =
                                    'es-notice-delivery-panel-v18';

                                panel.hidden = true;

                                labels.forEach(
                                    function (label) {
                                        label.classList.add(
                                            'es-notice-delivery-option-v18'
                                        );

                                        panel.appendChild(label);
                                    }
                                );

                                multi.appendChild(trigger);
                                multi.appendChild(panel);

                                wrapper.appendChild(multi);

                                function syncTenantSummary() {
                                    const selected =
                                        tenantCheckboxes.filter(
                                            function (checkbox) {
                                                return checkbox.checked;
                                            }
                                        );

                                    if (!selected.length) {
                                        summary.textContent =
                                            'Select SaaS tenants';

                                        return;
                                    }

                                    const names =
                                        selected.map(
                                            function (checkbox) {
                                                const label =
                                                    checkbox.closest(
                                                        'label'
                                                    );

                                                return (
                                                    label?.textContent
                                                        || checkbox.value
                                                )
                                                    .replace(
                                                        /\s+/g,
                                                        ' '
                                                    )
                                                    .trim();
                                            }
                                        );

                                    summary.textContent =
                                        names.join(' + ');
                                }

                                trigger.addEventListener(
                                    'click',
                                    function (event) {
                                        event.preventDefault();
                                        event.stopPropagation();

                                        const willOpen =
                                            panel.hidden;

                                        panel.hidden =
                                            !willOpen;

                                        trigger.setAttribute(
                                            'aria-expanded',
                                            willOpen
                                                ? 'true'
                                                : 'false'
                                        );
                                    }
                                );

                                tenantCheckboxes.forEach(
                                    function (checkbox) {
                                        checkbox.addEventListener(
                                            'change',
                                            syncTenantSummary
                                        );
                                    }
                                );

                                document.addEventListener(
                                    'click',
                                    function (event) {
                                        if (
                                            !multi.contains(
                                                event.target
                                            )
                                        ) {
                                            panel.hidden = true;

                                            trigger.setAttribute(
                                                'aria-expanded',
                                                'false'
                                            );
                                        }
                                    }
                                );

                                document.addEventListener(
                                    'keydown',
                                    function (event) {
                                        if (
                                            event.key === 'Escape'
                                        ) {
                                            panel.hidden = true;

                                            trigger.setAttribute(
                                                'aria-expanded',
                                                'false'
                                            );
                                        }
                                    }
                                );

                                window
                                    .esSyncTenantSummaryV21 =
                                    syncTenantSummary;

                                syncTenantSummary();
                            }

                            function syncCentralUserSummaryV21() {
                                if (!centralUserSummary) {
                                    return;
                                }

                                const selected =
                                    centralUserCheckboxes.filter(
                                        function (checkbox) {
                                            return checkbox.checked;
                                        }
                                    );

                                if (!selected.length) {
                                    centralUserSummary.textContent =
                                        'Select Central users';

                                    return;
                                }

                                centralUserSummary.textContent =
                                    selected
                                        .map(
                                            function (checkbox) {
                                                return (
                                                    checkbox.dataset.userName
                                                    || checkbox.value
                                                );
                                            }
                                        )
                                        .join(' + ');
                            }

                            function closeCentralUserDropdownV21() {
                                if (centralUserPanel) {
                                    centralUserPanel.hidden = true;
                                }

                                if (centralUserTrigger) {
                                    centralUserTrigger.setAttribute(
                                        'aria-expanded',
                                        'false'
                                    );
                                }
                            }

                            centralUserTrigger?.addEventListener(
                                'click',
                                function (event) {
                                    event.preventDefault();
                                    event.stopPropagation();

                                    const willOpen =
                                        centralUserPanel?.hidden
                                        !== false;

                                    if (centralUserPanel) {
                                        centralUserPanel.hidden =
                                            !willOpen;
                                    }

                                    centralUserTrigger.setAttribute(
                                        'aria-expanded',
                                        willOpen
                                            ? 'true'
                                            : 'false'
                                    );
                                }
                            );

                            centralUserCheckboxes.forEach(
                                function (checkbox) {
                                    checkbox.addEventListener(
                                        'change',
                                        syncCentralUserSummaryV21
                                    );
                                }
                            );

                            document.addEventListener(
                                'click',
                                function (event) {
                                    if (
                                        centralUserMulti
                                        && !centralUserMulti.contains(
                                            event.target
                                        )
                                    ) {
                                        closeCentralUserDropdownV21();
                                    }
                                }
                            );

                            document.addEventListener(
                                'keydown',
                                function (event) {
                                    if (
                                        event.key === 'Escape'
                                    ) {
                                        closeCentralUserDropdownV21();
                                    }
                                }
                            );


                            function selectedCentralRoleIdsV19() {
                                return centralRoleCheckboxes
                                    .filter(
                                        function (checkbox) {
                                            return checkbox.checked;
                                        }
                                    )
                                    .map(
                                        function (checkbox) {
                                            return String(
                                                checkbox.value
                                            );
                                        }
                                    );
                            }

                            function syncCentralRoleSummaryV19() {
                                if (!centralRoleSummary) {
                                    return;
                                }

                                const selected =
                                    centralRoleCheckboxes.filter(
                                        function (checkbox) {
                                            return checkbox.checked;
                                        }
                                    );

                                if (selected.length === 0) {
                                    centralRoleSummary.textContent =
                                        'Select Central roles';

                                    return;
                                }

                                const labels =
                                    selected.map(
                                        function (checkbox) {
                                            return checkbox.dataset.roleName
                                                || checkbox.value;
                                        }
                                    );

                                centralRoleSummary.textContent =
                                    labels.join(' + ');
                            }

                            function closeCentralRoleDropdownV19() {
                                if (centralRolePanel) {
                                    centralRolePanel.hidden = true;
                                }

                                if (centralRoleTrigger) {
                                    centralRoleTrigger.setAttribute(
                                        'aria-expanded',
                                        'false'
                                    );
                                }
                            }

                            function syncCentralAudienceV19() {
                                const channels =
                                    selectedDeliveryChannelsV18();

                                const hasCentral =
                                    channels.includes('central');

                                if (centralAudienceWrap) {
                                    centralAudienceWrap.style.display =
                                        hasCentral
                                            ? ''
                                            : 'none';
                                }

                                if (!hasCentral) {
                                    if (centralTargetType) {
                                        centralTargetType.value = 'all';
                                    }

                                    centralRoleCheckboxes.forEach(
                                        function (checkbox) {
                                            checkbox.checked = false;
                                        }
                                    );

                                    centralUserCheckboxes.forEach(
                                        function (checkbox) {
                                            checkbox.checked = false;
                                        }
                                    );

                                    if (centralRolesWrap) {
                                        centralRolesWrap.style.display =
                                            'none';
                                    }

                                    if (centralUsersWrap) {
                                        centralUsersWrap.style.display =
                                            'none';
                                    }

                                    closeCentralRoleDropdownV19();
                                    closeCentralUserDropdownV21();

                                    syncCentralRoleSummaryV19();
                                    syncCentralUserSummaryV21();

                                    return;
                                }

                                const selectedMode =
                                    centralTargetType?.value
                                    === 'selected';

                                if (centralRolesWrap) {
                                    centralRolesWrap.style.display =
                                        selectedMode
                                            ? ''
                                            : 'none';
                                }

                                if (centralUsersWrap) {
                                    centralUsersWrap.style.display =
                                        selectedMode
                                            ? ''
                                            : 'none';
                                }

                                if (!selectedMode) {
                                    centralRoleCheckboxes.forEach(
                                        function (checkbox) {
                                            checkbox.checked = false;
                                        }
                                    );

                                    centralUserCheckboxes.forEach(
                                        function (checkbox) {
                                            checkbox.checked = false;
                                        }
                                    );

                                    closeCentralRoleDropdownV19();
                                    closeCentralUserDropdownV21();
                                }

                                syncCentralRoleSummaryV19();
                                syncCentralUserSummaryV21();
                            }

                            centralTargetType?.addEventListener(
                                'change',
                                function () {
                                    syncCentralAudienceV19();
                                }
                            );

                            centralRoleTrigger?.addEventListener(
                                'click',
                                function (event) {
                                    event.preventDefault();
                                    event.stopPropagation();

                                    const willOpen =
                                        centralRolePanel?.hidden
                                        !== false;

                                    if (centralRolePanel) {
                                        centralRolePanel.hidden =
                                            !willOpen;
                                    }

                                    centralRoleTrigger.setAttribute(
                                        'aria-expanded',
                                        willOpen
                                            ? 'true'
                                            : 'false'
                                    );
                                }
                            );

                            centralRoleCheckboxes.forEach(
                                function (checkbox) {
                                    checkbox.addEventListener(
                                        'change',
                                        function () {
                                            syncCentralRoleSummaryV19();
                                        }
                                    );
                                }
                            );

                            installTenantMultiSelectV21();

                            document.addEventListener(
                                'click',
                                function (event) {
                                    if (
                                        centralRoleMulti
                                        && !centralRoleMulti.contains(
                                            event.target
                                        )
                                    ) {
                                        closeCentralRoleDropdownV19();
                                    }
                                }
                            );

                            document.addEventListener(
                                'keydown',
                                function (event) {
                                    if (
                                        event.key === 'Escape'
                                    ) {
                                        closeCentralRoleDropdownV19();
                                    }
                                }
                            );


                            function selectedDeliveryChannelsV18() {
                                return deliveryCheckboxes
                                    .filter(
                                        function (checkbox) {
                                            return checkbox.checked;
                                        }
                                    )
                                    .map(
                                        function (checkbox) {
                                            return checkbox.value;
                                        }
                                    );
                            }

                            function deliveryLabelV18(channel) {
                                const labels = {
                                    saas: 'SaaS Core',
                                    off_server: 'Off-server Core',
                                    central: 'Esubiz Central',
                                };

                                return labels[channel]
                                    || channel;
                            }

                            function syncDeliveryDropdownV18(
                                syncAudienceState = true
                            ) {
                                const channels =
                                    selectedDeliveryChannelsV18();

                                if (deliverySummary) {
                                    deliverySummary.textContent =
                                        channels.length
                                            ? channels
                                                .map(deliveryLabelV18)
                                                .join(' + ')
                                            : 'Select delivery';
                                }

                                /*
                                 * Maintain legacy delivery_scope only
                                 * for compatibility with older UI/Core
                                 * behaviour. delivery_channels[] is the
                                 * authoritative submitted value.
                                 */
                                /*
                                 * ESUBIZ_NOTICE_LEGACY_DELIVERY_FINAL_REMOVAL_V39
                                 *
                                 * delivery_channels[] is authoritative.
                                 * delivery_scope remains hidden compatibility
                                 * data only.
                                 */
                                const legacyDeliveryScopeV39 =
                                    document.getElementById(
                                        'esNoticeDeliveryScopeV6'
                                    );

                                if (legacyDeliveryScopeV39) {
                                    const hasSaas =
                                        channels.includes('saas');

                                    const hasOffServer =
                                        channels.includes(
                                            'off_server'
                                        );

                                    if (
                                        hasSaas
                                        && hasOffServer
                                    ) {
                                        legacyDeliveryScopeV39.value =
                                            'all';
                                    } else if (hasSaas) {
                                        legacyDeliveryScopeV39.value =
                                            'saas';
                                    } else {
                                        legacyDeliveryScopeV39.value =
                                            'off_server';
                                    }
                                }

                                if (
                                    syncAudienceState
                                    && typeof syncAudience ===
                                        'function'
                                ) {
                                    syncAudience();
                                }

                                syncCentralAudienceV19();
                            }

                            function setDeliveryChannelsV18(
                                channels
                            ) {
                                const selected =
                                    new Set(
                                        (channels || [])
                                            .map(String)
                                    );

                                deliveryCheckboxes.forEach(
                                    function (checkbox) {
                                        checkbox.checked =
                                            selected.has(
                                                String(
                                                    checkbox.value
                                                )
                                            );
                                    }
                                );

                                syncDeliveryDropdownV18();
                            }

                            function closeDeliveryDropdownV18() {
                                if (deliveryPanel) {
                                    deliveryPanel.hidden = true;
                                }

                                if (deliveryTrigger) {
                                    deliveryTrigger.setAttribute(
                                        'aria-expanded',
                                        'false'
                                    );
                                }
                            }

                            deliveryTrigger?.addEventListener(
                                'click',
                                function (event) {
                                    event.preventDefault();
                                    event.stopPropagation();

                                    const willOpen =
                                        deliveryPanel?.hidden
                                        !== false;

                                    if (deliveryPanel) {
                                        deliveryPanel.hidden =
                                            !willOpen;
                                    }

                                    deliveryTrigger.setAttribute(
                                        'aria-expanded',
                                        willOpen
                                            ? 'true'
                                            : 'false'
                                    );
                                }
                            );

                            deliveryCheckboxes.forEach(
                                function (checkbox) {
                                    checkbox.addEventListener(
                                        'change',
                                        function () {
                                            syncDeliveryDropdownV18();
                                        }
                                    );
                                }
                            );

                            document.addEventListener(
                                'click',
                                function (event) {
                                    if (
                                        deliveryMulti
                                        && !deliveryMulti.contains(
                                            event.target
                                        )
                                    ) {
                                        closeDeliveryDropdownV18();
                                    }
                                }
                            );

                            document.addEventListener(
                                'keydown',
                                function (event) {
                                    if (
                                        event.key === 'Escape'
                                    ) {
                                        closeDeliveryDropdownV18();
                                    }
                                }
                            );


                            function resetForm(
                                options = {}
                            ) {
                                form.reset();

                                if (editingId) {
                                    editingId.value = '';
                                }

                                if (removeImage) {
                                    removeImage.value = '0';
                                }

                                if (heading) {
                                    heading.textContent =
                                        'Add Dashboard Notice';
                                }

                                if (saveButton) {
                                    saveButton.textContent =
                                        'Add Notice';
                                }

                                cancelButton
                                    ?.classList
                                    .remove('is-visible');

                                if (imageInput) {
                                    imageInput.value = '';
                                }

                                setImagePreview('');

                                form
                                    .querySelectorAll(
                                        'input[name="tenant_ids[]"]'
                                    )
                                    .forEach(
                                        function (checkbox) {
                                            checkbox.checked =
                                                false;
                                        }
                                    );

                                if (centralTargetType) {
                                    centralTargetType.value =
                                        'all';
                                }

                                centralRoleCheckboxes.forEach(
                                    function (checkbox) {
                                        checkbox.checked =
                                            false;
                                    }
                                );

                                centralUserCheckboxes.forEach(
                                    function (checkbox) {
                                        checkbox.checked =
                                            false;
                                    }
                                );

                                syncCentralRoleSummaryV19();
                                syncCentralUserSummaryV21();

                                if (
                                    typeof window.esSyncTenantSummaryV21
                                    === 'function'
                                ) {
                                    window.esSyncTenantSummaryV21();
                                }

                                if (rotationEnabled) {
                                    rotationEnabled.value =
                                        '1';
                                }

                                if (rotationSeconds) {
                                    rotationSeconds.value =
                                        '8';
                                }

                                if (
                                    formMessage
                                    && options.preserveMessage !== true
                                ) {
                                    formMessage.textContent =
                                        '';

                                    formMessage.classList.remove(
                                        'is-success',
                                        'is-error'
                                    );
                                }

                                setDeliveryChannelsV18([
                                    'saas',
                                    'off_server',
                                ]);

                                syncAudience();
                                syncRotation();
                            }

                            function populateEdit(notice) {
                                if (!notice) {
                                    return;
                                }

                                resetForm();

                                editingId.value =
                                    notice.id || '';

                                form.elements.title.value =
                                    notice.title || '';

                                form.elements.message.value =
                                    notice.message || '';

                                form.elements.status.value =
                                    notice.status || 'draft';

                                form.elements.delivery_scope.value =
                                    notice.delivery_scope
                                    || 'all';

                                let savedDeliveryChannels =
                                    Array.isArray(
                                        notice.delivery_channels
                                    )
                                    ? notice.delivery_channels
                                    : [];

                                if (
                                    savedDeliveryChannels.length
                                    === 0
                                ) {
                                    if (
                                        notice.delivery_scope
                                        === 'saas'
                                    ) {
                                        savedDeliveryChannels = [
                                            'saas',
                                        ];
                                    } else if (
                                        notice.delivery_scope
                                        === 'off_server'
                                    ) {
                                        savedDeliveryChannels = [
                                            'off_server',
                                        ];
                                    } else {
                                        savedDeliveryChannels = [
                                            'saas',
                                            'off_server',
                                        ];
                                    }
                                }

                                setDeliveryChannelsV18(
                                    savedDeliveryChannels
                                );

                                form.elements.variant.value =
                                    notice.variant
                                    || 'info';

                                form.elements.dismissible.value =
                                    notice.dismissible
                                        ? '1'
                                        : '0';

                                form.elements.target_type.value =
                                    notice.target_type
                                    || 'all';

                                if (centralTargetType) {
                                    centralTargetType.value =
                                        notice.central_target_type
                                        || 'all';
                                }

                                const selectedCentralRoles =
                                    new Set(
                                        (
                                            notice.central_role_ids
                                            || []
                                        ).map(String)
                                    );

                                centralRoleCheckboxes.forEach(
                                    function (checkbox) {
                                        checkbox.checked =
                                            selectedCentralRoles.has(
                                                String(
                                                    checkbox.value
                                                )
                                            );
                                    }
                                );

                                const selectedCentralUsers =
                                    new Set(
                                        (
                                            notice.central_user_ids
                                            || []
                                        ).map(String)
                                    );

                                centralUserCheckboxes.forEach(
                                    function (checkbox) {
                                        checkbox.checked =
                                            selectedCentralUsers.has(
                                                String(
                                                    checkbox.value
                                                )
                                            );
                                    }
                                );

                                syncCentralRoleSummaryV19();
                                syncCentralUserSummaryV21();
                                syncCentralAudienceV19();

                                if (
                                    typeof window.esSyncTenantSummaryV21
                                    === 'function'
                                ) {
                                    window.esSyncTenantSummaryV21();
                                }

                                /*
                                 * ESUBIZ_NOTICE_EDIT_LOCALTIME_FIX_V42
                                 *
                                 * Notice timestamps arrive from the backend
                                 * as UTC. datetime-local requires a timezone-
                                 * free value representing browser local time.
                                 *
                                 * Convert UTC -> browser local for Edit.
                                 * V41 converts browser local -> UTC on save.
                                 */
                                const noticeUtcToLocalInputV42 =
                                    function (value) {
                                        if (!value) {
                                            return '';
                                        }

                                        /*
                                         * Laravel/SQL timestamps may arrive
                                         * as "YYYY-MM-DD HH:mm:ss". Explicitly
                                         * mark those as UTC before parsing.
                                         */
                                        let normalized =
                                            String(value).trim();

                                        if (
                                            /^\d{4}-\d{2}-\d{2} \d{2}:\d{2}/
                                                .test(normalized)
                                        ) {
                                            normalized =
                                                normalized.replace(
                                                    ' ',
                                                    'T'
                                                ) + 'Z';
                                        }

                                        const date =
                                            new Date(normalized);

                                        if (
                                            Number.isNaN(
                                                date.getTime()
                                            )
                                        ) {
                                            return '';
                                        }

                                        const pad =
                                            function (number) {
                                                return String(number)
                                                    .padStart(2, '0');
                                            };

                                        return (
                                            date.getFullYear()
                                            + '-'
                                            + pad(
                                                date.getMonth() + 1
                                            )
                                            + '-'
                                            + pad(
                                                date.getDate()
                                            )
                                            + 'T'
                                            + pad(
                                                date.getHours()
                                            )
                                            + ':'
                                            + pad(
                                                date.getMinutes()
                                            )
                                        );
                                    };

                                form.elements.published_at.value =
                                    noticeUtcToLocalInputV42(
                                        notice.published_at
                                    );

                                form.elements.expires_at.value =
                                    noticeUtcToLocalInputV42(
                                        notice.expires_at
                                    );

                                rotationEnabled.value =
                                    notice.rotation_enabled
                                        ? '1'
                                        : '0';

                                rotationSeconds.value =
                                    notice.rotation_seconds
                                    || 8;

                                const selected =
                                    new Set(
                                        (
                                            notice.tenant_ids
                                            || []
                                        ).map(String)
                                    );

                                form
                                    .querySelectorAll(
                                        'input[name="tenant_ids[]"]'
                                    )
                                    .forEach(
                                        function (checkbox) {
                                            checkbox.checked =
                                                selected.has(
                                                    String(
                                                        checkbox.value
                                                    )
                                                );
                                        }
                                    );

                                setImagePreview(
                                    notice.image_url
                                    || ''
                                );

                                removeImage.value =
                                    '0';

                                heading.textContent =
                                    'Edit Dashboard Notice';

                                saveButton.textContent =
                                    'Save Changes';

                                cancelButton
                                    ?.classList
                                    .add('is-visible');

                                syncAudience();
                                syncRotation();

                                form.scrollIntoView({
                                    behavior:'smooth',
                                    block:'start'
                                });
                            }

                            function showView(notice) {
                                if (!notice || !viewModal) {
                                    return;
                                }

                                const title =
                                    document.getElementById(
                                        'esNoticeViewTitleV6'
                                    );

                                const image =
                                    document.getElementById(
                                        'esNoticeViewImageV6'
                                    );

                                const meta =
                                    document.getElementById(
                                        'esNoticeViewMetaV6'
                                    );

                                const message =
                                    document.getElementById(
                                        'esNoticeViewMessageV6'
                                    );

                                title.textContent =
                                    notice.title
                                    || 'Dashboard Notice';

                                if (notice.image_url) {
                                    image.src =
                                        notice.image_url;

                                    image.classList.add(
                                        'is-visible'
                                    );
                                } else {
                                    image.src = '';

                                    image.classList.remove(
                                        'is-visible'
                                    );
                                }

                                const statusLabel =
                                    notice.status === 'published'
                                        ? 'Active'
                                        : (
                                            notice.status === 'disabled'
                                                ? 'Inactive'
                                                : 'Draft'
                                        );

                                const deliveryLabel =
                                    notice.delivery_scope === 'all'
                                        ? 'SaaS + Off-server'
                                        : (
                                            notice.delivery_scope === 'saas'
                                                ? 'SaaS'
                                                : 'Off-server'
                                        );

                                meta.innerHTML = '';

                                [
                                    statusLabel,
                                    deliveryLabel,
                                    (
                                        notice.variant
                                        || 'info'
                                    ),
                                    notice.rotation_enabled
                                        ? (
                                            'Rotation: '
                                            + (
                                                notice.rotation_seconds
                                                || 8
                                            )
                                            + 's'
                                        )
                                        : 'Rotation: Off'
                                ].forEach(
                                    function (label) {
                                        const pill =
                                            document.createElement(
                                                'span'
                                            );

                                        pill.className =
                                            'es-notice-view-pill-v6';

                                        pill.textContent =
                                            label;

                                        meta.appendChild(
                                            pill
                                        );
                                    }
                                );

                                /*
                                 * This content originates only from
                                 * trusted Central Admin notice authoring.
                                 */
                                message.innerHTML =
                                    notice.message
                                    || '';

                                viewModal.classList.add(
                                    'is-open'
                                );

                                viewModal.setAttribute(
                                    'aria-hidden',
                                    'false'
                                );
                            }

                            function closeView() {
                                viewModal
                                    ?.classList
                                    .remove('is-open');

                                viewModal
                                    ?.setAttribute(
                                        'aria-hidden',
                                        'true'
                                    );
                            }

                            
        async function loadDashboardNoticePageV10(page, options = {}) {
            let targetPage = Math.max(1, Number(page || 1));

            const url = new URL(window.location.href);
            url.searchParams.set('tab', 'notices');
            url.searchParams.set('notice_page', String(targetPage));

            const response = await fetch(
                url.toString(),
                {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                }
            );

            if (!response.ok) {
                throw new Error(
                    'Unable to refresh Dashboard Notices.'
                );
            }

            const html = await response.text();
            const parsed = new DOMParser().parseFromString(
                html,
                'text/html'
            );

            let replacement =
                parsed.getElementById(
                    'esDashboardNoticeListV6'
                );

            /*
             * If deleting the last row on a page makes that page invalid,
             * automatically fall back one page and try once more.
             */
            if (
                options.allowPreviousFallback === true
                && targetPage > 1
                && replacement
                && replacement.querySelectorAll(
                    '.es-notice-row-v6[data-notice]'
                ).length === 0
            ) {
                targetPage -= 1;

                const fallbackUrl =
                    new URL(window.location.href);

                fallbackUrl.searchParams.set(
                    'tab',
                    'notices'
                );

                fallbackUrl.searchParams.set(
                    'notice_page',
                    String(targetPage)
                );

                const fallbackResponse =
                    await fetch(
                        fallbackUrl.toString(),
                        {
                            headers: {
                                'X-Requested-With':
                                    'XMLHttpRequest',
                            },
                        }
                    );

                if (!fallbackResponse.ok) {
                    throw new Error(
                        'Unable to load the previous Dashboard Notice page.'
                    );
                }

                const fallbackHtml =
                    await fallbackResponse.text();

                const fallbackParsed =
                    new DOMParser().parseFromString(
                        fallbackHtml,
                        'text/html'
                    );

                replacement =
                    fallbackParsed.getElementById(
                        'esDashboardNoticeListV6'
                    );
            }

            if (!replacement) {
                throw new Error(
                    'Dashboard Notice list was not found in the refreshed response.'
                );
            }

            const current =
                document.getElementById(
                    'esDashboardNoticeListV6'
                );

            if (!current) {
                throw new Error(
                    'Current Dashboard Notice list was not found.'
                );
            }

            current.replaceWith(
                replacement
            );

            currentNoticePageV10 =
                targetPage;

            const stateUrl =
                new URL(window.location.href);

            stateUrl.searchParams.set(
                'tab',
                'notices'
            );

            stateUrl.searchParams.set(
                'notice_page',
                String(targetPage)
            );

            window.history.replaceState(
                {},
                '',
                stateUrl.toString()
            );

            return replacement;
        }

/*
                             * ====================================================
                             * ESUBIZ_DASHBOARD_NOTICE_PAGINATION_WIRING_V11
                             * ====================================================
                             *
                             * Compatibility entry point for the existing V6
                             * Previous / Next and mutation handlers.
                             *
                             * - explicit page = navigate there
                             * - no page       = stay on current AJAX page
                             * - delete may request previous-page fallback
                             */
                            async function loadNoticePage(
                                page = null,
                                options = {}
                            ) {
                                const targetPage =
                                    page === null
                                    || page === undefined
                                    || page === ''
                                        ? currentNoticePageV10
                                        : Math.max(
                                            1,
                                            Number(page)
                                        );

                                const currentList =
                                    document.getElementById(
                                        'esDashboardNoticeListV6'
                                    );

                                if (!currentList) {
                                    return;
                                }

                                currentList.style.opacity =
                                    '.55';

                                currentList.style.pointerEvents =
                                    'none';

                                try {
                                    return await loadDashboardNoticePageV10(
                                        targetPage,
                                        options
                                    );
                                } catch (error) {
                                    /*
                                     * The original list may still be mounted
                                     * when the request itself fails.
                                     */
                                    const liveList =
                                        document.getElementById(
                                            'esDashboardNoticeListV6'
                                        );

                                    if (liveList) {
                                        liveList.style.opacity =
                                            '';

                                        liveList.style.pointerEvents =
                                            '';
                                    }

                                    throw error;
                                }
                            }

                            async function changeStatus(
                                notice,
                                button
                            ) {
                                const nextStatus =
                                    notice.status
                                    === 'published'
                                        ? 'disabled'
                                        : 'published';

                                button.disabled =
                                    true;

                                try {
                                    const response =
                                        await fetch(
                                            notice.status_url,
                                            {
                                                method:'PATCH',
                                                headers:{
                                                    'Accept':
                                                        'application/json',
                                                    'Content-Type':
                                                        'application/json',
                                                    'X-CSRF-TOKEN':
                                                        csrf
                                                },
                                                body:JSON.stringify({
                                                    status:
                                                        nextStatus
                                                })
                                            }
                                        );

                                    const payload =
                                        await response.json();

                                    if (!response.ok) {
                                        throw new Error(
                                            payload.message
                                            || 'Unable to change notice status.'
                                        );
                                    }

                                    await loadNoticePage();
                                } finally {
                                    button.disabled =
                                        false;
                                }
                            }

                            async function deleteNotice(
                                notice,
                                button
                            ) {
                                if (
                                    !window.confirm(
                                        'Delete this dashboard notice permanently?'
                                    )
                                ) {
                                    return;
                                }

                                button.disabled =
                                    true;

                                try {
                                    const response =
                                        await fetch(
                                            notice.delete_url,
                                            {
                                                method:'DELETE',
                                                headers:{
                                                    'Accept':
                                                        'application/json',
                                                    'X-CSRF-TOKEN':
                                                        csrf
                                                }
                                            }
                                        );

                                    const payload =
                                        await response.json();

                                    if (!response.ok) {
                                        throw new Error(
                                            payload.message
                                            || 'Unable to delete notice.'
                                        );
                                    }

                                    if (
                                        String(editingId.value)
                                        === String(notice.id)
                                    ) {
                                        resetForm();
                                    }

                                    await loadNoticePage(
                                        currentNoticePageV10,
                                        {
                                            allowPreviousFallback:
                                                true
                                        }
                                    );
                                } finally {
                                    button.disabled =
                                        false;
                                }
                            }

                            targetType
                                ?.addEventListener(
                                    'change',
                                    syncAudience
                                );

                            /*
                             * ESUBIZ_NOTICE_DANGLING_DELIVERY_LISTENER_FIX_V40
                             *
                             * The old single Delivery control no longer exists.
                             * V18 delivery_channels[] checkboxes now drive
                             * Delivery/Audience synchronization.
                             */

                            rotationEnabled
                                ?.addEventListener(
                                    'change',
                                    syncRotation
                                );

                            imageInput
                                ?.addEventListener(
                                    'change',
                                    function () {
                                        const file =
                                            imageInput
                                                .files?.[0];

                                        if (!file) {
                                            return;
                                        }

                                        removeImage.value =
                                            '0';

                                        const reader =
                                            new FileReader();

                                        reader.onload =
                                            function (event) {
                                                setImagePreview(
                                                    event.target
                                                        ?.result
                                                    || ''
                                                );
                                            };

                                        reader.readAsDataURL(
                                            file
                                        );
                                    }
                                );

                            removeImageButton
                                ?.addEventListener(
                                    'click',
                                    function (event) {
                                        event.preventDefault();
                                        event.stopPropagation();

                                        if (imageInput) {
                                            imageInput.value =
                                                '';
                                        }

                                        removeImage.value =
                                            '1';

                                        setImagePreview('');
                                    }
                                );

                            cancelButton
                                ?.addEventListener(
                                    'click',
                                    resetForm
                                );

                            document
                                .getElementById(
                                    'esNoticeViewCloseV6'
                                )
                                ?.addEventListener(
                                    'click',
                                    closeView
                                );

                            viewModal
                                ?.addEventListener(
                                    'click',
                                    function (event) {
                                        if (
                                            event.target
                                            === viewModal
                                        ) {
                                            closeView();
                                        }
                                    }
                                );

                            form.addEventListener(
                                'submit',
                                async function (event) {
                                    event.preventDefault();

                                    saveButton.disabled =
                                        true;

                                    formMessage.textContent =
                                        editingId.value
                                            ? 'Saving changes...'
                                            : 'Creating notice...';

                                    try {
                                        const data =
                                            new FormData(
                                                form
                                            );

                                        /*
                                         * V41 TIMEZONE FIX
                                         *
                                         * datetime-local contains no timezone.
                                         * Treat the entered value as browser
                                         * local time and send an ISO UTC value
                                         * to Laravel.
                                         *
                                         * The application/database can remain
                                         * safely standardized on UTC.
                                         */
                                        [
                                            'published_at',
                                            'expires_at'
                                        ].forEach(
                                            function (fieldName) {
                                                const field =
                                                    form.querySelector(
                                                        '[name="'
                                                        + fieldName
                                                        + '"]'
                                                    );

                                                const value =
                                                    field
                                                        ?.value
                                                        ?.trim();

                                                if (!value) {
                                                    return;
                                                }

                                                const localDate =
                                                    new Date(
                                                        value
                                                    );

                                                if (
                                                    !Number.isNaN(
                                                        localDate
                                                            .getTime()
                                                    )
                                                ) {
                                                    data.set(
                                                        fieldName,
                                                        localDate
                                                            .toISOString()
                                                    );
                                                }
                                            }
                                        );

                                        /*
                                         * Disabled controls are omitted by
                                         * FormData. SaaS audience targeting
                                         * applies only when SaaS Core is
                                         * selected.
                                         */
                                        const submitChannelsV39 =
                                            selectedDeliveryChannelsV18();

                                        if (
                                            !submitChannelsV39.includes(
                                                'saas'
                                            )
                                        ) {
                                            data.set(
                                                'target_type',
                                                'all'
                                            );
                                        }

                                        if (
                                            rotationEnabled.value
                                            === '0'
                                        ) {
                                            data.set(
                                                'rotation_enabled',
                                                '0'
                                            );

                                            data.delete(
                                                'rotation_seconds'
                                            );
                                        }

                                        const isEditing =
                                            !!editingId.value;

                                        const url =
                                            isEditing
                                                ? (
                                                    form.dataset.updateBase
                                                    + '/'
                                                    + editingId.value
                                                )
                                                : form.dataset.createUrl;

                                        /*
                                         * PHP multipart uploads are
                                         * reliably parsed through POST.
                                         * Laravel method spoofing provides
                                         * the PATCH route for Edit.
                                         */
                                        if (isEditing) {
                                            data.set(
                                                '_method',
                                                'PATCH'
                                            );
                                        }

                                        const response =
                                            await fetch(
                                                url,
                                                {
                                                    method:'POST',
                                                    headers:{
                                                        'Accept':
                                                            'application/json',
                                                        'X-CSRF-TOKEN':
                                                            csrf
                                                    },
                                                    body:data
                                                }
                                            );

                                        const payload =
                                            await response.json();

                                        if (!response.ok) {
                                            let message =
                                                payload.message
                                                || (
                                                    isEditing
                                                        ? 'Unable to update dashboard notice.'
                                                        : 'Unable to create dashboard notice.'
                                                );

                                            if (
                                                payload.errors
                                            ) {
                                                const first =
                                                    Object.values(
                                                        payload.errors
                                                    )
                                                        .flat()[0];

                                                if (first) {
                                                    message =
                                                        first;
                                                }
                                            }

                                            throw new Error(
                                                message
                                            );
                                        }

                                        formMessage.textContent =
                                            payload.message
                                            || (
                                                isEditing
                                                    ? 'Dashboard notice updated.'
                                                    : 'Dashboard notice created.'
                                            );

                                        resetForm({
                                            preserveMessage: true
                                        });

                                        await loadNoticePage();
                                    } catch (error) {
                                        formMessage.textContent =
                                            error.message
                                            || 'Unable to save dashboard notice.';
                                    } finally {
                                        saveButton.disabled =
                                            false;
                                    }
                                }
                            );

                            document.addEventListener(
                                'click',
                                async function (event) {
                                    const trigger =
                                        event.target.closest(
                                            '.es-notice-menu-trigger-v6'
                                        );

                                    if (trigger) {
                                        event.preventDefault();
                                        event.stopPropagation();

                                        const menu =
                                            trigger
                                                .closest(
                                                    '.es-notice-menu-wrap-v6'
                                                )
                                                ?.querySelector(
                                                    '.es-notice-menu-v6'
                                                );

                                        const wasOpen =
                                            menu
                                                ?.classList
                                                .contains(
                                                    'is-open'
                                                );

                                        closeMenus();

                                        if (
                                            menu
                                            && !wasOpen
                                        ) {
                                            menu.classList.add(
                                                'is-open'
                                            );
                                        }

                                        return;
                                    }

                                    const row =
                                        event.target.closest(
                                            '.es-notice-row-v6'
                                        );

                                    const notice =
                                        decodeNotice(row);

                                    if (
                                        event.target.closest(
                                            '.es-notice-view-v6'
                                        )
                                    ) {
                                        closeMenus();
                                        showView(notice);
                                        return;
                                    }

                                    if (
                                        event.target.closest(
                                            '.es-notice-edit-v6'
                                        )
                                    ) {
                                        closeMenus();
                                        populateEdit(notice);
                                        return;
                                    }

                                    const statusButton =
                                        event.target.closest(
                                            '.es-notice-status-v6'
                                        );

                                    if (
                                        statusButton
                                        && notice
                                    ) {
                                        closeMenus();

                                        try {
                                            await changeStatus(
                                                notice,
                                                statusButton
                                            );
                                        } catch (error) {
                                            alert(
                                                error.message
                                                || 'Unable to change notice status.'
                                            );
                                        }

                                        return;
                                    }

                                    const deleteButton =
                                        event.target.closest(
                                            '.es-notice-delete-v6'
                                        );

                                    if (
                                        deleteButton
                                        && notice
                                    ) {
                                        closeMenus();

                                        try {
                                            await deleteNotice(
                                                notice,
                                                deleteButton
                                            );
                                        } catch (error) {
                                            alert(
                                                error.message
                                                || 'Unable to delete notice.'
                                            );
                                        }

                                        return;
                                    }

                                    const pageButton =
                                        event.target.closest(
                                            '.es-notice-page-btn-v6'
                                        );

                                    if (
                                        pageButton
                                        && !pageButton.disabled
                                    ) {
                                        try {
                                            await loadNoticePage(
                                                Number(
                                                    pageButton
                                                        .dataset
                                                        .page
                                                )
                                            );
                                        } catch (error) {
                                            alert(
                                                error.message
                                                || 'Unable to load notices.'
                                            );
                                        }

                                        return;
                                    }

                                    closeMenus();
                                }
                            );

                            document.addEventListener(
                                'keydown',
                                function (event) {
                                    if (
                                        event.key
                                        === 'Escape'
                                    ) {
                                        closeMenus();
                                        closeView();
                                    }
                                }
                            );

                            syncAudience();
                            syncRotation();
                        }
                    );
                </script>

            @elseif($tab === 'updates')

                <div class="mb-4">
                    <h2 class="es-settings-section-title">
                        {{ $currentSubs[$sub] ?? 'Migration & Updates' }}
                    </h2>

                    <p class="es-settings-section-copy">
                        Manage safe, versioned Core and product updates across existing Esubiz websites
                        without rerunning tenant installers, seeders or initialization processes.
                    </p>
                </div>

                @if($sub === 'overview')

                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6 col-xl">
                            <div class="es-setting-card h-100">
                                <div class="text-muted small">Registered Updates</div>
                                <div class="fs-3 fw-bold mt-2">{{ $platformUpdateStats['updates'] }}</div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-xl">
                            <div class="es-setting-card h-100">
                                <div class="text-muted small">Published</div>
                                <div class="fs-3 fw-bold mt-2">{{ $platformUpdateStats['published'] }}</div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-xl">
                            <div class="es-setting-card h-100">
                                <div class="text-muted small">Pending Runs</div>
                                <div class="fs-3 fw-bold mt-2">{{ $platformUpdateStats['pending_runs'] }}</div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-xl">
                            <div class="es-setting-card h-100">
                                <div class="text-muted small">Failed Runs</div>
                                <div class="fs-3 fw-bold mt-2">{{ $platformUpdateStats['failed_runs'] }}</div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6 col-xl">
                            <div class="es-setting-card h-100">
                                <div class="text-muted small">Websites</div>
                                <div class="fs-3 fw-bold mt-2">{{ $platformUpdateStats['websites'] }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="es-setting-card">
                        <h3>Safe Update Process</h3>

                        <div class="row g-3 mt-1">
                            <div class="col-12 col-lg-4">
                                <div class="border rounded-3 p-3 h-100">
                                    <div class="fw-bold mb-1">1. Preview / Dry Run</div>
                                    <div class="text-muted small">
                                        Detect eligible tenants, prerequisites and pending changes without modifying tenant data.
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-lg-4">
                                <div class="border rounded-3 p-3 h-100">
                                    <div class="fw-bold mb-1">2. Checkpoint</div>
                                    <div class="text-muted small">
                                        Record the tenant state and required backup information before an approved update.
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-lg-4">
                                <div class="border rounded-3 p-3 h-100">
                                    <div class="fw-bold mb-1">3. Update & Audit</div>
                                    <div class="text-muted small">
                                        Apply only controlled versioned changes and record each tenant result centrally.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                @elseif($sub === 'updates')

                    <style>
                        /* ESUBIZ_PLATFORM_UPDATE_AUTO_GRID_V5 */

                        /*
                         * Do not depend on the surrounding Site Settings
                         * Bootstrap/flex rules for this form.
                         *
                         * Desktop: 3 settings per row
                         * Tablet:  2 settings per row
                         * Mobile:  1 setting per row
                         */

                        #esPlatformUpdateSettingsFormV1 > .row.g-3 {
                            display:grid !important;
                            grid-template-columns:repeat(3, minmax(0, 1fr));
                            gap:16px !important;
                            margin-left:0 !important;
                            margin-right:0 !important;
                            width:100%;
                        }

                        #esPlatformUpdateSettingsFormV1 > .row.g-3 > [class*="col-"] {
                            width:auto !important;
                            max-width:none !important;
                            flex:none !important;
                            padding-left:0 !important;
                            padding-right:0 !important;
                            margin-top:0 !important;
                            min-width:0;
                        }

                        /*
                         * The Update Mode selector occupies its own full row.
                         * Automatic options begin beneath it.
                         */
                        #esPlatformUpdateSettingsFormV1 > .row.g-3:first-of-type
                        > [class*="col-"]:first-child {
                            grid-column:1 / -1;
                        }

                        /*
                         * Keep each automatic option visually contained.
                         */
                        #esPlatformUpdateSettingsFormV1 .form-control,
                        #esPlatformUpdateSettingsFormV1 .form-select,
                        #esPlatformUpdateSettingsFormV1 .input-group {
                            width:100%;
                        }

                        #esPlatformUpdateSettingsFormV1
                        > .row.g-3
                        > [class*="col-"]
                        > .border {
                            width:100%;
                            height:100%;
                        }

                        @media (max-width: 991.98px) {
                            #esPlatformUpdateSettingsFormV1 > .row.g-3 {
                                grid-template-columns:repeat(2, minmax(0, 1fr));
                            }

                            #esPlatformUpdateSettingsFormV1 > .row.g-3:first-of-type
                            > [class*="col-"]:first-child {
                                grid-column:1 / -1;
                            }
                        }

                        @media (max-width: 767.98px) {
                            #esPlatformUpdateSettingsFormV1 > .row.g-3 {
                                grid-template-columns:1fr;
                            }

                            #esPlatformUpdateSettingsFormV1 > .row.g-3:first-of-type
                            > [class*="col-"]:first-child {
                                grid-column:auto;
                            }
                        }

                        /* ESUBIZ_PLATFORM_UPDATE_PREMIUM_LAYOUT_V4 */
                        .es-upd-premium-save-v4 {
                            display:inline-flex;
                            align-items:center;
                            justify-content:center;
                            gap:9px;
                            min-height:44px;
                            padding:11px 20px;
                            border:1px solid #0b1f3a;
                            border-radius:10px;
                            background:#0b1f3a;
                            color:#fff;
                            font-size:13px;
                            font-weight:800;
                            line-height:1;
                            cursor:pointer;
                            box-shadow:0 7px 18px rgba(11,31,58,.18);
                            transition:
                                transform .16s ease,
                                box-shadow .16s ease,
                                background .16s ease,
                                border-color .16s ease;
                        }

                        .es-upd-premium-save-v4:hover {
                            background:#132d50;
                            border-color:#132d50;
                            color:#fff;
                            transform:translateY(-1px);
                            box-shadow:0 10px 24px rgba(11,31,58,.24);
                        }

                        .es-upd-premium-save-v4:active {
                            transform:translateY(0);
                            box-shadow:0 4px 12px rgba(11,31,58,.18);
                        }

                        .es-upd-premium-save-v4:focus {
                            outline:0;
                            box-shadow:
                                0 0 0 3px rgba(11,31,58,.14),
                                0 8px 20px rgba(11,31,58,.20);
                        }

                        .es-upd-premium-save-v4:disabled {
                            opacity:.65;
                            cursor:not-allowed;
                            transform:none;
                            box-shadow:none;
                        }

                        .es-upd-save-icon-v4 {
                            width:20px;
                            height:20px;
                            display:inline-flex;
                            align-items:center;
                            justify-content:center;
                            border-radius:50%;
                            background:rgba(255,255,255,.14);
                            font-size:11px;
                            font-weight:900;
                        }

                        /* ESUBIZ_PLATFORM_UPDATE_PREMIUM_MODE_TOGGLE_V3 */
                        .es-upd-mode-toggle-v3 {
                            display:inline-flex;
                            align-items:center;
                            padding:4px;
                            border:1px solid #dfe6ef;
                            border-radius:12px;
                            background:#f4f7fb;
                            box-shadow:inset 0 1px 2px rgba(15, 23, 42, .04);
                        }

                        .es-upd-mode-option-v3 {
                            min-width:110px;
                            height:40px;
                            padding:0 18px;
                            border:0;
                            border-radius:9px;
                            background:transparent;
                            color:#64748b;
                            font-size:13px;
                            font-weight:700;
                            cursor:pointer;
                            transition:all .18s ease;
                        }

                        .es-upd-mode-option-v3:hover {
                            color:#172033;
                        }

                        .es-upd-mode-option-v3.active {
                            background:#172033;
                            color:#fff;
                            box-shadow:0 4px 12px rgba(15, 23, 42, .16);
                        }

                        /* ESUBIZ_PLATFORM_UPDATE_TABLE_V8 */

                        .es-upd-toolbar-v8 {
                            display:flex;
                            align-items:center;
                            justify-content:space-between;
                            flex-wrap:wrap;
                            gap:14px;
                            margin-bottom:18px;
                        }

                        .es-upd-toolbar-v8 h3 {
                            margin:0 0 4px;
                            color:#172033;
                            font-size:18px;
                            font-weight:800;
                        }

                        .es-upd-table-card-v8 {
                            background:#fff;
                            border:1px solid #e3e9f2;
                            border-radius:16px;
                            overflow:visible;
                            box-shadow:0 8px 28px rgba(24,44,78,.045);
                        }

                        .es-upd-table-scroll-v8 {
                            overflow-x:auto;
                            overflow-y:visible;
                        }

                        .es-upd-table-v8 {
                            width:100%;
                            margin:0;
                            border-collapse:collapse;
                        }

                        .es-upd-table-v8 th {
                            padding:14px 16px;
                            background:#f8fafc;
                            border-bottom:1px solid #e5eaf1;
                            color:#66758a;
                            font-size:11px;
                            font-weight:800;
                            text-transform:uppercase;
                            letter-spacing:.04em;
                            white-space:nowrap;
                        }

                        .es-upd-table-v8 td {
                            padding:15px 16px;
                            border-bottom:1px solid #edf1f5;
                            color:#253044;
                            font-size:13px;
                            vertical-align:middle;
                        }

                        .es-upd-table-v8 tbody tr:last-child td {
                            border-bottom:0;
                        }

                        .es-upd-name-v8 {
                            font-weight:800;
                            color:#172033;
                            margin-bottom:3px;
                        }

                        .es-upd-source-v8 {
                            max-width:360px;
                            overflow:hidden;
                            text-overflow:ellipsis;
                            white-space:nowrap;
                            color:#8490a1;
                            font-size:11px;
                        }

                        .es-upd-badge-v8 {
                            display:inline-flex;
                            align-items:center;
                            min-height:26px;
                            padding:4px 9px;
                            border-radius:999px;
                            background:#f1f4f8;
                            color:#5c697c;
                            font-size:11px;
                            font-weight:750;
                            white-space:nowrap;
                        }

                        .es-upd-badge-v8.blue {
                            background:#eaf2ff;
                            color:#1464f4;
                        }

                        .es-upd-badge-v8.green {
                            background:#e9f8ef;
                            color:#198754;
                        }

                        .es-upd-badge-v8.amber {
                            background:#fff4dc;
                            color:#966300;
                        }

                        .es-upd-badge-v8.red {
                            background:#fff0f0;
                            color:#c43737;
                        }

                        .es-upd-menu-wrap-v8 {
                            position:relative;
                            display:flex;
                            justify-content:flex-end;
                        }

                        .es-upd-menu-btn-v8 {
                            width:36px;
                            height:36px;
                            display:inline-flex;
                            align-items:center;
                            justify-content:center;
                            border:1px solid #e1e7ef;
                            border-radius:10px;
                            background:#fff;
                            color:#425066;
                            font-size:20px;
                            font-weight:800;
                            line-height:1;
                        }

                        .es-upd-menu-v8 {
                            display:none;
                            position:absolute;
                            z-index:1080;
                            right:0;
                            top:41px;
                            width:190px;
                            padding:7px;
                            background:#fff;
                            border:1px solid #dfe5ed;
                            border-radius:12px;
                            box-shadow:0 14px 38px rgba(22,37,63,.16);
                        }

                        .es-upd-menu-v8.active {
                            display:block;
                        }

                        .es-upd-menu-v8 button {
                            display:block;
                            width:100%;
                            padding:9px 10px;
                            border:0;
                            border-radius:8px;
                            background:transparent;
                            color:#344156;
                            text-align:left;
                            font-size:12px;
                            font-weight:650;
                        }

                        .es-upd-menu-v8 button:hover {
                            background:#f3f7fd;
                            color:#1464f4;
                        }

                        .es-upd-menu-v8 button:disabled {
                            opacity:.45;
                            cursor:not-allowed;
                        }

                        .es-upd-pagination-v8 {
                            display:flex;
                            align-items:center;
                            justify-content:space-between;
                            flex-wrap:wrap;
                            gap:12px;
                            padding:14px 16px;
                            border-top:1px solid #e8edf3;
                        }

                        .es-upd-page-buttons-v8 {
                            display:flex;
                            gap:8px;
                        }

                        .es-upd-page-buttons-v8 button {
                            border:1px solid #dce3ec;
                            background:#fff;
                            color:#425066;
                            border-radius:9px;
                            padding:8px 13px;
                            font-size:12px;
                            font-weight:700;
                        }

                        .es-upd-page-buttons-v8 button:disabled {
                            opacity:.45;
                        }

                        .es-upd-empty-v8 {
                            padding:48px 20px;
                            text-align:center;
                            color:#78869a;
                        }

                        .es-upd-action-modal-v8 .modal-content {
                            border:0;
                            border-radius:17px;
                            overflow:hidden;
                        }

                        /* ESUBIZ_PLATFORM_UPDATE_MODAL_V9 */
                        #esUpdateActionModalV8 {
                            display:none !important;
                            position:fixed;
                            inset:0;
                            z-index:9999;
                            width:100%;
                            height:100%;
                            padding:24px;
                            overflow-y:auto;
                            background:rgba(15, 23, 42, .55);
                            align-items:center;
                            justify-content:center;
                        }

                        #esUpdateActionModalV8.es-upd-modal-open-v9,

                        #esUpdateActionModalV8 .modal-dialog,

                        #esUpdateActionModalV8 .modal-content,

                        #esUpdateActionModalV8 .modal-header,

                        #esUpdateActionModalV8 .modal-body,

                        #esUpdateActionModalV8 .modal-footer,

                        .es-upd-close-v9 {
                            width:34px;
                            height:34px;
                            border:0;
                            border-radius:9px;
                            background:#f3f6fa;
                            color:#475569;
                            font-size:20px;
                            line-height:1;
                            display:inline-flex;
                            align-items:center;
                            justify-content:center;
                            cursor:pointer;
                        }

                        .es-upd-close-v9:hover {
                            background:#e9eef5;
                        }

                        body.es-upd-modal-lock-v9 {
                            overflow:hidden;
                        }

                        @media(max-width:767.98px) {
                            #esUpdateActionModalV8 {
                                padding:12px;
                                align-items:flex-start;
                            }

                            #esUpdateActionModalV8 .modal-content,
                        }

                        .es-upd-target-box-v8 {
                            padding:15px;
                            border:1px solid #e3e9f2;
                            border-radius:13px;
                            background:#f9fbfe;
                        }

                        .es-upd-results-v8 {
                            margin-top:15px;
                            border:1px solid #e1e7ef;
                            border-radius:12px;
                            overflow:hidden;
                        }

                        /* ESUBIZ_UPDATE_TARGET_LAYOUT_V11 */
                        .es-upd-target-grid-v11 {
                            display:grid;
                            grid-template-columns:minmax(0, 1fr) minmax(0, 1fr);
                            gap:16px;
                            align-items:start;
                            width:100%;
                        }

                        .es-upd-target-field-v11 {
                            min-width:0;
                            width:100%;
                        }

                        .es-upd-target-field-v11 .form-select,
                        .es-upd-target-field-v11 .es-upd-multi-v10 {
                            width:100%;
                        }

                        @media (max-width: 767px) {
                            .es-upd-target-grid-v11 {
                                grid-template-columns:1fr;
                            }
                        }

                        /* ESUBIZ_UPDATE_TENANT_MULTISELECT_V10 */
                        .es-upd-multi-v10 {
                            position:relative;
                        }

                        .es-upd-multi-button-v10 {
                            width:100%;
                            min-height:42px;
                            display:flex;
                            align-items:center;
                            justify-content:space-between;
                            gap:12px;
                            padding:9px 12px;
                            border:1px solid #d7dee8;
                            border-radius:9px;
                            background:#fff;
                            color:#344156;
                            text-align:left;
                            font-size:13px;
                        }

                        .es-upd-multi-button-v10:focus {
                            border-color:#86afff;
                            box-shadow:0 0 0 3px rgba(20,100,244,.10);
                            outline:0;
                        }

                        .es-upd-multi-arrow-v10 {
                            color:#7a8799;
                            transition:transform .18s ease;
                        }

                        .es-upd-multi-button-v10[aria-expanded="true"]
                        .es-upd-multi-arrow-v10 {
                            transform:rotate(180deg);
                        }

                        .es-upd-multi-menu-v10 {
                            display:none;
                            position:absolute;
                            z-index:10050;
                            top:calc(100% + 7px);
                            left:0;
                            right:0;
                            background:#fff;
                            border:1px solid #dce3ec;
                            border-radius:12px;
                            box-shadow:0 16px 38px rgba(22,37,63,.16);
                            overflow:hidden;
                        }

                        .es-upd-multi-menu-v10.active {
                            display:block;
                        }

                        .es-upd-multi-tools-v10 {
                            display:flex;
                            justify-content:space-between;
                            gap:8px;
                            padding:9px 11px;
                            border-bottom:1px solid #edf1f5;
                            background:#f8fafc;
                        }

                        .es-upd-multi-tools-v10 button {
                            border:0;
                            background:transparent;
                            color:#1464f4;
                            padding:3px 5px;
                            font-size:11px;
                            font-weight:750;
                        }

                        .es-upd-multi-list-v10 {
                            max-height:250px;
                            overflow-y:auto;
                            padding:6px;
                        }

                        .es-upd-multi-option-v10 {
                            display:flex;
                            align-items:center;
                            gap:10px;
                            padding:9px 8px;
                            margin:0;
                            border-radius:8px;
                            cursor:pointer;
                        }

                        .es-upd-multi-option-v10:hover {
                            background:#f3f7fd;
                        }

                        .es-upd-multi-option-v10 input {
                            width:16px;
                            height:16px;
                            flex:0 0 auto;
                            accent-color:#1464f4;
                        }

                        .es-upd-multi-option-v10 span {
                            min-width:0;
                            display:flex;
                            flex-direction:column;
                        }

                        .es-upd-multi-option-v10 strong {
                            color:#344156;
                            font-size:12px;
                            font-weight:700;
                            overflow:hidden;
                            text-overflow:ellipsis;
                            white-space:nowrap;
                        }

                        .es-upd-multi-option-v10 small {
                            color:#8a96a7;
                            font-size:10px;
                        }

                        .es-upd-result-v8 {
                            display:flex;
                            justify-content:space-between;
                            gap:14px;
                            padding:11px 13px;
                            border-bottom:1px solid #edf1f5;
                            font-size:12px;
                        }

                        .es-upd-result-v8:last-child {
                            border-bottom:0;
                        }

                        @media(max-width:767.98px) {
                            .es-upd-table-v8 {
                                min-width:900px;
                            }
                        }
                    
                    /* ESUBIZ_PLATFORM_UPDATE_DRY_RUN_RESULTS_V16 */
                    .es-upd-dry-summary-v16 {
                        margin-bottom: 14px;
                    }

                    .es-upd-dry-safe-v16 {
                        display: flex;
                        flex-direction: column;
                        gap: 4px;
                        padding: 13px 14px;
                        border: 1px solid rgba(37, 99, 235, .20);
                        border-radius: 10px;
                        background: rgba(37, 99, 235, .06);
                    }

                    .es-upd-dry-safe-v16 span {
                        font-size: 12px;
                        opacity: .78;
                    }

                    .es-upd-dry-counts-v16 {
                        display: flex;
                        flex-wrap: wrap;
                        gap: 8px;
                        margin-top: 10px;
                    }

                    .es-upd-dry-counts-v16 span {
                        padding: 5px 9px;
                        border-radius: 999px;
                        background: rgba(127, 127, 127, .10);
                        font-size: 12px;
                    }

                    .es-upd-result-v16 {
                        display: flex;
                        justify-content: space-between;
                        align-items: flex-start;
                        gap: 14px;
                        padding: 13px 0;
                        border-bottom: 1px solid rgba(127, 127, 127, .14);
                    }

                    .es-upd-result-main-v16 {
                        min-width: 0;
                    }

                    .es-upd-result-migration-v16 {
                        margin: 3px 0;
                        font-family: monospace;
                        font-size: 11px;
                        overflow-wrap: anywhere;
                        opacity: .68;
                    }

                    .es-upd-state-v16 {
                        flex: 0 0 auto;
                        display: inline-flex;
                        align-items: center;
                        padding: 5px 9px;
                        border-radius: 999px;
                        font-size: 11px;
                        font-weight: 700;
                        white-space: nowrap;
                        background: rgba(127, 127, 127, .12);
                    }

                    .es-upd-state-already_applied {
                        background: rgba(22, 163, 74, .10);
                    }

                    .es-upd-state-pending {
                        background: rgba(234, 179, 8, .14);
                    }

                    .es-upd-state-skipped {
                        background: rgba(107, 114, 128, .12);
                    }

                    .es-upd-state-unable_to_verify {
                        background: rgba(220, 38, 38, .10);
                    }

                    @media (max-width: 767px) {
                        .es-upd-result-v16 {
                            flex-direction: column;
                        }
                    }
</style>

                    <div class="es-upd-toolbar-v8">
                        <div>
                    {{-- ESUBIZ_PLATFORM_UPDATE_SETTINGS_UI_V1 --}}
                    {{-- ESUBIZ_DASHBOARD_NOTICE_DECOUPLE_V9 --}}
                    @if($platformUpdateSettings)
                        <div class="es-setting-card mb-4">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
                                <div>
                                    <h3 class="mb-1">Platform Update Settings</h3>
                                    <div class="text-muted small">
                                        Configure how Esubiz prepares, protects and notifies tenants during platform updates.
                                    </div>
                                </div>

                                <span
                                    id="esPlatformUpdateSettingsStatusV1"
                                    class="badge bg-light text-secondary border"
                                >
                                    Saved
                                </span>
                            </div>

                            <form
                                id="esPlatformUpdateSettingsFormV1"
                                data-url="{{ route('admin.site-settings.updates.settings.update') }}"
                            >
                                @csrf

                                <div class="row g-3">
                                    {{-- ESUBIZ_PLATFORM_UPDATE_MODE_TOGGLE_V2 --}}
                                    <div class="col-12">
                                        <div class="border rounded-3 p-3">
                                            <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
                                                <div>
                                                    <div class="fw-semibold">
                                                        Update Mode
                                                    </div>

                                                    <div
                                                        id="esUpdModeDescriptionV2"
                                                        class="text-muted small mt-1"
                                                    >
                                                        Manual updates are currently active.
                                                    </div>
                                                </div>

                                                {{-- ESUBIZ_PLATFORM_UPDATE_PREMIUM_MODE_TOGGLE_V3 --}}
                                                <div class="es-upd-mode-toggle-v3">
                                                    <input
                                                        type="hidden"
                                                        name="mode"
                                                        id="esUpdModeValueV2"
                                                        value="{{ $platformUpdateSettings->mode }}"
                                                    >

                                                    <button
                                                        type="button"
                                                        id="esUpdManualBtnV3"
                                                        class="es-upd-mode-option-v3"
                                                        data-mode="manual"
                                                        aria-pressed="{{ $platformUpdateSettings->mode === 'manual' ? 'true' : 'false' }}"
                                                    >
                                                        Manual
                                                    </button>

                                                    <button
                                                        type="button"
                                                        id="esUpdAutomaticBtnV3"
                                                        class="es-upd-mode-option-v3"
                                                        data-mode="automatic"
                                                        aria-pressed="{{ $platformUpdateSettings->mode === 'automatic' ? 'true' : 'false' }}"
                                                    >
                                                        Automatic
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-12 col-lg-4">
                                        <label class="form-label fw-semibold">Default Timing</label>
                                        <select name="default_timing" class="form-select">
                                            <option value="immediate"
                                                @selected($platformUpdateSettings->default_timing === 'immediate')>
                                                Immediate
                                            </option>
                                            <option value="delayed"
                                                @selected($platformUpdateSettings->default_timing === 'delayed')>
                                                Delayed
                                            </option>
                                        </select>
                                    </div>

                                    <div class="col-12 col-md-6 col-lg-4">
                                        <label class="form-label fw-semibold">Delay</label>
                                        <input
                                            type="number"
                                            name="delay_value"
                                            min="0"
                                            max="525600"
                                            class="form-control"
                                            value="{{ $platformUpdateSettings->delay_value }}"
                                        >
                                    </div>

                                    <div class="col-12 col-md-6 col-lg-4">
                                        <label class="form-label fw-semibold">Unit</label>
                                        <select name="delay_unit" class="form-select">
                                            @foreach(['minutes', 'hours', 'days'] as $unit)
                                                <option value="{{ $unit }}"
                                                    @selected($platformUpdateSettings->delay_unit === $unit)>
                                                    {{ ucfirst($unit) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-12 col-lg-4">
                                        <label class="form-label fw-semibold">Timezone</label>
                                        <input
                                            type="text"
                                            name="timezone"
                                            class="form-control"
                                            value="{{ $platformUpdateSettings->timezone }}"
                                        >
                                    </div>

                                    <div class="col-12 col-lg-4">
                                        <label class="form-label fw-semibold">Successful Backup Retention</label>
                                        <div class="input-group">
                                            <input
                                                type="number"
                                                name="success_backup_days"
                                                min="1"
                                                max="3650"
                                                class="form-control"
                                                value="{{ $platformUpdateSettings->success_backup_days }}"
                                            >
                                            <span class="input-group-text">days</span>
                                        </div>
                                    </div>

                                    <div class="col-12 col-lg-4">
                                        <label class="form-label fw-semibold">Failed Backup Retention</label>
                                        <div class="input-group">
                                            <input
                                                type="number"
                                                name="failed_backup_days"
                                                min="1"
                                                max="3650"
                                                class="form-control"
                                                value="{{ $platformUpdateSettings->failed_backup_days }}"
                                            >
                                            <span class="input-group-text">days</span>
                                        </div>
                                    </div>
                                </div>

                                <hr class="my-4">

                                <div class="row g-3">
                                    @php
                                        $updateToggleFields = [
                                            'backup_enabled' => [
                                                'label' => 'Backup Before Update',
                                                'description' => 'Create a protected rollback point before eligible updates.',
                                            ],
                                            'auto_restore_failure' => [
                                                'label' => 'Auto Restore on Failure',
                                                'description' => 'Restore the protected state when an update fails.',
                                            ],
                                            'health_check_enabled' => [
                                                'label' => 'Post-update Health Check',
                                                'description' => 'Verify tenant health after an update finishes.',
                                            ],
                                            'cleanup_enabled' => [
                                                'label' => 'Automatic Cleanup',
                                                'description' => 'Remove expired update backups according to retention rules.',
                                            ],
                                            'email_notice' => [
                                                'label' => 'Email Notice',
                                                'description' => 'Send update notifications to applicable tenants.',
                                            ],
                                        ];
                                    @endphp

                                    @foreach($updateToggleFields as $field => $meta)
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <div class="border rounded-3 p-3 h-100">
                                                <div class="form-check form-switch">
                                                    <input
                                                        type="hidden"
                                                        name="{{ $field }}"
                                                        value="0"
                                                    >
                                                    <input
                                                        class="form-check-input"
                                                        type="checkbox"
                                                        role="switch"
                                                        id="esUpdSetting_{{ $field }}"
                                                        name="{{ $field }}"
                                                        value="1"
                                                        @checked((bool)$platformUpdateSettings->{$field})
                                                    >
                                                    <label
                                                        class="form-check-label fw-semibold"
                                                        for="esUpdSetting_{{ $field }}"
                                                    >
                                                        {{ $meta['label'] }}
                                                    </label>
                                                </div>

                                                <div class="text-muted small mt-2">
                                                    {{ $meta['description'] }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="row g-3 mt-1">
                                    <div class="col-12 col-md-6 col-lg-4">
                                        <label class="form-label fw-semibold">Notice Before</label>
                                        <input
                                            type="number"
                                            name="notice_value"
                                            min="0"
                                            max="525600"
                                            class="form-control"
                                            value="{{ $platformUpdateSettings->notice_value }}"
                                        >
                                    </div>

                                    <div class="col-12 col-md-6 col-lg-4">
                                        <label class="form-label fw-semibold">Unit</label>
                                        <select name="notice_unit" class="form-select">
                                            @foreach(['minutes', 'hours', 'days'] as $unit)
                                                <option value="{{ $unit }}"
                                                    @selected($platformUpdateSettings->notice_unit === $unit)>
                                                    {{ ucfirst($unit) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-12 col-lg-4 d-flex align-items-end">
                                        <div class="w-100 border rounded-3 p-3 bg-light">
                                            <div class="form-check form-switch mb-0">
                                                <input
                                                    type="hidden"
                                                    name="scheduler_enabled"
                                                    value="0"
                                                >
                                                <input
                                                    class="form-check-input"
                                                    type="checkbox"
                                                    role="switch"
                                                    id="esUpdSchedulerEnabledV1"
                                                    name="scheduler_enabled"
                                                    value="1"
                                                    @checked((bool)$platformUpdateSettings->scheduler_enabled)
                                                >
                                                <label
                                                    class="form-check-label fw-semibold"
                                                    for="esUpdSchedulerEnabledV1"
                                                >
                                                    Automatic Scheduler
                                                </label>
                                            </div>
                                            <div class="text-muted small mt-2">
                                                Scheduler activation is centrally protected until the automatic update lifecycle is officially enabled.
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div
                                    id="esPlatformUpdateSettingsMessageV1"
                                    class="small mt-3"
                                    style="display:none"
                                ></div>

                                <div class="d-flex justify-content-end mt-4">
                                    {{-- ESUBIZ_PLATFORM_UPDATE_PREMIUM_LAYOUT_V4 --}}
                                    <button
                                        type="submit"
                                        class="es-upd-premium-save-v4"
                                        id="esPlatformUpdateSettingsSaveV1"
                                    >
                                        <span class="es-upd-save-icon-v4">✓</span>
                                        <span>Save Update Settings</span>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <script>
                            document.addEventListener('DOMContentLoaded', function () {
                                const form = document.getElementById(
                                    'esPlatformUpdateSettingsFormV1'
                                );

                                if (!form) {
                                    return;
                                }

                                const button = document.getElementById(
                                    'esPlatformUpdateSettingsSaveV1'
                                );

                                const status = document.getElementById(
                                    'esPlatformUpdateSettingsStatusV1'
                                );

                                const message = document.getElementById(
                                    'esPlatformUpdateSettingsMessageV1'
                                );

                                /*
                                 * ESUBIZ_PLATFORM_UPDATE_MODE_TOGGLE_V2
                                 *
                                 * Manual is the safe/default workflow.
                                 * Automatic-only lifecycle settings remain hidden
                                 * until Admin deliberately enables Automatic mode.
                                 */
                                const modeValue = document.getElementById(
                                    'esUpdModeValueV2'
                                );

                                const manualButton = document.getElementById(
                                    'esUpdManualBtnV3'
                                );

                                const automaticButton = document.getElementById(
                                    'esUpdAutomaticBtnV3'
                                );

                                const modeDescription = document.getElementById(
                                    'esUpdModeDescriptionV2'
                                );

                                const automaticFields = [
                                    'default_timing',
                                    'delay_value',
                                    'delay_unit',
                                    'timezone',
                                    'success_backup_days',
                                    'failed_backup_days',
                                    'backup_enabled',
                                    'auto_restore_failure',
                                    'health_check_enabled',
                                    'cleanup_enabled',
                                    'email_notice',
                                    'notice_value',
                                    'notice_unit',
                                    'scheduler_enabled'
                                ];

                                /*
                                 * ESUBIZ_PLATFORM_UPDATE_TIMING_VISIBILITY_V6
                                 *
                                 * Immediate execution has no delay configuration.
                                 * Delay amount/unit are only relevant when Delayed
                                 * timing is selected.
                                 */
                                const defaultTimingSelect = form.querySelector(
                                    '[name="default_timing"]'
                                );

                                const delayValueControl = form.querySelector(
                                    '[name="delay_value"]'
                                );

                                const delayUnitControl = form.querySelector(
                                    '[name="delay_unit"]'
                                );

                                function timingFieldContainer(control) {
                                    if (!control) {
                                        return null;
                                    }

                                    return control.closest(
                                        '.col-12, .col-6'
                                    );
                                }

                                const delayValueContainer =
                                    timingFieldContainer(delayValueControl);

                                const delayUnitContainer =
                                    timingFieldContainer(delayUnitControl);

                                function syncDefaultTimingV6() {
                                    const automatic =
                                        modeValue.value === 'automatic';

                                    const delayed =
                                        defaultTimingSelect &&
                                        defaultTimingSelect.value === 'delayed';

                                    const showDelay =
                                        automatic && delayed;

                                    if (delayValueContainer) {
                                        delayValueContainer.style.display =
                                            showDelay ? '' : 'none';
                                    }

                                    if (delayUnitContainer) {
                                        delayUnitContainer.style.display =
                                            showDelay ? '' : 'none';
                                    }
                                }

                                function automaticFieldContainer(fieldName) {
                                    const control = form.querySelector(
                                        '[name="' + fieldName + '"]'
                                    );

                                    if (!control) {
                                        return null;
                                    }

                                    return control.closest(
                                        '.col-12, .col-6'
                                    );
                                }

                                const separator = Array.from(
                                    form.querySelectorAll('hr')
                                ).find(function (item) {
                                    return item.classList.contains('my-4');
                                });

                                function syncUpdateModeV2() {
                                    const automatic =
                                        modeValue.value === 'automatic';

                                    automaticFields.forEach(function (field) {
                                        const container =
                                            automaticFieldContainer(field);

                                        if (container) {
                                            container.style.display =
                                                automatic ? '' : 'none';
                                        }
                                    });

                                    if (separator) {
                                        separator.style.display =
                                            automatic ? '' : 'none';
                                    }

                                    if (manualButton) {
                                        manualButton.classList.toggle(
                                            'active',
                                            !automatic
                                        );

                                        manualButton.setAttribute(
                                            'aria-pressed',
                                            automatic ? 'false' : 'true'
                                        );
                                    }

                                    if (automaticButton) {
                                        automaticButton.classList.toggle(
                                            'active',
                                            automatic
                                        );

                                        automaticButton.setAttribute(
                                            'aria-pressed',
                                            automatic ? 'true' : 'false'
                                        );
                                    }

                                    if (modeDescription) {
                                        modeDescription.textContent = automatic
                                            ? 'Automatic update configuration is enabled. Configure the lifecycle settings below.'
                                            : 'Manual updates are currently active. Automatic lifecycle settings are hidden.';
                                    }

                                    syncDefaultTimingV6();
                                }

                                if (defaultTimingSelect) {
                                    defaultTimingSelect.addEventListener(
                                        'change',
                                        syncDefaultTimingV6
                                    );
                                }

                                if (manualButton) {
                                    manualButton.addEventListener(
                                        'click',
                                        function () {
                                            modeValue.value = 'manual';
                                            syncUpdateModeV2();
                                        }
                                    );
                                }

                                if (automaticButton) {
                                    automaticButton.addEventListener(
                                        'click',
                                        function () {
                                            modeValue.value = 'automatic';
                                            syncUpdateModeV2();
                                        }
                                    );
                                }

                                syncUpdateModeV2();

                                form.addEventListener('submit', async function (event) {
                                    event.preventDefault();

                                    button.disabled = true;
                                    button.textContent = 'Saving...';

                                    message.style.display = 'none';
                                    message.className = 'small mt-3';

                                    const formData = new FormData(form);
                                    const payload = {};

                                    for (const [key, value] of formData.entries()) {
                                        payload[key] = value;
                                    }

                                    const booleanFields = [
                                        'backup_enabled',
                                        'auto_restore_failure',
                                        'health_check_enabled',
                                        'cleanup_enabled',
                                        'email_notice',
                                        'scheduler_enabled'
                                    ];

                                    booleanFields.forEach(function (field) {
                                        payload[field] =
                                            form.querySelector(
                                                '[name="' + field + '"][type="checkbox"]'
                                            )?.checked
                                                ? 1
                                                : 0;
                                    });

                                    payload.delay_value =
                                        Number(payload.delay_value || 0);

                                    payload.success_backup_days =
                                        Number(payload.success_backup_days || 1);

                                    payload.failed_backup_days =
                                        Number(payload.failed_backup_days || 1);

                                    payload.notice_value =
                                        Number(payload.notice_value || 0);

                                    try {
                                        const response = await fetch(
                                            form.dataset.url,
                                            {
                                                method: 'PATCH',
                                                headers: {
                                                    'Accept': 'application/json',
                                                    'Content-Type': 'application/json',
                                                    'X-CSRF-TOKEN':
                                                        form.querySelector(
                                                            '[name="_token"]'
                                                        ).value
                                                },
                                                body: JSON.stringify(payload)
                                            }
                                        );

                                        const data = await response.json();

                                        if (!response.ok || !data.ok) {
                                            let errorMessage =
                                                data.message ||
                                                'Unable to save update settings.';

                                            if (data.errors) {
                                                const firstError =
                                                    Object.values(data.errors)
                                                        .flat()[0];

                                                if (firstError) {
                                                    errorMessage = firstError;
                                                }
                                            }

                                            throw new Error(errorMessage);
                                        }

                                        status.textContent = 'Saved';
                                        status.className =
                                            'badge bg-success-subtle text-success border';

                                        message.textContent =
                                            data.message ||
                                            'Update lifecycle settings saved.';

                                        message.className =
                                            'small mt-3 text-success';

                                        message.style.display = 'block';

                                        if (
                                            data.settings &&
                                            data.settings.automatic_ready === false
                                        ) {
                                            const scheduler =
                                                document.getElementById(
                                                    'esUpdSchedulerEnabledV1'
                                                );

                                            if (scheduler) {
                                                scheduler.checked = false;
                                            }
                                        }
                                    } catch (error) {
                                        status.textContent = 'Not saved';
                                        status.className =
                                            'badge bg-danger-subtle text-danger border';

                                        message.textContent = error.message;
                                        message.className =
                                            'small mt-3 text-danger';

                                        message.style.display = 'block';
                                    } finally {
                                        button.disabled = false;
                                        button.textContent =
                                            'Save Update Settings';
                                    }
                                });
                            });
                        </script>
                    @endif

                            <h3>Available Updates</h3>
                            <div class="text-muted small">
                                Core updates are detected automatically. Review, dry-run and deploy them from here.
                            </div>
                        </div>

                        <!-- ESUBIZ_PLATFORM_UPDATE_MANUAL_REGISTER_HIDDEN_V7 -->
                        <!-- ESUBIZ_REGISTER_UPDATE_DEAD_CODE_CLEANUP_V8 -->
                    </div>

                    <div class="es-upd-table-card-v8">

                        @if($platformUpdates->isEmpty())

                            <div class="es-upd-empty-v8">
                                <div class="fw-bold mb-2">Everything is up to date</div>
                                <div class="small">
                                    No Core or product updates are currently registered.
                                </div>
                            </div>

                        @else

                            <div class="es-upd-table-scroll-v8">
                                <table class="es-upd-table-v8">
                                    <thead>
                                        <tr>
                                            <th>Update</th>
                                            <th>Product</th>
                                            <th>Version</th>
                                            <th>Type</th>
                                            <th>Status</th>
                                            <th>Safety</th>
                                            <th style="text-align:right;">Actions</th>
                                        </tr>
                                    </thead>

                                    <tbody id="esUpdateTableBodyV8">
                                        @foreach($platformUpdates as $update)

                                            @php
                                                $statusClass = match($update->status) {
                                                    'published' => 'green',
                                                    'disabled' => 'red',
                                                    default => 'amber',
                                                };

                                                $canRun =
                                                    $update->status === 'published'
                                                    && !(bool)$update->is_destructive
                                                    && !(bool)$update->requires_backup
                                                    && $update->product_type === 'core';
                                            @endphp

                                            <tr
                                                class="es-upd-row-v8"
                                                data-update-id="{{ $update->id }}"
                                                data-update-name="{{ e($update->name) }}"
                                                data-update-version="{{ e($update->version) }}"
                                                data-update-description="{{ e($update->description ?: '') }}"
                                                data-update-source="{{ e($update->migration_path ?: $update->package_path ?: '') }}"
                                                data-dry-url="{{ route('admin.site-settings.updates.dry-run', $update->id) }}"
                                                data-run-url="{{ route('admin.site-settings.updates.execute', $update->id) }}"
                                                data-status-url="{{ route('admin.site-settings.updates.status', $update->id) }}"
                                                data-can-run="{{ $canRun ? '1' : '0' }}"
                                                data-status="{{ $update->status }}"
                                            >
                                                <td>
                                                    <div class="es-upd-name-v8">
                                                        {{ $update->name }}
                                                    </div>

                                                    @if($update->migration_path || $update->package_path)
                                                        <div
                                                            class="es-upd-source-v8"
                                                            title="{{ $update->migration_path ?: $update->package_path }}"
                                                        >
                                                            {{ $update->migration_path ?: $update->package_path }}
                                                        </div>
                                                    @endif
                                                </td>

                                                <td>
                                                    <span class="es-upd-badge-v8">
                                                        {{ ucwords(str_replace('_', ' ', $update->product_type)) }}
                                                    </span>
                                                </td>

                                                <td>
                                                    <span class="es-upd-badge-v8 blue">
                                                        v{{ $update->version }}
                                                    </span>
                                                </td>

                                                <td>
                                                    {{ ucfirst($update->update_type) }}
                                                </td>

                                                <td>
                                                    <span class="es-upd-badge-v8 {{ $statusClass }}">
                                                        {{ ucfirst($update->status) }}
                                                    </span>
                                                </td>

                                                <td>
                                                    @if($update->is_destructive)
                                                        <span class="es-upd-badge-v8 red">
                                                            Destructive
                                                        </span>
                                                    @elseif($update->requires_backup)
                                                        <span class="es-upd-badge-v8 amber">
                                                            Backup Required
                                                        </span>
                                                    @else
                                                        <span class="es-upd-badge-v8 green">
                                                            Ready
                                                        </span>
                                                    @endif
                                                </td>

                                                <td>
                                                    <div class="es-upd-menu-wrap-v8">
                                                        <button
                                                            type="button"
                                                            class="es-upd-menu-btn-v8"
                                                            aria-label="Update actions"
                                                        >
                                                            ⋮
                                                        </button>

                                                        <div class="es-upd-menu-v8">
                                                            <button
                                                                type="button"
                                                                class="es-upd-view-v8"
                                                            >
                                                                View Details
                                                            </button>

                                                            <button
                                                                type="button"
                                                                class="es-upd-dry-open-v8"
                                                            >
                                                                Dry Run
                                                            </button>

                                                            @if($update->status === 'published')
                                                                <button
                                                                    type="button"
                                                                    class="es-upd-status-v8"
                                                                    data-next-status="disabled"
                                                                >
                                                                    Disable Update
                                                                </button>
                                                            @else
                                                                <button
                                                                    type="button"
                                                                    class="es-upd-status-v8"
                                                                    data-next-status="published"
                                                                >
                                                                    Publish Update
                                                                </button>
                                                            @endif

                                                            <button
                                                                type="button"
                                                                class="es-upd-run-open-v8"
                                                                @disabled(!$canRun)
                                                            >
                                                                Run Update
                                                            </button>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>

                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="es-upd-pagination-v8">
                                <div class="small text-muted" id="esUpdatePageInfoV8"></div>

                                <div class="es-upd-page-buttons-v8">
                                    <button type="button" id="esUpdatePrevV8">
                                        Previous
                                    </button>

                                    <button type="button" id="esUpdateNextV8">
                                        Next
                                    </button>
                                </div>
                            </div>

                        @endif
                    </div>

                    {{-- One focused action modal for all update rows --}}
                    <div
                        class="modal fade es-upd-action-modal-v8"
                        id="esUpdateActionModalV8"
                        tabindex="-1"
                        aria-hidden="true"
                    >
                        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content">

                                <div class="modal-header">
                                    <div>
                                        <h5 class="modal-title fw-bold" id="esUpdModalTitleV8">
                                            Update
                                        </h5>
                                        <div class="small text-muted" id="esUpdModalVersionV8"></div>
                                    </div>

                                    <button
                                        type="button"
                                        class="es-upd-close-v9"
                                        data-es-upd-close-v9
                                        aria-label="Close"
                                    >×</button>
                                </div>

                                <div class="modal-body">

                                    <div
                                        id="esUpdDetailsV8"
                                        class="mb-4"
                                    ></div>

                                    <div
                                        id="esUpdTargetAreaV8"
                                        class="es-upd-target-box-v8"
                                    >
                                        <div class="es-upd-target-grid-v11">
                                            <div class="es-upd-target-field-v11">
                                                <label class="form-label fw-semibold">
                                                    Target
                                                </label>

                                                <select
                                                    id="esUpdTargetModeV8"
                                                    class="form-select"
                                                >
                                                    <option value="all">
                                                        All Tenants
                                                    </option>
                                                    <option value="selected">
                                                        Selected Tenants
                                                    </option>
                                                </select>
                                            </div>

                                            <div
                                                class="es-upd-target-field-v11"
                                                id="esUpdTenantFieldV10"
                                                style="display:none;"
                                            >
                                                <label class="form-label fw-semibold">
                                                    Select Tenants
                                                </label>

                                                <div class="es-upd-multi-v10">
                                                    <button
                                                        type="button"
                                                        id="esUpdTenantDropdownV10"
                                                        class="es-upd-multi-button-v10"
                                                        aria-expanded="false"
                                                    >
                                                        <span id="esUpdTenantSummaryV10">
                                                            Select tenants
                                                        </span>

                                                        <span class="es-upd-multi-arrow-v10">
                                                            ▾
                                                        </span>
                                                    </button>

                                                    <div
                                                        id="esUpdTenantMenuV10"
                                                        class="es-upd-multi-menu-v10"
                                                    >
                                                        <div class="es-upd-multi-tools-v10">
                                                            <button
                                                                type="button"
                                                                id="esUpdSelectAllV10"
                                                            >
                                                                Select All
                                                            </button>

                                                            <button
                                                                type="button"
                                                                id="esUpdClearAllV10"
                                                            >
                                                                Clear
                                                            </button>
                                                        </div>

                                                        <div class="es-upd-multi-list-v10">
                                                            @foreach($platformUpdateTenants as $tenant)
                                                                @php
                                                                    $tenantLabel =
                                                                        $tenant->name
                                                                        ?? $tenant->site_name
                                                                        ?? $tenant->domain
                                                                        ?? ('Website #' . $tenant->id);
                                                                @endphp

                                                                <label class="es-upd-multi-option-v10">
                                                                    <input
                                                                        type="checkbox"
                                                                        class="es-upd-tenant-check-v10"
                                                                        value="{{ $tenant->id }}"
                                                                    >

                                                                    <span>
                                                                        <strong>{{ $tenantLabel }}</strong>
                                                                        <small>#{{ $tenant->id }}</small>
                                                                    </span>
                                                                </label>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="form-text">
                                                    Select one or multiple tenants.
                                                </div>
                                            </div>
                                        </div>

                                        <div
                                            id="esUpdResultsV8"
                                            class="es-upd-results-v8 d-none"
                                        ></div>
                                    </div>

                                </div>

                                <div class="modal-footer">
                                    <button
                                        type="button"
                                        class="btn btn-light"
                                        data-es-upd-close-v9
                                    >
                                        Close
                                    </button>

                                    <button
                                        type="button"
                                        id="esUpdModalActionV8"
                                        class="btn px-4 fw-semibold"
                                        style="background:#1464f4;color:#fff;border-radius:10px;"
                                    >
                                        Dry Run
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- Manual registration remains modal-only; no duplicate bottom form --}}
                    
                    {{-- Manual update registration UI removed from production Admin. --}}


                    <script>
                    document.addEventListener('DOMContentLoaded', function () {
                        const rows = Array.from(
                            document.querySelectorAll('.es-upd-row-v8')
                        );

                        const pageSize = 10;
                        let page = 1;

                        const prev = document.getElementById('esUpdatePrevV8');
                        const next = document.getElementById('esUpdateNextV8');
                        const info = document.getElementById('esUpdatePageInfoV8');

                        function renderPage() {
                            const totalPages = Math.max(
                                1,
                                Math.ceil(rows.length / pageSize)
                            );

                            if (page > totalPages) page = totalPages;

                            rows.forEach(function(row, index) {
                                const start = (page - 1) * pageSize;
                                const end = start + pageSize;
                                row.style.display =
                                    index >= start && index < end ? '' : 'none';
                            });

                            if (info) {
                                const first = rows.length
                                    ? ((page - 1) * pageSize) + 1
                                    : 0;

                                const last = Math.min(
                                    page * pageSize,
                                    rows.length
                                );

                                info.textContent =
                                    first + '–' + last +
                                    ' of ' + rows.length + ' updates';
                            }

                            if (prev) prev.disabled = page <= 1;
                            if (next) next.disabled = page >= totalPages;
                        }

                        prev?.addEventListener('click', function() {
                            if (page > 1) {
                                page--;
                                renderPage();
                            }
                        });

                        next?.addEventListener('click', function() {
                            const totalPages = Math.ceil(rows.length / pageSize);

                            if (page < totalPages) {
                                page++;
                                renderPage();
                            }
                        });

                        renderPage();

                        document.addEventListener('click', function(event) {
                            const menuButton =
                                event.target.closest('.es-upd-menu-btn-v8');

                            document
                                .querySelectorAll('.es-upd-menu-v8.active')
                                .forEach(function(menu) {
                                    if (!menuButton ||
                                        menu !== menuButton.nextElementSibling) {
                                        menu.classList.remove('active');
                                    }
                                });

                            if (menuButton) {
                                event.preventDefault();
                                event.stopPropagation();

                                menuButton
                                    .nextElementSibling
                                    ?.classList.toggle('active');
                            }
                        });

                        const modalEl =
                            document.getElementById('esUpdateActionModalV8');

                        function openUpdateModalV9(element) {
                            if (!element) return;

                            element.classList.add('es-upd-modal-open-v9');
                            document.body.classList.add('es-upd-modal-lock-v9');
                        }

                        function closeUpdateModalV9(element) {
                            if (!element) return;

                            element.classList.remove('es-upd-modal-open-v9');

                            if (!document.querySelector(
                                '.es-upd-modal-open-v9'
                            )) {
                                document.body.classList.remove(
                                    'es-upd-modal-lock-v9'
                                );
                            }
                        }

                        document
                            .getElementById('esOpenRegisterUpdateV9')
                            ?.addEventListener('click', function () {
                                openUpdateModalV9(registerModalEl);
                            });

                        document
                            .querySelectorAll('[data-es-upd-close-v9]')
                            .forEach(function(button) {
                                button.addEventListener('click', function() {
                                    closeUpdateModalV9(
                                        this.closest(
                                            '#esUpdateActionModalV8'
                                        )
                                    );
                                });
                            });

                        [modalEl, registerModalEl].forEach(function(element) {
                            element?.addEventListener('click', function(event) {
                                if (event.target === element) {
                                    closeUpdateModalV9(element);
                                }
                            });
                        });

                        document.addEventListener('keydown', function(event) {
                            if (event.key === 'Escape') {
                                closeUpdateModalV9(modalEl);
                                closeUpdateModalV9(registerModalEl);
                            }
                        });

                        const modalTitle =
                            document.getElementById('esUpdModalTitleV8');

                        const modalVersion =
                            document.getElementById('esUpdModalVersionV8');

                        const details =
                            document.getElementById('esUpdDetailsV8');

                        const targetArea =
                            document.getElementById('esUpdTargetAreaV8');

                        const targetMode =
                            document.getElementById('esUpdTargetModeV8');

                        const tenantField =
                            document.getElementById('esUpdTenantFieldV10');

                        const tenantDropdown =
                            document.getElementById('esUpdTenantDropdownV10');

                        const tenantMenu =
                            document.getElementById('esUpdTenantMenuV10');

                        const tenantSummary =
                            document.getElementById('esUpdTenantSummaryV10');

                        const tenantChecks = Array.from(
                            document.querySelectorAll('.es-upd-tenant-check-v10')
                        );

                        const results =
                            document.getElementById('esUpdResultsV8');

                        const actionButton =
                            document.getElementById('esUpdModalActionV8');

                        let activeRow = null;
                        let activeMode = 'view';

                        function updateTenantSummaryV10() {
                            const selected = tenantChecks.filter(
                                checkbox => checkbox.checked
                            );

                            if (!tenantSummary) return;

                            if (!selected.length) {
                                tenantSummary.textContent = 'Select tenants';
                            } else if (selected.length === 1) {
                                tenantSummary.textContent =
                                    selected[0]
                                        .closest('.es-upd-multi-option-v10')
                                        ?.querySelector('strong')
                                        ?.textContent
                                        ?.trim()
                                    || '1 tenant selected';
                            } else {
                                tenantSummary.textContent =
                                    selected.length + ' tenants selected';
                            }
                        }

                        function closeTenantDropdownV10() {
                            tenantMenu?.classList.remove('active');
                            tenantDropdown?.setAttribute(
                                'aria-expanded',
                                'false'
                            );
                        }

                        targetMode?.addEventListener('change', function() {
                            const selectedMode = this.value === 'selected';

                            if (tenantField) {
                                tenantField.style.display =
                                    selectedMode ? '' : 'none';
                            }

                            if (!selectedMode) {
                                tenantChecks.forEach(function(checkbox) {
                                    checkbox.checked = false;
                                });

                                updateTenantSummaryV10();
                                closeTenantDropdownV10();
                            }
                        });

                        tenantDropdown?.addEventListener('click', function(event) {
                            event.stopPropagation();

                            const opening =
                                !tenantMenu.classList.contains('active');

                            tenantMenu.classList.toggle('active', opening);

                            tenantDropdown.setAttribute(
                                'aria-expanded',
                                opening ? 'true' : 'false'
                            );
                        });

                        tenantMenu?.addEventListener('click', function(event) {
                            event.stopPropagation();
                        });

                        tenantChecks.forEach(function(checkbox) {
                            checkbox.addEventListener(
                                'change',
                                updateTenantSummaryV10
                            );
                        });

                        document
                            .getElementById('esUpdSelectAllV10')
                            ?.addEventListener('click', function() {
                                tenantChecks.forEach(function(checkbox) {
                                    checkbox.checked = true;
                                });

                                updateTenantSummaryV10();
                            });

                        document
                            .getElementById('esUpdClearAllV10')
                            ?.addEventListener('click', function() {
                                tenantChecks.forEach(function(checkbox) {
                                    checkbox.checked = false;
                                });

                                updateTenantSummaryV10();
                            });

                        document.addEventListener(
                            'click',
                            closeTenantDropdownV10
                        );

                        function openAction(row, mode) {
                            document
                                .querySelectorAll('.es-upd-menu-v8.active')
                                .forEach(function(menu) {
                                    menu.classList.remove('active');
                                });

                            activeRow = row;
                            activeMode = mode;

                            modalTitle.textContent =
                                row.dataset.updateName || 'Update';

                            modalVersion.textContent =
                                'Version ' + (row.dataset.updateVersion || '');

                            const description =
                                row.dataset.updateDescription ||
                                'No description provided.';

                            const source = row.dataset.updateSource;

                            details.innerHTML =
                                '<p class="mb-2">' + description + '</p>' +
                                (source
                                    ? '<div class="small text-muted"><strong>Source:</strong> ' +
                                      source + '</div>'
                                    : '');

                            results.innerHTML = '';
                            results.classList.add('d-none');

                            targetMode.value = 'all';

                            if (tenantField) {
                                tenantField.style.display = 'none';
                            }

                            tenantChecks.forEach(function(checkbox) {
                                checkbox.checked = false;
                            });

                            updateTenantSummaryV10();
                            closeTenantDropdownV10();

                            if (mode === 'view') {
                                targetArea.classList.add('d-none');
                                actionButton.classList.add('d-none');
                            } else {
                                targetArea.classList.remove('d-none');
                                actionButton.classList.remove('d-none');

                                actionButton.textContent =
                                    mode === 'run'
                                        ? 'Run Update'
                                        : 'Dry Run';
                            }

                            openUpdateModalV9(modalEl);
                        }

                        document.querySelectorAll('.es-upd-view-v8')
                            .forEach(function(button) {
                                button.addEventListener('click', function() {
                                    openAction(
                                        this.closest('.es-upd-row-v8'),
                                        'view'
                                    );
                                });
                            });

                        document.querySelectorAll('.es-upd-dry-open-v8')
                            .forEach(function(button) {
                                button.addEventListener('click', function() {
                                    openAction(
                                        this.closest('.es-upd-row-v8'),
                                        'dry'
                                    );
                                });
                            });

                        document.querySelectorAll('.es-upd-run-open-v8')
                            .forEach(function(button) {
                                button.addEventListener('click', function() {
                                    if (this.disabled) return;

                                    openAction(
                                        this.closest('.es-upd-row-v8'),
                                        'run'
                                    );
                                });
                            });

                        function requestPayload() {
                            return {
                                all_tenants: targetMode.value === 'all',
                                website_ids:
                                    targetMode.value === 'selected'
                                        ? tenantChecks
                                            .filter(checkbox => checkbox.checked)
                                            .map(checkbox => Number(checkbox.value))
                                        : []
                            };
                        }

                        function escapeUpdateResultV16(value) {
                            return String(value ?? '')
                                .replace(/&/g, '&amp;')
                                .replace(/</g, '&lt;')
                                .replace(/>/g, '&gt;')
                                .replace(/"/g, '&quot;')
                                .replace(/'/g, '&#039;');
                        }

                        function renderResults(data) {
                            if (!data.ok) {
                                results.innerHTML =
                                    '<div class="p-3 text-danger fw-semibold">' +
                                    escapeUpdateResultV16(
                                        data.message || 'Request failed.'
                                    ) +
                                    '</div>';

                                results.classList.remove('d-none');
                                return;
                            }

                            const list = Array.isArray(data.results)
                                ? data.results
                                : [];

                            const labels = {
                                already_applied: 'Already Applied',
                                pending: 'Pending',
                                skipped: 'Skipped',
                                unable_to_verify: 'Unable to Verify'
                            };

                            const counts = {
                                already_applied: 0,
                                pending: 0,
                                skipped: 0,
                                unable_to_verify: 0
                            };

                            list.forEach(function(result) {
                                const status =
                                    result.status || 'unable_to_verify';

                                if (Object.prototype.hasOwnProperty.call(
                                    counts,
                                    status
                                )) {
                                    counts[status]++;
                                } else {
                                    counts.unable_to_verify++;
                                }
                            });

                            const summary =
                                '<div class="es-upd-dry-summary-v16">' +
                                    '<div class="es-upd-dry-safe-v16">' +
                                        '<strong>Read-only Dry Run</strong>' +
                                        '<span>No migrations were executed and no tenant data or files were changed.</span>' +
                                    '</div>' +
                                    '<div class="es-upd-dry-counts-v16">' +
                                        '<span><strong>' + counts.already_applied + '</strong> Already Applied</span>' +
                                        '<span><strong>' + counts.pending + '</strong> Pending</span>' +
                                        '<span><strong>' + counts.skipped + '</strong> Skipped</span>' +
                                        '<span><strong>' + counts.unable_to_verify + '</strong> Unable to Verify</span>' +
                                    '</div>' +
                                '</div>';

                            const rows = list.length
                                ? list.map(function(result) {
                                    const name =
                                        result.website_name ||
                                        ('Website #' + result.website_id);

                                    const status =
                                        result.status || 'unable_to_verify';

                                    const label =
                                        labels[status] || 'Unable to Verify';

                                    const migration =
                                        result.migration || '—';

                                    const message =
                                        result.message || '';

                                    return '<div class="es-upd-result-v16">' +
                                        '<div class="es-upd-result-main-v16">' +
                                            '<strong>' +
                                                escapeUpdateResultV16(name) +
                                            '</strong>' +
                                            '<div class="es-upd-result-migration-v16">' +
                                                escapeUpdateResultV16(migration) +
                                            '</div>' +
                                            '<div class="text-muted">' +
                                                escapeUpdateResultV16(message) +
                                            '</div>' +
                                        '</div>' +
                                        '<span class="es-upd-state-v16 es-upd-state-' +
                                            escapeUpdateResultV16(status) + '">' +
                                            escapeUpdateResultV16(label) +
                                        '</span>' +
                                    '</div>';
                                }).join('')
                                : '<div class="p-3 text-muted">No tenant results returned.</div>';

                            results.innerHTML = summary + rows;
                            results.classList.remove('d-none');
                        }

                        actionButton?.addEventListener('click', async function() {
                            if (!activeRow) return;

                            const payload = requestPayload();

                            if (!payload.all_tenants &&
                                !payload.website_ids.length) {
                                renderResults({
                                    ok:false,
                                    message:'Select at least one tenant or choose All Tenants.'
                                });
                                return;
                            }

                            if (activeMode === 'run') {
                                const message = payload.all_tenants
                                    ? 'Run this update on ALL tenants?'
                                    : 'Run this update on the selected tenant(s)?';

                                if (!window.confirm(message)) return;
                            }

                            const url = activeMode === 'run'
                                ? activeRow.dataset.runUrl
                                : activeRow.dataset.dryUrl;

                            const original = this.textContent;
                            this.disabled = true;
                            this.textContent =
                                activeMode === 'run'
                                    ? 'Updating...'
                                    : 'Checking...';

                            try {
                                const response = await fetch(url, {
                                    method:'POST',
                                    headers:{
                                        'Content-Type':'application/json',
                                        'Accept':'application/json',
                                        'X-CSRF-TOKEN':'{{ csrf_token() }}'
                                    },
                                    body:JSON.stringify(payload)
                                });

                                const data = await response.json();
                                renderResults(data);
                            } catch (error) {
                                renderResults({
                                    ok:false,
                                    message:'Unable to contact the update service.'
                                });
                            } finally {
                                this.disabled = false;
                                this.textContent = original;
                            }
                        });

                        document.querySelectorAll('.es-upd-status-v8')
                            .forEach(function(button) {
                                button.addEventListener('click', async function() {
                                    const row =
                                        this.closest('.es-upd-row-v8');

                                    const status =
                                        this.dataset.nextStatus;

                                    const verb =
                                        status === 'published'
                                            ? 'publish'
                                            : 'disable';

                                    if (!window.confirm(
                                        'Are you sure you want to ' +
                                        verb + ' this update?'
                                    )) {
                                        return;
                                    }

                                    try {
                                        const response = await fetch(
                                            row.dataset.statusUrl,
                                            {
                                                method:'PATCH',
                                                headers:{
                                                    'Content-Type':'application/json',
                                                    'Accept':'application/json',
                                                    'X-CSRF-TOKEN':'{{ csrf_token() }}'
                                                },
                                                body:JSON.stringify({
                                                    status:status
                                                })
                                            }
                                        );

                                        const data = await response.json();

                                        if (data.ok) {
                                            window.location.reload();
                                        } else {
                                            alert(
                                                data.message ||
                                                'Unable to change update status.'
                                            );
                                        }
                                    } catch (error) {
                                        alert('Unable to contact the update service.');
                                    }
                                });
                            });
                    });
                    </script>

                @elseif($sub === 'tenants')

                    <div class="es-setting-card">
                        <h3>Tenant Update Status</h3>
                        <p class="text-muted">
                            Provisioned, pending, updated, skipped and failed website states will be managed here.
                        </p>

                        <div class="border rounded-3 p-4">
                            <span class="fw-bold">{{ $platformUpdateStats['websites'] }}</span>
                            websites are currently registered with Central Esubiz.
                        </div>
                    </div>

                @elseif($sub === 'history')

                    <div class="es-setting-card">
                        <h3>Migration History</h3>

                        @if($platformUpdateRuns->isEmpty())
                            <div class="border rounded-3 p-4 text-center text-muted">
                                No managed update runs have been recorded yet.
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead>
                                        <tr>
                                            <th>Update</th>
                                            <th>Website</th>
                                            <th>Status</th>
                                            <th>From</th>
                                            <th>To</th>
                                            <th>Finished</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($platformUpdateRuns as $run)
                                            <tr>
                                                <td>#{{ $run->platform_update_id }}</td>
                                                <td>#{{ $run->website_id }}</td>
                                                <td>{{ ucfirst($run->status) }}</td>
                                                <td>{{ $run->from_version ?: '—' }}</td>
                                                <td>{{ $run->to_version ?: '—' }}</td>
                                                <td>{{ $run->finished_at ?: '—' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>

                @endif

            @else

                <p class="es-settings-section-copy">
                    {{ $tabs[$tab] }} / {{ $currentSubs[$sub] ?? 'Settings' }}
                </p>

                <div class="es-settings-grid">

                    <div class="es-setting-card es-coming">
                        <h3>{{ $currentSubs[$sub] ?? $tabs[$tab] }}</h3>

                        <p>
                            This section is positioned inside the Central Settings architecture.
                            Its existing configuration contract will be connected here before
                            any storage or provider implementation is added.
                        </p>

                        <span class="es-coming-badge">Coming Soon</span>
                    </div>

                </div>

            @endif

        </div>

    </div>
</div>

{{-- ESUBIZ_NOTICE_DELIVERY_AUDIENCE_LAYOUT_V25 --}}
<style>
    /*
     * Delivery + Audience layout
     *
     * Desktop:
     * Delivery sits on the left.
     * Related audience controls sit beside it on the right.
     *
     * Mobile:
     * Audience drops below Delivery at full width.
     */
    #esNoticeDeliveryAudienceRowV25 {
        display:grid;
        grid-template-columns:
            minmax(0, 1fr)
            minmax(0, 1fr);
        gap:16px;
        width:100%;
        max-width:100%;
        align-items:start;
        box-sizing:border-box;
    }

    #esNoticeDeliveryColumnV25,
    #esNoticeAudienceColumnV25 {
        min-width:0;
        width:100%;
        max-width:100%;
        box-sizing:border-box;
    }

    #esNoticeAudienceColumnV25 {
        display:flex;
        flex-direction:column;
        gap:14px;
    }

    /*
     * Once moved into the Delivery/Audience row these fields must
     * occupy their own column instead of retaining old grid spans.
     */
    #esNoticeDeliveryAudienceRowV25
    .es-notice-field-v6 {
        width:100% !important;
        max-width:100% !important;
        min-width:0 !important;
        grid-column:auto !important;
        box-sizing:border-box !important;
        margin-left:0 !important;
        margin-right:0 !important;
    }

    #esNoticeDeliveryAudienceRowV25
    input,
    #esNoticeDeliveryAudienceRowV25
    select,
    #esNoticeDeliveryAudienceRowV25
    textarea,
    #esNoticeDeliveryAudienceRowV25
    button {
        max-width:100%;
        box-sizing:border-box;
    }

    /*
     * Dropdown shell.
     * Keep it contained inside its field and above neighbouring
     * form controls.
     */
    #esNoticeDeliveryAudienceRowV25
    .es-notice-delivery-trigger-v18 {
        width:100% !important;
        max-width:100% !important;
        min-width:0 !important;
        box-sizing:border-box !important;
        text-align:left;
    }

    #esNoticeDeliveryAudienceRowV25
    .es-notice-delivery-summary-v18 {
        display:block;
        min-width:0;
        overflow:hidden;
        text-overflow:ellipsis;
        white-space:nowrap;
    }

    #esNoticeDeliveryAudienceRowV25
    .es-notice-delivery-panel-v18 {
        z-index:10050 !important;
        max-width:100% !important;
        min-width:100% !important;
        box-sizing:border-box !important;
        overflow-x:hidden !important;
        overflow-y:auto !important;
        max-height:260px !important;
        background:#ffffff !important;
        color:#0b1f3a !important;
        border:1px solid #dce2ea !important;
        box-shadow:0 12px 30px rgba(11,31,58,.12) !important;
    }

    /*
     * Fix blank dropdown appearance.
     *
     * Some of the older audience labels inherited display/visibility
     * rules from the original checkbox list. Force each moved option
     * to render as an actual selectable row.
     */
    #esNoticeDeliveryAudienceRowV25
    .es-notice-delivery-panel-v18
    .es-notice-delivery-option-v18 {
        display:flex !important;
        visibility:visible !important;
        opacity:1 !important;
        position:relative !important;
        width:100% !important;
        max-width:100% !important;
        min-width:0 !important;
        align-items:flex-start !important;
        gap:8px !important;
        padding:8px 10px !important;
        margin:0 !important;
        color:#0b1f3a !important;
        background:#ffffff !important;
        cursor:pointer !important;
        box-sizing:border-box !important;
        white-space:normal !important;
    }

    #esNoticeDeliveryAudienceRowV25
    .es-notice-delivery-panel-v18
    .es-notice-delivery-option-v18:hover {
        background:#f5f7fa !important;
    }

    #esNoticeDeliveryAudienceRowV25
    .es-notice-delivery-panel-v18
    .es-notice-delivery-option-v18 > span {
        display:block !important;
        flex:1 1 auto !important;
        min-width:0 !important;
        color:#0b1f3a !important;
        line-height:1.35 !important;
        overflow-wrap:anywhere;
    }

    #esNoticeDeliveryAudienceRowV25
    .es-notice-delivery-panel-v18
    .es-notice-delivery-option-v18 strong {
        display:block !important;
        color:#0b1f3a !important;
        font-size:13px !important;
        font-weight:600 !important;
    }

    #esNoticeDeliveryAudienceRowV25
    .es-notice-delivery-panel-v18
    .es-notice-delivery-option-v18 small {
        display:block !important;
        margin-top:2px !important;
        color:#7a8596 !important;
        font-size:11px !important;
        line-height:1.35 !important;
    }

    /*
     * Small true multi-select checkboxes.
     */
    #esNoticeDeliveryAudienceRowV25
    .es-notice-delivery-option-v18
    input[type="checkbox"] {
        display:block !important;
        visibility:visible !important;
        opacity:1 !important;
        appearance:auto !important;
        -webkit-appearance:checkbox !important;
        flex:0 0 13px !important;
        width:13px !important;
        height:13px !important;
        min-width:13px !important;
        min-height:13px !important;
        max-width:13px !important;
        max-height:13px !important;
        margin:2px 0 0 !important;
        padding:0 !important;
        cursor:pointer !important;
    }

    /*
     * Prevent old form/grid styles from shifting the Dashboard
     * Notice setup toward the right on small screens.
     */
    #esDashboardNoticeFormV6,
    #esDashboardNoticeFormV6 > *,
    #esDashboardNoticeFormV6 .es-notice-field-v6 {
        min-width:0;
        max-width:100%;
        box-sizing:border-box;
    }

    @media (max-width: 767px) {
        #esNoticeDeliveryAudienceRowV25 {
            grid-template-columns:minmax(0, 1fr);
            gap:12px;
            width:100%;
            max-width:100%;
            margin:0;
            padding:0;
        }

        #esNoticeDeliveryColumnV25,
        #esNoticeAudienceColumnV25 {
            width:100%;
            max-width:100%;
            min-width:0;
            margin:0;
            padding:0;
        }

        /*
         * On mobile, render dropdown contents in normal document
         * flow. This prevents overlays from sitting off-screen or
         * blocking fields below them.
         */
        #esNoticeDeliveryAudienceRowV25
        .es-notice-delivery-panel-v18 {
            position:static !important;
            left:auto !important;
            right:auto !important;
            top:auto !important;
            bottom:auto !important;
            transform:none !important;
            width:100% !important;
            min-width:0 !important;
            max-width:100% !important;
            margin-top:6px !important;
            max-height:230px !important;
            border-radius:10px !important;
        }

        #esNoticeDeliveryAudienceRowV25
        .es-notice-delivery-trigger-v18 {
            width:100% !important;
            min-width:0 !important;
        }

        #esDashboardNoticeFormV6 {
            width:100% !important;
            max-width:100% !important;
            margin-left:0 !important;
            margin-right:0 !important;
            padding-left:0 !important;
            padding-right:0 !important;
            overflow:visible !important;
        }

        #esDashboardNoticeFormV6
        .es-notice-field-v6 {
            width:100% !important;
            max-width:100% !important;
            min-width:0 !important;
            margin-left:0 !important;
            margin-right:0 !important;
            grid-column:1 / -1 !important;
        }

        #esDashboardNoticeFormV6
        input:not([type="checkbox"]):not([type="radio"]),
        #esDashboardNoticeFormV6
        select,
        #esDashboardNoticeFormV6
        textarea {
            width:100% !important;
            max-width:100% !important;
            min-width:0 !important;
            box-sizing:border-box !important;
        }
    }
</style>

<script>
(function () {
    function installDeliveryAudienceLayoutV25() {
        const form =
            document.getElementById(
                'esDashboardNoticeFormV6'
            );

        const deliveryTrigger =
            document.getElementById(
                'esNoticeDeliveryTriggerV18'
            );

        if (!form || !deliveryTrigger) {
            return;
        }

        if (
            document.getElementById(
                'esNoticeDeliveryAudienceRowV25'
            )
        ) {
            syncDeliveryAudienceV25();
            return;
        }

        const deliveryField =
            deliveryTrigger.closest(
                '.es-notice-field-v6'
            );

        if (!deliveryField) {
            return;
        }

        const targetType =
            form.querySelector(
                '[name="target_type"]'
            );

        const saasAudienceField =
            targetType
                ? targetType.closest(
                    '.es-notice-field-v6'
                )
                : null;

        const selectedTenantsWrap =
            document.getElementById(
                'esNoticeSelectedTenantsWrapV6'
            );

        const centralAudience =
            document.getElementById(
                'esNoticeCentralAudienceWrapV19'
            );

        /*
         * If the existing target_type field is not the complete
         * SaaS audience container, use the closest common field
         * that also contains the selected tenant control.
         */
        let resolvedSaasAudience =
            saasAudienceField;

        if (
            selectedTenantsWrap
            && resolvedSaasAudience
            && !resolvedSaasAudience.contains(
                selectedTenantsWrap
            )
        ) {
            const tenantField =
                selectedTenantsWrap.closest(
                    '.es-notice-field-v6'
                );

            /*
             * Keep both pieces together by wrapping them in one
             * audience group.
             */
            if (tenantField) {
                const group =
                    document.createElement('div');

                group.id =
                    'esNoticeSaasAudienceGroupV25';

                group.style.display =
                    'flex';

                group.style.flexDirection =
                    'column';

                group.style.gap =
                    '10px';

                group.style.width =
                    '100%';

                group.style.minWidth =
                    '0';

                resolvedSaasAudience
                    .parentNode
                    .insertBefore(
                        group,
                        resolvedSaasAudience
                    );

                group.appendChild(
                    resolvedSaasAudience
                );

                group.appendChild(
                    tenantField
                );

                resolvedSaasAudience =
                    group;
            }
        }

        const row =
            document.createElement('div');

        row.id =
            'esNoticeDeliveryAudienceRowV25';

        const deliveryColumn =
            document.createElement('div');

        deliveryColumn.id =
            'esNoticeDeliveryColumnV25';

        const audienceColumn =
            document.createElement('div');

        audienceColumn.id =
            'esNoticeAudienceColumnV25';

        deliveryField
            .parentNode
            .insertBefore(
                row,
                deliveryField
            );

        row.appendChild(
            deliveryColumn
        );

        row.appendChild(
            audienceColumn
        );

        deliveryColumn.appendChild(
            deliveryField
        );

        if (resolvedSaasAudience) {
            audienceColumn.appendChild(
                resolvedSaasAudience
            );

            resolvedSaasAudience.dataset
                .esSaasAudienceV25 =
                '1';
        }

        if (centralAudience) {
            audienceColumn.appendChild(
                centralAudience
            );

            centralAudience.dataset
                .esCentralAudienceV25 =
                '1';
        }

        window.esSyncDeliveryAudienceV25 =
            syncDeliveryAudienceV25;

        syncDeliveryAudienceV25();

        /*
         * V21 can move tenant labels after initial page parsing.
         * Re-sync after it has completed its own setup.
         */
        setTimeout(
            syncDeliveryAudienceV25,
            0
        );

        setTimeout(
            syncDeliveryAudienceV25,
            120
        );
    }

    function currentDeliveryChannelsV25() {
        const form =
            document.getElementById(
                'esDashboardNoticeFormV6'
            );

        if (!form) {
            return [];
        }

        return Array.from(
            form.querySelectorAll(
                'input[name="delivery_channels[]"]:checked'
            )
        ).map(
            function (checkbox) {
                return checkbox.value;
            }
        );
    }

    function makeAudienceOptionsVisibleV25(
        container
    ) {
        if (!container) {
            return;
        }

        container
            .querySelectorAll(
                '.es-notice-delivery-option-v18'
            )
            .forEach(
                function (option) {
                    option.style
                        .setProperty(
                            'display',
                            'flex',
                            'important'
                        );

                    option.style
                        .setProperty(
                            'visibility',
                            'visible',
                            'important'
                        );

                    option.style
                        .setProperty(
                            'opacity',
                            '1',
                            'important'
                        );
                }
            );
    }

    function syncDeliveryAudienceV25() {
        const form =
            document.getElementById(
                'esDashboardNoticeFormV6'
            );

        if (!form) {
            return;
        }

        const channels =
            currentDeliveryChannelsV25();

        const hasSaas =
            channels.includes(
                'saas'
            );

        const hasCentral =
            channels.includes(
                'central'
            );

        const saasAudience =
            document.querySelector(
                '[data-es-saas-audience-v25="1"]'
            );

        const centralAudience =
            document.querySelector(
                '[data-es-central-audience-v25="1"]'
            );

        /*
         * Audience visibility is now tied directly to its own
         * delivery channel.
         *
         * SaaS audience only exists when SaaS is selected.
         * Central audience only exists when Central is selected.
         * Off-server has no audience selector.
         */
        if (saasAudience) {
            saasAudience.style.display =
                hasSaas
                    ? ''
                    : 'none';

            if (hasSaas) {
                makeAudienceOptionsVisibleV25(
                    saasAudience
                );
            }
        }

        if (centralAudience) {
            centralAudience.style.display =
                hasCentral
                    ? ''
                    : 'none';

            if (hasCentral) {
                makeAudienceOptionsVisibleV25(
                    centralAudience
                );
            }
        }

        const audienceColumn =
            document.getElementById(
                'esNoticeAudienceColumnV25'
            );

        if (audienceColumn) {
            audienceColumn.style.display =
                (
                    hasSaas
                    || hasCentral
                )
                    ? 'flex'
                    : 'none';
        }

        /*
         * Make sure all currently open dropdowns contain visible
         * option rows instead of appearing blank.
         */
        document
            .querySelectorAll(
                '#esNoticeDeliveryAudienceRowV25 '
                + '.es-notice-delivery-panel-v18'
            )
            .forEach(
                makeAudienceOptionsVisibleV25
            );
    }

    function bootV25() {
        installDeliveryAudienceLayoutV25();

        const form =
            document.getElementById(
                'esDashboardNoticeFormV6'
            );

        if (!form) {
            return;
        }

        form.addEventListener(
            'change',
            function (event) {
                if (
                    event.target.matches(
                        'input[name="delivery_channels[]"]'
                    )
                    || event.target.matches(
                        '[name="target_type"]'
                    )
                    || event.target.matches(
                        '[name="central_target_type"]'
                    )
                ) {
                    /*
                     * Allow V18/V19/V21 handlers to update first,
                     * then apply the final layout state.
                     */
                    setTimeout(
                        syncDeliveryAudienceV25,
                        0
                    );
                }
            }
        );

        /*
         * Edit/reset operations are performed by older Dashboard
         * Notice scripts. MutationObserver keeps the V25 layout
         * synchronized without changing those workflows.
         */
        const observer =
            new MutationObserver(
                function () {
                    syncDeliveryAudienceV25();
                }
            );

        const deliveryPanel =
            document.getElementById(
                'esNoticeDeliveryPanelV18'
            );

        if (deliveryPanel) {
            observer.observe(
                deliveryPanel,
                {
                    attributes:true,
                    subtree:true,
                    attributeFilter:[
                        'checked',
                        'hidden',
                        'style'
                    ]
                }
            );
        }
    }

    if (
        document.readyState ===
        'loading'
    ) {
        document.addEventListener(
            'DOMContentLoaded',
            bootV25
        );
    } else {
        bootV25();
    }
})();
</script>


{{-- ESUBIZ_NOTICE_AUDIENCE_NESTED_FIX_V26 --}}
<style>
    /*
     * The Audience parent and its related sub-options now behave
     * as one contained control group.
     */
    .es-notice-audience-group-v26 {
        display:flex !important;
        flex-direction:column !important;
        gap:10px !important;
        width:100% !important;
        max-width:100% !important;
        min-width:0 !important;
        box-sizing:border-box !important;
    }

    .es-notice-audience-sub-v26 {
        display:block;
        width:100% !important;
        max-width:100% !important;
        min-width:0 !important;
        margin:0 !important;
        padding:0 !important;
        box-sizing:border-box !important;
        grid-column:auto !important;
    }

    /*
     * Dropdown container must own the positioning context so its
     * list opens directly under the field that was clicked.
     */
    #esNoticeTenantMultiV21,
    #esNoticeCentralRoleMultiV19,
    #esNoticeCentralUserMultiV21 {
        position:relative !important;
        width:100% !important;
        max-width:100% !important;
        min-width:0 !important;
        box-sizing:border-box !important;
    }

    #esNoticeTenantPanelV21,
    #esNoticeCentralRolePanelV19,
    #esNoticeCentralUserPanelV21 {
        position:absolute !important;
        left:0 !important;
        right:auto !important;
        top:calc(100% + 5px) !important;
        bottom:auto !important;
        width:100% !important;
        min-width:100% !important;
        max-width:100% !important;
        max-height:240px !important;
        overflow-y:auto !important;
        overflow-x:hidden !important;
        z-index:12000 !important;
        margin:0 !important;
        padding:5px !important;
        border:1px solid #dce2ea !important;
        border-radius:10px !important;
        background:#fff !important;
        box-shadow:0 12px 30px rgba(11,31,58,.14) !important;
        box-sizing:border-box !important;
    }

    /*
     * A native hidden attribute must always win while the panel
     * is closed.
     */
    #esNoticeTenantPanelV21[hidden],
    #esNoticeCentralRolePanelV19[hidden],
    #esNoticeCentralUserPanelV21[hidden] {
        display:none !important;
    }

    /*
     * Explicit option-row rendering prevents inherited Dashboard
     * Notice form CSS from producing an apparently blank panel.
     */
    #esNoticeTenantPanelV21 label,
    #esNoticeCentralRolePanelV19 label,
    #esNoticeCentralUserPanelV21 label {
        display:flex !important;
        align-items:flex-start !important;
        visibility:visible !important;
        opacity:1 !important;
        width:100% !important;
        max-width:100% !important;
        min-width:0 !important;
        min-height:30px !important;
        gap:8px !important;
        margin:0 !important;
        padding:7px 8px !important;
        border-radius:7px !important;
        background:#fff !important;
        color:#0b1f3a !important;
        cursor:pointer !important;
        box-sizing:border-box !important;
        white-space:normal !important;
    }

    #esNoticeTenantPanelV21 label:hover,
    #esNoticeCentralRolePanelV19 label:hover,
    #esNoticeCentralUserPanelV21 label:hover {
        background:#f5f7fa !important;
    }

    #esNoticeTenantPanelV21 label > span,
    #esNoticeCentralRolePanelV19 label > span,
    #esNoticeCentralUserPanelV21 label > span,
    .es-notice-option-text-v26 {
        display:block !important;
        flex:1 1 auto !important;
        width:auto !important;
        min-width:0 !important;
        visibility:visible !important;
        opacity:1 !important;
        color:#0b1f3a !important;
        font-size:12px !important;
        line-height:1.4 !important;
        white-space:normal !important;
        overflow-wrap:anywhere !important;
    }

    #esNoticeTenantPanelV21 strong,
    #esNoticeCentralRolePanelV19 strong,
    #esNoticeCentralUserPanelV21 strong {
        display:block !important;
        visibility:visible !important;
        opacity:1 !important;
        color:#0b1f3a !important;
        font-size:12px !important;
        font-weight:600 !important;
    }

    #esNoticeTenantPanelV21 small,
    #esNoticeCentralRolePanelV19 small,
    #esNoticeCentralUserPanelV21 small {
        display:block !important;
        visibility:visible !important;
        opacity:1 !important;
        margin-top:2px !important;
        color:#788397 !important;
        font-size:10px !important;
        line-height:1.3 !important;
    }

    #esNoticeTenantPanelV21 input[type="checkbox"],
    #esNoticeCentralRolePanelV19 input[type="checkbox"],
    #esNoticeCentralUserPanelV21 input[type="checkbox"] {
        display:block !important;
        visibility:visible !important;
        opacity:1 !important;
        appearance:auto !important;
        -webkit-appearance:checkbox !important;
        flex:0 0 13px !important;
        width:13px !important;
        height:13px !important;
        min-width:13px !important;
        min-height:13px !important;
        max-width:13px !important;
        max-height:13px !important;
        margin:2px 0 0 !important;
        padding:0 !important;
        cursor:pointer !important;
    }

    @media (max-width:767px) {
        /*
         * Mobile: lists expand directly below the clicked field
         * in document flow instead of floating over/off the form.
         */
        #esNoticeTenantPanelV21,
        #esNoticeCentralRolePanelV19,
        #esNoticeCentralUserPanelV21 {
            position:static !important;
            left:auto !important;
            right:auto !important;
            top:auto !important;
            width:100% !important;
            min-width:0 !important;
            max-width:100% !important;
            margin-top:5px !important;
            max-height:220px !important;
            transform:none !important;
        }

        .es-notice-audience-group-v26,
        .es-notice-audience-sub-v26 {
            width:100% !important;
            max-width:100% !important;
            min-width:0 !important;
            margin-left:0 !important;
            margin-right:0 !important;
        }
    }
</style>

<script>
(function () {
    function fieldOfV26(element) {
        if (!element) {
            return null;
        }

        return element.closest(
            '.es-notice-field-v6'
        ) || element;
    }

    function cleanTextV26(value) {
        return String(value || '')
            .replace(/\s+/g, ' ')
            .trim();
    }

    function repairPanelOptionsV26(
        panel,
        type
    ) {
        if (!panel) {
            return;
        }

        const checkboxes =
            Array.from(
                panel.querySelectorAll(
                    'input[type="checkbox"]'
                )
            );

        checkboxes.forEach(
            function (checkbox) {
                let label =
                    checkbox.closest('label');

                if (!label) {
                    label =
                        document.createElement(
                            'label'
                        );

                    checkbox
                        .parentNode
                        ?.insertBefore(
                            label,
                            checkbox
                        );

                    label.appendChild(
                        checkbox
                    );
                }

                label.classList.add(
                    'es-notice-delivery-option-v18'
                );

                let visibleText =
                    cleanTextV26(
                        label.textContent
                    );

                if (!visibleText) {
                    if (type === 'central_role') {
                        visibleText =
                            cleanTextV26(
                                checkbox.dataset.roleName
                            );
                    }

                    if (type === 'central_user') {
                        visibleText =
                            cleanTextV26(
                                checkbox.dataset.userName
                            );
                    }

                    if (type === 'tenant') {
                        visibleText =
                            cleanTextV26(
                                checkbox.dataset.tenantName
                                || checkbox.dataset.name
                                || checkbox.dataset.label
                            );
                    }
                }

                /*
                 * Last-resort text means the panel can never be
                 * visually blank even if an old option had no
                 * descriptive markup.
                 */
                if (!visibleText) {
                    if (type === 'tenant') {
                        visibleText =
                            'Tenant #' + checkbox.value;
                    } else if (
                        type === 'central_role'
                    ) {
                        visibleText =
                            'Role #' + checkbox.value;
                    } else {
                        visibleText =
                            'User #' + checkbox.value;
                    }
                }

                const hasTextNode =
                    Array.from(
                        label.children
                    ).some(
                        function (child) {
                            return (
                                child !== checkbox
                                && cleanTextV26(
                                    child.textContent
                                )
                            );
                        }
                    );

                if (!hasTextNode) {
                    const span =
                        document.createElement(
                            'span'
                        );

                    span.className =
                        'es-notice-option-text-v26';

                    span.textContent =
                        visibleText;

                    label.appendChild(
                        span
                    );
                }

                label.style.setProperty(
                    'display',
                    'flex',
                    'important'
                );

                label.style.setProperty(
                    'visibility',
                    'visible',
                    'important'
                );

                label.style.setProperty(
                    'opacity',
                    '1',
                    'important'
                );
            }
        );
    }

    function nestAudienceV26() {
        const form =
            document.getElementById(
                'esDashboardNoticeFormV6'
            );

        if (!form) {
            return;
        }

        /*
         * ============================
         * SaaS Audience
         * ============================
         */
        const targetType =
            form.querySelector(
                '[name="target_type"]'
            );

        const tenantWrap =
            document.getElementById(
                'esNoticeSelectedTenantsWrapV6'
            );

        const tenantMulti =
            document.getElementById(
                'esNoticeTenantMultiV21'
            );

        const tenantPanel =
            document.getElementById(
                'esNoticeTenantPanelV21'
            );

        const saasParent =
            fieldOfV26(targetType);

        const tenantField =
            fieldOfV26(
                tenantWrap || tenantMulti
            );

        if (
            saasParent
            && tenantField
            && saasParent !== tenantField
            && !saasParent.contains(
                tenantField
            )
        ) {
            saasParent.classList.add(
                'es-notice-audience-group-v26'
            );

            tenantField.classList.add(
                'es-notice-audience-sub-v26'
            );

            saasParent.appendChild(
                tenantField
            );
        }

        repairPanelOptionsV26(
            tenantPanel,
            'tenant'
        );

        /*
         * ============================
         * Central Audience
         * ============================
         */
        const centralParent =
            document.getElementById(
                'esNoticeCentralAudienceWrapV19'
            );

        const roleWrap =
            document.getElementById(
                'esNoticeCentralRolesWrapV19'
            );

        const userWrap =
            document.getElementById(
                'esNoticeCentralUsersWrapV21'
            );

        const roleField =
            fieldOfV26(roleWrap);

        const userField =
            fieldOfV26(userWrap);

        if (centralParent) {
            centralParent.classList.add(
                'es-notice-audience-group-v26'
            );

            if (
                roleField
                && roleField !== centralParent
                && !centralParent.contains(
                    roleField
                )
            ) {
                roleField.classList.add(
                    'es-notice-audience-sub-v26'
                );

                centralParent.appendChild(
                    roleField
                );
            }

            if (
                userField
                && userField !== centralParent
                && !centralParent.contains(
                    userField
                )
            ) {
                userField.classList.add(
                    'es-notice-audience-sub-v26'
                );

                centralParent.appendChild(
                    userField
                );
            }
        }

        repairPanelOptionsV26(
            document.getElementById(
                'esNoticeCentralRolePanelV19'
            ),
            'central_role'
        );

        repairPanelOptionsV26(
            document.getElementById(
                'esNoticeCentralUserPanelV21'
            ),
            'central_user'
        );

        /*
         * Re-run existing visibility logic after physically moving
         * the sub-options beside their parent selector.
         */
        if (
            typeof window
                .esSyncDeliveryAudienceV25
            === 'function'
        ) {
            window
                .esSyncDeliveryAudienceV25();
        }

        /*
         * Keep sub-options tied to the parent Audience mode.
         */
        const centralTarget =
            form.querySelector(
                '[name="central_target_type"]'
            );

        const syncNestedVisibility =
            function () {
                if (
                    tenantField
                    && targetType
                ) {
                    tenantField.style.display =
                        targetType.value ===
                        'selected'
                            ? ''
                            : 'none';
                }

                if (
                    roleField
                    && centralTarget
                ) {
                    roleField.style.display =
                        centralTarget.value ===
                        'selected'
                            ? ''
                            : 'none';
                }

                if (
                    userField
                    && centralTarget
                ) {
                    userField.style.display =
                        centralTarget.value ===
                        'selected'
                            ? ''
                            : 'none';
                }
            };

        syncNestedVisibility();

        targetType?.addEventListener(
            'change',
            syncNestedVisibility
        );

        centralTarget?.addEventListener(
            'change',
            syncNestedVisibility
        );

        form.addEventListener(
            'change',
            function (event) {
                if (
                    event.target.matches(
                        'input[name="delivery_channels[]"]'
                    )
                ) {
                    setTimeout(
                        syncNestedVisibility,
                        0
                    );
                }
            }
        );

        /*
         * Existing Edit/Reset scripts update values after load.
         * A short second pass guarantees nested visibility and
         * option text remain correct.
         */
        setTimeout(
            function () {
                repairPanelOptionsV26(
                    document.getElementById(
                        'esNoticeTenantPanelV21'
                    ),
                    'tenant'
                );

                repairPanelOptionsV26(
                    document.getElementById(
                        'esNoticeCentralRolePanelV19'
                    ),
                    'central_role'
                );

                repairPanelOptionsV26(
                    document.getElementById(
                        'esNoticeCentralUserPanelV21'
                    ),
                    'central_user'
                );

                syncNestedVisibility();
            },
            150
        );
    }

    function bootAudienceV26() {
        /*
         * V21 and V25 build/move parts of the audience controls.
         * Run after those existing DOM handlers have completed.
         */
        setTimeout(
            nestAudienceV26,
            0
        );

        setTimeout(
            nestAudienceV26,
            200
        );
    }

    if (
        document.readyState ===
        'loading'
    ) {
        document.addEventListener(
            'DOMContentLoaded',
            bootAudienceV26
        );
    } else {
        bootAudienceV26();
    }
})();
</script>


{{-- ESUBIZ_NOTICE_AUDIENCE_VISIBILITY_SEARCH_MOBILE_V27 --}}
<style>
    /*
     * V27 authoritative visibility.
     * [hidden] must beat the older V26 display:flex !important.
     */
    #esDashboardNoticeFormV6 [hidden] {
        display:none !important;
    }

    #esNoticeCentralAudienceWrapV19.es-v27-force-hidden,
    [data-es-saas-audience-v25="1"].es-v27-force-hidden,
    #esNoticeCentralRolesWrapV19.es-v27-force-hidden,
    #esNoticeCentralUsersWrapV21.es-v27-force-hidden,
    #esNoticeSelectedTenantsWrapV6.es-v27-force-hidden {
        display:none !important;
    }

    /*
     * Search box inside dynamic multi-select dropdowns.
     */
    .es-notice-dropdown-search-wrap-v27 {
        position:sticky;
        top:0;
        z-index:4;
        display:block;
        width:100%;
        padding:6px;
        margin:0 0 4px;
        background:#fff;
        box-sizing:border-box;
    }

    .es-notice-dropdown-search-v27 {
        display:block !important;
        width:100% !important;
        max-width:100% !important;
        min-width:0 !important;
        height:34px !important;
        padding:6px 10px !important;
        margin:0 !important;
        border:1px solid #d8dee8 !important;
        border-radius:8px !important;
        background:#fff !important;
        color:#0b1f3a !important;
        font-size:12px !important;
        line-height:1.2 !important;
        outline:none !important;
        box-sizing:border-box !important;
    }

    .es-notice-dropdown-search-v27:focus {
        border-color:#0b1f3a !important;
        box-shadow:0 0 0 2px rgba(11,31,58,.08) !important;
    }

    .es-notice-dropdown-empty-v27 {
        display:none;
        padding:11px 10px;
        color:#7a8596;
        font-size:12px;
        text-align:center;
    }

    /*
     * Keep every dropdown row visible and readable.
     */
    #esNoticeTenantPanelV21 label,
    #esNoticeCentralRolePanelV19 label,
    #esNoticeCentralUserPanelV21 label,
    #esNoticeTenantPanelV21 .es-notice-delivery-option-v18,
    #esNoticeCentralRolePanelV19 .es-notice-delivery-option-v18,
    #esNoticeCentralUserPanelV21 .es-notice-delivery-option-v18 {
        visibility:visible !important;
        opacity:1 !important;
        color:#0b1f3a !important;
        background:#fff !important;
    }

    /*
     * Mobile containment.
     */
    #esDashboardNoticeFormV6 {
        width:100% !important;
        max-width:100% !important;
        min-width:0 !important;
        box-sizing:border-box !important;
    }

    #esDashboardNoticeFormV6 * {
        box-sizing:border-box;
    }

    @media (max-width:767px) {
        html,
        body {
            max-width:100vw;
            overflow-x:hidden;
        }

        #esDashboardNoticeFormV6,
        #esDashboardNoticeFormV6 > *,
        #esDashboardNoticeFormV6 .es-notice-field-v6,
        #esNoticeDeliveryAudienceRowV25,
        #esNoticeDeliveryColumnV25,
        #esNoticeAudienceColumnV25,
        .es-notice-audience-group-v26,
        .es-notice-audience-sub-v26 {
            width:100% !important;
            max-width:100% !important;
            min-width:0 !important;
            margin-left:0 !important;
            margin-right:0 !important;
            transform:none !important;
            box-sizing:border-box !important;
        }

        #esDashboardNoticeFormV6 > *,
        #esDashboardNoticeFormV6 .es-notice-field-v6 {
            grid-column:1 / -1 !important;
        }

        #esDashboardNoticeFormV6 input:not([type="checkbox"]):not([type="radio"]),
        #esDashboardNoticeFormV6 select,
        #esDashboardNoticeFormV6 textarea,
        #esDashboardNoticeFormV6 button {
            max-width:100% !important;
            min-width:0 !important;
            box-sizing:border-box !important;
        }

        #esNoticeDeliveryAudienceRowV25 {
            display:grid !important;
            grid-template-columns:minmax(0, 1fr) !important;
            gap:12px !important;
        }

        #esNoticeAudienceColumnV25 {
            gap:12px !important;
        }

        /*
         * Dynamic lists expand underneath their own control.
         */
        #esNoticeTenantPanelV21,
        #esNoticeCentralRolePanelV19,
        #esNoticeCentralUserPanelV21 {
            position:static !important;
            left:auto !important;
            right:auto !important;
            top:auto !important;
            bottom:auto !important;
            transform:none !important;
            width:100% !important;
            min-width:0 !important;
            max-width:100% !important;
            margin:5px 0 0 !important;
        }
    }
</style>

<script>
(function () {
    const FORM_ID = 'esDashboardNoticeFormV6';

    function formV27() {
        return document.getElementById(FORM_ID);
    }

    function checkedDeliveryV27() {
        const form = formV27();

        if (!form) {
            return [];
        }

        return Array.from(
            form.querySelectorAll(
                'input[name="delivery_channels[]"]:checked'
            )
        ).map(function (input) {
            return String(input.value);
        });
    }

    function forceVisibilityV27(element, visible) {
        if (!element) {
            return;
        }

        if (visible) {
            element.hidden = false;
            element.classList.remove(
                'es-v27-force-hidden'
            );

            element.style.removeProperty(
                'display'
            );
        } else {
            element.hidden = true;
            element.classList.add(
                'es-v27-force-hidden'
            );

            element.style.setProperty(
                'display',
                'none',
                'important'
            );
        }
    }

    function syncAudienceVisibilityV27() {
        const form = formV27();

        if (!form) {
            return;
        }

        const delivery =
            checkedDeliveryV27();

        const hasSaas =
            delivery.includes('saas');

        const hasCentral =
            delivery.includes('central');

        const saasAudience =
            document.querySelector(
                '[data-es-saas-audience-v25="1"]'
            )
            || form.querySelector(
                '[name="target_type"]'
            )?.closest(
                '.es-notice-audience-group-v26'
            )
            || form.querySelector(
                '[name="target_type"]'
            )?.closest(
                '.es-notice-field-v6'
            );

        const centralAudience =
            document.getElementById(
                'esNoticeCentralAudienceWrapV19'
            );

        const audienceColumn =
            document.getElementById(
                'esNoticeAudienceColumnV25'
            );

        /*
         * Main Audience sections follow Delivery exactly.
         */
        forceVisibilityV27(
            saasAudience,
            hasSaas
        );

        forceVisibilityV27(
            centralAudience,
            hasCentral
        );

        if (audienceColumn) {
            forceVisibilityV27(
                audienceColumn,
                hasSaas || hasCentral
            );
        }

        /*
         * SaaS selected tenant sub-option.
         */
        const targetType =
            form.querySelector(
                '[name="target_type"]'
            );

        const tenantWrap =
            document.getElementById(
                'esNoticeSelectedTenantsWrapV6'
            )
            || document.getElementById(
                'esNoticeTenantMultiV21'
            )?.closest(
                '.es-notice-audience-sub-v26'
            );

        forceVisibilityV27(
            tenantWrap,
            hasSaas
            && targetType
            && targetType.value === 'selected'
        );

        /*
         * Central selected role/user sub-options.
         */
        const centralTarget =
            form.querySelector(
                '[name="central_target_type"]'
            );

        const centralSelected =
            hasCentral
            && centralTarget
            && centralTarget.value === 'selected';

        const roleWrap =
            document.getElementById(
                'esNoticeCentralRolesWrapV19'
            )
            || document.getElementById(
                'esNoticeCentralRoleMultiV19'
            )?.closest(
                '.es-notice-audience-sub-v26'
            );

        const userWrap =
            document.getElementById(
                'esNoticeCentralUsersWrapV21'
            )
            || document.getElementById(
                'esNoticeCentralUserMultiV21'
            )?.closest(
                '.es-notice-audience-sub-v26'
            );

        forceVisibilityV27(
            roleWrap,
            centralSelected
        );

        forceVisibilityV27(
            userWrap,
            centralSelected
        );
    }

    function cleanV27(value) {
        return String(value || '')
            .replace(/\s+/g, ' ')
            .trim();
    }

    function optionRowsV27(panel) {
        if (!panel) {
            return [];
        }

        const checkboxes =
            Array.from(
                panel.querySelectorAll(
                    'input[type="checkbox"]'
                )
            );

        const rows = [];

        checkboxes.forEach(function (checkbox) {
            let row =
                checkbox.closest('label');

            if (!row) {
                row =
                    checkbox.closest(
                        '.es-notice-delivery-option-v18'
                    );
            }

            if (!row) {
                const wrapper =
                    document.createElement(
                        'label'
                    );

                wrapper.className =
                    'es-notice-delivery-option-v18';

                checkbox.parentNode?.insertBefore(
                    wrapper,
                    checkbox
                );

                wrapper.appendChild(
                    checkbox
                );

                row = wrapper;
            }

            if (!rows.includes(row)) {
                rows.push(row);
            }
        });

        return rows;
    }

    function ensureReadableOptionV27(
        checkbox,
        row,
        type
    ) {
        if (!checkbox || !row) {
            return;
        }

        let text =
            cleanV27(
                row.textContent
            );

        if (!text) {
            if (type === 'role') {
                text =
                    cleanV27(
                        checkbox.dataset.roleName
                    );
            }

            if (type === 'user') {
                text =
                    cleanV27(
                        checkbox.dataset.userName
                        || checkbox.dataset.email
                    );
            }

            if (type === 'tenant') {
                text =
                    cleanV27(
                        checkbox.dataset.tenantName
                        || checkbox.dataset.name
                        || checkbox.dataset.label
                        || checkbox.dataset.domain
                    );
            }
        }

        if (!text) {
            const prefix =
                type === 'role'
                    ? 'Role'
                    : (
                        type === 'user'
                            ? 'User'
                            : 'Tenant'
                    );

            text =
                prefix + ' #' + checkbox.value;
        }

        const hasReadableChild =
            Array.from(
                row.children
            ).some(function (child) {
                return (
                    child !== checkbox
                    && cleanV27(
                        child.textContent
                    )
                );
            });

        if (!hasReadableChild) {
            const span =
                document.createElement(
                    'span'
                );

            span.className =
                'es-notice-option-text-v26';

            span.textContent =
                text;

            row.appendChild(span);
        }

        row.style.setProperty(
            'display',
            'flex',
            'important'
        );

        row.style.setProperty(
            'visibility',
            'visible',
            'important'
        );

        row.style.setProperty(
            'opacity',
            '1',
            'important'
        );
    }

    function installSearchV27(
        panelId,
        placeholder,
        type
    ) {
        const panel =
            document.getElementById(
                panelId
            );

        if (!panel) {
            return;
        }

        if (
            panel.querySelector(
                '.es-notice-dropdown-search-v27'
            )
        ) {
            return;
        }

        const rows =
            optionRowsV27(panel);

        rows.forEach(function (row) {
            const checkbox =
                row.querySelector(
                    'input[type="checkbox"]'
                );

            ensureReadableOptionV27(
                checkbox,
                row,
                type
            );
        });

        const searchWrap =
            document.createElement('div');

        searchWrap.className =
            'es-notice-dropdown-search-wrap-v27';

        const search =
            document.createElement('input');

        search.type = 'search';
        search.className =
            'es-notice-dropdown-search-v27';

        search.placeholder =
            placeholder;

        search.autocomplete = 'off';

        search.setAttribute(
            'aria-label',
            placeholder
        );

        const empty =
            document.createElement('div');

        empty.className =
            'es-notice-dropdown-empty-v27';

        empty.textContent =
            'No matching result';

        searchWrap.appendChild(
            search
        );

        panel.insertBefore(
            searchWrap,
            panel.firstChild
        );

        panel.appendChild(
            empty
        );

        /*
         * Clicking the search field must not close
         * the parent dropdown.
         */
        [
            'click',
            'mousedown',
            'touchstart'
        ].forEach(function (eventName) {
            search.addEventListener(
                eventName,
                function (event) {
                    event.stopPropagation();
                }
            );
        });

        search.addEventListener(
            'input',
            function () {
                const query =
                    cleanV27(
                        search.value
                    ).toLowerCase();

                let visibleCount = 0;

                rows.forEach(function (row) {
                    const haystack =
                        cleanV27(
                            row.textContent
                        ).toLowerCase();

                    const checkbox =
                        row.querySelector(
                            'input[type="checkbox"]'
                        );

                    const metadata =
                        checkbox
                            ? cleanV27(
                                Object.values(
                                    checkbox.dataset
                                ).join(' ')
                            ).toLowerCase()
                            : '';

                    const match =
                        !query
                        || haystack.includes(
                            query
                        )
                        || metadata.includes(
                            query
                        );

                    row.style.setProperty(
                        'display',
                        match
                            ? 'flex'
                            : 'none',
                        'important'
                    );

                    if (match) {
                        visibleCount++;
                    }
                });

                empty.style.display =
                    visibleCount === 0
                        ? 'block'
                        : 'none';
            }
        );
    }

    function installAllSearchesV27() {
        installSearchV27(
            'esNoticeTenantPanelV21',
            'Search SaaS tenants...',
            'tenant'
        );

        installSearchV27(
            'esNoticeCentralRolePanelV19',
            'Search Central roles...',
            'role'
        );

        installSearchV27(
            'esNoticeCentralUserPanelV21',
            'Search Central users...',
            'user'
        );
    }

    function mobileContainmentV27() {
        const form = formV27();

        if (!form) {
            return;
        }

        if (
            !window.matchMedia(
                '(max-width: 767px)'
            ).matches
        ) {
            return;
        }

        /*
         * Remove inherited minimum widths from the
         * Dashboard Notice card/form ancestry only.
         */
        let current = form;

        for (
            let i = 0;
            i < 5 && current;
            i++
        ) {
            current.style.setProperty(
                'min-width',
                '0',
                'important'
            );

            current.style.setProperty(
                'max-width',
                '100%',
                'important'
            );

            current.style.setProperty(
                'box-sizing',
                'border-box',
                'important'
            );

            current = current.parentElement;
        }

        form.querySelectorAll(
            '.es-notice-field-v6,'
            + ' input, select, textarea,'
            + ' #esNoticeDeliveryAudienceRowV25,'
            + ' #esNoticeDeliveryColumnV25,'
            + ' #esNoticeAudienceColumnV25'
        ).forEach(function (element) {
            element.style.setProperty(
                'min-width',
                '0',
                'important'
            );

            element.style.setProperty(
                'max-width',
                '100%',
                'important'
            );

            element.style.setProperty(
                'box-sizing',
                'border-box',
                'important'
            );
        });
    }

    function syncV27() {
        syncAudienceVisibilityV27();
        installAllSearchesV27();
        mobileContainmentV27();
    }

    function bootV27() {
        const form = formV27();

        if (!form) {
            return;
        }

        syncV27();

        /*
         * Existing V18/V19/V21/V25/V26 scripts may run
         * immediately after DOMContentLoaded, so V27 gets
         * the final state afterwards.
         */
        setTimeout(syncV27, 0);
        setTimeout(syncV27, 100);
        setTimeout(syncV27, 300);

        form.addEventListener(
            'change',
            function (event) {
                if (
                    event.target.matches(
                        'input[name="delivery_channels[]"]'
                    )
                    || event.target.matches(
                        '[name="target_type"]'
                    )
                    || event.target.matches(
                        '[name="central_target_type"]'
                    )
                ) {
                    setTimeout(
                        syncAudienceVisibilityV27,
                        0
                    );
                }
            },
            true
        );

        /*
         * Edit/Reset actions from earlier scripts can
         * change selections programmatically.
         */
        form.addEventListener(
            'click',
            function () {
                setTimeout(
                    syncAudienceVisibilityV27,
                    30
                );

                setTimeout(
                    installAllSearchesV27,
                    50
                );
            },
            true
        );

        window.addEventListener(
            'resize',
            mobileContainmentV27
        );

        window.esSyncDashboardNoticeAudienceV27 =
            syncAudienceVisibilityV27;
    }

    if (
        document.readyState === 'loading'
    ) {
        document.addEventListener(
            'DOMContentLoaded',
            bootV27
        );
    } else {
        bootV27();
    }
})();
</script>


{{-- ESUBIZ_NOTICE_CENTRAL_ACCOUNTS_MOBILE_DISMISS_V28 --}}
<style>
    /*
     * ========================================================
     * V28 — AUTHORITATIVE MOBILE VIEWPORT FIX
     * ========================================================
     *
     * The complete Dashboard Notices area, not merely its inputs,
     * is prevented from exceeding the mobile viewport.
     */
    @media (max-width: 767px) {
        body.es-dashboard-notices-mobile-v28 {
            width:100% !important;
            max-width:100vw !important;
            min-width:0 !important;
            overflow-x:hidden !important;
        }

        body.es-dashboard-notices-mobile-v28
        .es-dashboard-notice-mobile-shell-v28 {
            position:relative !important;
            left:auto !important;
            right:auto !important;
            inset-inline:auto !important;
            transform:none !important;

            width:100% !important;
            max-width:100% !important;
            min-width:0 !important;

            margin-left:0 !important;
            margin-right:0 !important;

            box-sizing:border-box !important;
        }

        body.es-dashboard-notices-mobile-v28
        #esDashboardNoticeFormV6 {
            display:block !important;

            width:100% !important;
            max-width:100% !important;
            min-width:0 !important;

            margin-left:0 !important;
            margin-right:0 !important;

            padding-left:0 !important;
            padding-right:0 !important;

            box-sizing:border-box !important;
            overflow:visible !important;
        }

        body.es-dashboard-notices-mobile-v28
        #esDashboardNoticeFormV6 > *,
        body.es-dashboard-notices-mobile-v28
        #esDashboardNoticeFormV6 .es-notice-field-v6,
        body.es-dashboard-notices-mobile-v28
        #esNoticeDeliveryAudienceRowV25,
        body.es-dashboard-notices-mobile-v28
        #esNoticeDeliveryColumnV25,
        body.es-dashboard-notices-mobile-v28
        #esNoticeAudienceColumnV25,
        body.es-dashboard-notices-mobile-v28
        .es-notice-audience-group-v26,
        body.es-dashboard-notices-mobile-v28
        .es-notice-audience-sub-v26 {
            position:relative !important;

            left:auto !important;
            right:auto !important;

            width:100% !important;
            max-width:100% !important;
            min-width:0 !important;

            margin-left:0 !important;
            margin-right:0 !important;

            transform:none !important;

            grid-column:1 / -1 !important;

            box-sizing:border-box !important;
        }

        body.es-dashboard-notices-mobile-v28
        #esNoticeDeliveryAudienceRowV25 {
            display:grid !important;
            grid-template-columns:minmax(0, 1fr) !important;
            gap:12px !important;
        }

        body.es-dashboard-notices-mobile-v28
        #esDashboardNoticeFormV6 input:not([type="checkbox"]):not([type="radio"]),
        body.es-dashboard-notices-mobile-v28
        #esDashboardNoticeFormV6 select,
        body.es-dashboard-notices-mobile-v28
        #esDashboardNoticeFormV6 textarea,
        body.es-dashboard-notices-mobile-v28
        #esDashboardNoticeFormV6 button {
            width:100% !important;
            max-width:100% !important;
            min-width:0 !important;
            box-sizing:border-box !important;
        }

        /*
         * Do not make close buttons / compact controls full width.
         */
        body.es-dashboard-notices-mobile-v28
        #esDashboardNoticeFormV6 button[type="button"].es-notice-delivery-option-v18,
        body.es-dashboard-notices-mobile-v28
        #esDashboardNoticeFormV6 input[type="checkbox"],
        body.es-dashboard-notices-mobile-v28
        #esDashboardNoticeFormV6 input[type="radio"] {
            width:auto !important;
        }

        #esNoticeTenantPanelV21,
        #esNoticeCentralRolePanelV19,
        #esNoticeCentralUserPanelV21 {
            position:static !important;

            width:100% !important;
            max-width:100% !important;
            min-width:0 !important;

            left:auto !important;
            right:auto !important;
            top:auto !important;
            bottom:auto !important;

            margin:6px 0 0 !important;

            transform:none !important;

            box-sizing:border-box !important;
        }

        .es-notice-dropdown-search-wrap-v27,
        .es-notice-dropdown-search-v27 {
            width:100% !important;
            max-width:100% !important;
            min-width:0 !important;
            box-sizing:border-box !important;
        }
    }


    /*
     * ========================================================
     * CENTRAL ROLE ACCOUNT INFORMATION
     * ========================================================
     */
    .es-central-role-account-count-v28 {
        display:block;
        margin-top:2px;
        color:#7a8596;
        font-size:10px;
        line-height:1.3;
    }

    .es-central-role-account-count-v28 strong {
        display:inline !important;
        color:#566176 !important;
        font-size:inherit !important;
        font-weight:600 !important;
    }

    .es-central-user-role-v28 {
        display:inline-flex;
        align-items:center;
        margin:3px 4px 0 0;
        padding:2px 6px;
        border-radius:999px;
        background:#f0f3f7;
        color:#536176;
        font-size:9px;
        line-height:1.2;
        font-weight:600;
    }
</style>

<script>
(function () {
    const formIdV28 =
        'esDashboardNoticeFormV6';

    function formV28() {
        return document.getElementById(
            formIdV28
        );
    }


    /*
     * ========================================================
     * CENTRAL ROLE MEMBERSHIP
     * ========================================================
     *
     * A role selection represents every Central account assigned
     * to that role through user_roles.
     *
     * No role name is hard-coded.
     * IDs remain the source of truth.
     *
     * Future users assigned to a selected role are therefore
     * automatically recipients without editing the notice.
     */
    async function loadCentralMembershipV28() {
        const rolePanel =
            document.getElementById(
                'esNoticeCentralRolePanelV19'
            );

        const userPanel =
            document.getElementById(
                'esNoticeCentralUserPanelV21'
            );

        if (!rolePanel && !userPanel) {
            return;
        }

        /*
         * Server-rendered role checkboxes already carry their role
         * IDs. The actual notice delivery engine resolves those IDs
         * through user_roles at delivery time.
         *
         * Here we enhance the interface using current checkbox/user
         * metadata and make that automatic relationship explicit.
         */
        if (rolePanel) {
            rolePanel
                .querySelectorAll(
                    'input[name="central_role_ids[]"]'
                )
                .forEach(function (checkbox) {
                    const row =
                        checkbox.closest('label')
                        || checkbox.closest(
                            '.es-notice-delivery-option-v18'
                        );

                    if (!row) {
                        return;
                    }

                    if (
                        row.querySelector(
                            '.es-central-role-account-count-v28'
                        )
                    ) {
                        return;
                    }

                    const count =
                        checkbox.dataset.accountCount
                        || checkbox.dataset.userCount
                        || checkbox.dataset.memberCount
                        || '';

                    const info =
                        document.createElement(
                            'span'
                        );

                    info.className =
                        'es-central-role-account-count-v28';

                    info.innerHTML =
                        count !== ''
                            ? '<strong>'
                                + String(count)
                                + '</strong> Central account'
                                + (
                                    String(count) === '1'
                                        ? ''
                                        : 's'
                                )
                                + ' currently assigned. Future assignments are included automatically.'
                            : 'All Central accounts assigned to this role are targeted automatically.';

                    row.appendChild(info);
                });
        }

        /*
         * Specific Central Users are independent overrides.
         * Role-targeted users do not have to be manually selected
         * here because role membership is resolved automatically.
         */
        if (userPanel) {
            userPanel
                .querySelectorAll(
                    'input[name="central_user_ids[]"]'
                )
                .forEach(function (checkbox) {
                    const row =
                        checkbox.closest('label')
                        || checkbox.closest(
                            '.es-notice-delivery-option-v18'
                        );

                    if (!row) {
                        return;
                    }

                    row.dataset.centralAccountV28 =
                        '1';
                });
        }
    }


    /*
     * ========================================================
     * MOBILE VIEWPORT NORMALIZER
     * ========================================================
     *
     * Previous versions constrained individual fields but an older
     * Site Settings wrapper is still wider than the viewport.
     *
     * V28 marks the actual ancestor chain from the notice form up
     * to the main page content and constrains that chain only on
     * mobile.
     */
    function normalizeMobileViewportV28() {
        const form =
            formV28();

        if (!form) {
            return;
        }

        const mobile =
            window.matchMedia(
                '(max-width: 767px)'
            ).matches;

        if (!mobile) {
            document.body.classList.remove(
                'es-dashboard-notices-mobile-v28'
            );

            document
                .querySelectorAll(
                    '.es-dashboard-notice-mobile-shell-v28'
                )
                .forEach(function (element) {
                    element.classList.remove(
                        'es-dashboard-notice-mobile-shell-v28'
                    );
                });

            return;
        }

        document.body.classList.add(
            'es-dashboard-notices-mobile-v28'
        );

        /*
         * Include enough of the actual ancestor chain to eliminate
         * desktop grid/min-width rules causing the page to lean
         * outside the viewport.
         */
        let current = form;

        for (
            let depth = 0;
            current
            && current !== document.body
            && depth < 12;
            depth++
        ) {
            current.classList.add(
                'es-dashboard-notice-mobile-shell-v28'
            );

            current.style.setProperty(
                'min-width',
                '0',
                'important'
            );

            current.style.setProperty(
                'max-width',
                '100%',
                'important'
            );

            current.style.setProperty(
                'box-sizing',
                'border-box',
                'important'
            );

            /*
             * Only the Dashboard Notice chain is changed.
             * We do not alter the global navigation/header.
             */
            const computed =
                window.getComputedStyle(
                    current
                );

            if (
                computed.position === 'absolute'
                || computed.position === 'fixed'
            ) {
                current.style.setProperty(
                    'position',
                    'relative',
                    'important'
                );

                current.style.setProperty(
                    'left',
                    'auto',
                    'important'
                );

                current.style.setProperty(
                    'right',
                    'auto',
                    'important'
                );
            }

            current = current.parentElement;
        }

        /*
         * Find the actual widest offending descendant and ensure no
         * notice control is allowed beyond its parent width.
         */
        form.querySelectorAll('*')
            .forEach(function (element) {
                const rect =
                    element.getBoundingClientRect();

                const parent =
                    element.parentElement;

                if (!parent) {
                    return;
                }

                const parentRect =
                    parent.getBoundingClientRect();

                if (
                    rect.width >
                    parentRect.width + 2
                ) {
                    element.style.setProperty(
                        'max-width',
                        '100%',
                        'important'
                    );

                    element.style.setProperty(
                        'min-width',
                        '0',
                        'important'
                    );

                    element.style.setProperty(
                        'box-sizing',
                        'border-box',
                        'important'
                    );
                }
            });
    }


    /*
     * ========================================================
     * UNIVERSAL DISMISSIBLE WORDING
     * ========================================================
     */
    function normalizeDismissibleControlV28() {
        const form =
            formV28();

        if (!form) {
            return;
        }

        const dismiss =
            form.querySelector(
                '[name="dismissible"]'
            );

        if (!dismiss) {
            return;
        }

        const field =
            dismiss.closest(
                '.es-notice-field-v6'
            )
            || dismiss.parentElement;

        if (!field) {
            return;
        }

        const candidates =
            field.querySelectorAll(
                'label, strong, .form-label, small, p'
            );

        candidates.forEach(
            function (node) {
                const value =
                    String(
                        node.textContent || ''
                    );

                if (
                    /core\s*admin\s*can\s*close/i
                        .test(value)
                ) {
                    node.textContent =
                        value.replace(
                            /core\s*admin\s*can\s*close(?:\s*notice)?/i,
                            'Recipients Can Close Notice'
                        );
                }

                if (
                    /core\s*admin/i.test(
                        node.textContent || ''
                    )
                ) {
                    node.textContent =
                        String(
                            node.textContent
                        ).replace(
                            /core\s*admin/ig,
                            'recipient'
                        );
                }
            }
        );
    }


    function bootV28() {
        const form =
            formV28();

        if (!form) {
            return;
        }

        normalizeDismissibleControlV28();
        normalizeMobileViewportV28();

        setTimeout(
            loadCentralMembershipV28,
            0
        );

        setTimeout(
            loadCentralMembershipV28,
            250
        );

        setTimeout(
            normalizeMobileViewportV28,
            0
        );

        setTimeout(
            normalizeMobileViewportV28,
            250
        );

        window.addEventListener(
            'resize',
            normalizeMobileViewportV28
        );

        window.addEventListener(
            'orientationchange',
            function () {
                setTimeout(
                    normalizeMobileViewportV28,
                    150
                );
            }
        );

        /*
         * Edit/Add/reset workflows can rebuild portions of the
         * audience control, so restore enhancements afterwards.
         */
        form.addEventListener(
            'click',
            function () {
                setTimeout(
                    loadCentralMembershipV28,
                    60
                );

                setTimeout(
                    normalizeMobileViewportV28,
                    60
                );
            },
            true
        );
    }

    if (
        document.readyState ===
        'loading'
    ) {
        document.addEventListener(
            'DOMContentLoaded',
            bootV28
        );
    } else {
        bootV28();
    }
})();
</script>


{{-- ESUBIZ_DASHBOARD_NOTICE_MOBILE_VIEWPORT_V30 --}}
<style>
@media (max-width:767px) {

    /*
     * Do not allow the page itself to drift horizontally.
     * Horizontal scrolling belongs ONLY to the saved notices list.
     */
    html,
    body {
        width:100% !important;
        max-width:100% !important;
        overflow-x:hidden !important;
    }

    /*
     * V30 runtime-selected Dashboard Notices workspace.
     */
    .es-notice-mobile-workspace-v30 {
        position:relative !important;

        left:auto !important;
        right:auto !important;
        transform:none !important;

        width:calc(100vw - 20px) !important;
        max-width:calc(100vw - 20px) !important;
        min-width:0 !important;

        margin-left:auto !important;
        margin-right:auto !important;

        box-sizing:border-box !important;
        overflow:visible !important;
    }

    .es-notice-mobile-workspace-v30 *,
    .es-notice-mobile-chain-v30 {
        box-sizing:border-box !important;
    }

    /*
     * Add/Edit form must always fit the actual phone width.
     */
    #esDashboardNoticeFormV6,
    #esDashboardNoticeFormV6 > *,
    #esDashboardNoticeFormV6 .es-notice-field-v6,
    #esNoticeDeliveryAudienceRowV25,
    #esNoticeDeliveryColumnV25,
    #esNoticeAudienceColumnV25,
    .es-notice-audience-group-v26,
    .es-notice-audience-sub-v26 {
        position:relative !important;

        left:auto !important;
        right:auto !important;

        width:100% !important;
        max-width:100% !important;
        min-width:0 !important;

        margin-left:0 !important;
        margin-right:0 !important;

        transform:none !important;

        grid-column:1 / -1 !important;

        box-sizing:border-box !important;
    }

    #esDashboardNoticeFormV6 {
        display:block !important;
        overflow:visible !important;
    }

    #esDashboardNoticeFormV6 input:not([type="checkbox"]):not([type="radio"]),
    #esDashboardNoticeFormV6 select,
    #esDashboardNoticeFormV6 textarea {
        width:100% !important;
        max-width:100% !important;
        min-width:0 !important;

        box-sizing:border-box !important;
    }

    #esNoticeDeliveryAudienceRowV25 {
        display:grid !important;
        grid-template-columns:minmax(0,1fr) !important;
        gap:12px !important;
    }

    /*
     * Audience dropdown lists remain under their own field.
     */
    #esNoticeTenantPanelV21,
    #esNoticeCentralRolePanelV19,
    #esNoticeCentralUserPanelV21 {
        position:static !important;

        left:auto !important;
        right:auto !important;
        top:auto !important;
        bottom:auto !important;

        width:100% !important;
        max-width:100% !important;
        min-width:0 !important;

        margin:6px 0 0 !important;

        transform:none !important;

        box-sizing:border-box !important;
    }

    /*
     * Saved notices:
     * the viewport stays fixed, but this area can be swiped sideways.
     */
    #esDashboardNoticeListV6 {
        display:block !important;

        position:relative !important;

        width:100% !important;
        max-width:100% !important;
        min-width:0 !important;

        overflow-x:auto !important;
        overflow-y:visible !important;

        -webkit-overflow-scrolling:touch;

        margin-left:0 !important;
        margin-right:0 !important;
        padding-bottom:10px !important;

        box-sizing:border-box !important;
    }

    /*
     * Table-style saved notice list.
     */
    #esDashboardNoticeListV6 table {
        width:max-content !important;
        min-width:760px !important;
        max-width:none !important;

        table-layout:auto !important;
    }

    #esDashboardNoticeListV6 table th,
    #esDashboardNoticeListV6 table td {
        white-space:nowrap !important;
    }

    /*
     * Non-table saved rows/cards must also stay inside the
     * scroll container instead of stretching the whole page.
     */
    #esDashboardNoticeListV6 > .es-notice-saved-row-v30 {
        min-width:720px !important;
        width:720px !important;
        max-width:none !important;
    }
}
</style>

<script>
(function () {

    function installMobileViewportV30() {
        const form =
            document.getElementById(
                'esDashboardNoticeFormV6'
            );

        if (!form) {
            return;
        }

        const isMobile =
            window.matchMedia(
                '(max-width:767px)'
            ).matches;

        if (!isMobile) {
            document
                .querySelectorAll(
                    '.es-notice-mobile-workspace-v30,'
                    + '.es-notice-mobile-chain-v30'
                )
                .forEach(function (el) {
                    el.classList.remove(
                        'es-notice-mobile-workspace-v30',
                        'es-notice-mobile-chain-v30'
                    );

                    el.style.removeProperty('width');
                    el.style.removeProperty('max-width');
                    el.style.removeProperty('min-width');
                    el.style.removeProperty('margin-left');
                    el.style.removeProperty('margin-right');
                    el.style.removeProperty('left');
                    el.style.removeProperty('right');
                    el.style.removeProperty('transform');
                });

            return;
        }

        const list =
            document.getElementById(
                'esDashboardNoticeListV6'
            );

        /*
         * Find the smallest common Dashboard Notices container
         * that owns both the Add/Edit form and saved notices list.
         */
        let workspace = form;

        if (list) {
            let candidate =
                form.parentElement;

            while (
                candidate
                && candidate !== document.body
            ) {
                if (
                    candidate.contains(form)
                    && candidate.contains(list)
                ) {
                    workspace = candidate;
                    break;
                }

                candidate =
                    candidate.parentElement;
            }
        } else {
            workspace =
                form.parentElement || form;
        }

        workspace.classList.add(
            'es-notice-mobile-workspace-v30'
        );

        /*
         * Remove desktop minimum-width/grid offsets from the
         * form ancestry, without changing the global header/nav.
         */
        let node = form;

        while (
            node
            && node !== workspace.parentElement
        ) {
            node.classList.add(
                'es-notice-mobile-chain-v30'
            );

            node.style.setProperty(
                'min-width',
                '0',
                'important'
            );

            node.style.setProperty(
                'max-width',
                '100%',
                'important'
            );

            node.style.setProperty(
                'box-sizing',
                'border-box',
                'important'
            );

            node.style.setProperty(
                'left',
                'auto',
                'important'
            );

            node.style.setProperty(
                'right',
                'auto',
                'important'
            );

            node.style.setProperty(
                'transform',
                'none',
                'important'
            );

            node = node.parentElement;
        }

        /*
         * Correct any remaining right overflow dynamically.
         */
        requestAnimationFrame(function () {
            const rect =
                workspace.getBoundingClientRect();

            const viewportWidth =
                document.documentElement.clientWidth;

            const safeWidth =
                Math.max(
                    280,
                    viewportWidth - 20
                );

            workspace.style.setProperty(
                'width',
                safeWidth + 'px',
                'important'
            );

            workspace.style.setProperty(
                'max-width',
                safeWidth + 'px',
                'important'
            );

            workspace.style.setProperty(
                'margin-left',
                'auto',
                'important'
            );

            workspace.style.setProperty(
                'margin-right',
                'auto',
                'important'
            );

            /*
             * If an inherited desktop rule still pushes the
             * workspace beyond the right edge, counter it.
             */
            const corrected =
                workspace.getBoundingClientRect();

            if (
                corrected.right >
                viewportWidth - 5
            ) {
                const excess =
                    corrected.right
                    - (viewportWidth - 5);

                workspace.style.setProperty(
                    'margin-left',
                    Math.max(
                        0,
                        corrected.left - excess
                    ) + 'px',
                    'important'
                );

                workspace.style.setProperty(
                    'margin-right',
                    '0',
                    'important'
                );
            }
        });

        /*
         * Saved notice rows/cards:
         * if there is no table, give each direct saved row a
         * scrollable desktop-sized canvas inside the list only.
         */
        if (list && !list.querySelector('table')) {
            Array.from(
                list.children
            ).forEach(function (child) {
                /*
                 * Skip paginator/navigation elements.
                 */
                if (
                    child.matches(
                        'script, style'
                    )
                ) {
                    return;
                }

                child.classList.add(
                    'es-notice-saved-row-v30'
                );
            });
        }
    }


    function bootV30() {
        installMobileViewportV30();

        setTimeout(
            installMobileViewportV30,
            100
        );

        setTimeout(
            installMobileViewportV30,
            350
        );

        window.addEventListener(
            'resize',
            installMobileViewportV30
        );

        window.addEventListener(
            'orientationchange',
            function () {
                setTimeout(
                    installMobileViewportV30,
                    150
                );
            }
        );

        const form =
            document.getElementById(
                'esDashboardNoticeFormV6'
            );

        form?.addEventListener(
            'click',
            function () {
                setTimeout(
                    installMobileViewportV30,
                    50
                );
            },
            true
        );
    }


    if (
        document.readyState === 'loading'
    ) {
        document.addEventListener(
            'DOMContentLoaded',
            bootV30
        );
    } else {
        bootV30();
    }

})();
</script>


{{-- ESUBIZ_DASHBOARD_NOTICE_REAL_MOBILE_LAYOUT_V31 --}}
<style>
/*
 * ============================================================
 * V31 — REAL DASHBOARD NOTICE MOBILE STRUCTURE
 * ============================================================
 *
 * Actual hierarchy:
 *
 * .es-notice-shell-v6
 *   .es-notice-card-v6
 *     .es-notice-head-v6
 *     .es-notice-body-v6
 *       #esDashboardNoticeFormV6
 *         .es-notice-grid-v6
 *
 *   .es-notice-card-v6
 *     #esDashboardNoticeListV6
 *       .es-notice-table-wrap-v6
 *         .es-notice-table-scroll-v6
 *           .es-notice-table-v6
 *
 * These existing V6 elements are now the authoritative mobile
 * layout targets.
 */

@media (max-width:767px) {

    /*
     * Page itself must never become a horizontally shifted canvas.
     */
    html,
    body {
        width:100% !important;
        max-width:100vw !important;
        min-width:0 !important;
        overflow-x:hidden !important;
    }

    /*
     * Override V28/V30 runtime workspace sizing.
     */
    .es-notice-mobile-workspace-v30,
    .es-notice-mobile-chain-v30,
    body.es-dashboard-notices-mobile-v28
    .es-dashboard-notice-mobile-shell-v28 {
        width:auto !important;
        max-width:none !important;
        min-width:0 !important;

        margin-left:0 !important;
        margin-right:0 !important;

        left:auto !important;
        right:auto !important;

        transform:none !important;
    }


    /*
     * ========================================================
     * ACTUAL NOTICE SHELL
     * ========================================================
     */
    .es-notice-shell-v6 {
        display:block !important;

        position:relative !important;

        width:100% !important;
        max-width:100% !important;
        min-width:0 !important;

        margin:0 !important;
        padding:0 !important;

        left:auto !important;
        right:auto !important;

        transform:none !important;

        box-sizing:border-box !important;

        overflow:visible !important;
    }


    /*
     * Both Add/Edit and Saved Notice cards fit the viewport.
     */
    .es-notice-shell-v6 > .es-notice-card-v6,
    .es-notice-card-v6 {
        display:block !important;

        position:relative !important;

        width:100% !important;
        max-width:100% !important;
        min-width:0 !important;

        margin-left:0 !important;
        margin-right:0 !important;

        left:auto !important;
        right:auto !important;

        transform:none !important;

        box-sizing:border-box !important;

        overflow:visible !important;
    }


    /*
     * Card heading/body cannot retain desktop minimum widths.
     */
    .es-notice-head-v6,
    .es-notice-head-v6 > div,
    .es-notice-body-v6 {
        display:block !important;

        width:100% !important;
        max-width:100% !important;
        min-width:0 !important;

        margin-left:0 !important;
        margin-right:0 !important;

        box-sizing:border-box !important;
    }

    .es-notice-head-v6 h3,
    .es-notice-head-v6 p {
        max-width:100% !important;
        overflow-wrap:anywhere !important;
    }


    /*
     * ========================================================
     * ADD / EDIT FORM
     * ========================================================
     */
    #esDashboardNoticeFormV6 {
        display:block !important;

        position:relative !important;

        width:100% !important;
        max-width:100% !important;
        min-width:0 !important;

        margin:0 !important;

        left:auto !important;
        right:auto !important;

        transform:none !important;

        box-sizing:border-box !important;

        overflow:visible !important;
    }

    #esDashboardNoticeFormV6 .es-notice-grid-v6 {
        display:grid !important;

        grid-template-columns:minmax(0,1fr) !important;

        width:100% !important;
        max-width:100% !important;
        min-width:0 !important;

        gap:14px !important;

        margin:0 !important;

        box-sizing:border-box !important;
    }

    #esDashboardNoticeFormV6
    .es-notice-grid-v6 > *,

    #esDashboardNoticeFormV6
    .es-notice-field-v6,

    #esDashboardNoticeFormV6
    .es-notice-span-2-v6,

    #esNoticeDeliveryAudienceRowV25,

    #esNoticeDeliveryColumnV25,

    #esNoticeAudienceColumnV25,

    .es-notice-audience-group-v26,

    .es-notice-audience-sub-v26 {
        grid-column:1 / -1 !important;

        position:relative !important;

        width:100% !important;
        max-width:100% !important;
        min-width:0 !important;

        margin-left:0 !important;
        margin-right:0 !important;

        left:auto !important;
        right:auto !important;

        transform:none !important;

        box-sizing:border-box !important;
    }


    /*
     * Delivery / Audience must be vertical on mobile.
     */
    #esNoticeDeliveryAudienceRowV25 {
        display:grid !important;

        grid-template-columns:minmax(0,1fr) !important;

        gap:12px !important;
    }


    /*
     * Inputs cannot force the grid wider.
     */
    #esDashboardNoticeFormV6
    input:not([type="checkbox"]):not([type="radio"]),

    #esDashboardNoticeFormV6 select,

    #esDashboardNoticeFormV6 textarea,

    #esDashboardNoticeFormV6
    .es-notice-delivery-trigger-v18,

    #esDashboardNoticeFormV6
    .es-notice-dropdown-search-v27 {
        display:block !important;

        width:100% !important;
        max-width:100% !important;
        min-width:0 !important;

        margin-left:0 !important;
        margin-right:0 !important;

        box-sizing:border-box !important;
    }


    /*
     * Audience dropdowns open directly underneath their field.
     */
    #esNoticeTenantMultiV21,
    #esNoticeCentralRoleMultiV19,
    #esNoticeCentralUserMultiV21 {
        position:relative !important;

        width:100% !important;
        max-width:100% !important;
        min-width:0 !important;

        box-sizing:border-box !important;
    }

    #esNoticeTenantPanelV21,
    #esNoticeCentralRolePanelV19,
    #esNoticeCentralUserPanelV21 {
        position:static !important;

        width:100% !important;
        max-width:100% !important;
        min-width:0 !important;

        left:auto !important;
        right:auto !important;
        top:auto !important;
        bottom:auto !important;

        margin:6px 0 0 !important;

        transform:none !important;

        box-sizing:border-box !important;

        overflow-x:hidden !important;
        overflow-y:auto !important;
    }


    /*
     * ========================================================
     * SAVED NOTICES
     * ========================================================
     *
     * The card stays fixed to the viewport.
     * ONLY the existing table-scroll element moves horizontally.
     */
    #esDashboardNoticeListV6 {
        display:block !important;

        width:100% !important;
        max-width:100% !important;
        min-width:0 !important;

        margin:0 !important;

        box-sizing:border-box !important;

        overflow:hidden !important;
    }

    #esDashboardNoticeListV6
    .es-notice-table-wrap-v6 {
        display:block !important;

        width:100% !important;
        max-width:100% !important;
        min-width:0 !important;

        margin:0 !important;

        box-sizing:border-box !important;

        overflow:hidden !important;
    }

    #esDashboardNoticeListV6
    .es-notice-table-scroll-v6 {
        display:block !important;

        width:100% !important;
        max-width:100% !important;
        min-width:0 !important;

        overflow-x:auto !important;
        overflow-y:visible !important;

        -webkit-overflow-scrolling:touch;

        overscroll-behavior-x:contain;

        touch-action:pan-x pan-y;

        padding-bottom:10px !important;

        box-sizing:border-box !important;
    }

    #esDashboardNoticeListV6
    .es-notice-table-v6 {
        width:max-content !important;

        min-width:820px !important;
        max-width:none !important;

        table-layout:auto !important;

        margin:0 !important;
    }

    #esDashboardNoticeListV6
    .es-notice-table-v6 th,

    #esDashboardNoticeListV6
    .es-notice-table-v6 td {
        white-space:nowrap !important;
    }


    /*
     * The Notice/title column can wrap so it does not consume
     * unnecessary horizontal space.
     */
    #esDashboardNoticeListV6
    .es-notice-table-v6 th:first-child,

    #esDashboardNoticeListV6
    .es-notice-table-v6 td:first-child {
        min-width:180px !important;
        max-width:220px !important;

        white-space:normal !important;

        overflow-wrap:anywhere !important;
    }


    /*
     * Saved notice pagination/buttons remain inside viewport.
     */
    .es-notice-pagination-v6,
    .es-notice-action-buttons-v6 {
        width:100% !important;
        max-width:100% !important;
        min-width:0 !important;

        margin-left:0 !important;
        margin-right:0 !important;

        box-sizing:border-box !important;
    }
}
</style>

<script>
(function () {

    function enforceRealMobileLayoutV31() {
        if (
            !window.matchMedia(
                '(max-width:767px)'
            ).matches
        ) {
            return;
        }

        const shell =
            document.querySelector(
                '.es-notice-shell-v6'
            );

        if (!shell) {
            return;
        }

        /*
         * Remove inline styles introduced by V28/V30 from the real
         * Dashboard Notice structure. V31 CSS then becomes the
         * authoritative mobile layout.
         */
        [
            shell,
            ...shell.querySelectorAll(
                '.es-notice-card-v6,'
                + '.es-notice-head-v6,'
                + '.es-notice-body-v6,'
                + '#esDashboardNoticeFormV6,'
                + '.es-notice-grid-v6,'
                + '.es-notice-field-v6,'
                + '#esNoticeDeliveryAudienceRowV25,'
                + '#esNoticeDeliveryColumnV25,'
                + '#esNoticeAudienceColumnV25,'
                + '#esDashboardNoticeListV6,'
                + '.es-notice-table-wrap-v6,'
                + '.es-notice-table-scroll-v6'
            )
        ].forEach(function (element) {
            element.style.setProperty(
                'min-width',
                '0',
                'important'
            );

            element.style.setProperty(
                'box-sizing',
                'border-box',
                'important'
            );

            element.style.setProperty(
                'left',
                'auto',
                'important'
            );

            element.style.setProperty(
                'right',
                'auto',
                'important'
            );

            element.style.setProperty(
                'transform',
                'none',
                'important'
            );
        });


        /*
         * Explicitly keep the shell within its real parent width.
         */
        shell.style.setProperty(
            'width',
            '100%',
            'important'
        );

        shell.style.setProperty(
            'max-width',
            '100%',
            'important'
        );

        shell.style.setProperty(
            'margin-left',
            '0',
            'important'
        );

        shell.style.setProperty(
            'margin-right',
            '0',
            'important'
        );


        /*
         * Saved notice horizontal movement belongs to the existing
         * .es-notice-table-scroll-v6 only.
         */
        const tableScroll =
            shell.querySelector(
                '.es-notice-table-scroll-v6'
            );

        if (tableScroll) {
            tableScroll.style.setProperty(
                'overflow-x',
                'auto',
                'important'
            );

            tableScroll.style.setProperty(
                'width',
                '100%',
                'important'
            );

            tableScroll.style.setProperty(
                'max-width',
                '100%',
                'important'
            );
        }
    }


    function bootV31() {
        enforceRealMobileLayoutV31();

        setTimeout(
            enforceRealMobileLayoutV31,
            50
        );

        setTimeout(
            enforceRealMobileLayoutV31,
            250
        );

        window.addEventListener(
            'resize',
            enforceRealMobileLayoutV31
        );

        window.addEventListener(
            'orientationchange',
            function () {
                setTimeout(
                    enforceRealMobileLayoutV31,
                    150
                );
            }
        );

        /*
         * Add/Edit/AJAX pagination can modify the notice DOM.
         */
        document.addEventListener(
            'click',
            function (event) {
                if (
                    event.target.closest(
                        '.es-notice-shell-v6'
                    )
                ) {
                    setTimeout(
                        enforceRealMobileLayoutV31,
                        50
                    );
                }
            },
            true
        );
    }


    if (
        document.readyState === 'loading'
    ) {
        document.addEventListener(
            'DOMContentLoaded',
            bootV31
        );
    } else {
        bootV31();
    }

})();
</script>


{{-- ESUBIZ_NOTICE_MOBILE_CONTROL_RESET_V32 --}}
<style>
@media (max-width:767px) {

    /*
     * ========================================================
     * V32
     *
     * Original V6 already has a correct one-column mobile grid.
     * The later Delivery/Audience components are therefore reset
     * to ordinary block-flow elements on mobile.
     * ========================================================
     */

    .es-notice-body-v6 {
        padding:16px !important;
    }

    #esDashboardNoticeFormV6,
    #esDashboardNoticeFormV6 .es-notice-grid-v6 {
        width:100% !important;
        max-width:100% !important;
        min-width:0 !important;

        margin:0 !important;

        box-sizing:border-box !important;
    }

    #esDashboardNoticeFormV6 .es-notice-grid-v6 {
        display:grid !important;
        grid-template-columns:minmax(0,1fr) !important;
        gap:14px !important;
    }

    /*
     * Every direct grid field occupies one real mobile column.
     */
    #esDashboardNoticeFormV6
    .es-notice-grid-v6 > * {
        grid-column:1 !important;

        width:100% !important;
        max-width:100% !important;
        min-width:0 !important;

        margin-left:0 !important;
        margin-right:0 !important;

        box-sizing:border-box !important;
    }


    /*
     * ========================================================
     * DELIVERY / AUDIENCE
     * ========================================================
     *
     * Kill desktop flex/grid positioning from V25/V26.
     */
    #esNoticeDeliveryAudienceRowV25 {
        display:block !important;

        position:static !important;

        width:100% !important;
        max-width:100% !important;
        min-width:0 !important;

        margin:0 !important;
        padding:0 !important;

        transform:none !important;

        box-sizing:border-box !important;
    }

    #esNoticeDeliveryColumnV25,
    #esNoticeAudienceColumnV25 {
        display:block !important;

        position:static !important;

        width:100% !important;
        max-width:100% !important;
        min-width:0 !important;

        margin:0 0 14px !important;
        padding:0 !important;

        transform:none !important;

        box-sizing:border-box !important;
    }

    #esNoticeAudienceColumnV25:empty {
        display:none !important;
    }


    /*
     * SaaS and Central audience groups.
     */
    [data-es-saas-audience-v25="1"],
    #esNoticeCentralAudienceWrapV19,
    .es-notice-audience-group-v26,
    .es-notice-audience-sub-v26,
    #esNoticeSelectedTenantsWrapV6,
    #esNoticeCentralRolesWrapV19,
    #esNoticeCentralUsersWrapV21 {
        position:static !important;

        float:none !important;

        width:100% !important;
        max-width:100% !important;
        min-width:0 !important;

        margin-left:0 !important;
        margin-right:0 !important;

        padding-left:0 !important;
        padding-right:0 !important;

        transform:none !important;

        box-sizing:border-box !important;
    }


    /*
     * Preserve authoritative V27 visibility.
     */
    #esNoticeCentralAudienceWrapV19[hidden],
    [data-es-saas-audience-v25="1"][hidden],
    #esNoticeSelectedTenantsWrapV6[hidden],
    #esNoticeCentralRolesWrapV19[hidden],
    #esNoticeCentralUsersWrapV21[hidden],
    .es-v27-force-hidden {
        display:none !important;
    }


    /*
     * ========================================================
     * CUSTOM DROPDOWN ROOTS / TRIGGERS
     * ========================================================
     */
    #esNoticeDeliveryMultiV18,
    #esNoticeTenantMultiV21,
    #esNoticeCentralRoleMultiV19,
    #esNoticeCentralUserMultiV21 {
        display:block !important;

        position:relative !important;

        float:none !important;

        width:100% !important;
        max-width:100% !important;
        min-width:0 !important;

        margin-left:0 !important;
        margin-right:0 !important;

        box-sizing:border-box !important;
    }

    #esNoticeDeliveryTriggerV18,
    #esNoticeTenantTriggerV21,
    #esNoticeCentralRoleTriggerV19,
    #esNoticeCentralUserTriggerV21 {
        display:flex !important;

        position:relative !important;

        align-items:center !important;
        justify-content:space-between !important;

        width:100% !important;
        max-width:100% !important;
        min-width:0 !important;

        margin:0 !important;

        left:auto !important;
        right:auto !important;

        transform:none !important;

        box-sizing:border-box !important;

        white-space:normal !important;
    }


    /*
     * ========================================================
     * DROPDOWN PANELS
     * ========================================================
     *
     * On phones the options are part of normal document flow.
     * They cannot extend beyond the card or require page-level
     * horizontal scrolling.
     */
    #esNoticeDeliveryPanelV18,
    #esNoticeTenantPanelV21,
    #esNoticeCentralRolePanelV19,
    #esNoticeCentralUserPanelV21 {
        position:static !important;

        inset:auto !important;

        float:none !important;

        width:100% !important;
        max-width:100% !important;
        min-width:0 !important;

        margin:6px 0 0 !important;

        transform:none !important;

        box-sizing:border-box !important;

        overflow-x:hidden !important;
        overflow-y:auto !important;
    }

    #esNoticeDeliveryPanelV18 label,
    #esNoticeTenantPanelV21 label,
    #esNoticeCentralRolePanelV19 label,
    #esNoticeCentralUserPanelV21 label {
        width:100% !important;
        max-width:100% !important;
        min-width:0 !important;

        box-sizing:border-box !important;

        overflow-wrap:anywhere !important;
    }


    /*
     * Search fields.
     */
    .es-notice-dropdown-search-wrap-v27,
    .es-notice-dropdown-search-v27 {
        width:100% !important;
        max-width:100% !important;
        min-width:0 !important;

        box-sizing:border-box !important;
    }


    /*
     * Inputs inside the form.
     */
    #esDashboardNoticeFormV6
    input:not([type="checkbox"]):not([type="radio"]),
    #esDashboardNoticeFormV6 select,
    #esDashboardNoticeFormV6 textarea {
        width:100% !important;
        max-width:100% !important;
        min-width:0 !important;

        box-sizing:border-box !important;
    }


    /*
     * ========================================================
     * SAVED NOTICES
     * ========================================================
     *
     * Keep the part of V31 that is now confirmed working.
     */
    #esDashboardNoticeListV6 {
        width:100% !important;
        max-width:100% !important;
        min-width:0 !important;

        overflow:hidden !important;
    }

    #esDashboardNoticeListV6
    .es-notice-table-wrap-v6 {
        width:100% !important;
        max-width:100% !important;
        min-width:0 !important;

        overflow:hidden !important;
    }

    #esDashboardNoticeListV6
    .es-notice-table-scroll-v6 {
        display:block !important;

        width:100% !important;
        max-width:100% !important;
        min-width:0 !important;

        overflow-x:auto !important;
        overflow-y:visible !important;

        -webkit-overflow-scrolling:touch;
        touch-action:pan-x pan-y;
    }
}
</style>

<script>
(function () {

    function resetNoticeMobileControlsV32() {
        if (
            !window.matchMedia(
                '(max-width:767px)'
            ).matches
        ) {
            return;
        }

        const form =
            document.getElementById(
                'esDashboardNoticeFormV6'
            );

        if (!form) {
            return;
        }

        /*
         * Remove inline geometry introduced by the older mobile
         * experiments from the form and the later audience widgets.
         */
        const selectors = [
            '#esNoticeDeliveryAudienceRowV25',
            '#esNoticeDeliveryColumnV25',
            '#esNoticeAudienceColumnV25',
            '[data-es-saas-audience-v25="1"]',
            '#esNoticeCentralAudienceWrapV19',
            '#esNoticeSelectedTenantsWrapV6',
            '#esNoticeCentralRolesWrapV19',
            '#esNoticeCentralUsersWrapV21',
            '#esNoticeDeliveryMultiV18',
            '#esNoticeTenantMultiV21',
            '#esNoticeCentralRoleMultiV19',
            '#esNoticeCentralUserMultiV21',
            '#esNoticeDeliveryPanelV18',
            '#esNoticeTenantPanelV21',
            '#esNoticeCentralRolePanelV19',
            '#esNoticeCentralUserPanelV21'
        ];

        selectors.forEach(function (selector) {
            document
                .querySelectorAll(selector)
                .forEach(function (element) {
                    [
                        'width',
                        'max-width',
                        'min-width',
                        'left',
                        'right',
                        'top',
                        'bottom',
                        'transform',
                        'margin-left',
                        'margin-right'
                    ].forEach(function (property) {
                        element.style.removeProperty(
                            property
                        );
                    });
                });
        });


        /*
         * V28/V30 added classes/inline widths to ancestors.
         * Remove those experimental classes from the Dashboard
         * Notice form chain. V32 relies on the original V6 mobile
         * grid instead.
         */
        let node = form;

        for (
            let depth = 0;
            node
            && node !== document.body
            && depth < 12;
            depth++
        ) {
            node.classList.remove(
                'es-dashboard-notice-mobile-shell-v28',
                'es-notice-mobile-workspace-v30',
                'es-notice-mobile-chain-v30'
            );

            [
                'width',
                'max-width',
                'min-width',
                'left',
                'right',
                'transform',
                'margin-left',
                'margin-right'
            ].forEach(function (property) {
                node.style.removeProperty(
                    property
                );
            });

            node = node.parentElement;
        }
    }


    function bootV32() {
        resetNoticeMobileControlsV32();

        setTimeout(
            resetNoticeMobileControlsV32,
            50
        );

        setTimeout(
            resetNoticeMobileControlsV32,
            250
        );

        window.addEventListener(
            'resize',
            resetNoticeMobileControlsV32
        );

        document.addEventListener(
            'click',
            function (event) {
                if (
                    event.target.closest(
                        '.es-notice-shell-v6'
                    )
                ) {
                    setTimeout(
                        resetNoticeMobileControlsV32,
                        30
                    );
                }
            },
            true
        );
    }


    if (
        document.readyState === 'loading'
    ) {
        document.addEventListener(
            'DOMContentLoaded',
            bootV32
        );
    } else {
        bootV32();
    }

})();
</script>


{{-- ESUBIZ_NOTICE_VIEWPORT_ANCHOR_V33 --}}
<style>
@media (max-width:767px) {

    /*
     * V33 is deliberately independent of the Site Settings
     * desktop container width.
     */
    body {
        overflow-x:hidden !important;
    }

    .es-notice-shell-v6 {
        position:relative !important;

        width:calc(100vw - 24px) !important;
        max-width:calc(100vw - 24px) !important;
        min-width:0 !important;

        margin-left:0 !important;
        margin-right:0 !important;

        box-sizing:border-box !important;
    }

    .es-notice-shell-v6 > .es-notice-card-v6 {
        width:100% !important;
        max-width:100% !important;
        min-width:0 !important;

        box-sizing:border-box !important;
    }

    .es-notice-body-v6,
    .es-notice-head-v6 {
        width:100% !important;
        max-width:100% !important;
        min-width:0 !important;

        box-sizing:border-box !important;
    }

    #esDashboardNoticeFormV6,
    #esDashboardNoticeFormV6 .es-notice-grid-v6 {
        width:100% !important;
        max-width:100% !important;
        min-width:0 !important;

        box-sizing:border-box !important;
    }

    #esDashboardNoticeFormV6 .es-notice-grid-v6 {
        display:grid !important;
        grid-template-columns:minmax(0,1fr) !important;
    }

    #esDashboardNoticeFormV6
    .es-notice-grid-v6 > * {
        grid-column:1 / -1 !important;

        width:100% !important;
        max-width:100% !important;
        min-width:0 !important;

        box-sizing:border-box !important;
    }

    /*
     * Preserve the working Saved Notices scroll behavior.
     */
    #esDashboardNoticeListV6
    .es-notice-table-scroll-v6 {
        width:100% !important;
        max-width:100% !important;
        min-width:0 !important;

        overflow-x:auto !important;
        overflow-y:visible !important;

        -webkit-overflow-scrolling:touch;
        touch-action:pan-x pan-y;
    }

    #esDashboardNoticeListV6
    .es-notice-table-v6 {
        min-width:820px !important;
        width:max-content !important;
        max-width:none !important;
    }
}
</style>

<script>
(function () {

    let runningV33 = false;

    function anchorNoticeWorkspaceV33() {
        if (runningV33) {
            return;
        }

        if (
            !window.matchMedia(
                '(max-width:767px)'
            ).matches
        ) {
            return;
        }

        const shell =
            document.querySelector(
                '.es-notice-shell-v6'
            );

        if (!shell) {
            return;
        }

        runningV33 = true;

        requestAnimationFrame(function () {

            const viewportWidth =
                document.documentElement.clientWidth
                || window.innerWidth;

            const gutter = 12;

            const desiredWidth =
                Math.max(
                    280,
                    viewportWidth - (gutter * 2)
                );

            /*
             * First establish the real phone-width workspace.
             */
            shell.style.setProperty(
                'width',
                desiredWidth + 'px',
                'important'
            );

            shell.style.setProperty(
                'max-width',
                desiredWidth + 'px',
                'important'
            );

            shell.style.setProperty(
                'min-width',
                '0',
                'important'
            );

            shell.style.setProperty(
                'margin-left',
                '0',
                'important'
            );

            shell.style.setProperty(
                'margin-right',
                '0',
                'important'
            );

            /*
             * Reset previous translation before measuring.
             */
            shell.style.setProperty(
                'transform',
                'translateX(0px)',
                'important'
            );

            const rect =
                shell.getBoundingClientRect();

            /*
             * This is the key difference from earlier patches:
             *
             * the workspace is moved according to its ACTUAL
             * viewport position, not according to parent width.
             *
             * Even if Site Settings is shifted 100px to the right,
             * the notice shell is pulled back so its left edge is
             * exactly 12px from the physical phone viewport.
             */
            const correction =
                gutter - rect.left;

            shell.style.setProperty(
                'transform',
                'translateX('
                    + correction
                    + 'px)',
                'important'
            );

            /*
             * Force every Add/Edit card descendant to respect
             * the corrected shell width.
             */
            shell.querySelectorAll(
                '.es-notice-card-v6,'
                + '.es-notice-head-v6,'
                + '.es-notice-body-v6,'
                + '#esDashboardNoticeFormV6,'
                + '.es-notice-grid-v6,'
                + '.es-notice-field-v6,'
                + '#esNoticeDeliveryAudienceRowV25,'
                + '#esNoticeDeliveryColumnV25,'
                + '#esNoticeAudienceColumnV25,'
                + '.es-notice-audience-group-v26,'
                + '.es-notice-audience-sub-v26'
            ).forEach(function (element) {

                element.style.setProperty(
                    'max-width',
                    '100%',
                    'important'
                );

                element.style.setProperty(
                    'min-width',
                    '0',
                    'important'
                );

                element.style.setProperty(
                    'box-sizing',
                    'border-box',
                    'important'
                );
            });


            /*
             * Delivery/Audience container becomes true vertical
             * flow regardless of older V25/V26 desktop rules.
             */
            const deliveryAudience =
                document.getElementById(
                    'esNoticeDeliveryAudienceRowV25'
                );

            if (deliveryAudience) {
                deliveryAudience.style.setProperty(
                    'display',
                    'block',
                    'important'
                );

                deliveryAudience.style.setProperty(
                    'width',
                    '100%',
                    'important'
                );

                deliveryAudience.style.setProperty(
                    'max-width',
                    '100%',
                    'important'
                );

                deliveryAudience.style.setProperty(
                    'min-width',
                    '0',
                    'important'
                );
            }


            [
                'esNoticeDeliveryColumnV25',
                'esNoticeAudienceColumnV25'
            ].forEach(function (id) {
                const element =
                    document.getElementById(id);

                if (!element) {
                    return;
                }

                element.style.setProperty(
                    'display',
                    'block',
                    'important'
                );

                element.style.setProperty(
                    'width',
                    '100%',
                    'important'
                );

                element.style.setProperty(
                    'max-width',
                    '100%',
                    'important'
                );

                element.style.setProperty(
                    'min-width',
                    '0',
                    'important'
                );

                element.style.setProperty(
                    'margin-left',
                    '0',
                    'important'
                );

                element.style.setProperty(
                    'margin-right',
                    '0',
                    'important'
                );
            });


            /*
             * Dropdown panels stay inside the corrected workspace.
             */
            [
                'esNoticeDeliveryPanelV18',
                'esNoticeTenantPanelV21',
                'esNoticeCentralRolePanelV19',
                'esNoticeCentralUserPanelV21'
            ].forEach(function (id) {
                const panel =
                    document.getElementById(id);

                if (!panel) {
                    return;
                }

                panel.style.setProperty(
                    'position',
                    'static',
                    'important'
                );

                panel.style.setProperty(
                    'width',
                    '100%',
                    'important'
                );

                panel.style.setProperty(
                    'max-width',
                    '100%',
                    'important'
                );

                panel.style.setProperty(
                    'min-width',
                    '0',
                    'important'
                );

                panel.style.setProperty(
                    'left',
                    'auto',
                    'important'
                );

                panel.style.setProperty(
                    'right',
                    'auto',
                    'important'
                );

                panel.style.setProperty(
                    'transform',
                    'none',
                    'important'
                );

                panel.style.setProperty(
                    'box-sizing',
                    'border-box',
                    'important'
                );
            });

            runningV33 = false;
        });
    }


    function queueV33() {
        anchorNoticeWorkspaceV33();

        setTimeout(
            anchorNoticeWorkspaceV33,
            50
        );

        setTimeout(
            anchorNoticeWorkspaceV33,
            250
        );

        setTimeout(
            anchorNoticeWorkspaceV33,
            600
        );
    }


    if (
        document.readyState === 'loading'
    ) {
        document.addEventListener(
            'DOMContentLoaded',
            queueV33
        );
    } else {
        queueV33();
    }

    window.addEventListener(
        'resize',
        queueV33
    );

    window.addEventListener(
        'orientationchange',
        function () {
            setTimeout(
                queueV33,
                150
            );
        }
    );

    document.addEventListener(
        'click',
        function (event) {
            if (
                event.target.closest(
                    '.es-notice-shell-v6'
                )
            ) {
                setTimeout(
                    anchorNoticeWorkspaceV33,
                    30
                );
            }
        },
        true
    );

})();
</script>


<style>
/* ESUBIZ_NOTICE_LEGACY_DELIVERY_UI_REMOVED_V34 */
/* ESUBIZ_NOTICE_DELIVERY_V34_CORRECTION_V36
 * The V18 Delivery wrapper is authoritative and must remain visible.
 * Only legacy delivery_scope compatibility data stays hidden.
 */
#esNoticeDeliveryScopeV6 {
    display:none !important;
}
</style>

<script>
(function () {

    /*
     * delivery_channels[] is authoritative.
     * delivery_scope remains only as a compatibility value for
     * older backend/data paths.
     */
    function syncLegacyDeliveryScopeV34() {
        const legacy =
            document.getElementById(
                'esNoticeDeliveryScopeV6'
            );

        if (!legacy) {
            return;
        }

        const selected =
            Array.from(
                document.querySelectorAll(
                    'input[name="delivery_channels[]"]:checked'
                )
            ).map(function (input) {
                return input.value;
            });

        const hasSaas =
            selected.includes('saas');

        const hasOffServer =
            selected.includes('off_server');

        /*
         * Legacy scope understands Core delivery only.
         * Central is represented exclusively by delivery_channels.
         */
        if (hasSaas && hasOffServer) {
            legacy.value = 'all';
        } else if (hasSaas) {
            legacy.value = 'saas';
        } else if (hasOffServer) {
            legacy.value = 'off_server';
        } else {
            /*
             * Central-only notices still submit a valid compatibility
             * value; V16 delivery_channels[] remains authoritative.
             */
            legacy.value = 'all';
        }
    }

    document.addEventListener(
        'change',
        function (event) {
            if (
                event.target.matches(
                    'input[name="delivery_channels[]"]'
                )
            ) {
                syncLegacyDeliveryScopeV34();
            }
        }
    );

    document.addEventListener(
        'submit',
        function (event) {
            if (
                event.target
                && event.target.id
                    === 'esDashboardNoticeFormV6'
            ) {
                syncLegacyDeliveryScopeV34();
            }
        },
        true
    );

    if (document.readyState === 'loading') {
        document.addEventListener(
            'DOMContentLoaded',
            syncLegacyDeliveryScopeV34
        );
    } else {
        syncLegacyDeliveryScopeV34();
    }

})();
</script>

@endsection

{{-- ESUBIZ_NOTICE_CENTRAL_ROLE_USER_FILTER_V44 --}}
{{-- ESUBIZ_NOTICE_CENTRAL_ROLE_USER_FILTER_IMPORTANT_V45 --}}
<script>
(function () {
    'use strict';

    function initCentralRoleUserFilterV44() {
        const form =
            document.getElementById(
                'esDashboardNoticeFormV6'
            );

        if (!form) {
            return;
        }

        const roleCheckboxes =
            Array.from(
                form.querySelectorAll(
                    'input[name="central_role_ids[]"]'
                )
            );

        const userCheckboxes =
            Array.from(
                form.querySelectorAll(
                    'input[name="central_user_ids[]"]'
                )
            );

        if (
            !roleCheckboxes.length
            || !userCheckboxes.length
        ) {
            return;
        }

        function filterCentralUsersV44() {
            const selectedRoleIds =
                new Set(
                    roleCheckboxes
                        .filter(function (checkbox) {
                            return checkbox.checked;
                        })
                        .map(function (checkbox) {
                            return String(
                                checkbox.value
                            );
                        })
                );

            userCheckboxes.forEach(
                function (checkbox) {
                    const row =
                        checkbox.closest(
                            'label.es-notice-delivery-option-v18'
                        );

                    if (!row) {
                        return;
                    }

                    /*
                     * No role selected:
                     * preserve existing direct-user targeting.
                     */
                    if (!selectedRoleIds.size) {
                        row.style.removeProperty(
                            'display'
                        );
                        return;
                    }

                    const userRoleIds =
                        String(
                            checkbox.dataset.roleIds
                            || ''
                        )
                            .split(',')
                            .map(function (roleId) {
                                return roleId.trim();
                            })
                            .filter(Boolean);

                    const matches =
                        userRoleIds.some(
                            function (roleId) {
                                return selectedRoleIds.has(
                                    roleId
                                );
                            }
                        );

                    /*
                     * Selected roles show only their real members.
                     * Keep an already-checked explicit user visible
                     * so Edit does not hide an existing selection.
                     */
                    if (
                        matches
                        || checkbox.checked
                    ) {
                        row.style.setProperty(
                            'display',
                            'flex',
                            'important'
                        );
                    } else {
                        row.style.setProperty(
                            'display',
                            'none',
                            'important'
                        );
                    }
                }
            );
        }

        roleCheckboxes.forEach(
            function (checkbox) {
                checkbox.addEventListener(
                    'change',
                    filterCentralUsersV44
                );
            }
        );

        /*
         * ESUBIZ_NOTICE_CENTRAL_ROLE_FILTER_RESYNC_V46
         *
         * Older V25 audience synchronization intentionally restores
         * dropdown rows to display:flex !important. Reapply ONLY the
         * Central role -> Central user filter after those existing
         * UI synchronizations have completed.
         */
        function queueCentralRoleUserFilterV46() {
            window.setTimeout(
                filterCentralUsersV44,
                0
            );

            window.setTimeout(
                filterCentralUsersV44,
                150
            );

            window.setTimeout(
                filterCentralUsersV44,
                350
            );
        }

        roleCheckboxes.forEach(
            function (checkbox) {
                checkbox.addEventListener(
                    'change',
                    queueCentralRoleUserFilterV46
                );
            }
        );

        const centralUserPanel =
            document.getElementById(
                'esNoticeCentralUserPanelV21'
            );

        const centralUserTrigger =
            document.querySelector(
                '#esNoticeCentralUserMultiV21 button'
            );

        if (centralUserTrigger) {
            centralUserTrigger.addEventListener(
                'click',
                queueCentralRoleUserFilterV46
            );
        }

        if (centralUserPanel) {
            centralUserPanel.addEventListener(
                'click',
                function () {
                    window.setTimeout(
                        filterCentralUsersV44,
                        0
                    );
                }
            );
        }

        queueCentralRoleUserFilterV46();
    }

    if (document.readyState === 'loading') {
        document.addEventListener(
            'DOMContentLoaded',
            initCentralRoleUserFilterV44
        );
    } else {
        initCentralRoleUserFilterV44();
    }
})();
</script>

