<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            !Schema::hasTable(
                'marketplace_product_stagings'
            )
        ) {
            Schema::create(
                'marketplace_product_stagings',
                function (Blueprint $table) {

                    $table->id();

                    $table->uuid(
                        'uuid'
                    )->unique();

                    /*
                     * Prevent the same paid order/product from
                     * being staged twice.
                     */
                    $table->string(
                        'staging_key',
                        191
                    )->unique();

                    $table->foreignId(
                        'website_id'
                    )
                        ->constrained('websites')
                        ->cascadeOnDelete();

                    $table->unsignedBigInteger(
                        'workspace_id'
                    )->nullable()->index();

                    $table->unsignedBigInteger(
                        'user_id'
                    )->nullable()->index();

                    $table->unsignedBigInteger(
                        'marketplace_order_id'
                    )->nullable()->index();

                    $table->unsignedBigInteger(
                        'marketplace_listing_id'
                    )->nullable()->index();

                    /*
                     * addon
                     * bundle
                     * theme
                     * module
                     * future installable product
                     */
                    $table->string(
                        'product_type',
                        100
                    )->index();

                    $table->unsignedBigInteger(
                        'product_id'
                    )->index();

                    $table->string(
                        'product_name',
                        191
                    )->nullable();

                    $table->string(
                        'product_version',
                        100
                    )->nullable();

                    $table->enum(
                        'deployment_type',
                        [
                            'saas',
                            'off_server',
                        ]
                    )->index();

                    /*
                     * capability
                     * package
                     *
                     * Add-ons/bundles may be capability based.
                     * Themes/modules may be package based.
                     */
                    $table->string(
                        'staging_type',
                        50
                    )->default('package');

                    /*
                     * pending
                     * staged
                     * activated
                     * failed
                     * revoked
                     */
                    $table->string(
                        'status',
                        50
                    )->default('pending')
                    ->index();

                    /*
                     * Protected Central package reference.
                     *
                     * Never expose this raw path to an off-server
                     * browser/client.
                     */
                    $table->text(
                        'package_path'
                    )->nullable();

                    $table->string(
                        'checksum_sha256',
                        64
                    )->nullable();

                    /*
                     * addons
                     * themes
                     * modules
                     * etc.
                     */
                    $table->string(
                        'activation_area',
                        100
                    )->nullable();

                    $table->timestamp(
                        'staged_at'
                    )->nullable();

                    $table->timestamp(
                        'activated_at'
                    )->nullable();

                    $table->text(
                        'error_message'
                    )->nullable();

                    $table->json(
                        'metadata'
                    )->nullable();

                    $table->timestamps();

                    $table->index(
                        [
                            'website_id',
                            'product_type',
                            'status',
                        ],
                        'mps_site_product_status_idx'
                    );
                }
            );
        }
    }


    public function down(): void
    {
        Schema::dropIfExists(
            'marketplace_product_stagings'
        );
    }
};
