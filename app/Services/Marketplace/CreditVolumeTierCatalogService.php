<?php

namespace App\Services\Marketplace;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class CreditVolumeTierCatalogService
{
    public function page(string $creditType, string $deployment, int $page = 1): array
    {
        if (!in_array($creditType, [
            'ai_credits', 'sms_credits', 'email_credits',
            'whatsapp_credits', 'kyc_credits',
        ], true) || !in_array($deployment, ['saas', 'off_server'], true)) {
            throw new InvalidArgumentException('Invalid credit price selection.');
        }

        $currency = strtoupper((string) app(
            \App\Services\Platform\CentralSiteSettingsService::class
        )->primaryCurrency());

        $rows = DB::table('marketplace_credit_volume_tiers')
            ->where('credit_type', $creditType)
            ->where('currency', $currency)
            ->where('is_active', true)
            ->whereIn('deployment_type', [$deployment, 'both'])
            ->orderBy('min_quantity')
            ->orderByRaw('CASE WHEN deployment_type = ? THEN 0 ELSE 1 END', [$deployment])
            ->orderBy('id')
            ->paginate(10, ['*'], 'page', max(1, $page));

        return [
            'currency' => $currency,
            'tiers' => $rows->getCollection()->map(fn ($tier) => [
                'minimum' => (int) $tier->min_quantity,
                'maximum' => $tier->max_quantity === null
                    ? null
                    : (int) $tier->max_quantity,
                'unit_price' => (float) (
                    $tier->unit_price
                    ?? ((int) $tier->unit_price_minor / 100)
                ),
                'applies_to' => $tier->deployment_type === 'both'
                    ? 'All websites'
                    : ($deployment === 'saas' ? 'SaaS websites' : 'Installed websites'),
            ])->all(),
            'page' => $rows->currentPage(),
            'has_previous' => $rows->currentPage() > 1,
            'has_next' => $rows->hasMorePages(),
        ];
    }
}
