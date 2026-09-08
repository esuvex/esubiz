<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * ESUBIZ_CATALOG_PRODUCT_WEBSITE_TYPE_ENUM_V2
     *
     * Preserve every existing catalog product type and add
     * website_type as the canonical Marketplace identity
     * for prepared Website Type packages.
     */
    public function up(): void
    {
        DB::statement("
            ALTER TABLE catalog_products
            MODIFY product_type ENUM(
                'plan',
                'core',
                'website',
                'website_type',
                'addon',
                'module',
                'theme',
                'template',
                'membership',
                'domain',
                'professional_email',
                'ai_credit',
                'sms_credit',
                'email_credit',
                'whatsapp_credit',
                'service',
                'license',
                'other'
            ) NOT NULL
        ");
    }

    public function down(): void
    {
        /*
         * Intentionally do not remove website_type automatically.
         * Existing Website Type catalog records may depend on it.
         */
    }
};
