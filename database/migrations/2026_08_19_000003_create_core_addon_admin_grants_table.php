<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('core_addon_admin_grants', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('addon_id');
            $table->unsignedBigInteger('website_id');
            $table->unsignedBigInteger('workspace_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();

            $table->decimal('price', 10, 2)->default(0);
            $table->char('currency', 3)->default('NGN');

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            $table->enum('status', [
                'active',
                'expired',
                'cancelled'
            ])->default('active');

            $table->timestamps();

            $table->index('addon_id');
            $table->index('website_id');
            $table->index('workspace_id');
            $table->index('user_id');
            $table->index(['website_id', 'addon_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('core_addon_admin_grants');
    }
};
