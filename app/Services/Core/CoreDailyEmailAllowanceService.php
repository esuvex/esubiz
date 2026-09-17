<?php

namespace App\Services\Core;

use App\Models\Website;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class CoreDailyEmailAllowanceService
{
    /**
     * ESUBIZ_CORE_DAILY_EMAIL_ALLOWANCE_V1
     *
     * Central authority for SaaS daily email sending.
     *
     * Base allowance:
     *   core_feature_limits.email_daily_sends
     *
     * Additional allowance:
     *   existing Core entitlement / Add-on architecture
     *
     * Usage:
     *   website_daily_email_usage
     *
     * DirectAdmin is deliberately not modified when this allowance changes.
     */

    public const LIMIT_KEY = 'email_daily_sends';

    public const DEFAULT_LIMIT = 10;

    public function __construct(
        protected CoreEntitlementService $entitlements
    ) {
    }

    public function appliesTo(Website $website): bool
    {
        return strtolower(
            trim((string) ($website->deployment_type ?? 'saas'))
        ) !== 'off_server';
    }

    public function allowance(Website $website): ?int
    {
        if (!$this->appliesTo($website)) {
            return null;
        }

        /*
         * CoreEntitlementService is the existing authority for combining
         * the Core base feature limit with purchased/granted capability.
         */
        try {
            $limit =
                $this->entitlements->limit(
                    'email',
                    self::LIMIT_KEY
                );

            /*
             * Missing capability is not unlimited. During staged deployment
             * Central falls back to the defined SaaS base allowance.
             */
            if ($limit === null) {
                return $this->configuredBaseLimit();
            }

            /*
             * Explicit unlimited remains unlimited.
             */
            if (
                (bool) ($limit->is_unlimited ?? false)
                || (string) ($limit->value_type ?? '') === 'unlimited'
            ) {
                return null;
            }

            /*
             * value() is the authoritative resolver for this Core limit:
             *
             * base core_feature_limits.default_value
             * + website-specific purchased/granted entitlement allocation.
             */
            $resolved =
                $this->entitlements->value(
                    'email',
                    self::LIMIT_KEY,
                    (int) $website->id
                );

            if ($resolved === null) {
                return null;
            }

            return max(0, (int) $resolved);
        } catch (\Throwable $e) {
            /*
             * During staged deployment the new capability may not yet be
             * visible to older entitlement code. Fail closed to the defined
             * Esubiz base rather than allowing unlimited SaaS sending.
             */
            report($e);

            return $this->configuredBaseLimit();
        }
    }

    public function usedToday(Website $website): int
    {
        if (!$this->appliesTo($website)) {
            return 0;
        }

        if (!Schema::hasTable('website_daily_email_usage')) {
            return 0;
        }

        return (int) (
            DB::table('website_daily_email_usage')
                ->where('website_id', $website->id)
                ->where('usage_date', now()->toDateString())
                ->value('sent_count')
            ?? 0
        );
    }

    public function remainingToday(Website $website): ?int
    {
        $allowance = $this->allowance($website);

        if ($allowance === null) {
            return null;
        }

        return max(
            0,
            $allowance - $this->usedToday($website)
        );
    }

    public function assertCanSend(
        Website $website,
        int $quantity = 1
    ): void {
        if (!$this->appliesTo($website)) {
            return;
        }

        $quantity = max(1, $quantity);

        $allowance = $this->allowance($website);

        /*
         * null represents an explicitly unlimited entitlement.
         */
        if ($allowance === null) {
            return;
        }

        $used = $this->usedToday($website);

        if (($used + $quantity) > $allowance) {
            throw new RuntimeException(
                'Your website has reached its daily email sending allowance. '
                . 'The allowance resets automatically the next day.'
            );
        }
    }

    public function recordSuccessfulSend(
        Website $website,
        int $quantity = 1
    ): void {
        if (!$this->appliesTo($website)) {
            return;
        }

        if (!Schema::hasTable('website_daily_email_usage')) {
            throw new RuntimeException(
                'Central daily email usage tracking is unavailable.'
            );
        }

        $quantity = max(1, $quantity);
        $date = now()->toDateString();
        $now = now();

        DB::transaction(function () use (
            $website,
            $quantity,
            $date,
            $now
        ): void {
            DB::table('website_daily_email_usage')
                ->insertOrIgnore([
                    'website_id' => $website->id,
                    'usage_date' => $date,
                    'sent_count' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

            $row =
                DB::table('website_daily_email_usage')
                    ->where('website_id', $website->id)
                    ->where('usage_date', $date)
                    ->lockForUpdate()
                    ->first();

            if (!$row) {
                throw new RuntimeException(
                    'Central daily email usage row could not be resolved.'
                );
            }

            DB::table('website_daily_email_usage')
                ->where('id', $row->id)
                ->update([
                    'sent_count' =>
                        ((int) $row->sent_count) + $quantity,

                    'updated_at' => $now,
                ]);
        });
    }

    public function summary(Website $website): array
    {
        $allowance = $this->allowance($website);
        $used = $this->usedToday($website);

        return [
            'applies' => $this->appliesTo($website),
            'limit_key' => self::LIMIT_KEY,
            'allowance' => $allowance,
            'used' => $used,
            'remaining' =>
                $allowance === null
                    ? null
                    : max(0, $allowance - $used),
            'unlimited' => $allowance === null,
            'period' => 'daily',
            'usage_date' => now()->toDateString(),
        ];
    }

    protected function configuredBaseLimit(): int
    {
        if (
            !Schema::hasTable('core_features')
            || !Schema::hasTable('core_feature_limits')
        ) {
            return self::DEFAULT_LIMIT;
        }

        $value =
            DB::table('core_feature_limits as l')
                ->join(
                    'core_features as f',
                    'f.id',
                    '=',
                    'l.core_feature_id'
                )
                ->where('f.key', 'email')
                ->where('l.limit_key', self::LIMIT_KEY)
                ->where('l.is_active', true)
                ->value('l.default_value');

        return $value === null
            ? self::DEFAULT_LIMIT
            : max(0, (int) $value);
    }
}
