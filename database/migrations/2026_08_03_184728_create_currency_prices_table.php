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
        Schema::create('currency_prices', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Ownership Scope
            |--------------------------------------------------------------------------
            */

            $table->enum('scope_type', [

                'core',

                'saas',

                'developer',

                'application'

            ])->default('core');

            $table->foreignId('owner_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Product Reference
            |--------------------------------------------------------------------------
            */

            $table->string('priceable_type');

            $table->unsignedBigInteger('priceable_id');

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
            | Pricing
            |--------------------------------------------------------------------------
            */

            $table->decimal('price', 18, 8);

            $table->decimal('setup_fee', 18, 8)
                ->default(0);

            $table->decimal('discount', 18, 8)
                ->default(0);

            /*
            |--------------------------------------------------------------------------
            | Billing
            |--------------------------------------------------------------------------
            */

            $table->enum('billing_period', [

                'one_time',

                'daily',

                'weekly',

                'monthly',

                'quarterly',

                'yearly'

            ])->default('monthly');

            /*
            |--------------------------------------------------------------------------
            | Rules
            |--------------------------------------------------------------------------
            */

            $table->json('conditions')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_active')
                ->default(true);

            /*
            |--------------------------------------------------------------------------
            | Audit
            |--------------------------------------------------------------------------
            */

            $table->timestamps();

            $table->softDeletes();

            $table->unique([
                'priceable_type',
                'priceable_id',
                'currency_id'
            ]);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currency_prices');
    }
};
