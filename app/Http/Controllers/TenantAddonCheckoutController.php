<?php

namespace App\Http\Controllers;

use App\Models\Website;
use App\Services\Marketplace\SaasCheckoutLinkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TenantAddonCheckoutController extends Controller
{
    /**
     * ESUBIZ_UNIVERSAL_ADDON_CHECKOUT_HANDOFF_V1
     *
     * Convert a centrally-authorized Add-on selection into the
     * existing signed SaaS Marketplace checkout URL.
     *
     * Browser input is never trusted for price, deployment type,
     * entitlement or checkout destination.
     */
    public function create(
        Request $request,
        Website $website,
        SaasCheckoutLinkService $checkout
    ): JsonResponse {
        $validated = $request->validate([
            'addon_id' => [
                'required',
                'integer',
                'min:1',
            ],

            'return_url' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'return_area' => [
                'nullable',
                'string',
                'max:100',
            ],
        ]);

        /*
         * Website ownership/access remains governed by the tenant
         * route middleware. This endpoint only performs the checkout
         * handoff for that resolved website.
         */

        $addon = DB::table('core_addons')
            ->where(
                'id',
                (int) $validated['addon_id']
            )
            ->where('is_active', 1)
            ->where('saas_available', 1)
            ->first();

        if (!$addon) {
            abort(
                404,
                'This Add-on is not available for SaaS checkout.'
            );
        }

        /*
         * Product type is server-controlled.
         *
         * The browser supplies only the selected central Add-on ID.
         */
        try {
            $url = $checkout->create(
                $website,
                'addon',
                (int) $addon->id,
                $validated['return_url'] ?? null,
                $validated['return_area'] ?? null
            );
        } catch (RuntimeException $exception) {
            return response()->json(
                [
                    'message' =>
                        $exception->getMessage(),
                ],
                422
            );
        }

        return response()->json([
            'url' => $url,
        ]);
    }
}
