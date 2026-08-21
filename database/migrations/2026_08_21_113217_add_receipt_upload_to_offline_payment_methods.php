<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offline_payment_methods', function (Blueprint $table) {
            $table->boolean('receipt_upload_enabled')
                ->default(false)
                ->after('instructions');

            $table->string('receipt_upload_label')
                ->nullable()
                ->after('receipt_upload_enabled');

            $table->string('receipt_upload_help')
                ->nullable()
                ->after('receipt_upload_label');
        });
    }

    public function down(): void
    {
        Schema::table('offline_payment_methods', function (Blueprint $table) {
            $table->dropColumn([
                'receipt_upload_enabled',
                'receipt_upload_label',
                'receipt_upload_help',
            ]);
        });
    }
};
