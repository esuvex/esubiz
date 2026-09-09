<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Profile Settings</title>

    <style>
        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            min-height: 100%;
            background: #f5f7fb;
            color: #101828;
            font-family:
                Inter,
                ui-sans-serif,
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                sans-serif;
        }

        body {
            min-height: 100vh;
        }

        .core-user-shell {
            display: grid;
            grid-template-columns: 250px minmax(0, 1fr);
            min-height: 100vh;
        }

        .core-user-sidebar {
            position: sticky;
            top: 0;
            height: 100vh;
            padding: 24px 18px;
            overflow-y: auto;
            color: #fff;
        }

        .cus-brand {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 6px 8px 22px;
            border-bottom:
                1px solid rgba(255,255,255,.08);
        }

        .cus-brand-mark {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border-radius: 11px;
            background: rgba(255,255,255,.1);
            font-size: 15px;
            font-weight: 800;
        }

        .cus-brand-copy {
            min-width: 0;
        }

        .cus-brand-title {
            overflow: hidden;
            color: #fff;
            font-size: 13px;
            font-weight: 800;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .cus-brand-subtitle {
            margin-top: 2px;
            color: #98a2b3;
            font-size: 10px;
        }

        .cus-nav-label {
            margin: 24px 10px 9px;
            color: #8291b7;
            font-size: 9px;
            font-weight: 800;
            letter-spacing: .09em;
            text-transform: uppercase;
        }

        .cus-nav {
            display: grid;
            gap: 5px;
        }

        .cus-link {
            display: flex;
            align-items: center;
            gap: 11px;
            min-height: 44px;
            width: 100%;
            padding: 0 12px;
            border: 0;
            border-radius: 11px;
            background: transparent;
            color: #d0d5dd;
            text-decoration: none;
            text-align: left;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
        }

        .cus-link:hover,
        .cus-link.active {
            background: rgba(255,255,255,.10);
            color: #fff;
        }

        .cus-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 25px;
            height: 25px;
            border-radius: 8px;
            background: rgba(255,255,255,.08);
            font-size: 11px;
            font-weight: 800;
        }

        .cus-footer {
            margin-top: 28px;
            padding-top: 18px;
            border-top:
                1px solid rgba(255,255,255,.08);
        }

        .cus-logout {
            width: 100%;
        }

        .core-user-content {
            min-width: 0;
        }

        .core-user-topbar,
        .core-user-mobile-header {
            min-height: 68px;
            padding: 10px 24px;
            border-bottom: 1px solid #eaecf0;
            background: #fff;
        }

        .core-user-topbar {
            display: flex;
            align-items: center;
            justify-content: flex-end;
        }

        .core-user-mobile-header {
            display: none;
        }

        .core-user-profile {
            position: relative;
        }

        .core-user-profile-trigger {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            min-height: 42px;
            padding: 4px 8px 4px 5px;
            border: 1px solid #eaecf0;
            border-radius: 12px;
            background: #fff;
            color: #101828;
            cursor: pointer;
        }

        .core-user-profile-avatar {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background:
                linear-gradient(
                    180deg,
                    #0b1739 0%,
                    #10245a 100%
                );
            color: #fff;
            font-size: 12px;
            font-weight: 800;
        }

        .core-user-profile-meta {
            text-align: left;
        }

        .core-user-profile-name,
        .core-user-profile-email {
            display: block;
            max-width: 170px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .core-user-profile-name {
            font-size: 11px;
            font-weight: 800;
        }

        .core-user-profile-email {
            margin-top: 1px;
            color: #667085;
            font-size: 9px;
        }

        .core-user-profile-menu {
            position: absolute;
            top: calc(100% + 8px);
            right: 0;
            z-index: 1300;
            display: none;
            width: 210px;
            padding: 7px;
            border: 1px solid #eaecf0;
            border-radius: 13px;
            background: #fff;
            box-shadow:
                0 18px 45px rgba(16,24,40,.15);
        }

        .core-user-profile.open
        .core-user-profile-menu {
            display: block;
        }

        .profile-menu-summary {
            padding: 9px 10px;
            margin-bottom: 5px;
            border-bottom: 1px solid #f2f4f7;
        }

        .profile-menu-summary strong,
        .profile-menu-summary span {
            display: block;
        }

        .profile-menu-summary strong {
            font-size: 11px;
        }

        .profile-menu-summary span {
            margin-top: 2px;
            color: #667085;
            font-size: 9px;
            overflow-wrap: anywhere;
        }

        .profile-menu-item {
            display: flex;
            align-items: center;
            width: 100%;
            min-height: 38px;
            padding: 0 10px;
            border: 0;
            border-radius: 8px;
            background: transparent;
            color: #344054;
            text-decoration: none;
            font-size: 10px;
            font-weight: 700;
            cursor: pointer;
            text-align: left;
        }

        .profile-menu-item:hover {
            background: #f9fafb;
        }

        .profile-menu-item.logout {
            color: #b42318;
        }

        .profile-page {
            width: min(
                1120px,
                calc(100% - 32px)
            );
            margin: 0 auto;
            padding: 34px 0 60px;
        }

        .page-heading {
            margin-bottom: 24px;
        }

        .page-heading h1 {
            margin: 0;
            font-size: 26px;
        }

        .page-heading p {
            margin: 7px 0 0;
            color: #667085;
            font-size: 13px;
        }

        .notice {
            margin-bottom: 18px;
            padding: 13px 15px;
            border-radius: 11px;
            font-size: 12px;
        }

        .notice.success {
            border: 1px solid #abefc6;
            background: #ecfdf3;
            color: #067647;
        }

        .notice.error {
            border: 1px solid #fecdca;
            background: #fef3f2;
            color: #b42318;
        }

        .profile-grid {
            display: grid;
            grid-template-columns:
                repeat(2, minmax(0, 1fr));
            gap: 22px;
        }

        .profile-card {
            overflow: hidden;
            border: 1px solid #eaecf0;
            border-radius: 16px;
            background: #fff;
            box-shadow:
                0 5px 20px rgba(16,24,40,.04);
        }

        .card-head {
            padding: 20px 22px;
            border-bottom: 1px solid #f2f4f7;
        }

        .card-head h2 {
            margin: 0;
            font-size: 16px;
        }

        .card-head p {
            margin: 5px 0 0;
            color: #667085;
            font-size: 11px;
        }

        .card-body {
            display: grid;
            gap: 18px;
            padding: 22px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            color: #344054;
            font-size: 11px;
            font-weight: 700;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="file"] {
            display: block;
            width: 100%;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            min-height: 43px;
            padding: 0 12px;
            border: 1px solid #d0d5dd;
            border-radius: 10px;
            background: #fff;
            color: #101828;
            outline: none;
        }

        input:focus {
            border-color: #667eea;
            box-shadow:
                0 0 0 3px rgba(102,126,234,.12);
        }

        input:disabled {
            background: #f9fafb;
            color: #667085;
        }

        .field-help {
            margin: 6px 0 0;
            color: #667085;
            font-size: 10px;
        }

        .avatar-row {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .avatar-preview,
        .avatar-fallback {
            width: 76px;
            height: 76px;
            flex: 0 0 76px;
            border-radius: 50%;
        }

        .avatar-preview {
            object-fit: cover;
            border: 1px solid #eaecf0;
        }

        .avatar-fallback {
            display: flex;
            align-items: center;
            justify-content: center;
            background:
                linear-gradient(
                    180deg,
                    #0b1739 0%,
                    #10245a 100%
                );
            color: #fff;
            font-size: 24px;
            font-weight: 800;
        }

        .hidden {
            display: none !important;
        }

        .button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            padding: 0 17px;
            border: 0;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 700;
        }

        .button-primary {
            background: #101828;
            color: #fff;
        }

        .button-danger {
            margin-top: 9px;
            border: 1px solid #fecdca;
            background: #fff;
            color: #b42318;
        }

        .core-user-sidebar-overlay {
            display: none;
        }

        .core-user-menu-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            border: 1px solid #d0d5dd;
            border-radius: 11px;
            background: #fff;
            cursor: pointer;
            font-size: 20px;
        }

        @media (max-width: 900px) {
            .core-user-shell {
                display: block;
            }

            .core-user-sidebar {
                position: fixed;
                top: 0;
                left: 0;
                z-index: 1200;
                width: 270px;
                height: 100vh;
                transform: translateX(-100%);
                transition: transform .22s ease;
                box-shadow:
                    14px 0 35px rgba(16,24,40,.18);
            }

            body.core-user-sidebar-open
            .core-user-sidebar {
                transform: translateX(0);
            }

            .core-user-sidebar-overlay {
                position: fixed;
                inset: 0;
                z-index: 1190;
                background: rgba(16,24,40,.48);
            }

            body.core-user-sidebar-open
            .core-user-sidebar-overlay {
                display: block;
            }

            .core-user-topbar {
                display: none;
            }

            .core-user-mobile-header {
                position: sticky;
                top: 0;
                z-index: 1100;
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 10px 16px;
            }

            .profile-grid {
                grid-template-columns: 1fr;
            }

            .profile-page {
                width: min(
                    100% - 24px,
                    1120px
                );
                padding-top: 22px;
            }
        }
    

        /*
         * ESUBIZ_CORE_USER_HEADER_RESPONSIVE_FIX_V1
         *
         * Desktop:
         *   profile navigation only, top-right.
         *
         * Mobile:
         *   hamburger top-left,
         *   profile navigation top-right.
         */

        @media (min-width: 901px) {
            .core-user-topbar {
                display: flex !important;
                align-items: center;
                justify-content: flex-end;
            }

            .core-user-mobile-header {
                display: none !important;
            }
        }

        @media (max-width: 900px) {
            .core-user-topbar {
                display: none !important;
            }

            .core-user-mobile-header {
                display: flex !important;
                align-items: center;
                justify-content: space-between;
            }

            .core-user-mobile-header
            .core-user-profile-meta {
                display: none;
            }
        }

