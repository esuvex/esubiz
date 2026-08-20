<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('core_addon_capability_allocations', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('addon_id');
            $table->string('capability_key', 255);

            $table->decimal('allocation', 18, 2)->default(0);
            $table->boolean('is_unlimited')->default(false);

            $table->timestamps();

            $table->unique(
                ['addon_id', 'capability_key'],
                'uq_core_addon_capability_allocation'
            );

            $table->index('addon_id');
            $table->index('capability_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('core_addon_capability_allocations');
    }
};
