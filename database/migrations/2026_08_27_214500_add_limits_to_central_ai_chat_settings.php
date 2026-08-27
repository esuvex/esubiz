<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('central_ai_chat_settings', function (Blueprint $table) {

            /*
             * Global Esubiz AI conversation limits.
             *
             * These are Central Admin defaults and are intended
             * for SaaS and off-server AI clients.
             */

            if (!Schema::hasColumn(
                'central_ai_chat_settings',
                'max_message_characters'
            )) {
                $table->unsignedInteger('max_message_characters')
                    ->default(10000);
            }

            if (!Schema::hasColumn(
                'central_ai_chat_settings',
                'max_conversation_messages'
            )) {
                $table->unsignedInteger('max_conversation_messages')
                    ->default(100);
            }

            if (!Schema::hasColumn(
                'central_ai_chat_settings',
                'max_photos_per_message'
            )) {
                $table->unsignedInteger('max_photos_per_message')
                    ->default(5);
            }

            if (!Schema::hasColumn(
                'central_ai_chat_settings',
                'max_photo_size_mb'
            )) {
                $table->unsignedInteger('max_photo_size_mb')
                    ->default(10);
            }
        });
    }


    public function down(): void
    {
        Schema::table('central_ai_chat_settings', function (Blueprint $table) {

            $columns = [
                'max_message_characters',
                'max_conversation_messages',
                'max_photos_per_message',
                'max_photo_size_mb',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn(
                    'central_ai_chat_settings',
                    $column
                )) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
