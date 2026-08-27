<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('central_ai_chat_settings')) {
            Schema::create('central_ai_chat_settings', function (Blueprint $table) {
                $table->id();

                $table->string('ai_bubble_color', 20)
                    ->default('#0b1f3a');

                $table->string('ai_text_color', 20)
                    ->default('#ffffff');

                $table->string('user_bubble_color', 20)
                    ->default('#f1f5f9');

                $table->string('user_text_color', 20)
                    ->default('#0f172a');

                $table->timestamps();
            });
        }

        if (
            DB::table('central_ai_chat_settings')->count() === 0
        ) {
            DB::table('central_ai_chat_settings')->insert([
                'ai_bubble_color' => '#0b1f3a',
                'ai_text_color' => '#ffffff',
                'user_bubble_color' => '#f1f5f9',
                'user_text_color' => '#0f172a',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('central_ai_chat_settings');
    }
};
