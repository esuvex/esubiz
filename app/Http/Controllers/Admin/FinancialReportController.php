<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class FinancialReportController
{
    public function index(Request $request)
    {
        $period = $request->input('period', '30d');

        $periods = [
            '7d'  => now()->subDays(7),
            '30d' => now()->subDays(30),
            '3m'  => now()->subMonths(3),
            '6m'  => now()->subMonths(6),
            '12m' => now()->subMonths(12),
        ];

        $customFrom = $request->input('from');
        $customTo = $request->input('to');

        if ($customFrom && $customTo) {
            $from = \Carbon\Carbon::parse($customFrom)->startOfDay();
            $to = \Carbon\Carbon::parse($customTo)->endOfDay();
            $period = 'custom';
        } else {
            $from = $periods[$period] ?? $periods['30d'];
            $to = now();
        }

        $type = $request->input('type', 'all');
        $source = $request->input('source');
        $status = $request->input('status');
        $userId = $request->input('user_id');
        $websiteId = $request->input('website_id');
        $currency = strtoupper(trim($request->input('currency', '')));

        $currencySettings = app(\App\Services\Core\CoreCurrencyService::class)
            ->getCurrencies(null);

        $enabledCurrencies = array_values(array_unique(array_filter([
            $currencySettings['primary'] ?? null,
            !empty($currencySettings['secondary_enabled'])
                ? ($currencySettings['secondary'] ?? null)
                : null,
        ])));


        /*
         * -------------------------------------------------------
         * CREDIT / INCOME
         * -------------------------------------------------------
         */
        $credits = DB::table('revenue_events as revenue')
            ->leftJoin('websites', 'websites.id', '=', 'revenue.website_id')
            ->whereBetween('revenue.created_at', [$from, $to])
            ->whereNull('revenue.deleted_at')
            ->where('revenue.revenue_owner', 'esubiz')
            ->where('revenue.is_platform_revenue', true)
            ->select([
                'revenue.id',
                'revenue.source_module',
                'revenue.net_amount',
                'revenue.currency',
                'revenue.status',
                'revenue.user_id',
                'revenue.website_id',
                'revenue.workspace_id',
                'revenue.reference_type',
                'revenue.reference_id',
                'revenue.item_type',
                'revenue.item_id',
                'revenue.item_name',
                'revenue.created_at',
                'websites.name as website_name',
                'websites.domain as website_domain',
            ])
            ->get()
            ->map(function ($row) {
                if (!$row->website_id && $row->user_id) {
                    $website = DB::table('websites')
                        ->where('owner_id', $row->user_id)
                        ->where('status', 'active')
                        ->orderByDesc('is_default')
                        ->orderByDesc('updated_at')
                        ->first();

                    if ($website) {
                        $row->website_id = $website->id;
                        $row->website_name = $website->name;
                        $row->website_domain = $website->domain;
                    }
                }
                return [
                    'id' => $row->id,
                    'type' => 'credit',
                    'source' => $row->source_module ?: 'other',
                    'category' => $row->source_module
 ?: 'other',
                    'amount' => (float) $row->net_amount,
                    'currency' => $row->currency,
                    'status' => $row->status,
                    'user_id' => $row->user_id,
                    'website_id' => $row->website_id,
                    'website_name' => $row->website_name ?? null,
                    'website_domain' => $row->website_domain ?? null,
                    'workspace_id' => $row->workspace_id,
                    'reference_type' => $row->reference_type,
                    'reference_id' => $row->reference_id,
                    'item_type' => $row->item_type ?? null,
                    'item_id' => $row->item_id ?? null,
                    'item_name' => $row->item_name
                        ?? (
                            !empty($row->item_type) && !empty($row->item_id)
                                ? ucwords(str_replace('_', ' ', $row->item_type)) . ' #' . $row->item_id
                                : ($row->reference_type
                                    ? ucwords(str_replace('_', ' ', $row->reference_type)) . ' #' . $row->reference_id
                                    : '—')
                        ),
                    'item' => $row->item_name
                        ?? (
                            !empty($row->item_type) && !empty($row->item_id)
                                ? ucwords(str_replace('_', ' ', $row->item_type)) . ' #' . $row->item_id
                                : ($row->reference_type
                                    ? ucwords(str_replace('_', ' ', $row->reference_type)) . ' #' . $row->reference_id
                                    : '—')
                        ),
                    'created_at' => $row->created_at,
                ];
            });

        /*
         * -------------------------------------------------------
         * DEBIT / EXPENSES
         *
         * expense_events is the authoritative expense event
         * ledger. Column differences are handled from the actual
         * schema so the report does not depend on speculative
         * column names.
         * -------------------------------------------------------
         */
        $expenseColumns = Schema::getColumnListing('expense_events');

        $expenseAmount = in_array('amount', $expenseColumns)
            ? 'amount'
            : (in_array('net_amount', $expenseColumns)
                ? 'net_amount'
                : null);

        $expenseSource = in_array('source_module', $expenseColumns)
            ? 'source_module'
            : (in_array('source', $expenseColumns)
                ? 'source'
                : (in_array('category', $expenseColumns)
                    ? 'category'
                    : null));

        $expenseStatus = in_array('status', $expenseColumns)
            ? 'status'
            : null;

        if ($expenseAmount) {
            $select = [
                'id',
                DB::raw("$expenseAmount as report_amount"),
                'created_at',
            ];

            foreach ([
                'currency',
                'user_id',
                'website_id',
                'workspace_id',
                'reference_type',
                'reference_id',
                'category',
                'source',
                'source_module',
                'status',
                'item_type',
                'item_id',
                'item_name',

            ] as $column) {
                if (in_array($column, $expenseColumns)) {
                    $select[] = $column;
                }
            }

            $expenseQuery = DB::table('expense_events')
                ->whereBetween('created_at', [$from, $to])
                ->select($select);

            if ($expenseStatus && $status) {
                $expenseQuery->where($expenseStatus, $status);
            }

            $debits = $expenseQuery
                ->get()
                ->map(function ($row) use ($expenseSource) {
                    $source = $expenseSource
                        ? ($row->{$expenseSource} ?? 'other')
                        : 'other';

                    return [
                        'id' => $row->id,
                        'type' => 'debit',
                        'source' => $source ?: 'other',
                        'category' => $row->category ?? $source ?: 'other',
                        'amount' => (float) $row->report_amount,
                        'currency' => $row->currency ?? 'NGN',
                        'status' => $row->status ?? 'processed',
                        'user_id' => $row->user_id ?? null,
                        'website_id' => $row->website_id ?? null,
                        'workspace_id' => $row->workspace_id ?? null,
                        'reference_type' => $row->reference_type ?? null,
                        'reference_id' => $row->reference_id ?? null,
                        'item_type' => $row->item_type ?? null,
                        'item_id' => $row->item_id ?? $row->reference_id ?? null,
                        'item_name' => $row->item_name
                            ?? $row->description
                            ?? $row->expense_type
                            ?? $source
                            ?? '—',
                        'created_at' => $row->created_at,
                    ];
                });
        } else {
            $debits = collect();
        }

        /*
         * -------------------------------------------------------
         * FILTERS
         * -------------------------------------------------------
         */
        $entries = $credits
            ->merge($debits)
            ->filter(function ($entry) use (
                $type,
                $source,
                $status,
                $userId,
                $websiteId,
                $currency
            ) {
                if ($type !== 'all' && $entry['type'] !== $type) {
                    return false;
                }

                if ($source && $entry['source'] !== $source) {
                    return false;
                }

                if ($status && $entry['status'] !== $status) {
                    return false;
                }

                if ($userId && (string) $entry['user_id'] !== (string) $userId) {
                    return false;
                }

                if ($websiteId && (string) $entry['website_id'] !== (string) $websiteId) {
                    return false;
                }

                if ($currency && strtoupper($entry['currency']) !== $currency) {
                    return false;
                }

                return true;
            })
            ->sortByDesc('created_at')
            ->values();

        /*
         * -------------------------------------------------------
         * SUMMARY
         * -------------------------------------------------------
         */
        $income = $entries
            ->where('type', 'credit')
            ->sum('amount');

        $expenses = $entries
            ->where('type', 'debit')
            ->sum('amount');

        $net = $income - $expenses;

        /*
         * Source/category breakdown.
         */
        $breakdown = $entries
            ->groupBy(function ($entry) {
                return $entry['type']
                    . ':'
                    . $entry['source']
                    . ':'
                    . ($entry['item_type'] ?? '')
                    . ':'
                    . ($entry['item_id'] ?? '');
            })
            ->map(function ($group) {
                $first = $group->first();

                return [
                    'type' => $first['type'],
                    'source' => $first['source'],
                    'item_type' => $first['item_type'] ?? null,
                    'item_id' => $first['item_id'] ?? null,
                    'item_name' => $first['item_name']
                        ?? $first['item']
                        ?? '—',
                    'item' => $first['item']
                        ?? $first['item_name']
                        ?? '—',
                    'total' => $group->sum('amount'),
                ];
            })
            ->values();

        /*
         * Origin details.
         */
        $websiteIds = $entries
            ->pluck('website_id')
            ->filter()
            ->unique()
            ->values();

        $userIds = $entries
            ->pluck('user_id')
            ->filter()
            ->unique()
            ->values();

        $websites = $websiteIds->isEmpty()
            ? collect()
            : DB::table('websites')
                ->whereIn('id', $websiteIds)
                ->pluck('name', 'id');

        $users = $userIds->isEmpty()
            ? collect()
            : DB::table('users')
                ->whereIn('id', $userIds)
                ->select('id', 'name', 'email')
                ->get()
                ->keyBy('id');

        /*
         * Available source filters.
         */
        $sources = $entries
            ->pluck('source')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $page = max((int) $request->input('page', 1), 1);
        $perPage = 10;
        $totalEntries = $entries->count();

        $paginatedEntries = $entries
            ->slice(($page - 1) * $perPage, $perPage)
            ->values();

        $lastPage = max((int) ceil($totalEntries / $perPage), 1);

        return view('admin.financial-reports.index', compact(
            'entries',
            'paginatedEntries',
            'income',
            'expenses',
            'net',
            'breakdown',
            'websites',
            'users',
            'sources',
            'period',
            'type',
            'source',
            'status',
            'userId',
            'websiteId',
            'currency',
            'enabledCurrencies',
            'from',
            'to',
            'customFrom',
            'customTo',
            'page',
            'perPage',
            'totalEntries',
            'lastPage'
        ));
    }

    public function csv(Request $request)
    {
        $data = $this->reportData($request);

        $filename = 'esubiz-financial-report-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($data) {
            $out = fopen('php://output', 'w');

            fputcsv($out, [
                'Date',
                'Type',
                'Source',
                'Category',
                'User',
                'Website',
                'Currency',
                'Amount',
                'Status',
                'Reference Type',
                'Reference ID',
            ]);

            foreach ($data['entries'] as $entry) {
                fputcsv($out, [
                    $entry['created_at'],
                    $entry['type'],
                    $entry['source'],
                    $entry['category'],
                    $data['users'][$entry['user_id']]->name ?? (
                        $entry['user_id'] ? 'User #'.$entry['user_id'] : ''
                    ),
                    $entry['website_id']
                        ? ($data['websites'][$entry['website_id']] ?? 'Website #'.$entry['website_id'])
                        : '',
                    $entry['currency'],
                    number_format($entry['amount'], 2, '.', ''),
                    $entry['status'],
                    $entry['reference_type'],
                    $entry['reference_id'],
                ]);
            }

            fclose($out);
        }, $filename);
    }

    public function pdf(Request $request)
    {
        $data = $this->reportData($request);

        $data['income'] = collect($data['entries'])
            ->where('type', 'credit')
            ->sum('amount');

        $data['expenses'] = collect($data['entries'])
            ->where('type', 'debit')
            ->sum('amount');

        $data['net'] = $data['income'] - $data['expenses'];

        $pdf = Pdf::loadView(
            'admin.financial-reports.pdf',
            $data
        );

        return $pdf->download(
            'esubiz-financial-report-' . now()->format('Y-m-d-His') . '.pdf'
        );
    }

    public function email(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $data = $this->reportData($request);

        Mail::send(
            'admin.financial-reports.email',
            $data,
            function ($message) use ($request) {
                $message
                    ->to($request->email)
                    ->subject('Esubiz Financial Report - ' . now()->format('d M Y'));
            }
        );

        return back()->with(
            'success',
            'Financial report sent successfully.'
        );
    }

    protected function reportData(Request $request): array
    {
        $period = $request->input('period', '30d');

        $periods = [
            '7d'  => now()->subDays(7),
            '30d' => now()->subDays(30),
            '3m'  => now()->subMonths(3),
            '6m'  => now()->subMonths(6),
            '12m' => now()->subMonths(12),
        ];

        if ($request->filled('from') && $request->filled('to')) {
            $from = \Carbon\Carbon::parse($request->input('from'))->startOfDay();
            $to = \Carbon\Carbon::parse($request->input('to'))->endOfDay();
        } else {
            $from = $periods[$period] ?? $periods['30d'];
            $to = now();
        }

        $type = $request->input('type', 'all');
        $source = $request->input('source');
        $status = $request->input('status');
        $currency = strtoupper(trim($request->input('currency', '')));

        $credits = DB::table('revenue_events as revenue')
            ->leftJoin('websites', 'websites.id', '=', 'revenue.website_id')
            ->whereBetween('revenue.created_at', [$from, $to])
            ->whereNull('revenue.deleted_at')
            ->where('revenue.revenue_owner', 'esubiz')
            ->where('revenue.is_platform_revenue', true)
            ->select([
                'revenue.id',
                'revenue.source_module',
                'revenue.net_amount',
                'revenue.currency',
                'revenue.status',
                'revenue.user_id',
                'revenue.website_id',
                'revenue.workspace_id',
                'revenue.reference_type',
                'revenue.reference_id',
                'revenue.item_type',
                'revenue.item_id',
                'revenue.item_name',
                'revenue.created_at',
                'websites.name as website_name',
                'websites.domain as website_domain',
            ])
            ->get()
            ->map(function ($row) {
                if (!$row->website_id && $row->user_id) {
                    $website = DB::table('websites')
                        ->where('owner_id', $row->user_id)
                        ->where('status', 'active')
                        ->orderByDesc('is_default')
                        ->orderByDesc('updated_at')
                        ->first();

                    if ($website) {
                        $row->website_id = $website->id;
                        $row->website_name = $website->name;
                        $row->website_domain = $website->domain;
                    }
                }

                $itemName = $row->item_name;

                if (!$itemName && $row->item_type && $row->item_id) {
                    $itemName = ucwords(
                        str_replace('_', ' ', $row->item_type)
                    ) . ' #' . $row->item_id;
                }

                if (!$itemName) {
                    $itemName = $row->reference_type && $row->reference_id
                        ? ucwords(str_replace('_', ' ', $row->reference_type))
                            . ' #' . $row->reference_id
                        : '—';
                }

                return [
                    'id' => $row->id,
                    'type' => 'credit',
                    'source' => $row->source_module ?: 'other',
                    'category' => $row->source_module ?: 'other',
                    'item_type' => $row->item_type,
                    'item_id' => $row->item_id,
                    'item_name' => $itemName,
                    'amount' => (float) $row->net_amount,
                    'currency' => $row->currency,
                    'status' => $row->status,
                    'user_id' => $row->user_id,
                    'website_id' => $row->website_id,
                    'website_name' => $row->website_name ?? null,
                    'website_domain' => $row->website_domain ?? null,
                    'workspace_id' => $row->workspace_id,
                    'reference_type' => $row->reference_type,
                    'reference_id' => $row->reference_id,
                    'created_at' => $row->created_at,
                ];
            });

        $expenseColumns = Schema::getColumnListing('expense_events');

        $amountColumn = in_array('amount', $expenseColumns)
            ? 'amount'
            : (in_array('net_amount', $expenseColumns)
                ? 'net_amount'
                : null);

        if ($amountColumn) {
            $expenseRows = DB::table('expense_events')
                ->whereBetween('created_at', [$from, $to])
                ->get();

            $debits = $expenseRows->map(function ($row) use ($amountColumn) {
                $source = isset($row->source_module)
                    ? $row->source_module
                    : ($row->source ?? $row->category ?? 'other');

                $itemName = $row->item_name
                    ?? $row->description
                    ?? $row->expense_type
                    ?? $source
                    ?? '—';

                return [
                    'id' => $row->id,
                    'type' => 'debit',
                    'source' => $source ?: 'other',
                    'category' => $row->category ?? $source ?? 'other',
                    'item_type' => $row->item_type ?? null,
                    'item_id' => $row->item_id ?? $row->reference_id ?? null,
                    'item_name' => $itemName,
                    'amount' => (float) $row->{$amountColumn},
                    'currency' => $row->currency ?? 'NGN',
                    'status' => $row->status ?? 'processed',
                    'user_id' => $row->user_id ?? null,
                    'website_id' => $row->website_id ?? null,
                    'website_name' => null,
                    'website_domain' => null,
                    'workspace_id' => $row->workspace_id ?? null,
                    'reference_type' => $row->reference_type ?? null,
                    'reference_id' => $row->reference_id ?? null,
                    'created_at' => $row->created_at,
                ];
            });
        } else {
            $debits = collect();
        }

        $entries = $credits
            ->merge($debits)
            ->filter(function ($entry) use (
                $type,
                $source,
                $status,
                $currency
            ) {
                if ($type !== 'all') {
                    $wantedType = $type === 'revenue'
                        ? 'credit'
                        : ($type === 'expense' ? 'debit' : $type);

                    if ($entry['type'] !== $wantedType) {
                        return false;
                    }
                }

                if ($source && $entry['source'] !== $source) {
                    return false;
                }

                if ($status && $entry['status'] !== $status) {
                    return false;
                }

                if (
                    $currency
                    && strtoupper($entry['currency']) !== $currency
                ) {
                    return false;
                }

                return true;
            })
            ->sortByDesc('created_at')
            ->values();

        $userIds = $entries
            ->pluck('user_id')
            ->filter()
            ->unique()
            ->values();

        $websiteIds = $entries
            ->pluck('website_id')
            ->filter()
            ->unique()
            ->values();

        return [
            'from' => $from,
            'to' => $to,
            'entries' => $entries,
            'users' => DB::table('users')
                ->whereIn('id', $userIds)
                ->get()
                ->keyBy('id'),
            'websites' => DB::table('websites')
                ->whereIn('id', $websiteIds)
                ->get()
                ->mapWithKeys(fn ($website) => [
                    $website->id => $website->name
                        ?: ($website->domain ?: 'Website #' . $website->id),
                ]),
        ];
    }

}
