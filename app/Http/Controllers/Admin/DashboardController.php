<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        session(['account_mode' => 'admin']);

        $totalUsers = DB::table('users')->count();

        $developerAccounts = DB::table('user_roles')
            ->join('roles', 'roles.id', '=', 'user_roles.role_id')
            ->where('roles.slug', 'developer')
            ->where('roles.is_active', true)
            ->distinct('user_roles.user_id')
            ->count('user_roles.user_id');

        $activeWebsites = DB::table('websites')
            ->where('status', 'active')
            ->count();

        $totalWebsites = DB::table('websites')->count();

        $currentMonthRevenue = DB::table('revenue_events')
            ->where('status', 'processed')
            ->where('currency', 'NGN')
            ->whereNull('deleted_at')
            ->whereBetween('created_at', [
                now()->startOfMonth(),
                now()->endOfMonth(),
            ])
            ->where('revenue_owner', 'esubiz')->where('is_platform_revenue', true)
            ->sum('net_amount');

        $totalRevenue = DB::table('revenue_events')
            ->where('status', 'processed')
            ->where('currency', 'NGN')
            ->whereNull('deleted_at')
            ->where('revenue_owner', 'esubiz')->where('is_platform_revenue', true)
            ->sum('net_amount');

        $publishedListings = DB::table('marketplace_listings')
            ->where('status', 'published')
            ->whereNull('deleted_at')
            ->count();

        $apiRequests = DB::table('api_requests')
            ->whereNull('deleted_at')
            ->count();

        $successfulApiRequests = DB::table('api_requests')
            ->where('status', 'success')
            ->whereNull('deleted_at')
            ->count();

        $paidMarketplaceOrders = DB::table('marketplace_orders')
            ->where('payment_status', 'paid')
            ->whereNull('deleted_at')
            ->count();

        $marketplaceRevenue = DB::table('marketplace_orders')
            ->where('payment_status', 'paid')
            ->whereNull('deleted_at')
            ->sum('amount');

        $recentUsers = DB::table('users')
            ->latest('created_at')
            ->limit(5)
            ->get([
                'id',
                'name',
                'email',
                'created_at',
            ]);

        $recentActivity = DB::table('activity_events')
            ->whereNull('deleted_at')
            ->latest('created_at')
            ->limit(8)
            ->get([
                'id',
                'source_module',
                'event_type',
                'action',
                'user_id',
                'created_at',
            ]);


        /*
         * Income vs Expense dashboard data.
         */
        $incomeExpensePeriod = request('period', '30d');

        $periodDays = [
            '7d' => 7,
            '30d' => 30,
            '3m' => 90,
            '6m' => 180,
            '12m' => 365,
        ];

        if (!isset($periodDays[$incomeExpensePeriod])) {
            $incomeExpensePeriod = '30d';
        }

        $periodStart = now()
            ->copy()
            ->subDays($periodDays[$incomeExpensePeriod] - 1)
            ->startOfDay();

        $periodEnd = now()->endOfDay();

        $incomeRows = DB::table('revenue_events')
            ->where('revenue_owner', 'esubiz')
            ->where('is_platform_revenue', true)
            ->where('status', 'processed')
            ->whereNull('deleted_at')
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->selectRaw('DATE(created_at) as report_date, SUM(net_amount) as total')
            ->groupByRaw('DATE(created_at)')
            ->get()
            ->keyBy('report_date');

        $expenseRows = DB::table('expense_events')
            ->where('status', 'processed')
            ->whereBetween('occurred_at', [$periodStart, $periodEnd])
            ->selectRaw('DATE(occurred_at) as report_date, SUM(amount) as total')
            ->groupByRaw('DATE(occurred_at)')
            ->get()
            ->keyBy('report_date');

        $incomeExpenseChart = [];

        for (
            $date = $periodStart->copy();
            $date->lte($periodEnd);
            $date->addDay()
        ) {
            $key = $date->format('Y-m-d');

            $income = (float) ($incomeRows[$key]->total ?? 0);
            $expenses = (float) ($expenseRows[$key]->total ?? 0);

            $incomeExpenseChart[] = [
                'date' => $key,
                'label' => $date->format('M d'),
                'income' => $income,
                'expenses' => $expenses,
                'net' => $income - $expenses,
            ];
        }

        $periodIncome = collect($incomeExpenseChart)->sum('income');
        $periodExpenses = collect($incomeExpenseChart)->sum('expenses');
        $periodNet = $periodIncome - $periodExpenses;


        /*
         * Processed Esubiz expenses for the current month.
         * Expense events are debits and are intentionally kept
         * separate from platform revenue events.
         */
        $currentMonthExpenses = DB::table('expense_events')
            ->where('status', 'processed')
            ->whereBetween('occurred_at', [
                now()->startOfMonth(),
                now()->endOfMonth(),
            ])
            ->sum('amount');

        return view('admin.index', [
            'totalUsers' => $totalUsers,
            'developerAccounts' => $developerAccounts,
            'activeWebsites' => $activeWebsites,
            'totalWebsites' => $totalWebsites,
            'currentMonthRevenue' => $currentMonthRevenue,
            'totalRevenue' => $totalRevenue,
            'publishedListings' => $publishedListings,
            'apiRequests' => $apiRequests,
            'successfulApiRequests' => $successfulApiRequests,
            'paidMarketplaceOrders' => $paidMarketplaceOrders,
            'marketplaceRevenue' => $marketplaceRevenue,
            'recentUsers' => $recentUsers,
            'recentActivity' => $recentActivity,
        
            'incomeExpenseChart' => $incomeExpenseChart,
            'incomeExpensePeriod' => $incomeExpensePeriod,
            'periodIncome' => $periodIncome,
            'periodExpenses' => $periodExpenses,
            'periodNet' => $periodNet,
            'currentMonthExpenses' => $currentMonthExpenses,
            'centralDashboardNotices' =>
                app(\App\Services\DashboardNotices\CentralDashboardNoticeService::class)
                    ->forUser(request()->user()),]);
    }
}
