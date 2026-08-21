<?php

namespace App\Services\Payment;

use App\Services\Payment\Gateways\CorePaymentGatewayInterface;
use InvalidArgumentException;

class CorePaymentGatewayManager
{
    /**
     * Registered gateway implementations.
     *
     * Example:
     * [
     *     'paystack' => PaystackGateway::class,
     *     'flutterwave' => FlutterwaveGateway::class,
     * ]
     */
    protected array $gateways = [];

    public function __construct()
    {
        $this->registerConfigured(
            config('payment.gateways', [])
        );
    }

    public function registerConfigured(array $gateways): void
    {
        foreach ($gateways as $slug => $gatewayClass) {
            $this->register($slug, $gatewayClass);
        }
    }

    public function register(
        string $slug,
        string $gatewayClass
    ): void {
        if (!is_a(
            $gatewayClass,
            CorePaymentGatewayInterface::class,
            true
        )) {
            throw new InvalidArgumentException(
                "{$gatewayClass} must implement CorePaymentGatewayInterface."
            );
        }

        $this->gateways[$slug] = $gatewayClass;
    }

    public function has(string $slug): bool
    {
        return isset($this->gateways[$slug]);
    }

    public function resolve(
        string $slug
    ): CorePaymentGatewayInterface {
        if (!$this->has($slug)) {
            throw new InvalidArgumentException(
                "Payment gateway [{$slug}] is not registered."
            );
        }

        return app($this->gateways[$slug]);
    }

    public function registered(): array
    {
        return array_keys($this->gateways);
    }
}
