@extends('admin.layouts.app')

@section('title', 'Developer Dashboard')

@section('content')

<div class="mb-8">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm font-semibold uppercase tracking-wider text-blue-600">
                Developer Workspace
            </p>

            <h1 class="mt-1 text-3xl font-bold tracking-tight text-slate-900">
                Developer Dashboard
            </h1>

            <p class="mt-2 text-slate-500">
                Welcome back, {{ auth()->user()->name }}. Manage your websites and track your developer earnings.
            </p>
        </div>

    </div>
</div>

{{-- Statistics --}}
<div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-4">

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <p class="text-sm font-medium text-slate-500">Websites</p>
        <p class="mt-3 text-3xl font-bold text-slate-900">
            {{ number_format($websiteCount) }}
        </p>
        <p class="mt-2 text-xs text-slate-400">Websites assigned to you</p>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <p class="text-sm font-medium text-slate-500">Total Earnings</p>
        <p class="mt-3 text-3xl font-bold text-slate-900">
            ₦{{ number_format($totalEarnings, 2) }}
        </p>
        <p class="mt-2 text-xs text-slate-400">Developer commissions</p>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <p class="text-sm font-medium text-slate-500">Pending Earnings</p>
        <p class="mt-3 text-3xl font-bold text-amber-600">
            ₦{{ number_format($pendingEarnings, 2) }}
        </p>
        <p class="mt-2 text-xs text-slate-400">Awaiting approval</p>
    </div>

    <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <p class="text-sm font-medium text-slate-500">Paid Earnings</p>
        <p class="mt-3 text-3xl font-bold text-emerald-600">
            ₦{{ number_format($paidEarnings, 2) }}
        </p>
        <p class="mt-2 text-xs text-slate-400">Successfully paid</p>
    </div>

</div>

{{-- Main workspace --}}
<div class="mt-8 grid grid-cols-1 gap-6 xl:grid-cols-3">

    <div class="xl:col-span-2 rounded-2xl border border-slate-200 bg-white shadow-sm">

        <div class="border-b border-slate-100 p-6">
            <h2 class="text-lg font-bold text-slate-900">
                Developer Tools
            </h2>
            <p class="mt-1 text-sm text-slate-500">
                Tools and resources available in your developer workspace.
            </p>
        </div>

        <div class="grid grid-cols-1 gap-4 p-6 sm:grid-cols-2">

            <a href="{{ route('websites.create') }}"
               class="rounded-xl border border-slate-200 p-5 transition hover:border-blue-300 hover:bg-blue-50/30">
                <p class="font-semibold text-slate-900">My Websites</p>
                <p class="mt-1 text-sm text-slate-500">
                    Manage websites associated with your developer account.
                </p>
            </a>

            <div class="rounded-xl border border-slate-200 p-5">
                <p class="font-semibold text-slate-900">Website Compiler</p>
                <p class="mt-1 text-sm text-slate-500">
                    Build and prepare websites for deployment.
                </p>
            </div>

            <div class="rounded-xl border border-slate-200 p-5">
                <p class="font-semibold text-slate-900">Themes & Modules</p>
                <p class="mt-1 text-sm text-slate-500">
                    Developer resources for extending websites.
                </p>
            </div>

            <div class="rounded-xl border border-slate-200 p-5">
                <p class="font-semibold text-slate-900">API Tools</p>
                <p class="mt-1 text-sm text-slate-500">
                    API infrastructure will appear here when the developer API system is enabled.
                </p>
            </div>

        </div>

    </div>

    {{-- Earnings summary --}}
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm">

        <div class="border-b border-slate-100 p-6">
            <h2 class="text-lg font-bold text-slate-900">
                Earnings
            </h2>
            <p class="mt-1 text-sm text-slate-500">
                Developer commission summary.
            </p>
        </div>

        <div class="space-y-5 p-6">

            <div class="flex items-center justify-between">
                <span class="text-sm text-slate-500">Total</span>
                <span class="font-semibold text-slate-900">
                    ₦{{ number_format($totalEarnings, 2) }}
                </span>
            </div>

            <div class="flex items-center justify-between">
                <span class="text-sm text-slate-500">Pending</span>
                <span class="font-semibold text-amber-600">
                    ₦{{ number_format($pendingEarnings, 2) }}
                </span>
            </div>

            <div class="flex items-center justify-between">
                <span class="text-sm text-slate-500">Approved</span>
                <span class="font-semibold text-blue-600">
                    ₦{{ number_format($approvedEarnings, 2) }}
                </span>
            </div>

            <div class="flex items-center justify-between">
                <span class="text-sm text-slate-500">Paid</span>
                <span class="font-semibold text-emerald-600">
                    ₦{{ number_format($paidEarnings, 2) }}
                </span>
            </div>

        </div>

    </div>

</div>

{{-- Recent activity --}}
<div class="mt-8 rounded-2xl border border-slate-200 bg-white shadow-sm">

    <div class="border-b border-slate-100 p-6">
        <h2 class="text-lg font-bold text-slate-900">
            Recent Developer Earnings
        </h2>
        <p class="mt-1 text-sm text-slate-500">
            Your latest developer commission activity.
        </p>
    </div>

    @if($recentCommissions->isEmpty())

        <div class="p-8 text-center">
            <p class="font-medium text-slate-700">No developer earnings yet.</p>
            <p class="mt-1 text-sm text-slate-400">
                Your commission activity will appear here when available.
            </p>
        </div>

    @else

        <div class="divide-y divide-slate-100">

            @foreach($recentCommissions as $commission)

                <div class="flex flex-col gap-3 p-5 sm:flex-row sm:items-center sm:justify-between">

                    <div>
                        <p class="font-medium text-slate-800">
                            Developer commission
                        </p>

                        <p class="mt-1 text-xs text-slate-400">
                            {{ $commission->created_at }}
                        </p>
                    </div>

                    <div class="flex items-center gap-4">

                        <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold capitalize text-slate-600">
                            {{ $commission->status }}
                        </span>

                        <span class="font-bold text-slate-900">
                            ₦{{ number_format($commission->commission_amount, 2) }}
                        </span>

                    </div>

                </div>

            @endforeach

        </div>

    @endif

</div>

@endsection
