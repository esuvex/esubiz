<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Pages - {{ $website->name }}
    </title>


    {{-- ESUBIZ_TENANT_ADMIN_FAVICON_V1 --}}
    @php
        $tenantAdminFavicon =
            $settings['theme.corporate.favicon_path']
                ?? null;
    @endphp

    @if(!empty($tenantAdminFavicon))
        <link
            rel="icon"
            href="{{ request()->getSchemeAndHttpHost()
                . '/media/'
                . implode(
                    '/',
                    array_map(
                        'rawurlencode',
                        explode(
                            '/',
                            ltrim(
                                $tenantAdminFavicon,
                                '/'
                            )
                        )
                    )
                ) }}"
        >
    @endif

    <script src="https://cdn.tailwindcss.com"></script>

</head>


<body class="min-h-screen bg-slate-100 text-slate-900">


{{-- Mobile overlay --}}

<div
    id="tenantCmsOverlay"
    class="fixed inset-0 z-40 hidden bg-slate-950/50 lg:hidden"
></div>


{{-- Sidebar --}}

@include(
    'tenant.admin.partials.sidebar',
    ['website' => $website]
)


{{-- Header --}}

{{-- ESUBIZ_CORE_CANONICAL_HEADER_INCLUDE_V59 --}}
@include('tenant.admin.partials.header')


{{-- Content --}}

<div
    class="min-h-screen pt-[72px] lg:pl-[280px]"
>

    <main class="w-full p-4 sm:p-6 lg:p-8">

        @if(session('success'))

            <div
                class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 font-bold text-emerald-700"
            >
                {{ session('success') }}
            </div>

        @endif


        <div
            class="mb-7 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between"
        >

            <div>

                <h1
                    class="text-3xl font-black lg:text-4xl"
                >
                    Pages
                </h1>

                <p
                    class="mt-2 text-slate-500"
                >
                    Create and manage the pages on your website.
                </p>

            </div>


            <a
                href="{{ route(
                    'tenant.cms.pages.create',
                    ['subdomain' => $website->subdomain]
                ) }}"
                class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-5 py-3 text-sm font-black text-white"
            >
                + Add Page
            </a>

        </div>

        @include('tenant.admin.components.resource-measurement', [
            'resourceKey' => 'pages',
            'label' => 'Pages Usage',
        ])

        <section
            class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm"
        >

            <div
                class="overflow-x-auto"
            >

                <table
                    class="min-w-full"
                >

                    <thead class="bg-slate-50">

                        <tr
                            class="text-left text-xs uppercase tracking-wide text-slate-400"
                        >
                            <th class="px-6 py-4">
                                Page
                            </th>

                            <th class="px-6 py-4">
                                URL
                            </th>

                            <th class="px-6 py-4">
                                Status
                            </th>

                            <th class="px-6 py-4">
                                Type
                            </th>

                            <th class="px-6 py-4 text-right">
                                Actions
                            </th>
                        </tr>

                    </thead>


                    <tbody
                        class="divide-y divide-slate-100"
                    >

                        @forelse($pages as $page)

                            <tr>

                                <td class="px-6 py-5">

                                    <div class="font-black">
                                        {{ $page->title }}
                                    </div>

                                </td>


                                <td
                                    class="px-6 py-5 text-sm text-slate-500"
                                >
                                    {{ $page->is_homepage
                                        ? '/'
                                        : '/' . $page->slug }}
                                </td>


                                <td class="px-6 py-5">

                                    @if($page->status === 'published')

                                        <span
                                            class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700"
                                        >
                                            Published
                                        </span>

                                    @else

                                        <span
                                            class="rounded-full bg-amber-100 px-3 py-1 text-xs font-bold text-amber-700"
                                        >
                                            Draft
                                        </span>

                                    @endif

                                </td>


                                <td class="px-6 py-5">

                                    @if($page->is_homepage)

                                        <span
                                            class="rounded-full bg-blue-100 px-3 py-1 text-xs font-bold text-blue-700"
                                        >
                                            Homepage
                                        </span>

                                    @else

                                        <span
                                            class="text-sm text-slate-400"
                                        >
                                            Standard
                                        </span>

                                    @endif

                                </td>


                                <td
                                    class="px-6 py-5"
                                >

                                    <div
                                        class="flex justify-end gap-2"
                                    >

                                        <a
                                            href="{{ $page->is_homepage
                                                ? '/'
                                                : '/' . $page->slug }}"
                                            target="_blank"
                                            class="rounded-lg border border-slate-200 px-3 py-2 text-xs font-bold"
                                        >
                                            View
                                        </a>


                                        <a
                                            href="{{ route(
                                                'tenant.cms.pages.edit',
                                                [
                                                    'subdomain' =>
                                                        $website->subdomain,
                                                    'page' =>
                                                        $page->id,
                                                ]
                                            ) }}"
                                            class="rounded-lg bg-blue-50 px-3 py-2 text-xs font-bold text-blue-700"
                                        >
                                            Edit
                                        </a>


                                        @if(!$page->is_homepage)

                                            <form
                                                method="POST"
                                                action="{{ route(
                                                    'tenant.cms.pages.destroy',
                                                    [
                                                        'subdomain' =>
                                                            $website->subdomain,
                                                        'page' =>
                                                            $page->id,
                                                    ]
                                                ) }}"
                                                onsubmit="return confirm('Delete this page?');"
                                            >

                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="rounded-lg bg-red-50 px-3 py-2 text-xs font-bold text-red-600"
                                                >
                                                    Delete
                                                </button>

                                            </form>

                                        @endif

                                    </div>

                                </td>

                            </tr>

                        @empty

                            <tr>

                                <td
                                    colspan="5"
                                    class="px-6 py-12 text-center text-slate-500"
                                >
                                    No pages have been created yet.
                                </td>

                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </section>

    </main>

</div>


<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const sidebar =
            document.getElementById(
                'tenantCmsSidebar'
            );

        const overlay =
            document.getElementById(
                'tenantCmsOverlay'
            );

        const open =
            document.getElementById(
                'tenantCmsMenuButton'
            );

        const close =
            document.getElementById(
                'tenantCmsCloseButton'
            );


        const closeMenu = function () {

            sidebar.classList.add(
                '-translate-x-full'
            );

            overlay.classList.add(
                'hidden'
            );

        };


        if (open) {
            open.addEventListener(
                'click',
                function () {

                    sidebar.classList.remove(
                        '-translate-x-full'
                    );

                    overlay.classList.remove(
                        'hidden'
                    );

                }
            );
        }


        if (close) {
            close.addEventListener(
                'click',
                closeMenu
            );
        }


        if (overlay) {
            overlay.addEventListener(
                'click',
                closeMenu
            );
        }

    }
);

</script>

</body>
</html>
