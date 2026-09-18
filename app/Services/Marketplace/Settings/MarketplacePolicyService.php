<?php

namespace App\Services\Marketplace\Settings;

use App\Models\MarketplaceFinancialRule;
use App\Models\MarketplaceReferralRule;
use App\Models\MarketplaceSetting;

class MarketplacePolicyService
{
    public function setting(
        string $key,
        mixed $default = null
    ): mixed {
        $setting = MarketplaceSetting::query()
            ->active()
            ->where('key', $key)
            ->first();

        if (!$setting) {
            return $default;
        }

        $value = $setting->value;

        if (
            is_array($value)
            && array_key_exists('value', $value)
        ) {
            return $value['value'];
        }

        return $value ?? $default;
    }

    public function financialRule(
        string $productType
    ): ?MarketplaceFinancialRule {
        return MarketplaceFinancialRule::query()
            ->active()
            ->forProductType($productType)
            ->first();
    }

    public function developerSharePercent(
        string $productType
    ): float {
        return (float) (
            $this->financialRule($productType)
                ?->developer_share_percent
            ?? 0
        );
    }

    public function platformSharePercent(
        string $productType
    ): float {
        return max(
            0,
            100 - $this->developerSharePercent(
                $productType
            )
        );
    }

    public function expensePercent(
        string $productType
    ): float {
        return (float) (
            $this->financialRule($productType)
                ?->expense_percent
            ?? 0
        );
    }

    /*
     * Marketplace may override ONLY Level 1 percentage.
     *
     * Returning null means:
     * use the Level 1 commission percentage supplied by the
     * authoritative Central Referral Plan.
     *
     * Multi-level rules, duration, attribution, eligibility,
     * expiry and every other referral rule remain Central-owned.
     */
    public function levelOneReferralOverride(
        string $productType,
        string $referrerRole
    ): ?float {
        $rule = MarketplaceReferralRule::query()
            ->active()
            ->forProductType($productType)
            ->forRole($referrerRole)
            ->first();

        return $rule
            ? (float) $rule->level_one_commission_percent
            : null;
    }

    public function splitDeveloperSale(
        float $grossAmount,
        string $productType
    ): array {
        $developerPercent =
            $this->developerSharePercent($productType);

        $platformPercent =
            $this->platformSharePercent($productType);

        $developerAmount = round(
            $grossAmount * ($developerPercent / 100),
            2
        );

        $platformAmount = round(
            $grossAmount - $developerAmount,
            2
        );

        return [
            'gross_amount' => $grossAmount,

            'developer_share_percent' =>
                $developerPercent,

            'developer_share_amount' =>
                $developerAmount,

            'platform_share_percent' =>
                $platformPercent,

            'platform_share_amount' =>
                $platformAmount,
        ];
    }
}
