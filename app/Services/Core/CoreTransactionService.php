<?php

namespace App\Services\Core;

use App\Events\Core\CoreTransactionRecorded;
use InvalidArgumentException;

class CoreTransactionService
{
    public function __construct(
        protected CoreCurrencyService $currencyService,
        protected CoreCurrencyConverter $currencyConverter
    ) {
    }

    /**
     * Record a module/Core transaction using the site's
     * configured primary currency automatically.
     *
     * Modules do not need to implement currency logic.
     */
    public function record(
        ?int $workspaceId,
        string $sourceType,
        int|string|null $sourceId,
        string $transactionType,
        float $amount,
        ?string $currency = null,
        array $data = []
    ): void {
        $settings = $this->currencyService
            ->getCurrencies($workspaceId);

        $currency = strtoupper(
            trim($currency ?? $settings['primary'])
        );

        if (!$this->currencyService->isEnabled(
            $workspaceId,
            $currency
        )) {
            throw new InvalidArgumentException(
                "Currency {$currency} is not enabled for this site."
            );
        }

        /*
         * The originating transaction remains in its
         * actual/base currency.
         */
        CoreTransactionRecorded::dispatch(
            $sourceType,
            $sourceId,
            $transactionType,
            $amount,
            $currency,
            array_merge(
                $data,
                [
                    'workspace_id' => $workspaceId,
                    'website_id' => $data['website_id'] ?? null,
                    'item_type' => $data['item_type'] ?? $data['product_type'] ?? null,
                    'item_id' => $data['item_id'] ?? $data['product_id'] ?? null,
                    'item_name' => $data['item_name']
                        ?? $data['product_name']
                        ?? $data['name']
                        ?? $data['title']
                        ?? null,
                ]
            )
        );
    }

    /**
     * Return the site's currency context for a module.
     *
     * This is the single currency context modules should use
     * for displaying/pricing the same transaction.
     */
    public function currencyContext(
        ?int $workspaceId,
        float $baseAmount
    ): array {
        $settings = $this->currencyService
            ->getCurrencies($workspaceId);

        $primary = $settings['primary'];

        $context = [
            'primary' => [
                'currency' => $primary,
                'amount' => round($baseAmount, 2),
            ],
            'secondary' => null,
        ];

        if (
            $settings['secondary_enabled'] &&
            $settings['secondary']
        ) {
            $context['secondary'] =
                $this->currencyConverter->convertForSite(
                    $workspaceId,
                    $baseAmount
                );
        }

        return $context;
    }

    /**
     * Return the currencies currently available to the site.
     */
    public function enabledCurrencies(
        ?int $workspaceId
    ): array {
        $settings = $this->currencyService
            ->getCurrencies($workspaceId);

        $currencies = [
            $settings['primary'],
        ];

        if (
            $settings['secondary_enabled'] &&
            $settings['secondary']
        ) {
            $currencies[] = $settings['secondary'];
        }

        return array_values(array_unique($currencies));
    }
}
