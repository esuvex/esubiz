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
        Schema::create('node_types', function (Blueprint $table) {

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
            | Category
            |--------------------------------------------------------------------------
            */

            $table->enum('category', [

                'trigger',

                'condition',

                'action',

                'ai',

                'communication',

                'finance',

                'crm',

                'hr',

                'integration',

                'utility',

                'logic'

            ]);

            /*
            |--------------------------------------------------------------------------
            | Engine
            |--------------------------------------------------------------------------
            */

            $table->string('engine');

            /*
            |--------------------------------------------------------------------------
            | UI
            |--------------------------------------------------------------------------
            */

            $table->string('icon')->nullable();

            $table->string('color')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Configuration
            |--------------------------------------------------------------------------
            */

            $table->json('schema')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_system')->default(true);

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
        Schema::dropIfExists('node_types');
    }
};
