<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


/**
 * ================================================================
 * CHECKPOINT 8 — ONE-TIME CENTRAL SERVICE CREDIT BACKFILL
 * ================================================================
 *
 * Purpose:
 *
 * Move existing website service balances into the new authoritative
 * Central service-credit ledger WITHOUT changing the user's balance.
 *
 * Rules:
 *
 * 1. Never overwrite an existing Central ledger row.
 * 2. Prefer the current canonical website balance column.
 * 3. If unavailable, use the latest generic credit transaction
 *    balance when that ledger exists.
 * 4. Missing service balance = zero.
 * 5. Backfill is idempotent.
 */
return new class extends Migration
{
    protected array $serviceColumns = [
        'ai' => [
            'ai_credits',
            'ai_credit',
        ],

        'sms' => [
            'sms_credits',
            'sms_credit',
        ],

        'email' => [
            'email_credits',
            'email_credit',
        ],

        'whatsapp' => [
            'whatsapp_credits',
            'whatsapp_credit',
        ],
    ];


    protected array $creditTypes = [
        'ai' => [
            'ai_credits',
            'ai_credit',
            'ai',
        ],

        'sms' => [
            'sms_credits',
            'sms_credit',
            'sms',
        ],

        'email' => [
            'email_credits',
            'email_credit',
            'email',
        ],

        'whatsapp' => [
            'whatsapp_credits',
            'whatsapp_credit',
            'whatsapp',
        ],
    ];


    public function up(): void
    {
        if (
            !Schema::hasTable(
                'central_website_service_credits'
            )
            || !Schema::hasTable(
                'websites'
            )
        ) {
            return;
        }


        DB::table('websites')
            ->orderBy('id')
            ->chunkById(
                100,
                function ($websites) {

                    foreach ($websites as $website) {

                        foreach (
                            $this->serviceColumns
                            as $service => $columns
                        ) {

                            /*
                             * Never overwrite the new authoritative
                             * ledger after a row has been established.
                             */
                            $exists =
                                DB::table(
                                    'central_website_service_credits'
                                )
                                    ->where(
                                        'website_id',
                                        $website->id
                                    )
                                    ->where(
                                        'service',
                                        $service
                                    )
                                    ->exists();


                            if ($exists) {
                                continue;
                            }


                            $balance =
                                $this->balanceFromWebsite(
                                    $website,
                                    $columns
                                );


                            /*
                             * Generic credit transaction ledger is a
                             * fallback when the website table does not
                             * expose the service balance directly.
                             */
                            if ($balance === null) {

                                $balance =
                                    $this->balanceFromTransactions(
                                        (int) $website->id,
                                        $service
                                    );
                            }


                            $balance =
                                max(
                                    0,
                                    (float) (
                                        $balance
                                        ?? 0
                                    )
                                );


                            DB::table(
                                'central_website_service_credits'
                            )
                                ->insert([
                                    'website_id' =>
                                        $website->id,

                                    'service' =>
                                        $service,

                                    'balance' =>
                                        $balance,

                                    /*
                                     * This is an opening balance,
                                     * not a newly purchased credit.
                                     *
                                     * Do not falsely count it as new
                                     * lifetime sales.
                                     */
                                    'lifetime_credited' =>
                                        0,

                                    'lifetime_consumed' =>
                                        0,

                                    'created_at' =>
                                        now(),

                                    'updated_at' =>
                                        now(),
                                ]);


                            /*
                             * Record the migration as an auditable
                             * Central opening-balance transaction.
                             */
                            if (
                                Schema::hasTable(
                                    'central_website_service_credit_transactions'
                                )
                            ) {

                                DB::table(
                                    'central_website_service_credit_transactions'
                                )
                                    ->insert([
                                        'website_id' =>
                                            $website->id,

                                        'service' =>
                                            $service,

                                        'direction' =>
                                            'opening_balance',

                                        'amount' =>
                                            $balance,

                                        'balance_before' =>
                                            0,

                                        'balance_after' =>
                                            $balance,

                                        'request_key' =>
                                            'checkpoint8-opening:'
                                            . $website->id
                                            . ':'
                                            . $service,

                                        'source_type' =>
                                            'legacy_balance_migration',

                                        'source_id' =>
                                            $website->id,

                                        'user_id' =>
                                            $website->owner_id
                                            ?? null,

                                        'installation_id' =>
                                            null,

                                        'metadata' =>
                                            json_encode([
                                                'checkpoint' => 8,
                                                'migration' =>
                                                    'central_service_credit_opening_balance',
                                            ]),

                                        'created_at' =>
                                            now(),

                                        'updated_at' =>
                                            now(),
                                    ]);
                            }
                        }
                    }
                }
            );
    }


    protected function balanceFromWebsite(
        object $website,
        array $columns
    ): ?float {

        foreach ($columns as $column) {

            if (
                !Schema::hasColumn(
                    'websites',
                    $column
                )
            ) {
                continue;
            }


            if (
                property_exists(
                    $website,
                    $column
                )
                && $website->{$column} !== null
            ) {
                return (float) $website->{$column};
            }
        }


        return null;
    }


    protected function balanceFromTransactions(
        int $websiteId,
        string $service
    ): ?float {

        /*
         * Existing generic credit ledger created earlier in the
         * Marketplace/Credit architecture.
         */
        if (
            !Schema::hasTable(
                'credit_transactions'
            )
        ) {
            return null;
        }


        if (
            !Schema::hasColumn(
                'credit_transactions',
                'website_id'
            )
            || !Schema::hasColumn(
                'credit_transactions',
                'credit_type'
            )
            || !Schema::hasColumn(
                'credit_transactions',
                'balance_after'
            )
        ) {
            return null;
        }


        $types =
            $this->creditTypes[$service]
            ?? [];


        $query =
            DB::table(
                'credit_transactions'
            )
                ->where(
                    'website_id',
                    $websiteId
                )
                ->whereIn(
                    'credit_type',
                    $types
                );


        if (
            Schema::hasColumn(
                'credit_transactions',
                'status'
            )
        ) {
            $query->where(
                'status',
                'completed'
            );
        }


        $row =
            $query
                ->orderByDesc('id')
                ->first();


        return $row
            ? (float) $row->balance_after
            : null;
    }


    public function down(): void
    {
        /*
         * Do NOT delete Central balances automatically.
         *
         * Once Central becomes authoritative, rolling back this
         * migration must never erase credits purchased/consumed
         * after the migration ran.
         */
    }
};
