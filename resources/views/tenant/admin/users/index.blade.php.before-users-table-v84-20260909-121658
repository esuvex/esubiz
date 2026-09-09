@extends('tenant.admin.layouts.app')

@section('content')

{{-- ESUBIZ_CORE_PARTNER_ADMIN_CONFIG_LINK_V1 --}}
@coreCan('partners.manage')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-6">
        <div class="flex justify-end">
            <a
                href="/admin/users/partners"
                class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700"
            >
                Partners / Investors
            </a>
        </div>
    </div>
@endcoreCan


<div class="mx-auto max-w-7xl space-y-6">

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-black text-slate-900">
                Users
            </h1>

<div style="display:flex;gap:10px;flex-wrap:wrap;margin:14px 0 20px;">
    <a
        href="/admin/users"
        style="display:inline-flex;align-items:center;padding:9px 14px;border:1px solid #d1d5db;border-radius:8px;text-decoration:none;color:#111827;background:#fff;"
    >
        Users
    </a>

    @coreCan('roles.view')
<a
        href="/admin/users/roles"
        style="display:inline-flex;align-items:center;padding:9px 14px;border:1px solid #111827;border-radius:8px;text-decoration:none;color:#fff;background:#111827;"
    >
        Roles &amp; Permissions
    </a>
@endcoreCan
</div>


            <p class="mt-1 text-sm text-slate-500">
                Manage Core website users and their roles.
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

    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

        @if($users->isEmpty())
            <div class="px-6 py-16 text-center">
                <div class="text-lg font-black text-slate-900">
                    No Core users yet
                </div>
            </div>
        @else
            <div class="divide-y divide-slate-100">

                @foreach($users as $user)
                    <div class="flex flex-col gap-4 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">

                        <div class="min-w-0">

                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="font-black text-slate-900">
                                    {{ $user->name }}
                                </h2>

                                @if((int) $user->id === (int) $currentUserId)
                                    <span class="rounded-full bg-blue-50 px-2.5 py-1 text-[11px] font-black uppercase tracking-wide text-blue-700">
                                        You
                                    </span>
                                @endif

                                @if(!$user->is_active)
                                    <span class="rounded-full bg-red-50 px-2.5 py-1 text-[11px] font-black uppercase tracking-wide text-red-700">
                                        Inactive
                                    </span>
                                @endif
                            </div>

                            <div class="mt-1 text-sm text-slate-500">
                                {{ $user->email }}

                                @if($user->phone)
                                    · {{ $user->phone }}
                                @endif
                            </div>

                            <div class="mt-2 flex flex-wrap gap-2">
                                @forelse($user->roles as $role)
                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs font-bold text-slate-700">
                                        {{ $role->name }}
                                    </span>
                                @empty
                                    <span class="text-xs font-semibold text-slate-400">
                                        No role assigned
                                    </span>
                                @endforelse
                            </div>

                        </div>

                        <div class="flex shrink-0 items-center gap-2">

                            @coreCan('users.edit')
<a
                                href="/admin/users/{{ $user->id }}/edit"
                                class="rounded-lg border border-slate-200 px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50"
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
                                        class="rounded-lg border border-red-200 px-4 py-2 text-sm font-bold text-red-700 hover:bg-red-50"
                                    >
                                        Delete
                                    </button>
                                </form>
@endcoreCan
                            @endif

                        </div>

                    </div>
                @endforeach

            </div>
        @endif

    </div>
</div>
@endsection
