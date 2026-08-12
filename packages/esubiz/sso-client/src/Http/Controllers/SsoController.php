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

        $authConfig = config('esubiz-sso.local_auth', []);

        if (($authConfig['enabled'] ?? false) === true) {
            $modelClass = $authConfig['model'] ?? null;
            $identityField = $authConfig['identity_field'] ?? 'esubiz_user_id';

            if (!$modelClass) {
                throw new RuntimeException(
                    'Esubiz SSO local authentication model is not configured.'
                );
            }

            $localUser = $modelClass::query()
                ->where($identityField, $user['id'])
                ->first();

            if (!$localUser) {
                $localUser = new $modelClass();
                $localUser->{$identityField} = $user['id'];
            }

            $localUser->name = $user['name'] ?? $localUser->name;
            $localUser->email = $user['email'] ?? $localUser->email;

            if (isset($user['role']) && ($authConfig['role_field'] ?? null)) {
                $localUser->{$authConfig['role_field']} = $user['role'];
            }

            if (!$localUser->exists && empty($localUser->password)) {
                $localUser->password = \Illuminate\Support\Str::random(64);
            }

            $localUser->save();

            \Illuminate\Support\Facades\Auth::login($localUser);
            $request->session()->regenerate();
        }

        $role = $user['role'] ?? null;

        $roleRedirects = config('esubiz-sso.success_redirects', []);

        $successRedirect = $role && isset($roleRedirects[$role])
            ? $roleRedirects[$role]
            : config('esubiz-sso.success_redirect', '/');

        return redirect()->to($successRedirect);
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

        \Illuminate\Support\Facades\Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect(
            config('esubiz-sso.logout_redirect', '/')
        );
    }
}
