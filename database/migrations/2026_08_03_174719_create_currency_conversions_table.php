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
        Schema::create('currency_conversions', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Ownership
            |--------------------------------------------------------------------------
            */

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('workspace_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

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
            | Conversion
            |--------------------------------------------------------------------------
            */

            $table->decimal('from_amount', 18, 8);

            $table->decimal('to_amount', 18, 8);

            $table->decimal('exchange_rate', 18, 8);

            /*
            |--------------------------------------------------------------------------
            | Provider
            |--------------------------------------------------------------------------
            */

            $table->string('provider')->nullable();

            $table->string('reference')->unique();

            /*
            |--------------------------------------------------------------------------
            | Purpose
            |--------------------------------------------------------------------------
            */

            $table->string('source')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Fee
            |--------------------------------------------------------------------------
            */

            $table->decimal('fee', 18, 8)->default(0);

            /*
            |--------------------------------------------------------------------------
            | Audit
            |--------------------------------------------------------------------------
            */

            $table->timestamps();

            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currency_conversions');
    }
};
