<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('core_addons', function (Blueprint $table) {
            $table
                ->string('implementation_type', 20)
                ->default('allocation')
                ->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('core_addons', function (Blueprint $table) {
            $table->dropColumn('implementation_type');
        });
    }
};
