<?php

namespace App\Http\Controllers;

use App\Services\Sso\SsoService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ManagedAuthController extends Controller
{
    /*
     * ESUBIZ_MANAGED_AUTH_BROKER_V1
     *
     * Central Esubiz-managed external authentication broker.
     *
     * Core installations authenticate themselves through the
     * existing ApiApplication client registry.
     *
     * Provider-specific OAuth transport is intentionally added
     * separately so no provider endpoint, credential, scope or
     * callback contract is invented here.
     */

    private const PROVIDERS = [
        'google',
        'facebook',
        'instagram',
        'tiktok',
        'x',
    ];


    public function start(
        Request $request,
        string $provider,
        SsoService $sso
    ) {
        $provider = strtolower(
            trim($provider)
        );

        abort_unless(
            in_array(
                $provider,
                self::PROVIDERS,
                true
            ),
            404
        );

        $clientId = $request
            ->string('client_id')
            ->toString();

        $redirectUri = $request
            ->string('redirect_uri')
            ->toString();

        $state = $request
            ->string('state')
            ->toString();

        abort_if(
            $clientId === ''
            || $redirectUri === ''
            || $state === '',
            400,
            'Invalid managed authentication request.'
        );

        $application =
            $sso->findApplication(
                $clientId
            );

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

        /*
         * The originating Core state is never used as the
         * provider OAuth state directly.
         *
         * Central owns its own random state and keeps the Core
         * state server-side until provider authentication
         * completes.
         */
        $brokerState = Str::random(64);

        $request->session()->put(
            'esubiz_managed_auth.' . $brokerState,
            [
                'provider' =>
                    $provider,

                'api_application_id' =>
                    (int) $application->id,

                'client_id' =>
                    $application->client_id,

                'redirect_uri' =>
                    $redirectUri,

                'client_state' =>
                    $state,

                'created_at' =>
                    now()->timestamp,
            ]
        );

        /*
         * Provider transport is the next layer.
         *
         * Until a provider is configured centrally, fail safely
         * instead of falling back to Core-local credentials.
         */
        return response()->json(
            [
                'message' =>
                    ucfirst($provider)
                    . ' Esubiz-managed authentication '
                    . 'is not configured yet.',
            ],
            503
        );
    }


    /*
     * Provider callbacks terminate centrally.
     *
     * The actual provider-code exchange is intentionally not
     * implemented until that provider's Central credentials and
     * exact OAuth contract are configured.
     */
    public function callback(
        Request $request,
        string $provider
    ) {
        $provider = strtolower(
            trim($provider)
        );

        abort_unless(
            in_array(
                $provider,
                self::PROVIDERS,
                true
            ),
            404
        );

        return response()->json(
            [
                'message' =>
                    ucfirst($provider)
                    . ' Esubiz-managed callback '
                    . 'is not configured yet.',
            ],
            503
        );
    }
}
