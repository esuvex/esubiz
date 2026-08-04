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
        Schema::create('workflow_nodes', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Relationships
            |--------------------------------------------------------------------------
            */

            $table->foreignId('workflow_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('node_type_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Canvas
            |--------------------------------------------------------------------------
            */

            $table->uuid('uuid')->unique();

            $table->integer('position_x')->default(0);

            $table->integer('position_y')->default(0);

            /*
            |--------------------------------------------------------------------------
            | Configuration
            |--------------------------------------------------------------------------
            */

            $table->json('configuration')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_enabled')->default(true);

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
        Schema::dropIfExists('workflow_nodes');
    }
};
