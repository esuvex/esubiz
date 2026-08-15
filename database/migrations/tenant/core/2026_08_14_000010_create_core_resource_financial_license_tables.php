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

            $table->unique('resource', 'uq_res_ent_resource');
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

            $table->unique('resource', 'uq_ru_resource');
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

            $table->index('resource', 'ix_rue_resource');
            $table->index(
                ['source_type', 'source_id'],
                'ix_rue_source_t_source'
            );
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
                ->constrained('financial_accounts', 'id', 'fk_ft_account')
                ->nullOnDelete();

            $table->string('reference')->unique('uq_ft_referenc');

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

            $table->index('type', 'ix_ft_type');
            $table->index('category', 'ix_ft_category');
            $table->index('transaction_date', 'ix_ft_transact');
            $table->index(
                ['source_type', 'source_id'],
                'ix_ft_source_t_source'
            );
        });

        /*
        |--------------------------------------------------------------------------
        | Financial Income Records
        |--------------------------------------------------------------------------
        */

        Schema::create('financial_income_records', function (Blueprint $table) {
            $table->id();

            $table->string('reference')->unique('uq_fir_referenc');

            $table->string('source');

            $table->string('description')->nullable();

            $table->decimal('amount', 20, 2);

            $table->string('currency', 10)->default('NGN');

            $table->string('status')->default('received');

            $table->date('income_date');

            $table->foreignId('financial_transaction_id')
                ->nullable()
                ->constrained('financial_transactions', 'id', 'fk_fir_financia')
                ->nullOnDelete();

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index('source', 'ix_fir_source');
            $table->index('income_date', 'ix_fir_income_d');
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

            $table->string('license_key')->unique('uq_la_license');

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

            $table->index('product_type', 'ix_la_product');
            $table->index('product_id', 'ix_la_product_1');
            $table->index('role', 'ix_la_role');
            $table->index('status', 'ix_la_status');
        });

        /*
        |--------------------------------------------------------------------------
        | License Components
        |--------------------------------------------------------------------------
        */

        Schema::create('license_components', function (Blueprint $table) {
            $table->id();

            $table->foreignId('license_assignment_id')
                ->constrained('license_assignments', 'id', 'fk_lc_license')
                ->cascadeOnDelete();

            $table->string('component_type');

            $table->string('component_id')->nullable();

            $table->string('component_name');

            $table->string('component_version')->nullable();

            $table->string('status')->default('active');

            $table->json('metadata')->nullable();

            $table->timestamps();

            $table->index('component_type', 'ix_lc_componen');
            $table->index('component_id', 'ix_lc_componen_1');
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
