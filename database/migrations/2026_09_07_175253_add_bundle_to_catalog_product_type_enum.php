<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /*
     * ESUBIZ_CATALOG_PRODUCT_TYPE_BUNDLE_V1
     *
     * Adds bundle as a canonical Marketplace catalog product type.
     * Website Type deployment profiles can then dynamically fetch
     * saved bundles alongside themes, modules and add-ons.
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
                'bundle',
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
};
