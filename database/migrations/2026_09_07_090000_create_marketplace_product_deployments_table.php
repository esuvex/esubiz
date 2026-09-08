<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ESUBIZ_MARKETPLACE_PRODUCT_DEPLOYMENTS_V1
 *
 * Central deployment instruction/state for Marketplace products that must
 * be installed into an Esubiz Core instance.
 *
 * Commercial fulfilment creates entitlement/license first, then creates
 * a deployment record. The target Core performs the actual package
 * validation, extraction and installation.
 *
 * Used by:
 * - Esubiz-hosted SaaS tenant websites;
 * - off-server Esubiz Core installations.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('marketplace_product_deployments')) {
            return;
        }

        Schema::create(
            'marketplace_product_deployments',
            function (Blueprint $table) {
                $table->id();

                $table->uuid('uuid')->unique();

                $table->unsignedBigInteger('user_id')
                    ->nullable()
                    ->index();

                $table->unsignedBigInteger('website_id')
                    ->nullable()
                    ->index();

                $table->unsignedBigInteger('marketplace_order_id')
                    ->nullable()
                    ->index();

                $table->unsignedBigInteger('marketplace_listing_id')
                    ->nullable()
                    ->index();

                $table->unsignedBigInteger('catalog_product_id')
                    ->nullable()
                    ->index();

                $table->unsignedBigInteger('entitlement_id')
                    ->index();

                $table->unsignedBigInteger('license_id')
                    ->nullable()
                    ->index();

                $table->string('product_type', 40)
                    ->index();

                $table->string('product_slug', 191)
                    ->nullable();

                $table->string('product_version', 100)
                    ->nullable();

                $table->string('deployment_type', 30)
                    ->index();

                $table->string('action', 30)
                    ->default('install');

                $table->string('status', 30)
                    ->default('pending')
                    ->index();

                $table->unsignedInteger('attempts')
                    ->default(0);

                /*
                 * Package reference points to protected central package
                 * identity/storage metadata. It must never expose a public
                 * unrestricted package URL.
                 */
                $table->string('package_reference', 1024)
                    ->nullable();

                $table->string('checksum_sha256', 64)
                    ->nullable();

                $table->json('manifest')
                    ->nullable();

                $table->json('context')
                    ->nullable();

                $table->json('result')
                    ->nullable();

                $table->text('last_error')
                    ->nullable();

                $table->timestamp('claimed_at')
                    ->nullable();

                $table->timestamp('started_at')
                    ->nullable();

                $table->timestamp('completed_at')
                    ->nullable();

                $table->timestamp('failed_at')
                    ->nullable();

                $table->timestamps();

                $table->unique(
                    [
                        'entitlement_id',
                        'product_type',
                        'action',
                        'deployment_type',
                    ],
                    'marketplace_deployment_entitlement_action_unique'
                );
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'marketplace_product_deployments'
        );
    }
};
