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
        | Resource Entitlements
        |--------------------------------------------------------------------------
        |
        | Stores the effective resources available to this tenant.
        | Core provides the base entitlement. Website Types and Addons
        | may extend these values later.
        |
        */

        Schema::create('resource_entitlements', function (Blueprint $table) {
            $table->id();

            $table->string('resource');

            $table->decimal('base_limit', 20, 4)->nullable();
            $table->decimal('addon_limit', 20, 4)->default(0);

            $table->boolean('is_unlimited')->default(false);

            $table->string('unit')->nullable();

            $table->timestamps();

            $table->unique('resource');
        });

        /*
        |--------------------------------------------------------------------------
        | Resource Usage
        |--------------------------------------------------------------------------
        */

        Schema::create('resource_usage', function (Blueprint $table) {
            $table->id();

            $table->string('resource');

            $table->decimal('used', 20, 4)->default(0);

            $table->decimal('limit', 20, 4)->nullable();

            $table->boolean('is_unlimited')->default(false);

            $table->string('unit')->nullable();

            $table->timestamps();

            $table->unique('resource');
        });

        /*
        |--------------------------------------------------------------------------
        | Resource Usage Events
        |--------------------------------------------------------------------------
        */

        Schema::create('resource_usage_events', function (Blueprint $table) {
            $table->id();

            $table->string('resource');

            $table->string('action');

            $table->decimal('quantity', 20, 4)->default(0);

            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index('resource');
            $table->index([
                'source_type',
                'source_id',
            ]);
        });

        /*
        |--------------------------------------------------------------------------
        | Financial Accounts
        |--------------------------------------------------------------------------
        */

        Schema::create('financial_accounts', function (Blueprint $table) {
            $table->id();

            $table->string('name');

            $table->string('type')->default('income');

            $table->string('currency', 10)->default('NGN');

            $table->decimal('balance', 20, 2)->default(0);

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | Financial Transactions
        |--------------------------------------------------------------------------
        */

        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('account_id')
                ->nullable()
                ->constrained('financial_accounts')
                ->nullOnDelete();

            $table->string('reference')->unique();

            $table->string('type')->default('income');

            $table->string('category')->nullable();

            $table->string('description')->nullable();

            $table->decimal('amount', 20, 2);

            $table->string('currency', 10)->default('NGN');

            $table->string('status')->default('completed');

            $table->string('payment_method')->nullable();

            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();

            $table->date('transaction_date');

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index('type');
            $table->index('category');
            $table->index('transaction_date');
            $table->index([
                'source_type',
                'source_id',
            ]);
        });

        /*
        |--------------------------------------------------------------------------
        | Financial Income Records
        |--------------------------------------------------------------------------
        */

        Schema::create('financial_income_records', function (Blueprint $table) {
            $table->id();

            $table->string('reference')->unique();

            $table->string('source');

            $table->string('description')->nullable();

            $table->decimal('amount', 20, 2);

            $table->string('currency', 10)->default('NGN');

            $table->string('status')->default('received');

            $table->date('income_date');

            $table->foreignId('financial_transaction_id')
                ->nullable()
                ->constrained('financial_transactions')
                ->nullOnDelete();

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index('source');
            $table->index('income_date');
        });

        /*
        |--------------------------------------------------------------------------
        | License Assignments
        |--------------------------------------------------------------------------
        |
        | Records what this tenant is licensed/entitled to use.
        |
        */

        Schema::create('license_assignments', function (Blueprint $table) {
            $table->id();

            $table->string('license_key')->unique();

            $table->string('product_type');

            $table->string('product_id')->nullable();

            $table->string('product_name');

            $table->string('product_version')->nullable();

            $table->string('role')->default('component');

            $table->string('status')->default('active');

            $table->timestamp('issued_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index('product_type');
            $table->index('product_id');
            $table->index('role');
            $table->index('status');
        });

        /*
        |--------------------------------------------------------------------------
        | License Components
        |--------------------------------------------------------------------------
        */

        Schema::create('license_components', function (Blueprint $table) {
            $table->id();

            $table->foreignId('license_assignment_id')
                ->constrained('license_assignments')
                ->cascadeOnDelete();

            $table->string('component_type');

            $table->string('component_id')->nullable();

            $table->string('component_name');

            $table->string('component_version')->nullable();

            $table->string('status')->default('active');

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index('component_type');
            $table->index('component_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('license_components');
        Schema::dropIfExists('license_assignments');
        Schema::dropIfExists('financial_income_records');
        Schema::dropIfExists('financial_transactions');
        Schema::dropIfExists('financial_accounts');
        Schema::dropIfExists('resource_usage_events');
        Schema::dropIfExists('resource_usage');
        Schema::dropIfExists('resource_entitlements');
    }
};
