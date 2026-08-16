<?php

namespace App\Services\Core;

class CoreCheckoutCurrencyService
{
    public function __construct(
        protected CoreCurrencyService $currencyService,
        protected CoreCurrencyConverter $converter,
        protected CoreGatewayCurrencyService $gatewayCurrency,
        protected CoreTransactionService $transactionService
    ) {
    }

    /**
     * Resolve the final checkout currency and amount.
     */
    public function resolve(
        ?int $workspaceId,
        float $baseAmount,
        string $requestedCurrency,
        array $gatewayCurrencies
    ): array {
        $settings = $this->currencyService
            ->getCurrencies($workspaceId);

        $primary = $settings['primary'];
        $requestedCurrency = strtoupper(trim($requestedCurrency));

        $gateway = $this->gatewayCurrency->resolve(
            $primary,
            $settings['secondary'],
            $settings['secondary_enabled'],
            $requestedCurrency,
            $gatewayCurrencies
        );

        if (!$gateway['checkout_available']) {
            return [
                'checkout_available' => false,
                'currency' => null,
                'amount' => null,
                'base_amount' => $baseAmount,
                'fallback' => false,
                'reason' => $gateway['reason'],
            ];
        }

        if ($gateway['currency'] === $primary) {
            return [
                'checkout_available' => true,
                'currency' => $primary,
                'amount' => round($baseAmount, 2),
                'base_amount' => $baseAmount,
                'fallback' => $gateway['fallback'],
                'reason' => $gateway['reason'],
            ];
        }

        $converted = $this->converter->convertForSite(
            $workspaceId,
            $baseAmount
        );

        return [
            'checkout_available' => true,
            'currency' => $gateway['currency'],
            'amount' => $converted['converted_amount'],
            'base_amount' => $baseAmount,
            'rate' => $converted['rate'],
            'margin_type' => $converted['margin_type'],
            'margin_value' => $converted['margin_value'],
            'fallback' => false,
            'reason' => $gateway['reason'],
        ];
    }

    /**
     * Resolve checkout and record the actual charge transaction.
     *
     * The recorded transaction uses the final currency and amount
     * approved for the selected payment gateway.
     */
    public function recordCheckout(
        ?int $workspaceId,
        string $sourceType,
        int|string|null $sourceId,
        string $transactionType,
        float $baseAmount,
        string $requestedCurrency,
        array $gatewayCurrencies,
        array $data = []
    ): array {
        $resolved = $this->resolve(
            $workspaceId,
            $baseAmount,
            $requestedCurrency,
            $gatewayCurrencies
        );

        if (!$resolved['checkout_available']) {
            return $resolved;
        }

        $settings = $this->currencyService
            ->getCurrencies($workspaceId);

        $this->transactionService->record(
            $workspaceId,
            $sourceType,
            $sourceId,
            $transactionType,
            (float) $resolved['amount'],
            $resolved['currency'],
            array_merge(
                $data,
                [
                    'base_amount' => $baseAmount,
                    'base_currency' => $settings['primary'],
                    'requested_currency' =>
                        strtoupper(trim($requestedCurrency)),
                    'checkout_currency' =>
                        $resolved['currency'],
                    'currency_fallback' =>
                        $resolved['fallback'],
                    'gateway_currency_resolution' =>
                        $resolved['reason'],
                ]
            )
        );

        return $resolved;
    }
}
