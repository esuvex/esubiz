<?php

namespace App\Services\Core;

class CoreGatewayCurrencyService
{
    /**
     * Determine which currency should be sent to a payment gateway.
     *
     * The gateway supplies its supported currencies. Core decides
     * whether the requested currency can actually be charged.
     */
    public function resolve(
        string $primaryCurrency,
        ?string $secondaryCurrency,
        bool $secondaryEnabled,
        string $requestedCurrency,
        array $gatewayCurrencies
    ): array {
        $primaryCurrency = strtoupper(trim($primaryCurrency));
        $secondaryCurrency = $secondaryCurrency
            ? strtoupper(trim($secondaryCurrency))
            : null;
        $requestedCurrency = strtoupper(trim($requestedCurrency));

        $supported = array_values(array_unique(
            array_map(
                fn ($currency) => strtoupper(trim($currency)),
                $gatewayCurrencies
            )
        ));

        $secondaryAvailable =
            $secondaryEnabled &&
            $secondaryCurrency !== null &&
            in_array($secondaryCurrency, $supported, true);

        $requestedSupported =
            in_array($requestedCurrency, $supported, true);

        /*
         * Requested currency can be charged directly.
         */
        if ($requestedSupported) {
            return [
                'currency' => $requestedCurrency,
                'supported' => true,
                'fallback' => false,
                'checkout_available' => true,
                'reason' => 'gateway_supports_requested_currency',
            ];
        }

        /*
         * Secondary currency was requested but the gateway
         * does not support it. Fall back to the primary currency
         * if the gateway supports the primary currency.
         */
        if (
            $requestedCurrency === $secondaryCurrency &&
            in_array($primaryCurrency, $supported, true)
        ) {
            return [
                'currency' => $primaryCurrency,
                'supported' => true,
                'fallback' => true,
                'checkout_available' => true,
                'reason' => 'secondary_currency_not_supported_by_gateway',
            ];
        }

        /*
         * No supported checkout currency is available.
         */
        return [
            'currency' => null,
            'supported' => false,
            'fallback' => false,
            'checkout_available' => false,
            'reason' => 'gateway_supports_neither_requested_nor_primary_currency',
        ];
    }

    /**
     * Determine whether secondary-currency checkout is available
     * through a specific gateway.
     */
    public function supportsSecondary(
        string $secondaryCurrency,
        array $gatewayCurrencies
    ): bool {
        return in_array(
            strtoupper(trim($secondaryCurrency)),
            array_map(
                fn ($currency) => strtoupper(trim($currency)),
                $gatewayCurrencies
            ),
            true
        );
    }
}
