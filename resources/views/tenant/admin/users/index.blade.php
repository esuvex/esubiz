@extends('tenant.admin.layouts.app')

@section('title', 'Users')

@section('content')

<div class="mx-auto max-w-7xl space-y-6 px-4 py-8 sm:px-6 lg:px-8"
     data-core-users-v84>

    {{-- =====================================================
         HEADER
         ===================================================== --}}
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">

        <div>
            <h1 class="text-2xl font-black text-slate-900">
                Users
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                Manage Core website users, roles and access.
            </p>
        </div>

        @coreCan('users.create')
            <a
                href="/admin/users/create"
                class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-5 py-3 text-sm font-bold text-white hover:bg-slate-800"
            >
                + Create User
            </a>
        @endcoreCan

    </div>

    {{-- =====================================================
         MAIN MANAGEMENT TABS
         Add Role remains on Roles & Permissions page.
         ===================================================== --}}
    <div class="overflow-x-auto">
        <div class="flex min-w-max gap-2 border-b border-slate-200">

            <a
                href="/admin/users"
                class="border-b-2 border-slate-900 px-4 py-3 text-sm font-black text-slate-900"
            >
                Users
            </a>

            @coreCan('partners.manage')
                <a
                    href="/admin/users/partners"
                    class="border-b-2 border-transparent px-4 py-3 text-sm font-bold text-slate-500 hover:text-slate-900"
                >
                    Investors / Partners
                </a>
            @endcoreCan

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

    {{-- =====================================================
         DYNAMIC ROLE FILTERS
         ===================================================== --}}
    <div class="overflow-x-auto">
        <div class="flex min-w-max gap-2"
             data-user-role-filters>

            <button
                type="button"
                data-role-filter="all"
                class="rounded-full bg-slate-900 px-4 py-2 text-xs font-black text-white"
            >
                All
            </button>

            @foreach($roles as $role)
                <button
                    type="button"
                    data-role-filter="{{ $role->id }}"
                    class="rounded-full border border-slate-200 bg-white px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-50"
                >
                    {{ $role->name }}
                </button>
            @endforeach

        </div>
    </div>

    {{-- =====================================================
         USERS TABLE
         ===================================================== --}}
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
                            Phone
                        </th>
                        <th class="px-5 py-4 text-left text-xs font-black uppercase tracking-wide text-slate-500">
                            Role
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

                <tbody
                    class="divide-y divide-slate-100 bg-white"
                    data-users-table-body
                >
                    @foreach($users as $user)
                        @php
                            $roleIdsV84 = $user->roles
                                ->pluck('id')
                                ->map(fn ($id) => (string) $id)
                                ->implode(',');

                            $createdV84 = !empty($user->created_at)
                                ? \Illuminate\Support\Carbon::parse(
                                    $user->created_at
                                )->format('d M Y')
                                : '—';
                        @endphp

                        <tr
                            data-user-row
                            data-role-ids="{{ $roleIdsV84 }}"
                            class="hover:bg-slate-50"
                        >
                            <td class="whitespace-nowrap px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-slate-900">
                                        {{ $user->name }}
                                    </span>

                                    @if((int) $user->id === (int) $currentUserId)
                                        <span class="rounded-full bg-blue-50 px-2 py-1 text-[10px] font-black uppercase text-blue-700">
                                            You
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">
                                {{ $user->email }}
                            </td>

                            <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">
                                {{ $user->phone ?: '—' }}
                            </td>

                            <td class="px-5 py-4">
                                <div class="flex min-w-[150px] flex-wrap gap-1.5">
                                    @forelse($user->roles as $role)
                                        <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-700">
                                            {{ $role->name }}
                                        </span>
                                    @empty
                                        <span class="text-xs text-slate-400">
                                            No role
                                        </span>
                                    @endforelse
                                </div>
                            </td>

                            <td class="whitespace-nowrap px-5 py-4">
                                @if($user->is_active)
                                    <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">
                                        Active
                                    </span>
                                @else
                                    <span class="rounded-full bg-red-50 px-2.5 py-1 text-xs font-bold text-red-700">
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
                                    data-action-toggle
                                    aria-label="User actions"
                                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-200 bg-white text-xl font-black leading-none text-slate-600 hover:bg-slate-50"
                                >
                                    ⋮
                                </button>

                                <div
                                    data-action-menu
                                    class="absolute right-5 z-50 mt-2 hidden w-40 overflow-hidden rounded-xl border border-slate-200 bg-white py-1 text-left shadow-xl"
                                >
                                    @coreCan('users.view')
                                        <a
                                            href="/admin/users/{{ $user->id }}"
                                            class="block px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                                        >
                                            View
                                        </a>
                                    @endcoreCan

                                    @coreCan('users.edit')
                                        <a
                                            href="/admin/users/{{ $user->id }}/edit"
                                            class="block px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50"
                                        >
                                            Edit
                                        </a>
                                    @endcoreCan

                                    @if((int) $user->id !== (int) $currentUserId)
                                        @coreCan('users.delete')
                                            <form
                                                method="POST"
                                                action="/admin/users/{{ $user->id }}"
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
                                    @endif
                                </div>

                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>

        <div
            data-users-empty
            class="hidden px-6 py-14 text-center text-sm font-semibold text-slate-500"
        >
            No users found for this role.
        </div>

        <div class="flex items-center justify-between border-t border-slate-200 px-5 py-4">

            <div
                data-users-page-info
                class="text-sm font-semibold text-slate-500"
            ></div>

            <div class="flex gap-2">
                <button
                    type="button"
                    data-users-prev
                    class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-bold text-slate-700 disabled:cursor-not-allowed disabled:opacity-40"
                >
                    Previous
                </button>

                <button
                    type="button"
                    data-users-next
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
    const root = document.querySelector('[data-core-users-v84]');

    if (!root) {
        return;
    }

    const rows = Array.from(
        root.querySelectorAll('[data-user-row]')
    );

    const filters = Array.from(
        root.querySelectorAll('[data-role-filter]')
    );

    const prev = root.querySelector('[data-users-prev]');
    const next = root.querySelector('[data-users-next]');
    const info = root.querySelector('[data-users-page-info]');
    const empty = root.querySelector('[data-users-empty]');

    const perPage = 10;
    let currentRole = 'all';
    let page = 1;

    function filteredRows() {
        if (currentRole === 'all') {
            return rows;
        }

        return rows.filter(function (row) {
            const roleIds = String(
                row.dataset.roleIds || ''
            )
                .split(',')
                .filter(Boolean);

            return roleIds.includes(currentRole);
        });
    }

    function render() {
        const available = filteredRows();
        const totalPages = Math.max(
            1,
            Math.ceil(available.length / perPage)
        );

        if (page > totalPages) {
            page = totalPages;
        }

        rows.forEach(function (row) {
            row.classList.add('hidden');
        });

        const start = (page - 1) * perPage;
        const visible = available.slice(
            start,
            start + perPage
        );

        visible.forEach(function (row) {
            row.classList.remove('hidden');
        });

        empty.classList.toggle(
            'hidden',
            available.length !== 0
        );

        info.textContent = available.length
            ? 'Page ' + page + ' of ' + totalPages
                + ' · ' + available.length + ' users'
            : '0 users';

        prev.disabled = page <= 1;
        next.disabled =
            page >= totalPages
            || available.length === 0;
    }

    filters.forEach(function (button) {
        button.addEventListener('click', function () {
            currentRole =
                String(button.dataset.roleFilter || 'all');

            page = 1;

            filters.forEach(function (item) {
                item.classList.remove(
                    'bg-slate-900',
                    'text-white'
                );

                item.classList.add(
                    'border',
                    'border-slate-200',
                    'bg-white',
                    'text-slate-600'
                );
            });

            button.classList.remove(
                'border',
                'border-slate-200',
                'bg-white',
                'text-slate-600'
            );

            button.classList.add(
                'bg-slate-900',
                'text-white'
            );

            render();
        });
    });

    prev.addEventListener('click', function () {
        if (page > 1) {
            page--;
            render();
        }
    });

    next.addEventListener('click', function () {
        const available = filteredRows();
        const totalPages = Math.ceil(
            available.length / perPage
        );

        if (page < totalPages) {
            page++;
            render();
        }
    });

    root.addEventListener('click', function (event) {
        const toggle = event.target.closest(
            '[data-action-toggle]'
        );

        const clickedMenu = event.target.closest(
            '[data-action-menu]'
        );

        if (toggle) {
            event.stopPropagation();

            const cell = toggle.closest('td');
            const menu = cell?.querySelector(
                '[data-action-menu]'
            );

            root.querySelectorAll(
                '[data-action-menu]'
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
                '[data-action-menu]'
            ).forEach(function (menu) {
                menu.classList.add('hidden');
            });
        }
    });

    render();
});
</script>

@endsection
