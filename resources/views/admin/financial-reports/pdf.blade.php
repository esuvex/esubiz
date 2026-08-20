<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Esubiz Financial Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111827; }
        h1 { font-size: 20px; }
        .summary { width: 100%; margin-bottom: 20px; }
        .summary td { padding: 10px; border: 1px solid #ddd; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 7px; border: 1px solid #ddd; text-align: left; }
        th { background: #f3f4f6; }
        .credit { color: #059669; }
        .debit { color: #dc2626; }
    </style>
</head>
<body>

<h1>Esubiz Financial Report</h1>

<p>
    {{ $from->format('d M Y') }} -
    {{ $to->format('d M Y') }}
</p>

<table class="summary">
    <tr>
        <td><strong>Income</strong><br>₦{{ number_format($income, 2) }}</td>
        <td><strong>Expenses</strong><br>₦{{ number_format($expenses, 2) }}</td>
        <td><strong>Net</strong><br>₦{{ number_format($net, 2) }}</td>
    </tr>
</table>

<table>
    <thead>
        <tr>
            <th>Date</th>
            <th>Type</th>
            <th>Source</th>
            <th>User</th>
            <th>Website</th>
            <th>Status</th>
            <th>Amount</th>
        </tr>
    </thead>

    <tbody>
    @foreach($entries as $entry)
        <tr>
            <td>{{ $entry['created_at'] }}</td>
            <td class="{{ $entry['type'] }}">
                {{ ucfirst($entry['type']) }}
            </td>
            <td>{{ ucwords(str_replace('_', ' ', $entry['source'])) }}</td>
            <td>
                {{ $users[$entry['user_id']]->name ?? ($entry['user_id'] ? 'User #'.$entry['user_id'] : '—') }}
            </td>
            <td>
                {{ $entry['website_id'] ? ($websites[$entry['website_id']] ?? 'Website #'.$entry['website_id']) : '—' }}
            </td>
            <td>{{ ucfirst($entry['status']) }}</td>
            <td>
                {{ $entry['currency'] }}
                {{ number_format($entry['amount'], 2) }}
            </td>
        </tr>
    @endforeach
    </tbody>
</table>

</body>
</html>
