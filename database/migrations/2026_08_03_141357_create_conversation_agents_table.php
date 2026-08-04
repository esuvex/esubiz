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
        Schema::create('conversation_agents', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Ownership
            |--------------------------------------------------------------------------
            */

            $table->foreignId('workspace_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Identity
            |--------------------------------------------------------------------------
            */

            $table->uuid('uuid')->unique();

            $table->string('name');

            $table->string('slug');

            /*
            |--------------------------------------------------------------------------
            | Agent
            |--------------------------------------------------------------------------
            */

            $table->enum('type', [

                'human',

                'ai',

                'hybrid'

            ])->default('ai');

            $table->string('provider')->nullable();

            $table->string('model')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Behaviour
            |--------------------------------------------------------------------------
            */

            $table->text('system_prompt')->nullable();

            $table->json('configuration')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Assignment
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_default')->default(false);

            $table->boolean('handover_to_human')->default(true);

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
        Schema::dropIfExists('conversation_agents');
    }
};
