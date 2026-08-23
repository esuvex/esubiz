<?php

namespace App\Services\Core;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class PayoutConversionService
{
    public function convert(
        object $method,
        string $fromCurrency,
        float $amount
    ): array {
        if ($amount <= 0) {
            throw new RuntimeException(
                'Conversion amount must be greater than zero.'
            );
        }

        $fromCurrency = strtoupper(
            trim($fromCurrency)
        );

        /*
         * No currency conversion.
         *
         * Markdown may still be applied to the payout amount,
         * allowing the same pricing control for any payout method.
         */
        if (!(bool) ($method->conversion_enabled ?? false)) {
            $result = [
                'converted' => false,
                'from_currency' => $fromCurrency,
                'to_currency' => $fromCurrency,
                'source_amount' => $amount,
                'rate' => 1,
                'gross_receive_amount' => $amount,
                'receive_amount' => $amount,
                'provider' => null,
            ];

            return $this->applyMarkdown(
                $method,
                $result
            );
        }

        $type = strtolower(
            trim(
                $method->conversion_type ?? 'none'
            )
        );

        $provider = strtolower(
            trim(
                $method->conversion_provider ?? ''
            )
        );

        $target = strtoupper(
            trim(
                $method->conversion_target ?? ''
            )
        );

        if ($target === '') {
            throw new RuntimeException(
                'No conversion target is configured for this payout method.'
            );
        }

        if ($provider === 'manual') {
            $rate = (float) (
                $method->manual_conversion_rate ?? 0
            );

            if ($rate <= 0) {
                throw new RuntimeException(
                    'Manual conversion rate is not configured.'
                );
            }

            $result = $this->result(
                $fromCurrency,
                $target,
                $amount,
                $rate,
                'manual'
            );

            return $this->applyMarkdown(
                $method,
                $result
            );
        }

        if ($type === 'fiat') {
            $result = $this->convertFiat(
                $fromCurrency,
                $target,
                $amount,
                $provider
            );

            return $this->applyMarkdown(
                $method,
                $result
            );
        }

        if ($type === 'crypto') {
            $result = $this->convertCrypto(
                $fromCurrency,
                $target,
                $amount,
                $provider
            );

            return $this->applyMarkdown(
                $method,
                $result
            );
        }

        throw new RuntimeException(
            'Unsupported payout conversion configuration.'
        );
    }


    protected function convertFiat(
        string $fromCurrency,
        string $target,
        float $amount,
        string $provider
    ): array {
        if ($provider !== 'frankfurter') {
            throw new RuntimeException(
                'Unsupported fiat conversion provider.'
            );
        }

        $from = strtoupper(trim($fromCurrency));
        $to = strtoupper(trim($target));

        if ($from === $to) {
            return $this->result(
                $from,
                $to,
                $amount,
                1,
                'frankfurter'
            );
        }

        $response = Http::timeout(10)
            ->acceptJson()
            ->get(
                "https://api.frankfurter.dev/v2/rate/{$from}/{$to}"
            );

        if (!$response->successful()) {
            throw new RuntimeException(
                'Unable to retrieve the current currency conversion rate.'
            );
        }

        $data = $response->json();

        $rate = null;

        if (is_array($data)) {
            if (isset($data['rate'])) {
                $rate = (float) $data['rate'];
            } elseif (
                isset($data[0])
                && is_array($data[0])
                && isset($data[0]['rate'])
            ) {
                $rate = (float) $data[0]['rate'];
            }
        }

        if (!$rate || $rate <= 0) {
            throw new RuntimeException(
                'Currency conversion rate could not be resolved.'
            );
        }

        return $this->result(
            $from,
            $to,
            $amount,
            $rate,
            'frankfurter'
        );
    }


