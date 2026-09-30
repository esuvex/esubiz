<?php

namespace App\Http\Controllers;

use App\Models\Website;
use App\Services\Marketplace\CreditVolumeCheckoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MarketplaceCreditVolumeController extends Controller
{
    public function index(Request $request, string $deployment): View
    {
        abort_unless(in_array($deployment, ['saas', 'off_server'], true), 404);

        $websites = Website::withoutGlobalScopes()
            ->where('deployment_type', $deployment)
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->where(function ($query) use ($request) {
                $query->where('owner_id', $request->user()->id)
                    ->orWhere('developer_id', $request->user()->id);
            })
            ->orderBy('name')
            ->get(['id', 'name', 'domain', 'subdomain']);

        $selectedWebsiteId = (int) $request->query('website_id', 0);
        $selectedWebsiteId = $websites->contains('id', $selectedWebsiteId)
            ? $selectedWebsiteId
            : 0;

        $selectedCreditType = (string) $request->query('credit_type', '');
        if (!in_array($selectedCreditType, [
            'ai_credits', 'sms_credits', 'email_credits',
            'whatsapp_credits', 'kyc_credits',
        ], true)) {
            $selectedCreditType = '';
        }

        return view('marketplace.credit-volume', [
            'selectedWebsiteId' => $selectedWebsiteId,
            'selectedCreditType' => $selectedCreditType,
            'deployment' => $deployment,
            'websites' => $websites,
            'creditTypes' => [
                'ai_credits' => 'AI credits',
                'sms_credits' => 'SMS credits',
                'email_credits' => 'Email credits',
                'whatsapp_credits' => 'WhatsApp credits',
                'kyc_credits' => 'KYC credits',
            ],
            'currency' => app(
                \App\Services\Platform\CentralSiteSettingsService::class
            )->primaryCurrency(),
        ]);
    }

    public function saasHandoff(
        Request $request,
        CreditVolumeCheckoutService $checkouts
    ) {
        abort_unless($request->hasValidRelativeSignature(), 403);

        $data = $request->validate([
            'website_id' => ['required', 'integer', 'min:1'],
            'credit_type' => ['required', 'in:ai_credits,email_credits'],
            'quantity' => ['required', 'integer', 'min:1', 'max:100000000'],
        ]);

        $website = Website::withoutGlobalScopes()
            ->where('id', (int) $data['website_id'])
            ->where('deployment_type', 'saas')
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->firstOrFail();

        abort_unless($website->isRegistryActive(), 403, 'This website is unavailable.');

        $buyerId = (int) $website->owner_id;
        abort_unless($buyerId > 0, 403, 'This website has no billing account.');

        abort_unless(
            \App\Models\User::query()->whereKey($buyerId)->exists(),
            403,
            'This website billing account is unavailable.'
        );


        /*
         * The signed URL was issued after Core authenticated its site admin.
         * The Central account paying may differ from the website owner.
         * CreditVolumeCheckoutService records this payer as the buyer and
         * disables Wallet for a SaaS website-origin checkout.
         */
        try {
            $orderId = $checkouts->create(
                $website,
                $buyerId,
                $data['credit_type'],
                (int) $data['quantity'],
                'saas_website',
                'user'
            );
        } catch (\InvalidArgumentException|\RuntimeException $exception) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'quantity' => $exception->getMessage(),
            ]);
        }

        $pass = app(
            \App\Services\Marketplace\CoreCheckoutPassService::class
        )->issue($orderId, (int) $website->id, 'saas_website');

        return redirect()->route('marketplace.checkout', [
            'order' => $orderId,
            'core_checkout_pass' => $pass,
        ]);
    }

    public function store(
        Request $request,
        string $deployment,
        CreditVolumeCheckoutService $checkouts
    ) {
        abort_unless(in_array($deployment, ['saas', 'off_server'], true), 404);

        $data = $request->validate([
            'website_id' => ['required', 'integer', 'min:1'],
            'credit_type' => [
                'required',
                'in:ai_credits,sms_credits,email_credits,whatsapp_credits,kyc_credits',
            ],
            'quantity' => ['required', 'integer', 'min:1', 'max:100000000'],
        ]);

        $website = Website::withoutGlobalScopes()
            ->where('id', (int) $data['website_id'])
            ->where('deployment_type', $deployment)
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->where(function ($query) use ($request) {
                $query->where('owner_id', $request->user()->id)
                    ->orWhere('developer_id', $request->user()->id);
            })
            ->firstOrFail();

        try {
            $orderId = $checkouts->create(
                $website,
                (int) $request->user()->id,
                $data['credit_type'],
                (int) $data['quantity'],
                'central_account',
                (string) session('account_mode', 'user')
            );
        } catch (\InvalidArgumentException|\RuntimeException $exception) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'quantity' => $exception->getMessage(),
            ]);
        }

        return redirect()->route('marketplace.checkout', ['order' => $orderId]);
    }
}
