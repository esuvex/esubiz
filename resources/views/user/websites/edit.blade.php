@extends('admin.layouts.app')

@section('title', 'Edit Website')

@section('content')

@php

    /*
     * ESUBIZ_WEBSITE_MANAGEMENT_COMPLETE_UI_V1
     */

    $isCentralAdmin =
        ($managementMode ?? 'owner') === 'admin';

    $updateRoute =
        $isCentralAdmin
            ? route(
                'admin.websites.update',
                $website->id
            )
            : route(
                'user.websites.update',
                $website->id
            );

    $dashboardRoute =
        $isCentralAdmin
            ? (
                Route::has(
                    'admin.websites.login'
                )
                    ? route(
                        'admin.websites.login',
                        $website->id
                    )
                    : null
            )
            : (
                Route::has(
                    'user.websites.dashboard'
                )
                    ? route(
                        'user.websites.dashboard',
                        [
                            'website' =>
                                $website->id,
                        ]
                    )
                    : null
            );

    $backRoute =
        $isCentralAdmin
            ? route(
                'admin.websites.index'
            )
            : route(
                'user.websites.index'
            );

    $websiteAddress = null;

    if (
        $website->deployment_type
            === 'saas'
        && $website->subdomain
    ) {
        $websiteAddress =
            'https://'
            . $website->subdomain
            . '.esubiz.com';

    } elseif (
        $website->registered_domain
    ) {
        $websiteAddress =
            preg_match(
                '#^https?://#i',
                $website->registered_domain
            )
                ? $website->registered_domain
                : 'https://'
                    . $website->registered_domain;

    } elseif (
        $website->domain
    ) {
        $websiteAddress =
            preg_match(
                '#^https?://#i',
                $website->domain
            )
                ? $website->domain
                : 'https://'
                    . $website->domain;
    }

    $allEntitlements =
        collect(
            $entitlements ?? []
        );

    $addons =
        $allEntitlements
            ->filter(
                fn ($item) =>
                    in_array(
                        strtolower(
                            (string)
                            $item->product_type
                        ),
                        [
                            'addon',
                            'add-on',
                            'add_on',
                            'bundle',
                        ],
                        true
                    )
            );

    $themes =
        $allEntitlements
            ->filter(
                fn ($item) =>
                    strtolower(
                        (string)
                        $item->product_type
                    ) === 'theme'
            );

    $modules =
        $allEntitlements
            ->filter(
                fn ($item) =>
                    strtolower(
                        (string)
                        $item->product_type
                    ) === 'module'
            );

    $otherProducts =
        $allEntitlements
            ->reject(
                fn ($item) =>
                    in_array(
                        strtolower(
                            (string)
                            $item->product_type
                        ),
                        [
                            'addon',
                            'add-on',
                            'add_on',
                            'bundle',
                            'theme',
                            'module',
                        ],
                        true
                    )
            );

    $marketplaceBase =
        url('/marketplace');

@endphp


<div
    class="min-h-screen bg-slate-100 px-5 py-8 sm:px-6"
