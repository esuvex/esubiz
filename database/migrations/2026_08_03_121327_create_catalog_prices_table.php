<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('catalog_prices', function (Blueprint $table) {

            $table->id();

            $table->foreignId('catalog_product_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('billing_period_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('billing_model', 50);

            $table->decimal('price', 15, 2);

            $table->decimal('setup_fee', 15, 2)->default(0);

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique(
                ['catalog_product_id', 'billing_period_id', 'billing_model'],
                'cat_price_unique'
            );

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('catalog_prices');
    }
};
