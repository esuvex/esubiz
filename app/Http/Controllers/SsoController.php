<?php

namespace App\Http\Controllers;

use App\Services\Sso\SsoService;
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
            'authorization' => $authorization->uuid,
            'user_id' => $authorization->user_id,
            'scopes' => $authorization->scopes,
        ]);
    }

    public function user(Request $request)
    {
        $authorization = \App\Models\ApiAuthorization::query()
            ->where('uuid', $request->string('authorization')->toString())
            ->where('is_revoked', false)
            ->where('expires_at', '>', now())
            ->with('user')
            ->first();

        abort_unless($authorization, 401, 'Invalid authorization.');

        $user = $authorization->user;

        abort_unless($user, 401, 'User not found.');

        return response()->json([
            'id' => $user->id,
            'esubiz_user_id' => $user->esubiz_user_id,
            'name' => $user->name,
            'email' => $user->email,
            'scopes' => $authorization->scopes,
        ]);
    }

    public function authorize(Request $request, SsoService $sso)
    {
        $clientId = $request->string('client_id')->toString();
        $redirectUri = $request->string('redirect_uri')->toString();

        $application = $sso->findApplication($clientId);

        abort_unless($application, 400, 'Invalid client.');

        abort_unless(
            $sso->validateAuthorizationRequest(
                $application,
                $redirectUri
            ),
            400,
            'Invalid redirect URI.'
        );

        return response()->json([
            'client_id' => $application->client_id,
            'name' => $application->name,
            'redirect_uri' => $redirectUri,
        ]);
    }
}
