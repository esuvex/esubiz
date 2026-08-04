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
        Schema::create('a_i_agents', function (Blueprint $table) {

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
            | Agent
            |--------------------------------------------------------------------------
            */

            $table->enum('type', [

                'assistant',

                'automation',

                'support',

                'sales',

                'finance',

                'hr',

                'crm',

                'marketing',

                'custom'

            ])->default('assistant');

            $table->string('provider');

            $table->string('model');

            /*
            |--------------------------------------------------------------------------
            | Behaviour
            |--------------------------------------------------------------------------
            */

            $table->longText('system_prompt')->nullable();

            $table->json('configuration')->nullable();

            $table->json('tools')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Knowledge
            |--------------------------------------------------------------------------
            */

            $table->boolean('use_memory')->default(true);

            $table->boolean('use_knowledge_base')->default(true);

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_system')->default(false);

            $table->boolean('is_default')->default(false);

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
        Schema::dropIfExists('a_i_agents');
    }
};
