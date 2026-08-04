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
        Schema::create('capacity_product_items', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Relationships
            |--------------------------------------------------------------------------
            */

            $table->foreignId('capacity_product_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('capacity_type_id')
                ->constrained()
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Capacity
            |--------------------------------------------------------------------------
            */

            $table->unsignedBigInteger('value')->default(0);

            $table->boolean('is_unlimited')->default(false);

            /*
            |--------------------------------------------------------------------------
            | Duration
            |--------------------------------------------------------------------------
            |
            | Null = Permanent
            | Number = Expires after the specified duration
            |
            */

            $table->unsignedInteger('duration')->nullable();

            $table->enum('duration_unit', [

                'day',

                'week',

                'month',

                'year',

                'lifetime'

            ])->nullable();

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_active')->default(true);

            /*
            |--------------------------------------------------------------------------
            | Audit
            |--------------------------------------------------------------------------
            */

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Constraints
            |--------------------------------------------------------------------------
            */

            $table->unique([
                'capacity_product_id',
                'capacity_type_id'
            ]);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('capacity_product_items');
    }
};
