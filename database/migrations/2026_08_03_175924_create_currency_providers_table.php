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
        Schema::create('currency_providers', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Identity
            |--------------------------------------------------------------------------
            */

            $table->uuid('uuid')->unique();

            $table->string('name');

            $table->string('slug')->unique();

            /*
            |--------------------------------------------------------------------------
            | Provider
            |--------------------------------------------------------------------------
            */

            $table->string('type')->default('exchange');

            /*
            |--------------------------------------------------------------------------
            | API Configuration
            |--------------------------------------------------------------------------
            */

            $table->string('base_url')->nullable();

            $table->json('credentials')->nullable();

            $table->json('settings')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Supported Services
            |--------------------------------------------------------------------------
            */

            $table->boolean('supports_exchange')->default(true);

            $table->boolean('supports_crypto')->default(false);

            $table->boolean('supports_payment')->default(false);

            /*
            |--------------------------------------------------------------------------
            | Priority
            |--------------------------------------------------------------------------
            */

            $table->unsignedInteger('priority')->default(1);

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_active')->default(true);

            $table->boolean('is_default')->default(false);

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
        Schema::dropIfExists('currency_providers');
    }
};
