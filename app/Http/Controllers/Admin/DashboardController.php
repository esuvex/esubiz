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
            ->sum('net_amount');

        $totalRevenue = DB::table('revenue_events')
            ->where('status', 'processed')
            ->where('currency', 'NGN')
            ->whereNull('deleted_at')
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
        ]);
    }
}
