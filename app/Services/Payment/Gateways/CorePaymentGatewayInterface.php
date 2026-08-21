<?php

namespace App\Services\Payment\Gateways;

interface CorePaymentGatewayInterface
{
    /**
     * Initialize a payment attempt with the provider.
     */
    public function initialize(
        object $transaction,
        object $attempt,
        array $context = []
    ): array;

    /**
     * Verify a payment with the provider.
     */
    public function verify(
        object $transaction,
        object $attempt,
        array $context = []
    ): array;

    /**
     * Refund a successful payment.
     */
    public function refund(
        object $transaction,
        object $attempt,
        float $amount,
        array $context = []
    ): array;

    /**
     * Return currencies supported by this gateway.
     */
    public function supportedCurrencies(): array;
}
