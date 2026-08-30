<?php

namespace App\Services\Ai;

use App\Models\Ai\AiCommercialSetting;
use RuntimeException;

class AiPricingService
{
    /**
     * OpenAI provider cost -> Esubiz selling price.
     *
     * 300% markup means:
     *
     * provider cost + 300% provider cost
     * = provider cost x 4.
     */
    public function markupPercent(): float
    {
        return AiCommercialSetting::number(
            'provider_markup_percent',
            300
        );
    }


    public function sellingMultiplier(): float
    {
        return 1
            + (
                $this->markupPercent()
                / 100
            );
    }


    /**
     * Standard internal consumption value.
     *
     * Package discounts do NOT change this.
     */
    public function nairaPerCredit(): float
    {
        $value =
            AiCommercialSetting::number(
                'naira_per_ai_credit',
                10
            );


        if ($value <= 0) {
            throw new RuntimeException(
                'AI Credit Naira value must be greater than zero.'
            );
        }


        return $value;
    }


    /**
     * Central billing exchange rate.
     *
     * This is deliberately admin-controlled.
     */
    public function usdNgnRate(): float
    {
        $rate =
            AiCommercialSetting::number(
                'usd_ngn_rate',
                1600
            );


        if ($rate <= 0) {
            throw new RuntimeException(
                'AI USD/NGN billing rate must be greater than zero.'
            );
        }


        return $rate;
    }


    public function quote(
        float $providerCostUsd
    ): array {

        $providerCostUsd =
            max(
                0,
                $providerCostUsd
            );


        $markupPercent =
            $this->markupPercent();


        $multiplier =
            $this->sellingMultiplier();


        $usdNgnRate =
            $this->usdNgnRate();


        $nairaPerCredit =
            $this->nairaPerCredit();


        $providerCostNgn =
            $providerCostUsd
            * $usdNgnRate;


        $sellingPriceNgn =
            $providerCostNgn
            * $multiplier;


        /*
         * AI credits are whole consumable units.
         *
         * Never round down because Esubiz must not
         * undercharge provider usage.
         */
        $credits =
            $sellingPriceNgn > 0
                ? max(
                    1,
                    (int) ceil(
                        $sellingPriceNgn
                        / $nairaPerCredit
                    )
                )
                : 0;


        return [
            'provider_cost_usd' =>
                round(
                    $providerCostUsd,
                    8
                ),

            'usd_ngn_rate' =>
                $usdNgnRate,

            'provider_cost_ngn' =>
                round(
                    $providerCostNgn,
                    4
                ),

            'markup_percent' =>
                $markupPercent,

            'selling_multiplier' =>
                $multiplier,

            'selling_price_ngn' =>
                round(
                    $sellingPriceNgn,
                    4
                ),

            'naira_per_credit' =>
                $nairaPerCredit,

            'credits' =>
                $credits,
        ];
    }
}
