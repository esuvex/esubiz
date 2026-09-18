<?php

namespace App\Services\Platform;

use App\Services\Core\CoreCurrencyConverter;
use InvalidArgumentException;

class EsubizCurrencyPricingService
{
    /*
     * ESUBIZ_UNIVERSAL_PRODUCT_CURRENCY_PRICING_V2
     *
     * One product-pricing conversion layer for:
     *
     * - Central Esubiz
     * - SaaS
     * - Off-server integrations
     * - Wallet
     * - Gift Card
     * - Current products
     * - Future products
     *
     * Payment gateway integration is intentionally NOT performed here.
     *
     * Products keep one authoritative base price.
     * Converted prices are calculated dynamically.
     *
     * A provider failure or unsupported currency must never invent a
     * converted price. The quote remains available as a structured
     * "conversion unavailable" response while preserving the base price.
     */

    public function __construct(
        protected CentralSiteSettingsService $settings,
        protected CoreCurrencyConverter $converter
    ) {
    }

    public function primaryCurrency(): string
    {
        return strtoupper(
            trim(
                $this->settings
                    ->primaryCurrency()
            )
        );
    }

    public function enabledSecondaryCurrencies(): array
    {
        $configs = $this->settings
            ->secondaryCurrencySettings();

        $primary =
            $this->primaryCurrency();

        return array_values(
            array_filter(
                array_keys(
                    array_filter(
                        $configs,
                        fn ($config) =>
                            (bool) (
                                $config['enabled']
                                ?? false
                            )
                    )
                ),
                fn ($currency) =>
                    strtoupper(
                        trim(
                            (string) $currency
                        )
                    ) !== $primary
            )
        );
    }

    public function quote(
        float $baseAmount,
        string $targetCurrency
    ): array {
        $baseAmount = max(
            0,
            $baseAmount
        );

        $baseCurrency =
            $this->primaryCurrency();

        $targetCurrency = strtoupper(
            trim($targetCurrency)
        );

        if ($targetCurrency === '') {
            throw new InvalidArgumentException(
                'Target currency is required.'
            );
        }

        if ($targetCurrency === $baseCurrency) {
            return [
                'base_amount' => round(
                    $baseAmount,
                    2
                ),
                'base_currency' => $baseCurrency,
                'currency' => $baseCurrency,
                'rate' => 1.0,
                'rate_date' => now()->toDateString(),
                'rate_provider' => 'base_currency',
                'markup_type' => 'none',
                'markup_value' => 0.0,
                'converted_amount' => round(
                    $baseAmount,
                    2
                ),
                'converted' => false,
                'available' => true,
                'unavailable_reason' => null,
            ];
        }

        $configs = $this->settings
            ->secondaryCurrencySettings();

        $config =
            $configs[$targetCurrency]
            ?? null;

        if (
            !$config
            || !($config['enabled'] ?? false)
        ) {
            throw new InvalidArgumentException(
                "Currency {$targetCurrency} is not enabled for Esubiz."
            );
        }

        $rateResult =
            $this->converter
                ->tryRate(
                    $baseCurrency,
                    $targetCurrency
                );

        /*
         * Never fake a conversion.
         *
         * A currency may legitimately exist in the worldwide ISO directory
         * while being unsupported by the current exchange-rate provider.
         */
        if ($rateResult === null) {
            return [
                'base_amount' => round(
                    $baseAmount,
                    2
                ),
                'base_currency' => $baseCurrency,
                'currency' => $targetCurrency,
                'rate' => null,
                'rate_date' => null,
                'rate_provider' => null,
                'markup_type' =>
                    $config['markup_type']
                    ?? 'percentage',
                'markup_value' =>
                    max(
                        0,
                        (float) (
                            $config['markup_value']
                            ?? 0
                        )
                    ),
                'converted_amount' => null,
                'converted' => false,
                'available' => false,
                'unavailable_reason' =>
                    'exchange_rate_unavailable',
            ];
        }

        $rate = (float) (
            $rateResult['rate']
            ?? 0
        );

        if ($rate <= 0) {
            return [
                'base_amount' => round(
                    $baseAmount,
                    2
                ),
                'base_currency' => $baseCurrency,
                'currency' => $targetCurrency,
                'rate' => null,
                'rate_date' => null,
                'rate_provider' => null,
                'markup_type' =>
                    $config['markup_type']
                    ?? 'percentage',
                'markup_value' =>
                    max(
                        0,
                        (float) (
                            $config['markup_value']
                            ?? 0
                        )
                    ),
                'converted_amount' => null,
                'converted' => false,
                'available' => false,
                'unavailable_reason' =>
                    'invalid_exchange_rate',
            ];
        }

        $convertedAmount =
            $baseAmount * $rate;

        $markupType =
            $config['markup_type']
            ?? 'percentage';

        $markupValue =
            max(
                0,
                (float) (
                    $config['markup_value']
                    ?? 0
                )
            );

        if ($markupType === 'percentage') {
            $convertedAmount +=
                $convertedAmount
                * (
                    $markupValue
                    / 100
                );
        }

        if ($markupType === 'fixed') {
            $convertedAmount +=
                $markupValue;
        }

        return [
            'base_amount' => round(
                $baseAmount,
                2
            ),

            'base_currency' =>
                $baseCurrency,

            'currency' =>
                $targetCurrency,

            'rate' =>
                $rate,

            'rate_date' =>
                $rateResult['date']
                ?? null,

            'rate_provider' =>
                $rateResult['source']
                ?? 'frankfurter',

            'markup_type' =>
                $markupType,

            'markup_value' =>
                $markupValue,

            'converted_amount' =>
                round(
                    $convertedAmount,
                    2
                ),

            'converted' => true,
            'available' => true,
            'unavailable_reason' => null,
        ];
    }

