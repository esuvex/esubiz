<h2>Esubiz Financial Report</h2>

<p>
    Reporting period:
    <strong>{{ $from->format('d M Y') }}</strong>
    -
    <strong>{{ $to->format('d M Y') }}</strong>
</p>

<p><strong>Total Income:</strong> ₦{{ number_format($income, 2) }}</p>
<p><strong>Total Expenses:</strong> ₦{{ number_format($expenses, 2) }}</p>
<p><strong>Net:</strong> ₦{{ number_format($net, 2) }}</p>

<p>
    The detailed financial ledger is available from the Esubiz Admin Financial Reports page.
</p>
