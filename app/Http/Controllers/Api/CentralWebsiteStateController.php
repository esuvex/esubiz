<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Website;
use App\Services\CentralApi\CentralServiceAuthorizationService;
use App\Services\CentralApi\CentralWebsiteDetailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


/**
 * ================================================================
 * CHECKPOINT 10 — CENTRAL WEBSITE PRODUCT-STATE READ API
 * ================================================================
 *
 * One canonical read contract for:
 *
 * - SaaS websites
 * - off-server Core installations
 *
 * State includes:
 *
 * - Central website identity
 * - AI / SMS / Email / WhatsApp balances
 * - purchased entitlements
 * - active add-ons
 * - selected theme
 * - installed modules
 * - licence / deployment state
 *
 * The client never supplies authoritative website_id for off-server.
 */
class CentralWebsiteStateController extends Controller
{
    /**
     * Off-server Core state.
     *
     * Authority:
     *
     * bearer
     * -> current installation
     * -> Central website
     * -> active licence
     * -> website.identity scope
     */
    public function offServer(
        Request $request,
        CentralServiceAuthorizationService $authorization,
        CentralWebsiteDetailService $details
    ): JsonResponse {

        $identity =
            $authorization->authorizeOffServer(
                $request,
                'website.identity'
            );


        $website =
            Website::query()
                ->findOrFail(
                    (int) $identity['website_id']
                );


        return response()->json(
            $this->payload(
                $website,
                $details,
                $identity
            )
        );
    }


    /**
     * SaaS website state.
     *
     * Central caller supplies a trusted Website model through the
     * SaaS application context, never arbitrary external authority.
     */
    public function saas(
        Request $request,
        Website $website,
        CentralServiceAuthorizationService $authorization,
        CentralWebsiteDetailService $details
    ): JsonResponse {

        $identity =
            $authorization->authorizeSaas(
                $website,
                'website.identity',
                $request->user()?->id
            );


        abort_unless(
            (int) $identity['website_id']
            === (int) $website->id,
            403,
            'Website identity mismatch.'
        );


        return response()->json(
            $this->payload(
                $website,
                $details,
                $identity
            )
        );
    }


    protected function payload(
        Website $website,
        CentralWebsiteDetailService $details,
        array $identity
    ): array {

        $state =
            $details->get(
                $website
            );


        return [
            'success' =>
                true,

            'authority' =>
                'central',

            'identity_source' =>
                $identity['identity_source']
                ?? null,

            'website' =>
                $state['website'],

            'credits' =>
                $state['credits'],

            'addons' =>
                $state['addons'],

            'theme' =>
                $state['theme'],

            'modules' =>
                $state['modules'],

            'licence' =>
                $state['licence'],

            'entitlements' =>
                $state['entitlements'],

            'security' => [
                'local_credit_authority' =>
                    false,

                'local_entitlement_authority' =>
                    false,

                'central_authoritative' =>
                    true,
            ],
        ];
    }
}
