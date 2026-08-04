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
        Schema::create('knowledge_bases', function (Blueprint $table) {

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

            $table->string('title');

            $table->string('slug');

            /*
            |--------------------------------------------------------------------------
            | Content
            |--------------------------------------------------------------------------
            */

            $table->longText('content')->nullable();

            $table->longText('summary')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Source
            |--------------------------------------------------------------------------
            */

            $table->enum('source_type', [

                'manual',

                'document',

                'website',

                'faq',

                'api'

            ])->default('manual');

            $table->string('source_reference')->nullable();

            /*
            |--------------------------------------------------------------------------
            | AI
            |--------------------------------------------------------------------------
            */

            $table->boolean('ai_enabled')->default(true);

            $table->boolean('searchable')->default(true);

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_published')->default(true);

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
        Schema::dropIfExists('knowledge_bases');
    }
};
