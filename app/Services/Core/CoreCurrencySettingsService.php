<?php

namespace App\Services\Core;

use InvalidArgumentException;

class CoreCurrencySettingsService
{
    public function __construct(
        protected CoreCurrencyService $currencyService,
        protected CoreCurrencyCatalog $catalog
    ) {
    }

    public function get(?int $workspaceId): array
    {
        $settings = $this->currencyService
            ->getCurrencies($workspaceId);

        return [
            'primary_currency' => $settings['primary'],
            'secondary' => [
                'enabled' => $settings['secondary_enabled'],
                'currency' => $settings['secondary'],
            ],
            'conversion' => [
                'automatic' => $settings['automatic_conversion'],
                'manual_rate' => $settings['manual_rate'],
            ],
            'margin' => [
                'type' => $settings['margin_type'],
                'value' => $settings['margin_value'],
            ],
            'primary_details' => $this->catalog->find(
                $settings['primary']
            ),
            'secondary_details' => $settings['secondary']
                ? $this->catalog->find($settings['secondary'])
                : null,
        ];
    }

    public function save(
        ?int $workspaceId,
        string $primaryCurrency,
        bool $secondaryEnabled = false,
        ?string $secondaryCurrency = null,
        bool $automaticConversion = true,
        ?float $manualRate = null,
        string $marginType = 'none',
        float $marginValue = 0
    ): array {
        $primaryCurrency = strtoupper(trim($primaryCurrency));

        if (!$this->catalog->exists($primaryCurrency)) {
            throw new InvalidArgumentException(
                "Unsupported primary currency: {$primaryCurrency}"
            );
        }

        if (!$secondaryEnabled) {
            $secondaryCurrency = null;
            $automaticConversion = true;
            $manualRate = null;
            $marginType = 'none';
            $marginValue = 0;
        } else {
            if (!$secondaryCurrency) {
                throw new InvalidArgumentException(
                    'A secondary currency is required when enabled.'
                );
            }

            $secondaryCurrency = strtoupper(
                trim($secondaryCurrency)
            );

            if (!$this->catalog->exists($secondaryCurrency)) {
                throw new InvalidArgumentException(
                    "Unsupported secondary currency: {$secondaryCurrency}"
                );
            }

            if ($secondaryCurrency === $primaryCurrency) {
                throw new InvalidArgumentException(
                    'Primary and secondary currencies must be different.'
                );
            }

            if (!$automaticConversion) {
                if ($manualRate === null || $manualRate <= 0) {
                    throw new InvalidArgumentException(
                        'A valid manual exchange rate is required when automatic conversion is disabled.'
                    );
                }
            }

            if (!in_array(
                $marginType,
                ['none', 'percentage', 'fixed'],
                true
            )) {
                throw new InvalidArgumentException(
                    'Invalid secondary currency margin type.'
                );
            }

            if ($marginValue < 0) {
                throw new InvalidArgumentException(
                    'Currency margin cannot be negative.'
                );
            }

            if (
                $marginType === 'percentage' &&
                $marginValue > 100
            ) {
                throw new InvalidArgumentException(
                    'Percentage margin cannot exceed 100.'
                );
            }
        }

        $this->currencyService->configure(
            $workspaceId,
            $primaryCurrency,
            $secondaryCurrency,
            $automaticConversion,
            $manualRate,
            $marginType,
            $marginValue
        );

        return $this->get($workspaceId);
    }

    public function searchCurrencies(
        string $query,
        int $limit = 25
    ): array {
        return $this->catalog->search($query, $limit);
    }
}
