{{-- ESUBIZ_CORE_CANONICAL_HEADER_PARTIAL_V58 --}}
{{--
    SINGLE CORE INTERNAL HEADER SOURCE OF TRUTH

    Used by:
    - Core Admin
    - Staff
    - Investor / Partner
    - User

    Role differences must not create separate headers.
--}}
<header
    class="fixed left-0 right-0 top-0 z-30 h-[72px] border-b border-slate-200 bg-white lg:left-[280px]"
>

    <div
        class="flex h-full items-center justify-between gap-4 px-4 sm:px-6 lg:px-8"
    >

        <div
            class="flex min-w-0 items-center gap-3"
        >

            <button
                type="button"
                id="tenantCmsMenuButton"
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-slate-200 bg-white lg:hidden"
                aria-label="Open navigation"
            >
                ☰
            </button>

                {{-- ESUBIZ_TENANT_HEADER_THEME_LINK_REMOVED_V1 --}}


            {{-- ESUBIZ_UNIFIED_CORE_HEADER_V49 --}}
            <div class="min-w-0">

                <div
                    class="truncate text-lg font-black"
                >
                    @yield(
                        'header_title',
                        $settings['website_name']
                            ?? $website->name
                    )
                </div>

            </div>

        </div>


        <div class="flex shrink-0 items-center gap-2">


            <a
                href="/"
                target="_blank"
                rel="noopener noreferrer"
                class="flex h-10 items-center justify-center rounded-xl bg-blue-600 px-3 text-sm font-bold text-white sm:px-4"
            >
                <span class="hidden sm:inline">
                    View Website
                </span>

                <span class="sm:ml-2">
                    ↗
                </span>
            </a>


            {{-- ESUBIZ_TENANT_PROFILE_MENU_V2 --}}
            @php
                /*
                 * The Core site's local site_users identity
                 * is authoritative for the tenant header.
                 *
                 * Do not use the Central auth()->user()
                 * profile photo for Core avatars.
                 */
                $tenantProfileUser =
                    null;

                $tenantProfileUserId =
                    session()->get(
                        "tenant_cms_sites.{$website->id}.user_id"
                    )
                    ?? session()->get(
                        'tenant_cms_user_id'
                    );

                if ($tenantProfileUserId) {
                    try {
                        $tenantProfileUser =
                            \Illuminate\Support\Facades\DB
                                ::connection('tenant')
                                ->table('site_users')
                                ->where(
                                    'id',
                                    (int)
                                    $tenantProfileUserId
                                )
                                ->first();
                    } catch (\Throwable $profileLookupError) {
                        $tenantProfileUser =
                            null;
                    }
                }

                $tenantProfileName =
                    $tenantProfileUser->name
                    ?? auth()->user()->name
                    ?? 'Account';

                /*
                 * Core-owned avatar must resolve through
                 * the active website host, never APP_URL.
                 */
                $tenantProfilePhoto =
                    !empty(
                        $tenantProfileUser->avatar_path
                        ?? null
                    )
                        ? route(
                            'tenant.cms.settings.profile.avatar',
                            [
                                'subdomain' =>
                                    $website->subdomain,
                            ]
                        )
                        : null;

                $tenantProfileInitial =
                    strtoupper(
                        substr(
                            trim($tenantProfileName),
                            0,
                            1
                        )
                    );

                $tenantProfileUrl = route(
                    'tenant.cms.settings.profile',
                    [
                        'subdomain' =>
                            $website->subdomain,
                    ]
                );

                $esubizSupportUrl =
                    rtrim(config('app.url'), '/');
            @endphp

            <div
                class="relative"
                data-tenant-profile-menu
            >
                <button
                    type="button"
                    class="flex h-10 items-center gap-2 rounded-xl border border-slate-200 bg-white px-2 sm:px-3"
                    data-tenant-profile-button
                    aria-expanded="false"
                    aria-label="Open profile menu"
                >
                    @if($tenantProfilePhoto)

                        <img
                            src="{{ $tenantProfilePhoto }}"
                            alt="{{ $tenantProfileName }}"
                            class="h-7 w-7 rounded-full object-cover"
                        >

                    @else

                        <span
                            class="flex h-7 w-7 items-center justify-center rounded-full bg-blue-600 text-xs font-black text-white"
                        >
                            {{ $tenantProfileInitial ?: 'A' }}
                        </span>

                    @endif

                    <span
                        class="hidden max-w-[130px] truncate text-sm font-bold text-slate-700 sm:block"
                    >
                        {{ $tenantProfileName }}
                    </span>

                    <span
                        class="text-xs text-slate-400"
                    >
                        ▾
                    </span>
                </button>


                <div
                    class="absolute right-0 top-full z-50 mt-2 hidden w-64 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl"
                    data-tenant-profile-dropdown
                >
                    {{-- ESUBIZ_CORE_HEADER_SOURCE_OF_TRUTH_V57 --}}
