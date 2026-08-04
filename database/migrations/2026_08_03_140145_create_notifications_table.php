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
        Schema::create('notifications', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Relationships
            |--------------------------------------------------------------------------
            */

            $table->foreignId('workspace_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('notification_channel_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('notification_template_id')
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

            $table->string('reference')->unique();

            /*
            |--------------------------------------------------------------------------
            | Recipient
            |--------------------------------------------------------------------------
            */

            $table->string('recipient');

            /*
            |--------------------------------------------------------------------------
            | Content
            |--------------------------------------------------------------------------
            */

            $table->string('subject')->nullable();

            $table->longText('body');

            /*
            |--------------------------------------------------------------------------
            | Related Record
            |--------------------------------------------------------------------------
            */

            $table->nullableMorphs('notifiable');

            /*
            |--------------------------------------------------------------------------
            | Delivery
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [

                'pending',

                'queued',

                'sending',

                'sent',

                'delivered',

                'read',

                'failed'

            ])->default('pending');

            $table->timestamp('sent_at')->nullable();

            $table->timestamp('delivered_at')->nullable();

            $table->timestamp('read_at')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Provider
            |--------------------------------------------------------------------------
            */

            $table->string('provider_reference')->nullable();

            $table->json('provider_response')->nullable();

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
        Schema::dropIfExists('notifications');
    }
};
