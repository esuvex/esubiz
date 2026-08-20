<?php

namespace App\Services\Core;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ReferralRevenueProcessor
{
    public function process(object $revenueEvent): void
    {
        if (!$revenueEvent->is_commissionable || !$revenueEvent->user_id) {
            return;
        }

        $member = DB::table('referral_members')
            ->where('user_id', $revenueEvent->user_id)
            ->where('is_active', true)
            ->first();

        if (!$member) {
            return;
        }

        $rule = $this->resolveRule($revenueEvent, $member);

        if (!$rule) {
            return;
        }

        $currentMember = $member;

        $initialSponsor = $this->findSponsor($currentMember);

        if (!$initialSponsor) {
            return;
        }

        $initialOverride = $this->resolveUserOverride(
            $revenueEvent,
            $initialSponsor
        );

        $maximumLevels = $initialOverride->maximum_levels
            ?? $rule->maximum_levels;

        if ($maximumLevels < 1) {
            return;
        }

        for ($level = 1; $level <= $maximumLevels; $level++) {
            $sponsor = $this->findSponsor($currentMember);

            if (!$sponsor) {
                break;
            }

            $override = $this->resolveUserOverride(
                $revenueEvent,
                $sponsor
            );

            if (
                $override &&
                !$this->overrideAllowsTransaction(
                    $override,
                    $revenueEvent,
                    $member
                )
            ) {
                $currentMember = $sponsor;
                continue;
            }

            $levelCommissions = $this->decodeLevelCommissions(
                $override->level_commissions ?? null
            );

            $rate = $this->resolveCommissionRate(
                $rule,
                $override,
                $level,
                $levelCommissions
            );

            if ($rate === null) {
                $currentMember = $sponsor;
                continue;
            }

            $commission = $this->calculateCommission(
                (float) $revenueEvent->net_amount,
                $rate['type'],
                (float) $rate['value']
            );

            if ($commission <= 0) {
                $currentMember = $sponsor;
                continue;
            }

            $existing = DB::table('referral_commissions')
                ->where('revenue_event_id', $revenueEvent->id)
                ->where('referral_member_id', $sponsor->id)
                ->where('level', $level)
                ->exists();

            if (!$existing) {
                $this->createImmediateCommission(
                    $revenueEvent,
                    $sponsor,
                    $level,
                    $commission
                );
            }

            $currentMember = $sponsor;
        }
    }

