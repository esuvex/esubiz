<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CentralApi\CentralServiceAuthorizationService;
use App\Services\Core\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;


/**
 * ================================================================
 * CHECKPOINT 8 — OFF-SERVER CENTRAL COMMUNICATION API
 * ================================================================
 *
 * Shared Central execution endpoints for:
 *
 * - SMS
 * - Email
 * - WhatsApp
 *
 * Security:
 *
 * bearer token
 * -> current installation
 * -> Central website
 * -> active domain-bound licence
 * -> required service scope
 * -> Central balance preflight
 * -> provider
 * -> Central debit only after successful delivery
 *
 * The remote Core NEVER supplies authoritative website_id.
 */
class OffServerCommunicationController extends Controller
{
    public function sms(
        Request $request,
        CentralServiceAuthorizationService $authorization,
        NotificationService $notifications
    ): JsonResponse {

        $identity =
            $authorization->smsOffServer(
                $request
            );


        return $this->send(
            $request,
            $notifications,
            $identity,
            'sms'
        );
    }


    public function email(
        Request $request,
        CentralServiceAuthorizationService $authorization,
        NotificationService $notifications
    ): JsonResponse {

        $identity =
            $authorization->emailOffServer(
                $request
            );


        return $this->send(
            $request,
            $notifications,
            $identity,
            'email'
        );
    }


    public function whatsapp(
        Request $request,
        CentralServiceAuthorizationService $authorization,
        NotificationService $notifications
    ): JsonResponse {

        $identity =
            $authorization->whatsappOffServer(
                $request
            );


        return $this->send(
            $request,
            $notifications,
            $identity,
            'whatsapp'
        );
    }


    protected function send(
        Request $request,
        NotificationService $notifications,
        array $identity,
        string $type
    ): JsonResponse {

        $data =
            $request->validate([
                /*
                 * Existing Central NotificationService uses template
                 * slugs rather than allowing the external Core to
                 * inject arbitrary executable provider payloads.
                 */
                'template' => [
                    'required',
                    'string',
                    'max:191',
                ],

                'recipient' => [
                    'required',
                    'string',
                    'max:255',
                ],

                'variables' => [
                    'nullable',
                    'array',
                ],

                'reference' => [
                    'nullable',
                    'string',
                    'max:191',
                ],
            ]);


        $websiteId =
            (int) (
                $identity['website_id']
                ?? 0
            );


        if ($websiteId < 1) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Central website identity could not be resolved.',
            ], 403);
        }


        /*
         * The authoritative website ID comes from the bearer-token
         * authorization result above.
         *
         * No request website_id is accepted.
         */
        try {

            $notification =
                $notifications->send(
                    $type,
                    $data['template'],
                    $data['recipient'],
                    $data['variables']
                        ?? [],
                    $identity['owner_id']
                        ?? $identity['user_id']
                        ?? null,
                    null,
                    $data['reference']
                        ?? null,
                    null,
                    $websiteId
                );


            return response()->json([
                'success' =>
                    true,

                'notification' => [
                    'id' =>
                        $notification->id,

                    'uuid' =>
                        $notification->uuid,

                    'reference' =>
                        $notification->reference,

                    'status' =>
                        $notification->status,

                    'channel' =>
                        $type,
                ],

                'website' => [
                    'website_id' =>
                        $websiteId,

                    'website_uuid' =>
                        $identity['website_uuid']
                        ?? null,

                    'deployment_type' =>
                        $identity['deployment_type']
                        ?? null,

                    'registered_domain' =>
                        $identity['registered_domain']
                        ?? null,
                ],

                'billing' => [
                    'authority' =>
                        'central',

                    'service' =>
                        $type,

                    'charged_after_success' =>
                        true,
                ],
            ]);

        } catch (Throwable $e) {

            report($e);


            /*
             * NotificationService only consumes credits AFTER its
             * driver succeeds.
             *
             * Therefore provider/configuration failures reach here
             * without consuming Central credits.
             */
            return response()->json([
                'success' =>
                    false,

                'message' =>
                    $e->getMessage(),

                'website' => [
                    'website_id' =>
                        $websiteId,

                    'website_uuid' =>
                        $identity['website_uuid']
                        ?? null,
                ],

                'billing' => [
                    'charged' =>
                        false,
                ],
            ], 422);
        }
    }
}
