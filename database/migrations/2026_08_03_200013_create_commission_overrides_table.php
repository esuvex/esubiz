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
        Schema::create('commission_overrides', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Target User
            |--------------------------------------------------------------------------
            */

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Scope
            |--------------------------------------------------------------------------
            */

            $table->enum('scope_type', [

                'user',

                'saas',

                'developer',

                'workspace'

            ])->default('user');

            $table->foreignId('scope_id')
                ->nullable();

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
            | Reason
            |--------------------------------------------------------------------------
            */

            $table->text('reason')
                ->nullable();

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

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commission_overrides');
    }
};