<a
                        href="{{ app(\App\Services\Core\CorePermissionService::class)->internalLandingTarget() === '/user/dashboard'
        ? url('/user/profile')
        : route('tenant.cms.settings.profile', ['subdomain' => $website->subdomain]) }}"
                        class="flex items-center gap-3 border-b border-slate-100 px-4 py-4 hover:bg-slate-50"
                    >
                        @if($tenantProfilePhoto)

                            <img
                                src="{{ $tenantProfilePhoto }}"
                                alt="{{ $tenantProfileName }}"
                                class="h-10 w-10 rounded-full object-cover"
                            >

                        @else

                            <span
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-blue-600 text-sm font-black text-white"
                            >
                                {{ $tenantProfileInitial ?: 'A' }}
                            </span>

                        @endif

                        <span class="min-w-0">
                            <span
                                class="block truncate text-sm font-black text-slate-900"
                            >
                                {{ $tenantProfileName }}
                            </span>

                            <span
                                class="block text-xs font-semibold text-blue-600"
                            >
                                Profile Settings
                            </span>
                        </span>
                    </a>


                    <div class="py-2">

                        <a
                            href="{{ $esubizSupportUrl }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="flex items-center gap-3 px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50"
                        >
                            <span class="flex w-5 justify-center">
                                ?
                            </span>

                            <span>
                                Esubiz Support
                            </span>
                        </a>



                    </div>


                    <div class="border-t border-slate-100 p-2">

                        <form
                            method="POST"
                            action="{{ route(
                                'tenant.cms.logout',
                                [
                                    'subdomain' =>
                                        $website->subdomain
                                ]
                            ) }}"
                        >
                            @csrf

                            <button
                                type="submit"
                                class="flex w-full items-center gap-3 rounded-xl px-3 py-3 text-left text-sm font-bold text-red-600 hover:bg-red-50"
                            >
                                <span class="flex w-5 justify-center">
                                    ↪
                                </span>

                                <span>
                                    Logout
                                </span>
                            </button>
                        </form>

                    </div>
                </div>
            </div>

            <script>
                /*
                 * ESUBIZ_TENANT_PROFILE_MENU_JS_V1
                 */
                document.addEventListener(
                    'DOMContentLoaded',
                    function () {
                        document
                            .querySelectorAll(
                                '[data-tenant-profile-menu]'
                            )
                            .forEach(function (menu) {
                                const button =
                                    menu.querySelector(
                                        '[data-tenant-profile-button]'
                                    );

                                const dropdown =
                                    menu.querySelector(
                                        '[data-tenant-profile-dropdown]'
                                    );

                                if (!button || !dropdown) {
                                    return;
                                }

                                button.addEventListener(
                                    'click',
                                    function (event) {
                                        event.stopPropagation();

                                        const isHidden =
                                            dropdown.classList.contains(
                                                'hidden'
                                            );

                                        document
                                            .querySelectorAll(
                                                '[data-tenant-profile-dropdown]'
                                            )
                                            .forEach(function (item) {
                                                item.classList.add(
                                                    'hidden'
                                                );
                                            });

                                        if (isHidden) {
                                            dropdown.classList.remove(
                                                'hidden'
                                            );

                                            button.setAttribute(
                                                'aria-expanded',
                                                'true'
                                            );
                                        } else {
                                            button.setAttribute(
                                                'aria-expanded',
                                                'false'
                                            );
                                        }
                                    }
                                );

                                dropdown.addEventListener(
                                    'click',
                                    function (event) {
                                        event.stopPropagation();
                                    }
                                );
                            });

                        document.addEventListener(
                            'click',
                            function () {
                                document
                                    .querySelectorAll(
                                        '[data-tenant-profile-dropdown]'
                                    )
                                    .forEach(function (dropdown) {
                                        dropdown.classList.add(
                                            'hidden'
                                        );
                                    });

                                document
                                    .querySelectorAll(
                                        '[data-tenant-profile-button]'
                                    )
                                    .forEach(function (button) {
                                        button.setAttribute(
                                            'aria-expanded',
                                            'false'
                                        );
                                    });
                            }
                        );
                    }
                );
            </script>

        </div>

    </div>

</header>
