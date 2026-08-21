<?php

namespace App\Services\Payment\Gateways;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class PaystackGateway implements CorePaymentGatewayInterface
{
    public function initialize(
        object $transaction,
        object $attempt,
        array $context = []
    ): array {
        $secretKey = $this->secretKey($context);

        $response = Http::withToken($secretKey)
            ->acceptJson()
            ->post('https://api.paystack.co/transaction/initialize', [
                'email' => $context['email'] ?? null,
                'amount' => (int) round(((float) $transaction->amount) * 100),
                'currency' => strtoupper($transaction->currency),
                'reference' => $transaction->reference,
                'callback_url' => $context['callback_url'] ?? null,
                'metadata' => [
                    'payment_transaction_id' => $transaction->id,
                    'payment_attempt_id' => $attempt->id,
                    'reference' => $transaction->reference,
                ],
            ]);

        if (!$response->successful() || !$response->json('status')) {
            throw new RuntimeException(
                $response->json('message')
                    ?? 'Paystack payment initialization failed.'
            );
        }

        $data = $response->json('data');

        return [
            'status' => 'processing',
            'gateway_reference' => $data['reference'] ?? $transaction->reference,
            'authorization_url' => $data['authorization_url'] ?? null,
            'access_code' => $data['access_code'] ?? null,
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
                'https://api.paystack.co/transaction/verify/' .
                urlencode($reference)
            );

        if (!$response->successful() || !$response->json('status')) {
            return [
                'status' => 'failed',
                'gateway_reference' => $reference,
                'response' => $response->json(),
            ];
        }

        $data = $response->json('data');

        return [
            'status' => ($data['status'] ?? null) === 'success'
                    ? 'successful'
                    : 'failed',
            'gateway_reference' => $data['reference'] ?? $reference,
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

        $reference = $attempt->gateway_reference
            ?: $transaction->reference;

        $payload = [
            'transaction' => $reference,
        ];

        if ($amount > 0) {
            $payload['amount'] = (int) round($amount * 100);
        }

        $response = Http::withToken($secretKey)
            ->acceptJson()
            ->post(
                'https://api.paystack.co/refund',
                $payload
            );

        return [
            'status' => $response->successful()
                && $response->json('status')
                    ? 'successful'
                    : 'failed',
            'gateway_reference' => $reference,
            'response' => $response->json(),
        ];
    }

    public function supportedCurrencies(): array
    {
        return ['NGN', 'USD', 'GHS', 'ZAR', 'KES'];
    }

    protected function secretKey(array $context): string
    {
        $key = $context['secret_key']
            ?? config('services.paystack.secret');

        if (!$key) {
            throw new RuntimeException(
                'Paystack secret key is not configured.'
            );
        }

        return $key;
    }
}
