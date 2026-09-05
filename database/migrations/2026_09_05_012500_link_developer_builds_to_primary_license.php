<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('developer_builds', function (Blueprint $table) {
            if (!Schema::hasColumn('developer_builds', 'license_registration_id')) {
                $table->unsignedBigInteger('license_registration_id')
                    ->nullable()
                    ->after('payment_status')
                    ->index('dev_build_license_reg_idx');
            }

            if (!Schema::hasColumn('developer_builds', 'license_key')) {
                $table->string('license_key', 255)
                    ->nullable()
                    ->after('license_registration_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('developer_builds', function (Blueprint $table) {
            if (Schema::hasColumn('developer_builds', 'license_key')) {
                $table->dropColumn('license_key');
            }

            if (Schema::hasColumn('developer_builds', 'license_registration_id')) {
                $table->dropIndex('dev_build_license_reg_idx');
                $table->dropColumn('license_registration_id');
            }
        });
    }
};
