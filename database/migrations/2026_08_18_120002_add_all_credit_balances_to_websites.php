<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('websites')) {
            return;
        }

        Schema::table('websites', function (Blueprint $table) {
            if (!Schema::hasColumn('websites', 'email_credits')) {
                $table->unsignedBigInteger('email_credits')
                    ->default(0)
                    ->after('sms_credits');
            }

            if (!Schema::hasColumn('websites', 'whatsapp_credits')) {
                $table->unsignedBigInteger('whatsapp_credits')
                    ->default(0)
                    ->after('email_credits');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('websites')) {
            return;
        }

        Schema::table('websites', function (Blueprint $table) {
            if (Schema::hasColumn('websites', 'whatsapp_credits')) {
                $table->dropColumn('whatsapp_credits');
            }

            if (Schema::hasColumn('websites', 'email_credits')) {
                $table->dropColumn('email_credits');
            }
        });
    }
};
