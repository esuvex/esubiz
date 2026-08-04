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
        Schema::create('user_custom_plans', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Owner Scope (Core / SaaS / Developer)
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
            | User Assignment
            |--------------------------------------------------------------------------
            */

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('workspace_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Plan Reference
            |--------------------------------------------------------------------------
            */

            $table->foreignId('plan_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Custom Plan
            |--------------------------------------------------------------------------
            */

            $table->string('name');

            $table->decimal('price', 18, 2);

            $table->string('currency', 3)->default('NGN');

            $table->enum('billing_period', [

                'monthly',

                'quarterly',

                'yearly',

                'lifetime',

                'custom'

            ])->default('monthly');

            /*
            |--------------------------------------------------------------------------
            | Custom Features
            |--------------------------------------------------------------------------
            */

            $table->json('features')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Validity
            |--------------------------------------------------------------------------
            */

            $table->timestamp('starts_at')->nullable();

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

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_custom_plans');
    }
};
