<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dashboard_notices', function (Blueprint $table) {
            if (!Schema::hasColumn('dashboard_notices', 'image_path')) {
                $table->text('image_path')->nullable();
            }

            if (!Schema::hasColumn('dashboard_notices', 'rotation_enabled')) {
                $table->boolean('rotation_enabled')
                    ->default(true);
            }

            if (!Schema::hasColumn('dashboard_notices', 'rotation_seconds')) {
                $table->unsignedInteger('rotation_seconds')
                    ->default(8);
            }
        });
    }

    public function down(): void
    {
        Schema::table('dashboard_notices', function (Blueprint $table) {
            $columns = [];

            foreach ([
                'image_path',
                'rotation_enabled',
                'rotation_seconds',
            ] as $column) {
                if (Schema::hasColumn('dashboard_notices', $column)) {
                    $columns[] = $column;
                }
            }

            if ($columns) {
                $table->dropColumn($columns);
            }
        });
    }
};
