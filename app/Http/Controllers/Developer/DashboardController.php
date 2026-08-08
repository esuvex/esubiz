<?php

namespace App\Http\Controllers\Developer;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
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

        return view('developer.index', [
            'websiteCount' => $websiteCount,
            'totalEarnings' => $totalEarnings,
            'pendingEarnings' => $pendingEarnings,
            'approvedEarnings' => $approvedEarnings,
            'paidEarnings' => $paidEarnings,
            'recentCommissions' => $recentCommissions,
        ]);
    }
}
