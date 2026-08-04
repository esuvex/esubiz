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
        Schema::create('referral_programs', function (Blueprint $table) {

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

            $table->text('description')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Program Type
            |--------------------------------------------------------------------------
            */

            $table->enum('program_type', [

                'affiliate',

                'referral',

                'agent',

                'ambassador',

                'partner',

                'mlm'

            ])->default('referral');

            /*
            |--------------------------------------------------------------------------
            | Network Structure
            |--------------------------------------------------------------------------
            */

            $table->enum('network_structure', [

                'single_level',

                'unilevel',

                'binary',

                'matrix',

                'forced_matrix',

                'custom'

            ])->default('single_level');

            /*
            |--------------------------------------------------------------------------
            | MLM Configuration
            |--------------------------------------------------------------------------
            */

            $table->unsignedInteger('maximum_levels')->default(1);

            $table->unsignedInteger('matrix_width')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Wallet
            |--------------------------------------------------------------------------
            */

            $table->boolean('use_wallet')->default(true);

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
        Schema::dropIfExists('referral_programs');
    }
};
