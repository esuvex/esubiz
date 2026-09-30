<?php

namespace App\Services\Marketplace;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class CreditVolumePricingService
{
    /**
     * Return a Central-authoritative quote in minor currency units.
     * Browser-provided prices are never accepted.
     */
    public function quote(
        string $creditType,
        int $quantity,
        string $deploymentType,
        string $currency = ''
    ): array {
        $creditType = strtolower(trim($creditType));
        $deploymentType = strtolower(trim($deploymentType));
        $currency = strtoupper(trim($currency ?: app(\App\Services\Platform\CentralSiteSettingsService::class)->primaryCurrency()));

        if (!in_array($creditType, [
            'ai_credits',
            'sms_credits',
            'email_credits',
            'whatsapp_credits',
            'kyc_credits',
        ], true)) {
            throw new InvalidArgumentException('Unsupported credit type.');
        }

        if (!in_array($deploymentType, ['saas', 'off_server'], true)) {
            throw new InvalidArgumentException('Unsupported deployment type.');
        }

        if ($quantity < 1 || $quantity > 100000000) {
            throw new InvalidArgumentException('Invalid credit quantity.');
        }

        if (!preg_match('/^[A-Z]{3}$/', $currency)) {
            throw new InvalidArgumentException('Invalid currency.');
        }

        $tiers = DB::table('marketplace_credit_volume_tiers')
            ->where('credit_type', $creditType)
            ->where('currency', $currency)
            ->where('is_active', true)
            ->whereIn('deployment_type', [$deploymentType, 'both'])
            ->where('min_quantity', '<=', $quantity)
            ->where(function ($query) use ($quantity) {
                $query->whereNull('max_quantity')
                    ->orWhere('max_quantity', '>=', $quantity);
            })
            ->get()
            ->sortBy(fn ($tier) => $tier->deployment_type === $deploymentType ? 0 : 1)
            ->values();

        if ($tiers->isEmpty()) {
            throw new RuntimeException('No active price tier covers this credit quantity.');
        }

        $selected = $tiers->first();
        $samePriority = $tiers->filter(
            fn ($tier) => $tier->deployment_type === $selected->deployment_type
        );

        if ($samePriority->count() !== 1) {
            throw new RuntimeException('Overlapping active credit price tiers.');
        }

        $unitPrice = (float) ($selected->unit_price
            ?? ((int) $selected->unit_price_minor / 100));

        if ($unitPrice <= 0 || $unitPrice * $quantity > 999999999999.99) {
            throw new RuntimeException('Invalid credit price tier.');
        }

        return [
            'tier_id' => (int) $selected->id,
            'credit_type' => $creditType,
            'deployment_type' => $deploymentType,
            'currency' => $currency,
            'quantity' => $quantity,
            'unit_price' => number_format($unitPrice, 2, '.', ''),
            'total' => number_format($unitPrice * $quantity, 2, '.', ''),
            'unit_price_minor' => (int) round($unitPrice * 100),
            'total_minor' => (int) round($unitPrice * $quantity * 100),
        ];
    }
}
