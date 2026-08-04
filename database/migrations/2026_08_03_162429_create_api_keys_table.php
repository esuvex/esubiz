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
        Schema::create('api_keys', function (Blueprint $table) {

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

            /*
            |--------------------------------------------------------------------------
            | Identity
            |--------------------------------------------------------------------------
            */

            $table->uuid('uuid')->unique();

            $table->string('name');

            /*
            |--------------------------------------------------------------------------
            | Key
            |--------------------------------------------------------------------------
            */

            $table->string('key')->unique();

            $table->string('secret')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Access
            |--------------------------------------------------------------------------
            */

            $table->json('permissions')->nullable();

            $table->json('allowed_ips')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Usage
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger('requests_count')->default(0);

            $table->timestamp('last_used_at')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_active')->default(true);

            $table->timestamp('expires_at')->nullable();

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
        Schema::dropIfExists('api_keys');
    }
};