>

    <div class="mx-auto max-w-7xl">


        {{-- ====================================================== --}}
        {{-- HEADER --}}
        {{-- ====================================================== --}}

        <div
            class="mb-8 flex flex-wrap items-start justify-between gap-5"
        >

            <div>

                <a
                    href="{{ $backRoute }}"
                    class="text-sm font-bold text-blue-600 hover:text-blue-700"
                >
                    ← Back to Websites
                </a>

                <div
                    class="mt-5 text-xs font-black uppercase tracking-widest text-blue-600"
                >
                    {{
                        $isCentralAdmin
                            ? 'Central Website Management'
                            : 'Website Management'
                    }}
                </div>

                <h1
                    class="mt-2 text-3xl font-black text-slate-900"
                >
                    {{ $website->name ?: 'Website' }}
                </h1>

                <p
                    class="mt-2 max-w-3xl text-sm leading-6 text-slate-500"
                >
                    {{
                        $isCentralAdmin
                            ? 'Manage this website centrally, review its products and credits, or enter the website dashboard for technical support.'
                            : 'Manage your website information, login credentials, products and credits from one place.'
                    }}
                </p>

            </div>


            <div
                class="flex flex-wrap gap-3"
            >

                @if($websiteAddress)

                    <a
                        href="{{ $websiteAddress }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex min-h-[46px] items-center justify-center rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-black text-slate-700 shadow-sm hover:bg-slate-50"
                    >
                        Visit Website
                    </a>

                @endif


                @if(
                    $dashboardRoute
                    && $website->deployment_type
                        === 'saas'
                )

                    <a
                        href="{{ $dashboardRoute }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex min-h-[46px] items-center justify-center rounded-xl bg-blue-600 px-5 py-3 text-sm font-black text-white shadow-sm hover:bg-blue-700"
                    >
                        Website Dashboard
                    </a>

                @endif

            </div>

        </div>


        {{-- ====================================================== --}}
        {{-- FLASH MESSAGES --}}
        {{-- ====================================================== --}}

        @if(session('success'))

            <div
                class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-800"
            >
                {{ session('success') }}
            </div>

        @endif


        @if($errors->any())

            <div
                class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-5 py-4"
            >

                <div
                    class="font-black text-red-800"
                >
                    Please correct the following:
                </div>

                <ul
                    class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-700"
                >
                    @foreach(
                        $errors->all()
                        as $error
                    )
                        <li>
                            {{ $error }}
                        </li>
                    @endforeach
                </ul>

            </div>

        @endif


        {{-- ====================================================== --}}
        {{-- TOP SUMMARY --}}
        {{-- ====================================================== --}}

        <div
            class="mb-7 grid gap-4 md:grid-cols-2 xl:grid-cols-4"
        >

            <div
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
            >
                <div
                    class="text-xs font-black uppercase tracking-wide text-slate-400"
                >
                    Website Code
                </div>

                <div
                    class="mt-2 break-all font-black text-slate-900"
                >
                    {{ $website->website_code ?: '—' }}
                </div>
            </div>


            <div
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
            >
                <div
                    class="text-xs font-black uppercase tracking-wide text-slate-400"
                >
                    Website Type
                </div>

                <div
                    class="mt-2 font-black text-slate-900"
                >
                    {{
                        ucfirst(
                            str_replace(
                                '_',
                                ' ',
                                $website->type
                                    ?: 'Unknown'
                            )
                        )
                    }}
                </div>
            </div>


            <div
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
            >
                <div
                    class="text-xs font-black uppercase tracking-wide text-slate-400"
                >
                    Plan
                </div>

                <div
                    class="mt-2 font-black text-slate-900"
                >
                    {{
                        $website->plan?->name
                            ?: 'No active plan'
                    }}
                </div>
            </div>


            <div
                class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
            >
                <div
                    class="text-xs font-black uppercase tracking-wide text-slate-400"
                >
                    System Status
                </div>

                <div
                    class="mt-2 font-black text-slate-900"
                >
                    {{
                        ucfirst(
                            str_replace(
                                '_',
                                ' ',
                                $website->status
                                    ?: 'Unknown'
                            )
                        )
                    }}
                </div>
            </div>

        </div>


        <div
            class="grid gap-7 xl:grid-cols-2"
        >


            {{-- ================================================== --}}
            {{-- WEBSITE IDENTITY --}}
            {{-- ================================================== --}}

            <section
                class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm"
            >

                <div
                    class="border-b border-slate-100 pb-5"
                >

                    <div
                        class="text-xs font-black uppercase tracking-widest text-blue-600"
                    >
                        Identity
                    </div>

                    <h2
                        class="mt-2 text-xl font-black text-slate-900"
                    >
                        Website Name
                    </h2>

                    <p
                        class="mt-2 text-sm leading-6 text-slate-500"
                    >
                        Change the public website name without changing its website code or address.
                    </p>

                </div>


                <form
                    method="POST"
                    action="{{ $updateRoute }}"
                    class="mt-6"
                >

                    @csrf

                    @if($isCentralAdmin)
                        @method('PATCH')
                    @else
                        @method('PUT')
                    @endif

                    <input
                        type="hidden"
                        name="section"
                        value="identity"
                    >

                    <label
                        class="text-sm font-black text-slate-700"
                    >
                        Website Name
                    </label>

                    <input
                        type="text"
                        name="name"
                        value="{{ old('name', $website->name) }}"
                        required
                        class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                    >

                    <button
                        type="submit"
                        class="mt-5 inline-flex items-center rounded-xl bg-blue-600 px-5 py-3 text-sm font-black text-white hover:bg-blue-700"
                    >
                        Save Website Name
                    </button>

                </form>

            </section>


            {{-- ================================================== --}}
            {{-- LOGIN CREDENTIALS --}}
            {{-- ================================================== --}}

            <section
                class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm"
            >

                <div
                    class="border-b border-slate-100 pb-5"
                >

                    <div
                        class="text-xs font-black uppercase tracking-widest text-blue-600"
                    >
                        Access
                    </div>

                    <h2
                        class="mt-2 text-xl font-black text-slate-900"
                    >
                        Website Login Credentials
                    </h2>

                    <p
                        class="mt-2 text-sm leading-6 text-slate-500"
                    >
                        Manage the administrator identity used for the website's own login.
                    </p>

                </div>


                <form
                    method="POST"
                    action="{{ $updateRoute }}"
                    class="mt-6 space-y-5"
                >

                    @csrf

                    @if($isCentralAdmin)
                        @method('PATCH')
                    @else
                        @method('PUT')
                    @endif

                    <input
                        type="hidden"
                        name="section"
                        value="credentials"
                    >


                    <div>

                        <label
                            class="text-sm font-black text-slate-700"
                        >
                            Administrator Name
                        </label>

                        <input
                            type="text"
                            name="admin_name"
                            value="{{ old('admin_name', $website->admin_name) }}"
                            class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                        >

                    </div>


                    <div>

                        <label
                            class="text-sm font-black text-slate-700"
                        >
                            Administrator Email
                        </label>

                        <input
                            type="email"
                            name="admin_email"
                            value="{{ old('admin_email', $website->admin_email) }}"
                            required
                            class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                        >

                    </div>


                    <div>

                        <label
                            class="text-sm font-black text-slate-700"
                        >
                            New Password
                        </label>

                        <input
                            type="password"
                            name="admin_password"
                            minlength="8"
                            autocomplete="new-password"
                            placeholder="Leave blank to keep current password"
                            class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                        >

                        <p
                            class="mt-2 text-xs leading-5 text-slate-500"
                        >
                            The existing password is never displayed. Enter a new password only when it should be changed.
                        </p>

                    </div>


                    <button
                        type="submit"
                        class="inline-flex items-center rounded-xl bg-blue-600 px-5 py-3 text-sm font-black text-white hover:bg-blue-700"
                    >
                        Update Login Credentials
                    </button>

                </form>

            </section>


            {{-- ================================================== --}}
            {{-- PLAN --}}
            {{-- ================================================== --}}

            <section
                class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm"
            >

                <div
                    class="border-b border-slate-100 pb-5"
                >

                    <div
                        class="text-xs font-black uppercase tracking-widest text-blue-600"
                    >
                        Subscription
                    </div>

                    <h2
                        class="mt-2 text-xl font-black text-slate-900"
                    >
                        Plan
                    </h2>

                    <p
                        class="mt-2 text-sm leading-6 text-slate-500"
                    >
                        Current website subscription and plan assignment.
                    </p>

                </div>


                @if($isCentralAdmin)

                    <form
                        method="POST"
                        action="{{ $updateRoute }}"
                        class="mt-6"
                    >

                        @csrf
                        @method('PATCH')

                        <input
                            type="hidden"
                            name="section"
                            value="plan"
                        >

                        <label
                            class="text-sm font-black text-slate-700"
                        >
                            Assigned Plan
                        </label>

                        <select
                            name="plan_id"
                            class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                        >

                            <option value="">
                                No Plan
                            </option>

                            @foreach(
                                $plans ?? []
                                as $plan
                            )

                                <option
                                    value="{{ $plan->id }}"
                                    @selected(
                                        (int)
                                        $website->plan_id
                                        ===
                                        (int)
                                        $plan->id
                                    )
                                >
                                    {{ $plan->name }}
                                </option>

                            @endforeach

                        </select>

                        <button
                            type="submit"
                            class="mt-5 inline-flex items-center rounded-xl bg-blue-600 px-5 py-3 text-sm font-black text-white hover:bg-blue-700"
                        >
                            Update Plan
                        </button>

                    </form>

                @else

                    <div
                        class="mt-6 rounded-2xl bg-slate-50 p-5"
                    >

                        <div
                            class="text-xs font-black uppercase tracking-wide text-slate-400"
                        >
                            Current Plan
                        </div>

                        <div
                            class="mt-2 text-lg font-black text-slate-900"
                        >
                            {{
                                $website->plan?->name
                                    ?: 'No active plan'
                            }}
                        </div>

                    </div>

                    <a
                        href="{{ $marketplaceBase }}?website_id={{ $website->id }}&product_type=plan"
                        class="mt-5 inline-flex items-center rounded-xl border border-blue-200 bg-blue-50 px-5 py-3 text-sm font-black text-blue-700 hover:bg-blue-100"
                    >
                        View Plans
                    </a>

                @endif

            </section>


            {{-- ================================================== --}}
            {{-- CREDITS --}}
            {{-- ================================================== --}}

            <section
                class="rounded-3xl border border-slate-200 bg-white p-7 shadow-sm"
            >

                <div
                    class="border-b border-slate-100 pb-5"
                >

                    <div
                        class="text-xs font-black uppercase tracking-widest text-blue-600"
                    >
                        Usage Credits
                    </div>

                    <h2
                        class="mt-2 text-xl font-black text-slate-900"
                    >
                        Credits
                    </h2>

                    <p
                        class="mt-2 text-sm leading-6 text-slate-500"
                    >
                        Central Esubiz balances used by this website.
                    </p>

                </div>


                @if($isCentralAdmin)

                    <form
                        method="POST"
                        action="{{ $updateRoute }}"
                        class="mt-6"
                    >

                        @csrf
                        @method('PATCH')

                        <input
                            type="hidden"
                            name="section"
                            value="credits"
                        >

                        <div
                            class="grid gap-4 sm:grid-cols-2"
                        >

                            @foreach([
                                'ai_credits' =>
                                    'AI Credits',
                                'sms_credits' =>
                                    'SMS Credits',
                                'email_credits' =>
                                    'Email Credits',
                                'whatsapp_credits' =>
                                    'WhatsApp Credits',
                            ] as $field => $label)

                                <div>

                                    <label
                                        class="text-sm font-black text-slate-700"
                                    >
                                        {{ $label }}
                                    </label>

                                    <input
                                        type="number"
                                        name="{{ $field }}"
                                        min="0"
                                        required
                                        value="{{ old($field, (int) $website->{$field}) }}"
                                        class="mt-2 w-full rounded-xl border border-slate-300 bg-white px-4 py-3 text-slate-900 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                                    >

                                </div>

                            @endforeach

                        </div>

                        <button
                            type="submit"
                            class="mt-5 inline-flex items-center rounded-xl bg-blue-600 px-5 py-3 text-sm font-black text-white hover:bg-blue-700"
                        >
                            Update Credit Balances
                        </button>

                    </form>

                @else

                    <div
                        class="mt-6 grid grid-cols-2 gap-4"
                    >

                        @foreach([
                            'AI' =>
                                $website->ai_credits,
                            'SMS' =>
                                $website->sms_credits,
                            'Email' =>
                                $website->email_credits,
                            'WhatsApp' =>
                                $website->whatsapp_credits,
                        ] as $label => $balance)

                            <div
                                class="rounded-2xl bg-slate-50 p-4"
                            >

                                <div
                                    class="text-xs font-black uppercase tracking-wide text-slate-400"
                                >
                                    {{ $label }}
                                </div>

                                <div
                                    class="mt-2 text-xl font-black text-slate-900"
                                >
                                    {{
                                        number_format(
                                            (int)
                                            $balance
                                        )
                                    }}
                                </div>

                            </div>

                        @endforeach

                    </div>

                    <a
                        href="{{ $marketplaceBase }}?website_id={{ $website->id }}&product_type=credits"
                        class="mt-5 inline-flex items-center rounded-xl border border-blue-200 bg-blue-50 px-5 py-3 text-sm font-black text-blue-700 hover:bg-blue-100"
                    >
                        Buy Credits
                    </a>

                @endif

            </section>

        </div>


        {{-- ====================================================== --}}
        {{-- PRODUCTS / ENTITLEMENTS --}}
        {{-- ====================================================== --}}

        <section
            class="mt-7 rounded-3xl border border-slate-200 bg-white p-7 shadow-sm"
        >

            <div
                class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-100 pb-5"
            >

                <div>

                    <div
                        class="text-xs font-black uppercase tracking-widest text-blue-600"
                    >
                        Products
                    </div>

                    <h2
                        class="mt-2 text-xl font-black text-slate-900"
                    >
                        Add-ons, Themes & Modules
                    </h2>

                    <p
                        class="mt-2 text-sm leading-6 text-slate-500"
                    >
                        Products listed here come from the website's central Esubiz entitlement records.
                    </p>

                </div>


                <a
                    href="{{ $marketplaceBase }}?website_id={{ $website->id }}"
                    class="inline-flex items-center rounded-xl border border-blue-200 bg-blue-50 px-5 py-3 text-sm font-black text-blue-700 hover:bg-blue-100"
                >
                    {{
                        $isCentralAdmin
                            ? 'Manage Products'
                            : 'Add Products'
                    }}
                </a>

            </div>


            <div
                class="mt-6 grid gap-6 lg:grid-cols-3"
            >


                {{-- ADD-ONS --}}

                <div
                    class="rounded-2xl border border-slate-200 p-5"
                >

                    <div
                        class="flex items-center justify-between gap-3"
                    >

                        <h3
                            class="font-black text-slate-900"
                        >
                            Add-ons
                        </h3>

                        <span
                            class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-600"
                        >
                            {{ $addons->count() }}
                        </span>

                    </div>


                    <div class="mt-4 space-y-3">

                        @forelse(
                            $addons
                            as $item
                        )

                            <div
                                class="rounded-xl bg-slate-50 p-4"
                            >

                                <div
                                    class="font-bold text-slate-900"
                                >
                                    {{
                                        $item->product_name
                                            ?: 'Add-on'
                                    }}
                                </div>

                                <div
                                    class="mt-1 text-xs font-semibold text-slate-500"
                                >
                                    {{
                                        ucfirst(
                                            $item->status
                                                ?: 'active'
                                        )
                                    }}
                                </div>

                            </div>

                        @empty

                            <div
                                class="rounded-xl bg-slate-50 p-4 text-sm text-slate-500"
                            >
                                No Add-ons assigned.
                            </div>

                        @endforelse

                    </div>


                    <a
                        href="{{ $marketplaceBase }}?website_id={{ $website->id }}&product_type=addon"
                        class="mt-4 inline-flex text-sm font-black text-blue-600 hover:text-blue-700"
                    >
                        Browse Add-ons →
                    </a>

                </div>


                {{-- THEMES --}}

                <div
                    class="rounded-2xl border border-slate-200 p-5"
                >

                    <div
                        class="flex items-center justify-between gap-3"
                    >

                        <h3
                            class="font-black text-slate-900"
                        >
                            Themes
                        </h3>

                        <span
                            class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-600"
                        >
                            {{ $themes->count() }}
                        </span>

                    </div>


                    <div class="mt-4 space-y-3">

                        @forelse(
                            $themes
                            as $item
                        )

                            <div
                                class="rounded-xl bg-slate-50 p-4"
                            >

                                <div
                                    class="font-bold text-slate-900"
                                >
                                    {{
                                        $item->product_name
                                            ?: 'Theme'
                                    }}
                                </div>

                                <div
                                    class="mt-1 text-xs font-semibold text-slate-500"
                                >
                                    {{
                                        ucfirst(
                                            $item->status
                                                ?: 'active'
                                        )
                                    }}
                                </div>

                            </div>

                        @empty

                            <div
                                class="rounded-xl bg-slate-50 p-4 text-sm text-slate-500"
                            >
                                No purchased Theme entitlement.
                            </div>

                        @endforelse

                    </div>


                    <a
                        href="{{ $marketplaceBase }}?website_id={{ $website->id }}&product_type=theme"
                        class="mt-4 inline-flex text-sm font-black text-blue-600 hover:text-blue-700"
                    >
                        Browse Themes →
                    </a>

                </div>


                {{-- MODULES --}}

                <div
                    class="rounded-2xl border border-slate-200 p-5"
                >

                    <div
                        class="flex items-center justify-between gap-3"
                    >

                        <h3
                            class="font-black text-slate-900"
                        >
                            Modules
                        </h3>

                        <span
                            class="rounded-full bg-slate-100 px-3 py-1 text-xs font-black text-slate-600"
                        >
                            {{ $modules->count() }}
                        </span>

                    </div>


                    <div class="mt-4 space-y-3">

                        @forelse(
                            $modules
                            as $item
                        )

                            <div
                                class="rounded-xl bg-slate-50 p-4"
                            >

                                <div
                                    class="font-bold text-slate-900"
                                >
                                    {{
                                        $item->product_name
                                            ?: 'Module'
                                    }}
                                </div>

                                <div
                                    class="mt-1 text-xs font-semibold text-slate-500"
                                >
                                    {{
                                        ucfirst(
                                            $item->status
                                                ?: 'active'
                                        )
                                    }}
                                </div>

                            </div>

                        @empty

                            <div
                                class="rounded-xl bg-slate-50 p-4 text-sm text-slate-500"
                            >
                                No Modules assigned.
                            </div>

                        @endforelse

                    </div>


                    <a
                        href="{{ $marketplaceBase }}?website_id={{ $website->id }}&product_type=module"
                        class="mt-4 inline-flex text-sm font-black text-blue-600 hover:text-blue-700"
                    >
                        Browse Modules →
                    </a>

                </div>

            </div>


            @if($otherProducts->isNotEmpty())

                <div
                    class="mt-6 border-t border-slate-100 pt-6"
                >

                    <h3
                        class="font-black text-slate-900"
                    >
                        Other Website Products
                    </h3>

                    <div
                        class="mt-4 grid gap-3 sm:grid-cols-2 lg:grid-cols-3"
                    >

                        @foreach(
                            $otherProducts
                            as $item
                        )

                            <div
                                class="rounded-xl bg-slate-50 p-4"
                            >

                                <div
                                    class="font-bold text-slate-900"
                                >
                                    {{
                                        $item->product_name
                                            ?: ucfirst(
                                                str_replace(
                                                    '_',
                                                    ' ',
                                                    $item->product_type
                                                )
                                            )
                                    }}
                                </div>

                                <div
                                    class="mt-1 text-xs font-semibold text-slate-500"
                                >
                                    {{
                                        ucfirst(
                                            $item->status
                                                ?: 'active'
                                        )
                                    }}
                                </div>

                            </div>

                        @endforeach

                    </div>

                </div>

            @endif

        </section>


        {{-- ====================================================== --}}
        {{-- WEBSITE INFORMATION --}}
        {{-- ====================================================== --}}

        <section
            class="mt-7 rounded-3xl border border-slate-200 bg-white p-7 shadow-sm"
        >

            <div
                class="border-b border-slate-100 pb-5"
            >

                <div
                    class="text-xs font-black uppercase tracking-widest text-blue-600"
                >
                    Information
                </div>

                <h2
                    class="mt-2 text-xl font-black text-slate-900"
                >
                    Website Details
                </h2>

            </div>


            <div
                class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3"
            >

                @foreach([
                    'Deployment' =>
                        ucfirst(
                            str_replace(
                                '_',
                                ' ',
                                $website->deployment_type
                                    ?: 'saas'
                            )
                        ),

                    'Esubiz Address' =>
                        $website->subdomain
                            ? $website->subdomain
                                . '.esubiz.com'
                            : 'Not assigned',

                    'Registered Domain' =>
                        $website->registered_domain
                            ?: 'Not connected',

                    'Custom Domain' =>
                        $website->domain
                            ?: 'Not connected',

                    'Registry Status' =>
                        ucfirst(
                            str_replace(
                                '_',
                                ' ',
                                $website->registry_status
                                    ?: 'unknown'
                            )
                        ),

                    'Website UUID' =>
                        $website->website_uuid
                            ?: (
                                $website->uuid
                                    ?: '—'
                            ),
                ] as $label => $value)

                    <div
                        class="rounded-2xl bg-slate-50 p-5"
                    >

                        <div
                            class="text-xs font-black uppercase tracking-wide text-slate-400"
                        >
                            {{ $label }}
                        </div>

                        <div
                            class="mt-2 break-all font-bold text-slate-900"
                        >
                            {{ $value }}
                        </div>

                    </div>

                @endforeach

            </div>

        </section>


        {{-- ====================================================== --}}
        {{-- OWNER AVAILABILITY --}}
        {{-- ====================================================== --}}

        @if(!$isCentralAdmin)

            <section
                class="mt-7 rounded-3xl border border-slate-200 bg-white p-7 shadow-sm"
            >

                <div
                    class="flex flex-wrap items-center justify-between gap-5"
                >

                    <div>

                        <div
                            class="text-xs font-black uppercase tracking-widest text-blue-600"
                        >
                            Availability
                        </div>

                        <h2
                            class="mt-2 text-xl font-black text-slate-900"
                        >
                            Website Availability
                        </h2>

                        <p
                            class="mt-2 max-w-2xl text-sm leading-6 text-slate-500"
                        >
                            This controls your own website availability. Central Esubiz enforcement for payments, security, abuse or compliance remains authoritative.
                        </p>

                    </div>


                    <form
                        method="POST"
                        action="{{ $updateRoute }}"
                    >

                        @csrf
                        @method('PUT')

                        <input
                            type="hidden"
                            name="section"
                            value="availability"
                        >

                        <input
                            type="hidden"
                            name="user_enabled"
                            value="{{
                                $website->user_enabled
                                    ? 0
                                    : 1
                            }}"
                        >

                        <button
                            type="submit"
                            class="inline-flex min-h-[46px] items-center rounded-xl px-5 py-3 text-sm font-black {{
                                $website->user_enabled
                                    ? 'border border-red-200 bg-red-50 text-red-700 hover:bg-red-100'
                                    : 'bg-emerald-600 text-white hover:bg-emerald-700'
                            }}"
                        >
                            {{
                                $website->user_enabled
                                    ? 'Disable Website'
                                    : 'Enable Website'
                            }}
                        </button>

                    </form>

                </div>

            </section>

        @endif


    </div>

</div>

@endsection
