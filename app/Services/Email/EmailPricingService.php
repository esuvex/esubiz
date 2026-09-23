<?php

namespace App\Services\Email;

use App\Models\EmailCommercialSetting;
use App\Services\Aws\AwsSesPricingService;
use App\Services\Platform\CentralExchangeRateService;
use App\Services\Platform\CentralSiteSettingsService;
use InvalidArgumentException;

class EmailPricingService
{
    public function __construct(
        protected AwsSesPricingService $awsPricing,
        protected CentralExchangeRateService $exchangeRates,
        protected CentralSiteSettingsService $siteSettings
    ) {
    }

    /**
     * Quote Premium Email consumption.
     *
     * Commercial chain:
     *
     * AWS provider cost
     * -> Esubiz Email commercial markup
     * -> Central primary-currency conversion
     * -> Email Credit conversion
     * -> whole credits charged
     *
     * IMPORTANT:
     * General visitor/product FX markup is intentionally NOT
     * applied here. That belongs to customer-facing currency
     * display/payment conversion, not internal Email Credit
     * consumption.
     */
    public function quote(
        int $recipientCount = 1
    ): array {
        if ($recipientCount < 1) {
            throw new InvalidArgumentException(
                'Recipient count must be at least 1.'
            );
        }

        $providerPrice =
            $this->awsPricing
                ->outboundEmailPrice();

        if ($providerPrice === null) {
            return $this->unavailableQuote(
                $recipientCount,
                'aws_provider_price_unavailable'
            );
        }

        $providerCurrency = strtoupper(
            trim(
                (string) (
                    $providerPrice['currency']
                    ?? 'USD'
                )
            )
        );

        $providerUnitCost = (float) (
            $providerPrice[
                'normalized_unit_amount'
            ]
            ?? 0
        );

        if ($providerUnitCost < 0) {
            return $this->unavailableQuote(
                $recipientCount,
                'invalid_aws_provider_price'
            );
        }

        $providerTotalCost =
            $providerUnitCost
            * $recipientCount;

        $markupType =
            $this->markupType();

        $markupValue =
            $this->markupValue();

        /*
         * Premium Email pricing is calculated per recipient first.
         *
         * AWS provider cost
         * -> Esubiz percentage/fixed markup
         * -> Central base/default currency conversion
         * -> optional Admin selling-rate cap
         * -> recipient count
         *
         * This guarantees one consistent Esubiz rate per recipient
         * for the quoted send. AWS volume/tier savings are retained
         * by Esubiz and are not exposed as progressive customer
         * discounts beyond the configured maximum selling rate.
         */
        $markupAmountPerRecipient =
            $this->markupAmount(
                $providerUnitCost,
                $markupType,
                $markupValue
            );

        $sellingUnitProviderCurrency =
            $providerUnitCost
            + $markupAmountPerRecipient;

        $centralCurrency = strtoupper(trim($this->siteSettings->primaryCurrency()));

        $unitConversion =
            $this->convertWithoutFxMarkup(
                $sellingUnitProviderCurrency,
                $providerCurrency,
                $centralCurrency
            );

        if (!$unitConversion['available']) {
            return array_merge(
                $this->unavailableQuote(
                    $recipientCount,
                    $unitConversion[
                        'unavailable_reason'
                    ]
                ),
                [
                    'provider_price' =>
                        $providerPrice,
                    'provider_currency' =>
                        $providerCurrency,
                    'provider_unit_cost' =>
                        $providerUnitCost,
                    'provider_total_cost' =>
                        $providerTotalCost,
                    'email_markup_type' =>
                        $markupType,
                    'email_markup_value' =>
                        $markupValue,
                    'email_markup_amount_per_recipient' =>
                        $markupAmountPerRecipient,
                    'selling_unit_provider_currency' =>
                        $sellingUnitProviderCurrency,
                    'central_currency' =>
                        $centralCurrency,
                ]
            );
        }

        $uncappedRatePerRecipientBaseCurrency =
            (float) $unitConversion['amount'];

        $maxRatePerRecipientBaseCurrency =
            $this->maxRatePerRecipientBaseCurrency();

        $capApplied =
            $maxRatePerRecipientBaseCurrency > 0
            && $uncappedRatePerRecipientBaseCurrency
                > $maxRatePerRecipientBaseCurrency;

        $sellingRatePerRecipientBaseCurrency =
            $capApplied
                ? $maxRatePerRecipientBaseCurrency
                : $uncappedRatePerRecipientBaseCurrency;

        $sellingCostCentral =
            $sellingRatePerRecipientBaseCurrency
            * $recipientCount;

        /*
         * Keep the existing aggregate fields for downstream billing
         * compatibility while their semantics remain explicit.
         */
        $markupAmount =
            $markupAmountPerRecipient
            * $recipientCount;

        $sellingCostProviderCurrency =
            $sellingUnitProviderCurrency
            * $recipientCount;

        $conversion = [
            'available' => true,
            'amount' => $sellingCostCentral,
            'rate' => $unitConversion['rate'],
            'provider' => $unitConversion['provider'],
            'date' => $unitConversion['date'],
            'unavailable_reason' => null,
        ];



        /*
         * Email Credits are internally denominated in NGN.
         *
         * Customer selling price and its optional cap remain in
         * Central's configurable base/default currency. The final
         * capped selling total must therefore be converted to NGN
         * before Email Credit consumption is calculated.
         *
         * This uses the raw exchange-rate path and deliberately
         * excludes customer-facing secondary-currency FX markup.
         */
        $creditCurrency = 'NGN';

        $creditConversion =
            $this->convertWithoutFxMarkup(
                $sellingCostCentral,
                $centralCurrency,
                $creditCurrency
            );

        if (!$creditConversion['available']) {
            return array_merge(
                $this->unavailableQuote(
                    $recipientCount,
                    $creditConversion[
                        'unavailable_reason'
                    ]
                    ?? 'email_credit_currency_conversion_unavailable'
                ),
                [
                    'provider_price' =>
                        $providerPrice,
                    'provider_currency' =>
                        $providerCurrency,
                    'provider_unit_cost' =>
                        $providerUnitCost,
                    'provider_total_cost' =>
                        $providerTotalCost,
                    'email_markup_type' =>
                        $markupType,
                    'email_markup_value' =>
                        $markupValue,
                    'email_markup_amount' =>
                        $markupAmount,
                    'selling_cost_provider_currency' =>
                        $sellingCostProviderCurrency,
                    'central_currency' =>
                        $centralCurrency,
                    'selling_cost_central_currency' =>
                        $sellingCostCentral,
                    'credit_currency' =>
                        $creditCurrency,
                ]
            );
        }

        $sellingCostCreditCurrency =
            (float) $creditConversion['amount'];

        $nairaPerCredit =
            max(
                0,
                EmailCommercialSetting::number(
                    'naira_per_email_credit',
                    1
                )
            );

        if ($nairaPerCredit <= 0) {
            return array_merge(
                $this->unavailableQuote(
                    $recipientCount,
                    'invalid_email_credit_value'
                ),
                [
                    'provider_price' =>
                        $providerPrice,
                    'provider_currency' =>
                        $providerCurrency,
                    'provider_unit_cost' =>
                        $providerUnitCost,
                    'provider_total_cost' =>
                        $providerTotalCost,
                    'email_markup_type' =>
                        $markupType,
                    'email_markup_value' =>
                        $markupValue,
                    'email_markup_amount' =>
                        $markupAmount,
                    'selling_cost_provider_currency' =>
                        $sellingCostProviderCurrency,
                    'central_currency' =>
                        $centralCurrency,
                    'selling_cost_central_currency' =>
                        $sellingCostCentral,
                ]
            );
        }

        /*
         * Credits are whole consumption units and must never
         * round down below the calculated selling cost.
         */
        $credits = (int) ceil(
            $sellingCostCreditCurrency
            / $nairaPerCredit
        );

        /*
         * A positive billable send should consume at least one
         * Email Credit even when its monetary value is below one
         * full credit.
         */
        if (
            $sellingCostCreditCurrency > 0
            && $credits < 1
        ) {
            $credits = 1;
        }

        return [
            'available' => true,
            'unavailable_reason' => null,

            'recipient_count' =>
                $recipientCount,

            'provider' =>
                'aws',

            'provider_service' =>
                'ses',

            'provider_price' =>
                $providerPrice,

            'provider_currency' =>
                $providerCurrency,

            'provider_unit_cost' =>
                $providerUnitCost,

            'provider_total_cost' =>
                $providerTotalCost,

            'email_markup_type' =>
                $markupType,

            'email_markup_value' =>
                $markupValue,

            'email_markup_amount' =>
                $markupAmount,

            'selling_cost_provider_currency' =>
                $sellingCostProviderCurrency,

            'uncapped_rate_per_recipient_base_currency' =>
                $uncappedRatePerRecipientBaseCurrency,

            'max_rate_per_recipient_base_currency' =>
                $maxRatePerRecipientBaseCurrency,

            'cap_applied' =>
                $capApplied,

            'selling_rate_per_recipient_base_currency' =>
                $sellingRatePerRecipientBaseCurrency,

            'central_currency' =>
                $centralCurrency,

            'exchange_rate' =>
                $conversion['rate'],

            'exchange_rate_provider' =>
                $conversion['provider'],

            'exchange_rate_date' =>
                $conversion['date'],

            'fx_markup_applied' =>
                false,

            'selling_cost_central_currency' =>
                $sellingCostCentral,

            'email_credit_value' =>
                $nairaPerCredit,

            'credit_currency' =>
                $creditCurrency,

            'selling_cost_credit_currency' =>
                $sellingCostCreditCurrency,

            'credit_exchange_rate' =>
                $creditConversion['rate'],

            'credit_exchange_rate_provider' =>
                $creditConversion['provider'],

            'credit_exchange_rate_date' =>
                $creditConversion['date'],

            'credits' =>
                $credits,

            'pricing_snapshot' => [
                'aws_service_region' =>
                    $providerPrice[
                        'service_region'
                    ]
                    ?? null,

                'aws_service_code' =>
                    $providerPrice[
                        'service_code'
                    ]
                    ?? null,

                'aws_sku' =>
                    $providerPrice['sku']
                    ?? null,

                'aws_rate_code' =>
                    $providerPrice[
                        'rate_code'
                    ]
                    ?? null,

                'aws_unit' =>
                    $providerPrice['unit']
                    ?? null,

                'aws_price_synced_at' =>
                    $providerPrice[
                        'last_success_at'
                    ]
                    ?? null,

                'provider_unit_cost' =>
                    $providerUnitCost,

                'provider_total_cost' =>
                    $providerTotalCost,

                'email_markup_type' =>
                    $markupType,

                'email_markup_value' =>
                    $markupValue,

                'email_markup_amount' =>
                    $markupAmount,

                'selling_cost_provider_currency' =>
                    $sellingCostProviderCurrency,

                'uncapped_rate_per_recipient_base_currency' =>
                    $uncappedRatePerRecipientBaseCurrency,

                'max_rate_per_recipient_base_currency' =>
                    $maxRatePerRecipientBaseCurrency,

                'cap_applied' =>
                    $capApplied,

                'selling_rate_per_recipient_base_currency' =>
                    $sellingRatePerRecipientBaseCurrency,

                'exchange_rate' =>
                    $conversion['rate'],

                'exchange_rate_provider' =>
                    $conversion['provider'],

                'central_currency' =>
                    $centralCurrency,

                'selling_cost_central_currency' =>
                    $sellingCostCentral,

                'email_credit_value' =>
                    $nairaPerCredit,

                'credit_currency' =>
                    $creditCurrency,

                'selling_cost_credit_currency' =>
                    $sellingCostCreditCurrency,

                'credit_exchange_rate' =>
                    $creditConversion['rate'],

                'credit_exchange_rate_provider' =>
                    $creditConversion['provider'],

                'credit_exchange_rate_date' =>
                    $creditConversion['date'],

                'recipient_count' =>
                    $recipientCount,

                'credits' =>
                    $credits,

                'quoted_at' =>
                    now()->toIso8601String(),
            ],
        ];
    }

