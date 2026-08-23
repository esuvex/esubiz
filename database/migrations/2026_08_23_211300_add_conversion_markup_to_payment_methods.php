<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_providers', function (Blueprint $table) {
            if (!Schema::hasColumn(
                'payment_providers',
                'conversion_enabled'
            )) {
                $table->boolean('conversion_enabled')
                    ->default(false)
                    ->after('usage_contexts');

                $table->string(
                    'conversion_type',
                    20
                )
                    ->default('none')
                    ->after('conversion_enabled');

                $table->string(
                    'conversion_provider',
                    50
                )
                    ->nullable()
                    ->after('conversion_type');

                $table->string(
                    'conversion_target',
                    30
                )
                    ->nullable()
                    ->after('conversion_provider');

                $table->decimal(
                    'manual_conversion_rate',
                    24,
                    12
                )
                    ->nullable()
                    ->after('conversion_target');

                $table->boolean('markup_enabled')
                    ->default(false)
                    ->after('manual_conversion_rate');

                $table->string(
                    'markup_type',
                    20
                )
                    ->default('percentage')
                    ->after('markup_enabled');

                $table->decimal(
                    'markup_value',
                    18,
                    8
                )
                    ->default(0)
                    ->after('markup_type');
            }
        });

        Schema::table('offline_payment_methods', function (Blueprint $table) {
            if (!Schema::hasColumn(
                'offline_payment_methods',
                'conversion_enabled'
            )) {
                $table->boolean('conversion_enabled')
                    ->default(false)
                    ->after('usage_contexts');

                $table->string(
                    'conversion_type',
                    20
                )
                    ->default('none')
                    ->after('conversion_enabled');

                $table->string(
                    'conversion_provider',
                    50
                )
                    ->nullable()
                    ->after('conversion_type');

                $table->string(
                    'conversion_target',
                    30
                )
                    ->nullable()
                    ->after('conversion_provider');

                $table->decimal(
                    'manual_conversion_rate',
                    24,
                    12
                )
                    ->nullable()
                    ->after('conversion_target');

                $table->boolean('markup_enabled')
                    ->default(false)
                    ->after('manual_conversion_rate');

                $table->string(
                    'markup_type',
                    20
                )
                    ->default('percentage')
                    ->after('markup_enabled');

                $table->decimal(
                    'markup_value',
                    18,
                    8
                )
                    ->default(0)
                    ->after('markup_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payment_providers', function (Blueprint $table) {
            $columns = [
                'conversion_enabled',
                'conversion_type',
                'conversion_provider',
                'conversion_target',
                'manual_conversion_rate',
                'markup_enabled',
                'markup_type',
                'markup_value',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn(
                    'payment_providers',
                    $column
                )) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('offline_payment_methods', function (Blueprint $table) {
            $columns = [
                'conversion_enabled',
                'conversion_type',
                'conversion_provider',
                'conversion_target',
                'manual_conversion_rate',
                'markup_enabled',
                'markup_type',
                'markup_value',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn(
                    'offline_payment_methods',
                    $column
                )) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
