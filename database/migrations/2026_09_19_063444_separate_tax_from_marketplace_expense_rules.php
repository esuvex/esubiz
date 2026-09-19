<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketplace_expense_rules', function (Blueprint $table) {
            $table->dropForeign(['central_tax_id']);
            $table->dropIndex('mkt_exp_tax_ix');
            $table->dropColumn([
                'central_tax_id',
                'tax_is_exclusive',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('marketplace_expense_rules', function (Blueprint $table) {
            $table->foreignId('central_tax_id')
                ->nullable()
                ->constrained('central_taxes')
                ->nullOnDelete();

            $table->boolean('tax_is_exclusive')
                ->default(false);

            $table->index(
                'central_tax_id',
                'mkt_exp_tax_ix'
            );
        });
    }
};
