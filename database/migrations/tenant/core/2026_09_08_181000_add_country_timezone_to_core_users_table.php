<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * Esubiz Core canonical user table is site_users.
         *
         * Keep this migration additive and idempotent so it is safe
         * for both existing tenant upgrades and fresh Core installs.
         */
        if (!Schema::hasTable('site_users')) {
            return;
        }

        if (!Schema::hasColumn('site_users', 'country_code')) {
            Schema::table('site_users', function (Blueprint $table) {
                $table->string('country_code', 2)
                    ->default('NG')
                    ->after('phone');
            });
        }

        if (!Schema::hasColumn('site_users', 'timezone')) {
            Schema::table('site_users', function (Blueprint $table) {
                $table->string('timezone', 64)
                    ->default('Africa/Lagos')
                    ->after('country_code');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('site_users')) {
            return;
        }

        if (Schema::hasColumn('site_users', 'timezone')) {
            Schema::table('site_users', function (Blueprint $table) {
                $table->dropColumn('timezone');
            });
        }

        if (Schema::hasColumn('site_users', 'country_code')) {
            Schema::table('site_users', function (Blueprint $table) {
                $table->dropColumn('country_code');
            });
        }
    }
};
