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
        Schema::create('payment_provider_currencies', function (Blueprint $table) {

            $table->id();

            $table->foreignId('payment_provider_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('currency_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->boolean('is_default')->default(false);

            $table->boolean('is_enabled')->default(true);

            $table->timestamps();

            // Short index name (MySQL max identifier = 64 chars)
            $table->unique(
                ['payment_provider_id', 'currency_id'],
                'payprov_curr_unique'
            );

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_provider_currencies');
    }
};
