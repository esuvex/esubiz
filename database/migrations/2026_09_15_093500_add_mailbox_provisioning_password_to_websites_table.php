<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /*
     * ESUBIZ_SAAS_MAILBOX_INITIAL_CREDENTIAL_V3
     */
    public function up(): void
    {
        if (
            !Schema::hasColumn(
                'websites',
                'mailbox_provisioning_password'
            )
        ) {
            Schema::table(
                'websites',
                function (Blueprint $table): void {
                    $table
                        ->text('mailbox_provisioning_password')
                        ->nullable()
                        ->after('admin_password');
                }
            );
        }
    }

    public function down(): void
    {
        if (
            Schema::hasColumn(
                'websites',
                'mailbox_provisioning_password'
            )
        ) {
            Schema::table(
                'websites',
                function (Blueprint $table): void {
                    $table->dropColumn(
                        'mailbox_provisioning_password'
                    );
                }
            );
        }
    }
};
