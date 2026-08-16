<?php

namespace App\Services\Core;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CoreCurrencyService
{
    public function configure(
        ?int $workspaceId,
        string $primaryCurrency,
        ?string $secondaryCurrency = null,
        bool $automaticConversion = true,
        ?float $manualRate = null,
        string $marginType = 'none',
        float $marginValue = 0
    ): void {
        $primaryCurrency = $this->normalize($primaryCurrency);

        $secondaryCurrency = $secondaryCurrency !== null
            ? $this->normalize($secondaryCurrency)
            : null;

        if (
            $secondaryCurrency !== null &&
            $secondaryCurrency === $primaryCurrency
        ) {
            throw new InvalidArgumentException(
                'Primary and secondary currencies must be different.'
            );
        }

        if (!in_array($marginType, ['none', 'percentage', 'fixed'], true)) {
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
            !$automaticConversion &&
            $secondaryCurrency !== null &&
            ($manualRate === null || $manualRate <= 0)
        ) {
            throw new InvalidArgumentException(
                'A valid manual exchange rate is required when automatic conversion is disabled.'
            );
        }

        DB::table('site_settings')->updateOrInsert(
            [
                'workspace_id' => $workspaceId,
                'key' => 'core_currency',
            ],
            [
                'value' => json_encode([
                    'primary' => $primaryCurrency,
                    'secondary' => $secondaryCurrency,
                    'secondary_enabled' => $secondaryCurrency !== null,
                    'automatic_conversion' => $automaticConversion,
                    'manual_rate' => $manualRate,
                    'margin_type' => $marginType,
                    'margin_value' => $marginValue,
                ]),
                'updated_at' => now(),
            ]
        );
    }

    public function getCurrencies(?int $workspaceId): array
    {
        $setting = DB::table('site_settings')
            ->where('workspace_id', $workspaceId)
            ->where('key', 'core_currency')
            ->first();

        if (!$setting) {
            return [
                'primary' => 'NGN',
                'secondary' => null,
                'secondary_enabled' => false,
                'automatic_conversion' => true,
                'manual_rate' => null,
                'margin_type' => 'none',
                'margin_value' => 0,
            ];
        }

        $value = json_decode($setting->value, true) ?: [];

        return [
            'primary' => $value['primary'] ?? 'NGN',
            'secondary' => $value['secondary'] ?? null,
            'secondary_enabled' => (bool) (
                $value['secondary_enabled'] ?? false
            ),
            'automatic_conversion' => (bool) (
                $value['automatic_conversion'] ?? true
            ),
            'manual_rate' => isset($value['manual_rate'])
                ? (float) $value['manual_rate']
                : null,
            'margin_type' => $value['margin_type'] ?? 'none',
            'margin_value' => (float) (
                $value['margin_value'] ?? 0
            ),
        ];
    }

    public function isEnabled(
        ?int $workspaceId,
        string $currency
    ): bool {
        $currency = $this->normalize($currency);
        $settings = $this->getCurrencies($workspaceId);

        return $currency === $settings['primary']
            || (
                $settings['secondary_enabled'] &&
                $currency === $settings['secondary']
            );
    }

    public function getSecondarySettings(?int $workspaceId): array
    {
        $settings = $this->getCurrencies($workspaceId);

        return [
            'enabled' => $settings['secondary_enabled'],
            'currency' => $settings['secondary'],
            'automatic_conversion' => $settings['automatic_conversion'],
            'manual_rate' => $settings['manual_rate'],
            'margin_type' => $settings['margin_type'],
            'margin_value' => $settings['margin_value'],
        ];
    }

    protected function normalize(string $currency): string
    {
        $currency = strtoupper(trim($currency));

        if (!preg_match('/^[A-Z]{3}$/', $currency)) {
            throw new InvalidArgumentException(
                'Currency must be a valid three-letter currency code.'
            );
        }

        return $currency;
    }
}
