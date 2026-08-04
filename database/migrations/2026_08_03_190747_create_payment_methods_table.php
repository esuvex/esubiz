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
        Schema::create('payment_methods', function (Blueprint $table) {

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
            | Identity
            |--------------------------------------------------------------------------
            */

            $table->uuid('uuid')->unique();

            $table->string('name');

            $table->string('slug')->unique();

            /*
            |--------------------------------------------------------------------------
            | Method Type
            |--------------------------------------------------------------------------
            */

            $table->enum('type', [

                'card',

                'bank_transfer',

                'ussd',

                'mobile_money',

                'wallet',

                'crypto',

                'other'

            ])->default('card');

            /*
            |--------------------------------------------------------------------------
            | Availability
            |--------------------------------------------------------------------------
            */

            $table->json('supported_currencies')
                ->nullable();

            $table->json('supported_countries')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Pricing
            |--------------------------------------------------------------------------
            */

            $table->decimal('fixed_fee', 18, 8)
                ->default(0);

            $table->decimal('percentage_fee', 8, 4)
                ->default(0);

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
            | Checkout
            |--------------------------------------------------------------------------
            */

            $table->string('icon')
                ->nullable();

            $table->unsignedInteger('priority')
                ->default(1);

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_active')
                ->default(true);

            $table->boolean('is_default')
                ->default(false);

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
        Schema::dropIfExists('payment_methods');
    }
};
