<?php

namespace App\Services\Core;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

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
     */
    public function convertForSite(
        ?int $workspaceId,
        float $baseAmount
    ): array {
        $settings = $this->currencyService
            ->getSecondarySettings($workspaceId);

        $currencies = $this->currencyService
            ->getCurrencies($workspaceId);

        $baseCurrency = $currencies['primary'];
        $secondaryCurrency = $settings['currency'];

        if (
            !$settings['enabled'] ||
            !$secondaryCurrency
        ) {
            return [
                'amount' => $baseAmount,
                'currency' => $baseCurrency,
                'converted' => false,
            ];
        }

        $rate = $settings['automatic_conversion']
            ? $this->rate(
                $baseCurrency,
                $secondaryCurrency
            )['rate']
            : (float) $settings['manual_rate'];

        $convertedAmount = $baseAmount * $rate;

        if ($settings['margin_type'] === 'percentage') {
            $convertedAmount += $convertedAmount
                * ($settings['margin_value'] / 100);
        }

        if ($settings['margin_type'] === 'fixed') {
            $convertedAmount += $settings['margin_value'];
        }

        return [
            'base_amount' => $baseAmount,
            'base_currency' => $baseCurrency,
            'currency' => $secondaryCurrency,
            'rate' => $rate,
            'margin_type' => $settings['margin_type'],
            'margin_value' => $settings['margin_value'],
            'converted_amount' => round($convertedAmount, 2),
            'converted' => true,
        ];
    }

    public function rate(
        string $fromCurrency,
        string $toCurrency
    ): array {
        $fromCurrency = strtoupper(trim($fromCurrency));
        $toCurrency = strtoupper(trim($toCurrency));

        if ($fromCurrency === $toCurrency) {
            return [
                'rate' => 1,
                'date' => now()->toDateString(),
                'source' => 'same_currency',
            ];
        }

        $cacheKey = 'core_currency_rate:'
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
                    !isset($data['rate']) ||
                    !is_numeric($data['rate'])
                ) {
                    throw new RuntimeException(
                        'Currency provider returned an invalid rate.'
                    );
                }

                return [
                    'rate' => (float) $data['rate'],
                    'date' => $data['date'] ?? null,
                    'source' => 'frankfurter',
                ];
            }
        );
    }
}