    protected function createImmediateCommission(
        object $revenueEvent,
        object $sponsor,
        int $level,
        float $commission
    ): void {
        DB::transaction(function () use (
            $revenueEvent,
            $sponsor,
            $level,
            $commission
        ) {
            $wallet = DB::table('wallets')
                ->where('user_id', $sponsor->user_id)
                ->where('type', 'referral')
                ->where('currency', $revenueEvent->currency)
                ->where('is_active', true)
                ->orderByDesc('is_default')
                ->orderBy('id')
                ->lockForUpdate()
                ->first();

            if (!$wallet) {
                throw new \RuntimeException(
                    'Referral wallet not found for sponsor user '
                    . $sponsor->user_id
                );
            }

            $alreadyCreated = DB::table('referral_commissions')
                ->where('revenue_event_id', $revenueEvent->id)
                ->where('referral_member_id', $sponsor->id)
                ->where('level', $level)
                ->exists();

            if ($alreadyCreated) {
                return;
            }

            $balanceBefore = (float) $wallet->available_balance;
            $balanceAfter = $balanceBefore + $commission;

            $reference = 'REF-COMM-'
                . $revenueEvent->id
                . '-'
                . $sponsor->id
                . '-'
                . $level;

            $walletTransactionId = DB::table('wallet_transactions')
                ->insertGetId([
                    'wallet_id' => $wallet->id,
                    'workspace_id' => $revenueEvent->workspace_id
                        ?? $sponsor->workspace_id
                        ?? null,
                    'user_id' => $sponsor->user_id,
                    'uuid' => (string) Str::uuid(),
                    'reference' => $reference,
                    'type' => 'commission',
                    'direction' => 'credit',
                    'amount' => $commission,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceAfter,
                    'currency' => $revenueEvent->currency,
                    'source_type' => 'referral_commission',
                    'source_id' => $revenueEvent->id,
                    'status' => 'completed',
                    'description' => 'Referral commission',
                    'metadata' => json_encode([
                        'revenue_event_id' => $revenueEvent->id,
                        'referral_member_id' => $sponsor->id,
                        'level' => $level,
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

            DB::table('wallets')
                ->where('id', $wallet->id)
                ->update([
                    'available_balance' => $balanceAfter,
                    'updated_at' => now(),
                ]);

            DB::table('referral_commissions')
                ->insert([
                    'workspace_id' => $revenueEvent->workspace_id
                        ?? $sponsor->workspace_id
                        ?? null,
                    'referral_member_id' => $sponsor->id,
                    'wallet_transaction_id' => $walletTransactionId,
                    'revenue_event_id' => $revenueEvent->id,
                    'level' => $level,
                    'base_amount' => $revenueEvent->net_amount,
                    'commission_amount' => $commission,
                    'currency' => $revenueEvent->currency,
                    'status' => 'paid',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

        // Record the referral commission as a real Esubiz debit.
        DB::table('expense_events')->insert([
            'user_id' => $revenueEvent->user_id ?? null,
            'website_id' => $revenueEvent->website_id ?? null,
            'workspace_id' => $revenueEvent->workspace_id ?? null,
            'source' => 'referral_commission',
            'expense_type' => 'referral_commission',
            'description' => 'Referral commission paid',
            'reference_type' => 'revenue_event',
            'reference_id' => (string) ($revenueEvent->id),
            'amount' => $amount_var,
            'currency' => $revenueEvent->currency ?? 'NGN',
            'status' => 'processed',
            'occurred_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        });
    }

    protected function findSponsor(object $member): ?object
    {
        if (!$member->sponsor_id) {
            return null;
        }

        return DB::table('referral_members')
            ->where('id', $member->sponsor_id)
            ->where('is_active', true)
            ->first();
    }

    protected function resolveRule(
        object $revenueEvent,
        object $member
    ): ?object {
        $trigger = $this->resolveTrigger(
            $revenueEvent->event_type
        );

        return DB::table('referral_rules')
            ->where('is_active', true)
            ->where(function ($query) use ($member) {
                $query
                    ->whereNull('workspace_id')
                    ->orWhere('workspace_id', $member->workspace_id);
            })
            ->where('trigger', $trigger)
            ->where(
                'minimum_transaction',
                '<=',
                $revenueEvent->net_amount
            )
            ->orderByDesc('workspace_id')
            ->orderByDesc('id')
            ->first();
    }

    protected function resolveUserOverride(
        object $revenueEvent,
        object $sponsor
    ): ?object {
        $now = now();

        return DB::table('user_referral_overrides')
            ->where('user_id', $sponsor->user_id)
            ->where('is_active', true)
            ->where(function ($query) use ($sponsor) {
                $query
                    ->whereNull('workspace_id')
                    ->orWhere('workspace_id', $sponsor->workspace_id);
            })
            ->where(function ($query) use ($now) {
                $query
                    ->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query
                    ->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', $now);
            })
            ->orderByDesc('workspace_id')
            ->orderByDesc('id')
            ->first();
    }

    protected function overrideAllowsTransaction(
        object $override,
        object $revenueEvent,
        object $referredMember
    ): bool {
        $conditions = $this->decodeConditions(
            $override->conditions ?? null
        );

        if ($conditions === []) {
            return true;
        }

        $transactionLimit = $conditions['transaction_limit'] ?? null;

        if ($transactionLimit !== null) {
            $transactionCount = DB::table('revenue_events')
                ->where('user_id', $revenueEvent->user_id)
                ->whereNotIn('status', ['cancelled', 'reversed'])
                ->where('id', '<=', $revenueEvent->id)
                ->count();

            if ($transactionCount > (int) $transactionLimit) {
                return false;
            }
        }

        /*
         * Referral duration is fully configurable by the site admin.
         *
         * Supported configuration:
         *
         * {
         *     "duration_type": "days",
         *     "duration_value": 45
         * }
         *
         * {
         *     "duration_type": "months",
         *     "duration_value": 18
         * }
         *
         * {
         *     "duration_type": "years",
         *     "duration_value": 2
         * }
         *
         * {
         *     "duration_type": "lifetime"
         * }
         *
         * {
         *     "duration_type": "transaction"
         * }
         *
         * Legacy values such as "3_months", "6_months",
         * "12_months" and "lifetime" remain supported.
         */
        $durationType = $conditions['duration_type'] ?? null;
        $durationValue = $conditions['duration_value'] ?? null;

        /*
         * Backward compatibility with the earlier duration format.
         */
        if (!$durationType) {
            $legacyDuration = $conditions['duration'] ?? null;

            if ($legacyDuration === 'lifetime') {
                $durationType = 'lifetime';
            } elseif ($legacyDuration === 'transaction') {
                $durationType = 'transaction';
            } elseif (
                is_string($legacyDuration) &&
                preg_match(
                    '/^(\\d+)_(day|days|month|months|year|years)$/',
                    $legacyDuration,
                    $matches
                )
            ) {
                $durationValue = (int) $matches[1];

                $durationType = match ($matches[2]) {
                    'day', 'days' => 'days',
                    'month', 'months' => 'months',
                    'year', 'years' => 'years',
                };
            }
        }

        /*
         * First transaction only.
         */
        if ($durationType === 'transaction') {
            $alreadyCommissioned = DB::table('referral_commissions')
                ->where('referral_member_id', $referredMember->id)
                ->where('level', 1)
                ->exists();

            if ($alreadyCommissioned) {
                return false;
            }
        }

        /*
         * Lifetime has no expiry.
         */
        if ($durationType === 'lifetime' || !$durationType) {
            return true;
        }

        /*
         * Calculate expiry dynamically from the configured
         * duration value.
         */
        if (
            in_array($durationType, ['days', 'months', 'years'], true) &&
            is_numeric($durationValue) &&
            (int) $durationValue > 0
        ) {
            $startedAt = $override->starts_at
                ?? $referredMember->created_at;

            if (!$startedAt) {
                return true;
            }

            $startedAt = \Carbon\Carbon::parse($startedAt);

            $expiresAt = match ($durationType) {
                'days' => $startedAt->copy()->addDays(
                    (int) $durationValue
                ),
                'months' => $startedAt->copy()->addMonths(
                    (int) $durationValue
                ),
                'years' => $startedAt->copy()->addYears(
                    (int) $durationValue
                ),
            };

            if (now()->greaterThan($expiresAt)) {
                return false;
            }
        }

        return true;

        return true;
    }

    protected function resolveCommissionRate(
        object $rule,
        ?object $override,
        int $level,
        array $levelCommissions
    ): ?array {
        if ($levelCommissions !== []) {
            $levelValue = $levelCommissions[$level]
                ?? $levelCommissions[(string) $level]
                ?? null;

            if ($levelValue !== null) {
                if (is_array($levelValue)) {
                    return [
                        'type' => $levelValue['type']
                            ?? $override->commission_type
                            ?? $rule->reward_type,
                        'value' => $levelValue['value'] ?? 0,
                    ];
                }

                return [
                    'type' => $override->commission_type
                        ?? $rule->reward_type,
                    'value' => $levelValue,
                ];
            }
        }

        if ($override) {
            return [
                'type' => $override->commission_type,
                'value' => $override->commission_value,
            ];
        }

        return [
            'type' => $rule->reward_type,
            'value' => $rule->reward_value,
        ];
    }

    protected function calculateCommission(
        float $baseAmount,
        string $type,
        float $value
    ): float {
        if ($baseAmount <= 0 || $value <= 0) {
            return 0;
        }

        if ($type === 'fixed') {
            return round($value, 2);
        }

        return round(
            ($baseAmount * $value) / 100,
            2
        );
    }

    protected function decodeLevelCommissions(
        mixed $value
    ): array {
        if (!$value) {
            return [];
        }

        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode($value, true);

        return is_array($decoded)
            ? $decoded
            : [];
    }

    protected function decodeConditions(
        mixed $value
    ): array {
        if (!$value) {
            return [];
        }

        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode($value, true);

        return is_array($decoded)
            ? $decoded
            : [];
    }

    protected function resolveTrigger(string $eventType): string
    {
        return match ($eventType) {
            'subscription' => 'subscription',
            'renewal' => 'renewal',
            'purchase',
            'sale',
            'order',
            'payment' => 'purchase',
            'wallet_funding' => 'wallet_funding',
            'wallet_spending' => 'wallet_spending',
            'registration' => 'registration',
            default => 'custom',
        };
    }
}
