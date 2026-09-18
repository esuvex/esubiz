<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'marketplace_referral_rules',
            function (Blueprint $table) {
                $table->id();

                /*
                 * Marketplace product type:
                 * theme, module, addon, bundle, website_type, etc.
                 */
                $table->string('product_type', 50);

                /*
                 * Dynamic Central role identifier.
                 * Examples: user, developer, staff.
                 */
                $table->string('referrer_role', 80);

                /*
                 * Overrides ONLY Level 1 commission percentage.
                 * All other referral behaviour remains controlled
                 * by the authoritative Central Referral Plan.
                 */
                $table->decimal(
                    'level_one_commission_percent',
                    8,
                    4
                );

                $table->boolean('is_active')
                    ->default(true)
                    ->index('mkt_ref_active_ix');

                $table->timestamps();

                /*
                 * Only one Level 1 override may exist for the same
                 * Marketplace product type + referrer role.
                 */
                $table->unique(
                    [
                        'product_type',
                        'referrer_role',
                    ],
                    'mkt_ref_type_role_uq'
                );

                $table->index(
                    'product_type',
                    'mkt_ref_product_ix'
                );

                $table->index(
                    'referrer_role',
                    'mkt_ref_role_ix'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'marketplace_referral_rules'
        );
    }
};
