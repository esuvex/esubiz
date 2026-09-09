@extends('tenant.admin.layouts.app')

@section('title', 'Investors / Partners')

@section('content')

<div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8"
     data-core-partners-v84>

    <div>
        <h1 class="text-2xl font-black text-slate-900">
            Investors / Partners
        </h1>

        <p class="mt-1 text-sm text-slate-500">
            Manage Core investors, partners and investment configurations.
        </p>
    </div>

    <div class="overflow-x-auto">
        <div class="flex min-w-max gap-2 border-b border-slate-200">

            @coreCan('users.view')
                <a
                    href="/admin/users"
                    class="border-b-2 border-transparent px-4 py-3 text-sm font-bold text-slate-500 hover:text-slate-900"
                >
                    Users
                </a>
            @endcoreCan

            <a
                href="/admin/users/partners"
                class="border-b-2 border-slate-900 px-4 py-3 text-sm font-black text-slate-900"
            >
                Investors / Partners
            </a>

            @coreCan('roles.view')
                <a
                    href="/admin/users/roles"
                    class="border-b-2 border-transparent px-4 py-3 text-sm font-bold text-slate-500 hover:text-slate-900"
                >
                    Roles &amp; Permissions
                </a>
            @endcoreCan

        </div>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

        <div class="overflow-x-auto">

            <table class="min-w-full divide-y divide-slate-200">

                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wide text-slate-500">
                            Name
                        </th>
                        <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wide text-slate-500">
                            Email
                        </th>
                        <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wide text-slate-500">
                            Investment
                        </th>
                        <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wide text-slate-500">
                            Basis
                        </th>
                        <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wide text-slate-500">
                            Status
                        </th>
                        <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wide text-slate-500">
                            Created
                        </th>
                        <th class="px-5 py-4 text-right text-xs font-black uppercase tracking-wide text-slate-500">
                            Actions
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100"
                       data-partners-table-body>

                    @foreach($partners as $partner)
                        @php
                            $createdV84 =
                                !empty($partner->investment_created_at)
                                    ? \Illuminate\Support\Carbon::parse(
                                        $partner->investment_created_at
                                    )->format('d M Y')
                                    : '—';

                            $percentageV84 =
                                $partner->investment_percentage
                                ?? 0;

                            $activeV84 =
                                (bool) (
                                    $partner->investment_is_active
                                    ?? false
                                );
                        @endphp

                        <tr data-partner-row class="hover:bg-slate-50">

                            <td class="whitespace-nowrap px-5 py-4 font-bold text-slate-900">
                                {{ $partner->name }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">
                                {{ $partner->email }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-sm font-bold text-slate-700">
                                {{ number_format((float) $percentageV84, 2) }}%
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">
                                {{ ucfirst($partner->profit_basis ?? 'net') }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4">
                                @if($activeV84)
                                    <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">
                                        Active
                                    </span>
                                @else
                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-600">
                                        Inactive
                                    </span>
                                @endif
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-500">
                                {{ $createdV84 }}
                            </td>

                            <td class="relative whitespace-nowrap px-5 py-4 text-right">

                                <button
                                    type="button"
                                    data-partner-action-toggle
                                    aria-label="Partner actions"
                                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-xl font-black leading-none text-slate-600 hover:bg-slate-50"
                                >
                                    ⋮
                                </button>

                                <div
                                    data-partner-action-menu
                                    class="absolute right-5 z-50 mt-2 hidden w-40 overflow-hidden rounded-xl border border-slate-200 bg-white py-1 text-left shadow-xl"
                                >
                                    @coreCan('users.view')
                                        <a
                                            href="/admin/users/{{ $partner->id }}"
                                            class="block px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                                        >
                                            View
                                        </a>
                                    @endcoreCan

                                    @coreCan('users.edit')
                                        <a
                                            href="/admin/users/{{ $partner->id }}/edit"
                                            class="block px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                                        >
                                            Edit
                                        </a>
                                    @endcoreCan

                                    @coreCan('users.delete')
                                        <form
                                            method="POST"
                                            action="/admin/users/{{ $partner->id }}"
                                            onsubmit="return confirm('Delete this Core user permanently?');"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="block w-full px-4 py-2.5 text-left text-sm font-semibold text-red-600 hover:bg-red-50"
                                            >
                                                Delete
                                            </button>
                                        </form>
                                    @endcoreCan
                                </div>

                            </td>
                        </tr>
                    @endforeach

                </tbody>
            </table>

        </div>

        @if($partners->isEmpty())
            <div class="px-6 py-14 text-center">
                <div class="font-black text-slate-900">
                    No Investors / Partners
                </div>

                <p class="mt-2 text-sm text-slate-500">
                    Assign the Investors / Partners role to a Core user first.
                </p>
            </div>
        @endif

        <div class="flex items-center justify-between border-t border-slate-200 px-5 py-4">

            <div
                data-partners-page-info
                class="text-sm font-semibold text-slate-500"
            ></div>

            <div class="flex gap-2">
                <button
                    type="button"
                    data-partners-prev
                    class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 disabled:cursor-not-allowed disabled:opacity-40"
                >
                    Previous
                </button>

                <button
                    type="button"
                    data-partners-next
                    class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 disabled:cursor-not-allowed disabled:opacity-40"
                >
                    Next
                </button>
            </div>

        </div>

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const root = document.querySelector(
        '[data-core-partners-v84]'
    );

    if (!root) {
        return;
    }

    const rows = Array.from(
        root.querySelectorAll('[data-partner-row]')
    );

    const prev = root.querySelector(
        '[data-partners-prev]'
    );

    const next = root.querySelector(
        '[data-partners-next]'
    );

    const info = root.querySelector(
        '[data-partners-page-info]'
    );

    const perPage = 10;
    let page = 1;

    function render() {
        const totalPages = Math.max(
            1,
            Math.ceil(rows.length / perPage)
        );

        if (page > totalPages) {
            page = totalPages;
        }

        rows.forEach(function (row) {
            row.classList.add('hidden');
        });

        const start = (page - 1) * perPage;

        rows.slice(
            start,
            start + perPage
        ).forEach(function (row) {
            row.classList.remove('hidden');
        });

        info.textContent = rows.length
            ? 'Page ' + page + ' of ' + totalPages
                + ' · ' + rows.length + ' partners'
            : '0 partners';

        prev.disabled = page <= 1;
        next.disabled =
            page >= totalPages
            || rows.length === 0;
    }

    prev.addEventListener('click', function () {
        if (page > 1) {
            page--;
            render();
        }
    });

    next.addEventListener('click', function () {
        const totalPages = Math.ceil(
            rows.length / perPage
        );

        if (page < totalPages) {
            page++;
            render();
        }
    });

    root.addEventListener('click', function (event) {
        const toggle = event.target.closest(
            '[data-partner-action-toggle]'
        );

        const clickedMenu = event.target.closest(
            '[data-partner-action-menu]'
        );

        if (toggle) {
            event.stopPropagation();

            const cell = toggle.closest('td');
            const menu = cell?.querySelector(
                '[data-partner-action-menu]'
            );

            root.querySelectorAll(
                '[data-partner-action-menu]'
            ).forEach(function (other) {
                if (other !== menu) {
                    other.classList.add('hidden');
                }
            });

            menu?.classList.toggle('hidden');
            return;
        }

        if (!clickedMenu) {
            root.querySelectorAll(
                '[data-partner-action-menu]'
            ).forEach(function (menu) {
                menu.classList.add('hidden');
            });
        }
    });

    render();
});
</script>

@endsection
