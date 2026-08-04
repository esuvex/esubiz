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
        Schema::create('api_plans', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Ownership Scope
            |--------------------------------------------------------------------------
            */

            $table->enum('scope_type', [

                'core',

                'saas',

                'developer'

            ])->default('core');

            $table->foreignId('owner_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Identity
            |--------------------------------------------------------------------------
            */

            $table->uuid('uuid')->unique();

            $table->string('name');

            $table->string('slug')->unique();

            $table->text('description')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Pricing
            |--------------------------------------------------------------------------
            */

            $table->decimal('price', 18, 2)->default(0);

            $table->string('currency', 3)->default('NGN');

            $table->enum('billing_period', [

                'monthly',

                'quarterly',

                'yearly',

                'custom'

            ])->default('monthly');

            /*
            |--------------------------------------------------------------------------
            | Usage Limits
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger('request_limit')->default(0);

            $table->unsignedBigInteger('token_limit')->default(0);

            $table->unsignedBigInteger('bandwidth_limit')->default(0);

            /*
            |--------------------------------------------------------------------------
            | Features
            |--------------------------------------------------------------------------
            */

            $table->json('features')->nullable();

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
        Schema::dropIfExists('api_plans');
    }
};
