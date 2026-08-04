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
        Schema::create('capacity_product_items', function (Blueprint $table) {

            $table->id();

            $table->foreignId('capacity_product_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('capacity_type_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->integer('quantity')->default(0);

            $table->timestamps();

            // Short custom index name (avoids MySQL 64-character limit)
            $table->unique(
                ['capacity_product_id', 'capacity_type_id'],
                'cap_prod_type_unique'
            );

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('capacity_product_items');
    }
};
