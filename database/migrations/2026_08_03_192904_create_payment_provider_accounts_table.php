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
        Schema::create('payment_provider_accounts', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Ownership Scope
            |--------------------------------------------------------------------------
            */

            $table->enum('owner_type', [

                'platform',

                'workspace',

                'developer',

                'user'

            ])->default('platform');

            $table->foreignId('owner_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Provider
            |--------------------------------------------------------------------------
            */

            $table->foreignId('payment_provider_id')
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
            | Credentials
            |--------------------------------------------------------------------------
            */

            $table->json('credentials')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Configuration
            |--------------------------------------------------------------------------
            */

            $table->json('settings')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Verification
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_verified')
                ->default(false);

            $table->timestamp('verified_at')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Default Account
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_default')
                ->default(false);

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_active')
                ->default(true);

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
        Schema::dropIfExists('payment_provider_accounts');
    }
};
