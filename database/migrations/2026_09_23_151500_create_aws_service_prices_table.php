<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('aws_service_prices')) {
            return;
        }

        Schema::create(
            'aws_service_prices',
            function (Blueprint $table) {
                $table->id();

                /*
                 * Provider/service identity.
                 *
                 * Example:
                 * provider = aws
                 * service = ses
                 * price_key = outbound_email
                 */
                $table
                    ->string('provider', 50)
                    ->default('aws');

                $table
                    ->string('service', 100);

                $table
                    ->string('price_key', 150);

                /*
                 * Region whose service price this represents.
                 * This is the SES operating region, not necessarily
                 * the AWS Pricing API endpoint region.
                 */
                $table
                    ->string('service_region', 50)
                    ->nullable();

                /*
                 * Normalized provider price.
                 *
                 * amount is kept at high precision because AWS service
                 * unit prices can be very small.
                 */
                $table
                    ->decimal('amount', 24, 12);

                $table
                    ->string('currency', 10)
                    ->default('USD');

                $table
                    ->string('unit', 100);

                /*
                 * AWS measured-unit quantity represented by pricePerUnit.
                 *
                 * AWS Price List defines pricePerUnit as the cost of one
                 * measured unit identified by the price dimension's unit.
                 *
                 * Therefore quantity remains 1 for a directly consumed
                 * AWS price dimension unless AWS explicitly publishes a
                 * different machine-readable measurement contract.
                 *
                 * Do not infer a divisor such as 1,000 from marketing
                 * wording or a human-readable price description.
                 */
                $table
                    ->decimal(
                        'quantity',
                        24,
                        6
                    )
                    ->default(1);

                $table
                    ->decimal(
                        'normalized_unit_amount',
                        24,
                        12
                    );

                /*
                 * AWS catalog identifiers and dimensions are retained
                 * so we do not lose the provider source-of-truth context.
                 */
                $table
                    ->string('service_code', 150)
                    ->nullable();

                $table
                    ->string('sku', 150)
                    ->nullable();

                $table
                    ->string('rate_code', 255)
                    ->nullable();

                $table
                    ->string('usage_type', 255)
                    ->nullable();

                $table
                    ->string('operation', 255)
                    ->nullable();

                $table
                    ->string('location')
                    ->nullable();

                $table
                    ->string('description')
                    ->nullable();

                /*
                 * Raw provider product/term information retained for
                 * auditing and future AWS pricing-model changes.
                 */
                $table
                    ->json('provider_payload')
                    ->nullable();

                $table
                    ->timestamp('provider_effective_at')
                    ->nullable();

                $table
                    ->timestamp('synced_at')
                    ->nullable();

                $table
                    ->timestamp('last_success_at')
                    ->nullable();

                $table
                    ->text('last_error')
                    ->nullable();

                $table
                    ->boolean('is_active')
                    ->default(true);

                $table->timestamps();

                $table->unique(
                    [
                        'provider',
                        'service',
                        'price_key',
                        'service_region',
                    ],
                    'aws_service_prices_identity_unique'
                );

                $table->index(
                    [
                        'provider',
                        'service',
                        'is_active',
                    ],
                    'aws_service_prices_lookup_index'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'aws_service_prices'
        );
    }
};
