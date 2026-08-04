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
        Schema::create('admin_override_logs', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Admin
            |--------------------------------------------------------------------------
            */

            $table->foreignId('admin_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Target
            |--------------------------------------------------------------------------
            */

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Action
            |--------------------------------------------------------------------------
            */

            $table->string('type');

            /*
            |--------------------------------------------------------------------------
            | Change Data
            |--------------------------------------------------------------------------
            */

            $table->json('old_values')->nullable();

            $table->json('new_values')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Reason
            |--------------------------------------------------------------------------
            */

            $table->text('reason')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Audit
            |--------------------------------------------------------------------------
            */

            $table->string('ip_address')->nullable();

            $table->text('user_agent')->nullable();

            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_override_logs');
    }
};
