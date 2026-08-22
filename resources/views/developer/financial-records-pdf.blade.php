<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Esubiz Developer Financial Statement</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #172033;
        }

        h1 {
            margin-bottom: 4px;
        }

        .subtitle {
            color: #64748b;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f1f5f9;
            text-align: left;
            padding: 8px;
            border-bottom: 1px solid #cbd5e1;
        }

        td {
            padding: 8px;
            border-bottom: 1px solid #e2e8f0;
        }

        .revenue {
            color: #059669;
            font-weight: bold;
        }

        .expense {
            color: #dc2626;
            font-weight: bold;
        }

        .amount {
            text-align: right;
        }
    </style>
</head>
<body>

    <h1>Esubiz Developer Financial Statement</h1>

    <div class="subtitle">
        Generated {{ now()->format('d M Y H:i') }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Type</th>
                <th>Description</th>
                <th>Source</th>
                <th>Status</th>
                <th>Reference</th>
                <th class="amount">Amount</th>
            </tr>
        </thead>

        <tbody>
            @forelse($records as $record)
                @php
                    $type = $record->type ?? 'revenue';
                    $isExpense = $type === 'expense';
                @endphp

                <tr>
                    <td>{{ \Carbon\Carbon::parse($record->created_at)->format('d M Y H:i') }}</td>

                    <td class="{{ $isExpense ? 'expense' : 'revenue' }}">
                        {{ ucfirst($type) }}
                    </td>

                    <td>{{ $record->description ?? '—' }}</td>

                    <td>{{ $record->source ?? 'Esubiz' }}</td>

                    <td>{{ ucfirst($record->status ?? 'processed') }}</td>

                    <td>{{ $record->reference_id ?? '—' }}</td>

                    <td class="amount {{ $isExpense ? 'expense' : 'revenue' }}">
                        {{ $isExpense ? '-' : '+' }}
                        {{ $record->currency ?? 'NGN' }}
                        {{ number_format((float) ($record->amount ?? 0), 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center;padding:30px;">
                        No financial records found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
