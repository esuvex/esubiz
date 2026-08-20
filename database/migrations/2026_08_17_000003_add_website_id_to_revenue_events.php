<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('revenue_events', function (Blueprint $table) {
            $table->unsignedBigInteger('website_id')
                ->nullable()
                ->after('workspace_id');

            $table->index('website_id');
        });
    }

    public function down(): void
    {
        Schema::table('revenue_events', function (Blueprint $table) {
            $table->dropIndex(['website_id']);
            $table->dropColumn('website_id');
        });
    }
};
