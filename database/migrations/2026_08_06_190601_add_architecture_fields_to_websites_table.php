<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('websites', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | Website Architecture
            |--------------------------------------------------------------------------
            */

            $table->string('website_code')->nullable()->unique()->after('uuid');

            $table->string('edition')->default('saas')->after('type');

            $table->string('owner_type')->default('owner')->after('edition');

            $table->boolean('multi_branch')->default(false)->after('owner_type');

            $table->unsignedInteger('branch_limit')->default(1)->after('multi_branch');

            $table->unsignedInteger('ai_credits')->default(0)->after('branch_limit');

            $table->unsignedInteger('sms_credits')->default(0)->after('ai_credits');

            $table->unsignedInteger('storage_mb')->default(0)->after('sms_credits');

            $table->unsignedInteger('bandwidth_mb')->default(0)->after('storage_mb');

            $table->json('enabled_modules')->nullable()->after('bandwidth_mb');

            $table->json('enabled_features')->nullable()->after('enabled_modules');

            $table->json('settings')->nullable()->after('enabled_features');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('websites', function (Blueprint $table) {

            $table->dropColumn([
                'website_code',
                'edition',
                'owner_type',
                'multi_branch',
                'branch_limit',
                'ai_credits',
                'sms_credits',
                'storage_mb',
                'bandwidth_mb',
                'enabled_modules',
                'enabled_features',
                'settings',
            ]);

        });
    }
};
