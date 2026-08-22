@extends('admin.layouts.app')

@section('content')

<div class="min-h-screen bg-slate-50 px-4 py-6 sm:px-6 lg:px-8">

    <div class="mx-auto max-w-7xl">

        <div class="mb-6">
            <div class="text-xs font-black uppercase tracking-widest text-blue-600">
                Billing
            </div>

            <h1 class="mt-2 text-2xl font-black text-slate-900">
                Financials
            </h1>

            <p class="mt-1 text-sm text-slate-500">
                View your purchases, earnings and financial activity.
            </p>
        </div>

        <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <form method="GET" action="{{ route('user.financial.records') }}"
                  class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">

                <div>
                    <label class="text-xs font-bold text-slate-500">
                        From
                    </label>

                    <input
                        type="date"
                        name="from"
                        value="{{ request('from') }}"
                        class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-blue-500"
                    >
                </div>

                <div>
                    <label class="text-xs font-bold text-slate-500">
                        To
                    </label>

                    <input
                        type="date"
                        name="to"
                        value="{{ request('to') }}"
                        class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none focus:border-blue-500"
                    >
                </div>

                <div>
                    <label class="text-xs font-bold text-slate-500">
                        Type
                    </label>

                    <select
                        name="type"
                        class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold outline-none focus:border-blue-500">
                        <option value="all" {{ request('type', 'all') === 'all' ? 'selected' : '' }}>
                            All
                        </option>
                        <option value="revenue" {{ request('type') === 'revenue' ? 'selected' : '' }}>
                            Revenue
                        </option>
                        <option value="expense" {{ request('type') === 'expense' ? 'selected' : '' }}>
                            Expense
                        </option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button
                        type="submit"
                        class="w-full rounded-xl bg-blue-600 px-5 py-3 text-sm font-black text-white hover:bg-blue-700">
                        Filter
                    </button>
                </div>

                <div class="flex items-end gap-2">
                    <a
                        href="{{ route('user.financial.records.csv', request()->query()) }}"
                        class="flex-1 rounded-xl bg-red-600 px-4 py-3 text-center text-sm font-bold text-white hover:bg-red-700">
                        CSV
                    </a>

                    <a
                        href="{{ route('user.financial.records.pdf', request()->query()) }}"
                        class="flex-1 rounded-xl bg-emerald-600 px-4 py-3 text-center text-sm font-bold text-white hover:bg-emerald-700">
                        PDF
                    </a>
                </div>

            </form>

            <form method="POST" action="{{ route('user.financial.records.email') }}" class="mt-4">
                @csrf

                <input type="hidden" name="from" value="{{ request('from') }}">
                <input type="hidden" name="to" value="{{ request('to') }}">
                <input type="hidden" name="type" value="{{ request('type', 'all') }}">

                <button
                    type="submit"
                    class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-black text-white hover:bg-slate-800">
                    Email Financial Statement
                </button>
            </form>
        </div>

        @if(session('success'))
            <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
                {{ session('success') }}
            </div>
        @endif

        <div class="esubiz-card overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">

            <div class="border-b border-slate-200 px-5 py-4">
                <h2 class="font-black text-slate-900">
                    Financial Records
                </h2>
            </div>

            @if($records->count())

                <div class="overflow-x-auto">
                    <table class="w-full text-left">
    <thead class="bg-slate-50">
        <tr>
            <th class="px-5 py-4 text-xs font-black uppercase text-slate-400">Date</th>
            <th class="px-5 py-4 text-xs font-black uppercase text-slate-400">Type</th>
            <th class="px-5 py-4 text-xs font-black uppercase text-slate-400">Description</th>
            <th class="px-5 py-4 text-xs font-black uppercase text-slate-400">Source</th>
            <th class="px-5 py-4 text-right text-xs font-black uppercase text-slate-400">Amount</th>
        </tr>
    </thead>

    <tbody class="divide-y divide-slate-100">
        @forelse($records as $record)
            @php
                $isExpense = ($record->type ?? 'revenue') === 'expense';
                $amount = abs((float) ($record->amount ?? 0));
            @endphp

            <tr class="hover:bg-slate-50">
                <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-600">
                    {{ \Carbon\Carbon::parse($record->created_at)->format('d M Y') }}
                </td>

                <td class="px-5 py-4 text-sm font-black
                    {{ $isExpense ? 'text-red-600' : 'text-emerald-600' }}">
                    {{ $isExpense ? 'Expense' : 'Revenue' }}
                </td>

                <td class="px-5 py-4 text-sm font-semibold text-slate-800">
                    {{ $record->description ?? 'Financial transaction' }}
                </td>

                <td class="px-5 py-4 text-sm font-semibold text-slate-600">
                    {{ $record->source ?? 'Esubiz' }}
                </td>

                <td class="whitespace-nowrap px-5 py-4 text-right text-sm font-black
                    {{ $isExpense ? 'text-red-600' : 'text-emerald-600' }}">
                    {{ $isExpense ? '-' : '+' }}
                    {{ $record->currency ?? 'NGN' }}
                    {{ number_format($amount, 2) }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="px-5 py-10 text-center text-sm text-slate-400">
                    No financial records found.
                </td>
            </tr>
        @endforelse
    </tbody>
</table>
                </div>

                <div class="border-t border-slate-200 px-5 py-4">
                    
                </div>

            @else

                <div class="px-5 py-16 text-center">
                    <div class="text-lg font-black text-slate-900">
                        No financial records yet
                    </div>

                    <p class="mt-2 text-sm text-slate-500">
                        Your purchases and referral earnings will appear here.
                    </p>
                </div>

            @endif

        </div>

    </div>

</div>

@endsection
