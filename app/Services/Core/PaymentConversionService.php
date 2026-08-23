<?php

namespace App\Services\Core;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class PaymentConversionService
{
    /**
     * Calculate the actual amount the customer must pay through
     * an Online or Offline payment gateway.
     *
     * Flow:
     * Base amount
     * -> conversion (optional)
     * -> markup (optional)
     * -> final gateway charge
     *
     * The original Esubiz amount is never destroyed. It is returned
     * separately so Wallet Funding / Marketplace accounting can retain
     * the canonical base amount.
     */
    public function calculate(
        object $method,
        string $fromCurrency,
        float $amount
    ): array {
        if ($amount <= 0) {
            throw new RuntimeException(
                'Payment amount must be greater than zero.'
            );
        }

        $fromCurrency = strtoupper(trim($fromCurrency));

        $result = [
            'converted' => false,
            'from_currency' => $fromCurrency,
            'to_currency' => $fromCurrency,
            'source_amount' => round($amount, 8),
            'rate' => 1,
            'converted_amount' => round($amount, 8),
            'gross_payment_amount' => round($amount, 8),
            'payment_amount' => round($amount, 8),
            'provider' => null,

            'markup_enabled' => false,
            'markup_type' => null,
            'markup_value' => 0,
            'markup_amount' => 0,
        ];

        if ((bool) ($method->conversion_enabled ?? false)) {
            $result = $this->convert(
                $method,
                $fromCurrency,
                $amount
            );
        }

        return $this->applyMarkup(
            $method,
            $result
        );
    }

    protected function convert(
        object $method,
        string $fromCurrency,
        float $amount
    ): array {
        $type = strtolower(
            trim((string) ($method->conversion_type ?? 'none'))
        );

        $provider = strtolower(
            trim((string) ($method->conversion_provider ?? ''))
        );

        $target = strtoupper(
            trim((string) ($method->conversion_target ?? ''))
        );

        if ($target === '') {
            throw new RuntimeException(
                'No conversion target is configured for this payment gateway.'
            );
        }

        if ($provider === 'manual') {
            $rate = (float) (
                $method->manual_conversion_rate ?? 0
            );

            if ($rate <= 0) {
                throw new RuntimeException(
                    'Manual payment conversion rate is not configured.'
                );
            }

            return $this->result(
                $fromCurrency,
                $target,
                $amount,
                $rate,
                'manual'
            );
        }

        if ($type === 'fiat') {
            return $this->convertFiat(
                $fromCurrency,
                $target,
                $amount,
                $provider
            );
        }

        if ($type === 'crypto') {
            return $this->convertCrypto(
                $fromCurrency,
                $target,
                $amount,
                $provider
            );
        }

        throw new RuntimeException(
            'Unsupported payment conversion configuration.'
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
                'Unsupported fiat payment conversion provider.'
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
                'Unsupported crypto payment conversion provider.'
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
         * CoinGecko returns the value of one crypto unit
         * in the base currency.
         *
         * Example:
         * 1 USDT = NGN 1,349
         * NGN -> USDT rate = 1 / 1,349.
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
        $convertedAmount = round(
            $amount * $rate,
            12
        );

        return [
            'converted' => true,
            'from_currency' => strtoupper($from),
            'to_currency' => strtoupper($to),
            'source_amount' => round($amount, 8),
            'rate' => $rate,
            'converted_amount' => $convertedAmount,
            'gross_payment_amount' => $convertedAmount,
            'payment_amount' => $convertedAmount,
            'provider' => $provider,

            'markup_enabled' => false,
            'markup_type' => null,
            'markup_value' => 0,
            'markup_amount' => 0,
        ];
    }

    protected function applyMarkup(
        object $method,
        array $result
    ): array {
        if (!(bool) ($method->markup_enabled ?? false)) {
            return $result;
        }

        $type = strtolower(
            trim((string) ($method->markup_type ?? 'percentage'))
        );

        $value = max(
            0,
            (float) ($method->markup_value ?? 0)
        );

        if ($value <= 0) {
            return $result;
        }

        $gross = (float) $result['gross_payment_amount'];

        if ($type === 'percentage') {
            $markupAmount = $gross * ($value / 100);
        } elseif ($type === 'fixed') {
            $markupAmount = $value;
        } else {
            throw new RuntimeException(
                'Unsupported payment markup type.'
            );
        }

        $markupAmount = round($markupAmount, 12);
        $finalAmount = round(
            $gross + $markupAmount,
            12
        );

        $result['markup_enabled'] = true;
        $result['markup_type'] = $type;
        $result['markup_value'] = $value;
        $result['markup_amount'] = $markupAmount;
        $result['payment_amount'] = $finalAmount;

        return $result;
    }
}
