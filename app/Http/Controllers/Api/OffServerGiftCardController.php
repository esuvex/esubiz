<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CentralApi\CentralServiceAuthorizationService;
use App\Services\Core\GiftCardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;


/**
 * ================================================================
 * CHECKPOINT 7 — OFF-SERVER GIFT CARD API
 * ================================================================
 *
 * This controller does NOT create another Gift Card engine.
 *
 * Central GiftCardService remains authoritative.
 *
 * Security chain:
 *
 * bearer token
 * -> installation
 * -> Central website
 * -> active licence
 * -> required scope
 * -> GiftCardService
 */
class OffServerGiftCardController extends Controller
{
    public function validateCard(
        Request $request,
        CentralServiceAuthorizationService $authorization,
        GiftCardService $giftCards
    ): JsonResponse {

        $identity =
            $authorization
                ->giftcardValidateOffServer(
                    $request
                );


        $data =
            $request->validate([
                'code' =>
                    [
                        'required',
                        'string',
                        'max:100',
                    ],
            ]);


        try {

            $result =
                $giftCards->validateCard(
                    $data['code']
                );


            return response()->json([
                'success' =>
                    (bool) (
                        $result['valid']
                        ?? false
                    ),

                'validation' =>
                    $result,

                'website' => [
                    'website_id' =>
                        $identity['website_id']
                        ?? null,

                    'website_uuid' =>
                        $identity['website_uuid']
                        ?? null,

                    'deployment_type' =>
                        $identity['deployment_type']
                        ?? null,
                ],
            ]);

        } catch (Throwable $e) {

            report($e);

            return response()->json([
                'success' => false,
                'message' =>
                    'Unable to validate the gift card.',
            ], 422);
        }
    }


    public function redeem(
        Request $request,
        CentralServiceAuthorizationService $authorization,
        GiftCardService $giftCards
    ): JsonResponse {

        $identity =
            $authorization
                ->giftcardRedeemOffServer(
                    $request
                );


        $data =
            $request->validate([
                'code' =>
                    [
                        'required',
                        'string',
                        'max:100',
                    ],

                'amount' =>
                    [
                        'required',
                        'numeric',
                        'min:0.01',
                    ],

                'reference' =>
                    [
                        'nullable',
                        'string',
                        'max:191',
                    ],
            ]);


        /*
         * --------------------------------------------------------
         * IDEMPOTENCY
         * --------------------------------------------------------
         *
         * If the external Core supplies a reference, never allow
         * that same website/reference pair to redeem twice.
         *
         * Existing GiftCardService remains responsible for the
         * actual card balance mutation.
         */
        $reference =
            trim(
                (string) (
                    $data['reference']
                    ?? ''
                )
            );


        if ($reference !== '') {

            $existing =
                DB::table(
                    'gift_card_transactions'
                )
                    ->where(
                        'reference',
                        $reference
                    )
                    ->first();


            if ($existing) {

                return response()->json([
                    'success' => true,
                    'already_processed' => true,
                    'reference' =>
                        $reference,

                    'website' => [
                        'website_id' =>
                            $identity['website_id']
                            ?? null,

                        'website_uuid' =>
                            $identity['website_uuid']
                            ?? null,
                    ],
                ]);
            }
        }


        try {

            $result =
                $giftCards->redeem(
                    $data['code'],
                    (float) $data['amount'],
                    $reference !== ''
                        ? $reference
                        : null
                );


            return response()->json([
                'success' => true,
                'already_processed' => false,
                'redemption' =>
                    $result,

                'website' => [
                    'website_id' =>
                        $identity['website_id']
                        ?? null,

                    'website_uuid' =>
                        $identity['website_uuid']
                        ?? null,

                    'deployment_type' =>
                        $identity['deployment_type']
                        ?? null,
                ],
            ]);

        } catch (Throwable $e) {

            report($e);

            return response()->json([
                'success' => false,
                'message' =>
                    'Gift card redemption failed.',
            ], 422);
        }
    }
}
