<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('application_currencies', function (Blueprint $table) {

            $table->id();

            $table->string('application_type');

            $table->unsignedBigInteger('application_id');

            $table->foreignId('currency_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->boolean('is_default')->default(false);

            $table->timestamps();

            $table->unique(
                ['application_type', 'application_id', 'currency_id'],
                'app_curr_unique'
            );

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_currencies');
    }
};
