<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table(
            'marketplace_checkout_sessions',
            function (Blueprint $table) {

                if (
                    !Schema::hasColumn(
                        'marketplace_checkout_sessions',
                        'checkout_origin'
                    )
                ) {
                    /*
                     * central_account
                     * saas_website
                     * off_server_website
                     */
                    $table->string(
                        'checkout_origin',
                        50
                    )
                    ->default('central_account')
                    ->after('account_mode')
                    ->index();
                }


                if (
                    !Schema::hasColumn(
                        'marketplace_checkout_sessions',
                        'return_url'
                    )
                ) {
                    /*
                     * Final destination after successful fulfilment.
                     *
                     * This will NOT be blindly trusted.
                     * Off-server return URLs will later be validated
                     * against the website's registered domain.
                     */
                    $table->text(
                        'return_url'
                    )
                    ->nullable()
                    ->after('checkout_origin');
                }


                if (
                    !Schema::hasColumn(
                        'marketplace_checkout_sessions',
                        'return_area'
                    )
                ) {
                    /*
                     * Logical product destination:
                     *
                     * addons
                     * themes
                     * modules
                     * ai
                     * sms
                     * email
                     * whatsapp
                     * marketplace
                     */
                    $table->string(
                        'return_area',
                        100
                    )
                    ->nullable()
                    ->after('return_url');
                }


                if (
                    !Schema::hasColumn(
                        'marketplace_checkout_sessions',
                        'wallet_allowed'
                    )
                ) {
                    /*
                     * Central account + SaaS website:
                     *     true
                     *
                     * Off-server website checkout link:
                     *     false
                     */
                    $table->boolean(
                        'wallet_allowed'
                    )
                    ->default(true)
                    ->after('return_area');
                }


                if (
                    !Schema::hasColumn(
                        'marketplace_checkout_sessions',
                        'origin_token_id'
                    )
                ) {
                    /*
                     * Future signed off-server checkout-link identity.
                     *
                     * We intentionally store the identifier rather than
                     * a raw secret token.
                     */
                    $table->string(
                        'origin_token_id',
                        100
                    )
                    ->nullable()
                    ->after('wallet_allowed')
                    ->index();
                }
            }
        );
    }


    public function down(): void
    {
        Schema::table(
            'marketplace_checkout_sessions',
            function (Blueprint $table) {

                $columns = [
                    'checkout_origin',
                    'return_url',
                    'return_area',
                    'wallet_allowed',
                    'origin_token_id',
                ];


                foreach ($columns as $column) {

                    if (
                        Schema::hasColumn(
                            'marketplace_checkout_sessions',
                            $column
                        )
                    ) {
                        $table->dropColumn(
                            $column
                        );
                    }
                }
            }
        );
    }
};
