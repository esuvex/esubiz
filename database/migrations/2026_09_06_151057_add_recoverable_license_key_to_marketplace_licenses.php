<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketplace_licenses', function (Blueprint $table) {
            /*
             * ESUBIZ_MARKETPLACE_RECOVERABLE_LICENSE_V1
             *
             * The actual human-readable Marketplace license is retained
             * centrally for:
             *
             * - Admin Website Manager
             * - support
             * - audit
             * - refunds
             * - revocation
             * - transfers
             * - manual installation
             * - automatic installation
             *
             * It is encrypted with Laravel application encryption rather
             * than stored as unprotected plaintext.
             *
             * token_hash remains only an internal validation lookup index.
             */
            $table->longText('license_key_encrypted')
                ->nullable()
                ->after('license_reference');
        });
    }

    public function down(): void
    {
        Schema::table('marketplace_licenses', function (Blueprint $table) {
            $table->dropColumn('license_key_encrypted');
        });
    }
};
