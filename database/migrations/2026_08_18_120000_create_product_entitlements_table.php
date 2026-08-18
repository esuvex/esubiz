<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('product_entitlements')) {
            return;
        }

        Schema::create('product_entitlements', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('website_id')->nullable()->index();
            $table->unsignedBigInteger('workspace_id')->nullable()->index();

            $table->string('product_type', 50)->index();
            $table->unsignedBigInteger('product_id')->index();
            $table->string('product_name')->nullable();

            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->string('order_reference')->nullable()->index();

            $table->enum('status', [
                'pending',
                'active',
                'expired',
                'cancelled',
                'refunded',
            ])->default('active')->index();

            $table->enum('fulfilment_type', [
                'purchase',
                'subscription',
                'rental',
                'license',
                'marketplace',
            ])->default('purchase');

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(
                ['user_id', 'website_id', 'product_type', 'product_id'],
                'product_entitlement_lookup'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_entitlements');
    }
};
