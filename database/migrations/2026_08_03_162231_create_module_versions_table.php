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
        Schema::create('module_versions', function (Blueprint $table) {

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
            | Version Identity
            |--------------------------------------------------------------------------
            */

            $table->uuid('uuid')->unique();

            $table->string('version');

            /*
            |--------------------------------------------------------------------------
            | Release
            |--------------------------------------------------------------------------
            */

            $table->text('release_notes')->nullable();

            $table->json('changes')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Package
            |--------------------------------------------------------------------------
            */

            $table->string('package_path')->nullable();

            $table->unsignedBigInteger('package_size')->default(0);

            /*
            |--------------------------------------------------------------------------
            | Requirements
            |--------------------------------------------------------------------------
            */

            $table->json('requirements')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_stable')->default(false);

            $table->boolean('is_active')->default(true);

            /*
            |--------------------------------------------------------------------------
            | Audit
            |--------------------------------------------------------------------------
            */

            $table->timestamps();

            $table->softDeletes();

            $table->unique([
                'module_id',
                'version'
            ]);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('module_versions');
    }
};
