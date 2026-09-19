<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketplace_expense_rules', function (Blueprint $table) {
            $table->id();

            $table->string('product_type', 80);
            $table->string('name', 120);

            $table->enum('calculation_type', [
                'percentage',
                'fixed',
            ])->default('percentage');

            $table->decimal('value', 14, 4)->default(0);

            $table->foreignId('central_tax_id')
                ->nullable()
                ->constrained('central_taxes')
                ->nullOnDelete();

            $table->boolean('tax_is_exclusive')
                ->default(false);

            $table->boolean('is_active')
                ->default(true);

            $table->unsignedInteger('sort_order')
                ->default(0);

            $table->timestamps();

            $table->index(
                ['product_type', 'is_active'],
                'mkt_exp_type_active_ix'
            );

            $table->index(
                'central_tax_id',
                'mkt_exp_tax_ix'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_expense_rules');
    }
};
