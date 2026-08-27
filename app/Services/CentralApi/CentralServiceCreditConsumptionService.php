<?php

namespace App\Services\CentralApi;

use App\Models\Website;
use App\Services\Credits\WebsiteCreditService;
use InvalidArgumentException;


/**
 * ================================================================
 * CHECKPOINT 8 — GENERIC CENTRAL SERVICE CREDIT CONSUMPTION
 * ================================================================
 *
 * Shared billing facade for:
 *
 * - AI
 * - SMS
 * - Email
 * - WhatsApp
 *
 * Identity authorization happens before this service is called.
 *
 * This service only handles authoritative Central balances and
 * idempotent consumption.
 */
class CentralServiceCreditConsumptionService
{
    public function __construct(
        protected WebsiteCreditService $credits
    ) {
    }


    public function balance(
        Website|int $website,
        string $service
    ): float {

        $websiteId =
            $website instanceof Website
                ? (int) $website->id
                : (int) $website;


        return $this->credits
            ->centralBalance(
                $websiteId,
                $this->creditType(
                    $service
                )
            );
    }


    public function has(
        Website|int $website,
        string $service,
        float $amount
    ): bool {

        return $this->balance(
            $website,
            $service
        ) >= $amount;
    }


    public function consume(
        Website|int $website,
        string $service,
        float $amount,
        string $requestKey,
        array $context = []
    ): array {

        $websiteId =
            $website instanceof Website
                ? (int) $website->id
                : (int) $website;


        return $this->credits
            ->centralConsume(
                $websiteId,
                $this->creditType(
                    $service
                ),
                $amount,
                $requestKey,
                $context
            );
    }


    public function credit(
        Website|int $website,
        string $service,
        float $amount,
        ?string $requestKey = null,
        array $context = []
    ): array {

        $websiteId =
            $website instanceof Website
                ? (int) $website->id
                : (int) $website;


        return $this->credits
            ->centralCredit(
                $websiteId,
                $this->creditType(
                    $service
                ),
                $amount,
                $requestKey,
                $context
            );
    }


    public function balances(
        Website|int $website
    ): array {

        $websiteId =
            $website instanceof Website
                ? (int) $website->id
                : (int) $website;


        return $this->credits
            ->centralBalances(
                $websiteId
            );
    }


    public function aiBalance(
        Website|int $website
    ): float {

        return $this->balance(
            $website,
            'ai'
        );
    }


    public function smsBalance(
        Website|int $website
    ): float {

        return $this->balance(
            $website,
            'sms'
        );
    }


    public function emailBalance(
        Website|int $website
    ): float {

        return $this->balance(
            $website,
            'email'
        );
    }


    public function whatsappBalance(
        Website|int $website
    ): float {

        return $this->balance(
            $website,
            'whatsapp'
        );
    }


    public function consumeSms(
        Website|int $website,
        float $amount,
        string $requestKey,
        array $context = []
    ): array {

        return $this->consume(
            $website,
            'sms',
            $amount,
            $requestKey,
            $context
        );
    }


    public function consumeEmail(
        Website|int $website,
        float $amount,
        string $requestKey,
        array $context = []
    ): array {

        return $this->consume(
            $website,
            'email',
            $amount,
            $requestKey,
            $context
        );
    }


    public function consumeWhatsapp(
        Website|int $website,
        float $amount,
        string $requestKey,
        array $context = []
    ): array {

        return $this->consume(
            $website,
            'whatsapp',
            $amount,
            $requestKey,
            $context
        );
    }


    protected function creditType(
        string $service
    ): string {

        $service =
            strtolower(
                trim(
                    $service
                )
            );


        return match ($service) {

            'ai',
            'ai_credit',
            'ai_credits' =>
                'ai',

            'sms',
            'sms_credit',
            'sms_credits' =>
                'sms',

            'email',
            'email_credit',
            'email_credits' =>
                'email',

            'whatsapp',
            'whatsapp_credit',
            'whatsapp_credits' =>
                'whatsapp',

            default =>
                throw new InvalidArgumentException(
                    "Unsupported Central service credit [{$service}]."
                ),
        };
    }
}
