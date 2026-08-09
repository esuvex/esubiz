<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('developer_builds', function (Blueprint $table) {
            $table->string('stage')->default('information')->after('status');
            $table->string('build_type')->default('developer')->after('version');

            $table->decimal('subtotal', 15, 2)->default(0)->after('package_size');
            $table->decimal('total_cost', 15, 2)->default(0)->after('subtotal');

            $table->string('payment_status')->default('unpaid')->after('total_cost');
            $table->timestamp('paid_at')->nullable()->after('payment_status');

            $table->json('selected_capacity')->nullable()->after('configuration');
            $table->json('selected_modules')->nullable()->after('selected_capacity');
            $table->json('selected_theme')->nullable()->after('selected_modules');

            $table->index(['developer_id', 'stage']);
            $table->index(['developer_id', 'payment_status']);
        });
    }

    public function down(): void
    {
        Schema::table('developer_builds', function (Blueprint $table) {
            $table->dropIndex(['developer_id', 'stage']);
            $table->dropIndex(['developer_id', 'payment_status']);

            $table->dropColumn([
                'stage',
                'build_type',
                'subtotal',
                'total_cost',
                'payment_status',
                'paid_at',
                'selected_capacity',
                'selected_modules',
                'selected_theme',
            ]);
        });
    }
};
