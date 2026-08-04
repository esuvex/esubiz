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
        Schema::create('conversations', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Ownership
            |--------------------------------------------------------------------------
            */

            $table->foreignId('workspace_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Participants
            |--------------------------------------------------------------------------
            */

            $table->foreignId('customer_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('agent_id')
                ->nullable()
                ->constrained('users')
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
            | Channel
            |--------------------------------------------------------------------------
            */

            $table->enum('channel', [

                'live_chat',

                'whatsapp',

                'facebook',

                'instagram',

                'telegram',

                'email',

                'sms'

            ])->default('live_chat');

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->enum('status', [

                'open',

                'assigned',

                'waiting',

                'resolved',

                'closed'

            ])->default('open');

            /*
            |--------------------------------------------------------------------------
            | Assignment
            |--------------------------------------------------------------------------
            */

            $table->timestamp('assigned_at')->nullable();

            $table->timestamp('closed_at')->nullable();

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
        Schema::dropIfExists('conversations');
    }
};
