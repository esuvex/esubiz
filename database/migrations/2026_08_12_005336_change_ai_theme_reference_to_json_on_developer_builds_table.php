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
            $table->json('ai_theme_reference')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('developer_builds', function (Blueprint $table) {
            $table->string('ai_theme_reference')->nullable()->change();
        });
    }
};
