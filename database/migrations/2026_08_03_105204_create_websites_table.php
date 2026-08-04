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
        Schema::create('websites', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Relationships
            |--------------------------------------------------------------------------
            */

            $table->foreignId('workspace_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Identity
            |--------------------------------------------------------------------------
            */

            $table->uuid('uuid')->unique();

            $table->string('name');

            $table->string('slug')->unique();

            /*
            |--------------------------------------------------------------------------
            | Website Classification
            |--------------------------------------------------------------------------
            */

            $table->string('website_type');

            $table->string('industry')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Website Status
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [

                'draft',

                'building',

                'published',

                'maintenance',

                'suspended',

                'archived'

            ])->default('draft');

            /*
            |--------------------------------------------------------------------------
            | Publishing
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_default')->default(false);

            $table->boolean('is_homepage')->default(false);

            $table->boolean('is_pwa')->default(true);

            $table->boolean('is_native_app')->default(false);

            /*
            |--------------------------------------------------------------------------
            | Theme
            |--------------------------------------------------------------------------
            */

            $table->string('theme')->nullable();

            $table->string('template')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Dates
            |--------------------------------------------------------------------------
            */

            $table->timestamp('published_at')->nullable();

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
        Schema::dropIfExists('websites');
    }
};
