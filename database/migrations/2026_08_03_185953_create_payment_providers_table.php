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
        Schema::create('payment_providers', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Identity
            |--------------------------------------------------------------------------
            */

            $table->uuid('uuid')->unique();

            $table->string('name');

            $table->string('slug')->unique();

            /*
            |--------------------------------------------------------------------------
            | Provider Type
            |--------------------------------------------------------------------------
            */

            $table->enum('type', [

                'card',

                'bank',

                'mobile_money',

                'wallet',

                'crypto',

                'custom'

            ])->default('card');

            /*
            |--------------------------------------------------------------------------
            | API Configuration
            |--------------------------------------------------------------------------
            */

            $table->string('base_url')
                ->nullable();

            $table->json('credentials')
                ->nullable();

            $table->json('settings')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Supported Features
            |--------------------------------------------------------------------------
            */

            $table->boolean('supports_recurring')
                ->default(false);

            $table->boolean('supports_refunds')
                ->default(false);

            $table->boolean('supports_webhooks')
                ->default(false);

            /*
            |--------------------------------------------------------------------------
            | Availability
            |--------------------------------------------------------------------------
            */

            $table->json('supported_currencies')
                ->nullable();

            $table->json('supported_countries')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Priority
            |--------------------------------------------------------------------------
            */

            $table->unsignedInteger('priority')
                ->default(1);

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_active')
                ->default(true);

            $table->boolean('is_default')
                ->default(false);

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
        Schema::dropIfExists('payment_providers');
    }
};
