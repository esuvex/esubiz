<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('api_applications', function (Blueprint $table) {
            $table->string('application_type')
                ->default('external')
                ->after('description');

            $table->index('application_type');
        });
    }

    public function down(): void
    {
        Schema::table('api_applications', function (Blueprint $table) {
            $table->dropIndex(['application_type']);
            $table->dropColumn('application_type');
        });
    }
};
