<?php

namespace App\Services\Platform;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class CentralExchangeRateService
{
    /*
     * ESUBIZ_CENTRAL_EXCHANGE_RATE_V3
     *
     * Plug-and-play FX source for Central product pricing.
     *
     * Provider:
     * Frankfurter v2.
     *
     * Rules:
     * - Product prices remain stored only in the base currency.
     * - FX rates are fetched automatically when required.
     * - Fresh rates are cached for 12 hours.
     * - Last successful rates remain available for 7 days.
     * - No API key is required.
     * - Provider failure never invents a rate.
     */
    public function rate(
        string $baseCurrency,
        string $quoteCurrency
    ): ?float {
        $base = $this->normalizeCurrency(
            $baseCurrency
        );

        $quote = $this->normalizeCurrency(
            $quoteCurrency
        );

        if (
            $base === null
            || $quote === null
        ) {
            return null;
        }

        if ($base === $quote) {
            return 1.0;
        }

        $pair =
            strtolower($base)
            . ':'
            . strtolower($quote);

        $freshKey =
            'esubiz:fx:fresh:' . $pair;

        $staleKey =
            'esubiz:fx:stale:' . $pair;

        $cached = Cache::get($freshKey);

        if (
            is_numeric($cached)
            && (float) $cached > 0
        ) {
            return (float) $cached;
        }

        try {
            $response = Http::acceptJson()
                ->timeout(5)
                ->connectTimeout(3)
                ->get(
                    'https://api.frankfurter.dev/v2/rate/'
                    . strtolower($base)
                    . '/'
                    . strtolower($quote)
                );

            if ($response->successful()) {
                $rate = $response->json(
                    'rate'
                );

                if (
                    is_numeric($rate)
                    && (float) $rate > 0
                ) {
                    $rate = (float) $rate;

                    Cache::put(
                        $freshKey,
                        $rate,
                        now()->addHours(12)
                    );

                    Cache::put(
                        $staleKey,
                        $rate,
                        now()->addDays(7)
                    );

                    return $rate;
                }
            }
        } catch (\Throwable $exception) {
            // Fall through to last known successful rate.
        }

        $stale = Cache::get($staleKey);

        if (
            is_numeric($stale)
            && (float) $stale > 0
        ) {
            return (float) $stale;
        }

        return null;
    }

    protected function normalizeCurrency(
        string $currency
    ): ?string {
        $currency = strtoupper(
            trim($currency)
        );

        if (
            !preg_match(
                '/^[A-Z]{3}$/',
                $currency
            )
        ) {
            return null;
        }

        return $currency;
    }
}
