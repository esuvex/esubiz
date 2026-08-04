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
        Schema::create('module_assets', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Relationships
            |--------------------------------------------------------------------------
            */

            $table->foreignId('module_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Identity
            |--------------------------------------------------------------------------
            */

            $table->uuid('uuid')->unique();

            $table->string('name');

            /*
            |--------------------------------------------------------------------------
            | Asset Type
            |--------------------------------------------------------------------------
            */

            $table->enum('type', [

                'icon',

                'logo',

                'screenshot',

                'banner',

                'document',

                'package',

                'video',

                'other'

            ])->default('other');

            /*
            |--------------------------------------------------------------------------
            | File
            |--------------------------------------------------------------------------
            */

            $table->string('disk')->default('public');

            $table->string('path');

            $table->string('mime_type')->nullable();

            $table->unsignedBigInteger('size')->default(0);

            /*
            |--------------------------------------------------------------------------
            | Metadata
            |--------------------------------------------------------------------------
            */

            $table->json('metadata')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_public')->default(false);

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
        Schema::dropIfExists('module_assets');
    }
};
