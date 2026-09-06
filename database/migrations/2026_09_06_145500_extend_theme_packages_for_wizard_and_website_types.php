<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | ESUBIZ THEME PACKAGE COMMERCE / WIZARD CONTRACT V1
        |--------------------------------------------------------------------------
        |
        | Themes remain universal Core packages.
        |
        | Commercial deployment:
        | - SaaS availability + price + duration
        | - Off-server availability + price
        |
        | Wizard discovery:
        | - User Wizard visibility
        | - Developer Wizard visibility
        |
        | Website Type compatibility:
        | - Themes are assigned to Website Types
        | - Add-ons are NOT assigned per Website Type
        |
        */

        Schema::table('theme_packages', function (Blueprint $table) {

            if (!Schema::hasColumn('theme_packages', 'saas_billing_period')) {
                $table
                    ->unsignedInteger('saas_billing_period')
                    ->nullable()
                    ->after('saas_price');
            }

            if (!Schema::hasColumn('theme_packages', 'show_in_user_wizard')) {
                $table
                    ->boolean('show_in_user_wizard')
                    ->default(true)
                    ->index()
                    ->after('marketplace_enabled');
            }

            if (!Schema::hasColumn('theme_packages', 'show_in_developer_wizard')) {
                $table
                    ->boolean('show_in_developer_wizard')
                    ->default(true)
                    ->index()
                    ->after('show_in_user_wizard');
            }
        });


        /*
        |--------------------------------------------------------------------------
        | WEBSITE TYPE <-> THEME ASSIGNMENT
        |--------------------------------------------------------------------------
        |
        | Admin decides which themes belong to each Website Type.
        |
        | Example:
        |
        | Business
        |   - Business 1.0
        |
        | Ecommerce
        |   - Ecommerce Basic 1.0
        |   - Ecommerce Modern
        |
        | Wizard visibility remains a separate theme-level gate.
        |
        */

        if (!Schema::hasTable('theme_package_website_type')) {

            Schema::create(
                'theme_package_website_type',
                function (Blueprint $table) {

                    $table->id();

                    $table
                        ->foreignId('theme_package_id')
                        ->constrained('theme_packages')
                        ->cascadeOnDelete();

                    $table
                        ->foreignId('website_type_id')
                        ->constrained('website_types')
                        ->cascadeOnDelete();

                    $table->timestamps();

                    $table->unique(
                        [
                            'theme_package_id',
                            'website_type_id',
                        ],
                        'uq_theme_package_website_type'
                    );

                    $table->index(
                        [
                            'website_type_id',
                            'theme_package_id',
                        ],
                        'ix_website_type_theme_package'
                    );
                }
            );
        }
    }


    public function down(): void
    {
        Schema::dropIfExists(
            'theme_package_website_type'
        );

        Schema::table('theme_packages', function (Blueprint $table) {

            $columns = [];

            if (Schema::hasColumn('theme_packages', 'saas_billing_period')) {
                $columns[] = 'saas_billing_period';
            }

            if (Schema::hasColumn('theme_packages', 'show_in_user_wizard')) {
                $columns[] = 'show_in_user_wizard';
            }

            if (Schema::hasColumn('theme_packages', 'show_in_developer_wizard')) {
                $columns[] = 'show_in_developer_wizard';
            }

            if ($columns) {
                $table->dropColumn($columns);
            }
        });
    }
};
