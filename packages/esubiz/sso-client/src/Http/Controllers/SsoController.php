<?php

namespace Esubiz\SsoClient\Http\Controllers;

use Esubiz\SsoClient\SsoClient;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use RuntimeException;

class SsoController extends Controller
{
    public function login(Request $request, SsoClient $sso)
    {
        return $sso->login(
            $request->string('state')->toString() ?: null
        );
    }

    public function callback(Request $request, SsoClient $sso)
    {
        $code = $request->string('code')->toString();
        $state = $request->string('state')->toString();

        if ($code === '') {
            throw new RuntimeException('Missing SSO authorization code.');
        }

        if ($state === '') {
            throw new RuntimeException('Missing SSO state.');
        }

        $token = $sso->callback($code, $state);

        $accessToken = $token['access_token'] ?? null;

        if (!$accessToken) {
            throw new RuntimeException(
                'Esubiz SSO did not return an access token.'
            );
        }

        $user = $sso->user($accessToken);

        session([
            'esubiz_sso_access_token' => $accessToken,
            'esubiz_sso_user' => $user,
        ]);

        return redirect()->intended(
            config('esubiz-sso.success_redirect', '/')
        );
    }

    public function user()
    {
        return response()->json(
            session('esubiz_sso_user')
        );
    }

    public function logout(SsoClient $sso)
    {
        $sso->logout();

        return redirect(
            config('esubiz-sso.logout_redirect', '/')
        );
    }
}
