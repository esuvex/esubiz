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
        | Establish the website session
        |--------------------------------------------------------------------------
        */

        Auth::login($user);

        $request->session()->regenerate();

        /*
        |--------------------------------------------------------------------------
        | Return to the website
        |--------------------------------------------------------------------------
        */

        return redirect()->to(
            'https://' . $website->subdomain . '.esubiz.com/'
        );
    }

}
