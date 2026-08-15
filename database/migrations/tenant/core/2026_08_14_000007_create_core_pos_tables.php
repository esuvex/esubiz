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
        | POS Stores
        |--------------------------------------------------------------------------
        */

        Schema::create('pos_stores', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('code')->unique();

            $table->string('status')->default('active');

            $table->text('address')->nullable();

            $table->json('settings')->nullable();

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | POS Terminals
        |--------------------------------------------------------------------------
        */

        Schema::create('pos_terminals', function (Blueprint $table) {
            $table->id();

            $table->foreignId('store_id')
                ->constrained('pos_stores')
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('code');

            $table->string('status')->default('active');

            $table->boolean('offline_mode')->default(true);

            $table->timestamp('last_sync_at')->nullable();

            $table->json('settings')->nullable();

            $table->timestamps();

            $table->unique([
                'store_id',
                'code',
            ]);
        });

        /*
        |--------------------------------------------------------------------------
        | POS Products
        |--------------------------------------------------------------------------
        */

        Schema::create('pos_products', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('sku')->nullable()->unique();

            $table->text('description')->nullable();

            $table->decimal('price', 20, 2)->default(0);
            $table->decimal('cost_price', 20, 2)->default(0);

            $table->decimal('stock_quantity', 20, 4)->default(0);

            $table->string('status')->default('active');

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index('status');
        });

        /*
        |--------------------------------------------------------------------------
        | POS Sales
        |--------------------------------------------------------------------------
        */

        Schema::create('pos_sales', function (Blueprint $table) {
            $table->id();

            $table->foreignId('store_id')
                ->constrained('pos_stores')
                ->cascadeOnDelete();

            $table->foreignId('terminal_id')
                ->nullable()
                ->constrained('pos_terminals')
                ->nullOnDelete();

            $table->string('reference')->unique();

            $table->decimal('subtotal', 20, 2)->default(0);
            $table->decimal('discount', 20, 2)->default(0);
            $table->decimal('tax', 20, 2)->default(0);
            $table->decimal('total', 20, 2)->default(0);

            $table->string('currency', 10)->default('NGN');

            $table->string('payment_status')->default('pending');
            $table->string('sale_status')->default('completed');

            $table->boolean('is_offline')->default(false);

            $table->string('offline_reference')->nullable();

            $table->timestamp('completed_at')->nullable();

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index('payment_status');
            $table->index('sale_status');
            $table->index('offline_reference');
        });

        /*
        |--------------------------------------------------------------------------
        | POS Sale Items
        |--------------------------------------------------------------------------
        */

        Schema::create('pos_sale_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sale_id')
                ->constrained('pos_sales')
                ->cascadeOnDelete();

            $table->foreignId('product_id')
                ->nullable()
                ->constrained('pos_products')
                ->nullOnDelete();

            $table->string('product_name');

            $table->decimal('quantity', 20, 4)->default(1);

            $table->decimal('unit_price', 20, 2)->default(0);

            $table->decimal('discount', 20, 2)->default(0);

            $table->decimal('tax', 20, 2)->default(0);

            $table->decimal('total', 20, 2)->default(0);

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | POS Payments
        |--------------------------------------------------------------------------
        */

        Schema::create('pos_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sale_id')
                ->constrained('pos_sales')
                ->cascadeOnDelete();

            $table->string('method');

            $table->decimal('amount', 20, 2);

            $table->string('reference')->nullable();

            $table->boolean('is_offline')->default(false);

            $table->string('status')->default('completed');

            $table->timestamps();

            $table->index('method');
            $table->index('reference');
        });

        /*
        |--------------------------------------------------------------------------
        | POS Offline Sync Queue
        |--------------------------------------------------------------------------
        */

        Schema::create('pos_offline_sync_queue', function (Blueprint $table) {
            $table->id();

            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id');

            $table->string('action');

            $table->json('payload');

            $table->string('status')->default('pending');

            $table->unsignedInteger('attempts')->default(0);

            $table->text('last_error')->nullable();

            $table->timestamp('synced_at')->nullable();

            $table->timestamps();

            $table->index([
                'entity_type',
                'entity_id',
            ]);

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_offline_sync_queue');
        Schema::dropIfExists('pos_payments');
        Schema::dropIfExists('pos_sale_items');
        Schema::dropIfExists('pos_sales');
        Schema::dropIfExists('pos_products');
        Schema::dropIfExists('pos_terminals');
        Schema::dropIfExists('pos_stores');
    }
};
