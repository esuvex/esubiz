<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * usage_contexts is deliberately JSON.
         *
         * Example:
         * [
         *   "marketplace_checkout",
         *   "user_checkout",
         *   "developer_checkout",
         *   "wallet_funding"
         * ]
         *
         * This allows future Esubiz payment contexts to be added
         * without adding another database column every time.
         */

        Schema::table('payment_providers', function (Blueprint $table) {
            if (!Schema::hasColumn(
                'payment_providers',
                'usage_contexts'
            )) {
                $table->json('usage_contexts')
                    ->nullable()
                    ->after('settings');
            }
        });

        Schema::table('offline_payment_methods', function (Blueprint $table) {
            if (!Schema::hasColumn(
                'offline_payment_methods',
                'usage_contexts'
            )) {
                $table->json('usage_contexts')
                    ->nullable()
                    ->after('settings');
            }
        });

        Schema::table('payout_methods', function (Blueprint $table) {
            if (!Schema::hasColumn(
                'payout_methods',
                'usage_contexts'
            )) {
                $table->json('usage_contexts')
                    ->nullable()
                    ->after('form_fields');
            }
        });

        /*
         * Gift Card and Wallet are platform-level payment methods,
         * rather than normal payment-provider records.
         *
         * Store their allowed-use configuration centrally in
         * wallet_settings.
         */
        Schema::table('wallet_settings', function (Blueprint $table) {
            if (!Schema::hasColumn(
                'wallet_settings',
                'gift_card_usage_contexts'
            )) {
                $table->json('gift_card_usage_contexts')
                    ->nullable();
            }

            if (!Schema::hasColumn(
                'wallet_settings',
                'wallet_usage_contexts'
            )) {
                $table->json('wallet_usage_contexts')
                    ->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('payment_providers', function (Blueprint $table) {
            if (Schema::hasColumn(
                'payment_providers',
                'usage_contexts'
            )) {
                $table->dropColumn('usage_contexts');
            }
        });

        Schema::table('offline_payment_methods', function (Blueprint $table) {
            if (Schema::hasColumn(
                'offline_payment_methods',
                'usage_contexts'
            )) {
                $table->dropColumn('usage_contexts');
            }
        });

        Schema::table('payout_methods', function (Blueprint $table) {
            if (Schema::hasColumn(
                'payout_methods',
                'usage_contexts'
            )) {
                $table->dropColumn('usage_contexts');
            }
        });

        Schema::table('wallet_settings', function (Blueprint $table) {
            $drops = [];

            if (Schema::hasColumn(
                'wallet_settings',
                'gift_card_usage_contexts'
            )) {
                $drops[] = 'gift_card_usage_contexts';
            }

            if (Schema::hasColumn(
                'wallet_settings',
                'wallet_usage_contexts'
            )) {
                $drops[] = 'wallet_usage_contexts';
            }

            if ($drops) {
                $table->dropColumn($drops);
            }
        });
    }
};
