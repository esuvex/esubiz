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

            /*
            |--------------------------------------------------------------------------
            | Provider
            |--------------------------------------------------------------------------
            */

            $table->foreignId('payment_provider_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Currency
            |--------------------------------------------------------------------------
            */

            $table->foreignId('currency_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Configuration
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_enabled')
                ->default(true);

            /*
            |--------------------------------------------------------------------------
            | Limits
            |--------------------------------------------------------------------------
            */

            $table->decimal('minimum_amount', 18, 8)
                ->nullable();

            $table->decimal('maximum_amount', 18, 8)
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Fees
            |--------------------------------------------------------------------------
            */

            $table->decimal('fixed_fee', 18, 8)
                ->default(0);

            $table->decimal('percentage_fee', 8, 4)
                ->default(0);

            /*
            |--------------------------------------------------------------------------
            | Settlement
            |--------------------------------------------------------------------------
            */

            $table->foreignId('settlement_currency_id')
                ->nullable()
                ->constrained('currencies')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Audit
            |--------------------------------------------------------------------------
            */

            $table->timestamps();

            $table->softDeletes();

            $table->unique([
                'payment_provider_id',
                'currency_id'
            ]);

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
