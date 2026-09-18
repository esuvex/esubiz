<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'marketplace_financial_rules',
            function (Blueprint $table) {
                $table->id();

                /*
                 * theme, module, addon, bundle, website_type, etc.
                 */
                $table->string('product_type', 50)
                    ->unique('mkt_fin_product_uq');

                /*
                 * Percentage of eligible Marketplace sale revenue
                 * payable to the Developer/publisher.
                 */
                $table->decimal(
                    'developer_share_percent',
                    8,
                    4
                )->default(0);

                /*
                 * Marketplace operating expense allocation for this
                 * product type.
                 */
                $table->decimal(
                    'expense_percent',
                    8,
                    4
                )->default(0);

                $table->boolean('is_active')
                    ->default(true)
                    ->index('mkt_fin_active_ix');

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'marketplace_financial_rules'
        );
    }
};