    public function quoteAll(
        float $baseAmount
    ): array {
        $quotes = [];

        $primary =
            $this->primaryCurrency();

        $quotes[$primary] =
            $this->quote(
                $baseAmount,
                $primary
            );

        foreach (
            $this->enabledSecondaryCurrencies()
            as $currency
        ) {
            $quotes[$currency] =
                $this->quote(
                    $baseAmount,
                    $currency
                );
        }

        return $quotes;
    }

    /*
     * Generic product helper.
     *
     * The service deliberately knows nothing about product models,
     * tables or product types. Therefore new Esubiz products do not
     * require new currency-conversion code.
     */
    public function productPrices(
        float $basePrice
    ): array {
        return $this->quoteAll(
            $basePrice
        );
    }

    /*
     * ESUBIZ_UNIVERSAL_PRODUCT_CURRENCY_V5
     *
     * Canonical currency API for every Central Esubiz product:
     * plans/subscriptions, Marketplace products, Website Types,
     * themes, modules, add-ons and all credit products.
     *
     * Product/domain code should pass numeric base amounts through
     * this service instead of choosing its own currency symbol/code.
     */
    public function centralCurrencyCode(): string
    {
        /*
         * ESUBIZ_CENTRAL_CURRENCY_AUTHORITY_V1
         *
         * Central Admin's configured primary currency is the single
         * currency authority for all Esubiz products and services.
         *
         * Secondary currencies are converted dynamically against it.
         */
        return $this->primaryCurrency();
    }

    public function currencySymbol(
        ?string $currency = null
    ): string {
        $currency = strtoupper(
            trim(
                (string) (
                    $currency
                    ?: $this->centralCurrencyCode()
                )
            )
        );

        return match ($currency) {
            'NGN' => '₦',
            'USD' => '$',
            'GBP' => '£',
            'EUR' => '€',
            default => $currency,
        };
    }

    public function formatCentralAmount(
        int|float|string|null $amount,
        ?string $currency = null,
        int $decimals = 2
    ): string {
        $currency = strtoupper(
            trim(
                (string) (
                    $currency
                    ?: $this->centralCurrencyCode()
                )
            )
        );

        $numericAmount = is_numeric($amount)
            ? (float) $amount
            : 0.0;

        $formatted = number_format(
            $numericAmount,
            max(0, $decimals),
            '.',
            ','
        );

        if (
            $decimals > 0
            && abs(
                $numericAmount
                - round($numericAmount)
            ) < 0.0000001
        ) {
            $formatted = number_format(
                $numericAmount,
                0,
                '.',
                ','
            );
        }

        $symbol = $this->currencySymbol($currency);

        return in_array(
            $currency,
            ['NGN', 'USD', 'GBP', 'EUR'],
            true
        )
            ? $symbol . $formatted
            : $currency . ' ' . $formatted;
    }

    public function productMoney(
        int|float|string|null $amount,
        ?string $currency = null
    ): array {
        $currency = strtoupper(
            trim(
                (string) (
                    $currency
                    ?: $this->centralCurrencyCode()
                )
            )
        );

        $numericAmount = is_numeric($amount)
            ? (float) $amount
            : 0.0;

        return [
            'amount' => $numericAmount,
            'currency' => $currency,
            'symbol' => $this->currencySymbol(
                $currency
            ),
            'formatted' => $this->formatCentralAmount(
                $numericAmount,
                $currency
            ),
        ];
    }

}