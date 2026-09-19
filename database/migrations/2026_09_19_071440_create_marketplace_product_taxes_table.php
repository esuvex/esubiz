<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketplace_product_taxes', function (Blueprint $table) {
            $table->id();

            $table->string('product_type', 80);

            $table->foreignId('central_tax_id');

            /*
             * false = Inclusive:
             * tax is contained within the listed product price.
             *
             * true = Exclusive:
             * tax is added above the listed product price.
             */
            $table->boolean('is_exclusive')->default(false);

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->foreign('central_tax_id', 'mkt_prod_tax_tax_fk')
                ->references('id')
                ->on('central_taxes')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->unique(
                ['product_type', 'central_tax_id'],
                'mkt_prod_tax_type_uq'
            );

            $table->index(
                ['product_type', 'is_active'],
                'mkt_prod_tax_active_ix'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_product_taxes');
    }
};
