<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payout_methods', function (Blueprint $table) {
            /*
             * Markdown is applied AFTER payout conversion.
             *
             * Example:
             * NGN 10,000 -> 7.413 USDT
             * 10% markdown -> 6.672 USDT final payout.
             *
             * These settings work for both:
             * - Automatic payout methods
             * - Manual payout methods
             */
            $table->boolean('markdown_enabled')
                ->default(false)
                ->after('manual_conversion_rate');

            $table->string('markdown_type', 20)
                ->default('percentage')
                ->after('markdown_enabled');

            $table->decimal('markdown_value', 18, 8)
                ->default(0)
                ->after('markdown_type');
        });
    }

    public function down(): void
    {
        Schema::table('payout_methods', function (Blueprint $table) {
            $table->dropColumn([
                'markdown_enabled',
                'markdown_type',
                'markdown_value',
            ]);
        });
    }
};
