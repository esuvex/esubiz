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
        Schema::create('currencies', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Identity
            |--------------------------------------------------------------------------
            */

            $table->uuid('uuid')->unique();

            $table->string('name');

            $table->string('code', 3)->unique();

            $table->string('symbol');

            $table->string('country')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Formatting
            |--------------------------------------------------------------------------
            */

            $table->string('decimal_separator')->default('.');

            $table->string('thousand_separator')->default(',');

            $table->unsignedTinyInteger('decimal_places')->default(2);

            /*
            |--------------------------------------------------------------------------
            | Exchange
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_base')->default(false);

            $table->decimal('exchange_rate', 18, 8)
                ->default(1);

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_active')->default(true);

            $table->boolean('is_crypto')->default(false);

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
        Schema::dropIfExists('currencies');
    }
};
