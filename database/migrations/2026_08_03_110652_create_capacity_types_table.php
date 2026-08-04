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
        Schema::create('capacity_types', function (Blueprint $table) {

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
            | Classification
            |--------------------------------------------------------------------------
            */

            $table->enum('category', [

                'core_service',

                'communication',

                'commerce',

                'finance',

                'hr',

                'website',

                'ai',

                'developer',

                'system'

            ]);

            /*
            |--------------------------------------------------------------------------
            | Capacity Type
            |--------------------------------------------------------------------------
            */

            $table->enum('value_type', [

                'quantity',

                'storage',

                'credits',

                'slot',

                'boolean'

            ])->default('quantity');

            $table->string('unit')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Defaults
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger('default_value')->default(0);

            /*
            |--------------------------------------------------------------------------
            | Marketplace
            |--------------------------------------------------------------------------
            */

            $table->boolean('can_purchase')->default(true);

            $table->boolean('is_core')->default(false);

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
        Schema::dropIfExists('capacity_types');
    }
};
