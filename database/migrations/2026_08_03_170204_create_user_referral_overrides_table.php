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
        Schema::create('user_referral_overrides', function (Blueprint $table) {

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
            | User
            |--------------------------------------------------------------------------
            */

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Workspace
            |--------------------------------------------------------------------------
            */

            $table->foreignId('workspace_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Referral Override
            |--------------------------------------------------------------------------
            */

            $table->enum('commission_type', [

                'fixed',

                'percentage'

            ])->default('percentage');

            $table->decimal('commission_value', 18, 2);

            /*
            |--------------------------------------------------------------------------
            | MLM Override
            |--------------------------------------------------------------------------
            */

            $table->json('level_commissions')->nullable();

            $table->unsignedInteger('maximum_levels')->default(1);

            /*
            |--------------------------------------------------------------------------
            | Rules
            |--------------------------------------------------------------------------
            */

            $table->json('conditions')->nullable();

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
        Schema::dropIfExists('user_referral_overrides');
    }
};
