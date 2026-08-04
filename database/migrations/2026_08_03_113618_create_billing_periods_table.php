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
        Schema::create('billing_periods', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Identity
            |--------------------------------------------------------------------------
            */

            $table->string('name');

            $table->string('slug')->unique();

            $table->text('description')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Duration
            |--------------------------------------------------------------------------
            */

            $table->unsignedInteger('duration');

            $table->enum('duration_unit', [

                'day',

                'week',

                'month',

                'year',

                'lifetime'

            ]);

            /*
            |--------------------------------------------------------------------------
            | Behaviour
            |--------------------------------------------------------------------------
            */

            $table->boolean('is_recurring')->default(true);

            $table->boolean('is_lifetime')->default(false);

            /*
            |--------------------------------------------------------------------------
            | Ordering
            |--------------------------------------------------------------------------
            */

            $table->unsignedInteger('sort_order')->default(0);

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

            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_periods');
    }
};
