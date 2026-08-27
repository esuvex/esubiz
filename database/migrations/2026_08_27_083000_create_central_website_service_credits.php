<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'central_website_service_credits',
            function (Blueprint $table) {

                $table->id();

                /*
                 * Central Website registry is authoritative.
                 *
                 * SaaS and off-server websites therefore use exactly
                 * the same balance ownership model.
                 */
                $table
                    ->unsignedBigInteger('website_id');

                $table
                    ->string('service', 32);

                /*
                 * Decimal rather than integer so future services may
                 * consume fractional units without another schema.
                 */
                $table
                    ->decimal(
                        'balance',
                        20,
                        6
                    )
                    ->default(0);

                $table
                    ->decimal(
                        'lifetime_credited',
                        20,
                        6
                    )
                    ->default(0);

                $table
                    ->decimal(
                        'lifetime_consumed',
                        20,
                        6
                    )
                    ->default(0);

                $table->timestamps();

                $table->unique(
                    [
                        'website_id',
                        'service',
                    ],
                    'central_website_service_credit_unique'
                );

                $table->index(
                    'service',
                    'central_website_service_credit_service_idx'
                );
            }
        );


        Schema::create(
            'central_website_service_credit_transactions',
            function (Blueprint $table) {

                $table->id();

                $table
                    ->unsignedBigInteger('website_id');

                $table
                    ->string('service', 32);

                $table
                    ->string('direction', 16);

                $table
                    ->decimal(
                        'amount',
                        20,
                        6
                    );

                $table
                    ->decimal(
                        'balance_before',
                        20,
                        6
                    );

                $table
                    ->decimal(
                        'balance_after',
                        20,
                        6
                    );

                /*
                 * One immutable Central request/reference prevents
                 * duplicate callbacks or retries from charging or
                 * crediting the same service operation twice.
                 */
                $table
                    ->string(
                        'request_key',
                        191
                    )
                    ->nullable();

                $table
                    ->string(
                        'source_type',
                        64
                    )
                    ->nullable();

                $table
                    ->unsignedBigInteger(
                        'source_id'
                    )
                    ->nullable();

                $table
                    ->unsignedBigInteger(
                        'user_id'
                    )
                    ->nullable();

                $table
                    ->unsignedBigInteger(
                        'installation_id'
                    )
                    ->nullable();

                $table
                    ->json('metadata')
                    ->nullable();

                $table->timestamps();

                $table->unique(
                    [
                        'service',
                        'request_key',
                    ],
                    'central_service_credit_request_unique'
                );

                $table->index(
                    [
                        'website_id',
                        'service',
                    ],
                    'central_service_credit_website_service_idx'
                );
            }
        );
    }


    public function down(): void
    {
        Schema::dropIfExists(
            'central_website_service_credit_transactions'
        );

        Schema::dropIfExists(
            'central_website_service_credits'
        );
    }
};