    public function estimate(
        int $recipientCount
    ): array {
        return $this->quote(
            $recipientCount
        );
    }

    protected function markupType(): string
    {
        $type = strtolower(
            trim(
                EmailCommercialSetting::string(
                    'provider_markup_type',
                    'percentage'
                )
            )
        );

        return in_array(
            $type,
            [
                'percentage',
                'fixed',
            ],
            true
        )
            ? $type
            : 'percentage';
    }

    protected function markupValue(): float
    {
        return max(
            0,
            EmailCommercialSetting::number(
                'provider_markup_value',
                0
            )
        );
    }

    protected function markupAmount(
        float $providerUnitCost,
        string $type,
        float $value
    ): float {
        /*
         * Markup is calculated for one billable recipient.
         *
         * Percentage:
         * provider unit cost × percentage.
         *
         * Fixed:
         * fixed provider-currency amount for one recipient.
         *
         * The final selling rate in Central's base/default currency is
         * capped separately after provider-currency conversion.
         */
        if ($type === 'fixed') {
            return $value;
        }

        return $providerUnitCost
            * ($value / 100);
    }

    protected function maxRatePerRecipientBaseCurrency(): float
    {
        return max(
            0,
            EmailCommercialSetting::number(
                'max_rate_per_recipient_base_currency',
                0
            )
        );
    }

