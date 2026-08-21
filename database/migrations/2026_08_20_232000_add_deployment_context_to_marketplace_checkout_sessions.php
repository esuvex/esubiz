<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('marketplace_checkout_sessions', 'deployment_type')) {
            Schema::table('marketplace_checkout_sessions', function (Blueprint $table) {
                $table->enum('deployment_type', [
                    'saas',
                    'off_server',
                ])->nullable()->after('account_mode');
            });
        }

        if (!Schema::hasColumn('marketplace_checkout_sessions', 'website_id')) {
            Schema::table('marketplace_checkout_sessions', function (Blueprint $table) {
                $table->unsignedBigInteger('website_id')
                    ->nullable()
                    ->after('product_id');
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Explicit short index name
        |--------------------------------------------------------------------------
        */

        try {
            Schema::table('marketplace_checkout_sessions', function (Blueprint $table) {
                $table->index(
                    ['account_mode', 'deployment_type', 'status'],
                    'mkt_checkout_mode_status_idx'
                );
            });
        } catch (\Throwable $e) {
            /*
             * The index may already exist because the first migration attempt
             * completed the index operation before Laravel reported failure.
             */
        }
    }

    public function down(): void
    {
        try {
            Schema::table('marketplace_checkout_sessions', function (Blueprint $table) {
                $table->dropIndex('mkt_checkout_mode_status_idx');
            });
        } catch (\Throwable $e) {
            // Index does not exist; continue.
        }

        $columns = [];

        if (Schema::hasColumn('marketplace_checkout_sessions', 'website_id')) {
            $columns[] = 'website_id';
        }

        if (Schema::hasColumn('marketplace_checkout_sessions', 'deployment_type')) {
            $columns[] = 'deployment_type';
        }

        if (!empty($columns)) {
            Schema::table('marketplace_checkout_sessions', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
};
