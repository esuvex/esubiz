<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketplace_credit_volume_tiers', function (Blueprint $table) {
            $table->decimal('unit_price', 18, 2)
                ->nullable()
                ->after('unit_price_minor');
        });

        DB::table('marketplace_credit_volume_tiers')
            ->orderBy('id')
            ->chunkById(100, function ($tiers) {
                foreach ($tiers as $tier) {
                    DB::table('marketplace_credit_volume_tiers')
                        ->where('id', $tier->id)
                        ->update([
                            'unit_price' => number_format(
                                ((int) $tier->unit_price_minor) / 100,
                                2,
                                '.',
                                ''
                            ),
                        ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('marketplace_credit_volume_tiers', function (Blueprint $table) {
            $table->dropColumn('unit_price');
        });
    }
};
