<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Email Boxes
        |--------------------------------------------------------------------------
        */

        Schema::create('email_boxes', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('email')->unique();

            $table->string('username')->nullable();

            $table->string('status')->default('active');

            $table->json('settings')->nullable();

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | Email Messages
        |--------------------------------------------------------------------------
        */

        Schema::create('email_messages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('email_box_id')
                ->constrained('email_boxes')
                ->cascadeOnDelete();

            $table->string('direction')->default('inbound');

            $table->string('from_address');
            $table->text('to_addresses');

            $table->text('cc_addresses')->nullable();
            $table->text('bcc_addresses')->nullable();

            $table->string('subject')->nullable();

            $table->longText('body')->nullable();

            $table->string('status')->default('received');

            $table->timestamp('read_at')->nullable();

            $table->timestamps();

            $table->index('direction');
            $table->index('status');
        });

        /*
        |--------------------------------------------------------------------------
        | Live Chat Conversations
        |--------------------------------------------------------------------------
        */

        Schema::create('live_chat_conversations', function (Blueprint $table) {
            $table->id();

            $table->string('visitor_name')->nullable();
            $table->string('visitor_email')->nullable();

            $table->string('status')->default('open');

            $table->string('channel')->default('website');

            $table->timestamp('started_at')->nullable();
            $table->timestamp('closed_at')->nullable();

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | Live Chat Messages
        |--------------------------------------------------------------------------
        */

        Schema::create('live_chat_messages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('conversation_id')
                ->constrained('live_chat_conversations')
                ->cascadeOnDelete();

            $table->string('sender_type');
            $table->unsignedBigInteger('sender_id')->nullable();

            $table->longText('message');

            $table->string('message_type')->default('text');

            $table->timestamp('read_at')->nullable();

            $table->timestamps();

            $table->index([
                'sender_type',
                'sender_id',
            ]);
        });

        /*
        |--------------------------------------------------------------------------
        | WhatsApp Connections
        |--------------------------------------------------------------------------
        */

        Schema::create('whatsapp_connections', function (Blueprint $table) {
            $table->id();

            $table->string('name');

            $table->string('phone_number')->nullable();

            $table->string('provider')->nullable();

            $table->string('external_id')->nullable();

            $table->string('status')->default('inactive');

            $table->json('credentials')->nullable();
            $table->json('settings')->nullable();

            $table->timestamp('connected_at')->nullable();

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | WhatsApp Messages
        |--------------------------------------------------------------------------
        */

        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('connection_id')
                ->constrained('whatsapp_connections')
                ->cascadeOnDelete();

            $table->string('direction');

            $table->string('recipient')->nullable();

            $table->string('message_type')->default('text');

            $table->longText('message')->nullable();

            $table->string('external_id')->nullable();

            $table->string('status')->default('pending');

            $table->timestamp('sent_at')->nullable();

            $table->timestamps();

            $table->index('external_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');
        Schema::dropIfExists('whatsapp_connections');
        Schema::dropIfExists('live_chat_messages');
        Schema::dropIfExists('live_chat_conversations');
        Schema::dropIfExists('email_messages');
        Schema::dropIfExists('email_boxes');
    }
};
