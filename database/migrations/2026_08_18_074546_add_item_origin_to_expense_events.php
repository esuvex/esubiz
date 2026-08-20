<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expense_events', function (Blueprint $table) {
            $table->string('item_type')->nullable()->after('expense_type');
            $table->unsignedBigInteger('item_id')->nullable()->after('item_type');
            $table->string('item_name')->nullable()->after('item_id');

            $table->index(['item_type', 'item_id']);
        });
    }

    public function down(): void
    {
        Schema::table('expense_events', function (Blueprint $table) {
            $table->dropIndex(['item_type', 'item_id']);
            $table->dropColumn(['item_type', 'item_id', 'item_name']);
        });
    }
};
