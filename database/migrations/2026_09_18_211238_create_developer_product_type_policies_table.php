<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('developer_product_type_policies', function (Blueprint $table) {
            $table->id();

            $table->string('product_type', 50)->unique();

            $table->boolean('developer_build_enabled')
                ->default(false);

            $table->boolean('developer_publish_enabled')
                ->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('developer_product_type_policies');
    }
};
