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
        Schema::create('workflow_connections', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Workflow
            |--------------------------------------------------------------------------
            */

            $table->foreignId('workflow_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Source & Target Nodes
            |--------------------------------------------------------------------------
            */

            $table->foreignId('source_node_id')
                ->constrained('workflow_nodes')
                ->cascadeOnDelete();

            $table->foreignId('target_node_id')
                ->constrained('workflow_nodes')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Ports
            |--------------------------------------------------------------------------
            */

            $table->string('source_port')->default('output');

            $table->string('target_port')->default('input');

            /*
            |--------------------------------------------------------------------------
            | Condition
            |--------------------------------------------------------------------------
            */

            $table->string('label')->nullable();

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
        Schema::dropIfExists('workflow_connections');
    }
};
