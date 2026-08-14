<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('capacities');
    }

    public function down(): void
    {
        Schema::create('capacities', function ($table) {
            $table->id();
            $table->timestamps();
        });
    }
};
