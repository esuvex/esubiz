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
        Schema::create('plans', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Identity
            |--------------------------------------------------------------------------
            */

            $table->string('name');

            $table->string('slug')->unique();

            $table->text('description')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Plan Type
            |--------------------------------------------------------------------------
            */

            $table->enum('audience', [

                'saas',

                'developer',

                'both'

            ])->default('saas');

            $table->enum('billing_model', [

                'subscription',

                'commission',

                'hybrid'

            ])->default('subscription');

            /*
            |--------------------------------------------------------------------------
            | Pricing
            |--------------------------------------------------------------------------
            */

            $table->decimal('price', 15, 2)->default(0);

            $table->decimal('commission_rate', 5, 2)->default(0);

            /*
            |--------------------------------------------------------------------------
            | Trial
            |--------------------------------------------------------------------------
            */

            $table->boolean('has_trial')->default(true);

            $table->unsignedInteger('trial_days')->default(14);

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_active')->default(true);

            $table->boolean('is_featured')->default(false);

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
        Schema::dropIfExists('plans');
    }
};
