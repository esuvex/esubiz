<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ApiSubscription;
use App\Models\Wallet;
use App\Services\WebsiteDraftService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function downloadFinancialRecordsPdf(Request $request)
    {
        $records = $this->financialRecordsQuery($request);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'user.financial-records-pdf',
            ['records' => $records]
        );

        return $pdf->download('esubiz-financial-records.pdf');
    }

    public function downloadFinancialRecordsCsv(Request $request)
    {
        $records = $this->financialRecordsQuery($request);

        return response()->streamDownload(function () use ($records) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Date',
                'Type',
                'Source',
                'Amount',
                'Currency',
                'Status',
                'Reference',
            ]);

            fputcsv($handle, [
                'Date',
                'Type',
                'Description',
                'Source',
                'Amount',
                'Currency',
                'Status',
                'Reference',
            ]);

            foreach ($records as $record) {
                $signedAmount = $record->type === 'expense'
                    ? -abs((float) $record->amount)
                    : abs((float) $record->amount);

                fputcsv($handle, [
                    $record->created_at,
                    ucfirst($record->type),
                    $record->description,
                    $record->source,
                    number_format($signedAmount, 2, '.', ''),
                    $record->currency,
                    $record->status,
                    $record->reference_id,
                ]);
            }

            fclose($handle);
        }, 'esubiz-financial-records.csv');
    }

    public function emailFinancialRecords(Request $request)
    {
        $records = $this->financialRecordsQuery($request);

        \Illuminate\Support\Facades\Mail::to(auth()->user()->email)
            ->send(
                new \App\Mail\UserFinancialStatementMail($records)
            );

        return back()->with(
            'success',
            'Your financial statement has been sent to your email.'
        );
    }

    protected function financialRecordsQuery(Request $request)
    {
        $userId = auth()->id();

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
            ->where(function ($query) use ($userId) {
                $query->where('r.user_id', $userId)
                    ->orWhere('r.financial_account_user_id', $userId);
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

                $isPlatformPurchase =
                    $row->revenue_owner === 'esubiz'
                    && (bool) $row->is_platform_revenue
                    && !$isReferral;

                $type = $isPlatformPurchase
                    ? 'expense'
                    : 'revenue';

                $description = $row->item_name;

                if (!$description && $row->source_module === 'addons') {
                    $description = 'Add-on purchase';
                } elseif (!$description && $row->source_module === 'themes') {
                    $description = 'Theme purchase';
                } elseif (!$description && $row->source_module === 'modules') {
                    $description = 'Module purchase';
                } elseif (!$description && $row->source_module === 'subscriptions') {
                    $description = 'Website plan subscription';
                } elseif (!$description && $isReferral) {
                    $description = 'Referral bonus';
                } elseif (!$description) {
                    $description = ucwords(
                        str_replace('_', ' ', $row->source_module ?? 'Purchase')
                    );
                }

                return (object) [
                    'created_at' => $row->created_at,
                    'type' => $type,
                    'description' => $description,
                    'source' => (function () use ($row, $isReferral) {
                        if ($isReferral) {
                            return 'Esubiz';
                        }

                        $paymentPayload = $row->payment_payload
                            ? json_decode($row->payment_payload, true)
                            : [];

                        $paymentMode = $paymentPayload['payment_mode']
                            ?? null;

                        return match ($paymentMode) {
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
                                            ?? 'Offline'
                                    )
                                    : 'Offline'
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
                    })(),
                    'reference_id' => $row->reference_id,
                    'currency' => $row->currency ?? 'NGN',
                    'amount' => abs((float) (
                        $row->net_amount
                        ?? $row->gross_amount
                        ?? 0
                    )),
                    'status' => $row->status ?? 'processed',
                ];
            })
            ->filter(function ($record) use ($request) {
                return !$request->filled('type')
                    || $request->input('type') === 'all'
                    || $record->type === $request->input('type');
            })
            ->sortByDesc('created_at')
            ->values();

        return $records;
    }

    public function financialRecords(Request $request)
    {
        $records = $this->financialRecordsQuery($request);

        return view('user.financial-records', [
            'records' => $records,
        ]);
    }

    public function index()
    {
        session(['account_mode' => 'user']);

        $draftService = app(WebsiteDraftService::class);

        $draft = $draftService->latestDraft();

        $websites = auth()->user()
            ->websites()
            ->where('status', 'active')
            ->latest()
            ->get();

        $websiteCount = $websites->count();

        $failedWebsites = auth()->user()
            ->websites()
            ->where('status', 'failed')
            ->latest()
            ->get();

        $draftCount = auth()->user()
            ->websites()
            ->where('status', 'draft')
            ->count();

        $subscriptionCount = ApiSubscription::where(
            'user_id',
            auth()->id()
        )->count();

        $walletBalance = Wallet::where(
            'user_id',
            auth()->id()
        )
            ->where('currency', 'NGN')
            ->where('is_active', true)
            ->sum('available_balance');

        $financialRecords = \Illuminate\Support\Facades\DB::table('revenue_events')
            ->where(function ($query) {
                $query->where('financial_account_user_id', auth()->id())
                    ->orWhere(function ($q) {
                        $q->where('user_id', auth()->id())
                            ->whereNull('financial_account_developer_id');
                    });
            })
            ->latest('created_at')
            ->limit(100)
            ->get();

        return view('user.index', [
            'draft' => $draft,
            'websites' => $websites,
            'failedWebsites' => $failedWebsites,
            'websiteCount' => $websiteCount,
            'draftCount' => $draftCount,
            'subscriptionCount' => $subscriptionCount,
            'walletBalance' => $walletBalance,
            'financialRecords' => $financialRecords,
            'centralDashboardNotices' =>
                app(\App\Services\DashboardNotices\CentralDashboardNoticeService::class)
                    ->forUser(auth()->user()),
        ]);
    }
}
