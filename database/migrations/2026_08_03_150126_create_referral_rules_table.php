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
        Schema::create('referral_rules', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Ownership
            |--------------------------------------------------------------------------
            */

            $table->foreignId('workspace_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Identity
            |--------------------------------------------------------------------------
            */

            $table->string('name');

            $table->string('slug');

            /*
            |--------------------------------------------------------------------------
            | Rule
            |--------------------------------------------------------------------------
            */

            $table->enum('trigger', [

                'registration',

                'subscription',

                'renewal',

                'purchase',

                'wallet_funding',

                'wallet_spending',

                'custom'

            ]);

            $table->enum('reward_type', [

                'fixed',

                'percentage'

            ])->default('percentage');

            $table->decimal('reward_value', 18, 2);

            /*
            |--------------------------------------------------------------------------
            | MLM
            |--------------------------------------------------------------------------
            */

            $table->unsignedInteger('maximum_levels')->default(1);

            /*
            |--------------------------------------------------------------------------
            | Qualification
            |--------------------------------------------------------------------------
            */

            $table->decimal('minimum_transaction', 18, 2)->default(0);

            $table->boolean('requires_successful_payment')->default(true);

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

            $table->unique([
                'workspace_id',
                'slug'
            ]);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_rules');
    }
};
