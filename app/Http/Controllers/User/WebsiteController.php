<?php

namespace App\Http\Controllers\User;

use App\Services\Sso\SsoService;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class WebsiteController extends Controller
{
    /**
     * Display the user's websites.
     */
    public function index(): View
    {
        $websites = auth()->user()
            ->websites()
            ->latest()
            ->get();

        return view('user.websites.index', [
            'websites' => $websites,
        ]);
    }

    public function openDashboard(
        int $website,
        SsoService $sso
    ) {
        $website = \App\Models\Website::query()
            ->where('id', $website)
            ->where('owner_id', auth()->id())
            ->firstOrFail();

        abort_unless(
            $website->status === 'active'
            && (bool) $website->user_enabled,
            422,
            'This website is currently inactive.'
        );

        abort_unless(
            !empty($website->subdomain),
            422,
            'This website does not have an Esubiz subdomain.'
        );

        $application = $sso->findWebsiteApplication(
            (int) $website->id
        );

        abort_unless(
            $application,
            422,
            'Website SSO is not configured.'
        );

        $redirectUri =
            'https://'
            . $website->subdomain
            . '.esubiz.com/sso/callback';

        $state =
            'website-dashboard-'
            . $website->id
            . '-'
            . \Illuminate\Support\Str::random(24);

        session([
            'tenant_cms_sso_state' => $state,
            'tenant_cms_sso_website_id' => (int) $website->id,
            'tenant_cms_sso_destination' => '/admin/dashboard',
        ]);

        $url =
            'https://esubiz.com/oauth/authorize?'
            . http_build_query([
                'client_id' =>
                    $application->client_id,

                'redirect_uri' =>
                    $redirectUri,

                'state' =>
                    $state,
            ]);

        return redirect()->away($url);
    }



    public function dashboard(\App\Models\Website $website)
    {

        /*
         * TENANT-CMS-CENTRAL-LOGIN-RETURN
         *
         * A tenant owner may arrive here from tenant /admin
         * without an active central Esubiz session.
         * Preserve this URL through central authentication.
         */
        if (!auth()->check()) {
            session([
                'url.intended' => request()->fullUrl(),
            ]);

            return redirect()->route('login');
        }


        $user = auth()->user();

        abort_unless($user, 401);

        /*
         * Only the website owner may use this automatic
         * Website Dashboard SSO entry.
         */
        abort_unless(
            (int) $website->owner_id === (int) $user->id,
            403,
            'You do not own this website.'
        );

        abort_unless(
            $website->status === 'active'
            && (bool) $website->user_enabled,
            403,
            'This website is currently inactive.'
        );

        abort_unless(
            !empty($website->subdomain),
            422,
            'This website does not have a tenant subdomain.'
        );

        /*
         * Short-lived signed SSO payload.
         *
         * The tenant /admin endpoint will validate this token,
         * establish the tenant CMS owner session and redirect
         * to /admin/dashboard.
         */
        $expires = now()->addMinutes(2)->timestamp;

        $payload = implode('|', [
            $website->id,
            $user->id,
            $expires,
        ]);

        $signature = hash_hmac(
            'sha256',
            $payload,
            config('app.key')
        );

        $scheme = app()->environment('local') ? 'http' : 'https';

        $tenantUrl =
            $scheme
            . '://'
            . $website->subdomain
            . '.esubiz.com/admin'
            . '?sso=1'
            . '&website=' . urlencode((string) $website->id)
            . '&user=' . urlencode((string) $user->id)
            . '&expires=' . urlencode((string) $expires)
            . '&signature=' . urlencode($signature);

        return redirect()->away($tenantUrl);
    }


}
