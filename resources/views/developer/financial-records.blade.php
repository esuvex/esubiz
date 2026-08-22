@extends('admin.layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Financials</h1>
        <p class="text-sm text-slate-500 mt-1">
            Your marketplace earnings, referral income and Esubiz purchases.
        </p>
    </div>

    <div class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">

        <div class="grid gap-4 lg:grid-cols-7 lg:items-end">

            <form method="GET"
                  action="{{ route('developer.financial.records') }}"
                  class="contents">

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

                <div>
                    <button
                        type="submit"
                        class="w-full rounded-xl bg-blue-600 px-5 py-3 text-sm font-black text-white hover:bg-blue-700">
                        Filter
                    </button>
                </div>

            </form>

            <div class="grid grid-cols-3 gap-2 lg:col-span-3">

                <a
                    href="{{ route('developer.financial.records.csv', request()->query()) }}"
                    class="flex items-center justify-center rounded-xl bg-emerald-600 px-3 py-3 text-center text-sm font-bold text-white hover:bg-emerald-700">
                    CSV
                </a>

                <a
                    href="{{ route('developer.financial.records.pdf', request()->query()) }}"
                    class="flex items-center justify-center rounded-xl bg-red-600 px-3 py-3 text-center text-sm font-bold text-white hover:bg-red-700">
                    PDF
                </a>

                <form method="POST"
                      action="{{ route('developer.financial.records.email') }}">
                    @csrf

                    <input type="hidden" name="from" value="{{ request('from') }}">
                    <input type="hidden" name="to" value="{{ request('to') }}">
                    <input type="hidden" name="type" value="{{ request('type', 'all') }}">

                    <button
                        type="submit"
                        class="flex w-full items-center justify-center rounded-xl bg-slate-900 px-3 py-3 text-center text-sm font-bold text-white hover:bg-slate-800">
                        Email
                    </button>
                </form>

            </div>

        </div>

    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white">
        <table class="min-w-full text-sm">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-5 py-4 text-left">Date</th>
                    <th class="px-5 py-4 text-left">Type</th>
                    <th class="px-5 py-4 text-left">Description</th>
                    <th class="px-5 py-4 text-left">Source</th>
                    <th class="px-5 py-4 text-right">Amount</th>
                    <th class="px-5 py-4 text-left">Status</th>
                    <th class="px-5 py-4 text-left">Reference</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-slate-100">

            @forelse($records as $record)

                @php
                    $isExpense = $record->type === 'expense';
                    $amount = abs((float) $record->amount);
                @endphp

                <tr>
                    <td class="px-5 py-4 whitespace-nowrap">
                        {{ \Carbon\Carbon::parse($record->created_at)->format('d M Y H:i') }}
                    </td>

                    <td class="px-5 py-4">
                        <span class="{{ $isExpense ? 'text-red-600' : 'text-emerald-600' }} font-semibold">
                            {{ ucfirst($record->type) }}
                        </span>
                    </td>

                    <td class="px-5 py-4">
                        {{ $record->description }}
                    </td>

                    <td class="px-5 py-4">
                        {{ $record->source }}
                    </td>

                    <td class="px-5 py-4 text-right font-semibold {{ $isExpense ? 'text-red-600' : 'text-emerald-600' }}">
                        {{ $isExpense ? '-' : '+' }}
                        {{ $record->currency }} {{ number_format($amount, 2) }}
                    </td>

                    <td class="px-5 py-4">
                        {{ ucfirst($record->status) }}
                    </td>

                    <td class="px-5 py-4">
                        {{ $record->reference_id ?? '—' }}
                    </td>
                </tr>

            @empty

                <tr>
                    <td colspan="7" class="px-5 py-12 text-center text-slate-500">
                        No financial records found.
                    </td>
                </tr>

            @endforelse

            </tbody>
        </table>
    </div>

</div>
@endsection
