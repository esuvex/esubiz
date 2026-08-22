<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Esubiz Gift Cards</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; }
        h1 { font-size: 18px; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background: #f3f4f6; }
    </style>
</head>
<body>

<h1>Esubiz Gift Cards</h1>

<table>
    <thead>
        <tr>
            <th>Code</th>
            <th>Name</th>
            <th>Currency</th>
            <th>Initial</th>
            <th>Balance</th>
            <th>Usage</th>
            <th>Status</th>
            <th>Expires</th>
        </tr>
    </thead>

    <tbody>
        @foreach($cards as $card)
            <tr>
                <td>{{ $card->code }}</td>
                <td>{{ $card->name }}</td>
                <td>{{ $card->currency }}</td>
                <td>{{ number_format((float) $card->initial_amount, 2) }}</td>
                <td>{{ number_format((float) $card->remaining_balance, 2) }}</td>
                <td>{{ $card->usage_count }} / {{ $card->usage_limit ?? 'Unlimited' }}</td>
                <td>{{ ucfirst($card->status) }}</td>
                <td>{{ $card->expires_at ?: 'Never' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

</body>
</html>
