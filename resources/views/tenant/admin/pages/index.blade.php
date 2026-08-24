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

    <script src="https://cdn.tailwindcss.com"></script>

</head>


<body class="min-h-screen bg-slate-100 text-slate-900">


{{-- Mobile overlay --}}

<div
    id="tenantCmsOverlay"
    class="fixed inset-0 z-40 hidden bg-slate-950/50 lg:hidden"
></div>


{{-- Sidebar --}}

<aside
    id="tenantCmsSidebar"
    class="fixed inset-y-0 left-0 z-50 w-[280px] -translate-x-full overflow-y-auto border-r border-white/10 text-white shadow-xl transition-transform duration-300 lg:translate-x-0"
    style="background:linear-gradient(180deg,#0b1739 0%,#10245a 100%);"
>

    <div class="flex justify-end px-4 pt-4 lg:hidden">

        <button
            type="button"
            id="tenantCmsCloseButton"
            class="flex h-10 w-10 items-center justify-center rounded-xl border border-white/10 bg-white/5 text-xl"
        >
            ×
        </button>

    </div>


    <div
        class="border-b border-white/10 px-5 py-5"
    >

        <div class="flex items-center gap-3">

            <div
                class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-600 text-xl font-black"
            >
                {{ strtoupper(
                    substr(
                        $settings['website_name']
                            ?? $website->name,
                        0,
                        1
                    )
                ) }}
            </div>


            <div class="min-w-0">

                <div class="truncate font-black">
                    {{ $settings['website_name']
                        ?? $website->name }}
                </div>

                <div class="mt-1 text-xs text-blue-200">
                    Esubiz Core CMS
                </div>

            </div>

        </div>

    </div>


    <nav class="space-y-1 px-3 py-5">

        <a
            href="{{ route(
                'tenant.cms.dashboard',
                ['subdomain' => $website->subdomain]
            ) }}"
            class="flex items-center gap-3 rounded-xl px-4 py-3 text-sm font-bold text-blue-100 hover:bg-white/10"
        >
            ⌂ Dashboard
        </a>


        <details open class="group">

            <summary
                class="flex cursor-pointer items-center justify-between rounded-xl px-4 py-3 text-sm font-bold text-blue-100 hover:bg-white/10"
            >
                <span>▦ &nbsp; Site Management</span>
                <span>▼</span>
            </summary>


            <div
                class="ml-4 space-y-1 border-l border-white/10 pl-3"
            >

                <a
                    href="{{ route(
                        'tenant.cms.pages.index',
                        ['subdomain' => $website->subdomain]
                    ) }}"
                    class="block rounded-lg bg-blue-600 px-3 py-2.5 text-sm font-bold text-white"
                >
                    Pages
                </a>

                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300">
                    Media
                </a>

                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300">
                    Menus
                </a>

                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300">
                    Forms
                </a>

                <a href="#" class="block rounded-lg px-3 py-2.5 text-sm text-slate-300">
                    Users
                </a>

            </div>

        </details>

    </nav>

</aside>


{{-- Header --}}

<header
    class="fixed left-0 right-0 top-0 z-30 h-[72px] border-b border-slate-200 bg-white lg:left-[280px]"
>

    <div
        class="flex h-full items-center justify-between px-4 sm:px-6 lg:px-8"
    >

        <div class="flex items-center gap-3">

            <button
                type="button"
                id="tenantCmsMenuButton"
                class="flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 lg:hidden"
            >
                ☰
            </button>

            <div>

                <div
                    class="hidden text-[10px] font-black uppercase tracking-[.18em] text-blue-600 sm:block"
                >
                    Esubiz Core CMS
                </div>

                <div class="font-black">
                    {{ $website->name }}
                </div>

            </div>

        </div>


        <a
            href="/"
            target="_blank"
            class="rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-bold text-white"
        >
            View Website ↗
        </a>

    </div>

</header>


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
