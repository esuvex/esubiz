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
        Schema::create('workflows', function (Blueprint $table) {

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

            $table->uuid('uuid')->unique();

            $table->string('name');

            $table->string('slug');

            $table->text('description')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Workflow Scope
            |--------------------------------------------------------------------------
            */

            $table->enum('scope', [

                'system',

                'module',

                'workspace'

            ])->default('workspace');

            /*
            |--------------------------------------------------------------------------
            | Trigger
            |--------------------------------------------------------------------------
            */

            $table->string('trigger_event');

            /*
            |--------------------------------------------------------------------------
            | Execution
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_active')->default(true);

            $table->boolean('run_once')->default(false);

            $table->unsignedInteger('priority')->default(100);

            /*
            |--------------------------------------------------------------------------
            | AI
            |--------------------------------------------------------------------------
            */

            $table->boolean('allow_ai')->default(true);

            /*
            |--------------------------------------------------------------------------
            | Versioning
            |--------------------------------------------------------------------------
            */

            $table->unsignedInteger('version')->default(1);

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
        Schema::dropIfExists('workflows');
    }
};
