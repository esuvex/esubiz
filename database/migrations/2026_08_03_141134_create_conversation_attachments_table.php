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
        Schema::create('conversation_attachments', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Relationships
            |--------------------------------------------------------------------------
            */

            $table->foreignId('conversation_message_id')
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
            | File
            |--------------------------------------------------------------------------
            */

            $table->string('original_name');

            $table->string('stored_name');

            $table->string('disk')->default('public');

            $table->string('path');

            $table->string('mime_type');

            $table->unsignedBigInteger('size');

            /*
            |--------------------------------------------------------------------------
            | Attachment Type
            |--------------------------------------------------------------------------
            */

            $table->enum('type', [

                'image',

                'video',

                'audio',

                'document',

                'archive',

                'other'

            ])->default('other');

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_public')->default(false);

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
        Schema::dropIfExists('conversation_attachments');
    }
};
