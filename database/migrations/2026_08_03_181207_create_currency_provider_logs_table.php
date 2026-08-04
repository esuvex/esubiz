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
        Schema::create('currency_provider_logs', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Provider
            |--------------------------------------------------------------------------
            */

            $table->foreignId('currency_provider_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Request
            |--------------------------------------------------------------------------
            */

            $table->string('endpoint')->nullable();

            $table->json('request_payload')->nullable();

            $table->json('request_headers')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Response
            |--------------------------------------------------------------------------
            */

            $table->unsignedInteger('status_code')->nullable();

            $table->json('response_payload')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Performance
            |--------------------------------------------------------------------------
            */

            $table->unsignedInteger('response_time')->default(0);

            /*
            |--------------------------------------------------------------------------
            | Result
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [

                'success',

                'failed'

            ])->default('success');

            $table->text('error_message')->nullable();

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
        Schema::dropIfExists('currency_provider_logs');
    }
};
