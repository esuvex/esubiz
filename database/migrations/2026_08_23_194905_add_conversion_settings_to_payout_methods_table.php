<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payout_methods', function (Blueprint $table) {
            $table->string(
                'conversion_type',
                20
            )->default('none')->after('supported_countries');

            $table->string(
                'conversion_provider',
                50
            )->nullable()->after('conversion_type');

            $table->string(
                'conversion_target',
                30
            )->nullable()->after('conversion_provider');

            $table->boolean(
                'conversion_enabled'
            )->default(false)->after('conversion_target');

            $table->decimal(
                'manual_conversion_rate',
                24,
                12
            )->nullable()->after('conversion_enabled');

            $table->longText(
                'conversion_settings'
            )->nullable()->after('manual_conversion_rate');
        });
    }

    public function down(): void
    {
        Schema::table('payout_methods', function (Blueprint $table) {
            $table->dropColumn([
                'conversion_type',
                'conversion_provider',
                'conversion_target',
                'conversion_enabled',
                'manual_conversion_rate',
                'conversion_settings',
            ]);
        });
    }
};
