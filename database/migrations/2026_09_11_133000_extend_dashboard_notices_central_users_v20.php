<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dashboard_notices', function (Blueprint $table) {
            if (!Schema::hasColumn('dashboard_notices', 'central_user_ids')) {
                $table->json('central_user_ids')
                    ->nullable()
                    ->after('central_role_ids');
            }
        });
    }

    public function down(): void
    {
        Schema::table('dashboard_notices', function (Blueprint $table) {
            if (Schema::hasColumn('dashboard_notices', 'central_user_ids')) {
                $table->dropColumn('central_user_ids');
            }
        });
    }
};
