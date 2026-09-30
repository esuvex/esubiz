<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketplace_credit_volume_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('credit_type', 40);
            $table->string('deployment_type', 20)->default('both');
            $table->char('currency', 3)->default('NGN');
            $table->unsignedBigInteger('min_quantity');
            $table->unsignedBigInteger('max_quantity')->nullable();
            $table->unsignedBigInteger('unit_price_minor');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(
                ['credit_type', 'deployment_type', 'currency', 'is_active'],
                'credit_volume_lookup'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_credit_volume_tiers');
    }
};