    /**
     * Convert provider cost into Central's authoritative currency
     * using the raw provider exchange rate.
     *
     * This deliberately bypasses EsubizCurrencyPricingService
     * because that service applies customer-facing secondary
     * currency markup.
     */
    protected function convertWithoutFxMarkup(
        float $amount,
        string $fromCurrency,
        string $toCurrency
    ): array {
        $fromCurrency = strtoupper(
            trim($fromCurrency)
        );

        $toCurrency = strtoupper(
            trim($toCurrency)
        );

        if (
            $fromCurrency === $toCurrency
        ) {
            return [
                'available' => true,
                'amount' => $amount,
                'rate' => 1.0,
                'provider' =>
                    'same_currency',
                'date' =>
                    now()->toDateString(),
                'unavailable_reason' =>
                    null,
            ];
        }

        $rate =
            $this->exchangeRates->rate(
                $fromCurrency,
                $toCurrency
            );

        if (
            $rate === null
            || $rate <= 0
        ) {
            return [
                'available' => false,
                'amount' => null,
                'rate' => null,
                'provider' => null,
                'date' => null,
                'unavailable_reason' =>
                    'exchange_rate_unavailable',
            ];
        }

        return [
            'available' => true,
            'amount' =>
                $amount * $rate,
            'rate' =>
                $rate,
            'provider' =>
                'frankfurter',
            'date' =>
                now()->toDateString(),
            'unavailable_reason' =>
                null,
        ];
    }

    protected function unavailableQuote(
        int $recipientCount,
        string $reason
    ): array {
        return [
            'available' => false,
            'unavailable_reason' =>
                $reason,
            'recipient_count' =>
                $recipientCount,
            'credits' => null,
        ];
    }
}
