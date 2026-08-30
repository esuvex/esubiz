
<style>
.financial-summary-card {
    background: #0f172a !important;
    color: #ffffff !important;
}
.financial-summary-card * {
    color: #ffffff !important;
}
</style>
@extends('admin.layouts.app')

@section('content')

<div class="max-w-7xl mx-auto px-6 py-8">

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">

        <div>
            <h1 class="text-3xl font-bold text-slate-900">
                Financial Reports
            </h1>

            <p class="mt-2 text-slate-500">
                Complete Esubiz platform income and expense records.
            </p>
        </div>

        <a
            href="{{ route('admin.dashboard') }}"
            class="rounded-xl bg-slate-900 px-5 py-3 text-sm font-semibold text-white"
        >
            Back to Dashboard
        </a>

    </div>

    {{-- SUMMARY --}}

    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-8">

        <div class="financial-summary-card rounded-3xl bg-emerald-500 p-6 text-white shadow-lg">
            <p class="text-sm text-white/80">Total Income</p>

            <p class="mt-3 text-3xl font-bold">
                ₦{{ number_format($income, 2) }}
            </p>

            <p class="mt-2 text-sm text-white/80">
                Platform credits
            </p>
        </div>

        <div class="financial-summary-card rounded-3xl bg-red-500 p-6 text-white shadow-lg">
            <p class="text-sm text-white/80">Total Expenses</p>

            <p class="mt-3 text-3xl font-bold">
                ₦{{ number_format($expenses, 2) }}
            </p>

            <p class="mt-2 text-sm text-white/80">
                Platform debits
            </p>
        </div>

        <div class="rounded-3xl bg-slate-900 p-6 text-white shadow-lg">
            <p class="text-sm text-white/70">Net</p>

            <p class="mt-3 text-3xl font-bold">
                ₦{{ number_format($net, 2) }}
            </p>

            <p class="mt-2 text-sm text-white/70">
                Income minus expenses
            </p>
        </div>

    </div>

    {{-- FILTERS + EXPORTS --}}

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">

        <form
            method="GET"
            class="lg:col-span-2 rounded-3xl bg-white border border-slate-200 p-6 shadow-sm"
        >

            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">

                <div class="md:col-span-2 lg:col-span-2">
                    <label class="text-xs font-semibold text-slate-500">
                        Period
                    </label>

                    <select
                        name="period"
                        class="mt-1 w-full rounded-xl border-slate-300"
                    >
                        <option value="7d" @selected($period === '7d')>7 days</option>
                        <option value="30d" @selected($period === '30d')>30 days</option>
                        <option value="3m" @selected($period === '3m')>3 months</option>
                        <option value="6m" @selected($period === '6m')>6 months</option>
                        <option value="12m" @selected($period === '12m')>12 months</option>
                        <option value="custom" @selected($period === 'custom')>Custom Date</option>
                    </select>

                    <div
                        id="custom-date-range"
                        class="mt-3 grid grid-cols-2 gap-3 {{ $period === 'custom' ? '' : 'hidden' }}"
                    >
                        <div>
                            <label class="text-xs font-semibold text-slate-500">
                                From
                            </label>

                            <input
                                type="date"
                                name="from"
                                value="{{ $customFrom }}"
                                class="mt-1 w-full rounded-xl border-slate-300"
                            >
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-slate-500">
                                To
                            </label>

                            <input
                                type="date"
                                name="to"
                                value="{{ $customTo }}"
                                class="mt-1 w-full rounded-xl border-slate-300"
                            >
                        </div>
                    </div>
                </div>

                <div>
                    <label class="text-xs font-semibold text-slate-500">
                        Type
                    </label>

                    <select name="type" class="mt-1 w-full rounded-xl border-slate-300">
                        <option value="all" @selected($type === 'all')>Credit + Debit</option>
                        <option value="credit" @selected($type === 'credit')>Credit / Income</option>
                        <option value="debit" @selected($type === 'debit')>Debit / Expense</option>
                    </select>
                </div>

                <div>
                    <label class="text-xs font-semibold text-slate-500">
                        Source
                    </label>

                    <select name="source" class="mt-1 w-full rounded-xl border-slate-300">
                        <option value="">All sources</option>

                        @foreach($sources as $item)
                            <option value="{{ $item }}" @selected($source === $item)>
                                {{ ucwords(str_replace('_', ' ', $item)) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-xs font-semibold text-slate-500">
                        Status
                    </label>

                    <select name="status" class="mt-1 w-full rounded-xl border-slate-300">
                        <option value="">All statuses</option>
                        <option value="processed" @selected($status === 'processed')>Processed</option>
                        <option value="pending" @selected($status === 'pending')>Pending</option>
                        <option value="paid" @selected($status === 'paid')>Paid</option>
                        <option value="cancelled" @selected($status === 'cancelled')>Cancelled</option>
                    </select>
                </div>

                <div>
                    <label class="text-xs font-semibold text-slate-500">
                        Currency
                    </label>

                    <select
                        name="currency"
                        class="mt-1 w-full rounded-xl border-slate-300"
                    >
                        <option value="">All currencies</option>
                        @foreach($enabledCurrencies ?? [] as $enabledCurrency)
                            <option
                                value="{{ $enabledCurrency }}"
                                @selected($currency === $enabledCurrency)
                            >
                                {{ $enabledCurrency }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end">
                    <button
                        type="submit"
                        class="w-full rounded-xl bg-blue-600 px-5 py-2.5 font-semibold text-white"
                    >
                        Apply Filters
                    </button>
                </div>

            </div>

        </form>

        {{-- DOWNLOAD / EMAIL --}}

        <div class="rounded-3xl bg-white border border-slate-200 p-6 shadow-sm">

            <h2 class="text-lg font-bold text-slate-900">
                Reports
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Download or email the current financial report.
            </p>

            <div class="mt-5 flex flex-col gap-3">

                <a
                    href="{{ route('admin.financial-reports.csv', request()->query()) }}"
                    class="w-full text-center rounded-xl bg-emerald-600 px-4 py-3 text-sm font-semibold text-white"
                >
                    Download CSV
                </a>

                <a
                    href="{{ route('admin.financial-reports.pdf', request()->query()) }}"
                    class="w-full text-center rounded-xl bg-red-600 px-4 py-3 text-sm font-semibold text-white"
                >
                    Download PDF
                </a>

                <form
                    method="POST"
                    action="{{ route('admin.financial-reports.email') }}"
                    class="flex flex-col gap-2"
                >
                    @csrf

                    @foreach(request()->except('page') as $key => $value)
                        @if(is_scalar($value))
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach

                    <input
                        type="email"
                        name="email"
                        required
                        placeholder="admin@email.com"
                        class="w-full rounded-xl border-slate-300 text-sm"
                    >

                    <button
                        type="submit"
                        class="w-full rounded-xl bg-slate-900 px-4 py-3 text-sm font-semibold text-white"
                    >
                        Email Report
                    </button>
                </form>

            </div>

        </div>

    </div>

    {{-- BREAKDOWN --}}

    <div class="rounded-3xl bg-white border border-slate-200 shadow-sm mb-8">

        <div class="p-6 border-b border-slate-100">
            <h2 class="text-xl font-bold text-slate-900">
                Income & Expense Breakdown
            </h2>
        </div>

        <div class="overflow-x-auto">

            <table class="w-full text-sm">

                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-4 text-left">Type</th>
                        <th class="px-4 py-3 text-left">Item</th>
<th class="px-6 py-4 text-left">Source</th>
                        <th class="px-6 py-4 text-right">Amount</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">

                    @forelse($breakdown as $row)

                        <tr>
                            <td class="px-6 py-4">
                                <span class="{{ $row['type'] === 'credit' ? 'text-emerald-600' : 'text-red-600' }} font-semibold">
                                    {{ ucfirst($row['type']) }}
                                </span>
                            </td>

                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-800">
                                    {{ $row['item_name'] ?? $row['item'] ?? '—' }}
                                </div>
                                @if(!empty($row['item_type']) || !empty($row['item_id']))
                                    <div class="text-xs text-slate-500">
                                        {{ ucwords(str_replace('_', ' ', $row['item_type'] ?? '')) }}
                                        @if(!empty($row['item_id']))
                                            #{{ $row['item_id'] }}
                                        @endif
                                    </div>
                                @endif
                            </td>
            
<td class="px-6 py-4 font-medium text-slate-700">
                                {{ ucwords(str_replace('_', ' ', $row['source'])) }}
                            </td>

                            <td class="px-6 py-4 text-right font-semibold">
                                ₦{{ number_format($row['total'], 2) }}
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="3" class="px-6 py-10 text-center text-slate-500">
                                No financial records found for this period.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

    {{-- TRANSACTION LEDGER --}}

    <div class="rounded-3xl bg-white border border-slate-200 shadow-sm">

        <div class="p-6 border-b border-slate-100">
            <h2 class="text-xl font-bold text-slate-900">
                Financial Ledger
            </h2>

            <p class="text-sm text-slate-500 mt-1">
                Every platform credit and debit with its transaction origin.
            </p>
        </div>

        <div class="overflow-x-auto">

            <table class="w-full text-sm">

                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-5 py-4 text-left">Date</th>
                        <th class="px-5 py-4 text-left">Type</th>
                        <th class="px-5 py-4 text-left">Source</th>
                            <th>Item</th>
                        <th class="px-5 py-4 text-left">User</th>
                        <th class="px-5 py-4 text-left">Website</th>
                        <th class="px-5 py-4 text-left">Gateway</th>
                        <th class="px-5 py-4 text-right">Amount</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">

                    @forelse($paginatedEntries as $entry)

                        <tr>

                            <td class="px-5 py-4 text-slate-500 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($entry['created_at'])->format('d M Y H:i') }}
                            </td>

                            <td class="px-5 py-4">
                                <span class="font-semibold {{ $entry['type'] === 'credit' ? 'text-emerald-600' : 'text-red-600' }}">
                                    {{ ucfirst($entry['type']) }}
                                </span>
                            </td>

                            <td class="px-5 py-4 font-medium">
                                {{ ucwords(str_replace('_', ' ', $entry['source'])) }}
                            </td>
                            <td>
                                <div class="font-medium text-slate-900">
                                    {{ $entry['item_name'] ?? '—' }}
                                </div>
                                @if(!empty($entry['item_type']))
                                    <div class="text-xs text-slate-500">
                                        {{ ucwords(str_replace('_', ' ', $entry['item_type'])) }}
                                        @if(!empty($entry['item_id']))
                                            #{{ $entry['item_id'] }}
                                        @endif
                                    </div>
                                @endif
                            </td>

                            <td class="px-5 py-4 text-slate-600">
                                @if(isset($users[$entry['user_id']]))
                                    {{ $users[$entry['user_id']]->name }}
                                @elseif($entry['user_id'])
                                    User #{{ $entry['user_id'] }}
                                @else
                                    —
                                @endif
                            </td>

                            <td class="px-5 py-4 text-slate-600">
                                @if($entry['website_id'])
                                    {{ $websites[$entry['website_id']] ?? 'Website #'.$entry['website_id'] }}
                                @else
                                    —
                                @endif
                            </td>

                            {{-- ESUBIZ_FINANCIAL_LEDGER_GATEWAY_COLUMN_V1 --}}
                            <td class="px-5 py-4">
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs">
                                    {{ !empty($entry['payment_source'])
                                        ? ucwords(str_replace('_', ' ', $entry['payment_source']))
                                        : '—' }}
                                </span>
                            </td>

                            <td class="px-5 py-4 text-right font-bold whitespace-nowrap
                                {{ $entry['type'] === 'credit' ? 'text-emerald-600' : 'text-red-600' }}">
                                {{ $entry['type'] === 'credit' ? '+' : '-' }}
                                {{ $entry['currency'] }}
                                {{ number_format($entry['amount'], 2) }}
                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                                No transactions found.
                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

    <div class="mt-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4">

        <div class="flex items-center gap-3 text-sm text-slate-500">

            <span>
                Showing
                {{ $totalEntries ? (($page - 1) * $perPage) + 1 : 0 }}
                -
                {{ min($page * $perPage, $totalEntries) }}
                of {{ $totalEntries }}
            </span>

            @if($page > 1)
                <a
                    href="{{ request()->fullUrlWithQuery(['page' => $page - 1]) }}"
                    class="rounded-xl border border-slate-300 px-4 py-2"
                >
                    Previous
                </a>
            @endif

            @if($page < $lastPage)
                <a
                    href="{{ request()->fullUrlWithQuery(['page' => $page + 1]) }}"
                    class="rounded-xl bg-blue-600 px-4 py-2 font-semibold text-white"
                >
                    Next
                </a>
            @endif

        </div>

    </div>

</div>

@endsection

<script>
document.addEventListener('DOMContentLoaded', function () {
    const period = document.querySelector('select[name="period"]');
    const range = document.getElementById('custom-date-range');

    function financialReportPeriodToggle() {
        const custom = period && period.value === 'custom';

        range?.classList.toggle('hidden', !custom);

        if (!custom) {
            const fromInput = range?.querySelector('input[name="from"]');
            const toInput = range?.querySelector('input[name="to"]');

            if (fromInput) fromInput.value = '';
            if (toInput) toInput.value = '';
        }
    }

    period?.addEventListener('change', financialReportPeriodToggle);
    financialReportPeriodToggle();
});
</script>
