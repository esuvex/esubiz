@extends('admin.layouts.app')

@section('title', 'User Websites')

@section('content')

<div class="min-h-screen bg-slate-100 px-5 py-8 sm:px-6">

    <div class="mx-auto max-w-7xl">

        {{-- Header --}}
        <div class="mb-8 flex flex-wrap items-end justify-between gap-4">

            <div>

                <div class="text-xs font-black uppercase tracking-widest text-blue-600">
                    Central Website Registry
                </div>

                <h1 class="mt-2 text-3xl font-black text-slate-900">
                    User Websites
                </h1>

                <p class="mt-2 max-w-3xl text-sm leading-6 text-slate-500">
                    View and manage every website registered with Esubiz,
                    including SaaS websites and licensed off-server installations.
                </p>

            </div>

            <div class="rounded-2xl border border-slate-200 bg-white px-5 py-3 shadow-sm">

                <div class="text-xs font-bold uppercase tracking-wide text-slate-400">
                    Total Registered
                </div>

                <div class="mt-1 text-2xl font-black text-slate-900">
                    {{ number_format($websites->total()) }}
                </div>

            </div>

        </div>


        {{-- Filters --}}
        <form
            method="GET"
            action="{{ route('admin.websites.index') }}"
            class="mb-6 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm"
        >

            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">

                <div class="xl:col-span-2">

                    <label class="text-xs font-black uppercase tracking-wide text-slate-500">
                        Search
                    </label>

                    <input
                        type="text"
                        name="search"
                        value="{{ $filters['search'] ?? '' }}"
                        placeholder="Name, domain, UUID, owner..."
                        class="esubiz-admin-input mt-2"
                    >

                </div>


                <div>

                    <label class="text-xs font-black uppercase tracking-wide text-slate-500">
                        Deployment
                    </label>

                    <select
                        name="deployment"
                        class="esubiz-admin-input mt-2"
                    >
                        <option value="">All deployments</option>

                        <option
                            value="saas"
                            @selected(($filters['deployment'] ?? '') === 'saas')
                        >
                            SaaS
                        </option>

                        <option
                            value="off_server"
                            @selected(($filters['deployment'] ?? '') === 'off_server')
                        >
                            Off Server
                        </option>
                    </select>

                </div>


                <div>

                    <label class="text-xs font-black uppercase tracking-wide text-slate-500">
                        Registry
                    </label>

                    <select
                        name="registry_status"
                        class="esubiz-admin-input mt-2"
                    >
                        <option value="">All registry states</option>

                        <option
                            value="active"
                            @selected(($filters['registry_status'] ?? '') === 'active')
                        >
                            Active
                        </option>

                        <option
                            value="suspended"
                            @selected(($filters['registry_status'] ?? '') === 'suspended')
                        >
                            Suspended
                        </option>

                        <option
                            value="revoked"
                            @selected(($filters['registry_status'] ?? '') === 'revoked')
                        >
                            Revoked
                        </option>
                    </select>

                </div>


                <div>

                    <label class="text-xs font-black uppercase tracking-wide text-slate-500">
                        Website Status
                    </label>

                    <input
                        type="text"
                        name="status"
                        value="{{ $filters['status'] ?? '' }}"
                        placeholder="e.g. active"
                        class="esubiz-admin-input mt-2"
                    >

                </div>

            </div>


            <div class="mt-5 flex flex-wrap gap-3">

                <button
                    type="submit"
                    class="esubiz-admin-primary"
                >
                    Apply Filters
                </button>

                <a
                    href="{{ route('admin.websites.index') }}"
                    class="esubiz-admin-secondary"
                >
                    Reset
                </a>

            </div>

        </form>


        {{-- Registry --}}
        <div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">

            <div class="overflow-x-auto">

                <table class="min-w-full divide-y divide-slate-200">

                    <thead class="bg-slate-50">

                        <tr class="text-left text-[11px] font-black uppercase tracking-wider text-slate-500">

                            <th class="px-6 py-4">
                                Website
                            </th>

                            <th class="px-6 py-4">
                                Owner
                            </th>

                            <th class="px-6 py-4">
                                Deployment
                            </th>

                            <th class="px-6 py-4">
                                Registry
                            </th>

                            <th class="px-6 py-4">
                                Plan
                            </th>

                            <th class="px-6 py-4">
                                Status
                            </th>

                            <th class="px-6 py-4 text-right">
                                Action
                            </th>

                        </tr>

                    </thead>


                    <tbody class="divide-y divide-slate-100">

                        @forelse($websites as $website)

                            @php
                                /*
                                 * ADMIN WEBSITE LIST DOMAIN
                                 *
                                 * SaaS websites must always show the
                                 * actual deployed Esubiz subdomain.
                                 *
                                 * registered_domain may still contain
                                 * an old wizard draft-* hostname and
                                 * must never override the live subdomain.
                                 */
                                $registeredDomain =
                                    (
                                        $website->deployment_type === 'saas'
                                        && !empty($website->subdomain)
                                    )
                                        ? strtolower(
                                            trim(
                                                (string) $website->subdomain
                                            )
                                        ) . '.esubiz.com'
                                        : (
                                            !empty($website->domain)
                                                ? preg_replace(
                                                    '#^https?://#',
                                                    '',
                                                    trim(
                                                        (string) $website->domain
                                                    )
                                                )
                                                : (
                                                    !empty($website->registered_domain)
                                                        ? preg_replace(
                                                            '#^https?://#',
                                                            '',
                                                            trim(
                                                                (string) $website->registered_domain
                                                            )
                                                        )
                                                        : null
                                                )
                                        );

                                $deploymentLabel =
                                    $website->deployment_type === 'off_server'
                                        ? 'Off Server'
                                        : 'SaaS';

                                $registryStatus =
                                    $website->registry_status
                                    ?: 'unknown';
                            @endphp

                            <tr class="align-top transition hover:bg-slate-50/80">

                                <td class="px-6 py-5">

                                    <div class="font-black text-slate-900">
                                        {{ $website->name ?: 'Unnamed Website' }}
                                    </div>

                                    <div class="mt-1 text-sm font-medium text-slate-500">
                                        {{ $registeredDomain ?: 'No registered domain' }}
                                    </div>

                                    <div class="mt-2 font-mono text-[11px] text-slate-400">
                                        {{ $website->website_uuid ?: 'ID: ' . $website->id }}
                                    </div>

                                </td>


                                <td class="px-6 py-5">

                                    @if($website->owner)

                                        <div class="font-bold text-slate-800">
                                            {{ $website->owner->name ?: 'User #' . $website->owner_id }}
                                        </div>

                                        <div class="mt-1 text-xs text-slate-500">
                                            {{ $website->owner->email }}
                                        </div>

                                    @else

                                        <span class="text-sm text-slate-400">
                                            No owner
                                        </span>

                                    @endif

                                </td>


                                <td class="px-6 py-5">

                                    <span
                                        class="inline-flex rounded-full px-3 py-1 text-[11px] font-black {{
                                            $website->deployment_type === 'off_server'
                                                ? 'bg-violet-100 text-violet-700'
                                                : 'bg-blue-100 text-blue-700'
                                        }}"
                                    >
                                        {{ $deploymentLabel }}
                                    </span>

                                </td>


                                <td class="px-6 py-5">

                                    <span
                                        class="inline-flex rounded-full px-3 py-1 text-[11px] font-black {{
                                            strtolower((string) $website->status) === 'draft'
                                                ? 'bg-blue-100 text-blue-700'
                                                : (
                                                    $registryStatus === 'active'
                                                        ? 'bg-emerald-100 text-emerald-700'
                                                        : (
                                                            $registryStatus === 'suspended'
                                                                ? 'bg-amber-100 text-amber-700'
                                                                : (
                                                                    $registryStatus === 'revoked'
                                                                        ? 'bg-red-100 text-red-700'
                                                                        : 'bg-slate-100 text-slate-600'
                                                                )
                                                        )
                                                )
                                        }}"
                                    >
                                        {{
                                            strtolower((string) $website->status) === 'draft'
                                                ? 'Draft'
                                                : ucfirst(str_replace('_', ' ', $registryStatus))
                                        }}
                                    </span>

                                </td>


                                <td class="px-6 py-5">

                                    <div class="text-sm font-bold text-slate-700">
                                        {{ $website->plan?->name ?: 'No plan' }}
                                    </div>

                                </td>


                                <td class="px-6 py-5">

                                    <span class="text-sm font-bold text-slate-700">
                                        {{ ucfirst(str_replace('_', ' ', $website->status ?: 'unknown')) }}
                                    </span>

                                </td>


                                <td
                                    class="relative px-6 py-5 text-right"
                                >

                                    {{--
                                        ESUBIZ_ADMIN_WEBSITE_COMPACT_ACTION_MENU_V1
                                    --}}

                                    <div
                                        class="relative inline-block text-left"
                                        data-website-actions
                                    >

                                        <button
                                            type="button"
                                            data-website-actions-trigger
                                            aria-label="Website actions"
                                            aria-expanded="false"
                                            class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-xl font-black leading-none text-slate-600 shadow-sm transition hover:border-blue-300 hover:bg-blue-50 hover:text-blue-700"
                                        >
                                            &#8942;
                                        </button>


                                        <div
                                            data-website-actions-menu
                                            class="absolute right-0 z-50 mt-2 hidden w-56 overflow-hidden rounded-2xl border border-slate-200 bg-white py-2 text-left shadow-xl"
                                        >

                                            <a
                                                href="{{ route('admin.websites.show', $website->id) }}"
                                                class="block px-4 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50"
                                            >
                                                View Website Info
                                            </a>


                                            @unless(
                                                strtolower((string) $website->status) === 'draft'
                                            )

                                            {{--
                                                ESUBIZ_ADMIN_WEBSITE_VISIT_ACTION_V1
                                            --}}

                                            @php
                                                $visitHost =
                                                    trim(
                                                        (string) (
                                                            $website->registered_domain
                                                            ?: (
                                                                !empty($website->subdomain)
                                                                    ? $website->subdomain
                                                                        . '.'
                                                                        . preg_replace(
                                                                            '/^www\./i',
                                                                            '',
                                                                            parse_url(
                                                                                config('app.url'),
                                                                                PHP_URL_HOST
                                                                            )
                                                                                ?: 'esubiz.com'
                                                                        )
                                                                    : ''
                                                            )
                                                        )
                                                    );

                                                $visitUrl =
                                                    $visitHost !== ''
                                                        ? (
                                                            preg_match(
                                                                '#^https?://#i',
                                                                $visitHost
                                                            )
                                                                ? $visitHost
                                                                : 'https://' . $visitHost
                                                        )
                                                        : null;
                                            @endphp

                                            @if($visitUrl)

                                                <a
                                                    href="{{ $visitUrl }}"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="block px-4 py-3 text-sm font-bold text-blue-700 transition hover:bg-blue-50"
                                                >
                                                    Visit Website
                                                </a>

                                            @endif


                                            @if(
                                                $website->deployment_type === 'saas'
                                                && !empty($website->subdomain)
                                            )

                                                <a
                                                    href="{{ route('admin.websites.login', $website->id) }}"
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    class="block px-4 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50"
                                                >
                                                    Manage Website
                                                </a>

                                            @endif


                                            <a
                                                href="{{ route('admin.websites.edit', $website->id) }}"
                                                class="block px-4 py-3 text-sm font-bold text-slate-700 transition hover:bg-slate-50"
                                            >
                                                Edit Website
                                            </a>


                                            <div
                                                class="my-2 border-t border-slate-100"
                                            ></div>


                                            <form
                                                method="POST"
                                                action="{{ route('admin.websites.toggle', $website->id) }}"
                                            >

                                                @csrf
                                                @method('PATCH')

                                                <button
                                                    type="submit"
                                                    class="block w-full px-4 py-3 text-left text-sm font-bold {{
                                                        $website->user_enabled
                                                            ? 'text-amber-700 hover:bg-amber-50'
                                                            : 'text-emerald-700 hover:bg-emerald-50'
                                                    }}"
                                                >
                                                    {{
                                                        $website->user_enabled
                                                            ? 'Disable Website'
                                                            : 'Enable Website'
                                                    }}
                                                </button>

                                            </form>

                                            @endunless


                                            <div
                                                class="my-2 border-t border-slate-100"
                                            ></div>


                                            <form
                                                method="POST"
                                                action="{{ route('admin.websites.destroy', $website->id) }}"
                                                onsubmit="return confirm(
                                                    'PERMANENTLY DELETE {{ addslashes($website->name ?: 'this website') }}?\n\n'
                                                    + 'This cannot be undone. The tenant database and all operational website records will be removed immediately.'
                                                );"
                                            >

                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="block w-full px-4 py-3 text-left text-sm font-black text-red-600 transition hover:bg-red-50"
                                                >
                                                    Delete Website
                                                </button>

                                            </form>

                                        </div>

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="7"
                                    class="px-6 py-16 text-center"
                                >

                                    <div class="text-lg font-black text-slate-700">
                                        No websites found
                                    </div>

                                    <p class="mt-2 text-sm text-slate-500">
                                        No Central website registry records match the selected filters.
                                    </p>

                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>


        {{-- Pagination --}}
        @if($websites->hasPages())

            <div class="mt-6">
                {{ $websites->links() }}
            </div>

        @endif

    </div>

