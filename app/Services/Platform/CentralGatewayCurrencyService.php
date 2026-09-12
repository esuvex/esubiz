<?php

namespace App\Services\Platform;

class CentralGatewayCurrencyService
{
    /*
     * ESUBIZ_GATEWAY_CURRENCY_CAPABILITY_V1
     *
     * Central currency capability resolver for payment gateways.
     *
     * Important:
     *
     * A gateway is offered at checkout only when the gateway
     * explicitly supports the resolved checkout currency.
     *
     * Support can come from:
     *
     * 1. Central Admin gateway currency configuration.
     * 2. Gateway adapter/runtime declarations.
     *
     * We deliberately do NOT assume that every account for a
     * provider supports every currency supported by that provider.
     */
    public function __construct(
        protected CentralSiteSettingsService $settings
    ) {
    }

    public function normalizeGateway(
        ?string $gateway
    ): ?string {
        if ($gateway === null) {
            return null;
        }

        $gateway = strtolower(
            trim($gateway)
        );

        $gateway = preg_replace(
            '/[^a-z0-9_\-]/',
            '',
            $gateway
        );

        return $gateway !== ''
            ? $gateway
            : null;
    }

    public function normalizeCurrency(
        ?string $currency
    ): ?string {
        if ($currency === null) {
            return null;
        }

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

    public function configuredCurrencies(
        string $gateway
    ): array {
        $gateway = $this->normalizeGateway(
            $gateway
        );

        if ($gateway === null) {
            return [];
        }

        $value = $this->settings->get(
            'payments.gateway_currency_support.'
                . $gateway
        );

        return $this->normalizeCurrencyList(
            $value
        );
    }

    public function supportedCurrencies(
        string $gateway,
        array $runtimeCurrencies = []
    ): array {
        $configured =
            $this->configuredCurrencies(
                $gateway
            );

        /*
         * Admin configuration is authoritative when present.
         *
         * This allows Esubiz to reflect the exact currencies
         * enabled on the merchant's gateway account rather than
         * merely the provider's theoretical global capabilities.
         */
        if ($configured !== []) {
            return $configured;
        }

        return $this->normalizeCurrencyList(
            $runtimeCurrencies
        );
    }

    public function supports(
        string $gateway,
        string $currency,
        array $runtimeCurrencies = []
    ): bool {
        $currency = $this->normalizeCurrency(
            $currency
        );

        if ($currency === null) {
            return false;
        }

        return in_array(
            $currency,
            $this->supportedCurrencies(
                $gateway,
                $runtimeCurrencies
            ),
            true
        );
    }

    public function availableGateways(
        array $gateways,
        string $currency
    ): array {
        $currency = $this->normalizeCurrency(
            $currency
        );

        if ($currency === null) {
            return [];
        }

        return array_values(
            array_filter(
                $gateways,
                function ($gateway) use ($currency) {
                    if (is_string($gateway)) {
                        return $this->supports(
                            $gateway,
                            $currency
                        );
                    }

                    if (!is_array($gateway)) {
                        return false;
                    }

                    $key =
                        $gateway['key']
                        ?? $gateway['gateway']
                        ?? $gateway['slug']
                        ?? $gateway['name']
                        ?? null;

                    if (!is_string($key)) {
                        return false;
                    }

                    $runtimeCurrencies =
                        $gateway['supported_currencies']
                        ?? $gateway['currencies']
                        ?? [];

                    return $this->supports(
                        $key,
                        $currency,
                        is_array($runtimeCurrencies)
                            ? $runtimeCurrencies
                            : []
                    );
                }
            )
        );
    }

    public function checkoutCurrencyContext(
        string $gateway,
        array $money,
        array $runtimeCurrencies = []
    ): array {
        $currency =
            $this->normalizeCurrency(
                $money['currency'] ?? null
            );

        $supported =
            $currency !== null
            && $this->supports(
                $gateway,
                $currency,
                $runtimeCurrencies
            );

        return [
            'gateway' =>
                $this->normalizeGateway(
                    $gateway
                ),
            'currency' =>
                $currency,
            'amount' =>
                $money['amount'] ?? null,
            'formatted' =>
                $money['formatted'] ?? null,
            'converted' =>
                (bool) (
                    $money['converted']
                    ?? false
                ),
            'gateway_supports_currency' =>
                $supported,
            'can_checkout' =>
                $supported,
        ];
    }

    protected function normalizeCurrencyList(
        mixed $value
    ): array {
        if (is_string($value)) {
            $decoded = json_decode(
                $value,
                true
            );

            if (is_array($decoded)) {
                $value = $decoded;
            } else {
                $value = preg_split(
                    '/[\s,;|]+/',
                    $value,
                    -1,
                    PREG_SPLIT_NO_EMPTY
                );
            }
        }

        if (!is_array($value)) {
            return [];
        }

        $currencies = [];

        foreach ($value as $currency) {
            $currency =
                $this->normalizeCurrency(
                    is_scalar($currency)
                        ? (string) $currency
                        : null
                );

            if ($currency !== null) {
                $currencies[] = $currency;
            }
        }

        $currencies = array_values(
            array_unique($currencies)
        );

        sort($currencies);

        return $currencies;
    }
}
