{{-- ESUBIZ_CORE_USER_PROFILE_MENU_PARTIAL_V1 --}}

@php
    $profileMenuName =
        trim(
            (string) (
                $coreUser->name
                ?? ''
            )
        ) ?: 'User';

    $profileMenuEmail =
        trim(
            (string) (
                $coreUser->email
                ?? ''
            )
        );

    $profileMenuInitial =
        strtoupper(
            substr(
                $profileMenuName,
                0,
                1
            )
        );
@endphp

<div
    class="core-user-profile"
    data-core-user-profile
>
    <button
        type="button"
        class="core-user-profile-trigger"
        aria-expanded="false"
        data-core-user-profile-toggle
    >
        <span class="core-user-profile-avatar">
            {{ $profileMenuInitial ?: 'U' }}
        </span>

        <span class="core-user-profile-meta">
            <span class="core-user-profile-name">
                {{ $profileMenuName }}
            </span>

            <span class="core-user-profile-email">
                {{ $profileMenuEmail }}
            </span>
        </span>

        <span aria-hidden="true">
            ▾
        </span>
    </button>

    <div class="core-user-profile-menu">

        <div class="profile-menu-summary">
            <strong>
                {{ $profileMenuName }}
            </strong>

            <span>
                {{ $profileMenuEmail }}
            </span>
        </div>

        <a
            href="/user/profile"
            class="profile-menu-item"
        >
            Profile Settings
        </a>

        <form
            method="POST"
            action="/admin/logout"
        >
            @csrf

            <button
                type="submit"
                class="
                    profile-menu-item
                    logout
                "
            >
                Logout
            </button>
        </form>

    </div>
</div>
