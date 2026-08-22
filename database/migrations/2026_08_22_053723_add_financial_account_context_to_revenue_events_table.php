<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('revenue_events', function (Blueprint $table) {
            $table->unsignedBigInteger('financial_account_user_id')
                ->nullable()
                ->after('user_id');

            $table->unsignedBigInteger('financial_account_developer_id')
                ->nullable()
                ->after('financial_account_user_id');

            $table->string('financial_account_type', 50)
                ->default('user')
                ->after('financial_account_developer_id');

            $table->index(
                ['financial_account_type', 'financial_account_user_id'],
                'revenue_events_user_account_idx'
            );

            $table->index(
                ['financial_account_type', 'financial_account_developer_id'],
                'revenue_events_developer_account_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('revenue_events', function (Blueprint $table) {
            $table->dropIndex('revenue_events_user_account_idx');
            $table->dropIndex('revenue_events_developer_account_idx');
            $table->dropColumn([
                'financial_account_user_id',
                'financial_account_developer_id',
                'financial_account_type',
            ]);
        });
    }
};
