<?php

namespace App\Services\CentralApi;

use App\Models\Website;
use Illuminate\Http\Request;
use InvalidArgumentException;


/**
 * ================================================================
 * CHECKPOINT 7 — GENERIC CENTRAL SERVICE AUTHORIZATION
 * ================================================================
 *
 * Thin shared facade used by every Central Esubiz service.
 *
 * It does NOT:
 *
 * - charge credits
 * - execute AI
 * - send SMS
 * - send Email
 * - send WhatsApp
 * - redeem Gift Cards
 * - process payments
 *
 * It ONLY establishes:
 *
 * trusted website identity
 * + origin
 * + required service scope
 *
 * The actual service engine then performs entitlement/balance/
 * commercial checks separately.
 */
class CentralServiceAuthorizationService
{
    public function __construct(
        protected CentralWebsiteAuthorizationService $websites
    ) {
    }


    public function authorizeSaas(
        Website|int $website,
        string $scope,
        ?int $userId = null
    ): array {

        return $this->websites
            ->authorizeService(
                $scope,
                CentralWebsiteAuthorizationService::ORIGIN_SAAS,
                $website,
                $userId
            );
    }


    public function authorizeOffServer(
        Request $request,
        string $scope
    ): array {

        return $this->websites
            ->authorizeService(
                $scope,
                CentralWebsiteAuthorizationService::ORIGIN_OFF_SERVER,
                $request
            );
    }


    public function authorizeCentral(
        Website|int $website,
        string $scope,
        int $userId
    ): array {

        return $this->websites
            ->authorizeService(
                $scope,
                CentralWebsiteAuthorizationService::ORIGIN_CENTRAL,
                $website,
                $userId
            );
    }


    /**
     * Convenience scope methods.
     */

    public function aiSaas(
        Website|int $website,
        ?int $userId = null
    ): array {
        return $this->authorizeSaas(
            $website,
            \App\Support\CentralApi\CentralServiceScopeRegistry::AI_USE,
            $userId
        );
    }


    public function smsSaas(
        Website|int $website,
        ?int $userId = null
    ): array {
        return $this->authorizeSaas(
            $website,
            \App\Support\CentralApi\CentralServiceScopeRegistry::SMS_USE,
            $userId
        );
    }


    public function emailSaas(
        Website|int $website,
        ?int $userId = null
    ): array {
        return $this->authorizeSaas(
            $website,
            \App\Support\CentralApi\CentralServiceScopeRegistry::EMAIL_USE,
            $userId
        );
    }


    public function whatsappSaas(
        Website|int $website,
        ?int $userId = null
    ): array {
        return $this->authorizeSaas(
            $website,
            \App\Support\CentralApi\CentralServiceScopeRegistry::WHATSAPP_USE,
            $userId
        );
    }


    public function giftcardValidateSaas(
        Website|int $website,
        ?int $userId = null
    ): array {
        return $this->authorizeSaas(
            $website,
            \App\Support\CentralApi\CentralServiceScopeRegistry::GIFTCARD_VALIDATE,
            $userId
        );
    }


    public function giftcardRedeemSaas(
        Website|int $website,
        ?int $userId = null
    ): array {
        return $this->authorizeSaas(
            $website,
            \App\Support\CentralApi\CentralServiceScopeRegistry::GIFTCARD_REDEEM,
            $userId
        );
    }


    public function paymentsSaas(
        Website|int $website,
        ?int $userId = null
    ): array {
        return $this->authorizeSaas(
            $website,
            \App\Support\CentralApi\CentralServiceScopeRegistry::PAYMENTS_CONNECT,
            $userId
        );
    }


    public function smsOffServer(
        Request $request
    ): array {
        return $this->authorizeOffServer(
            $request,
            \App\Support\CentralApi\CentralServiceScopeRegistry::SMS_USE
        );
    }


    public function emailOffServer(
        Request $request
    ): array {
        return $this->authorizeOffServer(
            $request,
            \App\Support\CentralApi\CentralServiceScopeRegistry::EMAIL_USE
        );
    }


    public function whatsappOffServer(
        Request $request
    ): array {
        return $this->authorizeOffServer(
            $request,
            \App\Support\CentralApi\CentralServiceScopeRegistry::WHATSAPP_USE
        );
    }


    public function giftcardValidateOffServer(
        Request $request
    ): array {
        return $this->authorizeOffServer(
            $request,
            \App\Support\CentralApi\CentralServiceScopeRegistry::GIFTCARD_VALIDATE
        );
    }


    public function giftcardRedeemOffServer(
        Request $request
    ): array {
        return $this->authorizeOffServer(
            $request,
            \App\Support\CentralApi\CentralServiceScopeRegistry::GIFTCARD_REDEEM
        );
    }


    public function paymentsOffServer(
        Request $request
    ): array {
        return $this->authorizeOffServer(
            $request,
            \App\Support\CentralApi\CentralServiceScopeRegistry::PAYMENTS_CONNECT
        );
    }
}
