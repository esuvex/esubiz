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
        Schema::table('developer_builds', function (Blueprint $table) {
            $table->boolean('ai_theme_enabled')->default(false)->after('selected_theme');
            $table->text('ai_theme_prompt')->nullable()->after('ai_theme_enabled');
            $table->json('ai_theme_preferences')->nullable()->after('ai_theme_prompt');
            $table->string('ai_theme_status')->nullable()->after('ai_theme_preferences');
            $table->string('ai_theme_reference')->nullable()->after('ai_theme_status');
            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('developer_builds', function (Blueprint $table) {
            $table->boolean('ai_theme_enabled')->default(false)->after('selected_theme');
            $table->text('ai_theme_prompt')->nullable()->after('ai_theme_enabled');
            $table->json('ai_theme_preferences')->nullable()->after('ai_theme_prompt');
            $table->string('ai_theme_status')->nullable()->after('ai_theme_preferences');
            $table->string('ai_theme_reference')->nullable()->after('ai_theme_status');
            //
        });
    }
};
