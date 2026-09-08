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
        | Marketplace Entitlements
        |--------------------------------------------------------------------------
        |
        | ESUBIZ_MARKETPLACE_ENTITLEMENTS_V1
        |
        | Central authoritative ownership record for every Marketplace product.
        |
        | product_type is deliberately generic:
        | theme, module, addon, website_type, core_product, etc.
        |
        | SaaS and off-server purchases both create an entitlement.
        | Off-server entitlements may additionally receive a signed license.
        |
        */

        Schema::create('marketplace_entitlements', function (Blueprint $table) {
            $table->id();

            $table->uuid('uuid')->unique();

            /*
             * Commerce identity.
             *
             * We intentionally avoid hard foreign keys here because Marketplace
             * products/orders may evolve independently while entitlement history
             * must remain durable.
             */
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->unsignedBigInteger('order_item_id')->nullable()->index();

            $table->string('product_type', 64)->index();
            $table->unsignedBigInteger('product_id')->index();

            $table->string('product_slug', 191)->nullable()->index();
            $table->string('product_version', 64)->nullable();

            /*
             * Owner / target.
             */
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('website_id')->nullable()->index();

            /*
             * saas | off_server
             */
            $table->string('deployment', 32)->index();

            /*
             * active | suspended | revoked | expired | refunded
             */
            $table->string('status', 32)
                ->default('active')
                ->index();

            /*
             * Entitlement capabilities.
             *
             * Example:
             * ["install","activate","update"]
             */
            $table->json('capabilities')->nullable();

            /*
             * Quantity/seats are generic so future products can license
             * more than one installation where commercially allowed.
             */
            $table->unsignedInteger('quantity')
                ->default(1);

            $table->unsignedInteger('activation_limit')
                ->default(1);

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->timestamp('revoked_at')->nullable();
            $table->string('revocation_reason', 500)->nullable();

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index(
                ['product_type', 'product_id', 'deployment', 'status'],
                'mp_entitlement_product_deployment_status'
            );

            $table->index(
                ['user_id', 'website_id', 'status'],
                'mp_entitlement_owner_status'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | Marketplace Licenses
        |--------------------------------------------------------------------------
        |
        | Central-issued signed license records.
        |
        | The private signing authority belongs only to Central Esubiz.
        | Core installations receive a signed payload/token and can never
        | manufacture authoritative licenses themselves.
        |
        */

        Schema::create('marketplace_licenses', function (Blueprint $table) {
            $table->id();

            $table->uuid('uuid')->unique();

            $table->unsignedBigInteger('entitlement_id')->index();

            /*
             * Public-friendly license reference.
             *
             * Not used as the cryptographic secret.
             */
            $table->string('license_reference', 96)
                ->unique();

            /*
             * Hash/fingerprint of the issued token so Central can identify
             * and revoke it without storing an unsafe reusable plaintext secret.
             */
            $table->string('token_hash', 128)
                ->nullable()
                ->unique();

            /*
             * Signed entitlement payload issued by Central.
             * Signature format/key rotation can evolve independently.
             */
            $table->longText('signed_payload')->nullable();

            $table->string('signature_algorithm', 64)
                ->nullable();

            $table->string('signing_key_id', 128)
                ->nullable()
                ->index();

            /*
             * active | suspended | revoked | expired
             */
            $table->string('status', 32)
                ->default('active')
                ->index();

            $table->unsignedInteger('activation_limit')
                ->default(1);

            $table->timestamp('issued_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->timestamp('last_validated_at')->nullable();

            $table->timestamp('revoked_at')->nullable();
            $table->string('revocation_reason', 500)->nullable();

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->foreign('entitlement_id')
                ->references('id')
                ->on('marketplace_entitlements')
                ->cascadeOnDelete();

            $table->index(
                ['entitlement_id', 'status'],
                'mp_license_entitlement_status'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | Marketplace License Activations
        |--------------------------------------------------------------------------
        |
        | Binds an off-server license to a specific standalone Core instance.
        |
        | core_instance_uuid must be generated once by the standalone Core and
        | persisted locally. It is not regenerated on every request.
        |
        */

        Schema::create('marketplace_license_activations', function (Blueprint $table) {
            $table->id();

            $table->uuid('uuid')->unique();

            $table->unsignedBigInteger('license_id')->index();

            $table->uuid('core_instance_uuid')->index();

            $table->string('domain', 255)
                ->nullable()
                ->index();

            /*
             * Stable installation fingerprint. It must not contain raw
             * hardware/server secrets.
             */
            $table->string('installation_fingerprint', 128)
                ->nullable()
                ->index();

            /*
             * active | deactivated | revoked
             */
            $table->string('status', 32)
                ->default('active')
                ->index();

            $table->timestamp('activated_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamp('last_validated_at')->nullable();
            $table->timestamp('deactivated_at')->nullable();

            $table->string('core_version', 64)->nullable();
            $table->string('deployment', 32)
                ->default('off_server');

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->foreign('license_id')
                ->references('id')
                ->on('marketplace_licenses')
                ->cascadeOnDelete();

            /*
             * A license cannot be activated twice against the exact same
             * standalone Core instance.
             */
            $table->unique(
                ['license_id', 'core_instance_uuid'],
                'mp_license_core_instance_unique'
            );

            $table->index(
                ['license_id', 'status'],
                'mp_activation_license_status'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_license_activations');
        Schema::dropIfExists('marketplace_licenses');
        Schema::dropIfExists('marketplace_entitlements');
    }
};
