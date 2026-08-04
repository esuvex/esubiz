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

            /*
            |--------------------------------------------------------------------------
            | Relationships
            |--------------------------------------------------------------------------
            */

            $table->foreignId('catalog_product_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('billing_period_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Pricing
            |--------------------------------------------------------------------------
            */

            $table->decimal('price', 15, 2)->default(0);

            $table->string('currency', 3)->default('NGN');

            /*
            |--------------------------------------------------------------------------
            | Billing Model
            |--------------------------------------------------------------------------
            */

            $table->enum('billing_model', [

                'subscription',

                'commission',

                'hybrid',

                'one_time',

                'free'

            ])->default('subscription');

            /*
            |--------------------------------------------------------------------------
            | Commission
            |--------------------------------------------------------------------------
            */

            $table->decimal('commission_rate', 5, 2)->default(0);

            /*
            |--------------------------------------------------------------------------
            | Trial
            |--------------------------------------------------------------------------
            */

            $table->boolean('has_trial')->default(false);

            $table->unsignedInteger('trial_days')->default(0);

            /*
            |--------------------------------------------------------------------------
            | Availability
            |--------------------------------------------------------------------------
            */

            $table->dateTime('starts_at')->nullable();

            $table->dateTime('ends_at')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_default')->default(false);

            $table->boolean('is_active')->default(true);

            /*
            |--------------------------------------------------------------------------
            | Audit
            |--------------------------------------------------------------------------
            */

            $table->timestamps();

            $table->softDeletes();

            /*
            |--------------------------------------------------------------------------
            | Constraints
            |--------------------------------------------------------------------------
            */

            $table->unique([
                'catalog_product_id',
                'billing_period_id',
                'billing_model'
            ]);

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
