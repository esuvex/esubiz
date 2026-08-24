<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function downloadFinancialRecordsPdf(Request $request)
    {
        $records = $this->financialRecordsQuery($request);

        $pdf = Pdf::loadView(
            'developer.financial-records-pdf',
            ['records' => $records]
        );

        return $pdf->download(
            'esubiz-developer-financial-records-' . now()->format('Y-m-d-His') . '.pdf'
        );
    }

    public function downloadFinancialRecordsCsv(Request $request)
    {
        $records = $this->financialRecordsQuery($request);

        $filename = 'esubiz-developer-financial-records-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($records) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Date',
                'Type',
                'Description',
                'Source',
                'Currency',
                'Amount',
                'Status',
                'Reference',
            ]);

            foreach ($records as $record) {
                fputcsv($handle, [
                    $record->created_at,
                    ucfirst($record->type),
                    $record->description,
                    $record->source,
                    $record->currency,
                    number_format((float) $record->amount, 2, '.', ''),
                    $record->status,
                    $record->reference_id ?? '',
                ]);
            }

            fclose($handle);
        }, $filename);
    }

    public function emailFinancialRecords(Request $request)
    {
        $records = $this->financialRecordsQuery($request);
        $email = auth()->user()->email;

        Mail::send(
            'developer.financial-records-email',
            ['records' => $records],
            function ($message) use ($email) {
                $message->to($email)
                    ->subject('Esubiz Developer Financial Statement');
            }
        );

        return back()->with(
            'success',
            'Your developer financial statement has been sent to your email.'
        );
    }

    public function financialRecords(Request $request): View
    {
        $records = $this->financialRecordsQuery($request);

        return view('developer.financial-records', [
            'records' => $records,
        ]);
    }

    protected function financialRecordsQuery(Request $request)
    {
        $developerId = auth()->id();

        $from = $request->filled('from')
            ? \Carbon\Carbon::parse($request->input('from'))->startOfDay()
            : now()->subYears(10)->startOfDay();

        $to = $request->filled('to')
            ? \Carbon\Carbon::parse($request->input('to'))->endOfDay()
            : now()->endOfDay();

        $records = DB::table('revenue_events as r')
            ->leftJoin('payment_transactions as pt', function ($join) {
                $join->on('pt.id', '=', 'r.reference_id')
                    ->where('r.reference_type', '=', 'payment_transaction');
            })
            ->leftJoin('payment_providers as pp', 'pp.id', '=', 'pt.payment_provider_id')
            ->whereNull('r.deleted_at')
            ->whereBetween('r.created_at', [$from, $to])
            ->where(function ($query) use ($developerId) {
                $query->where('r.financial_account_developer_id', $developerId)
                    ->orWhere(function ($q) use ($developerId) {
                        $q->where('r.financial_account_user_id', $developerId)
                            ->whereIn('r.financial_account_type', [
                                'developer',
                                'developer_referral',
                            ]);
                    })
                    ->orWhere(function ($q) use ($developerId) {
                        $q->where('r.user_id', $developerId)
                            ->where('r.revenue_owner', 'esubiz')
                            ->where('r.is_platform_revenue', true);
                    });
            })
            ->select(
                'r.*',
                'pp.name as gateway_name',
                'pp.slug as gateway_slug',
                'pt.payload as payment_payload'
            )
            ->get()
            ->map(function ($row) {

                $isReferral =
                    $row->source_module === 'referral_commission';

                $isOwnProductSale =
                    $row->financial_account_developer_id !== null
                    && !$isReferral
                    && $row->revenue_owner !== 'esubiz';

                $isPlatformPurchase =
                    $row->revenue_owner === 'esubiz'
                    && (bool) $row->is_platform_revenue
                    && !$isReferral
                    && !$isOwnProductSale;

                $type = $isPlatformPurchase
                    ? 'expense'
                    : 'revenue';

                $description = $row->item_name;

                if (!$description) {
                    $description = $row->item_type && $row->item_id
                        ? ucwords(str_replace('_', ' ', $row->item_type))
                            . ' #' . $row->item_id
                        : ($row->reference_type
                            ? ucwords(str_replace('_', ' ', $row->reference_type))
                                . ' #' . $row->reference_id
                            : '—');
                }

                if ($isReferral) {
                    $source = 'Esubiz';
                } else {
                    $paymentPayload = $row->payment_payload
                        ? json_decode($row->payment_payload, true)
                        : [];

                    $paymentMode = $paymentPayload['payment_mode']
                        ?? null;

                    $source = match ($paymentMode) {
                        'wallet' => 'Wallet',
                        'gift_card', 'giftcard' => 'Gift Card',
                        'offline' => (
                            !empty($paymentPayload['offline_payment_method_id'])
                                ? (
                                    \Illuminate\Support\Facades\DB::table(
                                        'offline_payment_methods'
                                    )
                                        ->where(
                                            'id',
                                            (int) $paymentPayload[
                                                'offline_payment_method_id'
                                            ]
                                        )
                                        ->whereNull('deleted_at')
                                        ->value('name')
                                    ?? (
                                        !empty($paymentPayload['offline_payment_method'])
                                            ? ucwords(
                                                str_replace(
                                                    ['-', '_'],
                                                    ' ',
                                                    $paymentPayload[
                                                        'offline_payment_method'
                                                    ]
                                                )
                                            )
                                            : 'Offline'
                                    )
                                )
                                : (
                                    !empty($paymentPayload['offline_payment_method'])
                                        ? ucwords(
                                            str_replace(
                                                ['-', '_'],
                                                ' ',
                                                $paymentPayload[
                                                    'offline_payment_method'
                                                ]
                                            )
                                        )
                                        : 'Offline'
                                )
                        ),
                        'online' => $row->gateway_name
                            ?? $row->gateway_slug
                            ?? ucfirst(
                                $paymentPayload['payment_provider']
                                    ?? 'Online'
                            ),
                        default => $row->gateway_name
                            ?? $row->gateway_slug
                            ?? (
                                $row->source_module === 'addons'
                                    ? 'Esubiz'
                                    : ucwords(
                                        str_replace(
                                            '_',
                                            ' ',
                                            $row->source_module
                                                ?? 'Esubiz'
                                        )
                                    )
                            ),
                    };
                }

                return (object) [
                    'created_at' => $row->created_at,
                    'type' => $type,
                    'description' => $description,
                    'source' => $source,
                    'reference_id' => $row->reference_id,
                    'currency' => $row->currency ?? 'NGN',
                    'amount' => abs((float) ($row->net_amount ?? 0)),
                    'status' => $row->status ?? 'processed',
                ];
            })
            ->filter(function ($record) use ($request) {
                if (!$request->filled('type') || $request->input('type') === 'all') {
                    return true;
                }

                return $record->type === $request->input('type');
            })
            ->sortByDesc('created_at')
            ->values();

        return $records;
    }

    /**
     * Display the developer dashboard.
     */
    public function index(): View
    {
        $user = request()->user();

        $websiteCount = DB::table('websites')
            ->where('developer_id', $user->id)
            ->count();

        $commissions = DB::table('commission_transactions')
            ->where('user_id', $user->id)
            ->where('type', 'developer');

        $totalEarnings = (clone $commissions)
            ->sum('commission_amount');

        $pendingEarnings = (clone $commissions)
            ->where('status', 'pending')
            ->sum('commission_amount');

        $approvedEarnings = (clone $commissions)
            ->where('status', 'approved')
            ->sum('commission_amount');

        $paidEarnings = (clone $commissions)
            ->where('status', 'paid')
            ->sum('commission_amount');

        $recentCommissions = DB::table('commission_transactions')
            ->where('user_id', $user->id)
            ->where('type', 'developer')
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        $financialRecords = DB::table('revenue_events')
            ->where(function ($query) use ($user) {
                $query->where('financial_account_developer_id', $user->id)
                    ->orWhere(function ($q) use ($user) {
                        $q->where('financial_account_user_id', $user->id)
                            ->whereIn('financial_account_type', [
                                'developer',
                                'developer_referral',
                            ]);
                    });
            })
            ->latest('created_at')
            ->limit(100)
            ->get();

        return view('developer.index', [
            'websiteCount' => $websiteCount,
            'totalEarnings' => $totalEarnings,
            'pendingEarnings' => $pendingEarnings,
            'approvedEarnings' => $approvedEarnings,
            'paidEarnings' => $paidEarnings,
            'recentCommissions' => $recentCommissions,
            'financialRecords' => $financialRecords,
        ]);
    }
}
