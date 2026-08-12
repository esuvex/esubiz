<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('currency_tax_rules', function (Blueprint $table) {
            $table->string('type')->default('vat')->change();
        });
    }

    public function down(): void
    {
        Schema::table('currency_tax_rules', function (Blueprint $table) {
            $table->enum('type', [
                'vat',
                'sales_tax',
                'withholding',
                'service_tax',
                'custom',
            ])->default('vat')->change();
        });
    }
};
