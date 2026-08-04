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
        Schema::create('conversation_messages', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Relationships
            |--------------------------------------------------------------------------
            */

            $table->foreignId('conversation_id')
                ->constrained()
                ->cascadeOnDelete();

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

            /*
            |--------------------------------------------------------------------------
            | Sender
            |--------------------------------------------------------------------------
            */

            $table->enum('sender_type', [

                'customer',

                'agent',

                'ai',

                'system'

            ]);

            /*
            |--------------------------------------------------------------------------
            | Message
            |--------------------------------------------------------------------------
            */

            $table->longText('message')->nullable();

            $table->enum('message_type', [

                'text',

                'image',

                'video',

                'audio',

                'file',

                'location',

                'contact',

                'system'

            ])->default('text');

            /*
            |--------------------------------------------------------------------------
            | Attachments
            |--------------------------------------------------------------------------
            */

            $table->json('attachments')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_read')->default(false);

            $table->timestamp('read_at')->nullable();

            $table->timestamp('delivered_at')->nullable();

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
        Schema::dropIfExists('conversation_messages');
    }
};
