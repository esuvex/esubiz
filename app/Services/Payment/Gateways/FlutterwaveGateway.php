<?php

namespace App\Services\Payment\Gateways;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class FlutterwaveGateway implements CorePaymentGatewayInterface
{
    protected string $baseUrl = 'https://api.flutterwave.com/v3';

    public function initialize(
        object $transaction,
        object $attempt,
        array $context = []
    ): array {
        $secretKey = $this->secretKey($context);

        $response = Http::withToken($secretKey)
            ->acceptJson()
            ->post($this->baseUrl . '/payments', [
                'tx_ref' => $transaction->reference,
                'amount' => (float) $transaction->amount,
                'currency' => strtoupper($transaction->currency),
                'redirect_url' => $context['callback_url'] ?? null,
                'customer' => [
                    'email' => $context['email'] ?? null,
                    'name' => $context['customer_name'] ?? null,
                ],
                'meta' => [
                    'payment_transaction_id' => $transaction->id,
                    'payment_attempt_id' => $attempt->id,
                ],
            ]);

        if (!$response->successful() || $response->json('status') !== 'success') {
            throw new RuntimeException(
                $response->json('message')
                    ?? 'Flutterwave payment initialization failed.'
            );
        }

        $data = $response->json('data', []);

        return [
            'status' => 'processing',
            'gateway_reference' => $data['tx_ref']
                ?? $transaction->reference,
            'authorization_url' => $data['link'] ?? null,
            'response' => $response->json(),
        ];
    }

    public function verify(
        object $transaction,
        object $attempt,
        array $context = []
    ): array {
        $secretKey = $this->secretKey($context);

        $reference = $attempt->gateway_reference
            ?: $transaction->reference;

        $response = Http::withToken($secretKey)
            ->acceptJson()
            ->get(
                $this->baseUrl . '/transactions/verify_by_reference',
                [
                    'tx_ref' => $reference,
                ]
            );

        if (!$response->successful()) {
            return [
                'status' => 'failed',
                'gateway_reference' => $reference,
                'response' => $response->json(),
            ];
        }

        $data = $response->json('data', []);

        return [
            'status' => ($data['status'] ?? null) === 'successful'
                ? 'successful'
                : 'failed',
            'gateway_reference' => $data['tx_ref'] ?? $reference,
            'response' => $response->json(),
        ];
    }

    public function refund(
        object $transaction,
        object $attempt,
        float $amount,
        array $context = []
    ): array {
        $secretKey = $this->secretKey($context);

        $transactionId = $context['gateway_transaction_id'] ?? null;

        if (!$transactionId) {
            return [
                'status' => 'failed',
                'gateway_reference' => $attempt->gateway_reference
                    ?? $transaction->reference,
                'response' => [
                    'message' => 'Flutterwave transaction ID is required for refund.',
                ],
            ];
        }

        $response = Http::withToken($secretKey)
            ->acceptJson()
            ->post(
                $this->baseUrl . '/transactions/' .
                urlencode((string) $transactionId) .
                '/refund',
                [
                    'amount' => $amount,
                ]
            );

        return [
            'status' => $response->successful()
                && $response->json('status') === 'success'
                    ? 'successful'
                    : 'failed',
            'gateway_reference' => $attempt->gateway_reference
                ?? $transaction->reference,
            'response' => $response->json(),
        ];
    }

    public function supportedCurrencies(): array
    {
        return [
            'NGN',
            'USD',
            'GBP',
            'EUR',
            'KES',
            'GHS',
            'ZAR',
            'TZS',
            'UGX',
            'RWF',
            'ZMW',
        ];
    }

    protected function secretKey(array $context): string
    {
        $key = $context['secret_key']
            ?? config('services.flutterwave.secret');

        if (!$key) {
            throw new RuntimeException(
                'Flutterwave secret key is not configured.'
            );
        }

        return $key;
    }
}
