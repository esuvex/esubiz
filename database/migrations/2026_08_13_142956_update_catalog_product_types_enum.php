<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE catalog_products MODIFY product_type ENUM(
            'plan',
            'core',
            'website',
            'addon',
            'module',
            'theme',
            'template',
            'membership',
            'domain',
            'professional_email',
            'ai_credit',
            'sms_credit',
            'whatsapp_credit',
            'service',
            'license',
            'other'
        ) NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE catalog_products MODIFY product_type ENUM(
            'plan',
            'capacity',
            'module',
            'theme',
            'template',
            'membership',
            'domain',
            'professional_email',
            'ai_credit',
            'sms_credit',
            'whatsapp_credit',
            'service',
            'license',
            'other'
        ) NOT NULL");
    }
};
