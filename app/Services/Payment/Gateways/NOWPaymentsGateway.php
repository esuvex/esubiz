<?php

namespace App\Services\Payment\Gateways;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class NOWPaymentsGateway implements CorePaymentGatewayInterface
{
    protected string $baseUrl = 'https://api.nowpayments.io/v1';

    protected function apiKey(array $context = []): string
    {
        $key = $context['api_key']
            ?? config('services.nowpayments.api_key');

        if (!$key) {
            throw new RuntimeException(
                'NOWPayments API key is not configured.'
            );
        }

        return $key;
    }

    public function initialize(
        object $transaction,
        object $attempt,
        array $context = []
    ): array {
        $apiKey = $this->apiKey($context);

        $payCurrency = strtoupper(
            $context['pay_currency']
                ?? $transaction->currency
        );

        $response = Http::withHeaders([
                'x-api-key' => $apiKey,
                'Content-Type' => 'application/json',
            ])
            ->acceptJson()
            ->post(
                $this->baseUrl . '/payment',
                [
                    'price_amount' => (float) $transaction->amount,
                    'price_currency' => strtoupper($transaction->currency),
                    'pay_currency' => $payCurrency,
                    'order_id' => $transaction->reference,
                    'order_description' => $context['description']
                        ?? 'Esubiz payment',
                    'ipn_callback_url' => $context['callback_url'] ?? null,
                ]
            );

        if (!$response->successful()) {
            throw new RuntimeException(
                $response->json('message')
                    ?? 'NOWPayments payment initialization failed.'
            );
        }

        $data = $response->json();

        return [
            'status' => 'processing',
            'gateway_reference' => $data['payment_id']
                ?? $transaction->reference,
            'authorization_url' => $data['pay_address']
                ?? null,
            'pay_address' => $data['pay_address'] ?? null,
            'pay_amount' => $data['pay_amount'] ?? null,
            'pay_currency' => $data['pay_currency'] ?? $payCurrency,
            'response' => $data,
        ];
    }

    public function verify(
        object $transaction,
        object $attempt,
        array $context = []
    ): array {
        $apiKey = $this->apiKey($context);

        $paymentId = $attempt->gateway_reference
            ?: $transaction->reference;

        $response = Http::withHeaders([
                'x-api-key' => $apiKey,
            ])
            ->acceptJson()
            ->get(
                $this->baseUrl . '/payment/' .
                urlencode((string) $paymentId)
            );

        if (!$response->successful()) {
            return [
                'status' => 'failed',
                'gateway_reference' => $paymentId,
                'response' => $response->json(),
            ];
        }

        $data = $response->json();

        $status = strtolower(
            (string) ($data['payment_status'] ?? '')
        );

        return [
            'status' => in_array(
                $status,
                ['finished', 'confirmed'],
                true
            )
                ? 'successful'
                : (
                    in_array(
                        $status,
                        ['failed', 'expired', 'refunded'],
                        true
                    )
                        ? 'failed'
                        : 'processing'
                ),
            'gateway_reference' => $data['payment_id']
                ?? $paymentId,
            'response' => $data,
        ];
    }

    public function refund(
        object $transaction,
        object $attempt,
        float $amount,
        array $context = []
    ): array {
        return [
            'status' => 'failed',
            'gateway_reference' => $attempt->gateway_reference
                ?? $transaction->reference,
            'response' => [
                'message' =>
                    'NOWPayments refunds require provider-specific processing and are not automatically executed by this adapter.',
            ],
        ];
    }

    public function supportedCurrencies(): array
    {
        return [
            'BTC',
            'ETH',
            'USDT',
            'USDC',
            'LTC',
            'TRX',
            'BNB',
        ];
    }
}
