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
        Schema::create('referral_levels', function (Blueprint $table) {

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
            | Level
            |--------------------------------------------------------------------------
            */

            $table->unsignedInteger('level');

            $table->string('name');

            /*
            |--------------------------------------------------------------------------
            | Commission
            |--------------------------------------------------------------------------
            */

            $table->enum('commission_type', [

                'fixed',

                'percentage'

            ])->default('percentage');

            $table->decimal('commission_value', 18, 2);

            /*
            |--------------------------------------------------------------------------
            | Qualification
            |--------------------------------------------------------------------------
            */

            $table->unsignedInteger('minimum_referrals')->default(0);

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
                'level'
            ]);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_levels');
    }
};
