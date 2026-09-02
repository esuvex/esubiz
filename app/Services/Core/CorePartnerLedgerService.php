<?php

namespace App\Services\Core;

use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use RuntimeException;

class CorePartnerLedgerService
{
    protected string $connection = 'website_tenant';

    public function postBusinessResult(
        int $userId,
        float $businessResult,
        string $periodKey,
        string $sourceType,
        string $sourceReference,
        ?CarbonInterface $postedAt = null
    ): object {
        $this->assertAvailable();

        $investment = DB::connection($this->connection)
            ->table('site_partner_investments')
            ->where('user_id', $userId)
            ->where('is_active', true)
            ->first();

        if (!$investment) {
            throw new RuntimeException(
                'Active partner investment configuration not found.'
            );
        }

        $percentage = (float)
            $investment->investment_percentage;

        if (
            $percentage < 0
            || $percentage > 100
        ) {
            throw new RuntimeException(
                'Partner investment percentage is invalid.'
            );
        }

        $basis = strtolower(
            (string) $investment->profit_basis
        );

        if (!in_array($basis, ['gross', 'net'], true)) {
            throw new RuntimeException(
                'Partner profit basis must be gross or net.'
            );
        }

        $periodKey = trim($periodKey);
        $sourceType = trim($sourceType);
        $sourceReference = trim($sourceReference);

        if ($periodKey === '') {
            throw new InvalidArgumentException(
                'Period key is required.'
            );
        }

        if ($sourceType === '') {
            throw new InvalidArgumentException(
                'Source type is required.'
            );
        }

        if ($sourceReference === '') {
            throw new InvalidArgumentException(
                'Source reference is required.'
            );
        }

        /*
         * Partner share is always based on the absolute
         * magnitude of the business result.
         *
         * Positive result = profit = CREDIT.
         * Negative result = loss = DEBIT.
         */
        $share = round(
            abs($businessResult)
            * ($percentage / 100),
            2
        );

        $entryType = $businessResult >= 0
            ? 'credit'
            : 'debit';

        $category = $businessResult >= 0
            ? 'profit_share'
            : 'loss_share';

        $description = $businessResult >= 0
            ? 'Partner share of business profit'
            : 'Partner share of business loss';

        DB::connection($this->connection)
            ->table('site_partner_ledger_entries')
            ->updateOrInsert(
                [
                    'user_id' => $userId,
                    'source_type' => $sourceType,
                    'source_reference' =>
                        $sourceReference,
                ],
                [
                    'entry_type' => $entryType,
                    'entry_category' => $category,
                    'amount' => $share,
                    'investment_percentage' =>
                        $percentage,
                    'profit_basis' => $basis,
                    'business_result' =>
                        round($businessResult, 2),
                    'period_key' => $periodKey,
                    'description' => $description,
                    'posted_at' => $postedAt
                        ? $postedAt->toDateTimeString()
                        : now(),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

        return DB::connection($this->connection)
            ->table('site_partner_ledger_entries')
            ->where('user_id', $userId)
            ->where('source_type', $sourceType)
            ->where(
                'source_reference',
                $sourceReference
            )
            ->first();
    }

    public function postDebit(
        int $userId,
        float $amount,
        string $category,
        string $sourceType,
        string $sourceReference,
        ?string $description = null
    ): object {
        return $this->postManualEntry(
            $userId,
            'debit',
            $amount,
            $category,
            $sourceType,
            $sourceReference,
            $description
        );
    }

    public function postCredit(
        int $userId,
        float $amount,
        string $category,
        string $sourceType,
        string $sourceReference,
        ?string $description = null
    ): object {
        return $this->postManualEntry(
            $userId,
            'credit',
            $amount,
            $category,
            $sourceType,
            $sourceReference,
            $description
        );
    }

    protected function postManualEntry(
        int $userId,
        string $entryType,
        float $amount,
        string $category,
        string $sourceType,
        string $sourceReference,
        ?string $description
    ): object {
        $this->assertAvailable();

        if ($amount < 0) {
            throw new InvalidArgumentException(
                'Ledger amount cannot be negative.'
            );
        }

        $category = trim($category);
        $sourceType = trim($sourceType);
        $sourceReference = trim($sourceReference);

        if (
            $category === ''
            || $sourceType === ''
            || $sourceReference === ''
        ) {
            throw new InvalidArgumentException(
                'Category and source identity are required.'
            );
        }

        DB::connection($this->connection)
            ->table('site_partner_ledger_entries')
            ->updateOrInsert(
                [
                    'user_id' => $userId,
                    'source_type' => $sourceType,
                    'source_reference' =>
                        $sourceReference,
                ],
                [
                    'entry_type' => $entryType,
                    'entry_category' => $category,
                    'amount' => round($amount, 2),
                    'description' => $description,
                    'posted_at' => now(),
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

        return DB::connection($this->connection)
            ->table('site_partner_ledger_entries')
            ->where('user_id', $userId)
            ->where('source_type', $sourceType)
            ->where(
                'source_reference',
                $sourceReference
            )
            ->first();
    }

    public function totals(int $userId): array
    {
        $this->assertAvailable();

        $query = DB::connection($this->connection)
            ->table('site_partner_ledger_entries')
            ->where('user_id', $userId);

        $credits = (float) (
            clone $query
        )
            ->where('entry_type', 'credit')
            ->sum('amount');

        $debits = (float) (
            clone $query
        )
            ->where('entry_type', 'debit')
            ->sum('amount');

        return [
            'credits' => round($credits, 2),
            'debits' => round($debits, 2),
            'balance' => round(
                $credits - $debits,
                2
            ),
        ];
    }

    public function recent(
        int $userId,
        int $limit = 10
    ): array {
        $this->assertAvailable();

        return DB::connection($this->connection)
            ->table('site_partner_ledger_entries')
            ->where('user_id', $userId)
            ->orderByDesc('posted_at')
            ->orderByDesc('id')
            ->limit(max(1, min($limit, 50)))
            ->get()
            ->all();
    }

    protected function assertAvailable(): void
    {
        if (
            !Schema::connection($this->connection)
                ->hasTable(
                    'site_partner_ledger_entries'
                )
        ) {
            throw new RuntimeException(
                'Partner ledger is not installed.'
            );
        }
    }
}
