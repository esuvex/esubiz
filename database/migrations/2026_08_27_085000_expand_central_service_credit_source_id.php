<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


/**
 * ================================================================
 * CHECKPOINT 8 — CENTRAL SERVICE SOURCE ID COMPATIBILITY
 * ================================================================
 *
 * Central service events may originate from:
 *
 * - numeric database IDs
 * - UUID request IDs
 * - Marketplace references
 * - provider references
 * - external application references
 *
 * Therefore source_id must not be restricted to BIGINT.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (
            !Schema::hasTable(
                'central_website_service_credit_transactions'
            )
            || !Schema::hasColumn(
                'central_website_service_credit_transactions',
                'source_id'
            )
        ) {
            return;
        }


        DB::statement(
            'ALTER TABLE central_website_service_credit_transactions
             MODIFY source_id VARCHAR(191) NULL'
        );
    }


    public function down(): void
    {
        /*
         * Intentionally do not automatically convert back to BIGINT.
         *
         * Once UUID/string source references exist, narrowing this
         * column could destroy valid transaction provenance.
         */
    }
};
