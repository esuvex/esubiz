<?php

namespace App\Services\Core;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class CoreCurrencyConverter
{
    protected string $endpoint = 'https://api.frankfurter.dev/v2';

    public function __construct(
        protected CoreCurrencyService $currencyService
    ) {
    }

    /**
     * Convert a base/site price into the enabled secondary currency.
     *
     * The stored/base price is never changed.
     *
     * Automatic conversion failures are deliberately non-destructive:
     * the authoritative base price is returned instead of fabricating
     * a rate or allowing the provider failure to break the page.
     */
    public function convertForSite(
        ?int $workspaceId,
        float $baseAmount
    ): array {
        $settings = $this->currencyService
            ->getSecondarySettings($workspaceId);

        $currencies = $this->currencyService
            ->getCurrencies($workspaceId);

        $baseCurrency = strtoupper(
            trim(
                (string) (
                    $currencies['primary']
                    ?? 'NGN'
                )
            )
        );

        $secondaryCurrency = strtoupper(
            trim(
                (string) (
                    $settings['currency']
                    ?? ''
                )
            )
        );

        if (
            !($settings['enabled'] ?? false)
            || $secondaryCurrency === ''
        ) {
            return [
                'amount' => $baseAmount,
                'currency' => $baseCurrency,
                'converted' => false,
                'available' => true,
            ];
        }

        if (
            $settings['automatic_conversion']
            ?? false
        ) {
            $rateResult = $this->tryRate(
                $baseCurrency,
                $secondaryCurrency
            );

            if ($rateResult === null) {
                return [
                    'base_amount' => round(
                        $baseAmount,
                        2
                    ),
                    'base_currency' => $baseCurrency,
                    'currency' => $secondaryCurrency,
                    'converted_amount' => null,
                    'converted' => false,
                    'available' => false,
                    'unavailable_reason' =>
                        'exchange_rate_unavailable',
                ];
            }

            $rate = (float) $rateResult['rate'];
        } else {
            $rate = (float) (
                $settings['manual_rate']
                ?? 0
            );

            if ($rate <= 0) {
                return [
                    'base_amount' => round(
                        $baseAmount,
                        2
                    ),
                    'base_currency' => $baseCurrency,
                    'currency' => $secondaryCurrency,
                    'converted_amount' => null,
                    'converted' => false,
                    'available' => false,
                    'unavailable_reason' =>
                        'manual_rate_unavailable',
                ];
            }
        }

        $convertedAmount =
            $baseAmount * $rate;

        if (
            ($settings['margin_type'] ?? '')
            === 'percentage'
        ) {
            $convertedAmount +=
                $convertedAmount
                * (
                    (
                        (float) (
                            $settings['margin_value']
                            ?? 0
                        )
                    ) / 100
                );
        }

        if (
            ($settings['margin_type'] ?? '')
            === 'fixed'
        ) {
            $convertedAmount +=
                (float) (
                    $settings['margin_value']
                    ?? 0
                );
        }

        return [
            'base_amount' => round(
                $baseAmount,
                2
            ),
            'base_currency' => $baseCurrency,
            'currency' => $secondaryCurrency,
            'rate' => $rate,
            'margin_type' =>
                $settings['margin_type']
                ?? 'none',
            'margin_value' =>
                (float) (
                    $settings['margin_value']
                    ?? 0
                ),
            'converted_amount' => round(
                $convertedAmount,
                2
            ),
            'converted' => true,
            'available' => true,
        ];
    }

    /**
     * Safe public conversion-rate lookup.
     *
     * A missing/unsupported currency or temporary provider outage returns
     * null. It NEVER substitutes 1:1 or another fake exchange rate.
     */
    public function tryRate(
        string $fromCurrency,
        string $toCurrency
    ): ?array {
        $fromCurrency = strtoupper(
            trim($fromCurrency)
        );

        $toCurrency = strtoupper(
            trim($toCurrency)
        );

        if (
            $fromCurrency === ''
            || $toCurrency === ''
        ) {
            return null;
        }

        $failureKey =
            'core_currency_rate_failure:'
            . $fromCurrency
            . ':'
            . $toCurrency;

        /*
         * Briefly remember provider/unsupported-currency failures so a page
         * containing many products does not repeatedly hit the provider.
         */
        if (Cache::has($failureKey)) {
            return null;
        }

        try {
            $result = $this->rate(
                $fromCurrency,
                $toCurrency
            );

            Cache::forget($failureKey);

            return $result;
        } catch (Throwable $e) {
            Cache::put(
                $failureKey,
                true,
                now()->addMinutes(10)
            );

            Log::warning(
                'Currency conversion rate unavailable.',
                [
                    'from' => $fromCurrency,
                    'to' => $toCurrency,
                    'provider' => 'frankfurter',
                    'message' => $e->getMessage(),
                ]
            );

            return null;
        }
    }

    /**
     * Strict rate lookup.
     *
     * Successful rates are cached for one hour.
     * Call tryRate() when a provider failure must not interrupt the request.
     */
    public function rate(
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
            $fromCurrency === ''
            || $toCurrency === ''
        ) {
            throw new RuntimeException(
                'Both source and target currencies are required.'
            );
        }

        if ($fromCurrency === $toCurrency) {
            return [
                'rate' => 1.0,
                'date' => now()->toDateString(),
                'source' => 'same_currency',
            ];
        }

        $cacheKey =
            'core_currency_rate:'
            . $fromCurrency
            . ':'
            . $toCurrency;

        return Cache::remember(
            $cacheKey,
            now()->addHour(),
            function () use (
                $fromCurrency,
                $toCurrency
            ) {
                $response = Http::timeout(10)
                    ->acceptJson()
                    ->get(
                        $this->endpoint
                        . '/rate/'
                        . $fromCurrency
                        . '/'
                        . $toCurrency
                    );

                if (!$response->successful()) {
                    throw new RuntimeException(
                        'Currency rate provider returned HTTP '
                        . $response->status()
                    );
                }

                $data = $response->json();

                if (
                    !isset($data['rate'])
                    || !is_numeric($data['rate'])
                    || (float) $data['rate'] <= 0
                ) {
                    throw new RuntimeException(
                        'Currency provider returned an invalid rate.'
                    );
                }

                return [
                    'rate' =>
                        (float) $data['rate'],

                    'date' =>
                        $data['date']
                        ?? null,

                    'source' =>
                        'frankfurter',
                ];
            }
        );
    }
}
