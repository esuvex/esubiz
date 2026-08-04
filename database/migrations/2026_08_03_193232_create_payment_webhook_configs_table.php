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
        Schema::create('payment_webhook_configs', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Provider Account
            |--------------------------------------------------------------------------
            */

            $table->foreignId('payment_provider_account_id')
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
            | Webhook URL
            |--------------------------------------------------------------------------
            */

            $table->string('endpoint_url');

            /*
            |--------------------------------------------------------------------------
            | Security
            |--------------------------------------------------------------------------
            */

            $table->string('secret')
                ->nullable();

            $table->string('signature_algorithm')
                ->default('sha256');

            /*
            |--------------------------------------------------------------------------
            | Events
            |--------------------------------------------------------------------------
            */

            $table->json('events')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Processing
            |--------------------------------------------------------------------------
            */

            $table->boolean('verify_signature')
                ->default(true);

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
        Schema::dropIfExists('payment_webhook_configs');
    }
};
