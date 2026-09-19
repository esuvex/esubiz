<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('central_taxes', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique('central_tax_uuid_uq');
            $table->string('name', 120);
            $table->string('code', 50)->unique('central_tax_code_uq');
            $table->decimal('rate', 8, 4);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(
                ['is_active', 'sort_order'],
                'central_tax_active_ix'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('central_taxes');
    }
};
