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
        Schema::create('payout_verification_logs', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Payout Account
            |--------------------------------------------------------------------------
            */

            $table->foreignId('payout_account_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Identity
            |--------------------------------------------------------------------------
            */

            $table->uuid('uuid')->unique();

            /*
            |--------------------------------------------------------------------------
            | Verification Provider
            |--------------------------------------------------------------------------
            */

            $table->string('provider')
                ->nullable();

            $table->string('verification_type')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Request / Response
            |--------------------------------------------------------------------------
            */

            $table->json('request_data')
                ->nullable();

            $table->json('response_data')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Result
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [

                'pending',

                'processing',

                'verified',

                'failed'

            ])->default('pending');

            $table->text('failure_reason')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Verified By
            |--------------------------------------------------------------------------
            */

            $table->foreignId('verified_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('verified_at')
                ->nullable();

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
        Schema::dropIfExists('payout_verification_logs');
    }
};
