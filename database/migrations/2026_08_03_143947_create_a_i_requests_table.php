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
        Schema::create('a_i_requests', function (Blueprint $table) {

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

            $table->foreignId('user_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('prompt_template_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Identity
            |--------------------------------------------------------------------------
            */

            $table->uuid('uuid')->unique();

            $table->string('reference')->unique();

            /*
            |--------------------------------------------------------------------------
            | AI
            |--------------------------------------------------------------------------
            */

            $table->string('provider');

            $table->string('model');

            /*
            |--------------------------------------------------------------------------
            | Request
            |--------------------------------------------------------------------------
            */

            $table->longText('system_prompt')->nullable();

            $table->longText('user_prompt');

            $table->json('input')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Response
            |--------------------------------------------------------------------------
            */

            $table->longText('response')->nullable();

            $table->json('output')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Usage
            |--------------------------------------------------------------------------
            */

            $table->unsignedInteger('prompt_tokens')->default(0);

            $table->unsignedInteger('completion_tokens')->default(0);

            $table->unsignedInteger('total_tokens')->default(0);

            /*
            |--------------------------------------------------------------------------
            | Cost
            |--------------------------------------------------------------------------
            */

            $table->decimal('cost', 18, 8)->default(0);

            $table->string('currency', 3)->default('USD');

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [

                'pending',

                'processing',

                'completed',

                'failed',

                'cancelled'

            ])->default('pending');

            /*
            |--------------------------------------------------------------------------
            | Metadata
            |--------------------------------------------------------------------------
            */

            $table->json('metadata')->nullable();

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
        Schema::dropIfExists('a_i_requests');
    }
};