</div>



<script>
document.addEventListener('DOMContentLoaded', function () {

    const wrappers =
        document.querySelectorAll(
            '[data-website-actions]'
        );

    function closeAll(except = null) {

        wrappers.forEach(function (wrapper) {

            if (wrapper === except) {
                return;
            }

            const menu =
                wrapper.querySelector(
                    '[data-website-actions-menu]'
                );

            const trigger =
                wrapper.querySelector(
                    '[data-website-actions-trigger]'
                );

            if (menu) {
                menu.classList.add('hidden');
            }

            if (trigger) {
                trigger.setAttribute(
                    'aria-expanded',
                    'false'
                );
            }

        });

    }


    wrappers.forEach(function (wrapper) {

        const trigger =
            wrapper.querySelector(
                '[data-website-actions-trigger]'
            );

        const menu =
            wrapper.querySelector(
                '[data-website-actions-menu]'
            );

        if (!trigger || !menu) {
            return;
        }

        trigger.addEventListener(
            'click',
            function (event) {

                event.stopPropagation();

                const willOpen =
                    menu.classList.contains(
                        'hidden'
                    );

                closeAll(wrapper);

                menu.classList.toggle(
                    'hidden',
                    !willOpen
                );

                trigger.setAttribute(
                    'aria-expanded',
                    willOpen
                        ? 'true'
                        : 'false'
                );
            }
        );

        menu.addEventListener(
            'click',
            function (event) {
                event.stopPropagation();
            }
        );

    });


    document.addEventListener(
        'click',
        function () {
            closeAll();
        }
    );


    document.addEventListener(
        'keydown',
        function (event) {

            if (event.key === 'Escape') {
                closeAll();
            }

        }
    );

});
</script>

@endsection
