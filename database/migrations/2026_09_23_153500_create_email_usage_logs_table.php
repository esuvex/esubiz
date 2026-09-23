<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('email_usage_logs')) {
            return;
        }

        Schema::create(
            'email_usage_logs',
            function (Blueprint $table) {
                $table->id();

                /*
                 * Stable application-side identity.
                 *
                 * One logical send keeps one request UUID even if
                 * provider/event processing happens later.
                 */
                $table->uuid('request_uuid')
                    ->unique();

                /*
                 * Ownership/context.
                 *
                 * website_id is nullable because Central Esubiz can
                 * send its own operational email without a tenant.
                 */
                $table->unsignedBigInteger('website_id')
                    ->nullable()
                    ->index();

                $table->unsignedBigInteger('user_id')
                    ->nullable()
                    ->index();

                /*
                 * Deployment/source context:
                 *
                 * central
                 * saas
                 * off_server
                 */
                $table->string('deployment_scope', 30)
                    ->index();

                /*
                 * Sender mode:
                 *
                 * default
                 * premium
                 * central
                 */
                $table->string('sender_mode', 30)
                    ->index();

                /*
                 * Canonical Email policy/type key:
                 *
                 * authentication
                 * core_transactional
                 * module
                 * esubiz_system
                 * marketing
                 *
                 * Stored as a string so the future Central Email
                 * type registry remains authoritative.
                 */
                $table->string('email_type', 80)
                    ->index();

                /*
                 * Provider/transport identity.
                 */
                $table->string('provider', 50)
                    ->default('aws');

                $table->string('provider_service', 80)
                    ->default('ses');

                $table->string('provider_region', 50)
                    ->nullable();

                $table->string('provider_message_id', 255)
                    ->nullable()
                    ->index();

                /*
                 * Send state.
                 *
                 * Examples:
                 * pending
                 * provider_accepted
                 * failed
                 * delivered
                 * bounced
                 * complained
                 * rejected
                 * suppressed
                 */
                $table->string('status', 50)
                    ->default('pending')
                    ->index();

                /*
                 * Recipient totals only.
                 *
                 * Actual recipient addresses belong to mailbox/send
                 * records, not duplicated into the commercial ledger.
                 */
                $table->unsignedInteger('recipient_count')
                    ->default(0);

                /*
                 * Immutable commercial snapshot for this send.
                 *
                 * Provider cost is preserved in the provider currency.
                 */
                $table->decimal(
                    'provider_unit_cost',
                    24,
                    12
                )->nullable();

                $table->decimal(
                    'provider_total_cost',
                    24,
                    12
                )->nullable();

                $table->string(
                    'provider_currency',
                    10
                )->nullable();

                $table->string(
                    'email_markup_type',
                    20
                )->nullable();

                $table->decimal(
                    'email_markup_value',
                    24,
                    8
                )->nullable();

                $table->decimal(
                    'email_markup_amount',
                    24,
                    12
                )->nullable();

                $table->decimal(
                    'selling_cost_provider_currency',
                    24,
                    12
                )->nullable();

                /*
                 * FX snapshot.
                 *
                 * No customer-facing FX markup is included here.
                 */
                $table->decimal(
                    'exchange_rate',
                    24,
                    12
                )->nullable();

                $table->string(
                    'exchange_rate_provider',
                    80
                )->nullable();

                $table->date(
                    'exchange_rate_date'
                )->nullable();

                $table->string(
                    'central_currency',
                    10
                )->nullable();

                $table->decimal(
                    'selling_cost_central_currency',
                    24,
                    8
                )->nullable();

                /*
                 * Per-recipient base/default-currency pricing snapshot.
                 *
                 * These fields make the Admin-configured selling-rate
                 * cap directly auditable/reportable without parsing the
                 * full JSON pricing snapshot.
                 */
                $table->decimal(
                    'uncapped_rate_per_recipient_base_currency',
                    24,
                    8
                )->nullable();

                $table->decimal(
                    'max_rate_per_recipient_base_currency',
                    24,
                    8
                )->nullable();

                $table->boolean(
                    'cap_applied'
                )->default(false);

                $table->decimal(
                    'selling_rate_per_recipient_base_currency',
                    24,
                    8
                )->nullable();

                /*
                 * Internal Email Credit conversion snapshot.
                 */
                $table->decimal(
                    'email_credit_value',
                    24,
                    8
                )->nullable();

                /*
                 * Commercial quote and actual Central ledger debit are
                 * deliberately separate.
                 *
                 * credits_quoted:
                 * immutable amount calculated before provider dispatch.
                 *
                 * credits_charged:
                 * actual amount successfully consumed from the
                 * authoritative Central Email Credit ledger.
                 */
                $table->decimal(
                    'credits_quoted',
                    18,
                    6
                )->default(0);

                $table->decimal(
                    'credits_charged',
                    18,
                    6
                )->default(0);

                /*
                 * Link to the existing authoritative Central credit
                 * transaction after a successful provider send.
                 *
                 * String keeps this compatible with the existing
                 * Central ledger's flexible source/reference model.
                 */
                $table->string(
                    'credit_transaction_reference',
                    191
                )->nullable()
                    ->index();

                /*
                 * Source record allows mailbox/notification/module
                 * sends to link back without coupling this table to
                 * one specific mail model.
                 */
                $table->string(
                    'source_type',
                    150
                )->nullable();

                $table->string(
                    'source_id',
                    191
                )->nullable();

                /*
                 * Complete pricing/provider snapshot:
                 *
                 * AWS SKU/rate code/unit
                 * provider sync timestamp
                 * quote details
                 * send context
                 * future provider metadata
                 */
                $table->json('metadata')
                    ->nullable();

                $table->text('error_message')
                    ->nullable();

                $table->timestamp('provider_accepted_at')
                    ->nullable();

                $table->timestamp('completed_at')
                    ->nullable();

                $table->timestamps();

                $table->index(
                    [
                        'website_id',
                        'created_at',
                    ],
                    'email_usage_logs_website_date_index'
                );

                $table->index(
                    [
                        'deployment_scope',
                        'sender_mode',
                        'created_at',
                    ],
                    'email_usage_logs_scope_mode_date_index'
                );

                $table->index(
                    [
                        'email_type',
                        'created_at',
                    ],
                    'email_usage_logs_type_date_index'
                );

                $table->index(
                    [
                        'source_type',
                        'source_id',
                    ],
                    'email_usage_logs_source_index'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'email_usage_logs'
        );
    }
};
