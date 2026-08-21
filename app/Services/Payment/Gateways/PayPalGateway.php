<?php

namespace App\Services\Payment\Gateways;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class PayPalGateway implements CorePaymentGatewayInterface
{
    protected function baseUrl(array $context = []): string
    {
        $environment = $context['environment']
            ?? config('services.paypal.environment', 'sandbox');

        return $environment === 'production'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    protected function accessToken(array $context = []): string
    {
        $clientId = $context['client_id']
            ?? config('services.paypal.client_id');

        $clientSecret = $context['client_secret']
            ?? config('services.paypal.client_secret');

        if (!$clientId || !$clientSecret) {
            throw new RuntimeException(
                'PayPal client ID and client secret are not configured.'
            );
        }

        $response = Http::asForm()
            ->withBasicAuth($clientId, $clientSecret)
            ->acceptJson()
            ->post(
                $this->baseUrl($context) . '/v1/oauth2/token',
                ['grant_type' => 'client_credentials']
            );

        if (!$response->successful() || !$response->json('access_token')) {
            throw new RuntimeException(
                $response->json('error_description')
                    ?? 'PayPal authentication failed.'
            );
        }

        return $response->json('access_token');
    }

    public function initialize(
        object $transaction,
        object $attempt,
        array $context = []
    ): array {
        $token = $this->accessToken($context);

        $response = Http::withToken($token)
            ->acceptJson()
            ->post(
                $this->baseUrl($context) . '/v2/checkout/orders',
                [
                    'intent' => 'CAPTURE',
                    'purchase_units' => [
                        [
                            'reference_id' => $transaction->reference,
                            'amount' => [
                                'currency_code' => strtoupper($transaction->currency),
                                'value' => number_format(
                                    (float) $transaction->amount,
                                    2,
                                    '.',
                                    ''
                                ),
                            ],
                        ],
                    ],
                    'application_context' => [
                        'return_url' => $context['callback_url'] ?? null,
                        'cancel_url' => $context['cancel_url'] ?? null,
                    ],
                ]
            );

        if (!$response->successful()) {
            throw new RuntimeException(
                $response->json('message')
                    ?? 'PayPal order creation failed.'
            );
        }

        $data = $response->json();

        $approvalUrl = null;

        foreach ($data['links'] ?? [] as $link) {
            if (($link['rel'] ?? null) === 'approve') {
                $approvalUrl = $link['href'] ?? null;
                break;
            }
        }

        return [
            'status' => 'processing',
            'gateway_reference' => $data['id'] ?? $transaction->reference,
            'authorization_url' => $approvalUrl,
            'response' => $data,
        ];
    }

    public function verify(
        object $transaction,
        object $attempt,
        array $context = []
    ): array {
        $token = $this->accessToken($context);

        $orderId = $attempt->gateway_reference
            ?: $transaction->reference;

        $response = Http::withToken($token)
            ->acceptJson()
            ->get(
                $this->baseUrl($context) .
                '/v2/checkout/orders/' .
                urlencode($orderId)
            );

        if (!$response->successful()) {
            return [
                'status' => 'failed',
                'gateway_reference' => $orderId,
                'response' => $response->json(),
            ];
        }

        $data = $response->json();

        return [
            'status' => ($data['status'] ?? null) === 'COMPLETED'
                ? 'successful'
                : 'failed',
            'gateway_reference' => $data['id'] ?? $orderId,
            'response' => $data,
        ];
    }

    public function refund(
        object $transaction,
        object $attempt,
        float $amount,
        array $context = []
    ): array {
        $token = $this->accessToken($context);

        $captureId = $context['capture_id'] ?? null;

        if (!$captureId) {
            return [
                'status' => 'failed',
                'gateway_reference' => $attempt->gateway_reference
                    ?? $transaction->reference,
                'response' => [
                    'message' => 'PayPal capture ID is required for refund.',
                ],
            ];
        }

        $payload = [];

        if ($amount > 0) {
            $payload['amount'] = [
                'value' => number_format(
                    $amount,
                    2,
                    '.',
                    ''
                ),
                'currency_code' => strtoupper($transaction->currency),
            ];
        }

        $response = Http::withToken($token)
            ->acceptJson()
            ->post(
                $this->baseUrl($context) .
                '/v2/payments/captures/' .
                urlencode((string) $captureId) .
                '/refund',
                $payload
            );

        return [
            'status' => $response->successful()
                ? 'successful'
                : 'failed',
            'gateway_reference' => $captureId,
            'response' => $response->json(),
        ];
    }

    public function supportedCurrencies(): array
    {
        return [
            'USD',
            'EUR',
            'GBP',
            'AUD',
            'CAD',
            'JPY',
            'SGD',
            'HKD',
            'NZD',
            'CHF',
        ];
    }
}
