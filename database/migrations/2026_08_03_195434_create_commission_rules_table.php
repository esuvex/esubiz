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
        Schema::create('commission_rules', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Scope
            |--------------------------------------------------------------------------
            */

            $table->enum('scope_type', [

                'global',

                'saas',

                'developer',

                'workspace',

                'user'

            ])->default('global');

            /*
            |--------------------------------------------------------------------------
            | Owner / Target
            |--------------------------------------------------------------------------
            */

            $table->foreignId('owner_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Commission Type
            |--------------------------------------------------------------------------
            */

            $table->enum('type', [

                'referral',

                'partner',

                'developer',

                'marketplace',

                'subscription',

                'custom'

            ])->default('referral');

            /*
            |--------------------------------------------------------------------------
            | Calculation
            |--------------------------------------------------------------------------
            */

            $table->enum('calculation_type', [

                'percentage',

                'fixed'

            ])->default('percentage');

            $table->decimal('rate', 8, 4)
                ->default(0);

            $table->decimal('fixed_amount', 18, 8)
                ->default(0);

            /*
            |--------------------------------------------------------------------------
            | Conditions
            |--------------------------------------------------------------------------
            */

            $table->json('conditions')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Priority
            |--------------------------------------------------------------------------
            */

            $table->unsignedInteger('priority')
                ->default(1);

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_active')
                ->default(true);

            /*
            |--------------------------------------------------------------------------
            | Validity
            |--------------------------------------------------------------------------
            */

            $table->timestamp('starts_at')
                ->nullable();

            $table->timestamp('expires_at')
                ->nullable();

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
        Schema::dropIfExists('commission_rules');
    }
};
