<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketplace_referral_rules', function (Blueprint $table) {
            $table->enum('calculation_type', [
                'percentage',
                'fixed',
            ])
                ->default('percentage')
                ->after('referrer_role');

            $table->decimal('commission_value', 14, 4)
                ->default(0)
                ->after('calculation_type');
        });

        /*
         * Preserve existing referral percentages.
         */
        DB::table('marketplace_referral_rules')
            ->update([
                'calculation_type' => 'percentage',
                'commission_value' => DB::raw(
                    'level_one_commission_percent'
                ),
            ]);
    }

    public function down(): void
    {
        Schema::table('marketplace_referral_rules', function (Blueprint $table) {
            $table->dropColumn([
                'calculation_type',
                'commission_value',
            ]);
        });
    }
};
