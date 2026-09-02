<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    protected $connection = 'website_tenant';

    public function up(): void
    {
        if (
            Schema::connection('website_tenant')
                ->hasTable('site_partner_ledger_entries')
        ) {
            return;
        }

        Schema::connection('website_tenant')
            ->create(
                'site_partner_ledger_entries',
                function (Blueprint $table): void {
                    $table->id();

                    $table->unsignedBigInteger('user_id');

                    $table->string(
                        'entry_type',
                        20
                    );

                    $table->string(
                        'entry_category',
                        50
                    );

                    $table->decimal(
                        'amount',
                        18,
                        2
                    );

                    $table->decimal(
                        'investment_percentage',
                        8,
                        4
                    )->nullable();

                    $table->string(
                        'profit_basis',
                        20
                    )->nullable();

                    $table->decimal(
                        'business_result',
                        18,
                        2
                    )->nullable();

                    $table->string(
                        'period_key',
                        100
                    )->nullable();

                    $table->string(
                        'source_type',
                        100
                    )->nullable();

                    $table->string(
                        'source_reference',
                        191
                    )->nullable();

                    $table->text('description')
                        ->nullable();

                    $table->timestamp(
                        'posted_at'
                    )->nullable();

                    $table->timestamps();

                    $table->index(
                        ['user_id', 'entry_type'],
                        'idx_partner_ledger_user_type'
                    );

                    $table->index(
                        ['user_id', 'posted_at'],
                        'idx_partner_ledger_user_posted'
                    );

                    $table->index(
                        ['period_key', 'profit_basis'],
                        'idx_partner_ledger_period_basis'
                    );

                    $table->unique(
                        [
                            'user_id',
                            'source_type',
                            'source_reference',
                        ],
                        'uq_partner_ledger_source'
                    );
                }
            );
    }

    public function down(): void
    {
        Schema::connection('website_tenant')
            ->dropIfExists(
                'site_partner_ledger_entries'
            );
    }
};
