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
        Schema::create('payment_webhooks', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Provider
            |--------------------------------------------------------------------------
            */

            $table->foreignId('payment_provider_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Identity
            |--------------------------------------------------------------------------
            */

            $table->uuid('uuid')->unique();

            $table->string('event_id')
                ->nullable();

            $table->string('event_type');

            /*
            |--------------------------------------------------------------------------
            | Request Data
            |--------------------------------------------------------------------------
            */

            $table->json('payload');

            $table->json('headers')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Verification
            |--------------------------------------------------------------------------
            */

            $table->string('signature')
                ->nullable();

            $table->boolean('signature_verified')
                ->default(false);

            /*
            |--------------------------------------------------------------------------
            | Processing
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [

                'received',

                'processing',

                'processed',

                'failed'

            ])->default('received');

            $table->text('error_message')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Related Transaction
            |--------------------------------------------------------------------------
            */

            $table->foreignId('payment_transaction_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Audit
            |--------------------------------------------------------------------------
            */

            $table->timestamps();

            $table->softDeletes();

            $table->index([
                'event_type',
                'status'
            ]);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_webhooks');
    }
};
