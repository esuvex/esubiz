<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('currency_tax_rules', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Scope
            |--------------------------------------------------------------------------
            */

            $table->enum('scope_type', [

                'global',

                'workspace',

                'application',

                'developer'

            ])->default('global');

            $table->foreignId('owner_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Location
            |--------------------------------------------------------------------------
            */

            $table->string('country_code', 2)
                ->nullable();

            $table->string('region')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Currency
            |--------------------------------------------------------------------------
            */

            $table->foreignId('currency_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Tax
            |--------------------------------------------------------------------------
            */

            $table->string('name');

            $table->enum('type', [

                'vat',

                'sales_tax',

                'withholding',

                'service_tax',

                'custom'

            ])->default('vat');

            $table->decimal('rate', 8, 4)
                ->default(0);

            /*
            |--------------------------------------------------------------------------
            | Calculation
            |--------------------------------------------------------------------------
            */

            $table->boolean('included_in_price')
                ->default(false);

            $table->json('conditions')
                ->nullable();

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_active')
                ->default(true);

            /*
            |--------------------------------------------------------------------------
            | Audit
            |--------------------------------------------------------------------------
            */

            $table->timestamps();

            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currency_tax_rules');
    }
};