</style>
</head>

<body>

<div
    class="core-user-sidebar-overlay"
    data-core-user-sidebar-close
></div>

<div class="core-user-shell">

    @include(
        'tenant.user.partials.sidebar',
        ['coreUser' => $profileUser]
    )

    <main class="core-user-content">

        <div class="core-user-topbar">
            @include(
                'tenant.user.partials.profile-menu',
                ['coreUser' => $profileUser]
            )
        </div>

        <div class="core-user-mobile-header">

            <button
                type="button"
                class="core-user-menu-button"
                aria-label="Open menu"
                aria-expanded="false"
                data-core-user-sidebar-toggle
            >
                ☰
            </button>

            @include(
                'tenant.user.partials.profile-menu',
                ['coreUser' => $profileUser]
            )

        </div>

        <div class="profile-page">

            <div class="page-heading">
                <h1>Profile Settings</h1>
                <p>
                    Manage your personal Core account.
                </p>
            </div>

            @if(session('success'))
                <div class="notice success">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('password_success'))
                <div class="notice success">
                    {{ session('password_success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="notice error">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @php
                $profileAvatarUrl =
                    !empty($profileUser->avatar_path)
                        ? '/user/profile/avatar'
                        : null;

                $profileInitial =
                    strtoupper(
                        substr(
                            trim(
                                (string) (
                                    $profileUser->name
                                    ?? 'U'
                                )
                            ),
                            0,
                            1
                        )
                    );
            @endphp

            <div class="profile-grid">

                <section class="profile-card">

                    <div class="card-head">
                        <h2>Profile Information</h2>
                        <p>
                            Manage your personal website
                            account details.
                        </p>
                    </div>

                    <form
                        method="POST"
                        enctype="multipart/form-data"
                        action="/user/profile"
                        class="card-body"
                    >
                        @csrf

                        <div>
                            <label>Profile Avatar</label>

                            <div class="avatar-row">

                                @if($profileAvatarUrl)
                                    <img
                                        src="{{ $profileAvatarUrl }}"
                                        alt="Profile avatar"
                                        class="avatar-preview"
                                        data-profile-avatar-preview
                                    >
                                @else
                                    <span
                                        class="avatar-fallback"
                                        data-profile-avatar-fallback
                                    >
                                        {{ $profileInitial ?: 'U' }}
                                    </span>

                                    <img
                                        src=""
                                        alt="Profile avatar preview"
                                        class="
                                            hidden
                                            avatar-preview
                                        "
                                        data-profile-avatar-preview
                                    >
                                @endif

                                <div>
                                    <input
                                        id="profile-avatar"
                                        type="file"
                                        name="avatar"
                                        accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                        data-profile-avatar-input
                                    >

                                    <p class="field-help">
                                        JPG, PNG or WebP.
                                        Maximum 3 MB.
                                    </p>

                                    @if($profileAvatarUrl)
                                        <button
                                            type="submit"
                                            form="delete-profile-avatar-form"
                                            class="
                                                button
                                                button-danger
                                            "
                                            onclick="
                                                return confirm(
                                                    'Delete your profile photo?'
                                                );
                                            "
                                        >
                                            Delete Photo
                                        </button>
                                    @endif
                                </div>

                            </div>
                        </div>

                        <div>
                            <label for="profile-name">
                                Name
                            </label>

                            <input
                                id="profile-name"
                                type="text"
                                name="name"
                                value="{{
                                    old(
                                        'name',
                                        $profileUser->name
                                        ?? ''
                                    )
                                }}"
                                required
                            >
                        </div>

                        <div>
                            <label for="profile-email">
                                Email Address
                            </label>

                            @if($isSso)
                                <input
                                    id="profile-email"
                                    type="email"
                                    value="{{
                                        $profileUser->email
                                        ?? ''
                                    }}"
                                    disabled
                                >

                                <p class="field-help">
                                    Your email is managed by
                                    your SSO identity provider.
                                </p>
                            @else
                                <input
                                    id="profile-email"
                                    type="email"
                                    name="email"
                                    value="{{
                                        old(
                                            'email',
                                            $profileUser->email
                                            ?? ''
                                        )
                                    }}"
                                    required
                                >
                            @endif
                        </div>

                        <div>
                            <label for="profile-phone">
                                Phone Number
                            </label>

                            <input
                                id="profile-phone"
                                type="text"
                                name="phone"
                                value="{{
                                    old(
                                        'phone',
                                        $profileUser->phone
                                        ?? ''
                                    )
                                }}"
                            >
                        </div>

                        <div>
                            <button
                                type="submit"
                                class="
                                    button
                                    button-primary
                                "
                            >
                                Save Profile
                            </button>
                        </div>

                    </form>

                    @if($profileAvatarUrl)
                        <form
                            id="delete-profile-avatar-form"
                            method="POST"
                            action="/user/profile/avatar"
                            class="hidden"
                        >
                            @csrf
                            @method('DELETE')
                        </form>
                    @endif

                </section>


                <section class="profile-card">

                    <div class="card-head">
                        <h2>Password</h2>

                        <p>
                            @if($isSso)
                                Create or replace the local
                                password for this website account.
                            @else
                                Change the password for your
                                website account.
                            @endif
                        </p>
                    </div>

                    <form
                        method="POST"
                        action="/user/profile/password"
                        class="card-body"
                    >
                        @csrf

                        @unless($isSso)
                            <div>
                                <label for="current-password">
                                    Current Password
                                </label>

                                <input
                                    id="current-password"
                                    type="password"
                                    name="current_password"
                                    autocomplete="current-password"
                                    required
                                >
                            </div>
                        @endunless

                        <div>
                            <label for="new-password">
                                New Password
                            </label>

                            <input
                                id="new-password"
                                type="password"
                                name="password"
                                autocomplete="new-password"
                                required
                            >
                        </div>

                        <div>
                            <label for="password-confirmation">
                                Confirm New Password
                            </label>

                            <input
                                id="password-confirmation"
                                type="password"
                                name="password_confirmation"
                                autocomplete="new-password"
                                required
                            >
                        </div>

                        <div>
                            <button
                                type="submit"
                                class="
                                    button
                                    button-primary
                                "
                            >
                                Update Password
                            </button>
                        </div>

                    </form>

                </section>

            </div>

        </div>

    </main>

