<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('financial_rules');
    }

    public function down(): void
    {
        Schema::create('financial_rules', function ($table) {
            $table->id();
            $table->timestamps();
        });
    }
};
