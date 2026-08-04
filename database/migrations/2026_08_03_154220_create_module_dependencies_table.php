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
        Schema::create('module_dependencies', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Module Relationships
            |--------------------------------------------------------------------------
            */

            $table->foreignId('module_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('dependency_module_id')
                ->constrained('modules')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Requirement
            |--------------------------------------------------------------------------
            */

            $table->string('minimum_version')->nullable();

            $table->boolean('required')->default(true);

            /*
            |--------------------------------------------------------------------------
            | Audit
            |--------------------------------------------------------------------------
            */

            $table->timestamps();

            $table->unique([
                'module_id',
                'dependency_module_id'
            ]);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('module_dependencies');
    }
};