</div>

<script>
document.addEventListener(
    'DOMContentLoaded',
    function () {
        const body = document.body;

        const sidebarToggle =
            document.querySelector(
                '[data-core-user-sidebar-toggle]'
            );

        const sidebarOverlay =
            document.querySelector(
                '[data-core-user-sidebar-close]'
            );

        const closeSidebar = function () {
            body.classList.remove(
                'core-user-sidebar-open'
            );

            if (sidebarToggle) {
                sidebarToggle.setAttribute(
                    'aria-expanded',
                    'false'
                );
            }
        };

        if (sidebarToggle) {
            sidebarToggle.addEventListener(
                'click',
                function () {
                    const open =
                        body.classList.toggle(
                            'core-user-sidebar-open'
                        );

                    sidebarToggle.setAttribute(
                        'aria-expanded',
                        open ? 'true' : 'false'
                    );
                }
            );
        }

        if (sidebarOverlay) {
            sidebarOverlay.addEventListener(
                'click',
                closeSidebar
            );
        }

        const profiles =
            document.querySelectorAll(
                '[data-core-user-profile]'
            );

        const closeProfiles = function () {
            profiles.forEach(function (profile) {
                profile.classList.remove('open');

                const trigger =
                    profile.querySelector(
                        '[data-core-user-profile-toggle]'
                    );

                if (trigger) {
                    trigger.setAttribute(
                        'aria-expanded',
                        'false'
                    );
                }
            });
        };

        profiles.forEach(function (profile) {
            const trigger =
                profile.querySelector(
                    '[data-core-user-profile-toggle]'
                );

            if (!trigger) {
                return;
            }

            trigger.addEventListener(
                'click',
                function (event) {
                    event.stopPropagation();

                    const wasOpen =
                        profile.classList.contains(
                            'open'
                        );

                    closeProfiles();

                    if (!wasOpen) {
                        profile.classList.add('open');

                        trigger.setAttribute(
                            'aria-expanded',
                            'true'
                        );
                    }
                }
            );

            const menu =
                profile.querySelector(
                    '.core-user-profile-menu'
                );

            if (menu) {
                menu.addEventListener(
                    'click',
                    function (event) {
                        event.stopPropagation();
                    }
                );
            }
        });

        document.addEventListener(
            'click',
            closeProfiles
        );

        const avatarInput =
            document.querySelector(
                '[data-profile-avatar-input]'
            );

        if (avatarInput) {
            avatarInput.addEventListener(
                'change',
                function () {
                    const file =
                        this.files
                        && this.files[0];

                    if (!file) {
                        return;
                    }

                    const preview =
                        document.querySelector(
                            '[data-profile-avatar-preview]'
                        );

                    const fallback =
                        document.querySelector(
                            '[data-profile-avatar-fallback]'
                        );

                    if (!preview) {
                        return;
                    }

                    const reader =
                        new FileReader();

                    reader.onload =
                        function (event) {
                            preview.src =
                                event.target.result;

                            preview.classList.remove(
                                'hidden'
                            );

                            if (fallback) {
                                fallback.classList.add(
                                    'hidden'
                                );
                            }
                        };

                    reader.readAsDataURL(file);
                }
            );
        }
    }
);
</script>

</body>
</html>
