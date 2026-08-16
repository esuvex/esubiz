<?php

namespace App\Services\Core;

class CoreModuleCurrencyService
{
    public function __construct(
        protected CoreTransactionService $transactionService
    ) {
    }

    /**
     * Return the currency context every module should use.
     *
     * Modules provide only their base amount. Core determines
     * the site's primary and optional secondary currency.
     */
    public function priceContext(
        ?int $workspaceId,
        float $baseAmount
    ): array {
        return $this->transactionService->currencyContext(
            $workspaceId,
            $baseAmount
        );
    }

    /**
     * Return the currencies currently available to the module.
     */
    public function currencies(?int $workspaceId): array
    {
        return $this->transactionService
            ->enabledCurrencies($workspaceId);
    }

    /**
     * Return searchable currency selector results.
     *
     * The complete catalogue remains centralized in Core.
     */
    public function searchCurrencies(
        string $query = '',
        int $limit = 25
    ): array {
        return app(CoreCurrencyCatalog::class)
            ->search($query, $limit);
    }

    /**
     * Return the site's complete currency selector context.
     */
    public function selectorContext(
        ?int $workspaceId,
        string $query = '',
        int $limit = 25
    ): array {
        $settings = app(CoreCurrencyService::class)
            ->getCurrencies($workspaceId);

        return [
            'primary' => $settings['primary'],
            'secondary' => [
                'enabled' => $settings['secondary_enabled'],
                'currency' => $settings['secondary'],
            ],
            'results' => $this->searchCurrencies(
                $query,
                $limit
            ),
        ];
    }

    /**
     * Determine the site's primary currency.
     */
    public function primaryCurrency(?int $workspaceId): string
    {
        return $this->transactionService
            ->currencyContext($workspaceId, 0)['primary']['currency'];
    }

    /**
     * Determine whether a secondary currency is active.
     */
    public function hasSecondaryCurrency(
        ?int $workspaceId
    ): bool {
        return count(
            $this->currencies($workspaceId)
        ) > 1;
    }
}
