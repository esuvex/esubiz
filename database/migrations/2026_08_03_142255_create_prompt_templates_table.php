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
        Schema::create('prompt_templates', function (Blueprint $table) {

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
            | Prompt
            |--------------------------------------------------------------------------
            */

            $table->longText('system_prompt');

            $table->longText('user_prompt')->nullable();

            /*
            |--------------------------------------------------------------------------
            | AI
            |--------------------------------------------------------------------------
            */

            $table->string('provider')->nullable();

            $table->string('model')->nullable();

            $table->float('temperature')->default(0.7);

            $table->unsignedInteger('max_tokens')->default(2000);

            /*
            |--------------------------------------------------------------------------
            | Variables
            |--------------------------------------------------------------------------
            */

            $table->json('variables')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_system')->default(false);

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
        Schema::dropIfExists('prompt_templates');
    }
};
