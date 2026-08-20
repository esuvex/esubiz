<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ApiSubscription;
use App\Models\Wallet;
use App\Services\WebsiteDraftService;

class DashboardController extends Controller
{
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

        return view('user.index', [
            'draft' => $draft,
            'websites' => $websites,
            'failedWebsites' => $failedWebsites,
            'websiteCount' => $websiteCount,
            'draftCount' => $draftCount,
            'subscriptionCount' => $subscriptionCount,
            'walletBalance' => $walletBalance,
        ]);
    }
}
