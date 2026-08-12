<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_custom_plans', function (Blueprint $table) {
            $table->boolean('is_complimentary')
                ->default(false)
                ->after('price');

            $table->unsignedInteger('complimentary_days')
                ->nullable()
                ->after('is_complimentary');

            $table->text('complimentary_reason')
                ->nullable()
                ->after('complimentary_days');
        });
    }

    public function down(): void
    {
        Schema::table('user_custom_plans', function (Blueprint $table) {
            $table->dropColumn([
                'is_complimentary',
                'complimentary_days',
                'complimentary_reason',
            ]);
        });
    }
};
