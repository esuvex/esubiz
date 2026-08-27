<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('websites', function (Blueprint $table) {
            if (!Schema::hasColumn('websites', 'site_ai_name')) {
                $table->string('site_ai_name', 100)
                    ->nullable()
                    ->after('user_enabled');
            }

            if (!Schema::hasColumn('websites', 'site_ai_avatar')) {
                $table->string('site_ai_avatar')
                    ->nullable()
                    ->after('site_ai_name');
            }

            if (!Schema::hasColumn('websites', 'site_ai_color')) {
                $table->string('site_ai_color', 20)
                    ->default('#0b1f3a')
                    ->after('site_ai_avatar');
            }

            if (!Schema::hasColumn('websites', 'site_ai_text_color')) {
                $table->string('site_ai_text_color', 20)
                    ->default('#ffffff')
                    ->after('site_ai_color');
            }

            if (!Schema::hasColumn('websites', 'site_ai_user_color')) {
                $table->string('site_ai_user_color', 20)
                    ->default('#f1f5f9')
                    ->after('site_ai_text_color');
            }

            if (!Schema::hasColumn('websites', 'site_ai_user_text_color')) {
                $table->string('site_ai_user_text_color', 20)
                    ->default('#0f172a')
                    ->after('site_ai_user_color');
            }
        });
    }

    public function down(): void
    {
        Schema::table('websites', function (Blueprint $table) {
            $columns = [
                'site_ai_name',
                'site_ai_avatar',
                'site_ai_color',
                'site_ai_text_color',
                'site_ai_user_color',
                'site_ai_user_text_color',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('websites', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
