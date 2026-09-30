<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketplace_credit_volume_purchases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('marketplace_order_id')->unique();
            $table->unsignedBigInteger('website_id');
            $table->unsignedBigInteger('tier_id');
            $table->string('credit_type', 40);
            $table->string('deployment_type', 20);
            $table->unsignedBigInteger('credit_quantity');
            $table->char('currency', 3);
            $table->decimal('unit_price', 18, 2);
            $table->decimal('total_amount', 18, 2);
            $table->timestamps();

            $table->index(['website_id', 'credit_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_credit_volume_purchases');
    }
};
