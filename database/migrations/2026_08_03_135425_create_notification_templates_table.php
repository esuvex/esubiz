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
        Schema::create('notification_templates', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Owner
            |--------------------------------------------------------------------------
            */

            $table->foreignId('workspace_id')
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('notification_channel_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Identity
            |--------------------------------------------------------------------------
            */

            $table->string('name');

            $table->string('slug');

            /*
            |--------------------------------------------------------------------------
            | Content
            |--------------------------------------------------------------------------
            */

            $table->string('subject')->nullable();

            $table->longText('body');

            /*
            |--------------------------------------------------------------------------
            | Variables
            |--------------------------------------------------------------------------
            */

            $table->json('variables')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Trigger
            |--------------------------------------------------------------------------
            */

            $table->string('event')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_enabled')->default(true);

            $table->boolean('is_default')->default(false);

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
        Schema::dropIfExists('notification_templates');
    }
};
