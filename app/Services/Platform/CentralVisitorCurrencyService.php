<?php

namespace App\Services\Platform;

use Illuminate\Http\Request;

class CentralVisitorCurrencyService
{
    /*
     * ESUBIZ_VISITOR_SECONDARY_CURRENCY_V1
     *
     * Resolution priority:
     *
     * 1. Explicit/manual country selection.
     * 2. Saved authenticated-user/session country.
     * 3. IP-detected visitor country.
     * 4. Central default country.
     *
     * Currency rule:
     *
     * - Visitor in Central default country => Central default currency.
     * - Visitor outside Central default country => configured secondary currency.
     *
     * IP detection is deliberately delegated to a replaceable provider layer
     * and will be added separately with caching/fail-safe behaviour.
     */
    public function __construct(
        protected CentralSiteSettingsService $settings,
        protected EsubizCurrencyPricingService $pricing,
        protected CentralIpCountryService $ipCountry,
        protected CentralExchangeRateService $exchangeRates
    ) {
    }

    public function defaultCountryCode(): string
    {
        $country = strtoupper(
            trim(
                (string) (
                    $this->settings->get('platform.default_country')
                    ?: $this->settings->get('country.default')
                    ?: 'NG'
                )
            )
        );

        return $country !== ''
            ? $country
            : 'NG';
    }

    public function secondaryCurrencyCode(): string
    {
        $currency = strtoupper(
            trim(
                (string) (
                    $this->settings->get('platform.secondary_currency')
                    ?: $this->settings->get('currency.secondary')
                    ?: 'USD'
                )
            )
        );

        return $currency !== ''
            ? $currency
            : 'USD';
    }

    public function normalizeCountry(
        ?string $country
    ): ?string {
        if ($country === null) {
            return null;
        }

        $country = strtoupper(trim($country));

        if (
            $country === ''
            || !preg_match('/^[A-Z]{2}$/', $country)
        ) {
            return null;
        }

        return $country;
    }

    public function selectedCountry(
        Request $request
    ): ?string {
        $manual = $this->normalizeCountry(
            $request->input('country')
                ?: $request->query('country')
                ?: $request->session()->get(
                    'esubiz_country'
                )
        );

        if ($manual !== null) {
            return $manual;
        }

        $user = $request->user();

        if ($user) {
            foreach (
                [
                    'country_code',
                    'country',
                    'default_country_code',
                ] as $field
            ) {
                $candidate = $this->normalizeCountry(
                    $user->{$field} ?? null
                );

                if ($candidate !== null) {
                    return $candidate;
                }
            }
        }

        return null;
    }

    /*
     * ESUBIZ_VISITOR_IP_RESOLUTION_V2
     *
     * Explicit/manual and account/session country always win.
     * IP detection runs only when no known visitor country exists.
     */
    public function resolveCountry(
        Request $request,
        ?string $ipCountry = null
    ): string {
        $selectedCountry =
            $this->selectedCountry($request);

        if ($selectedCountry !== null) {
            return $selectedCountry;
        }

        $detectedCountry =
            $this->normalizeCountry($ipCountry)
            ?: $this->ipCountry->countryCode(
                $request
            );

        return $detectedCountry
            ?: $this->defaultCountryCode();
    }

    public function currencyForCountry(
        ?string $country
    ): string {
        $country = $this->normalizeCountry($country)
            ?: $this->defaultCountryCode();

        if ($country === $this->defaultCountryCode()) {
            return $this->pricing->centralCurrencyCode();
        }

        return $this->secondaryCurrencyCode();
    }

    public function resolve(
        Request $request,
        ?string $ipCountry = null
    ): array {
        $country = $this->resolveCountry(
            $request,
            $ipCountry
        );

        $currency = $this->currencyForCountry(
            $country
        );

        return [
            'country' => $country,
            'currency' => $currency,
            'is_default_country' =>
                $country === $this->defaultCountryCode(),
            'is_secondary_currency' =>
                $currency === $this->secondaryCurrencyCode(),
        ];
    }

    /*
     * ESUBIZ_AUTOMATIC_SECONDARY_CONVERSION_V3
     *
     * Product prices have ONE source of truth:
     * the Central base-currency amount.
     *
     * Foreign display price =
     * base amount
     * × current FX rate
     * × (1 + Admin markup percentage / 100).
     *
     * No secondary product price is stored.
     */
    public function secondaryMarkupPercent(): float
    {
        $markup = $this->settings->get(
            'platform.secondary_currency_markup_percent'
        );

        if (
            $markup === null
            || $markup === ''
        ) {
            $markup = $this->settings->get(
                'currency.secondary_markup_percent'
            );
        }

        if (!is_numeric($markup)) {
            return 0.0;
        }

        return max(
            0.0,
            (float) $markup
        );
    }

    public function productMoney(
        Request $request,
        int|float|string|null $baseAmount
    ): array {
        $numericBaseAmount =
            is_numeric($baseAmount)
                ? (float) $baseAmount
                : 0.0;

        $visitor = $this->resolve(
            $request
        );

        $baseCurrency =
            $this->pricing
                ->centralCurrencyCode();

        $targetCurrency =
            strtoupper(
                (string) $visitor['currency']
            );

        /*
         * Default-country visitors always see the stored
         * base price without any FX conversion or markup.
         */
        if ($targetCurrency === $baseCurrency) {
            $money =
                $this->pricing->productMoney(
                    $numericBaseAmount,
                    $baseCurrency
                );

            return array_merge(
                $money,
                [
                    'base_amount' =>
                        $numericBaseAmount,
                    'base_currency' =>
                        $baseCurrency,
                    'country' =>
                        $visitor['country'],
                    'exchange_rate' => 1.0,
                    'markup_percent' => 0.0,
                    'effective_rate' => 1.0,
                    'converted' => false,
                    'conversion_available' => true,
                ]
            );
        }

        $rate = $this->exchangeRates->rate(
            $baseCurrency,
            $targetCurrency
        );

        /*
         * FX failure must never relabel the original
         * amount with the foreign currency.
         *
         * Fall back safely to the real stored base price.
         */
        if ($rate === null) {
            $money =
                $this->pricing->productMoney(
                    $numericBaseAmount,
                    $baseCurrency
                );

            return array_merge(
                $money,
                [
                    'base_amount' =>
                        $numericBaseAmount,
                    'base_currency' =>
                        $baseCurrency,
                    'country' =>
                        $visitor['country'],
                    'exchange_rate' => null,
                    'markup_percent' => 0.0,
                    'effective_rate' => null,
                    'converted' => false,
                    'conversion_available' => false,
                ]
            );
        }

        $markup =
            $this->secondaryMarkupPercent();

        $effectiveRate =
            $rate
            * (
                1
                + ($markup / 100)
            );

        $convertedAmount =
            $numericBaseAmount
            * $effectiveRate;

        $money =
            $this->pricing->productMoney(
                $convertedAmount,
                $targetCurrency
            );

        return array_merge(
            $money,
            [
                'base_amount' =>
                    $numericBaseAmount,
                'base_currency' =>
                    $baseCurrency,
                'country' =>
                    $visitor['country'],
                'exchange_rate' =>
                    $rate,
                'markup_percent' =>
                    $markup,
                'effective_rate' =>
                    $effectiveRate,
                'converted' => true,
                'conversion_available' => true,
            ]
        );
    }

}
