<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /*
     * ESUBIZ_CENTRAL_ACCOUNT_VERIFICATION_FIELDS_V1
     *
     * email_verified_at is Laravel's existing email authority
     * where available.
     *
     * SMS and WhatsApp verification are tracked separately
     * because Admin may enable either channel independently.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'sms_verified_at')) {
                $table->timestamp(
                    'sms_verified_at'
                )
                    ->nullable()
                    ->after('phone_number');
            }

            if (!Schema::hasColumn('users', 'whatsapp_verified_at')) {
                $table->timestamp(
                    'whatsapp_verified_at'
                )
                    ->nullable()
                    ->after('sms_verified_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (
                Schema::hasColumn(
                    'users',
                    'whatsapp_verified_at'
                )
            ) {
                $table->dropColumn(
                    'whatsapp_verified_at'
                );
            }

            if (
                Schema::hasColumn(
                    'users',
                    'sms_verified_at'
                )
            ) {
                $table->dropColumn(
                    'sms_verified_at'
                );
            }
        });
    }
};
