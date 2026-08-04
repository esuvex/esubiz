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
        Schema::create('exchange_rates', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Currency Pair
            |--------------------------------------------------------------------------
            */

            $table->foreignId('from_currency_id')
                ->constrained('currencies')
                ->cascadeOnDelete();

            $table->foreignId('to_currency_id')
                ->constrained('currencies')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Rate
            |--------------------------------------------------------------------------
            */

            $table->decimal('rate', 18, 8);

            /*
            |--------------------------------------------------------------------------
            | Provider
            |--------------------------------------------------------------------------
            */

            $table->string('provider')->nullable();

            $table->string('api_reference')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Update
            |--------------------------------------------------------------------------
            */

            $table->timestamp('fetched_at')->nullable();

            $table->timestamp('expires_at')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_active')->default(true);

            /*
            |--------------------------------------------------------------------------
            | Audit
            |--------------------------------------------------------------------------
            */

            $table->timestamps();

            $table->softDeletes();

            $table->unique([
                'from_currency_id',
                'to_currency_id'
            ]);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
