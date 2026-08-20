<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('core_addons', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->string('key')->unique();
            $table->string('name');
            $table->string('category')->nullable();
            $table->text('description')->nullable();

            $table->string('parent_capability')->nullable();
            $table->string('entitlement_type')->default('feature');

            $table->json('capabilities')->nullable();

            $table->boolean('saas_available')->default(false);
            $table->boolean('off_server_available')->default(false);

            $table->boolean('is_unlimited')->default(false);
            $table->decimal('default_allocation', 18, 2)->nullable();
            $table->string('allocation_unit')->nullable();

            $table->decimal('saas_price', 18, 2)->nullable();
            $table->string('saas_currency', 3)->nullable();
            $table->string('saas_billing_interval')->nullable();
            $table->unsignedInteger('saas_billing_period')->nullable();

            $table->decimal('off_server_price', 18, 2)->nullable();
            $table->string('off_server_currency', 3)->nullable();

            $table->string('version')->default('1.0.0');
            $table->boolean('is_active')->default(true);

            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('parent_capability');
            $table->index('saas_available');
            $table->index('off_server_available');
            $table->index('is_active');
        });

        Schema::create('core_addon_bundles', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();

            $table->string('key')->unique();
            $table->string('name');
            $table->string('category')->nullable();
            $table->text('description')->nullable();

            $table->boolean('saas_available')->default(false);
            $table->boolean('off_server_available')->default(false);

            $table->decimal('saas_price', 18, 2)->nullable();
            $table->string('saas_currency', 3)->nullable();
            $table->string('saas_billing_interval')->nullable();
            $table->unsignedInteger('saas_billing_period')->nullable();

            $table->decimal('off_server_price', 18, 2)->nullable();
            $table->string('off_server_currency', 3)->nullable();

            $table->string('version')->default('1.0.0');
            $table->boolean('is_active')->default(true);

            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('saas_available');
            $table->index('off_server_available');
            $table->index('is_active');
        });

        Schema::create('core_addon_bundle_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('bundle_id')
                ->constrained('core_addon_bundles')
                ->cascadeOnDelete();

            $table->foreignId('addon_id')
                ->constrained('core_addons')
                ->cascadeOnDelete();

            $table->decimal('allocation', 18, 2)->nullable();
            $table->boolean('is_unlimited')->default(false);

            $table->timestamps();

            $table->unique(
                ['bundle_id', 'addon_id'],
                'core_bundle_addon_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('core_addon_bundle_items');
        Schema::dropIfExists('core_addon_bundles');
        Schema::dropIfExists('core_addons');
    }
};
