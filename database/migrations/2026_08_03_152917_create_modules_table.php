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
        Schema::create('modules', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Identity
            |--------------------------------------------------------------------------
            */

            $table->uuid('uuid')->unique();

            $table->string('name');

            $table->string('slug')->unique();

            $table->text('description')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Developer
            |--------------------------------------------------------------------------
            */

            $table->foreignId('developer_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Module Type
            |--------------------------------------------------------------------------
            */

            $table->enum('type', [

                'core',

                'official',

                'developer',

                'private'

            ])->default('developer');

            /*
            |--------------------------------------------------------------------------
            | Version
            |--------------------------------------------------------------------------
            */

            $table->string('version')->default('1.0.0');

            /*
            |--------------------------------------------------------------------------
            | Package
            |--------------------------------------------------------------------------
            */

            $table->string('package_name')->nullable();

            $table->json('requirements')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_verified')->default(false);

            $table->boolean('is_active')->default(true);

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
        Schema::dropIfExists('modules');
    }
};
