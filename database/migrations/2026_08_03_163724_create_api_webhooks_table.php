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
        Schema::create('api_webhooks', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Relationships
            |--------------------------------------------------------------------------
            */

            $table->foreignId('api_key_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('workspace_id')
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
            | Webhook
            |--------------------------------------------------------------------------
            */

            $table->string('url');

            $table->string('secret')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Events
            |--------------------------------------------------------------------------
            */

            $table->json('events');

            /*
            |--------------------------------------------------------------------------
            | Delivery
            |--------------------------------------------------------------------------
            */

            $table->unsignedInteger('retry_count')->default(3);

            $table->timestamp('last_sent_at')->nullable();

            $table->timestamp('last_success_at')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

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
        Schema::dropIfExists('api_webhooks');
    }
};
