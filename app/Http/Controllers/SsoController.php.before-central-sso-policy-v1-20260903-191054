<?php

namespace App\Http\Controllers;

use App\Services\Sso\SsoService;
use App\Models\Website;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class SsoController extends Controller
{
    public function token(Request $request, SsoService $sso)
    {
        $clientId = $request->string('client_id')->toString();
        $clientSecret = $request->string('client_secret')->toString();
        $code = $request->string('code')->toString();

        $application = $sso->findApplication($clientId);

        abort_unless($application, 400, 'Invalid client.');

        abort_unless(
            $sso->validateClientSecret($application, $clientSecret),
            401,
            'Invalid client credentials.'
        );

        $authorization = $sso->consumeAuthorizationCode(
            $application,
            $code
        );

        abort_unless($authorization, 400, 'Invalid authorization code.');

        return response()->json([
            'access_token' => $authorization->access_token,
            'token_type' => 'Bearer',
            'expires_in' => 3600,
            'scopes' => $authorization->scopes,
        ]);
    }

    public function user(Request $request, SsoService $sso)
    {
        $authorizationHeader = $request->header('Authorization');

        abort_unless(
            $authorizationHeader &&
            str_starts_with($authorizationHeader, 'Bearer '),
            401,
            'Bearer token required.'
        );

        $accessToken = trim(substr($authorizationHeader, 7));

        $authorization = $sso->findByAccessToken($accessToken);

        abort_unless($authorization, 401, 'Invalid or expired access token.');

        $user = $authorization->user;

        abort_unless($user, 401, 'User not found.');

        $centralRole = $user->roles()
            ->whereHas('role', function ($query) {
                $query->where('is_active', true);
            })
            ->with('role')
            ->get()
            ->map(fn ($userRole) => $userRole->role?->slug)
            ->filter()
            ->first();

        $marketplaceRole = match ($centralRole) {
            'platform-admin' => 'admin',
            'developer' => 'developers',
            'user' => 'users',
            default => null,
        };

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $marketplaceRole,
            'central_role' => $centralRole,
            'scopes' => $authorization->scopes,
        ]);
    }

    /**
     * ESUBIZ_TENANT_SSO_START_V1
     *
     * Start central Esubiz authentication for a SaaS tenant website.
     */
    public function start(Request $request, SsoService $sso)
    {
        $host = strtolower($request->getHost());
        $baseDomain = 'esubiz.com';

        abort_unless(
            str_ends_with($host, '.' . $baseDomain),
            400,
            'Invalid website host.'
        );

        $subdomain = substr(
            $host,
            0,
            -strlen('.' . $baseDomain)
        );

        abort_unless(
            $subdomain !== ''
            && !str_contains($subdomain, '.'),
            400,
            'Invalid website subdomain.'
        );

        $website = Website::query()
            ->where('subdomain', $subdomain)
            ->where('status', 'active')
            ->first();

        abort_unless(
            $website,
            404,
            'Website not found.'
        );

        $application = $sso->findWebsiteApplication(
            (int) $website->id
        );

        abort_unless(
            $application,
            400,
            'Website SSO application not found.'
        );

        $redirectUri = $request->getScheme()
            . '://'
            . $request->getHost()
            . '/sso/callback';

        abort_unless(
            $sso->validateAuthorizationRequest(
                $application,
                $redirectUri
            ),
            400,
            'Website SSO callback is not registered.'
        );

        $state = \Illuminate\Support\Str::random(64);

        $request->session()->put(
            'tenant_cms_sso_state',
            $state
        );

        /*
         * Only request scopes already allowed for this application.
         * An empty scope is valid and avoids inventing permissions.
         */
        $scopes = $application
            ->applicationScopes()
            ->where('is_allowed', true)
            ->with('scope')
            ->get()
            ->pluck('scope.slug')
            ->filter()
            ->values()
            ->all();

        $query = [
            'client_id' => $application->client_id,
            'redirect_uri' => $redirectUri,
            'state' => $state,
        ];

        if (!empty($scopes)) {
            $query['scope'] = implode(' ', $scopes);
        }

        /*
         * IMPORTANT:
         * Authorization is performed on CENTRAL esubiz.com,
         * not on the tenant subdomain.
         */
        $authorizeUrl = 'https://esubiz.com/oauth/authorize'
            . '?'
            . http_build_query(
                $query,
                '',
                '&',
                PHP_QUERY_RFC3986
            );

        return redirect()->away($authorizeUrl);
    }


    public function authorize(Request $request, SsoService $sso)
    {
        $clientId = $request->string('client_id')->toString();
        $redirectUri = $request->string('redirect_uri')->toString();

        $requestedScopes = array_values(array_filter(
            preg_split('/\s+/', $request->string('scope')->toString())
        ));

        $application = $sso->findApplication($clientId);

        abort_unless(
            $application,
            400,
            'Invalid client.'
        );

        abort_unless(
            $sso->validateAuthorizationRequest(
                $application,
                $redirectUri
            ),
            400,
            'Invalid redirect URI.'
        );

        abort_unless(
            $sso->validateScopes(
                $application,
                $requestedScopes
            ),
            400,
            'Invalid scope.'
        );

        $authorization = $sso->createAuthorization(
            $application,
            $request->user()->id,
            $requestedScopes
        );

        $query = [
            'code' => $authorization->authorization_code,
        ];

        if ($request->filled('state')) {
            $query['state'] = $request->string('state')->toString();
        }

        $separator = str_contains($redirectUri, '?') ? '&' : '?';

        return redirect()->away(
            $redirectUri . $separator . http_build_query($query)
        );
    }

    /**
     * Handle the SSO callback for an Esubiz website.
     */
    public function callback(
        Request $request,
        SsoService $sso
    ) {
        $code = $request->string('code')->toString();

        abort_unless(
            $code !== '',
            400,
            'Authorization code required.'
        );

        /*
        |--------------------------------------------------------------------------
        | Resolve website from the current subdomain
        |--------------------------------------------------------------------------
        */

        $host = strtolower($request->getHost());

        $baseDomain = 'esubiz.com';

        abort_unless(
            str_ends_with($host, '.' . $baseDomain),
            400,
            'Invalid website host.'
        );

        $subdomain = substr(
            $host,
            0,
            -strlen('.' . $baseDomain)
        );

        abort_unless(
            $subdomain !== '' &&
            !str_contains($subdomain, '.'),
            400,
            'Invalid website subdomain.'
        );

        $website = Website::query()
            ->where('subdomain', $subdomain)
            ->where('status', 'active')
            ->first();

        abort_unless(
            $website,
            404,
            'Website not found.'
        );

        /*
        |--------------------------------------------------------------------------
        | Find the SSO application registered for this website
        |--------------------------------------------------------------------------
        */

        $application = $sso->findWebsiteApplication($website->id);

        abort_unless(
            $application,
            400,
            'Website SSO application not found.'
        );

        /*
        |--------------------------------------------------------------------------
        | Validate and consume the one-time authorization code
        |--------------------------------------------------------------------------
        */

        $authorization = $sso->consumeAuthorizationCode(
            $application,
            $code
        );

        abort_unless(
            $authorization,
            400,
            'Invalid or expired authorization code.'
        );

        $user = $authorization->user;

        abort_unless(
            $user,
            401,
            'User not found.'
        );

        /*
        |--------------------------------------------------------------------------
        | Validate Tenant CMS SSO State
        |--------------------------------------------------------------------------
        */

        $expectedState = $request->session()->pull(
            'tenant_cms_sso_state'
        );

        $receivedState = $request->string(
            'state'
        )->toString();

        if ($expectedState !== null) {
            abort_unless(
                $receivedState !== ''
                && hash_equals(
                    (string) $expectedState,
                    $receivedState
                ),
                419,
                'Invalid SSO state.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Establish Central + Tenant CMS Session
        |--------------------------------------------------------------------------
        */

        Auth::login($user);

        /*
         * ESUBIZ_CORE_LOCAL_SSO_USER_V1
         *
         * Every authenticated website user must resolve to this
         * Core installation's own site_users record.
         *
         * tenant_cms_user_id therefore always means:
         *     local site_users.id
         *
         * The central Esubiz user ID is retained separately.
         * This keeps the Core profile/account model portable for:
         *
         * - SaaS hosted websites
         * - custom domains
         * - off-server Core installations
         */
        $tenantDatabaseService = app(
            \App\Services\Website\WebsiteTenantDatabaseService::class
        );

        $tenantDatabaseService->connect($website);

        $tenantDb = $tenantDatabaseService->connection();

        $ssoEmail = strtolower(
            trim((string) $user->email)
        );

        $localUser = $tenantDb
            ->table('site_users')
            ->whereRaw(
                'LOWER(email) = ?',
                [$ssoEmail]
            )
            ->first();

        if ($localUser) {
            $localUserId = (int) $localUser->id;

            $tenantDb
                ->table('site_users')
                ->where('id', $localUserId)
                ->update([
                    'name' =>
                        trim(
                            (string) (
                                $user->name
                                ?? $localUser->name
                                ?? $ssoEmail
                            )
                        ),
                    'email' => $ssoEmail,
                    'is_active' => true,
                    'last_login_at' => now(),
                    'updated_at' => now(),
                ]);
        } else {
            /*
             * SSO-only users receive an unusable random local password.
             * A proper local password can later be established through
             * the Core password/reset flow.
             */
            $localUserId = (int) $tenantDb
                ->table('site_users')
                ->insertGetId([
                    'name' =>
                        trim(
                            (string) (
                                $user->name
                                ?? $ssoEmail
                            )
                        ),
                    'email' => $ssoEmail,
                    'phone' => null,
                    'password' =>
                        \Illuminate\Support\Facades\Hash::make(
                            \Illuminate\Support\Str::random(64)
                        ),
                    'is_active' => true,
                    'last_login_at' => now(),
                    'remember_token' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
        }

        $request->session()->regenerate();

        $request->session()->put([
            'tenant_cms_authenticated' => true,
            'tenant_cms_website_id' =>
                (int) $website->id,

            /*
             * Core-local identity.
             */
            'tenant_cms_user_id' =>
                $localUserId,

            /*
             * Central identity retained separately.
             */
            'tenant_cms_central_user_id' =>
                (int) $user->id,

            'tenant_cms_authenticated_via' =>
                'esubiz_sso',

            /*
             * ESUBIZ_MULTI_SITE_SSO_CALLBACK_SESSION_V1
             */
            "tenant_cms_sites.{$website->id}.authenticated" =>
                true,

            "tenant_cms_sites.{$website->id}.user_id" =>
                $localUserId,

            "tenant_cms_sites.{$website->id}.central_user_id" =>
                (int) $user->id,

            "tenant_cms_sites.{$website->id}.auth_method" =>
                'esubiz_sso',
        ]);

        $request->session()->forget([
            'tenant_cms_sso_website_id',
            'tenant_cms_sso_destination',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Return Directly To Website CMS
        |--------------------------------------------------------------------------
        */

        return redirect()->to(
            'https://'
            . $website->subdomain
            . '.esubiz.com/admin/dashboard'
        );
    }

}