    protected function convertCrypto(
        string $fromCurrency,
        string $target,
        float $amount,
        string $provider
    ): array {
        if ($provider !== 'coingecko') {
            throw new RuntimeException(
                'Unsupported crypto conversion provider.'
            );
        }

        $apiKey = config(
            'services.coingecko.demo_api_key'
        );

        if (!$apiKey) {
            throw new RuntimeException(
                'CoinGecko Demo API key is not configured.'
            );
        }

        $assetMap = [
            'BTC' => 'bitcoin',
            'ETH' => 'ethereum',
            'USDT' => 'tether',
            'USDC' => 'usd-coin',
            'BNB' => 'binancecoin',
            'SOL' => 'solana',
            'XRP' => 'ripple',
            'ADA' => 'cardano',
            'DOGE' => 'dogecoin',
            'TRX' => 'tron',
        ];

        $target = strtoupper(trim($target));
        $from = strtoupper(trim($fromCurrency));

        $assetId = $assetMap[$target] ?? null;

        if (!$assetId) {
            throw new RuntimeException(
                "Crypto asset {$target} is not configured for conversion."
            );
        }

        $vsCurrency = strtolower($from);

        $response = Http::timeout(10)
            ->acceptJson()
            ->withHeaders([
                'x-cg-demo-api-key' => $apiKey,
            ])
            ->get(
                'https://api.coingecko.com/api/v3/simple/price',
                [
                    'ids' => $assetId,
                    'vs_currencies' => $vsCurrency,
                ]
            );

        if (!$response->successful()) {
            throw new RuntimeException(
                'Unable to retrieve the current crypto conversion rate.'
            );
        }

        $price = (float) (
            $response->json(
                "{$assetId}.{$vsCurrency}"
            ) ?? 0
        );

        if ($price <= 0) {
            throw new RuntimeException(
                'Crypto conversion rate could not be resolved.'
            );
        }

        /*
         * Example:
         * 1 USDT = NGN 1,349
         * Therefore:
         * 1 NGN = 1 / 1,349 USDT.
         */
        $rate = 1 / $price;

        return $this->result(
            $from,
            $target,
            $amount,
            $rate,
            'coingecko'
        );
    }


    protected function result(
        string $from,
        string $to,
        float $amount,
        float $rate,
        ?string $provider
    ): array {
        $gross = round(
            $amount * $rate,
            12
        );

        return [
            'converted' => true,
            'from_currency' => strtoupper($from),
            'to_currency' => strtoupper($to),
            'source_amount' => round($amount, 8),
            'rate' => $rate,

            /*
             * Amount before Admin markdown.
             */
            'gross_receive_amount' => $gross,

            /*
             * Final amount is adjusted later.
             */
            'receive_amount' => $gross,

            'provider' => $provider,
        ];
    }


    /**
     * Apply Admin-configured payout markdown AFTER conversion.
     *
     * Percentage:
     * 10 USDT - 10% = 9 USDT
     *
     * Fixed:
     * 10 USDT - 1 USDT = 9 USDT
     */
    protected function applyMarkdown(
        object $method,
        array $result
    ): array {
        $gross = (float) (
            $result['gross_receive_amount']
                ?? $result['receive_amount']
                ?? 0
        );

        $enabled = (bool) (
            $method->markdown_enabled ?? false
        );

        $type = strtolower(
            trim(
                $method->markdown_type
                    ?? 'percentage'
            )
        );

        $value = max(
            0,
            (float) (
                $method->markdown_value ?? 0
            )
        );

        $markdown = 0;

        if ($enabled && $gross > 0 && $value > 0) {
            if ($type === 'percentage') {
                /*
                 * Never permit more than 100%.
                 */
                $percentage = min(
                    $value,
                    100
                );

                $markdown =
                    $gross * ($percentage / 100);

            } elseif ($type === 'fixed') {
                /*
                 * Fixed markdown is denominated in the
                 * target payout currency/asset.
                 */
                $markdown = min(
                    $value,
                    $gross
                );
            }
        }

        $final = max(
            0,
            $gross - $markdown
        );

        $result['markdown_applied'] =
            $enabled && $markdown > 0;

        $result['markdown_amount'] =
            round($markdown, 12);

        $result['receive_amount'] =
            round($final, 12);

        return $result;
    }
}
