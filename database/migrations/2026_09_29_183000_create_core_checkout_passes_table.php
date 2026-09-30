<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('core_checkout_passes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('marketplace_order_id');
            $table->unsignedBigInteger('website_id');
            $table->string('checkout_origin', 32);
            $table->char('token_hash', 64)->unique();
            $table->timestamp('expires_at');
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();

            $table->index(['marketplace_order_id', 'website_id']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('core_checkout_passes');
    }
};
