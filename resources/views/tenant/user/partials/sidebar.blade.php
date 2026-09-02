{{--
|--------------------------------------------------------------------------
| ESUBIZ_CORE_USER_SIDEBAR_PLUGIN_V1
|--------------------------------------------------------------------------
|
| Universal Core frontend/user sidebar.
|
| User-facing CRM, module, add-on, bundle, product and site
| functions register through CoreUserNavigationRegistry.
|
| Registered-but-unavailable features never render.
|
--}}

@php
    $coreUserNavigationGroups = app(
        \App\Services\Core\CoreUserNavigationRegistry::class
    )->grouped();

    $coreUserName = trim(
        (string) ($coreUser->name ?? '')
    ) ?: 'User';

    $coreUserInitial = strtoupper(
        substr($coreUserName, 0, 1)
    );

    $currentPath = '/' . ltrim(
        request()->path(),
        '/'
    );
@endphp

<aside
    class="core-user-sidebar"
    style="
        background:
            linear-gradient(
                180deg,
                #0b1739 0%,
                #10245a 100%
            );
    "
>
    <div class="cus-brand">

        <div class="cus-brand-mark">
            {{ $coreUserInitial }}
        </div>

        <div class="cus-brand-copy">

            <div class="cus-brand-title">
                {{ $coreUserName }}
            </div>

            <div class="cus-brand-subtitle">
                Website Account
            </div>

        </div>

    </div>


    @foreach($coreUserNavigationGroups as $section => $items)

        <div class="cus-nav-label">
            {{ $section }}
        </div>

        <nav class="cus-nav">

            @foreach($items as $item)

                @php
                    $itemUrl = (string) (
                        $item['url'] ?? '/'
                    );

                    $isActive =
                        $itemUrl !== '/'
                        && (
                            $currentPath === $itemUrl
                            || str_starts_with(
                                $currentPath,
                                rtrim(
                                    $itemUrl,
                                    '/'
                                ) . '/'
                            )
                        );

                    if (
                        $itemUrl === '/'
                        && $currentPath === '/'
                    ) {
                        $isActive = true;
                    }

                    $icon = trim(
                        (string) ($item['icon'] ?? '')
                    );

                    if ($icon === '') {
                        $icon = strtoupper(
                            substr(
                                (string) (
                                    $item['label'] ?? 'U'
                                ),
                                0,
                                1
                            )
                        );
                    }
                @endphp

                <a
                    href="{{ $itemUrl }}"
                    class="
                        cus-link
                        {{ $isActive ? 'active' : '' }}
                    "
                >
                    <span class="cus-icon">
                        {{ $icon }}
                    </span>

                    {{ $item['label'] }}
                </a>

            @endforeach

        </nav>

    @endforeach


    <div class="cus-footer">

        <form
            method="POST"
            action="/admin/logout"
        >
            @csrf

            <button
                type="submit"
                class="cus-link cus-logout"
            >
                <span class="cus-icon">
                    L
                </span>

                Logout
            </button>

        </form>

    </div>

</aside>
